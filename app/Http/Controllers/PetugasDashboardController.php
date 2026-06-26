<?php
// ============================================================
// FILE   : app/Http/Controllers/PetugasDashboardController.php
// STATUS : GANTI FILE LAMA (file ini sudah ada, timpa isinya)
// ============================================================

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\JadwalPetugas;
use App\Models\Absensi;
use App\Models\ChecklistHarian;
use App\Models\LaporanHarianBaru;
use App\Models\EvaluasiPetugas;
use App\Models\Tugas;
use Carbon\Carbon;

class PetugasDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->petugas) {
            abort(403, 'User belum terdaftar sebagai petugas');
        }

        $wilayahId = $user->petugas->wilayah_id;
        $userId    = $user->id;
        $bulanIni  = now('Asia/Jakarta');
        $today     = $bulanIni->toDateString();

        // ── Jadwal bulan ini ──
        $jadwalPetugas = JadwalPetugas::where('wilayah_id', $wilayahId)
            ->with('user')
            ->get();

        $totalJadwal = JadwalPetugas::where('user_id', $userId)
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month)
            ->count();

        // ── Kehadiran bulan ini ──
        $totalHadir = Absensi::where('user_id', $userId)
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->distinct('tanggal')
            ->count('tanggal');

        $totalIzin = Absensi::where('user_id', $userId)
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month)
            ->where('status_kehadiran', 'izin')
            ->distinct('tanggal')
            ->count('tanggal');

        $totalAlpha = Absensi::where('user_id', $userId)
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->distinct('tanggal')
            ->count('tanggal');

        $pctHadir = $totalJadwal > 0 ? round($totalHadir / $totalJadwal * 100) : 0;
        $pctIzin  = $totalJadwal > 0 ? round($totalIzin  / $totalJadwal * 100) : 0;
        $pctAlpha = $totalJadwal > 0 ? round($totalAlpha / $totalJadwal * 100) : 0;

        // ── Status absen hari ini ──
        $absensiHariIni = Absensi::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->latest()
            ->first();
        $sudahAbsen = $absensiHariIni !== null &&
            !in_array($absensiHariIni->status_kehadiran, ['tidak_hadir', 'alpha', null]);
        $jamMasuk   = $sudahAbsen ? optional($absensiHariIni->created_at)->format('H:i') : null;

        // ── Laporan Harian bulan ini — per status ──
        $laporanQuery = LaporanHarianBaru::where('user_id', $userId)
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month);

        $totalLaporan    = $laporanQuery->count();
        $laporanApproved = (clone $laporanQuery)->where('status', 'approved')->count();
        $laporanPending  = (clone $laporanQuery)->where('status', 'submitted')->count();
        $laporanDraft    = (clone $laporanQuery)->where('status', 'draft')->count();
        $laporanRejected = (clone $laporanQuery)->where('status', 'rejected')->count();

        // ── Checklist bulan ini ──
        $totalChecklist = ChecklistHarian::where('user_id', $userId)
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month)
            ->whereIn('status', ['submit', 'verified'])
            ->count();
        $pctChecklist = $totalJadwal > 0 ? round($totalChecklist / max(1, $totalJadwal * 2) * 100) : 0;

        // ── Nilai Kinerja terakhir ──
        $evalTerakhir = EvaluasiPetugas::where('petugas_id', $user->petugas->id)
            ->orderByDesc('periode')
            ->first();
        $nilaiKinerja = $evalTerakhir ? $evalTerakhir->jumlah_nilai : null;

        // ── Tugas Aktif ──
        // Coba ambil dari model Tugas jika ada, fallback ke null
        $tugasAktif = collect();
        if (class_exists(\App\Models\Tugas::class)) {
            try {
                $tugasAktif = \App\Models\Tugas::where('user_id', $userId)
                    ->whereIn('status', ['pending', 'aktif', 'belum'])
                    ->orderBy('deadline')
                    ->take(5)
                    ->get();
            } catch (\Exception $e) {
                $tugasAktif = collect();
            }
        }

        // ── Aktivitas Terakhir (gabungan absensi + laporan + checklist) ──
        $aktivitas = collect();

        Absensi::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->each(function ($a) use (&$aktivitas) {
                $label = match ($a->status_kehadiran) {
                    'hadir', 'tepat_waktu' => 'Absensi masuk tercatat',
                    'terlambat'            => 'Absensi masuk (terlambat)',
                    'izin'                 => 'Izin tercatat',
                    default                => 'Absensi diperbarui',
                };
                $aktivitas->push((object) [
                    'keterangan' => $label,
                    'created_at' => $a->created_at,
                    'type'       => 'grn',
                ]);
            });

        LaporanHarianBaru::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get()
            ->each(function ($l) use (&$aktivitas) {
                $label = match ($l->status) {
                    'submitted' => 'Laporan harian dikirim, menunggu review',
                    'approved'  => 'Laporan harian disetujui koordinator',
                    'rejected'  => 'Laporan harian dikembalikan koordinator',
                    default     => 'Laporan harian disimpan sebagai draft',
                };
                $aktivitas->push((object) [
                    'keterangan' => $label,
                    'created_at' => $l->updated_at,
                    'type'       => 'blu',
                ]);
            });

        ChecklistHarian::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->limit(2)
            ->get()
            ->each(function ($c) use (&$aktivitas) {
                $label = match ($c->status) {
                    'verified' => 'Checklist harian diverifikasi',
                    'submit'   => 'Checklist harian dikirim',
                    default    => 'Checklist harian diperbarui',
                };
                $aktivitas->push((object) [
                    'keterangan' => $label,
                    'created_at' => $c->updated_at,
                    'type'       => 'amb',
                ]);
            });

        $aktivitas = $aktivitas->sortByDesc('created_at')->take(6)->values();

        return view('petugas.dashboardpetugas', compact(
            'jadwalPetugas',
            'totalJadwal',
            'totalHadir',
            'pctHadir',
            'totalIzin',
            'pctIzin',
            'totalAlpha',
            'pctAlpha',
            'sudahAbsen',
            'jamMasuk',
            'totalLaporan',
            'laporanApproved',
            'laporanPending',
            'laporanDraft',
            'laporanRejected',
            'totalChecklist',
            'pctChecklist',
            'nilaiKinerja',
            'evalTerakhir',
            'tugasAktif',
            'aktivitas'
        ));
    }

    public function jadwal()
    {
        $userId = Auth::id();

        $jadwalPetugas = JadwalPetugas::where('user_id', $userId)
            ->orderBy('tanggal')
            ->get();

        return view('petugas.jadwal', compact('jadwalPetugas'));
    }
}