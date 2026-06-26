<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\DashboardKoordinatorController;
use App\Http\Controllers\JadwalPetugasController;
use App\Http\Controllers\AdminJadwalController;
use App\Http\Controllers\PetugasDashboardController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\ReviewLaporanController;
use App\Http\Controllers\KoordinatorMateriController;
use App\Http\Controllers\PetugasMateriController;
use App\Http\Controllers\ChecklistHarianController;
use App\Http\Controllers\LaporanHarianBaruController;
use App\Http\Controllers\AbsensiKoordinatorController;
use App\Http\Controllers\PetugasAbsensiController;
use App\Http\Controllers\AbsensiAdminController;
use App\Http\Controllers\AdminSurveyController;
use App\Http\Controllers\KategoriPenilaianController;
use App\Http\Controllers\KoordinatorSurveyController;
use App\Http\Controllers\SurveyPublikController;
use App\Http\Controllers\SurveyInternalController; // ← BARU
use App\Http\Controllers\PetugasSurveyController;
use App\Http\Controllers\SidebarBadgeController;
use App\Http\Controllers\KoordinatorMateriTriwulanController;
use App\Http\Controllers\PetugasMateriTriwulanController;
use App\Http\Controllers\EvaluasiPetugasPdfController;
use App\Models\Tugas;
use App\Models\Wilayah;

Route::get('/', function () { return redirect('/login'); });

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/login-redirect', function () {
    if (!session('login_success')) {
        return redirect('/login');
    }
    return view('auth.redirect');
})->name('login.redirect');

Route::get('/admin/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard');

Route::get('/admin/pengaturan', function () {
    return view('admin.pengaturan');
})->name('admin.pengaturan')->middleware('auth');

Route::post('/admin/pengaturan/profil', [AuthController::class, 'updateProfil'])
    ->name('admin.updateProfil')
    ->middleware('auth');

Route::post('/admin/pengaturan/password', [AuthController::class, 'updatePassword'])
    ->name('admin.updatePassword')
    ->middleware('auth');

Route::post('/admin/petugas/store', [PetugasController::class, 'store'])
    ->middleware('auth')
    ->name('petugas.store');

// ── Wilayah CRUD ───────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/admin/tim-petugas', [WilayahController::class, 'index'])
        ->name('admin.tim-petugas');

    Route::post('/admin/wilayah', [WilayahController::class, 'store'])
        ->name('admin.wilayah.store');

    Route::put('/admin/wilayah/{wilayah}', [WilayahController::class, 'update'])
        ->name('admin.wilayah.update');

    Route::delete('/admin/wilayah/{wilayah}', [WilayahController::class, 'destroy'])
        ->name('admin.wilayah.destroy');

    Route::get('/admin/tim-petugas/{wilayah}', function (\App\Models\Wilayah $wilayah) {
        $petugas = $wilayah->petugas()->with('user')->get();
        return view('admin.tim_petugas_detail', compact('wilayah', 'petugas'));
    })->name('admin.tim-petugas.detail');

    Route::delete('/admin/petugas/{petugas}', [PetugasController::class, 'destroy'])
        ->name('petugas.destroy');

    Route::put('/admin/petugas/{petugas}', [PetugasController::class, 'update'])
        ->name('petugas.update');

    Route::patch('/admin/petugas/{petugas}/toggle-status', [PetugasController::class, 'toggleStatus'])
        ->name('petugas.toggle-status');

    Route::get('/petugas/jadwal', [PetugasDashboardController::class, 'jadwal'])
        ->name('petugas.jadwal');

    Route::get('/petugas/dashboard', [PetugasDashboardController::class, 'index'])
        ->name('petugas.dashboard');
});

Route::middleware('auth')->group(function () {

    Route::get('/admin/tugas/{id}', [TugasController::class, 'show'])->name('admin.tugas.show');
    Route::get('/admin/tugas/{id}/edit', [TugasController::class, 'edit'])->name('admin.tugas.edit');
    Route::put('/admin/tugas/{id}', [TugasController::class, 'update'])->name('admin.tugas.update');
    Route::delete('/admin/tugas/{id}', [TugasController::class, 'destroy'])->name('admin.tugas.destroy');
});

// ── Koordinator ────────────────────────────────────────
Route::get('/koordinator/dashboard', [DashboardKoordinatorController::class, 'index'])
    ->name('koordinator.dashboard')
    ->middleware('auth');

