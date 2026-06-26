<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Petugas;
use App\Models\Absensi;
use App\Models\JadwalPetugas;
use App\Models\LaporanHarianBaru;
use App\Models\EvaluasiPetugas;
use App\Models\ChecklistHarian;
use App\Models\SurveyKepuasan;
use Carbon\Carbon;

class DashboardKoordinatorController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $wilayah   = $user->wilayah;
        $wilayahId = $user->wilayah_id ?? 0;
        $today     = now('Asia/Jakarta')->toDateString();
        $bulanIni  = now('Asia/Jakarta');

        // ── Petugas di wilayah ini ──
        $petugasIds    = Petugas::where('wilayah_id', $wilayahId)->pluck('user_id');
        $petugasObjIds = Petugas::where('wilayah_id', $wilayahId)->pluck('id');
        $totalPetugas  = $petugasIds->count();
        $tambahPetugas = Petugas::where('wilayah_id', $wilayahId)
            ->whereMonth('created_at', $bulanIni->month)->count();

        // ── KPI: Hadir Hari Ini ──
        $hadirHariIni = Absensi::whereIn('user_id', $petugasIds)
            ->whereDate('tanggal', $today)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->distinct('user_id')->count('user_id');

        // Basis persentase: petugas yang TERJADWAL hari ini di wilayah ini, bukan seluruh petugas aktif
        $totalTerjadwalHariIni = JadwalPetugas::where('wilayah_id', $wilayahId)
            ->whereDate('tanggal', $today)
            ->distinct('user_id')->count('user_id');
        $pctHadir = $totalTerjadwalHariIni > 0 ? round($hadirHariIni / $totalTerjadwalHariIni * 100, 1) : 0;

        // ── KPI: Laporan Pending ──
        $laporanPending  = LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'submitted')->count();
        $laporanApproved = LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'approved')->count();

        // ── KPI: Avg Kinerja Tim ──
        $avgKinerja = round(EvaluasiPetugas::whereIn('petugas_id', $petugasObjIds)->avg('jumlah_nilai') ?? 0);

        // ── Absensi Status Detail (untuk donut + grid) ──
        $absensiRows = Absensi::whereIn('user_id', $petugasIds)->whereDate('tanggal', $today)->get();
        $absensiStatus = [
            'tepat_waktu'       => $absensiRows->whereIn('status_kehadiran', ['tepat_waktu','toleransi'])->count(),
            'terlambat'         => $absensiRows->where('status_kehadiran', 'terlambat')->count(),
            'alpha'             => $absensiRows->where('status_kehadiran', 'alpha')->count(),
            'tidak_scan_keluar' => $absensiRows->where('status_kehadiran', 'tidak_scan_keluar')->count(),
        ];

        // ── Jadwal Hari Ini (wilayah ini saja) ──
        $jadwalHariIni = JadwalPetugas::with(['user', 'wilayah'])
            ->whereDate('tanggal', $today)
            ->where('wilayah_id', $wilayahId)
            ->orderBy('shift')
            ->get();

        // Belum absen = terjadwal hari ini tapi belum absen (bukan semua petugas)
        $userIdTerjadwal   = $jadwalHariIni->pluck('user_id')->unique();
        $totalTerjadwal    = $userIdTerjadwal->count();
        $userIdSudahAbsen  = Absensi::whereDate('tanggal', $today)
            ->whereIn('user_id', $userIdTerjadwal)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->pluck('user_id')->unique();
        $belumAbsenHariIni = max(0, $totalTerjadwal - $userIdSudahAbsen->count());

        // ── Aktivitas Terbaru (wilayah ini) ──
        $aktivitasTerbaru = collect();
        LaporanHarianBaru::where('wilayah_id', $wilayahId)
            ->where('status', 'submitted')->latest('updated_at')->limit(4)->get()
            ->each(fn($l) => $aktivitasTerbaru->push((object)[
                'type' => 'grn',
                'text' => '<strong>' . e($l->nama_petugas ?? 'Petugas') . '</strong> submit laporan harian',
                'time' => Carbon::parse($l->updated_at)->diffForHumans(),
            ]));
        Absensi::whereIn('user_id', $petugasIds)->with('user')
            ->whereDate('tanggal', $today)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->latest('created_at')->limit(4)->get()
            ->each(fn($a) => $aktivitasTerbaru->push((object)[
                'type' => 'blu',
                'text' => '<strong>' . e($a->user->name ?? 'Petugas') . '</strong> absen masuk',
                'time' => Carbon::parse($a->created_at)->diffForHumans(),
            ]));
        $aktivitasTerbaru = $aktivitasTerbaru->take(8);

        // ── Tabel Laporan Pending ──
        $laporanList = LaporanHarianBaru::with(['user', 'wilayah'])
            ->where('wilayah_id', $wilayahId)->where('status', 'submitted')
            ->latest('updated_at')->limit(5)->get();

        // ── Performa Tim ──
        $performaTim = collect();
        Petugas::with('user')->where('wilayah_id', $wilayahId)->get()
            ->each(function ($p) use (&$performaTim) {
                $eval = EvaluasiPetugas::where('petugas_id', $p->id)->latest('tanggal_evaluasi')->first();
                if ($eval && $eval->jumlah_nilai) {
                    $performaTim->push((object)[
                        'nama'  => $p->user->name ?? '-',
                        'nilai' => round($eval->jumlah_nilai, 1),
                    ]);
                }
            });
        $performaTim = $performaTim->sortByDesc('nilai')->take(5)->values();

        // ── Chart Tren (30 hari + 12 bulan + 5 tahun, mirip admin) ──
        // Data mingguan (12 minggu terakhir)
        $mingguan = [];
        for ($i = 11; $i >= 0; $i--) {
            $mulai = now('Asia/Jakarta')->startOfWeek()->subWeeks($i);
            $akhir = $mulai->copy()->endOfWeek();
            $mingguan[] = [
                'label'     => $mulai->format('d/m') . '–' . $akhir->format('d/m'),
                'hadir'     => Absensi::whereIn('user_id', $petugasIds)
                    ->whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
                    ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
                    ->whereNotNull('status_kehadiran')->distinct('user_id')->count('user_id'),
                'laporan'   => LaporanHarianBaru::where('wilayah_id', $wilayahId)
                    ->whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
                    ->whereIn('status', ['submitted', 'approved'])->count(),
                'checklist' => ChecklistHarian::whereIn('user_id', $petugasIds)
                    ->whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
                    ->whereIn('status', ['verified', 'selesai'])->count(),
            ];
        }

        // Data per petugas (untuk grafik perbandingan tim)
        $perPetugas = Petugas::with('user')
            ->where('wilayah_id', $wilayahId)
            ->whereHas('user', fn($q) => $q->where('role', 'petugas')->where('is_active', true))
            ->get()->map(function ($p) use ($wilayahId, $bulanIni) {
                $eval = EvaluasiPetugas::where('petugas_id', $p->id)->latest('tanggal_evaluasi')->first();
                return [
                    'label'     => $p->user->name ?? '-',
                    'hadir'     => Absensi::where('user_id', $p->user_id)
                        ->whereYear('tanggal', $bulanIni->year)->whereMonth('tanggal', $bulanIni->month)
                        ->whereNotIn('status_kehadiran', ['tidak_hadir','alpha'])
                        ->whereNotNull('status_kehadiran')->count(),
                    'laporan'   => LaporanHarianBaru::where('wilayah_id', $wilayahId)
                        ->where('user_id', $p->user_id)
                        ->whereYear('tanggal', $bulanIni->year)->whereMonth('tanggal', $bulanIni->month)
                        ->whereIn('status', ['submitted','approved'])->count(),
                    'checklist' => ChecklistHarian::where('user_id', $p->user_id)
                        ->whereYear('tanggal', $bulanIni->year)->whereMonth('tanggal', $bulanIni->month)
                        ->whereIn('status', ['verified','selesai'])->count(),
                    'kinerja'   => $eval ? round($eval->jumlah_nilai, 1) : 0,
                ];
            })->values()->toArray();

        $harian = [];
        for ($i = 29; $i >= 0; $i--) {
            $tgl = now('Asia/Jakarta')->subDays($i)->toDateString();
            $harian[] = [
                'label'     => Carbon::parse($tgl)->isoFormat('D/M'),
                'hadir'     => Absensi::whereIn('user_id', $petugasIds)->whereDate('tanggal', $tgl)
                                ->whereNotIn('status_kehadiran', ['tidak_hadir','alpha'])
                                ->whereNotNull('status_kehadiran')->distinct('user_id')->count('user_id'),
                'laporan'   => LaporanHarianBaru::where('wilayah_id', $wilayahId)
                                ->whereDate('tanggal', $tgl)->whereIn('status', ['submitted','approved'])->count(),
                'checklist' => ChecklistHarian::whereIn('user_id', $petugasIds)
                                ->whereDate('tanggal', $tgl)->whereIn('status', ['verified','selesai'])->count(),
            ];
        }
        $bulanan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bln = now('Asia/Jakarta')->subMonths($i);
            $bulanan[] = [
                'label'     => $bln->isoFormat('MMM YY'),
                'hadir'     => Absensi::whereIn('user_id', $petugasIds)
                              ->whereYear('tanggal', $bln->year)->whereMonth('tanggal', $bln->month)
                              ->whereNotIn('status_kehadiran', ['tidak_hadir','alpha'])
                              ->whereNotNull('status_kehadiran')->distinct('user_id')->count('user_id'),
                'laporan'   => LaporanHarianBaru::where('wilayah_id', $wilayahId)
                              ->whereYear('tanggal', $bln->year)->whereMonth('tanggal', $bln->month)
                              ->whereIn('status', ['submitted','approved'])->count(),
                'checklist' => ChecklistHarian::whereIn('user_id', $petugasIds)
                              ->whereYear('tanggal', $bln->year)->whereMonth('tanggal', $bln->month)
                              ->whereIn('status', ['verified','selesai'])->count(),
                'survey'    => SurveyKepuasan::where('wilayah_id', $wilayahId)
                              ->whereYear('diisi_pada', $bln->year)->whereMonth('diisi_pada', $bln->month)
                              ->where('status', 'selesai')->count(),
            ];
        }
        $tahunan = [];
        for ($i = 4; $i >= 0; $i--) {
            $tahun = now('Asia/Jakarta')->year - $i;
            $tahunan[] = [
                'label'     => (string)$tahun,
                'hadir'     => Absensi::whereIn('user_id', $petugasIds)->whereYear('tanggal', $tahun)
                              ->whereNotIn('status_kehadiran', ['tidak_hadir','alpha'])
                              ->whereNotNull('status_kehadiran')->distinct('user_id')->count('user_id'),
                'laporan'   => LaporanHarianBaru::where('wilayah_id', $wilayahId)->whereYear('tanggal', $tahun)
                              ->whereIn('status', ['submitted','approved'])->count(),
                'checklist' => ChecklistHarian::whereIn('user_id', $petugasIds)->whereYear('tanggal', $tahun)
                              ->whereIn('status', ['verified','selesai'])->count(),
                'survey'    => SurveyKepuasan::where('wilayah_id', $wilayahId)->whereYear('diisi_pada', $tahun)
                              ->where('status', 'selesai')->count(),
            ];
        }

        return view('koordinator.dashboardkoor', compact(
            'wilayah',
            'totalPetugas', 'tambahPetugas',
            'hadirHariIni', 'pctHadir', 'totalTerjadwalHariIni',
            'laporanPending', 'laporanApproved',
            'avgKinerja',
            'absensiStatus',
            'jadwalHariIni', 'belumAbsenHariIni',
            'aktivitasTerbaru',
            'laporanList',
            'performaTim',
            'harian', 'mingguan', 'bulanan', 'tahunan', 'perPetugas'
        ));
    }

    public function timPetugas()
    {
        $user    = Auth::user();
        $wilayah = $user->wilayah;
        $petugas = Petugas::with('user')
            ->where('wilayah_id', $user->wilayah_id)
            ->whereHas('user', fn($q) => $q->where('role', 'petugas'))
            ->get();
        return view('koordinator.tim_petugas', compact('petugas', 'wilayah'));
    }
}