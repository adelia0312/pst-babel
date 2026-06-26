<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\Jawaban;
use App\Models\JawabanQuiz;
use App\Models\JadwalPetugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PetugasMateriController extends Controller
{
    /**
     * Hitung deadline efektif untuk seorang petugas pada suatu tugas.
     *
     * Logika:
     * - Cari jadwal PERTAMA petugas sejak tugas terbit
     * - Kalau jadwal pertama itu SETELAH deadline global → pakai jadwal itu sebagai deadline
     * - Kalau jadwal pertama SEBELUM/PADA deadline → pakai deadline global
     * - Kalau petugas tidak punya jadwal sama sekali → pakai deadline global
     */
    private function deadlineEfektif(Tugas $tugas, $petugas): ?Carbon
    {
        if (! $tugas->deadline) {
            return null;
        }

        $deadlineGlobal = Carbon::parse($tugas->deadline)->startOfDay();

        // Tanggal petugas terdaftar (created_at di tabel petugas)
        $tanggalDaftar = Carbon::parse($petugas->created_at)->startOfDay();

        // Jadwal pertama petugas sejak tugas terbit
        $tugasTerbit = Carbon::parse($tugas->created_at)->startOfDay();

        $jadwalPertama = JadwalPetugas::where('user_id', $petugas->user_id)
            ->where('tanggal', '>=', $tugasTerbit->toDateString())
            ->orderBy('tanggal')
            ->first();

        // Kumpulkan kandidat deadline: global, jadwal pertama, tanggal daftar+1
        $kandidat = [$deadlineGlobal];

        if ($jadwalPertama) {
            $kandidat[] = Carbon::parse($jadwalPertama->tanggal)->startOfDay();
        }

        // Jika petugas baru terdaftar SETELAH deadline global,
        // beri 1 hari kerja sejak tanggal daftar sebagai deadline
        if ($tanggalDaftar->gt($deadlineGlobal)) {
            $kandidat[] = $tanggalDaftar->copy()->addDay();
        }

        // Deadline efektif = yang paling akhir dari semua kandidat
        return collect($kandidat)->sortDesc()->first();
    }

    /**
     * METHOD 1 — INDEX
     * Daftar semua tugas + status petugas login (sudah/belum/terlambat)
     */
    public function index()
    {
        $user    = Auth::user();
        $petugas = $user->petugas;

        if (! $petugas) {
            abort(403, 'Akun belum terdaftar sebagai petugas.');
        }

        $tugasList = Tugas::with(['quiz', 'files'])->latest()->get();

        $tugasList = $tugasList->map(function ($tugas) use ($petugas) {
            $jawaban = Jawaban::where('tugas_id', $tugas->id)
                ->where('petugas_id', $petugas->id)
                ->first();

            // Hitung deadline efektif untuk petugas ini
            $deadlineEfektif = $this->deadlineEfektif($tugas, $petugas);
            $tugas->deadlineEfektif = $deadlineEfektif;

            if (! $jawaban || $jawaban->status === 'belum') {
                $tugas->statusPetugas = 'belum';
                $tugas->statusLabel   = 'Belum Dikerjakan';
            } elseif (
                $deadlineEfektif &&
                $jawaban->updated_at &&
                Carbon::parse($jawaban->updated_at)->startOfDay()->gt($deadlineEfektif)
            ) {
                // Submit setelah deadline efektif → terlambat
                $tugas->statusPetugas = 'terlambat';
                $tugas->statusLabel   = 'Terlambat';
            } else {
                $tugas->statusPetugas = 'sudah';
                $tugas->statusLabel   = 'Selesai';
            }

            $tugas->jawabanData = $jawaban;
            return $tugas;
        });

        // Data triwulan
        $periodeTriwulanSekarang = \App\Helpers\SurveyInternalHelper::periodeTriwulanSekarang();
        $bisaIsiTriwulan         = \App\Helpers\SurveyInternalHelper::bisaDiakses();
        $materiTriwulanList = \App\Models\MateriTriwulan::with(['quiz', 'files'])
            ->where('wilayah_id', $petugas->wilayah_id)
            ->where('periode', $periodeTriwulanSekarang)
            ->latest()->get();
        $mtIds = $materiTriwulanList->pluck('id');
        $jawabanTriwulanMap = \App\Models\JawabanTriwulan::where('petugas_id', $petugas->id)
            ->whereIn('materi_triwulan_id', $mtIds)
            ->get()->keyBy('materi_triwulan_id');
        return view('petugas.materi.index', compact('tugasList', 'petugas', 'materiTriwulanList', 'jawabanTriwulanMap', 'bisaIsiTriwulan', 'periodeTriwulanSekarang'));
    }

    /**
     * METHOD 2 — SHOW
     * Detail tugas: info + file + link + soal quiz + form submit
     */
    public function show($id)
    {
        $user    = Auth::user();
        $petugas = $user->petugas;

        if (! $petugas) {
            abort(403, 'Akun belum terdaftar sebagai petugas.');
        }

        $tugas = Tugas::with(['quiz', 'files'])->findOrFail($id);

        $jawaban = Jawaban::where('tugas_id', $id)
            ->where('petugas_id', $petugas->id)
            ->first();

        $jawabanQuizMap = JawabanQuiz::where('tugas_id', $id)
            ->where('petugas_id', $petugas->id)
            ->get()
            ->keyBy('quiz_id');

        $sudahSubmit = $jawaban && $jawaban->status === 'sudah';

        // Cek apakah hari ini petugas punya jadwal
        $adaJadwalHariIni = JadwalPetugas::where('user_id', $petugas->user_id)
            ->where('tanggal', now('Asia/Jakarta')->toDateString())
            ->exists();

        // Hitung deadline efektif untuk ditampilkan di view
        $deadlineEfektif = $this->deadlineEfektif($tugas, $petugas);

        return view('petugas.materi.show', compact(
            'tugas',
            'jawaban',
            'jawabanQuizMap',
            'sudahSubmit',
            'petugas',
            'adaJadwalHariIni',
            'deadlineEfektif'
        ));
    }

    /**
     * METHOD 3 — SUBMIT
     * Simpan file, link, jawaban quiz ke database
     */
    public function submit(Request $request, $id)
    {
        $user    = Auth::user();
        $petugas = $user->petugas;

        if (! $petugas) {
            abort(403, 'Akun belum terdaftar sebagai petugas.');
        }

        $tugas = Tugas::with('quiz')->findOrFail($id);

        // ── CEK 1: Hari ini harus ada jadwal ──────────────────────────────
        $adaJadwalHariIni = JadwalPetugas::where('user_id', $petugas->user_id)
            ->where('tanggal', now('Asia/Jakarta')->toDateString())
            ->exists();

        if (! $adaJadwalHariIni) {
            return back()->with('error', 'Anda hanya dapat mengerjakan tugas pada hari jadwal bertugas.');
        }

        // ── CEK 2: Belum melewati deadline efektif petugas ini ────────────
        $deadlineEfektif = $this->deadlineEfektif($tugas, $petugas);

        if ($deadlineEfektif && now('Asia/Jakarta')->startOfDay()->gt($deadlineEfektif)) {
            return back()->with('error', 'Deadline pengumpulan tugas ini sudah lewat.');
        }

        $request->validate([
            'file'           => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:51200',
            'link'           => 'nullable|url',
            'quiz_jawaban.*' => 'nullable|in:a,b,c,d',
        ]);

        // ── 1. Upload file jika ada ──────────────────────────
        $filePath = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileName = time() . '_' . $petugas->id . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('submissions', $fileName, 'public');
        }

        // ── 2. Hitung skor quiz ──────────────────────────────
        $skor     = null;
        $quizList = $tugas->quiz;

        if ($quizList->isNotEmpty() && $request->has('quiz_jawaban')) {
            $benar = 0;
            foreach ($quizList as $soal) {
                $jawabanPetugas = $request->input("quiz_jawaban.{$soal->id}");
                $jawabanKunci   = strtolower($soal->jawaban ?? '');

                if ($jawabanPetugas && $jawabanPetugas === $jawabanKunci) {
                    $benar++;
                }
            }
            $skor = round(($benar / $quizList->count()) * 100);
        }

        // ── 3. Simpan / update tabel jawaban ────────────────
        $fileToSave = $filePath ?? Jawaban::where('tugas_id', $tugas->id)
            ->where('petugas_id', $petugas->id)
            ->value('file');

        $jawaban = Jawaban::updateOrCreate(
            [
                'tugas_id'   => $tugas->id,
                'petugas_id' => $petugas->id,
            ],
            [
                'status' => 'sudah',
                'skor'   => $skor,
                'link'   => $request->link,
                'file'   => $fileToSave,
            ]
        );

        // ── 4. Simpan jawaban per soal quiz ──────────────────
        if ($quizList->isNotEmpty() && $request->has('quiz_jawaban')) {
            foreach ($quizList as $soal) {
                $jawabanPetugas = $request->input("quiz_jawaban.{$soal->id}");
                if ($jawabanPetugas) {
                    JawabanQuiz::updateOrCreate(
                        [
                            'tugas_id'   => $tugas->id,
                            'petugas_id' => $petugas->id,
                            'quiz_id'    => $soal->id,
                        ],
                        ['jawaban' => $jawabanPetugas]
                    );
                }
            }
        }

        return redirect()
            ->route('petugas.materi.show', $tugas->id)
            ->with('success', 'Tugas berhasil dikumpulkan!' . ($skor !== null ? " Skor quiz kamu: {$skor}/100" : ''));
    }
}