Route::get('/koordinator/tim-petugas', [DashboardKoordinatorController::class, 'timPetugas'])
    ->name('koordinator.tim-petugas')
    ->middleware('auth');

Route::get('/koordinator/jadwal', [JadwalPetugasController::class, 'index'])->name('jadwal.index');
Route::get('/koordinator/jadwal/create', [JadwalPetugasController::class, 'create'])->name('jadwal.create');
Route::post('/koordinator/jadwal/store', [JadwalPetugasController::class, 'store'])->name('jadwal.store');

Route::middleware('auth')->group(function () {

    Route::get('/admin/jadwal', [AdminJadwalController::class, 'index'])
        ->name('admin.jadwal.index');

    Route::post('/admin/jadwal/store', [AdminJadwalController::class, 'store'])
        ->name('admin.jadwal.store');

    Route::get('/admin/materi', function (\Illuminate\Http\Request $request) {
        $tugas   = Tugas::with('files')->latest()->get();
        $wilayah = Wilayah::with('petugas')->get();
        $materiTriwulanOpen = \App\Models\SurveySetting::get('materi_triwulan_open', 'false') === 'true';

        $periodeOptions = [];
        $tahunAwal = 2026;
        $tahunAkhir = now()->year + 1;
        for ($y = $tahunAwal; $y <= $tahunAkhir; $y++) {
            for ($tw = 1; $tw <= 4; $tw++) {
                $periodeOptions["{$y}-TW{$tw}"] = "Triwulan {$tw} Tahun {$y}";
            }
        }

        $periodeTriwulanFilter = $request->input('periode_triwulan');
        $monitoringTriwulan = TugasController::dataMonitoringTriwulan($periodeTriwulanFilter);

        return view('admin.materi.index', compact(
            'tugas', 'wilayah', 'materiTriwulanOpen',
            'periodeOptions', 'monitoringTriwulan'
        ));
    })->name('admin.materi')->middleware('auth');

    Route::get('/admin/materi/polling', [TugasController::class, 'pollingMateri'])
        ->name('admin.materi.polling');

    Route::get('/admin/materi/monitoring/{wilayah}', [TugasController::class, 'monitoringDetail'])
        ->name('admin.materi.detail')
        ->middleware('auth');

    Route::get('/admin/materi/create-tugas', function () {
        return view('admin.materi.create_tugas');
    })->name('admin.materi.create-tugas');

    Route::post('/admin/tugas/store', [TugasController::class, 'store'])
        ->name('admin.tugas.store')
        ->middleware('auth');
});

Route::middleware('auth')->group(function () {

    Route::get('/koordinator/laporan', [ReviewLaporanController::class, 'index'])
        ->name('koordinator.laporan.index');

    Route::get('/koordinator/laporan/{tugasId}', [ReviewLaporanController::class, 'detail'])
        ->name('koordinator.laporan.detail');

    Route::get('/koordinator/materi', [KoordinatorMateriController::class, 'index'])
        ->name('koordinator.materi.index');
    Route::get('/koordinator/materi/polling', [KoordinatorMateriController::class, 'polling'])
        ->name('koordinator.materi.polling');
});

Route::middleware('auth')->group(function () {

    Route::get('/petugas/materi', [PetugasMateriController::class, 'index'])
        ->name('petugas.materi');

    Route::get('/petugas/materi/{id}', [PetugasMateriController::class, 'show'])
        ->name('petugas.materi.show');

    Route::post('/petugas/materi/{id}/submit', [PetugasMateriController::class, 'submit'])
        ->name('petugas.materi.submit');
});

