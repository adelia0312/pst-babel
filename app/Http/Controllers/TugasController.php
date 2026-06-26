<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\TugasFile;
use App\Models\Quiz;
use App\Models\Jawaban;
use App\Models\Wilayah;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    // Tampilkan detail tugas
    public function show($id)
    {
        $tugas = Tugas::with(['quiz', 'files'])->findOrFail($id);
        return view('admin.materi.detail_tugas', compact('tugas'));
    }

    // Form edit tugas
    public function edit($id)
    {
        $tugas = Tugas::with(['quiz', 'files'])->findOrFail($id);
        return view('admin.materi.edit_tugas', compact('tugas'));
    }

    // Update tugas
    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'deadline' => 'required|date',
            'file' => 'nullable|array',
            'file.*' => 'file|mimes:pdf,doc,docx,ppt,pptx|max:51200',
            'link' => 'nullable|url',
        ]);

        $tugas = Tugas::findOrFail($id);

        // Hapus file lama (legacy single-file) jika diminta secara eksplisit
        if ($request->boolean('hapus_file_legacy') && $tugas->file) {
            if (Storage::exists('public/' . $tugas->file)) {
                Storage::delete('public/' . $tugas->file);
            }
            $tugas->file = null;
        }

        // Hapus file tertentu dari relasi tugas_files jika diminta
        $hapusFileIds = $request->input('hapus_file_ids', []);
        if (is_array($hapusFileIds) && count($hapusFileIds) > 0) {
            $filesToDelete = TugasFile::where('tugas_id', $tugas->id)
                ->whereIn('id', $hapusFileIds)
                ->get();

            foreach ($filesToDelete as $f) {
                if (Storage::exists('public/' . $f->file)) {
                    Storage::delete('public/' . $f->file);
                }
                $f->delete();
            }
        }

        // Tambah file baru (bisa lebih dari satu)
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $uploaded) {
                $fileName = time() . '_' . uniqid() . '_' . $uploaded->getClientOriginalName();
                $filePath = $uploaded->storeAs('tugas', $fileName, 'public');

                TugasFile::create([
                    'tugas_id'  => $tugas->id,
                    'file'      => $filePath,
                    'nama_asli' => $uploaded->getClientOriginalName(),
                ]);
            }
        }

        // Update data utama
        $tugas->judul = $request->judul;
        $tugas->deskripsi = $request->deskripsi;
        $tugas->deadline = $request->deadline;
        $tugas->link = $request->link;
        $tugas->save();

        // Update Quiz
        if ($request->has('quiz')) {
            // Hapus quiz lama
            Quiz::where('tugas_id', $tugas->id)->delete();

            // Simpan quiz baru
            foreach ($request->quiz as $quizData) {
                if (!empty($quizData['pertanyaan'])) {
                    Quiz::create([
                        'tugas_id' => $tugas->id,
                        'pertanyaan' => $quizData['pertanyaan'],
                        'opsi_a' => $quizData['opsi_a'] ?? null,
                        'opsi_b' => $quizData['opsi_b'] ?? null,
                        'opsi_c' => $quizData['opsi_c'] ?? null,
                        'opsi_d' => $quizData['opsi_d'] ?? null,
                        'jawaban' => $quizData['jawaban'] ?? null,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.tugas.show', $tugas->id)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    // Hapus tugas
    public function destroy($id)
    {
        $tugas = Tugas::with('files')->findOrFail($id);

        // Hapus file legacy jika ada
        if ($tugas->file && Storage::exists('public/' . $tugas->file)) {
            Storage::delete('public/' . $tugas->file);
        }

        // Hapus semua file lampiran (tugas_files)
        foreach ($tugas->files as $f) {
            if (Storage::exists('public/' . $f->file)) {
                Storage::delete('public/' . $f->file);
            }
        }
        // Record di tugas_files otomatis terhapus lewat onDelete('cascade'),
        // tapi dihapus eksplisit juga agar konsisten meski cascade tidak aktif.
        TugasFile::where('tugas_id', $tugas->id)->delete();

        // Hapus quiz terkait (cascade)
        Quiz::where('tugas_id', $tugas->id)->delete();

        // Hapus tugas
        $tugas->delete();

        return redirect()
            ->route('admin.materi')
            ->with('success', 'Tugas berhasil dihapus!');
    }

    public function store(Request $request)
{
    $request->validate([
        'judul' => 'required|string|max:255',
        'deskripsi' => 'required|string',
        'deadline' => 'required|date',
        'file' => 'nullable|array',
        'file.*' => 'file|mimes:pdf,doc,docx,ppt,pptx|max:51200',
        'link' => 'nullable|url',
    ]);

    // simpan tugas (kolom `file` legacy dibiarkan kosong; semua file baru
    // disimpan lewat relasi tugas_files agar bisa lebih dari satu)
    $tugas = Tugas::create([
        'judul' => $request->judul,
        'deskripsi' => $request->deskripsi,
        'deadline' => $request->deadline,
        'file' => null,
        'link' => $request->link
    ]);

    // upload file (bisa banyak)
    if ($request->hasFile('file')) {
        foreach ($request->file('file') as $uploaded) {
            $fileName = time() . '_' . uniqid() . '_' . $uploaded->getClientOriginalName();
            $filePath = $uploaded->storeAs('tugas', $fileName, 'public');

            TugasFile::create([
                'tugas_id'  => $tugas->id,
                'file'      => $filePath,
                'nama_asli' => $uploaded->getClientOriginalName(),
            ]);
        }
    }

    // simpan quiz (kalau ada)
    if ($request->has('quiz')) {
        foreach ($request->quiz as $quizData) {
            if (!empty($quizData['pertanyaan'])) {
                Quiz::create([
                    'tugas_id' => $tugas->id,
                    'pertanyaan' => $quizData['pertanyaan'],
                    'opsi_a' => $quizData['opsi_a'] ?? null,
                    'opsi_b' => $quizData['opsi_b'] ?? null,
                    'opsi_c' => $quizData['opsi_c'] ?? null,
                    'opsi_d' => $quizData['opsi_d'] ?? null,
                    'jawaban' => $quizData['jawaban'] ?? null,
                ]);
            }
        }
    }

    return redirect()
        ->route('admin.materi')
        ->with('success', 'Tugas berhasil ditambahkan!');
}

    /**
     * Data monitoring Materi & Kuis Triwulan untuk ditampilkan di tab admin.
     * Merekap progres pengerjaan kuis triwulan per wilayah/koordinator, untuk
     * periode yang dipilih (default: periode triwulan berjalan).
     */
    public static function dataMonitoringTriwulan(?string $periode = null): array
    {
        $periode = $periode ?: \App\Helpers\SurveyInternalHelper::periodeTriwulanSekarang();

        $wilayahList = \App\Models\Wilayah::with(['petugas' => function ($q) {
            $q->whereHas('user', fn($qq) => $qq->where('role', 'petugas'));
        }])->get();

        $materiTriwulan = \App\Models\MateriTriwulan::with(['quiz', 'files', 'koordinator'])
            ->where('periode', $periode)
            ->get()
            ->groupBy('wilayah_id');

        $rekapWilayah = $wilayahList->map(function ($w) use ($materiTriwulan) {
            $petugasIds   = $w->petugas->pluck('id');
            $materiWilayah = $materiTriwulan->get($w->id, collect());

            $materiIds = $materiWilayah->pluck('id');

            $jawabanSudah = $materiIds->isNotEmpty()
                ? \App\Models\JawabanTriwulan::whereIn('materi_triwulan_id', $materiIds)
                    ->whereIn('petugas_id', $petugasIds)
                    ->where('status', 'sudah')
                    ->count()
                : 0;

            $totalSoal = $materiWilayah->sum(fn($m) => $m->quiz->count());
            $totalPetugas = $petugasIds->count();
            // Total "slot pengerjaan" = jumlah materi x jumlah petugas (tiap materi dikerjakan tiap petugas)
            $totalSlot = $materiWilayah->count() * $totalPetugas;
            $progres   = $totalSlot > 0 ? round($jawabanSudah / $totalSlot * 100) : 0;

            return (object) [
                'wilayah'       => $w,
                'totalMateri'   => $materiWilayah->count(),
                'totalSoal'     => $totalSoal,
                'totalPetugas'  => $totalPetugas,
                'jmlSudah'      => $jawabanSudah,
                'jmlBelum'      => max(0, $totalSlot - $jawabanSudah),
                'progres'       => $progres,
                'materiList'    => $materiWilayah->values(),
                'koordinatorNama' => $materiWilayah->first()?->koordinator?->name ?? '—',
            ];
        });

        return [
            'periode'      => $periode,
            'rekapWilayah' => $rekapWilayah,
        ];
    }

    /**
     * Monitoring detail per wilayah — dipanggil dari tombol "Lihat Detail"
     */
    public function monitoringDetail($wilayahId)
    {
        $wilayah = \App\Models\Wilayah::with('petugas.user')->findOrFail($wilayahId);
        $tugas   = \App\Models\Tugas::with('quiz')->latest()->get();

        $petugasIds = $wilayah->petugas->pluck('id');

        $tugasList = $tugas->map(function ($t) use ($petugasIds) {
            $jawabanList = \App\Models\Jawaban::where('tugas_id', $t->id)
                ->whereIn('petugas_id', $petugasIds)
                ->get()
                ->keyBy('petugas_id');

            $t->sudah       = $jawabanList->where('status', 'sudah')->count();
            $t->belum       = $petugasIds->count() - $t->sudah;
            $t->progress    = $petugasIds->count() > 0
                ? round(($t->sudah / $petugasIds->count()) * 100)
                : 0;
            $t->jawabanList = $jawabanList;
            return $t;
        });

        return view('admin.materi.detail', compact('wilayah', 'tugasList'));
    }

    // ── POLLING: Materi & Pembelajaran ─────────────────────────────
    public function pollingMateri(Request $request)
    {
        $after = (int) $request->input('after', 0);

        // Materi baru (tugas terbaru)
        $newMateri = \App\Models\Tugas::with('files')
            ->where('id', '>', $after)
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'judul'       => $t->judul,
                'deskripsi'   => $t->deskripsi,
                'created_at'  => $t->created_at->format('d M Y'),
                'has_file'    => (bool) $t->file || $t->files->isNotEmpty(),
                'has_link'    => (bool) $t->link,
                'detail_url'  => route('admin.tugas.show', $t->id),
            ]);

        $maxId = $newMateri->max('id') ?? $after;

        // Stats monitoring tugas
        $wilayah = \App\Models\Wilayah::with('petugas')->get();
        $statsPerWilayah = $wilayah->map(function ($w) {
            $petugasIds = $w->petugas->pluck('id');
            $sudah  = \App\Models\Jawaban::where('status', 'sudah')
                        ->whereIn('petugas_id', $petugasIds)->count();
            $total  = $petugasIds->count();
            return [
                'wilayah_id'   => $w->id,
                'sudah'        => $sudah,
                'belum'        => max(0, $total - $sudah),
                'total'        => $total,
                'progress_pct' => $total > 0 ? round($sudah / $total * 100) : 0,
            ];
        });

        $totalTugas      = \App\Models\Tugas::count();
        $totalMateri     = $totalTugas;
        $totalSudah      = \App\Models\Jawaban::where('status', 'sudah')->count();
        $totalPetugas    = \App\Models\Petugas::count();

        return response()->json([
            'new_materi'      => $newMateri,
            'max_id'          => $maxId,
            'stats_wilayah'   => $statsPerWilayah,
            'summary' => [
                'total_materi'   => $totalMateri,
                'total_sudah'    => $totalSudah,
                'total_petugas'  => $totalPetugas,
            ],
        ]);
    }
}