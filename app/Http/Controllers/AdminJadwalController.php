<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalPetugas;
use App\Models\Wilayah;
use App\Models\User;
use Carbon\Carbon;

class AdminJadwalController extends Controller
{
    public function index(Request $request)
    {
        $bulan     = $request->bulan ?? date('m');
        $tahun     = $request->tahun ?? date('Y');
        $wilayahId = $request->wilayah;

        // Ambil semua wilayah
        $semuaWilayah = Wilayah::orderBy('nama')->get();

        // Ambil semua jadwal bulan ini (semua wilayah)
        $query = JadwalPetugas::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        if ($wilayahId) {
            $query->where('wilayah_id', $wilayahId);
        }

        $semuaJadwal = $query->orderBy('tanggal')->with('user')->get();

        // Ambil semua petugas aktif, di-key by id
        $petugasMap = User::where('role', 'petugas')
            ->whereHas('petugas', fn($q) => $q->where('status', 'aktif'))
            ->get()->keyBy('id');

        // Petugas per wilayah (untuk form edit) — hanya yang aktif
        $petugasPerWilayah = User::where('role', 'petugas')
            ->whereHas('petugas', fn($q) => $q->where('status', 'aktif'))
            ->get()
            ->groupBy('wilayah_id');

        // Kelompokkan jadwal per wilayah, lalu per tanggal
        $jadwalPerWilayah = [];

        foreach ($semuaJadwal as $j) {
            $wid = $j->wilayah_id;
            $tgl = $j->tanggal;

            if (!isset($jadwalPerWilayah[$wid])) {
                $jadwalPerWilayah[$wid] = [];
            }

            if (!isset($jadwalPerWilayah[$wid][$tgl])) {
                $jadwalPerWilayah[$wid][$tgl] = (object)[
                    'shift_pagi_id'  => null,
                    'ket_pagi'       => null,
                    'shift_siang_id' => null,
                    'ket_siang'      => null,
                ];
            }

            if ($j->shift === 'pagi') {
                $jadwalPerWilayah[$wid][$tgl]->shift_pagi_id = $j->user_id;
                $jadwalPerWilayah[$wid][$tgl]->ket_pagi      = $j->keterangan;
            } else {
                $jadwalPerWilayah[$wid][$tgl]->shift_siang_id = $j->user_id;
                $jadwalPerWilayah[$wid][$tgl]->ket_siang      = $j->keterangan;
            }
        }

        foreach ($jadwalPerWilayah as $wid => &$jadwalMap) {
            ksort($jadwalMap);
        }
        unset($jadwalMap);

        return view('admin.jadwal.index', compact(
            'bulan',
            'tahun',
            'semuaWilayah',
            'jadwalPerWilayah',
            'petugasMap',
            'petugasPerWilayah',
            'wilayahId'
        ));
    }

    public function store(Request $request)
    {
        $bulan     = $request->bulan;
        $tahun     = $request->tahun;
        $wilayahId = $request->wilayah_id;

        if (!$wilayahId) {
            return redirect()->back()->with('error', 'Wilayah tidak boleh kosong.');
        }

        foreach ($request->jadwal as $hari => $data) {
            $tanggal = Carbon::createFromDate($tahun, $bulan, (int)$hari)->format('Y-m-d');

            // SHIFT PAGI
            if (!empty($data['shift_pagi'])) {
                JadwalPetugas::updateOrCreate(
                    [
                        'tanggal'    => $tanggal,
                        'shift'      => 'pagi',
                        'wilayah_id' => $wilayahId,
                    ],
                    [
                        'user_id'    => $data['shift_pagi'],
                        'wilayah_id' => $wilayahId,
                        'keterangan' => $data['ket_pagi'] ?? 'normal',
                    ]
                );
            } else {
                // Hapus jika dikosongkan
                JadwalPetugas::where('tanggal', $tanggal)
                    ->where('shift', 'pagi')
                    ->where('wilayah_id', $wilayahId)
                    ->delete();
            }

            // SHIFT SIANG
            if (!empty($data['shift_siang'])) {
                JadwalPetugas::updateOrCreate(
                    [
                        'tanggal'    => $tanggal,
                        'shift'      => 'siang',
                        'wilayah_id' => $wilayahId,
                    ],
                    [
                        'user_id'    => $data['shift_siang'],
                        'wilayah_id' => $wilayahId,
                        'keterangan' => $data['ket_siang'] ?? 'normal',
                    ]
                );
            } else {
                JadwalPetugas::where('tanggal', $tanggal)
                    ->where('shift', 'siang')
                    ->where('wilayah_id', $wilayahId)
                    ->delete();
            }
        }

        return redirect()
            ->route('admin.jadwal.index', ['bulan' => $bulan, 'tahun' => $tahun, 'wilayah' => $wilayahId])
            ->with('success', 'Jadwal berhasil disimpan.');
    }
}