// ── Checklist Admin ────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/admin/checklist', [ChecklistHarianController::class, 'adminIndex'])
        ->name('admin.checklist.index');
    Route::get('/admin/checklist/{id}', [ChecklistHarianController::class, 'adminDetail'])
        ->name('admin.checklist.detail');
    Route::patch('/admin/checklist/{id}/verify', [ChecklistHarianController::class, 'adminVerify'])
        ->name('admin.checklist.verify');
    Route::get('/admin/checklist-template', [ChecklistHarianController::class, 'adminTemplateIndex'])
        ->name('admin.checklist.template');
    Route::get('/admin/checklist/polling', [ChecklistHarianController::class, 'adminPolling'])
        ->name('admin.checklist.polling');
    Route::post('/admin/checklist-template', [ChecklistHarianController::class, 'adminTemplateStore'])
        ->name('admin.checklist.template.store');
    Route::put('/admin/checklist-template/{id}', [ChecklistHarianController::class, 'adminTemplateUpdate'])
        ->name('admin.checklist.template.update');
    Route::delete('/admin/checklist-template/{id}', [ChecklistHarianController::class, 'adminTemplateDestroy'])
        ->name('admin.checklist.template.destroy');
    Route::post('/admin/checklist-template/reorder', [ChecklistHarianController::class, 'adminTemplateReorder'])
        ->name('admin.checklist.template.reorder');
});

// ── Checklist Koordinator ──────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/koordinator/checklist', [ChecklistHarianController::class, 'koordinatorIndex'])
        ->name('koordinator.checklist.index');
    Route::get('/koordinator/checklist/polling', [ChecklistHarianController::class, 'koordinatorPolling'])
        ->name('koordinator.checklist.polling');
    Route::get('/koordinator/checklist/{id}', [ChecklistHarianController::class, 'koordinatorDetail'])
        ->name('koordinator.checklist.detail');
    Route::patch('/koordinator/checklist/{id}/verify', [ChecklistHarianController::class, 'koordinatorVerify'])
        ->name('koordinator.checklist.verify');
    Route::get('/koordinator/checklist-template', [ChecklistHarianController::class, 'koordinatorTemplateIndex'])
        ->name('koordinator.checklist.template');
    Route::post('/koordinator/checklist-template', [ChecklistHarianController::class, 'koordinatorTemplateStore'])
        ->name('koordinator.checklist.template.store');
    Route::put('/koordinator/checklist-template/{id}', [ChecklistHarianController::class, 'koordinatorTemplateUpdate'])
        ->name('koordinator.checklist.template.update');
    Route::delete('/koordinator/checklist-template/{id}', [ChecklistHarianController::class, 'koordinatorTemplateDestroy'])
        ->name('koordinator.checklist.template.destroy');
    Route::post('/koordinator/checklist-template/reorder', [ChecklistHarianController::class, 'koordinatorTemplateReorder'])
        ->name('koordinator.checklist.template.reorder');
});

// ── Checklist Petugas ──────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/petugas/checklist', [ChecklistHarianController::class, 'petugasIndex'])
        ->name('petugas.checklist');
    Route::post('/petugas/checklist/save', [ChecklistHarianController::class, 'petugasSave'])
        ->name('petugas.checklist.save');
});

// ── Admin: Laporan Harian ──────────────────────────────
Route::prefix('admin')->middleware(['auth'])->group(function () {

    Route::get('/laporan-harian', [LaporanHarianBaruController::class, 'adminIndex'])
        ->name('admin.laporanharian.index');
    Route::get('/laporan-harian-export', [LaporanHarianBaruController::class, 'adminExport'])
        ->name('admin.laporanharian.export');
    Route::get('/laporan-harian/polling', [LaporanHarianBaruController::class, 'adminPolling'])
        ->name('admin.laporanharian.polling');
    Route::get('/laporan-harian/{id}', [LaporanHarianBaruController::class, 'adminDetail'])
        ->name('admin.laporanharian.detail');
    Route::post('/laporan-template', [LaporanHarianBaruController::class, 'adminTemplateStore'])
        ->name('admin.laporanharian.template.store');
    Route::patch('/laporan-template/{id}', [LaporanHarianBaruController::class, 'adminTemplateUpdate'])
        ->name('admin.laporanharian.template.update');
    Route::delete('/laporan-template/{id}', [LaporanHarianBaruController::class, 'adminTemplateDestroy'])
        ->name('admin.laporanharian.template.destroy');
    Route::get('/absensi', [AbsensiAdminController::class, 'index'])
        ->name('admin.absensi.index');

    Route::get('/absensi/export', [AbsensiAdminController::class, 'export'])
        ->name('admin.absensi.export');

    Route::get('/absensi/polling', [AbsensiAdminController::class, 'polling'])
        ->name('admin.absensi.polling');

    // ── Penilaian (rekap semua wilayah) ──
    Route::get('/penilaian', [\App\Http\Controllers\AdminPenilaianController::class, 'index'])
        ->name('admin.penilaian.index');

    Route::get('/penilaian/debug', [\App\Http\Controllers\AdminPenilaianController::class, 'debug'])
        ->name('admin.penilaian.debug');

    Route::get('/penilaian/export', [\App\Http\Controllers\AdminPenilaianController::class, 'export'])
        ->name('admin.penilaian.export');

    Route::get('/penilaian/export-wilayah/{wilayahId}', [\App\Http\Controllers\AdminPenilaianController::class, 'exportWilayah'])
        ->name('admin.penilaian.export.wilayah');

    Route::get('/penilaian/wilayah/{wilayahId}', [\App\Http\Controllers\AdminPenilaianController::class, 'detail'])
        ->name('admin.penilaian.detail');

    Route::post('/penilaian/selesaikan-semua', [\App\Http\Controllers\AdminPenilaianController::class, 'selesaikanSemua'])
        ->name('admin.penilaian.selesaikan-semua');

    Route::get('/penilaian/{petugasId}/pdf', [\App\Http\Controllers\EvaluasiPetugasPdfController::class, 'adminPdf'])
        ->name('admin.penilaian.pdf');
});

