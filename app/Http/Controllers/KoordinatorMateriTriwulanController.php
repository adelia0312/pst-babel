<?php

namespace App\Http\Controllers;

use App\Helpers\SurveyInternalHelper;
use App\Models\JawabanTriwulan;
use App\Models\MateriTriwulan;
use App\Models\MateriTriwulanFile;
use App\Models\Petugas;
use App\Models\QuizTriwulan;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KoordinatorMateriTriwulanController extends Controller
{
    private function periodeSekarang(): string
    {
        return SurveyInternalHelper::periodeTriwulanSekarang();
    }

    private function labelPeriode(string $periode): string
    {
        // "2026-TW2" → "Triwulan 2 Tahun 2026"
        if (preg_match('/^(\d{4})-TW(\d)$/', $periode, $m)) {
            return "Triwulan {$m[2]} Tahun {$m[1]}";
        }
        return $periode;
    }

    private function semuaPeriode(): array
    {
        $list = [];
        $tahunAwal = 2026;
        $tahunAkhir = now()->year + 1;
        for ($y = $tahunAwal; $y <= $tahunAkhir; $y++) {
            for ($tw = 1; $tw <= 4; $tw++) {
                $list["{$y}-TW{$tw}"] = "Triwulan {$tw} Tahun {$y}";
            }
        }
        return $list;
    }

    public function index(Request $request)
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;
        $wilayah   = Wilayah::find($wilayahId);

        $periodeAktif = $request->input('periode', $this->periodeSekarang());

        $petugasList = Petugas::with('user')
            ->where('wilayah_id', $wilayahId)
            ->whereHas('user', fn($q) => $q->where('role', 'petugas'))
            ->get();

        $petugasIds = $petugasList->pluck('id');

        // Materi triwulan untuk periode ini
        $materiList = MateriTriwulan::with(['quiz', 'files'])
            ->where('wilayah_id', $wilayahId)
            ->where('periode', $periodeAktif)
            ->latest()
            ->get();

        // Enrich dengan data jawaban
        $materiList = $materiList->map(function ($m) use ($petugasList, $petugasIds) {
            $jawabanMap = JawabanTriwulan::where('materi_triwulan_id', $m->id)
                ->whereIn('petugas_id', $petugasIds)
                ->get()
                ->keyBy('petugas_id');

            $sudah  = $jawabanMap->where('status', 'sudah')->count();
            $belum  = $petugasIds->count() - $sudah;
            $progres = $petugasIds->count() > 0 ? round($sudah / $petugasIds->count() * 100) : 0;

            $m->jawabanMap  = $jawabanMap;
            $m->petugasList = $petugasList;
            $m->jmlSudah    = $sudah;
            $m->jmlBelum    = $belum;
            $m->progres     = $progres;
            return $m;
        });

        $periodeOptions = $this->semuaPeriode();

        return view('koordinator.materi.triwulan', compact(
            'materiList', 'wilayah', 'petugasList',
            'periodeAktif', 'periodeOptions'
        ));
    }

    public function create()
    {
        return view('koordinator.materi.triwulan_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'deskripsi'   => 'nullable|string',
            'periode'     => 'required|string',
            'file'        => 'nullable|array',
            'file.*'      => 'file|mimes:pdf,doc,docx,ppt,pptx|max:51200',
            'link'        => 'nullable|url',
            'quiz'        => 'nullable|array',
            'quiz.*.pertanyaan' => 'required_with:quiz|string',
            'quiz.*.jawaban'    => 'required_with:quiz|in:a,b,c,d',
        ]);

        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        $materi = MateriTriwulan::create([
            'wilayah_id'     => $wilayahId,
            'koordinator_id' => $user->id,
            'judul'          => $request->judul,
            'deskripsi'      => $request->deskripsi,
            'periode'        => $request->periode,
            'file'           => null,
            'link'           => $request->link,
        ]);

        // Simpan file (bisa lebih dari satu)
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $uploaded) {
                $filePath = $uploaded->storeAs(
                    'materi_triwulan',
                    time() . '_' . uniqid() . '_' . $uploaded->getClientOriginalName(),
                    'public'
                );

                MateriTriwulanFile::create([
                    'materi_triwulan_id' => $materi->id,
                    'file'               => $filePath,
                    'nama_asli'          => $uploaded->getClientOriginalName(),
                ]);
            }
        }

        // Simpan soal quiz
        if ($request->has('quiz')) {
            foreach ($request->quiz as $q) {
                if (!empty($q['pertanyaan'])) {
                    QuizTriwulan::create([
                        'materi_triwulan_id' => $materi->id,
                        'pertanyaan' => $q['pertanyaan'],
                        'opsi_a'     => $q['opsi_a'] ?? null,
                        'opsi_b'     => $q['opsi_b'] ?? null,
                        'opsi_c'     => $q['opsi_c'] ?? null,
                        'opsi_d'     => $q['opsi_d'] ?? null,
                        'jawaban'    => $q['jawaban'],
                    ]);
                }
            }
        }

        return redirect()
            ->route('koordinator.materi.triwulan', ['periode' => $request->periode])
            ->with('success', 'Materi triwulan berhasil dibuat!');
    }

    /**
     * Form edit materi triwulan.
     */
    public function edit($id)
    {
        $materi = MateriTriwulan::with(['quiz', 'files'])
            ->where('wilayah_id', Auth::user()->wilayah_id)
            ->findOrFail($id);

        $periodeOptions = $this->semuaPeriode();

        return view('koordinator.materi.triwulan_edit', compact('materi', 'periodeOptions'));
    }

    /**
     * Update materi triwulan: judul, deskripsi, periode, link, file (tambah/hapus), dan quiz.
     */
    public function update(Request $request, $id)
    {
        $materi = MateriTriwulan::where('wilayah_id', Auth::user()->wilayah_id)
            ->findOrFail($id);

        $request->validate([
            'judul'       => 'required|string|max:255',
            'deskripsi'   => 'nullable|string',
            'periode'     => 'required|string',
            'file'        => 'nullable|array',
            'file.*'      => 'file|mimes:pdf,doc,docx,ppt,pptx|max:51200',
            'link'        => 'nullable|url',
            'quiz'        => 'nullable|array',
            'quiz.*.pertanyaan' => 'required_with:quiz|string',
            'quiz.*.jawaban'    => 'required_with:quiz|in:a,b,c,d',
        ]);

        // Hapus file lama (legacy single-file) jika diminta
        if ($request->boolean('hapus_file_legacy') && $materi->file) {
            if (Storage::exists('public/' . $materi->file)) {
                Storage::delete('public/' . $materi->file);
            }
            $materi->file = null;
        }

        // Hapus file tertentu dari relasi materi_triwulan_files jika diminta
        $hapusFileIds = $request->input('hapus_file_ids', []);
        if (is_array($hapusFileIds) && count($hapusFileIds) > 0) {
            $filesToDelete = MateriTriwulanFile::where('materi_triwulan_id', $materi->id)
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
                $filePath = $uploaded->storeAs(
                    'materi_triwulan',
                    time() . '_' . uniqid() . '_' . $uploaded->getClientOriginalName(),
                    'public'
                );

                MateriTriwulanFile::create([
                    'materi_triwulan_id' => $materi->id,
                    'file'               => $filePath,
                    'nama_asli'          => $uploaded->getClientOriginalName(),
                ]);
            }
        }

        // Update data utama
        $materi->judul     = $request->judul;
        $materi->deskripsi = $request->deskripsi;
        $materi->periode   = $request->periode;
        $materi->link      = $request->link;
        $materi->save();

        // Update Quiz (replace semua soal lama dengan yang baru, sama seperti materi reguler)
        if ($request->has('quiz')) {
            QuizTriwulan::where('materi_triwulan_id', $materi->id)->delete();

            foreach ($request->quiz as $q) {
                if (!empty($q['pertanyaan'])) {
                    QuizTriwulan::create([
                        'materi_triwulan_id' => $materi->id,
                        'pertanyaan' => $q['pertanyaan'],
                        'opsi_a'     => $q['opsi_a'] ?? null,
                        'opsi_b'     => $q['opsi_b'] ?? null,
                        'opsi_c'     => $q['opsi_c'] ?? null,
                        'opsi_d'     => $q['opsi_d'] ?? null,
                        'jawaban'    => $q['jawaban'],
                    ]);
                }
            }
        }

        return redirect()
            ->route('koordinator.materi.triwulan', ['periode' => $materi->periode])
            ->with('success', 'Materi triwulan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $materi = MateriTriwulan::with('files')
            ->where('wilayah_id', Auth::user()->wilayah_id)
            ->findOrFail($id);

        if ($materi->file) {
            Storage::delete('public/' . $materi->file);
        }

        foreach ($materi->files as $f) {
            if (Storage::exists('public/' . $f->file)) {
                Storage::delete('public/' . $f->file);
            }
        }
        MateriTriwulanFile::where('materi_triwulan_id', $id)->delete();

        QuizTriwulan::where('materi_triwulan_id', $id)->delete();
        JawabanTriwulan::where('materi_triwulan_id', $id)->delete();
        $materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}