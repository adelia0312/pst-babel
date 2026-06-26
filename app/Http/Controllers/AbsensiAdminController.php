<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Petugas;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AbsensiAdminController extends Controller
{
    // ─────────────────────────────────────────
    // INDEX — halaman monitoring absensi
    // ─────────────────────────────────────────
    public function index(Request $request)
    {
        $filterTanggal = $request->input('tanggal', now()->toDateString());
        $filterWilayah = $request->input('wilayah_id');
        $filterSesi    = $request->input('sesi');
        $filterStatus  = $request->input('status');

        $query = Absensi::with(['user.wilayah'])
            ->where('tanggal', $filterTanggal)
            ->orderByDesc('id');

        if ($filterWilayah) {
            $query->whereHas('user', fn($q) => $q->where('wilayah_id', $filterWilayah));
        }

        if ($filterSesi) {
            $query->where('sesi', $filterSesi);
        }

        if ($filterStatus) {
            $query->where('status_kehadiran', $filterStatus);
        }

        $absensi = $query->paginate(25)->withQueryString();

        // Stats
        $baseQuery = Absensi::where('tanggal', $filterTanggal);

        // Basis "Total Petugas": petugas yang TERJADWAL pada tanggal (dan wilayah) yang difilter,
        // bukan seluruh petugas aktif di semua wilayah.
        $jadwalQuery = \App\Models\JadwalPetugas::whereDate('tanggal', $filterTanggal);
        if ($filterWilayah) {
            $jadwalQuery->where('wilayah_id', $filterWilayah);
        }
        $totalPetugas   = $jadwalQuery->distinct('user_id')->count('user_id');
        $totalHariIni   = (clone $baseQuery)->count();
        $totalHadir     = (clone $baseQuery)->whereIn('status_kehadiran', ['tepat_waktu', 'toleransi', 'terlambat', 'tidak_scan_keluar'])->distinct('user_id')->count('user_id');
        $totalTolerasi  = (clone $baseQuery)->where('status_kehadiran', 'toleransi')->count();
        $totalTerlambat = (clone $baseQuery)->where('status_kehadiran', 'terlambat')->count();

        $wilayahList = Wilayah::orderBy('nama')->get();

        return view('admin.absensi.index', compact(
            'absensi',
            'filterTanggal', 'filterWilayah', 'filterSesi', 'filterStatus',
            'totalPetugas', 'totalHariIni', 'totalHadir', 'totalTolerasi', 'totalTerlambat',
            'wilayahList'
        ));
    }

    // ─────────────────────────────────────────
    // POLLING — real-time update via JS fetch
    // ─────────────────────────────────────────
    public function polling(Request $request)
    {
        $after   = (int) $request->input('after', 0);
        $tanggal = $request->input('tanggal', now()->toDateString());

        $rows = Absensi::with(['user.wilayah'])
            ->where('tanggal', $tanggal)
            ->where('id', '>', $after)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id'              => $a->id,
                'nama'            => $a->user->name ?? '-',
                'username'        => $a->user->username ?? '-',
                'inisial'         => strtoupper(substr($a->user->name ?? '?', 0, 2)),
                'wilayah'         => $a->user->wilayah->nama ?? '-',
                'tanggal'         => \Carbon\Carbon::parse($a->tanggal)->format('d M Y'),
                'jenis_scan'      => $a->jenis_scan,
                'jenis_scan_label'=> $a->label_jenis_scan,
                'jam'             => $a->jam_masuk ?? $a->jam_keluar ?? '—',
                'status_kehadiran'=> $a->status_kehadiran,
                'keterlambatan_menit' => $a->keterlambatan_menit ?? 0,
            ]);

        $maxId = $rows->max('id') ?? $after;

        // Stats terkini
        $base           = Absensi::where('tanggal', $tanggal);
        $totalHariIni   = (clone $base)->count();
        $totalHadir     = (clone $base)->whereIn('status_kehadiran', ['tepat_waktu', 'toleransi', 'terlambat', 'tidak_scan_keluar'])->distinct('user_id')->count('user_id');
        $totalTolerasi  = (clone $base)->where('status_kehadiran', 'toleransi')->count();
        $totalTerlambat = (clone $base)->where('status_kehadiran', 'terlambat')->count();
        $totalPetugas   = \App\Models\JadwalPetugas::whereDate('tanggal', $tanggal)->distinct('user_id')->count('user_id');

        return response()->json([
            'rows'   => $rows,
            'max_id' => $maxId,
            'stats'  => [
                'total_petugas'   => $totalPetugas,
                'total_hari_ini'  => $totalHariIni,
                'total_hadir'     => $totalHadir,
                'total_toleransi' => $totalTolerasi,
                'total_terlambat' => $totalTerlambat,
            ],
        ]);
    }

    // ─────────────────────────────────────────
    // EXPORT — download CSV
    // ─────────────────────────────────────────
    public function export(Request $request)
    {
        $filterTanggal = $request->input('tanggal', now()->toDateString());
        $filterWilayah = $request->input('wilayah_id');
        $filterSesi    = $request->input('sesi');
        $filterStatus  = $request->input('status');

        $query = Absensi::with(['user.wilayah'])
            ->where('tanggal', $filterTanggal)
            ->orderByDesc('id');

        if ($filterWilayah) {
            $query->whereHas('user', fn($q) => $q->where('wilayah_id', $filterWilayah));
        }
        if ($filterSesi)   $query->where('sesi', $filterSesi);
        if ($filterStatus) $query->where('status_kehadiran', $filterStatus);

        $data = $query->get();

        $filename = 'absensi-' . $filterTanggal . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM agar Excel baca CSV sebagai UTF-8 (hindari karakter rusak)
            fputcsv($handle, ['Nama', 'Username', 'Wilayah', 'Tanggal', 'Jenis Scan', 'Jam', 'Status Kehadiran', 'Keterlambatan (menit)']);

            foreach ($data as $a) {
                fputcsv($handle, [
                    $a->user->name ?? '-',
                    $a->user->username ?? '-',
                    $a->user->wilayah->nama ?? '-',
                    \Carbon\Carbon::parse($a->tanggal)->format('d M Y'),
                    $a->label_jenis_scan,
                    $a->jam_masuk ?? $a->jam_keluar ?? '-',
                    $a->label_status_kehadiran,
                    $a->keterlambatan_menit ?? 0,
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}