// ── Petugas: Laporan Harian ────────────────────────────
Route::prefix('petugas')->middleware(['auth'])->group(function () {

    Route::get('/laporan-harian', [LaporanHarianBaruController::class, 'petugasIndex'])
        ->name('petugas.laporan.harian.index');
    Route::get('/laporan-harian/create', [LaporanHarianBaruController::class, 'petugasCreate'])
        ->name('petugas.laporan.harian.create');
    Route::get('/laporan-harian/{id}', [LaporanHarianBaruController::class, 'petugasShow'])
        ->name('petugas.laporan.harian.show');
    Route::post('/laporan-harian', [LaporanHarianBaruController::class, 'petugasStore'])
        ->name('petugas.laporan.harian.store');
    Route::get('/laporan-harian/{id}/edit', [LaporanHarianBaruController::class, 'petugasEdit'])
        ->name('petugas.laporan.harian.edit');
    Route::patch('/laporan-harian/{id}', [LaporanHarianBaruController::class, 'petugasUpdate'])
        ->name('petugas.laporan.harian.update');

// ── Nilai & Penilaian ──
    Route::get('/penilaian', [\App\Http\Controllers\PetugasPenilaianController::class, 'index'])
        ->name('petugas.penilaian.index');
    Route::get('/penilaian/export', [\App\Http\Controllers\PetugasPenilaianController::class, 'export'])
        ->name('petugas.penilaian.export');
    Route::get('/penilaian/export-tahunan', [\App\Http\Controllers\PetugasPenilaianController::class, 'exportTahunan'])
        ->name('petugas.penilaian.export.tahunan');
    Route::get('/penilaian/tahun/{tahun}/pdf', [EvaluasiPetugasPdfController::class, 'petugasPdfTahunan'])
        ->where('tahun', '[0-9]{4}')
        ->name('petugas.penilaian.pdf.tahunan');
    Route::get('/penilaian/{periode}', [\App\Http\Controllers\PetugasPenilaianController::class, 'show'])
        ->name('petugas.penilaian.show');
    Route::get('/penilaian/{periode}/pdf', [EvaluasiPetugasPdfController::class, 'petugasPdf'])
    ->name('petugas.penilaian.pdf');
});
// ── Koordinator: Laporan Harian ────────────────────────
Route::prefix('koordinator')->middleware(['auth'])->group(function () {

    Route::get('/laporan-harian', [LaporanHarianBaruController::class, 'koordinatorIndex'])
        ->name('koordinator.laporan.harian.index');
    Route::get('/laporan-harian/polling', [LaporanHarianBaruController::class, 'koordinatorPolling'])
        ->name('koordinator.laporan.harian.polling');
    Route::get('/laporan-harian-export', [LaporanHarianBaruController::class, 'koordinatorExport'])
        ->name('koordinator.laporan.harian.export');
    Route::get('/laporan-harian/{id}', [LaporanHarianBaruController::class, 'koordinatorDetail'])
        ->name('koordinator.laporan.harian.detail');
    Route::patch('/laporan-harian/{id}/approve', [LaporanHarianBaruController::class, 'koordinatorApprove'])
        ->name('koordinator.laporan.harian.approve');
    Route::patch('/laporan-harian/{id}/reject', [LaporanHarianBaruController::class, 'koordinatorReject'])
        ->name('koordinator.laporan.harian.reject');
});

