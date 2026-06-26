<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiPetugas;
use App\Models\Petugas;
use App\Models\SurveyKepuasan;
use App\Models\SurveyPertanyaan;
use App\Models\User;
use App\Models\WilayahSurveyToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * KoordinatorSurveyController
 *
 * Koordinator hanya bisa:
 *   1. Melihat rekap survey wilayahnya (index)
 *   2. Melihat detail per petugas (detail)
 *   3. Sinkron nilai survey ke evaluasi (sinkronEvaluasi)
 *   4. Polling real-time (polling)
 *
 * Cetak barcode & link online → dipindahkan ke PetugasSurveyController
 * Menu Survey di sidebar koordinator → dihapus (tidak ditampilkan)
 *
 * Letak file : app/Http/Controllers/KoordinatorSurveyController.php
 * Status     : FILE LAMA — diperbarui (hapus cetakBarcode & getLinkOnline)
 */
class KoordinatorSurveyController extends Controller
{
    private function wilayahId(): int
    {
        return (int) Auth::user()->wilayah_id;
    }

    private function getPetugasWilayah()
    {
        return User::where('wilayah_id', $this->wilayahId())
            ->where('role', 'petugas')
            ->where('is_active', true)
            ->with('petugas')
            ->get();
    }

    // ── INDEX — rekap survey wilayah ───────────────────────────────

    public function index(Request $request)
    {
        $periode    = $request->input('periode', now()->format('Y-m'));
        $allPetugas = $this->getPetugasWilayah();
        $wilayahId  = $this->wilayahId();

        $surveyMap = SurveyKepuasan::where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->with('jawaban.pertanyaan')
            ->get()
            ->groupBy('petugas_id');

        $surveyTanpaJadwal = $surveyMap->get(null, collect());

        $dataPetugas = $allPetugas->map(function ($user) use ($surveyMap) {
            $p = $user->petugas;
            if (!$p) return null;

            $surveys         = $surveyMap->get($p->id, collect());
            $jumlahResponden = $surveys->count();

            $allRatings = $surveys->flatMap(fn($s) => $s->jawaban)
                ->filter(fn($j) => ($j->pertanyaan?->tipe ?? '') === 'rating' && is_numeric($j->jawaban))
                ->map(fn($j) => (float) $j->jawaban);

            $rataKepuasan = $allRatings->count() ? round($allRatings->avg(), 2) : null;

            return [
                'user'             => $user,
                'petugas'          => $p,
                'jumlah_responden' => $jumlahResponden,
                'rata_kepuasan'    => $rataKepuasan,
            ];
        })->filter()->values();

        $totalResponden    = $dataPetugas->sum('jumlah_responden') + $surveyTanpaJadwal->count();
        $rataWilayahCol    = $dataPetugas->pluck('rata_kepuasan')->filter();
        $rataWilayah       = $rataWilayahCol->count() ? round($rataWilayahCol->avg(), 2) : null;
        $jumlahTanpaJadwal = $surveyTanpaJadwal->count();
        $hariIni           = SurveyKepuasan::where('wilayah_id', $wilayahId)
                                ->whereDate('created_at', now()->toDateString())
                                ->where('status', 'selesai')->count();

        $wst = WilayahSurveyToken::firstOrGenerate($wilayahId);

        return view('koordinator.survey.index', compact(
            'dataPetugas', 'periode', 'totalResponden', 'rataWilayah',
            'jumlahTanpaJadwal', 'wst', 'hariIni'
        ));
    }

    // ── DETAIL — detail survey per petugas ────────────────────────

