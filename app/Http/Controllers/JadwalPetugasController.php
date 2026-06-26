<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalPetugas;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JadwalPetugasController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $user = Auth::user();

        // ambil petugas sesuai wilayah
        $petugas = User::where('role', 'petugas')
            ->where('wilayah_id', $user->wilayah_id)
            ->get();

        // ambil jadwal bulan ini
        $jadwal = JadwalPetugas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('wilayah_id', $user->wilayah_id)
            ->get();

        // format jadwal per tanggal
        $jadwalBulan = [];

        $jadwalBulan = [];

foreach ($jadwal as $j) {
    $tgl = $j->tanggal;

    if (!isset($jadwalBulan[$tgl])) {
        $jadwalBulan[$tgl] = (object)[
            'shift_pagi_id' => null,
            'ket_pagi' => null,
            'shift_siang_id' => null,
            'ket_siang' => null,
        ];
    }

    if ($j->shift == 'pagi') {
        $jadwalBulan[$tgl]->shift_pagi_id = $j->user_id;
        $jadwalBulan[$tgl]->ket_pagi = $j->keterangan;
    } else {
        $jadwalBulan[$tgl]->shift_siang_id = $j->user_id;
        $jadwalBulan[$tgl]->ket_siang = $j->keterangan;
    }
}

        return view('koordinator.jadwal.index', compact('bulan', 'tahun', 'petugas', 'jadwalBulan'));
    }

    public function create()
    {
        $user = Auth::user();

        $petugas = User::where('role', 'petugas')
            ->where('wilayah_id', $user->wilayah_id)
            ->get();

        return view('koordinator.jadwal.create', compact('petugas'));
    }

public function store(Request $request)
{
    $bulan = $request->bulan;
    $tahun = $request->tahun;

    foreach ($request->jadwal as $hari => $data) {

        // PERBAIKAN: Carbon memastikan format YYYY-MM-DD konsisten
$tanggal = Carbon::createFromDate($tahun, $bulan, (int)$hari)->format('Y-m-d');

        // SHIFT PAGI
$wilayahId = Auth::user()->wilayah_id;

// SHIFT PAGI
if (!empty($data['shift_pagi'])) {
    JadwalPetugas::updateOrCreate(
        [
            'tanggal'    => $tanggal,
            'shift'      => 'pagi',
            'wilayah_id' => $wilayahId,  // PERBAIKAN: tambah wilayah_id ke key
        ],
        [
            'user_id'    => $data['shift_pagi'],
            'wilayah_id' => $wilayahId,
            'keterangan' => $data['ket_pagi'] ?? 'normal',
        ]
    );
}

// SHIFT SIANG
if (!empty($data['shift_siang'])) {
    JadwalPetugas::updateOrCreate(
        [
            'tanggal'    => $tanggal,
            'shift'      => 'siang',
            'wilayah_id' => $wilayahId,  // PERBAIKAN: tambah wilayah_id ke key
        ],
        [
            'user_id'    => $data['shift_siang'],
            'wilayah_id' => $wilayahId,
            'keterangan' => $data['ket_siang'] ?? 'normal',
        ]
    );
}
    }

    return redirect()->back()->with('success', 'Jadwal berhasil diupdate');
}
}