// ══════════════════════════════════════════════════════
// ── KOORDINATOR: ABSENSI ──────────────────────────────
// ══════════════════════════════════════════════════════
Route::prefix('koordinator')->middleware(['auth'])->group(function () {
    Route::get('/absensi', [AbsensiKoordinatorController::class, 'index'])
        ->name('koordinator.absensi.index');
    Route::patch('/absensi/{id}/verify', [AbsensiKoordinatorController::class, 'verify'])
        ->name('koordinator.absensi.verify');
    Route::get('/absensi/export', [AbsensiKoordinatorController::class, 'export'])
        ->name('koordinator.absensi.export');
    Route::patch('/absensi/bulk-verify', [AbsensiKoordinatorController::class, 'bulkVerify'])
        ->name('koordinator.absensi.bulkVerify');
    Route::get('/absensi/qr-json', [AbsensiKoordinatorController::class, 'qrJson'])
        ->name('koordinator.absensi.qrJson');
    Route::get('/absensi/polling', [AbsensiKoordinatorController::class, 'polling'])
        ->name('koordinator.absensi.polling');
});

Route::middleware('auth')->group(function () {
    Route::get('/petugas/absensi', [PetugasAbsensiController::class, 'index'])
        ->name('petugas.absensi.index');

    Route::get('/petugas/survey', [\App\Http\Controllers\PetugasSurveyController::class, 'index'])
        ->name('petugas.survey.index');
});

// ══════════════════════════════════════════════════════
// ── KOORDINATOR: NILAI & EVALUASI ─────────────────────
// ══════════════════════════════════════════════════════
Route::prefix('koordinator')->middleware(['auth'])->group(function () {
    Route::get('/nilai-evaluasi', [\App\Http\Controllers\NilaiEvaluasiController::class, 'index'])
        ->name('koordinator.nilai-evaluasi.index');

    Route::get('/nilai-evaluasi/{petugasId}/form', [\App\Http\Controllers\NilaiEvaluasiController::class, 'formEvaluasi'])
        ->name('koordinator.nilai-evaluasi.form');

    Route::put('/nilai-evaluasi/{petugasId}/simpan', [\App\Http\Controllers\NilaiEvaluasiController::class, 'simpanEvaluasi'])
        ->name('koordinator.nilai-evaluasi.simpan');

    Route::get('/nilai-evaluasi/{petugasId}/detail', [\App\Http\Controllers\NilaiEvaluasiController::class, 'detail'])
        ->name('koordinator.nilai-evaluasi.detail');

    Route::get('/nilai-evaluasi/export', [\App\Http\Controllers\KoordinatorPenilaianController::class, 'export'])
        ->name('koordinator.nilai-evaluasi.export');

    Route::get('/nilai-evaluasi/export-tahunan', [\App\Http\Controllers\KoordinatorPenilaianController::class, 'exportTahunan'])
        ->name('koordinator.nilai-evaluasi.export.tahunan');

    Route::post('/nilai-evaluasi/selesaikan-semua', [\App\Http\Controllers\NilaiEvaluasiController::class, 'selesaikanSemua'])
        ->name('koordinator.nilai-evaluasi.selesaikan-semua');

    Route::get('/nilai-evaluasi/{petugasId}/pdf', [\App\Http\Controllers\EvaluasiPetugasPdfController::class, 'koordinatorPdf'])
        ->name('koordinator.nilai-evaluasi.pdf');
});

// ══════════════════════════════════════════════════════
// ── SURVEY PUBLIK (tanpa auth) ────────────────────────
// ══════════════════════════════════════════════════════
Route::get('/survey/barcode/{tokenBarcode}', [SurveyPublikController::class, 'showBarcode'])
    ->name('survey.barcode');
Route::get('/survey/link/{tokenLink}', [SurveyPublikController::class, 'showLink'])
    ->name('survey.link');
Route::get('/survey/{token}', [SurveyPublikController::class, 'show'])
    ->name('survey.publik');
Route::post('/survey/{token}', [SurveyPublikController::class, 'submit'])
    ->name('survey.submit');