    public function detail(Request $request, int $petugasId)
    {
        $periode = $request->input('periode', now()->format('Y-m'));
        $petugas = Petugas::with('user')->findOrFail($petugasId);
        abort_if($petugas->user?->wilayah_id !== $this->wilayahId(), 403);

        $surveys = SurveyKepuasan::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->with('jawaban.pertanyaan')
            ->latest()
            ->get();

        $pertanyaan = SurveyPertanyaan::where('is_active', true)->orderBy('urutan')->get();

        $rataPerPertanyaan = $pertanyaan->map(function ($p) use ($surveys) {
            if ($p->tipe !== 'rating') return ['pertanyaan' => $p, 'rata' => null, 'count' => 0];

            $vals = $surveys->flatMap(fn($s) => $s->jawaban)
                ->where('pertanyaan_id', $p->id)
                ->filter(fn($j) => is_numeric($j->jawaban))
                ->map(fn($j) => (float) $j->jawaban);

            return [
                'pertanyaan' => $p,
                'rata'       => $vals->count() ? round($vals->avg(), 2) : null,
                'count'      => $vals->count(),
            ];
        });

        $evaluasi = EvaluasiPetugas::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->first();

        return view('koordinator.survey.detail', compact(
            'petugas', 'surveys', 'pertanyaan', 'rataPerPertanyaan', 'periode', 'evaluasi'
        ));
    }

    // ── SINKRON EVALUASI ───────────────────────────────────────────

    public function sinkronEvaluasi(Request $request, int $petugasId)
    {
        $request->validate(['periode' => 'required|regex:/^\d{4}-\d{2}$/']);
        $periode = $request->periode;
        $petugas = Petugas::with('user')->findOrFail($petugasId);
        abort_if($petugas->user?->wilayah_id !== $this->wilayahId(), 403);

        $surveys = SurveyKepuasan::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->with('jawaban.pertanyaan')
            ->get();

        $allRatings = $surveys->flatMap(fn($s) => $s->jawaban)
            ->filter(fn($j) => ($j->pertanyaan?->tipe ?? '') === 'rating' && is_numeric($j->jawaban))
            ->map(fn($j) => (float) $j->jawaban);

        abort_if($allRatings->isEmpty(), 422, 'Belum ada data survey untuk periode ini.');

        $nilaiKepuasan = round(($allRatings->avg() / 5) * 100, 2);

        $evaluasi = EvaluasiPetugas::firstOrCreate(
            ['petugas_id' => $petugasId, 'periode' => $periode, 'tipe_periode' => 'bulanan'],
            [
                'koordinator_id'   => Auth::id(),
                'wilayah_id'       => $this->wilayahId(),
                'tanggal_evaluasi' => now()->toDateString(),
            ]
        );

        $evaluasi->update(['nilai_kepuasan_pelanggan' => $nilaiKepuasan]);
        $evaluasi->hitungKomposit();
        $evaluasi->save();

        return back()->with('success', "Nilai kepuasan ({$nilaiKepuasan}) berhasil disinkronkan ke evaluasi.");
    }

    // ── POLLING ────────────────────────────────────────────────────

