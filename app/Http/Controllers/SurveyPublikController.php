<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\JadwalPetugas;
use App\Models\SurveyJawaban;
use App\Models\SurveyKepuasan;
use App\Models\SurveyPertanyaan;
use App\Models\WilayahSurveyToken;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * SurveyPublikController
 *
 * Alur barcode (fisik):
 *   Responden scan QR  → GET /survey/barcode/{tokenBarcode}
 *   → resolve wilayah dari token permanen
 *   → resolve petugas dari shift AKTIF saat ini di wilayah tsb
 *   → buat sesi baru (token sekali pakai)
 *   → redirect ke form pengisian
 *
 * Alur link (online):
 *   Petugas kirim link  → GET /survey/link/{tokenLink}
 *   → alur sama seperti barcode, hanya sumber = 'link'
 *
 * Logika resolve petugas (perbaikan):
 *   1. Deteksi shift aktif berdasarkan jam sekarang (pagi 07:00-12:00, siang 12:00-17:00)
 *   2. Cari petugas yang dijadwal shift tersebut hari ini di wilayah ini
 *   3. Jika tidak ada jadwal shift aktif → cari petugas yang absen masuk hari ini
 *      (sudah scan QR masuk, berarti sedang bertugas walau jadwal belum diinput)
 *   4. Jika masih tidak ada → petugas_id = null (SKM tetap tercatat ke wilayah)
 */
class SurveyPublikController extends Controller
{
    // ──────────────────────────────────────────────
    // BARCODE — scan QR fisik (dicetak koordinator)
    // ──────────────────────────────────────────────

    public function showBarcode(string $tokenBarcode)
    {
        $wst = WilayahSurveyToken::where('token_barcode', $tokenBarcode)
            ->with('wilayah')
            ->firstOrFail();

        return $this->buatSesiDanRedirect($wst->wilayah_id, 'barcode');
    }

    // ──────────────────────────────────────────────
    // LINK — dikirim petugas ke responden online
    // ──────────────────────────────────────────────

    public function showLink(string $tokenLink)
    {
        $wst = WilayahSurveyToken::where('token_link', $tokenLink)
            ->with('wilayah')
            ->firstOrFail();

        return $this->buatSesiDanRedirect($wst->wilayah_id, 'link');
    }

    // ──────────────────────────────────────────────
    // FORM — pengisian survey (token sesi sekali pakai)
    // ──────────────────────────────────────────────

    public function show(string $token)
    {
        $survey = SurveyKepuasan::where('token', $token)
            ->with(['petugas.user', 'wilayah'])
            ->firstOrFail();

        if ($survey->status === 'selesai') {
            return view('survey.terima_kasih', compact('survey'));
        }

        $pertanyaan = SurveyPertanyaan::untukEksternal()
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        if ($pertanyaan->isEmpty()) {
            return view('survey.belum_ada_pertanyaan');
        }

        return view('survey.form', compact('survey', 'pertanyaan'));
    }

    // ──────────────────────────────────────────────
    // SUBMIT
    // ──────────────────────────────────────────────

    public function submit(Request $request, string $token)
    {
        $survey = SurveyKepuasan::where('token', $token)->firstOrFail();

        if ($survey->status === 'selesai') {
            return redirect()->route('survey.publik', $token);
        }

        $pertanyaan = SurveyPertanyaan::untukEksternal()
            ->where('is_active', true)
            ->get();

        $rules = ['nama_responden' => 'nullable|string|max:100'];
        foreach ($pertanyaan as $p) {
            $key = "jawaban.{$p->id}";
            if ($p->tipe === 'rating') {
                $rules[$key] = 'required|integer|min:1|max:5';
            } elseif ($p->tipe === 'pilihan') {
                $opsi        = $p->opsi_pilihan ?? [];
                $rules[$key] = 'required|in:' . implode(',', $opsi);
            } else {
                $rules[$key] = 'nullable|string|max:1000';
            }
        }

        $validated = $request->validate($rules);

        foreach ($pertanyaan as $p) {
            $nilai = $validated['jawaban'][$p->id] ?? null;
            if ($nilai === null) continue;

            SurveyJawaban::create([
                'survey_id'     => $survey->id,
                'pertanyaan_id' => $p->id,
                'jawaban'       => (string) $nilai,
            ]);
        }

        $survey->update([
            'nama_responden' => $validated['nama_responden'] ?? null,
            'status'         => 'selesai',
            'diisi_pada'     => now(),
        ]);

        return redirect()->route('survey.publik', $token);
    }

    // ──────────────────────────────────────────────
    // PRIVATE HELPER
    // ──────────────────────────────────────────────