// ══════════════════════════════════════════════════════
// ── ADMIN: SURVEY EKSTERNAL ───────────────────────────
// ══════════════════════════════════════════════════════
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/survey/pertanyaan', [AdminSurveyController::class, 'pertanyaanIndex'])
        ->name('admin.survey.pertanyaan');
    Route::post('/survey/pertanyaan', [AdminSurveyController::class, 'pertanyaanStore'])
        ->name('admin.survey.pertanyaan.store');
    Route::put('/survey/pertanyaan/{id}', [AdminSurveyController::class, 'pertanyaanUpdate'])
        ->name('admin.survey.pertanyaan.update');
    Route::delete('/survey/pertanyaan/{id}', [AdminSurveyController::class, 'pertanyaanDestroy'])
        ->name('admin.survey.pertanyaan.destroy');
    Route::patch('/survey/pertanyaan/{id}/toggle', [AdminSurveyController::class, 'pertanyaanToggle'])
        ->name('admin.survey.pertanyaan.toggle');
    Route::post('/survey/pertanyaan/reorder', [AdminSurveyController::class, 'pertanyaanReorder'])
        ->name('admin.survey.pertanyaan.reorder');
    Route::get('/survey/hasil', [AdminSurveyController::class, 'hasilIndex'])
        ->name('admin.survey.hasil');
    Route::get('/survey/hasil/{petugasId}', [AdminSurveyController::class, 'hasilDetail'])
        ->name('admin.survey.hasil.detail');
    Route::get('/survey/polling', [AdminSurveyController::class, 'polling'])
        ->name('admin.survey.polling');
    Route::get('/survey/template', [AdminSurveyController::class, 'templateIndex'])
        ->name('admin.survey.template');
    Route::post('/survey/template/simpan', [AdminSurveyController::class, 'templateSimpan'])
        ->name('admin.survey.template.simpan');
    Route::post('/survey/generate-token/{wilayahId}', [AdminSurveyController::class, 'generateToken'])
        ->name('admin.survey.generate-token');

    // ══ SURVEY INTERNAL — Pengaturan & Rekap ══════════════════
    Route::post('/admin/materi-triwulan/toggle', [AdminSurveyController::class, 'materiTriwulanToggle'])
        ->name('admin.materi-triwulan.toggle');

    Route::post('/survey/internal/setting', [AdminSurveyController::class, 'internalSetting'])
        ->name('admin.survey.internal.setting');
    Route::post('/survey/internal/toggle-override', [AdminSurveyController::class, 'internalToggleOverride'])
        ->name('admin.survey.internal.toggle-override');
    Route::get('/survey/internal/hasil', [AdminSurveyController::class, 'internalHasilIndex'])
        ->name('admin.survey.internal.hasil');
    Route::get('/survey/internal/hasil/{petugasId}', [AdminSurveyController::class, 'internalHasilDetail'])
        ->name('admin.survey.internal.hasil.detail');

    // ══ KATEGORI PENILAIAN — kelola kategori dinamis + mapping komponen ══
    Route::get('/kategori-penilaian', [KategoriPenilaianController::class, 'index'])
        ->name('admin.kategori-penilaian');
    Route::post('/kategori-penilaian', [KategoriPenilaianController::class, 'store'])
        ->name('admin.kategori-penilaian.store');
    Route::put('/kategori-penilaian/{id}', [KategoriPenilaianController::class, 'update'])
        ->name('admin.kategori-penilaian.update');
    Route::patch('/kategori-penilaian/{id}/toggle', [KategoriPenilaianController::class, 'toggle'])
        ->name('admin.kategori-penilaian.toggle');
    Route::delete('/kategori-penilaian/{id}', [KategoriPenilaianController::class, 'destroy'])
        ->name('admin.kategori-penilaian.destroy');
});