    public function polling(Request $request)
    {
        $wilayahId = $this->wilayahId();
        $after     = (int) $request->input('after', 0);
        $today     = now()->toDateString();

        $newSurveys = SurveyKepuasan::with(['petugas.user'])
            ->where('wilayah_id', $wilayahId)
            ->whereDate('created_at', $today)
            ->where('status', 'selesai')
            ->where('id', '>', $after)
            ->orderByDesc('id')
            ->limit(15)
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'nama_responden' => $s->nama_responden ?: 'Anonim',
                'petugas'        => optional(optional($s->petugas)->user)->name ?? '—',
                'status'         => $s->status,
                'diisi_pada'     => $s->diisi_pada ? $s->diisi_pada->format('H:i') : '—',
                'rata_rating'    => $s->rataRating(),
            ]);

        $maxId     = $newSurveys->max('id') ?? $after;
        $baseToday = SurveyKepuasan::where('wilayah_id', $wilayahId)
                        ->whereDate('created_at', $today)->where('status', 'selesai');
        $rataToday = SurveyKepuasan::where('wilayah_id', $wilayahId)
                        ->whereDate('created_at', $today)->where('status', 'selesai')
                        ->get()->map(fn($s) => $s->rataRating())->filter()->avg();
        $totalSemua = SurveyKepuasan::where('wilayah_id', $wilayahId)->where('status', 'selesai')->count();

        return response()->json([
            'new_surveys' => $newSurveys,
            'max_id'      => $maxId,
            'stats'       => [
                'hari_ini'         => (clone $baseToday)->count(),
                'selesai_hari_ini' => (clone $baseToday)->count(),
                'rata_hari_ini'    => $rataToday ? round($rataToday, 2) : null,
                'total_semua'      => $totalSemua,
            ],
        ]);
    }

    // ══════════════════════════════════════════════════════════════
    // SURVEY INTERNAL (read-only) — monitoring penilaian antar-rekan
    // Hanya untuk wilayah koordinator yang login. Tidak ada aksi tulis.
    // ══════════════════════════════════════════════════════════════

    // ── INDEX — rekap survey internal wilayah ───────────────────────

    public function internalHasilIndex(Request $request)
    {
        $periode = $request->input('periode', \App\Helpers\SurveyInternalHelper::periodeTriwulanSekarang());
        $search  = $request->input('search', '');
        $wilayahId = $this->wilayahId();

        $wilayah = \App\Models\Wilayah::with('petugas.user')->findOrFail($wilayahId);

        $surveyMap = SurveyKepuasan::where('jenis', 'internal')
            ->where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->with('jawaban.pertanyaan')
            ->get()
            ->groupBy('petugas_id');

        $petugasList = $wilayah->petugas->filter(function ($p) use ($search) {
            if (!$p->user) return false;
            if (!$p->user->is_active) return false;
            if ($search && stripos($p->user->name, $search) === false) return false;
            return true;
        })->map(function ($p) use ($surveyMap) {
            $surveys       = $surveyMap->get($p->id, collect());
            $jumlahPenilai = $surveys->count();

            $allRatings = $surveys->flatMap(fn($s) => $s->jawaban)
                ->filter(fn($j) => $j->pertanyaan?->tipe === 'rating' && is_numeric($j->jawaban))
                ->map(fn($j) => (float) $j->jawaban);

            $rataKepuasan = $allRatings->count() ? round($allRatings->avg(), 2) : null;

            return [
                'user'           => $p->user,
                'petugas'        => $p,
                'jumlah_penilai' => $jumlahPenilai,
                'rata_kepuasan'  => $rataKepuasan,
            ];
        })->values();

        $totalSurvey = SurveyKepuasan::where('jenis', 'internal')
            ->where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->count();

        $rataWilayah = $petugasList->pluck('rata_kepuasan')->filter();
        $rataWilayah = $rataWilayah->count() ? round($rataWilayah->avg(), 2) : null;

        $periodeList = SurveyKepuasan::where('jenis', 'internal')
            ->where('wilayah_id', $wilayahId)
            ->distinct()
            ->orderByDesc('periode')
            ->pluck('periode');

        return view('koordinator.survey.internal-hasil', compact(
            'wilayah', 'petugasList', 'totalSurvey', 'rataWilayah', 'periode', 'periodeList', 'search'
        ));
    }

    // ── DETAIL — breakdown per pertanyaan untuk satu petugas ─────────

    public function internalHasilDetail(Request $request, int $petugasId)
    {
        $periode   = $request->input('periode', \App\Helpers\SurveyInternalHelper::periodeTriwulanSekarang());
        $wilayahId = $this->wilayahId();

        // Pastikan petugas yang diminta memang milik wilayah koordinator ini
        $petugas = Petugas::with('user')
            ->where('wilayah_id', $wilayahId)
            ->findOrFail($petugasId);

        $surveys = SurveyKepuasan::where('petugas_id', $petugasId)
            ->where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->where('jenis', 'internal')
            ->with('jawaban.pertanyaan')
            ->latest()
            ->get();

        $pertanyaan = SurveyPertanyaan::untukInternal()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $rataPerPertanyaan = $pertanyaan->map(function ($p) use ($surveys) {
            if ($p->tipe !== 'rating') return ['pertanyaan' => $p, 'rata' => null, 'count' => 0];

            $vals = $surveys->flatMap(fn($s) => $s->jawaban)
                ->where('pertanyaan_id', $p->id)
                ->filter(fn($j) => is_numeric($j->jawaban))
                ->map(fn($j) => (float) $j->jawaban);

            return [
                'pertanyaan' => $p,
                'rata'       => $vals->count() ? round($vals->avg(), 2) : null,
                'count'      => $vals->count(),
            ];
        });

        return view('koordinator.survey.internal-hasil-detail', compact(
            'petugas', 'surveys', 'pertanyaan', 'rataPerPertanyaan', 'periode'
        ));
    }
}