    /**
     * Buat sesi survey baru untuk wilayah tertentu, lalu redirect ke form.
     *
     * Logika resolve petugas (berurutan, berhenti di yang pertama berhasil):
     *
     * Langkah 1 — Deteksi shift aktif dari jam sekarang:
     *   - Sebelum 12:00 → shift 'pagi'
     *   - Pukul 12:00 ke atas → shift 'siang'
     *
     * Langkah 2 — Cari petugas dijadwal shift tsb hari ini di wilayah ini.
     *   Jika ada 2 petugas (misalnya salah input), ambil yang pertama.
     *
     * Langkah 3 — Jika tidak ada jadwal shift aktif → cari petugas yang
     *   SUDAH SCAN MASUK hari ini di wilayah ini (absensi masuk terbaru).
     *   Ini menangani kasus jadwal belum diinput tapi petugas sudah hadir.
     *
     * Langkah 4 — Jika masih tidak ada → petugas_id = null.
     *   SKM tetap tersimpan ke wilayah, bisa diatribusikan manual jika perlu.
     */
    private function buatSesiDanRedirect(int $wilayahId, string $sumber)
    {
        // Cek apakah ada pertanyaan aktif
        $adaPertanyaan = SurveyPertanyaan::untukEksternal()
            ->where('is_active', true)
            ->exists();

        if (!$adaPertanyaan) {
            return view('survey.belum_ada_pertanyaan');
        }

        $petugasId = $this->resolvePetugasAktif($wilayahId);

        // Buat sesi baru (token sekali pakai per responden)
        $survey = SurveyKepuasan::create([
            'wilayah_id' => $wilayahId,
            'petugas_id' => $petugasId,
            'jenis'      => 'eksternal',
            'periode'    => now('Asia/Jakarta')->format('Y-m'),
            'token'      => SurveyKepuasan::buatToken(),
            'status'     => 'menunggu',
        ]);

        return redirect()->route('survey.publik', ['token' => $survey->token]);
    }

    /**
     * Resolve petugas yang sedang aktif bertugas di wilayah ini saat ini.
     *
     * @return int|null petugas_id atau null jika tidak ditemukan
     */
    private function resolvePetugasAktif(int $wilayahId): ?int
    {
        $now      = Carbon::now('Asia/Jakarta');
        $today    = $now->toDateString();
        $jamMenit = $now->hour * 60 + $now->minute;

        // Tentukan shift aktif berdasarkan jam
        // Pagi:  07:00 – 11:59 (sebelum shift siang mulai)
        // Siang: 12:00 – 17:00
        $shiftAktif = $jamMenit < (12 * 60) ? 'pagi' : 'siang';

        // ── LANGKAH 1: Cari dari jadwal shift aktif ──────────────
        $jadwal = JadwalPetugas::where('wilayah_id', $wilayahId)
            ->where('tanggal', $today)
            ->where('shift', $shiftAktif)
            ->where('keterangan', '!=', 'libur')
            ->with('user.petugas')
            ->first();

        if ($jadwal?->user?->petugas?->id) {
            return $jadwal->user->petugas->id;
        }

        // ── LANGKAH 2: Cari dari semua jadwal hari ini (shift apapun) ──
        // Menangani kasus jam overlap atau jadwal ganti
        $jadwalHariIni = JadwalPetugas::where('wilayah_id', $wilayahId)
            ->where('tanggal', $today)
            ->where('keterangan', '!=', 'libur')
            ->with('user.petugas')
            ->orderByRaw("CASE WHEN shift = ? THEN 0 ELSE 1 END", [$shiftAktif])
            ->first();

        if ($jadwalHariIni?->user?->petugas?->id) {
            return $jadwalHariIni->user->petugas->id;
        }

        // ── LANGKAH 3: Fallback — cari dari absensi masuk hari ini ──
        // Petugas yang sudah scan masuk = sedang bertugas
        // Ambil scan masuk terbaru sesuai shift aktif
        $jenisScanAktif = $shiftAktif === 'pagi' ? 'masuk_pagi' : 'masuk_siang';

        $absensi = Absensi::where('wilayah_id', $wilayahId)
            ->where('tanggal', $today)
            ->where('jenis_scan', $jenisScanAktif)
            ->whereIn('status_kehadiran', ['tepat_waktu', 'toleransi', 'terlambat'])
            ->with('user.petugas')
            ->latest('id')
            ->first();

        if ($absensi?->user?->petugas?->id) {
            return $absensi->user->petugas->id;
        }

        // Fallback akhir: scan masuk shift apapun hari ini
        $absensiAny = Absensi::where('wilayah_id', $wilayahId)
            ->where('tanggal', $today)
            ->whereIn('jenis_scan', ['masuk_pagi', 'masuk_siang'])
            ->whereIn('status_kehadiran', ['tepat_waktu', 'toleransi', 'terlambat'])
            ->with('user.petugas')
            ->latest('id')
            ->first();

        return $absensiAny?->user?->petugas?->id ?? null;
    }
}