// ══════════════════════════════════════════════════════
// ── KOORDINATOR: SURVEY ───────────────────────────────
// ══════════════════════════════════════════════════════
Route::prefix('koordinator')->middleware(['auth'])->group(function () {
    Route::get('/survey', [KoordinatorSurveyController::class, 'index'])
        ->name('koordinator.survey.index');
    Route::get('/survey/polling', [KoordinatorSurveyController::class, 'polling'])
        ->name('koordinator.survey.polling');
    Route::get('/survey/{petugasId}', [KoordinatorSurveyController::class, 'detail'])
        ->name('koordinator.survey.detail');
    Route::post('/survey/{petugasId}/sinkron', [KoordinatorSurveyController::class, 'sinkronEvaluasi'])
        ->name('koordinator.survey.sinkron');

    // Survey Internal — monitoring read-only (wilayah koordinator)
    Route::get('/survey-internal/hasil', [KoordinatorSurveyController::class, 'internalHasilIndex'])
        ->name('koordinator.survey-internal.hasil');
    Route::get('/survey-internal/hasil/{petugasId}', [KoordinatorSurveyController::class, 'internalHasilDetail'])
        ->name('koordinator.survey-internal.hasil.detail');


});

// ══════════════════════════════════════════════════════
// ── PETUGAS: SURVEY INTERNAL + CETAK BARCODE ──────────
// ══════════════════════════════════════════════════════
Route::prefix('petugas')->middleware(['auth'])->group(function () {

    // Cetak / unduh barcode QR wilayah (dipindahkan dari koordinator)
    Route::get('/survey/cetak-barcode', [PetugasSurveyController::class, 'cetakBarcode'])
        ->name('petugas.survey.cetak-barcode');

    // Survey Internal — daftar rekan
    Route::get('/survey-internal', [SurveyInternalController::class, 'index'])
        ->name('petugas.survey-internal.index');

    // Survey Internal — form penilaian
    Route::get('/survey-internal/{petugasId}/form', [SurveyInternalController::class, 'form'])
        ->name('petugas.survey-internal.form');

    // Survey Internal — submit
    Route::post('/survey-internal/{petugasId}/submit', [SurveyInternalController::class, 'submit'])
        ->name('petugas.survey-internal.submit');
});

// ══════════════════════════════════════════════════════
// ── SIDEBAR BADGE – realtime badge counts (polling)
// ══════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {
    Route::get('/api/sidebar-badges/admin',       [SidebarBadgeController::class, 'admin'])
        ->name('sidebar.badges.admin');
    Route::get('/api/sidebar-badges/koordinator', [SidebarBadgeController::class, 'koordinator'])
        ->name('sidebar.badges.koordinator');
    Route::get('/api/sidebar-badges/petugas',     [SidebarBadgeController::class, 'petugas'])
        ->name('sidebar.badges.petugas');
});

// ══════════════════════════════════════════════════════
// ── Koordinator & Petugas: Materi & Quiz Triwulan ─────
// (FIX: sebelumnya route-route ini berada di luar middleware
//  'auth' sehingga bisa diakses tanpa login — sekarang dibungkus)
// ══════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {
    // ── Koordinator: Materi & Quiz Triwulan ─────────────────────────────────
    Route::get('/koordinator/materi/triwulan', [KoordinatorMateriTriwulanController::class, 'index'])
        ->name('koordinator.materi.triwulan');
    Route::get('/koordinator/materi/triwulan/create', [KoordinatorMateriTriwulanController::class, 'create'])
        ->name('koordinator.materi.triwulan.create');
    Route::post('/koordinator/materi/triwulan', [KoordinatorMateriTriwulanController::class, 'store'])
        ->name('koordinator.materi.triwulan.store');
    Route::get('/koordinator/materi/triwulan/{id}/edit', [KoordinatorMateriTriwulanController::class, 'edit'])
        ->name('koordinator.materi.triwulan.edit');
    Route::put('/koordinator/materi/triwulan/{id}', [KoordinatorMateriTriwulanController::class, 'update'])
        ->name('koordinator.materi.triwulan.update');
    Route::delete('/koordinator/materi/triwulan/{id}', [KoordinatorMateriTriwulanController::class, 'destroy'])
        ->name('koordinator.materi.triwulan.destroy');
    Route::patch('/koordinator/materi/triwulan/{id}/toggle-open', [KoordinatorMateriTriwulanController::class, 'toggleOpen'])
        ->name('koordinator.materi.triwulan.toggle-open');

    // ── Petugas: Quiz Triwulan ──────────────────────────────────────────────
    Route::get('/petugas/materi/triwulan/{id}', [PetugasMateriTriwulanController::class, 'show'])
        ->name('petugas.materi.triwulan.show');
    Route::post('/petugas/materi/triwulan/{id}/submit', [PetugasMateriTriwulanController::class, 'submit'])
        ->name('petugas.materi.triwulan.submit');
});