<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\LaporanHarianBaru;
use App\Models\Petugas;
use App\Models\Absensi;
use App\Models\Wilayah;
use App\Models\EvaluasiPetugas;
use App\Models\ChecklistHarian;
use App\Models\JadwalPetugas;
use App\Models\SurveyKepuasan;
use App\Models\SurveyJawaban;
use App\Models\SurveyPertanyaan;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $now   = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // ══════════════════════════════════════════
        // 1. TOTAL PETUGAS (dari Tim Petugas)
        // ══════════════════════════════════════════
        $totalPetugas   = Petugas::aktif()->count();
        $tambahPetugas  = Petugas::aktif()->whereMonth('created_at', $now->month)->count();

        // ══════════════════════════════════════════
        // 2. STATISTIK ABSENSI (dari menu Absensi)
        // ══════════════════════════════════════════
        $hadirHariIni = Absensi::whereDate('tanggal', $today)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->distinct('user_id')
            ->count('user_id');

        // Basis persentase: petugas yang TERJADWAL hari ini, bukan seluruh petugas aktif
        $totalTerjadwalHariIni = JadwalPetugas::whereDate('tanggal', $today)
            ->distinct('user_id')->count('user_id');
        $pctHadir = $totalTerjadwalHariIni > 0 ? round($hadirHariIni / $totalTerjadwalHariIni * 100, 1) : 0;

        // Minggu ini
        $startWeek = $now->copy()->startOfWeek();
        $endWeek   = $now->copy()->endOfWeek();
        $hadirMingguIni = Absensi::whereBetween('tanggal', [$startWeek->toDateString(), $endWeek->toDateString()])
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->distinct('user_id')
            ->count('user_id');

        // Bulan ini
        $hadirBulanIni = Absensi::whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->distinct('user_id')
            ->count('user_id');

        // Distribusi status absensi hari ini
        $absensiStatus = Absensi::whereDate('tanggal', $today)
            ->whereNotNull('status_kehadiran')
            ->selectRaw('status_kehadiran, count(*) as total')
            ->groupBy('status_kehadiran')
            ->pluck('total', 'status_kehadiran')
            ->toArray();

        // ══════════════════════════════════════════
        // 3. JADWAL PETUGAS HARI INI
        // ══════════════════════════════════════════
        $jadwalHariIni = JadwalPetugas::with(['user', 'wilayah'])
            ->whereDate('tanggal', $today)
            ->orderBy('shift')
            ->get();

        // Hitung yang terjadwal tapi belum absen (bukan semua petugas aktif)
        $userIdTerjadwal   = $jadwalHariIni->pluck('user_id')->unique();
        $totalTerjadwal    = $userIdTerjadwal->count();
        $userIdSudahAbsen  = Absensi::whereDate('tanggal', $today)
            ->whereIn('user_id', $userIdTerjadwal)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->pluck('user_id')->unique();
        $belumAbsenHariIni = max(0, $totalTerjadwal - $userIdSudahAbsen->count());

        // ══════════════════════════════════════════
        // 4. CHECKLIST HARIAN (dari menu Checklist)
        // ══════════════════════════════════════════
        $checklistHariIni = ChecklistHarian::whereDate('tanggal', $today)->get();
        $checklistSelesai = $checklistHariIni->filter(fn($c) => $c->status === 'verified' || $c->status === 'selesai')->count();
        $checklistBelum   = $checklistHariIni->filter(fn($c) => $c->status !== 'verified' && $c->status !== 'selesai')->count();
        $checklistTotal   = $checklistHariIni->count();

        // Hitung total item checked vs total item
        $totalItemChecked = 0;
        $totalItemAll     = 0;
        foreach ($checklistHariIni as $ck) {
            $totalItemChecked += $ck->totalChecked();
            $totalItemAll     += $ck->totalItems();
        }
        $pctChecklistItem = $totalItemAll > 0 ? round($totalItemChecked / $totalItemAll * 100) : 0;

        // ══════════════════════════════════════════
        // 5. LAPORAN HARIAN (dari menu Laporan Harian)
        // ══════════════════════════════════════════
        $laporanHariIni  = LaporanHarianBaru::whereDate('tanggal', $today)->count();
        $laporanBulanIni = LaporanHarianBaru::whereYear('tanggal', $now->year)->whereMonth('tanggal', $now->month)->count();
        $laporanPending  = LaporanHarianBaru::where('status', 'submitted')->count();
        $laporanApproved = LaporanHarianBaru::where('status', 'approved')->count();
        $pctLaporan      = $totalPetugas > 0 ? round($laporanPending / max(1, $totalPetugas) * 100) : 0;

        $laporanPendingList = LaporanHarianBaru::with(['user', 'wilayah'])
            ->where('status', 'submitted')
            ->latest('updated_at')
            ->limit(6)
            ->get();

        // ══════════════════════════════════════════
        // 6. SURVEI KEPUASAN (dari menu Survei Kepuasan)
        // ══════════════════════════════════════════
        $surveyTotal     = SurveyKepuasan::where('status', 'selesai')->count();
        $surveyBulanIni  = SurveyKepuasan::where('status', 'selesai')
            ->whereYear('diisi_pada', $now->year)
            ->whereMonth('diisi_pada', $now->month)
            ->count();

        // Rata-rata rating dari jawaban bertipe rating
        $ratingQuery = SurveyJawaban::whereHas('pertanyaan', fn($q) => $q->where('tipe', 'rating'))
            ->whereHas('survey', fn($q) => $q->where('status', 'selesai'));
        $avgRating = round($ratingQuery->avg(\DB::raw('CAST(jawaban AS DECIMAL(5,2))')) ?? 0, 1);

        // Distribusi rating (1-5) untuk chart donut
        $ratingDistribusi = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribusi[$i] = (clone $ratingQuery)
                ->where('jawaban', (string)$i)
                ->count();
        }

        // ══════════════════════════════════════════
        // 7. REKAP PENILAIAN / KINERJA
        // ══════════════════════════════════════════
        $avgKinerja   = round(EvaluasiPetugas::avg('jumlah_nilai') ?? 0, 1);
        $deltaKinerja = 0;

        $kinerjaWilayah = Wilayah::all()->map(function ($wilayah) {
            $avg = EvaluasiPetugas::where('wilayah_id', $wilayah->id)->avg('jumlah_nilai');
            $wilayah->avg_kinerja = $avg ? round($avg, 1) : null;
            return $wilayah;
        })->filter(fn($w) => $w->avg_kinerja !== null);

        // Grade distribusi
        $gradeDistribusi = EvaluasiPetugas::selectRaw('grade, count(*) as total')
            ->groupBy('grade')
            ->pluck('total', 'grade')
            ->toArray();

        $topPerformer = EvaluasiPetugas::with(['petugas.user'])
            ->orderByDesc('jumlah_nilai')
            ->limit(5)
            ->get();

        // ══════════════════════════════════════════
        // 8. GRAFIK AKTIVITAS OPERASIONAL
        // ══════════════════════════════════════════
        $wilayahList = Wilayah::orderBy('nama')->get();

        // Data per wilayah (untuk grafik perbandingan)
        $perWilayah = Wilayah::orderBy('nama')->get()->map(function ($w) use ($now) {
            $petugasIds = Petugas::where('wilayah_id', $w->id)->pluck('user_id');
            $bulanIni   = $now->copy();
            return [
                'label'     => $w->nama,
                'hadir'     => Absensi::whereIn('user_id', $petugasIds)
                    ->whereYear('tanggal', $bulanIni->year)->whereMonth('tanggal', $bulanIni->month)
                    ->whereNotIn('status_kehadiran', ['tidak_hadir','alpha'])
                    ->whereNotNull('status_kehadiran')->count(),
                'laporan'   => LaporanHarianBaru::where('wilayah_id', $w->id)
                    ->whereYear('tanggal', $bulanIni->year)->whereMonth('tanggal', $bulanIni->month)
                    ->whereIn('status', ['submitted','approved'])->count(),
                'checklist' => ChecklistHarian::whereIn('user_id', $petugasIds)
                    ->whereYear('tanggal', $bulanIni->year)->whereMonth('tanggal', $bulanIni->month)
                    ->whereIn('status', ['verified','selesai'])->count(),
                'survey'    => SurveyKepuasan::where('wilayah_id', $w->id)
                    ->whereYear('diisi_pada', $bulanIni->year)->whereMonth('diisi_pada', $bulanIni->month)
                    ->where('status', 'selesai')->count(),
                'kinerja'   => round(EvaluasiPetugas::where('wilayah_id', $w->id)->avg('jumlah_nilai') ?? 0, 1),
            ];
        })->values()->toArray();

        // Data mingguan (12 minggu terakhir)
        $mingguan = [];
        for ($i = 11; $i >= 0; $i--) {
            $mulai = $now->copy()->startOfWeek()->subWeeks($i);
            $akhir = $mulai->copy()->endOfWeek();
            $mingguan[] = [
                'label'     => $mulai->format('d/m') . '–' . $akhir->format('d/m'),
                'hadir'     => Absensi::whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
                    ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
                    ->whereNotNull('status_kehadiran')->count(),
                'laporan'   => LaporanHarianBaru::whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
                    ->whereIn('status', ['submitted', 'approved'])->count(),
                'checklist' => ChecklistHarian::whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
                    ->whereIn('status', ['verified', 'selesai'])->count(),
                'survey'    => SurveyKepuasan::whereBetween('diisi_pada', [$mulai->toDateString(), $akhir->toDateString()])
                    ->where('status', 'selesai')->count(),
                'kinerja'   => round(EvaluasiPetugas::whereBetween('tanggal_evaluasi', [$mulai->toDateString(), $akhir->toDateString()])->avg('jumlah_nilai') ?? 0, 1),
            ];
        }

        // Data harian (30 hari terakhir)
        $harian = [];
        for ($i = 29; $i >= 0; $i--) {
            $tgl = $now->copy()->subDays($i)->toDateString();
            $harian[] = [
                'label'     => Carbon::parse($tgl)->format('d/m'),
                'hadir'     => Absensi::whereDate('tanggal', $tgl)
                    ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
                    ->whereNotNull('status_kehadiran')->count(),
                'laporan'   => LaporanHarianBaru::whereDate('tanggal', $tgl)
                    ->whereIn('status', ['submitted', 'approved'])->count(),
                'checklist' => ChecklistHarian::whereDate('tanggal', $tgl)
                    ->whereIn('status', ['verified', 'selesai'])->count(),
                'survey'    => SurveyKepuasan::whereDate('diisi_pada', $tgl)
                    ->where('status', 'selesai')->count(),
                'kinerja'   => round(EvaluasiPetugas::whereDate('tanggal_evaluasi', $tgl)->avg('jumlah_nilai') ?? 0, 1),
            ];
        }

        // Data bulanan (12 bulan terakhir)
        $bulanan = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = $now->copy()->subMonths($i);
            $bulanan[] = [
                'label'     => $bulan->isoFormat('MMM YY'),
                'hadir'     => Absensi::whereYear('tanggal', $bulan->year)->whereMonth('tanggal', $bulan->month)
                    ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
                    ->whereNotNull('status_kehadiran')->count(),
                'laporan'   => LaporanHarianBaru::whereYear('tanggal', $bulan->year)->whereMonth('tanggal', $bulan->month)
                    ->whereIn('status', ['submitted', 'approved'])->count(),
                'checklist' => ChecklistHarian::whereYear('tanggal', $bulan->year)->whereMonth('tanggal', $bulan->month)
                    ->whereIn('status', ['verified', 'selesai'])->count(),
                'survey'    => SurveyKepuasan::whereYear('diisi_pada', $bulan->year)->whereMonth('diisi_pada', $bulan->month)
                    ->where('status', 'selesai')->count(),
                'kinerja'   => round(EvaluasiPetugas::whereYear('tanggal_evaluasi', $bulan->year)->whereMonth('tanggal_evaluasi', $bulan->month)->avg('jumlah_nilai') ?? 0, 1),
            ];
        }

        // Data tahunan (5 tahun terakhir)
        $tahunan = [];
        for ($i = 4; $i >= 0; $i--) {
            $tahun = $now->year - $i;
            $tahunan[] = [
                'label'     => (string)$tahun,
                'hadir'     => Absensi::whereYear('tanggal', $tahun)
                    ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
                    ->whereNotNull('status_kehadiran')->count(),
                'laporan'   => LaporanHarianBaru::whereYear('tanggal', $tahun)
                    ->whereIn('status', ['submitted', 'approved'])->count(),
                'checklist' => ChecklistHarian::whereYear('tanggal', $tahun)
                    ->whereIn('status', ['verified', 'selesai'])->count(),
                'survey'    => SurveyKepuasan::whereYear('diisi_pada', $tahun)
                    ->where('status', 'selesai')->count(),
                'kinerja'   => round(EvaluasiPetugas::whereYear('tanggal_evaluasi', $tahun)->avg('jumlah_nilai') ?? 0, 1),
            ];
        }

        // ══════════════════════════════════════════
        // 9. LAYANAN HARI INI (dari laporan harian)
        // ══════════════════════════════════════════
        $kunjunganLangsung = 0;
        $kunjunganRemote   = 0;
        $laporanHariIniData = LaporanHarianBaru::whereDate('tanggal', $today)
            ->whereIn('status', ['approved', 'submitted'])->get();
        foreach ($laporanHariIniData as $l) {
            $jawaban = is_array($l->jawaban) ? $l->jawaban : [];
            if (!empty($jawaban[1])) $kunjunganLangsung += max(0, (int)filter_var($jawaban[1], FILTER_SANITIZE_NUMBER_INT));
            if (!empty($jawaban[2])) $kunjunganRemote   += max(0, (int)filter_var($jawaban[2], FILTER_SANITIZE_NUMBER_INT));
        }
        $totalLayanan = $kunjunganLangsung + $kunjunganRemote;

        // ══════════════════════════════════════════
        // 10. NOTIFIKASI AKTIVITAS TERBARU
        // ══════════════════════════════════════════
        $aktivitasTerbaru = collect();

        LaporanHarianBaru::where('status', 'submitted')
            ->latest('updated_at')->limit(3)->get()
            ->each(fn($l) => $aktivitasTerbaru->push((object)[
                'type' => 'grn',
                'icon' => 'laporan',
                'text' => '<strong>' . e($l->nama_petugas ?? 'Petugas') . '</strong> mengirim laporan harian',
                'time' => Carbon::parse($l->updated_at)->diffForHumans(),
            ]));

        Absensi::with('user')->whereDate('tanggal', $today)
            ->whereNotIn('status_kehadiran', ['tidak_hadir', 'alpha'])
            ->whereNotNull('status_kehadiran')
            ->latest('created_at')->limit(3)->get()
            ->each(fn($a) => $aktivitasTerbaru->push((object)[
                'type' => 'blu',
                'icon' => 'absensi',
                'text' => '<strong>' . e($a->user->name ?? 'Petugas') . '</strong> absen masuk hari ini',
                'time' => Carbon::parse($a->created_at)->diffForHumans(),
            ]));

        ChecklistHarian::with('user')->whereDate('tanggal', $today)
            ->latest('updated_at')->limit(2)->get()
            ->each(fn($c) => $aktivitasTerbaru->push((object)[
                'type' => 'amb',
                'icon' => 'checklist',
                'text' => '<strong>' . e($c->user->name ?? 'Petugas') . '</strong> mengisi checklist harian',
                'time' => Carbon::parse($c->updated_at)->diffForHumans(),
            ]));

        SurveyKepuasan::where('status', 'selesai')
            ->latest('diisi_pada')->limit(2)->get()
            ->each(fn($s) => $aktivitasTerbaru->push((object)[
                'type' => 'grn',
                'icon' => 'survey',
                'text' => '<strong>Survei kepuasan</strong> baru dari ' . e($s->nama_responden ?? 'pengunjung'),
                'time' => Carbon::parse($s->diisi_pada)->diffForHumans(),
            ]));

        $aktivitasTerbaru = $aktivitasTerbaru->sortByDesc(fn($a) => $a->time)->take(8)->values();

        return view('admin.dashboard', compact(
            // Petugas
            'totalPetugas', 'tambahPetugas',
            // Absensi
            'hadirHariIni', 'pctHadir', 'hadirMingguIni', 'hadirBulanIni', 'absensiStatus', 'belumAbsenHariIni',
            'totalTerjadwalHariIni',
            // Jadwal
            'jadwalHariIni',
            // Checklist
            'checklistTotal', 'checklistSelesai', 'checklistBelum',
            'totalItemChecked', 'totalItemAll', 'pctChecklistItem',
            // Laporan
            'laporanHariIni', 'laporanBulanIni', 'laporanPending', 'laporanApproved', 'pctLaporan',
            'laporanPendingList',
            // Layanan
            'kunjunganLangsung', 'kunjunganRemote', 'totalLayanan',
            // Survei
            'surveyTotal', 'surveyBulanIni', 'avgRating', 'ratingDistribusi',
            // Kinerja
            'avgKinerja', 'deltaKinerja', 'kinerjaWilayah', 'gradeDistribusi', 'topPerformer',
            // Wilayah
            'wilayahList',
            // Grafik
            'harian', 'mingguan', 'bulanan', 'tahunan', 'perWilayah',
            // Aktivitas
            'aktivitasTerbaru'
        ));
    }
}