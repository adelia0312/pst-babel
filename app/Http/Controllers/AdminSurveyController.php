<?php

namespace App\Http\Controllers;

use App\Helpers\SurveyInternalHelper;
use App\Models\JadwalPetugas;
use App\Models\Petugas;
use App\Models\SurveyJawaban;
use App\Models\SurveyKepuasan;
use App\Models\SurveyPertanyaan;
use App\Models\SurveySetting;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\WilayahSurveyToken;
use Illuminate\Http\Request;

/**
 * AdminSurveyController
 *
 * Admin dapat:
 *   1. Mengelola pertanyaan survey (CRUD + toggle aktif + reorder) — per jenis
 *   2. Melihat rekap hasil survey semua wilayah
 *   3. Melihat detail per petugas
 *   4. Mengelola template pesan + lihat link semua wilayah
 *   5. [BARU] Mengelola pengaturan periode Survey Internal
 *   6. [BARU] Toggle override buka/tutup Survey Internal
 *   7. [BARU] Melihat rekap hasil Survey Internal
 *
 * Letak file: app/Http/Controllers/AdminSurveyController.php
 * Status     : FILE LAMA — diperbarui (tambah section Survey Internal)
 */
class AdminSurveyController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // PERTANYAAN — kelola daftar pertanyaan (dengan filter jenis)
    // ══════════════════════════════════════════════════════════════

    public function pertanyaanIndex()
    {
        $pertanyaan = SurveyPertanyaan::orderBy('urutan')->get();

        $wilayahList   = Wilayah::where('status', 'aktif')->get();
        $jadwalHariIni = [];

        foreach ($wilayahList as $w) {
            $jadwal = JadwalPetugas::where('wilayah_id', $w->id)
                ->where('tanggal', today()->toDateString())
                ->with('user')
                ->get();

            $jadwalHariIni[$w->id] = [
                'wilayah' => $w,
                'petugas' => $jadwal->map(fn($j) => [
                    'nama'  => $j->user?->name ?? '-',
                    'shift' => $j->shift,
                ]),
            ];
        }

        // ── BARU: setting Survey Internal untuk ditampilkan di panel ──
        $internalSetting = [
            'mode'           => SurveySetting::get('internal_periode_mode', 'triwulan'),
            'periode_aktif'  => SurveySetting::get('internal_periode_aktif'),
            'override_aktif' => SurveySetting::get('internal_override_aktif', 'false') === 'true',
            'override_label' => SurveySetting::get('internal_override_label', ''),
            'label_periode'  => SurveyInternalHelper::labelPeriodeAktif(),
            'bisa_diakses'   => SurveyInternalHelper::bisaDiakses(),
        ];

        $materiTriwulanOpen = SurveySetting::get('materi_triwulan_open', 'false') === 'true';

        // Daftar kategori untuk dropdown & pengelompokan tabel pertanyaan internal
        $kategoriList = SurveyPertanyaan::KATEGORI_LIST();

        return view('admin.survey.pertanyaan', compact(
            'pertanyaan', 'jadwalHariIni', 'internalSetting', 'materiTriwulanOpen', 'kategoriList'
        ));
    }

    public function pertanyaanStore(Request $request)
    {
        $data = $request->validate([
            'pertanyaan'     => 'required|string|max:500',
            'tipe'           => 'required|in:rating',
            'opsi_pilihan'   => 'nullable|array',
            'opsi_pilihan.*' => 'string|max:200',
            'urutan'         => 'nullable|integer|min:0',
            'jenis'          => 'required|in:eksternal,internal,semua',
            // Wajib diisi jika pertanyaan berlaku untuk survey internal,
            // supaya kategori jelas sejak pertanyaan dibuat (tidak ditebak
            // dari kata kunci lagi). Lihat NilaiEvaluasiController.
            'kategori'       => 'required_if:jenis,internal,semua|nullable|in:' . implode(',', array_keys(SurveyPertanyaan::KATEGORI_LIST())),
        ], [
            'kategori.required_if' => 'Kategori wajib dipilih untuk pertanyaan Survey Internal.',
        ]);

        // Pertanyaan eksternal tidak memakai kategori — pastikan null, bukan string kosong.
        if ($data['jenis'] === 'eksternal') {
            $data['kategori'] = null;
        }

        if (empty($data['urutan'])) {
            $data['urutan'] = SurveyPertanyaan::max('urutan') + 1;
        }

        SurveyPertanyaan::create($data);
        return back()->with('success', 'Pertanyaan berhasil ditambahkan. Barcode & link yang sudah ada tidak perlu diperbarui.');
    }

    public function pertanyaanUpdate(Request $request, int $id)
    {
        $pertanyaan = SurveyPertanyaan::findOrFail($id);

        $data = $request->validate([
            'pertanyaan'     => 'required|string|max:500',
            'tipe'           => 'required|in:rating',
            'opsi_pilihan'   => 'nullable|array',
            'opsi_pilihan.*' => 'string|max:200',
            'urutan'         => 'nullable|integer|min:0',
            'is_active'      => 'nullable|boolean',
            'jenis'          => 'required|in:eksternal,internal,semua',
            'kategori'       => 'required_if:jenis,internal,semua|nullable|in:' . implode(',', array_keys(SurveyPertanyaan::KATEGORI_LIST())),
        ], [
            'kategori.required_if' => 'Kategori wajib dipilih untuk pertanyaan Survey Internal.',
        ]);

        if ($data['jenis'] === 'eksternal') {
            $data['kategori'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');
        $pertanyaan->update($data);
        return back()->with('success', 'Pertanyaan berhasil diperbarui. Barcode & link tetap berlaku.');
    }

    public function pertanyaanDestroy(int $id)
    {
        SurveyPertanyaan::findOrFail($id)->delete();
        return back()->with('success', 'Pertanyaan dihapus.');
    }

    public function pertanyaanToggle(int $id)
    {
        $p = SurveyPertanyaan::findOrFail($id);
        $p->update(['is_active' => !$p->is_active]);
        return back()->with('success', 'Status pertanyaan diperbarui.');
    }

    public function pertanyaanReorder(Request $request)
    {
        $request->validate(['urutan' => 'required|array', 'urutan.*' => 'integer']);
        foreach ($request->urutan as $position => $id) {
            SurveyPertanyaan::where('id', $id)->update(['urutan' => $position + 1]);
        }
        return response()->json(['ok' => true]);
    }

    // ══════════════════════════════════════════════════════════════
    // TEMPLATE PESAN & LINK PER WILAYAH (tidak berubah)
    // ══════════════════════════════════════════════════════════════

    public function templateIndex()
    {
        $template    = SurveySetting::get('template_pesan');
        $wilayahList = Wilayah::orderBy('nama')->get();

        $linkPerWilayah = $wilayahList->map(function ($w) {
            $wst  = WilayahSurveyToken::where('wilayah_id', $w->id)->first();
            $link = $wst ? route('survey.link', ['tokenLink' => $wst->token_link]) : null;
            return [
                'wilayah' => $w,
                'link'    => $link,
                'wst'     => $wst,
            ];
        });

        return view('admin.survey.template', compact('template', 'linkPerWilayah'));
    }

    public function templateSimpan(Request $request)
    {
        $request->validate([
            'template_pesan' => 'required|string|max:2000',
        ]);

        SurveySetting::set('template_pesan', $request->template_pesan);
        return back()->with('success', 'Template pesan berhasil disimpan.');
    }

    public function generateToken(int $wilayahId)
    {
        Wilayah::findOrFail($wilayahId);
        WilayahSurveyToken::firstOrGenerate($wilayahId);
        return back()->with('success', 'Link survey berhasil dibuat.');
    }

    // ══════════════════════════════════════════════════════════════
    // HASIL EKSTERNAL — rekap semua wilayah (tidak berubah)
    // ══════════════════════════════════════════════════════════════

    public function hasilIndex(Request $request)
    {
        $periode = $request->input('periode', now()->format('Y-m'));
        $search  = $request->input('search', '');

        $wilayahList = Wilayah::with(['petugas.user'])->orderBy('nama')->get();

        $surveyMap = SurveyKepuasan::where('periode', $periode)
            ->where('status', 'selesai')
            ->where('jenis', 'eksternal') // ← hanya eksternal di halaman ini
            ->whereNotNull('petugas_id')
            ->with('jawaban.pertanyaan')
            ->get()
            ->groupBy('petugas_id');

        $dataPerWilayah = $wilayahList->map(function ($wilayah) use ($surveyMap, $search) {
            $petugasList = $wilayah->petugas->filter(function ($p) use ($search) {
                if (!$p->user) return false;
                if (!$p->user->is_active) return false;
                if ($search && stripos($p->user->name, $search) === false) return false;
                return true;
            })->map(function ($p) use ($surveyMap) {
                $surveys         = $surveyMap->get($p->id, collect());
                $jumlahResponden = $surveys->count();

                $allRatings = $surveys->flatMap(fn($s) => $s->jawaban)
                    ->filter(fn($j) => $j->pertanyaan?->tipe === 'rating' && is_numeric($j->jawaban))
                    ->map(fn($j) => (float) $j->jawaban);

                $rataKepuasan = $allRatings->count() ? round($allRatings->avg(), 2) : null;

                return [
                    'user'             => $p->user,
                    'petugas'          => $p,
                    'jumlah_responden' => $jumlahResponden,
                    'rata_kepuasan'    => $rataKepuasan,
                ];
            })->values();

            $rataWilayah = $petugasList->pluck('rata_kepuasan')->filter();
            $rataWilayah = $rataWilayah->count() ? round($rataWilayah->avg(), 2) : null;

            return [
                'wilayah'         => $wilayah,
                'petugas'         => $petugasList,
                'total_responden' => $petugasList->sum('jumlah_responden'),
                'rata_kepuasan'   => $rataWilayah,
            ];
        });

        $totalSurvey       = SurveyKepuasan::where('periode', $periode)->where('status', 'selesai')->where('jenis', 'eksternal')->count();
        $totalPetugas      = $dataPerWilayah->sum(fn($w) => $w['petugas']->count());
        $allRataGlobal     = $dataPerWilayah->pluck('rata_kepuasan')->filter();
        $rataGlobal        = $allRataGlobal->count() ? round($allRataGlobal->avg(), 2) : null;
        $surveyTanpaJadwal = SurveyKepuasan::where('periode', $periode)
            ->where('status', 'selesai')
            ->where('jenis', 'eksternal')
            ->whereNull('petugas_id')
            ->count();

        return view('admin.survey.hasil', compact(
            'dataPerWilayah', 'periode',
            'totalSurvey', 'totalPetugas', 'rataGlobal', 'search',
            'surveyTanpaJadwal'
        ));
    }

    public function hasilDetail(Request $request, int $petugasId)
    {
        $periode = $request->input('periode', now()->format('Y-m'));
        $petugas = Petugas::with('user')->findOrFail($petugasId);

        $surveys = SurveyKepuasan::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->where('jenis', 'eksternal') // ← hanya eksternal
            ->with('jawaban.pertanyaan')
            ->latest()
            ->get();

        $pertanyaan = SurveyPertanyaan::untukEksternal()
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

        return view('admin.survey.hasil_detail', compact(
            'petugas', 'surveys', 'pertanyaan', 'rataPerPertanyaan', 'periode'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // [BARU] SURVEY INTERNAL — Pengaturan Periode
    // ══════════════════════════════════════════════════════════════

    /**
     * Simpan pengaturan periode Survey Internal.
     * Mode: 'triwulan' (otomatis) atau 'manual' (admin pilih periode)
     */
    /**
     * Toggle buka/tutup Materi & Quiz Triwulan secara global.
     * Endpoint: POST /admin/materi-triwulan/toggle
     */
    public function materiTriwulanToggle(Request $request)
    {
        $current = SurveySetting::get('materi_triwulan_open', 'false');
        $new     = $current === 'true' ? 'false' : 'true';

        SurveySetting::set('materi_triwulan_open', $new);

        $status = $new === 'true' ? 'dibuka' : 'ditutup';
        return back()->with('success', "Akses Materi & Quiz Triwulan berhasil {$status} untuk semua petugas.");
    }

    public function internalSetting(Request $request)
    {
        $request->validate([
            'internal_periode_mode'  => 'required|in:triwulan,manual',
            'internal_periode_aktif' => 'nullable|regex:/^\d{4}-TW[1-4]$/',
        ]);

        SurveySetting::set('internal_periode_mode', $request->internal_periode_mode);

        if ($request->internal_periode_aktif) {
            SurveySetting::set('internal_periode_aktif', $request->internal_periode_aktif);
        } elseif ($request->internal_periode_mode === 'triwulan') {
            // Mode triwulan: set ke triwulan sekarang
            SurveySetting::set('internal_periode_aktif', SurveyInternalHelper::periodeTriwulanSekarang());
        }

        return back()->with('success', 'Pengaturan periode Survey Internal berhasil disimpan.');
    }

    /**
     * Toggle override buka/tutup Survey Internal.
     * Endpoint: POST /admin/survey/internal/toggle-override
     */
    public function internalToggleOverride(Request $request)
    {
        $request->validate([
            'override_aktif' => 'required|in:true,false',
            'override_label' => 'nullable|string|max:200',
        ]);

        SurveySetting::set('internal_override_aktif', $request->override_aktif);
        SurveySetting::set('internal_override_label', $request->override_label ?? '');

        $status = $request->override_aktif === 'true' ? 'dibuka' : 'ditutup';
        return back()->with('success', "Survey Internal berhasil {$status} secara manual.");
    }

    /**
     * Rekap hasil Survey Internal per periode triwulan.
     * Endpoint: GET /admin/survey/internal/hasil
     */
    public function internalHasilIndex(Request $request)
    {
        // Default periode: triwulan berjalan
        $periode = $request->input('periode', SurveyInternalHelper::periodeTriwulanSekarang());
        $search  = $request->input('search', '');

        $wilayahList = Wilayah::with(['petugas.user'])->orderBy('nama')->get();

        // Survey internal dikelompokkan per petugas yang dinilai
        $surveyMap = SurveyKepuasan::where('jenis', 'internal')
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->with('jawaban.pertanyaan')
            ->get()
            ->groupBy('petugas_id');

        $dataPerWilayah = $wilayahList->map(function ($wilayah) use ($surveyMap, $search) {
            $petugasList = $wilayah->petugas->filter(function ($p) use ($search) {
                if (!$p->user) return false;
                if (!$p->user->is_active) return false;
                if ($search && stripos($p->user->name, $search) === false) return false;
                return true;
            })->map(function ($p) use ($surveyMap) {
                $surveys         = $surveyMap->get($p->id, collect());
                $jumlahPenilai   = $surveys->count();

                $allRatings = $surveys->flatMap(fn($s) => $s->jawaban)
                    ->filter(fn($j) => $j->pertanyaan?->tipe === 'rating' && is_numeric($j->jawaban))
                    ->map(fn($j) => (float) $j->jawaban);

                $rataKepuasan = $allRatings->count() ? round($allRatings->avg(), 2) : null;

                return [
                    'user'            => $p->user,
                    'petugas'         => $p,
                    'jumlah_penilai'  => $jumlahPenilai,
                    'rata_kepuasan'   => $rataKepuasan,
                ];
            })->values();

            $rataWilayah = $petugasList->pluck('rata_kepuasan')->filter();
            $rataWilayah = $rataWilayah->count() ? round($rataWilayah->avg(), 2) : null;

            return [
                'wilayah'       => $wilayah,
                'petugas'       => $petugasList,
                'total_penilai' => $petugasList->sum('jumlah_penilai'),
                'rata_kepuasan' => $rataWilayah,
            ];
        });

        $totalSurvey  = SurveyKepuasan::where('jenis', 'internal')->where('periode', $periode)->where('status', 'selesai')->count();
        $allRataGlobal = $dataPerWilayah->pluck('rata_kepuasan')->filter();
        $rataGlobal    = $allRataGlobal->count() ? round($allRataGlobal->avg(), 2) : null;

        // Daftar semua periode triwulan yang tersedia di DB
        $periodeList = SurveyKepuasan::where('jenis', 'internal')
            ->distinct()
            ->orderByDesc('periode')
            ->pluck('periode');

        return view('admin.survey.internal-hasil', compact(
            'dataPerWilayah', 'periode', 'totalSurvey',
            'rataGlobal', 'search', 'periodeList'
        ));
    }

    /**
     * Detail Survey Internal per petugas yang dinilai.
     * Endpoint: GET /admin/survey/internal/hasil/{petugasId}
     */
    public function internalHasilDetail(Request $request, int $petugasId)
    {
        $periode = $request->input('periode', SurveyInternalHelper::periodeTriwulanSekarang());
        $petugas = Petugas::with('user')->findOrFail($petugasId);

        $surveys = SurveyKepuasan::where('petugas_id', $petugasId)
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

        return view('admin.survey.internal-hasil-detail', compact(
            'petugas', 'surveys', 'pertanyaan', 'rataPerPertanyaan', 'periode'
        ));
    }

    // ══════════════════════════════════════════════════════════════
    // POLLING real-time (tidak berubah, hanya tambah filter jenis)
    // ══════════════════════════════════════════════════════════════

    public function polling(\Illuminate\Http\Request $request)
    {
        $after = (int) $request->input('after', 0);
        $today = now()->toDateString();

        $newSurveys = \App\Models\SurveyKepuasan::with(['petugas.user', 'wilayah'])
            ->whereDate('created_at', $today)
            ->where('status', 'selesai')
            ->where('jenis', 'eksternal') // polling hanya untuk eksternal di dashboard
            ->where('id', '>', $after)
            ->orderByDesc('id')
            ->limit(15)
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'nama_responden' => $s->nama_responden ?: 'Anonim',
                'petugas'        => optional(optional($s->petugas)->user)->name ?? '—',
                'wilayah'        => optional($s->wilayah)->nama ?? '—',
                'status'         => $s->status,
                'diisi_pada'     => $s->diisi_pada ? $s->diisi_pada->format('H:i') : '—',
                'rata_rating'    => $s->rataRating(),
            ]);

        $maxId = $newSurveys->max('id') ?? $after;

        $baseToday    = \App\Models\SurveyKepuasan::whereDate('created_at', $today)->where('status', 'selesai')->where('jenis', 'eksternal');
        $selesaiToday = (clone $baseToday)->count();
        $rataToday    = \App\Models\SurveyKepuasan::whereDate('created_at', $today)
            ->where('status', 'selesai')->where('jenis', 'eksternal')
            ->get()->map(fn($s) => $s->rataRating())->filter()->avg();

        $totalSemua = \App\Models\SurveyKepuasan::where('status', 'selesai')->where('jenis', 'eksternal')->count();

        return response()->json([
            'new_surveys' => $newSurveys,
            'max_id'      => $maxId,
            'stats' => [
                'hari_ini'         => (clone $baseToday)->count(),
                'selesai_hari_ini' => $selesaiToday,
                'rata_hari_ini'    => $rataToday ? round($rataToday, 2) : null,
                'total_semua'      => $totalSemua,
                'total_selesai'    => $totalSemua,
            ],
        ]);
    }
}