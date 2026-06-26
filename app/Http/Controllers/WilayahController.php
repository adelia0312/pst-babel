<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wilayah;

class WilayahController extends Controller
{
    public function index()
    {
        $wilayahList = Wilayah::orderBy('nama')->get();
        return view('admin.tim_petugas', compact('wilayahList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'   => 'required|string|max:100|unique:wilayah,nama',
            'lokasi' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ], [
            'nama.required' => 'Nama wilayah wajib diisi.',
            'nama.unique'   => 'Nama wilayah sudah terdaftar.',
        ]);

        Wilayah::create($request->only('nama', 'lokasi', 'alamat', 'status'));

        return redirect()->route('admin.tim-petugas')
            ->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function update(Request $request, Wilayah $wilayah)
    {
        $request->validate([
            'nama'   => 'required|string|max:100|unique:wilayah,nama,' . $wilayah->id,
            'lokasi' => 'nullable|string|max:100',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
        ], [
            'nama.required' => 'Nama wilayah wajib diisi.',
            'nama.unique'   => 'Nama wilayah sudah terdaftar.',
        ]);

        $wilayah->update($request->only('nama', 'lokasi', 'alamat', 'status'));

        return redirect()->route('admin.tim-petugas')
            ->with('success', 'Wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $wilayah)
    {
        if ($wilayah->petugas()->count() > 0) {
            return redirect()->route('admin.tim-petugas')
                ->with('error', 'Wilayah tidak dapat dihapus karena masih memiliki petugas.');
        }

        $wilayah->delete();

        return redirect()->route('admin.tim-petugas')
            ->with('success', 'Wilayah berhasil dihapus.');
    }
}