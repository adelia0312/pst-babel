<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petugas;
use App\Models\Wilayah;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PetugasController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'username'   => 'required|unique:users,username|max:50',
            'password'   => 'required|min:5',
            'no_hp'      => 'nullable|string|max:20',
            'wilayah_id' => 'required|exists:wilayah,id',
        ]);

        // 1. Simpan ke tabel users
        $user = User::create([
    'name'       => $request->name,
    'username'   => $request->username,
    'password'   => Hash::make($request->password),
    'role'       => $request->role,
    'no_hp'      => $request->no_hp,
    'wilayah_id' => $request->wilayah_id,
    'is_active'  => true,  // ← tambah baris ini
]);

        // 2. Simpan ke tabel petugas
        Petugas::create([
            'user_id'    => $user->id,
            'wilayah_id' => $request->wilayah_id,
            'shift'      => 'pagi',
            'status'     => 'aktif',
        ]);

        return back()->with('success', 'Petugas berhasil ditambahkan.');
    }

    public function update(Request $request, Petugas $petugas)
{
   $request->validate([
    'name'       => 'required|string|max:100',
    'username' => 'required|max:50|unique:users,username,' . $petugas->user_id,
    // ✅ GANTI JADI — nullable, hanya validasi kalau diisi
'password' => 'nullable|min:5',
    'no_hp'      => 'nullable|string|max:20',
    'wilayah_id' => 'required|exists:wilayah,id',
    'role'       => 'required|in:petugas,koordinator', // 🔥 TAMBAH
    'status'     => 'nullable|in:aktif,nonaktif',      // STATUS PETUGAS
]);

    $data = [
    'name' => $request->name,
    'username' => $request->username,
    'no_hp' => $request->no_hp,
    'role' => $request->role, // 🔥 TAMBAH
];

    // kalau password diisi → update
    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $petugas->user->update($data);

    // Update status di tabel petugas + sinkronisasi users.is_active
    if ($request->filled('status')) {
        $petugas->status = $request->status;
        $petugas->save();

        $petugas->user->update([
            'is_active' => $request->status === 'aktif',
        ]);
    }

    return back()->with('success', 'Data berhasil diupdate');
}

    public function toggleStatus(Petugas $petugas)
    {
        $petugas->status = $petugas->status === 'aktif' ? 'nonaktif' : 'aktif';
        $petugas->save();

        // Sinkronisasi is_active di tabel users agar login ikut terblokir
        $petugas->user->update([
            'is_active' => $petugas->status === 'aktif',
        ]);

        $label = $petugas->status === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Petugas berhasil {$label}.");
    }

    // Ganti seluruh method destroy:
public function destroy(Petugas $petugas)
{
    $user = $petugas->user;
    $petugas->delete();
    if ($user && $user->role !== 'admin') {
        $user->delete();
    }
    return back()->with('success', 'Petugas berhasil dihapus.');
}
}