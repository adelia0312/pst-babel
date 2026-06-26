<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AbsensiKoordinatorController extends Controller
{
    private function wilayahId(): int
    {
        return (int) Auth::user()->wilayah_id;
    }

    // ─────────────────────────────────────────────────────────
    // INDEX — monitoring absensi wilayah koordinator
    // ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $wilayahId     = $this->wilayahId();
        $filterTanggal = $request->input('tanggal', now()->toDateString());
        $filterSesi    = $request->input('sesi');
        $filterStatus  = $request->input('status');

        $wilayah = Wilayah::findOrFail($wilayahId);

        $query = Absensi::with(['user'])
            ->where('wilayah_id', $wilayahId)
            ->where('tanggal', $filterTanggal)
            ->orderByDesc('id');

        if ($filterSesi) {
            $query->where('sesi', $filterSesi);
        }

        if ($filterStatus) {
            $query->where('status_kehadiran', $filterStatus);
        }

        $absensi = $query->paginate(25)->withQueryString();

        // Absen masuk terbaru hari ini (sidebar)
        $absensiTerbaru = Absensi::with('user')
            ->where('wilayah_id', $wilayahId)
            ->where('tanggal', $filterTanggal)
            ->whereNotNull('jam_masuk')
            ->orderByDesc('jam_masuk')
            ->limit(10)
            ->get();

        // Stats khusus wilayah ini
        $base           = Absensi::where('wilayah_id', $wilayahId)->where('tanggal', $filterTanggal);
        // Basis "Total Petugas": petugas yang TERJADWAL pada tanggal ini di wilayah ini,
        // bukan seluruh petugas aktif di wilayah (yang mungkin tidak semuanya bertugas hari ini).
        $totalPetugas   = \App\Models\JadwalPetugas::where('wilayah_id', $wilayahId)
            ->whereDate('tanggal', $filterTanggal)->distinct('user_id')->count('user_id');
        $totalHariIni   = (clone $base)->count();
        $totalHadir     = (clone $base)->whereIn('status_kehadiran', ['tepat_waktu', 'toleransi', 'terlambat', 'tidak_scan_keluar'])->distinct('user_id')->count('user_id');
        $totalTerlambat = (clone $base)->where('status_kehadiran', 'terlambat')->count();
        $totalAlpha     = max(0, $totalPetugas - $totalHadir);

        // QR status untuk tombol QR display
        $service  = new AbsensiService();
        $now      = Carbon::now();
        $qrStatus = collect($service->getStatusQrHariIni($now))->filter(fn($q) => $q['aktif']);

        return view('koordinator.absensi.index', compact(
            'wilayah',
            'absensi',
            'filterTanggal', 'filterSesi', 'filterStatus',
            'totalPetugas', 'totalHariIni', 'totalHadir', 'totalTerlambat', 'totalAlpha',
            'qrStatus',
            'absensiTerbaru'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // POLLING — real-time update via JS fetch
    // ─────────────────────────────────────────────────────────
    public function polling(Request $request)
    {
        $wilayahId = $this->wilayahId();
        $after     = (int) $request->input('after', 0);
        $tanggal   = $request->input('tanggal', now()->toDateString());

        $rows = Absensi::with(['user'])
            ->where('wilayah_id', $wilayahId)
            ->where('tanggal', $tanggal)
            ->where('id', '>', $after)
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id'                  => $a->id,
                'nama'                => $a->user->name ?? '-',
                'username'            => $a->user->username ?? '-',
                'inisial'             => strtoupper(substr($a->user->name ?? '?', 0, 2)),
                'tanggal'             => Carbon::parse($a->tanggal)->format('d M Y'),
                'jenis_scan'          => $a->jenis_scan,
                'jenis_scan_label'    => $a->label_jenis_scan,
                'jam'                 => $a->jam_masuk ?? $a->jam_keluar ?? '—',
                'sesi'                => $a->sesi,
                'status_kehadiran'    => $a->status_kehadiran,
                'keterlambatan_menit' => $a->keterlambatan_menit ?? 0,
                'verified_status'     => $a->verified_status,
            ]);

        $maxId = $rows->max('id') ?? $after;

        $base           = Absensi::where('wilayah_id', $wilayahId)->where('tanggal', $tanggal);
        $totalPetugas   = \App\Models\JadwalPetugas::where('wilayah_id', $wilayahId)
            ->whereDate('tanggal', $tanggal)->distinct('user_id')->count('user_id');
        $totalHariIni   = (clone $base)->count();
        $totalHadir     = (clone $base)->whereIn('status_kehadiran', ['tepat_waktu', 'toleransi', 'terlambat', 'tidak_scan_keluar'])->distinct('user_id')->count('user_id');
        $totalTerlambat = (clone $base)->where('status_kehadiran', 'terlambat')->count();

        return response()->json([
            'rows'   => $rows,
            'max_id' => $maxId,
            'stats'  => [
                'total_petugas'   => $totalPetugas,
                'total_hari_ini'  => $totalHariIni,
                'total_hadir'     => $totalHadir,
                'total_terlambat' => $totalTerlambat,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // VERIFY — verifikasi satu record absensi
    // Route: PATCH /koordinator/absensi/{id}/verify
    // ─────────────────────────────────────────────────────────
    public function verify(Request $request, $id)
    {
        $wilayahId = $this->wilayahId();

        $absensi = Absensi::where('id', $id)
            ->where('wilayah_id', $wilayahId)
            ->firstOrFail();

        $absensi->update([
            'verified_status' => 'approved',
            'verified_by'     => Auth::id(),
            'verified_at'     => now(),
        ]);

        return back()->with('success', 'Absensi ' . ($absensi->user->name ?? '') . ' berhasil diverifikasi.');
    }

    // ─────────────────────────────────────────────────────────
    // BULK VERIFY — verifikasi semua sekaligus
    // Route: PATCH /koordinator/absensi/bulk-verify
    // ─────────────────────────────────────────────────────────
    public function bulkVerify(Request $request)
    {
        $wilayahId = $this->wilayahId();
        $tanggal   = $request->input('tanggal', now()->toDateString());

        $count = Absensi::where('wilayah_id', $wilayahId)
            ->where('tanggal', $tanggal)
            ->where(fn($q) => $q->whereNull('verified_status')->orWhere('verified_status', '!=', 'approved'))
            ->update([
                'verified_status' => 'approved',
                'verified_by'     => Auth::id(),
                'verified_at'     => now(),
            ]);

        return back()->with('success', "{$count} data absensi berhasil diverifikasi.");
    }

    // ─────────────────────────────────────────────────────────
    // QR JSON — return status QR aktif (untuk halaman QR display)
    // Route: GET /koordinator/absensi/qr-json
    // ─────────────────────────────────────────────────────────
    public function qrJson(Request $request)
    {
        $sesi    = $request->input('sesi');
        $service = new AbsensiService();
        $now     = Carbon::now();

        $allQr = $service->getStatusQrHariIni($now);
        $aktif = collect($allQr)->filter(fn($q) => $q['aktif']);

        if ($sesi) {
            $aktif = $aktif->filter(fn($q) => $q['sesi'] === $sesi);
        }

        return response()->json([
            'success'     => true,
            'qr_status'   => $aktif,
            'server_time' => $now->format('H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // QR DISPLAY — halaman tampilan QR untuk petugas scan
    // Route: GET /koordinator/absensi/qr-display
    // ─────────────────────────────────────────────────────────
    public function qrDisplay(Request $request)
    {
        $wilayahId    = $this->wilayahId();
        $wilayah      = Wilayah::findOrFail($wilayahId);
        $selectedSesi = $request->input('sesi');
        $service      = new AbsensiService();
        $now          = Carbon::now();

        $allQr    = $service->getStatusQrHariIni($now);
        $qrStatus = collect($allQr)->filter(fn($q) => $q['aktif']);

        if ($selectedSesi) {
            $qrStatus = $qrStatus->filter(fn($q) => $q['sesi'] === $selectedSesi);
        }

        return view('koordinator.absensi.qr_display', compact(
            'wilayah', 'qrStatus', 'selectedSesi', 'now'
        ));
    }

    // ─────────────────────────────────────────────────────────
    // EXPORT — download CSV absensi wilayah koordinator
    // Route: GET /koordinator/absensi/export
    // ─────────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $wilayahId     = $this->wilayahId();
        $filterTanggal = $request->input('tanggal', now()->toDateString());
        $filterSesi    = $request->input('sesi');

        $query = Absensi::with(['user'])
            ->where('wilayah_id', $wilayahId)
            ->where('tanggal', $filterTanggal)
            ->orderByDesc('id');

        if ($filterSesi) {
            $query->where('sesi', $filterSesi);
        }

        $data     = $query->get();
        $wilayah  = Wilayah::find($wilayahId);
        $filename = 'absensi-' . ($wilayah->nama ?? 'wilayah') . '-' . $filterTanggal . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // BOM agar Excel baca CSV sebagai UTF-8 (hindari karakter rusak)
            fputcsv($handle, ['Nama', 'Username', 'Tanggal', 'Sesi', 'Jenis Scan', 'Jam', 'Status Kehadiran', 'Keterlambatan (menit)', 'Verified']);

            foreach ($data as $a) {
                fputcsv($handle, [
                    $a->user->name ?? '-',
                    $a->user->username ?? '-',
                    Carbon::parse($a->tanggal)->format('d M Y'),
                    ucfirst($a->sesi ?? '-'),
                    $a->label_jenis_scan,
                    $a->jam_masuk ?? $a->jam_keluar ?? '-',
                    $a->label_status_kehadiran,
                    $a->keterlambatan_menit ?? 0,
                    $a->verified_status === 'approved' ? 'Ya' : 'Belum',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }
}