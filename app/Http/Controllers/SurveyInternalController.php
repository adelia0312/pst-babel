<?php

namespace App\Http\Controllers;

use App\Helpers\SurveyInternalHelper;
use App\Models\Petugas;
use App\Models\SurveyJawaban;
use App\Models\SurveyKepuasan;
use App\Models\SurveyPertanyaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SurveyInternalController
 *
 * Menangani alur Survey Internal (penilaian antar pegawai).
 * Pegawai menilai rekan satu wilayah satu kali per triwulan.
 *
 * Letak file: app/Http/Controllers/SurveyInternalController.php
 * Status     : FILE BARU
 */
class SurveyInternalController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX — halaman utama survey internal pegawai
    // ──────────────────────────────────────────────────────────────

    public function index()
    {
        $user   = Auth::user();
        $bisaDiakses   = SurveyInternalHelper::bisaDiakses();
        $periodeAktif  = SurveyInternalHelper::periodeAktif();
        $labelPeriode  = SurveyInternalHelper::labelPeriodeAktif();
        $overrideAktif = SurveyInternalHelper::overrideAktif();
        $overrideLabel = SurveyInternalHelper::overrideLabel();
        $infoBerikutnya = SurveyInternalHelper::infoTriwulanBerikutnya();

        // Daftar rekan satu wilayah yang bisa dinilai (kecuali diri sendiri)
        $rekanList = collect();
        if ($bisaDiakses && $user->wilayah_id) {
            $rekanList = User::where('wilayah_id', $user->wilayah_id)
                ->where('role', 'petugas')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->with('petugas')
                ->get();
        }

        // Cek rekan mana yang sudah dinilai periode ini oleh user saat ini
        $sudahDinilai = collect();
        if ($bisaDiakses && $rekanList->isNotEmpty()) {
            $myPetugasId = $user->petugas?->id;
            if ($myPetugasId) {
                // Survey internal yang dibuat oleh pegawai ini periode ini
                // Kami menyimpan penilai di kolom nama_responden dengan format "petugas_id:{id}"
                // untuk keperluan tracking tanpa mengubah schema
                $sudahDinilai = SurveyKepuasan::where('jenis', 'internal')
                    ->where('periode', $periodeAktif)
                    ->where('nama_responden', 'penilai:' . $myPetugasId)
                    ->pluck('petugas_id')
                    ->toArray();
            }
        }

        return view('petugas.survey-internal.index', compact(
            'bisaDiakses', 'periodeAktif', 'labelPeriode',
            'overrideAktif', 'overrideLabel', 'infoBerikutnya',
            'rekanList', 'sudahDinilai'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // FORM — form penilaian untuk satu rekan
    // ──────────────────────────────────────────────────────────────

    public function form(int $petugasId)
    {
        // Gate: pastikan periode terbuka
        if (!SurveyInternalHelper::bisaDiakses()) {
            return redirect()->route('petugas.survey-internal.index')
                ->with('error', 'Survey Internal saat ini belum dibuka.');
        }

        $user         = Auth::user();
        $periodeAktif = SurveyInternalHelper::periodeAktif();
        $myPetugasId  = $user->petugas?->id;

        // Validasi: yang dinilai harus satu wilayah
        $dinilai = User::where('id', function ($q) use ($petugasId) {
                $q->select('user_id')->from('petugas')->where('id', $petugasId);
            })
            ->where('wilayah_id', $user->wilayah_id)
            ->where('role', 'petugas')
            ->where('is_active', true)
            ->firstOrFail();

        // Jangan nilai diri sendiri
        abort_if($dinilai->id === $user->id, 403, 'Tidak dapat menilai diri sendiri.');

        // Cek sudah dinilai belum
        if ($myPetugasId) {
            $sudah = SurveyKepuasan::where('jenis', 'internal')
                ->where('periode', $periodeAktif)
                ->where('petugas_id', $petugasId)
                ->where('nama_responden', 'penilai:' . $myPetugasId)
                ->where('status', 'selesai')
                ->exists();

            if ($sudah) {
                return redirect()->route('petugas.survey-internal.index')
                    ->with('info', 'Anda sudah menilai ' . $dinilai->name . ' untuk periode ini.');
            }
        }

        // Ambil pertanyaan internal
        $pertanyaan = SurveyPertanyaan::untukInternal()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        if ($pertanyaan->isEmpty()) {
            return redirect()->route('petugas.survey-internal.index')
                ->with('error', 'Belum ada pertanyaan Survey Internal yang aktif. Hubungi Admin.');
        }

        // Kelompokkan per kategori supaya rekan kerja mengisi per tema
        // (Komunikasi → Kerja Sama → Inovatif → Kesopanan & Keramahan),
        // bukan daftar pertanyaan polos tanpa konteks.
        $pertanyaanPerKategori = collect(SurveyPertanyaan::KATEGORI_LIST())
            ->map(fn($label, $key) => [
                'label'      => $label,
                'pertanyaan' => $pertanyaan->where('kategori', $key)->values(),
            ])
            ->filter(fn($grup) => $grup['pertanyaan']->isNotEmpty())
            ->values();

        // Pertanyaan lama yang belum diberi kategori (jika ada) tetap
        // ditampilkan, dikelompokkan sebagai "Lainnya" agar tidak hilang
        // dari form sebelum admin selesai melengkapi kategorinya.
        $tanpaKategori = $pertanyaan->whereNull('kategori')->values();
        if ($tanpaKategori->isNotEmpty()) {
            $pertanyaanPerKategori->push([
                'label'      => 'Lainnya',
                'pertanyaan' => $tanpaKategori,
            ]);
        }

        $labelPeriode = SurveyInternalHelper::labelPeriodeAktif();
        $petugas      = Petugas::findOrFail($petugasId);

        return view('petugas.survey-internal.form', compact(
            'dinilai', 'petugas', 'pertanyaan', 'pertanyaanPerKategori', 'periodeAktif', 'labelPeriode'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // SUBMIT — simpan penilaian
    // ──────────────────────────────────────────────────────────────

    public function submit(Request $request, int $petugasId)
    {
        // Gate: pastikan periode terbuka
        if (!SurveyInternalHelper::bisaDiakses()) {
            abort(403, 'Survey Internal saat ini belum dibuka.');
        }

        $user         = Auth::user();
        $periodeAktif = SurveyInternalHelper::periodeAktif();
        $myPetugasId  = $user->petugas?->id;

        abort_if(!$myPetugasId, 403, 'Akun Anda tidak terhubung ke data petugas.');

        // Validasi rekan satu wilayah
        $dinilai = User::where('id', function ($q) use ($petugasId) {
                $q->select('user_id')->from('petugas')->where('id', $petugasId);
            })
            ->where('wilayah_id', $user->wilayah_id)
            ->where('role', 'petugas')
            ->where('is_active', true)
            ->firstOrFail();

        abort_if($dinilai->id === $user->id, 403, 'Tidak dapat menilai diri sendiri.');

        // Cegah double submit
        $sudah = SurveyKepuasan::where('jenis', 'internal')
            ->where('periode', $periodeAktif)
            ->where('petugas_id', $petugasId)
            ->where('nama_responden', 'penilai:' . $myPetugasId)
            ->where('status', 'selesai')
            ->exists();

        if ($sudah) {
            return redirect()->route('petugas.survey-internal.index')
                ->with('info', 'Anda sudah menilai ' . $dinilai->name . ' untuk periode ini.');
        }

        // Ambil pertanyaan aktif
        $pertanyaan = SurveyPertanyaan::untukInternal()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Validasi dinamis berdasarkan pertanyaan
        $rules = [];
        foreach ($pertanyaan as $p) {
            $rules['jawaban.' . $p->id] = $p->tipe === 'teks' ? 'nullable|string|max:1000' : 'required';
        }
        $request->validate($rules);

        // Buat record survey
        $survey = SurveyKepuasan::create([
            'petugas_id'     => $petugasId,
            'wilayah_id'     => $user->wilayah_id,
            'nama_responden' => 'penilai:' . $myPetugasId, // tracking penilai
            'periode'        => $periodeAktif,
            'token'          => SurveyKepuasan::buatToken(),
            'status'         => 'selesai',
            'diisi_pada'     => now(),
            'jenis'          => 'internal',
        ]);

        // Simpan jawaban
        foreach ($pertanyaan as $p) {
            $jawaban = $request->input('jawaban.' . $p->id);
            if ($jawaban !== null && $jawaban !== '') {
                SurveyJawaban::create([
                    'survey_id'      => $survey->id,
                    'pertanyaan_id'  => $p->id,
                    'jawaban'        => $jawaban,
                ]);
            }
        }

        return redirect()->route('petugas.survey-internal.index')
            ->with('success', 'Penilaian untuk ' . $dinilai->name . ' berhasil disimpan. Terima kasih!');
    }
}