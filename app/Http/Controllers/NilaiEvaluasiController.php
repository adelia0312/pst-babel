<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\ChecklistHarian;
use App\Models\EvaluasiPetugas;
use App\Models\Jawaban;
use App\Models\JawabanQuiz;
use App\Models\JawabanTriwulan;
use App\Models\JadwalPetugas;
use App\Models\LaporanHarianBaru;
use App\Models\MateriTriwulan;
use App\Models\Petugas;
use App\Models\QuizTriwulan;
use App\Models\SurveyJawaban;
use App\Models\SurveyKepuasan;
use App\Models\SurveyPertanyaan;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NilaiEvaluasiController extends Controller
{
    // ── Helper ──────────────────────────────────────────────
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

    /**
     * Parse periode string ke array bulan.
     * "2026-TW1" → [2026, [1,2,3], 1]
     */
    private function parsePeriode(string $periode): array
    {
        if (preg_match('/^(\d{4})-TW([1-4])$/', $periode, $m)) {
            $tahun = (int)$m[1];
            $tw    = (int)$m[2];
            $bulan = match($tw) {
                1 => [1,2,3],
                2 => [4,5,6],
                3 => [7,8,9],
                4 => [10,11,12],
            };
            return [$tahun, $bulan, $tw];
        }
        [$y, $bul] = explode('-', $periode);
        return [(int)$y, [(int)$bul], null];
    }

    public static function periodeOptions(): array
    {
        $now  = Carbon::now();
        $tw   = (int) ceil($now->month / 3);
        $opts = [];
        for ($t = $tw; $t >= 1; $t--) {
            $key        = "{$now->year}-TW{$t}";
            $opts[$key] = "Triwulan {$t} Tahun {$now->year}";
        }
        return $opts;
    }

    public static function periodeSekarang(): string
    {
        $tw = (int) ceil(Carbon::now()->month / 3);
        return Carbon::now()->year . '-TW' . $tw;
    }

    // ════════════════════════════════════════════════════════
    // INDEX
    // ════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $wilayahId      = $this->wilayahId();
        $periode        = $request->input('periode', self::periodeSekarang());
        $search         = $request->input('search', '');
        $periodeOptions = self::periodeOptions();

        $petugas      = $this->getPetugasWilayah();
        $totalPetugas = $petugas->count();

        $evaluasiMap = EvaluasiPetugas::where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->get()
            ->keyBy('petugas_id');

        $sudahEvaluasi = $evaluasiMap->count();
        $belumEvaluasi = $totalPetugas - $sudahEvaluasi;
        $rataRata      = $evaluasiMap->whereNotNull('jumlah_nilai')->avg('jumlah_nilai');

        $dataPetugas = $petugas->map(function ($user) use ($periode, $evaluasiMap) {
            $p = $user->petugas;
            if (!$p) return null;
            $eval     = $evaluasiMap->get($p->id);
            $otomatis = $this->hitungNilaiOtomatis($user->id, $p->id, $periode);
            return [
                'user'           => $user,
                'petugas'        => $p,
                'evaluasi'       => $eval,
                'nilai_otomatis' => $otomatis,
                'rata_sikap'     => $eval?->rata_sikap_kerja,
                'rata_hasil'     => $eval?->rata_indikator_hasil,
                'rata_proses'    => $eval?->rata_indikator_proses,
                'rata_mutu'      => $eval?->rata_mutu_pelayanan,
                'jumlah_nilai'   => $eval?->jumlah_nilai,
                'grade'          => $eval?->grade ?? '-',
                'status'         => $eval ? $eval->status : 'belum',
            ];
        })->filter()->values();

        if ($search) {
            $dataPetugas = $dataPetugas->filter(fn($d) =>
                str_contains(strtolower($d['user']->name), strtolower($search))
            )->values();
        }

        $ranking = $dataPetugas
            ->filter(fn($d) => $d['jumlah_nilai'] !== null)
            ->sortByDesc('jumlah_nilai')
            ->values();

        $petugasTerbaik = $ranking->first();

        return view('koordinator.nilai_evaluasi.index', compact(
            'dataPetugas', 'ranking', 'periode', 'search',
            'totalPetugas', 'sudahEvaluasi', 'belumEvaluasi',
            'rataRata', 'petugasTerbaik', 'periodeOptions'
        ));
    }

    // ════════════════════════════════════════════════════════
    // FORM
    // ════════════════════════════════════════════════════════
    public function formEvaluasi(Request $request, $petugasId)
    {
        $wilayahId      = $this->wilayahId();
        $periode        = $request->input('periode', self::periodeSekarang());
        $periodeOptions = self::periodeOptions();

        $petugas = Petugas::with('user')
            ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId))
            ->findOrFail($petugasId);

        $evaluasi = EvaluasiPetugas::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->first();

        $otomatis = $this->hitungNilaiOtomatis($petugas->user_id, $petugasId, $periode);

        return view('koordinator.nilai_evaluasi.form_evaluasi', compact(
            'petugas', 'evaluasi', 'periode', 'otomatis', 'periodeOptions'
        ));
    }

    // ════════════════════════════════════════════════════════
    // SIMPAN
    // ════════════════════════════════════════════════════════
    public function simpanEvaluasi(Request $request, $petugasId)
    {
        $wilayahId = $this->wilayahId();
        $periode   = $request->input('periode', self::periodeSekarang());

        $petugas = Petugas::whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId))
            ->findOrFail($petugasId);

        $request->validate([
            'catatan'           => 'nullable|string|max:1000',
            'status'            => 'required|in:draft,selesai',
            'kepuasan_manual'   => 'nullable|numeric|min:0|max:100',
        ]);

        $kepuasanManual = $request->filled('kepuasan_manual')
            ? (float) $request->kepuasan_manual
            : null;

        $otomatis = $this->hitungNilaiOtomatis($petugas->user_id, $petugasId, $periode, $kepuasanManual);

        $data = [
            'petugas_id'               => $petugasId,
            'koordinator_id'           => Auth::id(),
            'wilayah_id'               => $wilayahId,
            'periode'                  => $periode,
            'tipe_periode'             => 'triwulan',
            'tanggal_evaluasi'         => now()->toDateString(),

            // I. SIKAP KERJA
            'nilai_kehadiran'          => $otomatis['kehadiran'],
            'nilai_disiplin'           => $otomatis['disiplin'],
            'nilai_komunikasi'         => $otomatis['komunikasi'],
            'nilai_kerjasama'          => $otomatis['kerjasama'],
            'nilai_inovatif'           => $otomatis['inovatif'],

            // II.A INDIKATOR HASIL
            'nilai_kepastian_waktu'    => $otomatis['kepastian_waktu'],
            'nilai_akurasi'            => $otomatis['akurasi'],

            // II.B INDIKATOR PROSES
            // Kesopanan & Keramahan digabung jadi 1 kategori survey internal
            // (lihat SurveyPertanyaan::KATEGORI_LIST & migration 2026_06_19_100000).
            'nilai_tanggungjawab'      => $otomatis['tanggungjawab'],
            'nilai_kesopanan_keramahan' => $otomatis['kesopanan_keramahan'],
            'nilai_kesesuaian_atribut' => $otomatis['kesesuaian_atribut'],

            // III. MUTU PELAYANAN
            'nilai_kepatuhan_sop'         => $otomatis['kepatuhan_sop'],
            'nilai_kepuasan_pelanggan'    => $otomatis['kepuasan_pelanggan'],
            'nilai_kepuasan_manual'       => $otomatis['kepuasan_manual'],
            'sumber_kepuasan'             => $otomatis['sumber_kepuasan'],

            // Field lama tidak terpakai
            'nilai_kejelasan'           => null,
            'nilai_kelengkapan_sarpras' => null,

            'status'  => $request->status,
            'catatan' => $request->catatan,
        ];

        $evaluasi = EvaluasiPetugas::updateOrCreate(
            ['petugas_id' => $petugasId, 'periode' => $periode, 'tipe_periode' => 'triwulan'],
            $data
        );

        // ── Simpan nilai kategori TAMBAHAN (snapshot per triwulan) ──
        // Lihat App\Models\EvaluasiKategoriNilai — nama & komponen
        // di-copy apa adanya saat ini, supaya jika admin mengedit nama/
        // komponen kategori di kemudian hari, histori triwulan ini
        // (yang sudah tampil di grafik) tidak ikut berubah.
        foreach ($otomatis['kategori_tambahan'] as $kat) {
            \App\Models\EvaluasiKategoriNilai::updateOrCreate(
                [
                    'evaluasi_petugas_id'    => $evaluasi->id,
                    'kategori_kode_snapshot' => $kat['kategori_kode_snapshot'],
                ],
                [
                    'kategori_penilaian_id'  => $kat['kategori_penilaian_id'],
                    'nama_kategori_snapshot' => $kat['nama_kategori_snapshot'],
                    'komponen_snapshot'      => $kat['komponen_snapshot'],
                    'nilai'                  => $kat['nilai'],
                ]
            );
        }

        $evaluasi->hitungKomposit();
        $evaluasi->save();

        $pesanStatus = $request->status === 'selesai'
            ? 'Evaluasi ' . $petugas->user->name . ' berhasil diselesaikan.'
            : 'Evaluasi ' . $petugas->user->name . ' disimpan sebagai Draft.';

        return redirect()->route('koordinator.nilai-evaluasi.index', ['periode' => $periode])
            ->with('success', $pesanStatus);
    }

    // ════════════════════════════════════════════════════════
    // DETAIL
    // ════════════════════════════════════════════════════════
    public function detail($petugasId)
    {
        $wilayahId = $this->wilayahId();

        $petugas = Petugas::with('user')
            ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId))
            ->findOrFail($petugasId);

        $userId = $petugas->user_id;

        $histori   = EvaluasiPetugas::where('petugas_id', $petugasId)->orderBy('periode','desc')->get();
        $absensi   = Absensi::where('user_id', $userId)->where('tanggal','>=',now()->subDays(90))->orderBy('tanggal','desc')->get();
        $checklist = ChecklistHarian::where('user_id', $userId)->where('tanggal','>=',now()->subDays(90))->orderBy('tanggal','desc')->get();
        $laporan   = LaporanHarianBaru::where('user_id', $userId)->orderBy('tanggal','desc')->limit(10)->get();
        $nilaiQuiz = Jawaban::where('petugas_id', $petugasId)->with('tugas')->orderBy('created_at','desc')->get();

        $totalAbsensi = Absensi::where('user_id', $userId)->count();
        $tepat        = Absensi::where('user_id', $userId)->where('status_kehadiran','tepat_waktu')->count();
        $terlambat    = Absensi::where('user_id', $userId)->where('status_kehadiran','terlambat')->count();

        $grafikData = $histori->map(fn($e) => [
            'periode'      => $e->periode,
            'jumlah_nilai' => $e->jumlah_nilai,
            'grade'        => $e->grade,
        ])->reverse()->values();

        return view('koordinator.nilai_evaluasi.detail', compact(
            'petugas', 'histori', 'absensi', 'checklist',
            'laporan', 'nilaiQuiz', 'grafikData',
            'totalAbsensi', 'tepat', 'terlambat'
        ));
    }

    // ════════════════════════════════════════════════════════
    // PRIVATE: Hitung Nilai Otomatis (per TRIWULAN)
    //
    // Sistem materi ada DUA jalur:
    //   Jalur A — Admin: tabel tugas + jawaban + jawaban_quiz + quiz
    //             (tidak ada kolom periode, filter pakai deadline)
    //   Jalur B — Koordinator: tabel materi_triwulan + jawaban_triwulan + quiz_triwulan
    //             (ada kolom periode & wilayah_id, skor langsung tersimpan)
    //
    // Kedua jalur digabung untuk Kepastian Waktu dan Akurasi Data.
    // ════════════════════════════════════════════════════════
    /**
     * Public wrapper — dipanggil dari PetugasPenilaianController
     * supaya petugas bisa lihat estimasi nilai tanpa nunggu koordinator.
     */
    public function hitungNilaiOtomatisPublic(int $userId, int $petugasId, string $periode, ?float $kepuasanManual = null): array
    {
        return $this->hitungNilaiOtomatis($userId, $petugasId, $periode, $kepuasanManual);
    }

    private function hitungNilaiOtomatis(int $userId, int $petugasId, string $periode, ?float $kepuasanManual = null): array
    {
        [$tahun, $bulanArr, $tw] = $this->parsePeriode($periode);

        $tglMulai = Carbon::createFromDate($tahun, $bulanArr[0], 1)->startOfDay();
        $tglAkhir = Carbon::createFromDate($tahun, end($bulanArr), 1)->endOfMonth()->endOfDay();

        // ══════════════════════════════════════════════════════
        // 1. KEHADIRAN
        //    Sumber: jadwal_petugas (keterangan) + absensi (scan masuk)
        //
        //    • Shift WAJIB = jadwal keterangan != 'libur'
        //    • Shift HADIR = ada scan masuk_pagi atau masuk_siang
        //      dengan status hadir (tepat/toleransi/terlambat)
        //    • keterangan 'ganti' tetap dihitung wajib (petugas ganti
        //      berarti tetap bertugas, hanya hari berbeda dari jadwal asal)
        //    Nilai = (shift hadir / shift wajib) × 100
        // ══════════════════════════════════════════════════════
        $jadwalWajib = JadwalPetugas::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->where('keterangan', '!=', 'libur')
            ->count();

        // COUNT DISTINCT tanggal+sesi — pakai subquery agar kompatibel semua versi MySQL
        // tidak_scan_keluar tetap dihitung HADIR di komponen ini (orangnya datang,
        // cuma lupa scan keluar) — penalti untuk itu masuk di komponen Disiplin.
        $shiftHadir = Absensi::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->whereIn('jenis_scan', ['masuk_pagi', 'masuk_siang'])
            ->whereIn('status_kehadiran', ['tepat_waktu', 'toleransi', 'terlambat', 'tidak_scan_keluar'])
            ->selectRaw('DATE(tanggal) as tgl, sesi')
            ->groupBy(DB::raw('DATE(tanggal)'), 'sesi')
            ->get()
            ->count();

        $jumlahGanti = JadwalPetugas::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->where('keterangan', 'ganti')
            ->count();

        $nilaiKehadiran = null;
        $infoKehadiran  = 'Jadwal belum diinput';

        if ($jadwalWajib > 0) {
            $nilaiKehadiran = round(min(($shiftHadir / $jadwalWajib) * 100, 100), 2);
            $infoKehadiran  = "{$shiftHadir} dari {$jadwalWajib} shift hadir"
                . ($jumlahGanti > 0 ? " (termasuk {$jumlahGanti}× ganti)" : '');
        } elseif ($shiftHadir > 0) {
            $infoKehadiran = 'Ada absensi tapi jadwal belum diinput koordinator';
        }

        // ══════════════════════════════════════════════════════
        // 2. DISIPLIN WAKTU
        //    Sumber: absensi (status_kehadiran, keterlambatan_menit)
        //    Hanya scan masuk (masuk_pagi & masuk_siang)
        //    Nilai = rata-rata skor ketepatan per scan
        //
        //    Skor: tepat_waktu=100, toleransi=88, tidak_scan_keluar=82,
        //          terlambat ≤10 mnt=80, ≤30 mnt=70, >30 mnt=65, alpha=0
        //
        //    tidak_scan_keluar: petugas tetap hadir & scan masuk tepat
        //    waktu/toleransi, tapi tidak menyelesaikan prosedur absen
        //    (lupa scan keluar). Diberi skor sedikit di bawah toleransi
        //    karena tetap dianggap kelalaian disiplin, bukan keterlambatan.
        // ══════════════════════════════════════════════════════
        // Sertakan juga baris alpha (jenis_scan null, dibuat oleh
        // absensi:deteksi-alpha) supaya skor 0 untuk alpha benar-benar
        // ikut menurunkan rata-rata disiplin, bukan diam-diam diabaikan.
        $scanMasuk = Absensi::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->where(fn($q) => $q->whereIn('jenis_scan', ['masuk_pagi', 'masuk_siang'])
                ->orWhere('status_kehadiran', 'alpha'))
            ->get();

        $nilaiDisiplin = null;
        $infoDisiplin  = 'Belum ada data absensi';

        if ($scanMasuk->isNotEmpty()) {
            $nilaiDisiplin = round($scanMasuk->map(fn($a) => match(true) {
                $a->status_kehadiran === 'tepat_waktu'        => 100,
                $a->status_kehadiran === 'toleransi'          => 88,
                $a->status_kehadiran === 'tidak_scan_keluar'  => 82,
                $a->status_kehadiran === 'alpha'              => 0,
                ($a->keterlambatan_menit ?? 0) <= 10          => 80,
                ($a->keterlambatan_menit ?? 0) <= 30          => 70,
                default                                       => 65,
            })->avg(), 2);

            $tepat            = $scanMasuk->where('status_kehadiran', 'tepat_waktu')->count();
            $terlambat        = $scanMasuk->whereIn('status_kehadiran', ['toleransi','terlambat'])->count();
            $tidakScanKeluar  = $scanMasuk->where('status_kehadiran', 'tidak_scan_keluar')->count();
            $jumlahAlpha      = $scanMasuk->where('status_kehadiran', 'alpha')->count();
            $infoDisiplin = "{$scanMasuk->count()} shift: {$tepat} tepat, {$terlambat} terlambat"
                . ($tidakScanKeluar > 0 ? ", {$tidakScanKeluar} lupa scan keluar" : '')
                . ($jumlahAlpha > 0 ? ", {$jumlahAlpha} alpha" : '');
        }

        // ══════════════════════════════════════════════════════
        // 3. KOMUNIKASI, KERJA SAMA, INOVATIF, KESOPANAN & KERAMAHAN
        //    Sumber: survey_kepuasan (jenis=internal) — diisi PETUGAS LAIN
        //    Jawaban disimpan di survey_jawaban per pertanyaan
        //
        //    Kategori diambil LANGSUNG dari kolom survey_pertanyaan.kategori
        //    (diisi admin saat membuat pertanyaan), TIDAK lagi ditebak dari
        //    kata kunci di teks pertanyaan. Ini memastikan setiap pertanyaan
        //    pasti masuk ke kategori yang benar, sesuai yang admin tentukan.
        //
        //    Nilai = (avg rating pertanyaan di kategori itu / 5) × 100
        // ══════════════════════════════════════════════════════
        $surveyInternal = SurveyKepuasan::where('petugas_id', $petugasId)
            ->where('jenis', 'internal')
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->with(['jawaban.pertanyaan'])
            ->get();

        $nilaiKomunikasi          = null;
        $nilaiKerjasama           = null;
        $nilaiInovatif            = null;
        $nilaiKesopananKeramahan  = null;
        $infoSurveyInt            = 'Survey internal belum ada untuk periode ini';

        if ($surveyInternal->isNotEmpty()) {
            $jumlahPenilai = $surveyInternal->count();

            $semuaJawaban = $surveyInternal->flatMap(fn($s) => $s->jawaban)
                ->filter(fn($j) => $j->pertanyaan?->tipe === 'rating' && is_numeric($j->jawaban));

            $calc = fn($kategoriKey) => $this->avgRatingUntukKategori($semuaJawaban, $kategoriKey);

            $nilaiKomunikasi         = $calc('komunikasi');
            $nilaiKerjasama          = $calc('kerja_sama');
            $nilaiInovatif           = $calc('inovatif');
            $nilaiKesopananKeramahan = $calc('kesopanan_keramahan');

            $tanpaKategori = $semuaJawaban->filter(fn($j) => empty($j->pertanyaan?->kategori))->count();
            $infoSurveyInt = "Dinilai {$jumlahPenilai} rekan"
                . ($tanpaKategori > 0 ? " ({$tanpaKategori} jawaban dari pertanyaan tanpa kategori diabaikan dari skor — lengkapi kategorinya di Admin)" : '');
        }

        // ── KATEGORI TAMBAHAN (di luar 4 kategori bawaan) ──────────
        // Kategori baru yang admin buat lewat menu Kategori Penilaian
        // (lihat App\Models\KategoriPenilaian) dihitung di sini, terpisah
        // dari 4 kategori bawaan di atas karena nilainya disimpan di
        // tabel evaluasi_kategori_nilai (baris), bukan kolom tetap di
        // evaluasi_petugas. Kategori bawaan SENGAJA tidak diulang di sini
        // agar tidak terhitung dua kali.
        $kategoriTambahanHasil = collect();
        if ($surveyInternal->isNotEmpty()) {
            $semuaJawabanUntukTambahan = $surveyInternal->flatMap(fn($s) => $s->jawaban)
                ->filter(fn($j) => $j->pertanyaan?->tipe === 'rating' && is_numeric($j->jawaban));

            $kodeBawaan = \App\Models\KategoriPenilaian::where('sumber', 'bawaan')->pluck('kode');

            $kategoriTambahanAktif = \App\Models\KategoriPenilaian::where('sumber', 'tambahan')
                ->where('is_active', true)
                ->orderBy('urutan')
                ->get();

            foreach ($kategoriTambahanAktif as $kat) {
                if ($kodeBawaan->contains($kat->kode)) {
                    continue; // jaga-jaga, seharusnya tidak terjadi
                }
                $nilai = $this->avgRatingUntukKategori($semuaJawabanUntukTambahan, $kat->kode);
                $kategoriTambahanHasil->push([
                    'kategori_penilaian_id'   => $kat->id,
                    'kategori_kode_snapshot'  => $kat->kode,
                    'nama_kategori_snapshot'  => $kat->nama,
                    'komponen_snapshot'       => $kat->komponen,
                    'nilai'                   => $nilai,
                ]);
            }
        }

        // ══════════════════════════════════════════════════════
        // 4. KEPASTIAN WAKTU (kehadiran mengerjakan materi/pembinaan)
        //
        //    JALUR A — Materi dari Admin (tabel: tugas + jawaban)
        //    Filter: tugas yang deadline-nya jatuh dalam triwulan ini
        //    Hadir = ada record di tabel jawaban (status='sudah')
        //
        //    JALUR B — Materi Triwulan dari Koordinator
        //              (tabel: materi_triwulan + jawaban_triwulan)
        //    Filter: materi_triwulan.periode = periode ini + wilayah petugas
        //    Hadir = ada record jawaban_triwulan (status='sudah')
        //
        //    Nilai = (total hadir A+B) / (total materi A+B) × 100
        // ══════════════════════════════════════════════════════

        // Jalur A: tugas dari Admin
        $tugasAdminIds = Tugas::whereBetween('deadline', [
            $tglMulai->toDateString(), $tglAkhir->toDateString()
        ])->pluck('id');

        $hadirJalurA = 0;
        $totalJalurA = $tugasAdminIds->count();
        if ($tugasAdminIds->isNotEmpty()) {
            $hadirJalurA = Jawaban::where('petugas_id', $petugasId)
                ->whereIn('tugas_id', $tugasAdminIds)
                ->where('status', 'sudah')
                ->count();
        }

        // Ambil wilayah_id petugas (perlu untuk filter materi triwulan)
        $wilayahPetugas = User::where('id', $userId)->value('wilayah_id');

        // Jalur B: materi triwulan dari Koordinator
        $materiTriwulanIds = MateriTriwulan::where('periode', $periode)
            ->where('wilayah_id', $wilayahPetugas)
            ->pluck('id');

        $hadirJalurB = 0;
        $totalJalurB = $materiTriwulanIds->count();
        if ($materiTriwulanIds->isNotEmpty()) {
            $hadirJalurB = JawabanTriwulan::where('petugas_id', $petugasId)
                ->whereIn('materi_triwulan_id', $materiTriwulanIds)
                ->where('status', 'sudah')
                ->count();
        }

        $totalMateri = $totalJalurA + $totalJalurB;
        $totalHadir  = $hadirJalurA + $hadirJalurB;

        $nilaiKepastianWaktu = null;
        $infoKepastian       = 'Belum ada materi/pembinaan di periode ini';

        if ($totalMateri > 0) {
            $nilaiKepastianWaktu = round(($totalHadir / $totalMateri) * 100, 2);
            $bagian = [];
            if ($totalJalurA > 0) $bagian[] = "{$hadirJalurA}/{$totalJalurA} materi admin";
            if ($totalJalurB > 0) $bagian[] = "{$hadirJalurB}/{$totalJalurB} materi koordinator";
            $infoKepastian = implode(', ', $bagian) . " dikerjakan";
        }

        // ══════════════════════════════════════════════════════
        // 5. AKURASI DATA (nilai quiz/post-test)
        //
        //    JALUR A — Quiz dari Admin (tabel: jawaban_quiz + quiz)
        //    Cocokkan jawaban petugas vs kunci di tabel quiz
        //
        //    JALUR B — Quiz Triwulan dari Koordinator
        //              (tabel: jawaban_triwulan kolom skor)
        //    Skor sudah dihitung saat submit (0-100), langsung pakai
        //
        //    Nilai = rata-rata skor semua quiz yang dikerjakan (A+B)
        // ══════════════════════════════════════════════════════

        // Jalur A: quiz dari Admin
        // Prioritas: pakai jawaban.skor (sudah dihitung saat submit)
        // Fallback: hitung ulang dari jawaban_quiz jika skor null (tugas tanpa quiz)
        $skorJalurA   = collect();
        $infoAkurasiA = '';

        if ($tugasAdminIds->isNotEmpty()) {
            // Ambil semua jawaban tugas yang sudah dikerjakan petugas ini
            $jawabanTugasAdmin = Jawaban::where('petugas_id', $petugasId)
                ->whereIn('tugas_id', $tugasAdminIds)
                ->where('status', 'sudah')
                ->get();

            foreach ($jawabanTugasAdmin as $jaw) {
                if ($jaw->skor !== null) {
                    // Skor sudah tersimpan saat submit — langsung pakai
                    $skorJalurA->push((float) $jaw->skor);
                } else {
                    // Fallback: tugas tanpa quiz tapi tetap dikerjakan, skor=null
                    // Tidak dihitung ke akurasi (tidak ada quiz = tidak ada nilai akurasi)
                }
            }

            // Jika ada tugas dengan quiz tapi skor belum tersimpan (data lama),
            // hitung ulang dari jawaban_quiz sebagai fallback
            if ($skorJalurA->isEmpty() && $jawabanTugasAdmin->isNotEmpty()) {
                $jawabanQuiz = \App\Models\JawabanQuiz::where('petugas_id', $petugasId)
                    ->whereIn('tugas_id', $tugasAdminIds)
                    ->with('quiz')
                    ->get();

                if ($jawabanQuiz->isNotEmpty()) {
                    $skorPerTugas = $jawabanQuiz->groupBy('tugas_id')->map(function ($grup) {
                        $total = $grup->count();
                        $benar = $grup->filter(
                            fn($j) => $j->quiz && strtolower($j->jawaban) === strtolower($j->quiz->jawaban)
                        )->count();
                        return $total > 0 ? round(($benar / $total) * 100, 2) : null;
                    })->filter();
                    $skorJalurA = $skorPerTugas->values();
                }
            }

            if ($skorJalurA->isNotEmpty()) {
                $infoAkurasiA = $skorJalurA->count() . ' quiz admin';
            }
        }

        // Jalur B: quiz triwulan dari Koordinator (skor sudah tersimpan di jawaban_triwulan)
        $skorJalurB  = collect();
        $infoAkurasiB = '';

        if ($materiTriwulanIds->isNotEmpty()) {
            $jawabanTW = JawabanTriwulan::where('petugas_id', $petugasId)
                ->whereIn('materi_triwulan_id', $materiTriwulanIds)
                ->where('status', 'sudah')
                ->whereNotNull('skor')
                ->pluck('skor');

            if ($jawabanTW->isNotEmpty()) {
                $skorJalurB  = $jawabanTW;
                $infoAkurasiB = $jawabanTW->count() . ' quiz koordinator';
            }
        }

        $semuaSkor = $skorJalurA->values()->concat($skorJalurB->values());

        $nilaiAkurasi = null;
        $infoAkurasi  = 'Belum ada quiz yang dikerjakan';

        if ($semuaSkor->isNotEmpty()) {
            $nilaiAkurasi = round($semuaSkor->avg(), 2);
            $bagianInfo   = array_filter([$infoAkurasiA, $infoAkurasiB]);
            $infoAkurasi  = 'Rata-rata ' . implode(' + ', $bagianInfo)
                . ', nilai: ' . number_format($nilaiAkurasi, 2);
        }

        // ══════════════════════════════════════════════════════
        // KEBIJAKAN: SHIFT ALPHA TIDAK BOLEH "MENOLONG" NILAI LAIN
        //
        // Jika suatu shift (tanggal+sesi) berstatus alpha — petugas
        // terjadwal tapi sama sekali tidak absen masuk — maka laporan
        // harian / checklist yang disubmit dengan tanggal+sesi yang
        // SAMA tidak dihitung di komponen Tanggungjawab & Kesesuaian
        // Atribut. Ini menutup celah "isi laporan/checklist tapi tidak
        // absen", karena tanpa absen, kehadiran fisik petugas pada
        // shift itu tidak dapat diverifikasi sama sekali.
        //
        // Catatan: laporan_harian menyimpan sesi sebagai 'Pagi'/'Siang'
        // (kapital), sedangkan absensi/checklist pakai 'pagi'/'siang'
        // (huruf kecil) — disamakan ke lowercase saat dibandingkan.
        // ══════════════════════════════════════════════════════
        $shiftAlpha = Absensi::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->where('status_kehadiran', 'alpha')
            ->get()
            ->map(fn($a) => Carbon::parse($a->tanggal)->toDateString() . '|' . strtolower($a->sesi))
            ->flip(); // jadi lookup set: isset($shiftAlpha["tgl|sesi"])

        // ══════════════════════════════════════════════════════
        // 6. TANGGUNGJAWAB PELAYANAN
        //    Sumber: laporan_harian (status submitted/approved)
        //    Target = shift dijadwalkan untuk petugas ini (keterangan != libur)
        //    Fallback ke kalender hari kerja jika jadwal belum diinput
        //    Nilai = (laporan disubmit / target shift) × 100
        //    Laporan pada shift yang alpha TIDAK dihitung (lihat kebijakan di atas).
        // ══════════════════════════════════════════════════════
        $targetShift = JadwalPetugas::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->where('keterangan', '!=', 'libur')
            ->count();

        // Fallback ke kalender hari kerja × 2 sesi jika jadwal belum diinput
        if ($targetShift === 0) {
            foreach ($bulanArr as $bul) {
                $start = Carbon::createFromDate($tahun, $bul, 1);
                $end   = $start->copy()->endOfMonth();
                for ($d = $start->copy(); $d <= $end; $d->addDay()) {
                    if ($d->isWeekday()) $targetShift += 2;
                }
            }
        }

        $laporanSemua = LaporanHarianBaru::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->whereIn('status', ['submitted', 'approved'])
            ->get();

        $laporanValid = $laporanSemua->reject(
            fn($l) => isset($shiftAlpha[Carbon::parse($l->tanggal)->toDateString() . '|' . strtolower($l->sesi)])
        );
        $laporanSubmit         = $laporanValid->count();
        $laporanDiabaikanAlpha = $laporanSemua->count() - $laporanSubmit;

        $nilaiTanggungjawab = $targetShift > 0
            ? round(min(($laporanSubmit / $targetShift) * 100, 100), 2)
            : null;
        $infoLaporan = "{$laporanSubmit} laporan dari {$targetShift} shift dijadwalkan"
            . ($laporanDiabaikanAlpha > 0 ? " ({$laporanDiabaikanAlpha} laporan diabaikan karena shift alpha)" : '');

        // ══════════════════════════════════════════════════════
        // 7. PENAMPILAN & KESESUAIAN ATRIBUT
        //    Sumber: checklist_harian (items JSON, method pctChecked())
        //    Nilai = rata-rata % item checklist yang dicentang per shift
        //    Checklist pada shift yang alpha TIDAK dihitung (lihat kebijakan di atas).
        // ══════════════════════════════════════════════════════
        // Hanya checklist yang sudah disubmit/diverifikasi (bukan draft)
        // Status: 'draft' = belum submit, 'submit' = sudah submit ke koordinator,
        //         'verified' = sudah diverifikasi koordinator
        $checklistSemua = ChecklistHarian::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->whereIn('status', ['submit', 'verified'])
            ->get();

        $checklist = $checklistSemua->reject(
            fn($c) => isset($shiftAlpha[Carbon::parse($c->tanggal)->toDateString() . '|' . strtolower($c->sesi)])
        );
        $checklistDiabaikanAlpha = $checklistSemua->count() - $checklist->count();

        // null hanya jika memang TIDAK ADA shift wajib sama sekali di periode ini
        // (petugas belum mulai bertugas / triwulan belum berjalan untuknya).
        // Jika ada shift wajib tapi nol checklist disubmit, nilainya 0 — bukan
        // diabaikan — supaya "hadir tapi sama sekali tidak kerja" benar-benar
        // berpengaruh menurunkan nilai, bukan hilang dari rata-rata komposit.
        if ($checklist->isNotEmpty()) {
            $nilaiAtribut = round($checklist->avg(fn($c) => $c->pctChecked()), 2);
        } elseif ($targetShift > 0) {
            $nilaiAtribut = 0.0;
        } else {
            $nilaiAtribut = null;
        }
        $infoChecklist = $checklist->count() . ' checklist disubmit'
            . ($checklistDiabaikanAlpha > 0 ? " ({$checklistDiabaikanAlpha} diabaikan karena shift alpha)" : '');

        // ══════════════════════════════════════════════════════
        // 8. KEPATUHAN SOP
        //    Sumber: laporan_harian
        //    Berbeda dari Tanggungjawab:
        //      Tanggungjawab = apakah laporan DISUBMIT (rajin atau tidak)
        //      Kepatuhan SOP = dari laporan yang masuk, berapa yang
        //                      DIAPPROVE koordinator (isi laporan benar/SOP)
        //    Nilai = (approved / submitted) × 100
        // ══════════════════════════════════════════════════════
        $laporanApproved = LaporanHarianBaru::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->where('status', 'approved')
            ->count();

        $laporanMasuk = LaporanHarianBaru::where('user_id', $userId)
            ->whereBetween('tanggal', [$tglMulai->toDateString(), $tglAkhir->toDateString()])
            ->whereIn('status', ['submitted', 'approved'])
            ->count();

        $nilaiKepatuhanSop = null;
        $infoSop           = 'Belum ada laporan yang disubmit';

        if ($laporanMasuk > 0) {
            $nilaiKepatuhanSop = round(($laporanApproved / $laporanMasuk) * 100, 2);
            $infoSop = "{$laporanApproved} dari {$laporanMasuk} laporan disetujui koordinator";
        }

        // ══════════════════════════════════════════════════════
        // 9. KEPUASAN PELANGGAN EKSTERNAL
        //    Sumber utama : survey_kepuasan (jenis=eksternal, status=selesai)
        //    Fallback     : input manual koordinator ($kepuasanManual)
        //    Nilai        = (rata-rata rating / 5) × 100
        // ══════════════════════════════════════════════════════
        // Filter pakai diisi_pada (waktu responden selesai submit),
        // bukan created_at (waktu sesi dibuat/form dibuka).
        // Ini memastikan survey yang dibuka di akhir triwulan tapi
        // baru diisi di awal triwulan berikutnya tidak masuk hitungan salah.
        $surveyEksternal = SurveyKepuasan::where('petugas_id', $petugasId)
            ->where('jenis', 'eksternal')
            ->where('status', 'selesai')
            ->whereBetween('diisi_pada', [$tglMulai, $tglAkhir])
            ->with('jawaban.pertanyaan')
            ->get();

        $allRatings = $surveyEksternal->flatMap(fn($s) => $s->jawaban)
            ->filter(fn($j) => ($j->pertanyaan?->tipe ?? '') === 'rating' && is_numeric($j->jawaban))
            ->map(fn($j) => (float) $j->jawaban);

        $nilaiKepuasan   = null;
        $nilaiKepuasanDb = null; // nilai manual yang disimpan ke DB
        $infoSurveyExt   = 'Belum ada SKM masuk';
        $sumberKepuasan  = 'kosong';

        if ($allRatings->isNotEmpty()) {
            // ── Sumber utama: SKM dari pengunjung ──
            $nilaiKepuasan  = round(($allRatings->avg() / 5) * 100, 2);
            $infoSurveyExt  = $surveyEksternal->count() . ' SKM, rata-rata '
                . round($allRatings->avg(), 2) . '/5';
            $sumberKepuasan = 'skm_eksternal';
        } elseif ($kepuasanManual !== null) {
            // ── Fallback: input manual koordinator ──
            $nilaiKepuasan   = round($kepuasanManual, 2);
            $nilaiKepuasanDb = $nilaiKepuasan;
            $infoSurveyExt   = 'Input manual koordinator';
            $sumberKepuasan  = 'manual';
        } else {
            // ── Tidak ada data sama sekali ──
            $infoSurveyExt  = 'Belum ada SKM masuk';
            $sumberKepuasan = 'kosong';
        }

        // ══════════════════════════════════════════════════════
        // KEMBALIKAN SEMUA NILAI + INFO
        // ══════════════════════════════════════════════════════
        return [
            'kehadiran'             => $nilaiKehadiran,
            'disiplin'              => $nilaiDisiplin,
            'komunikasi'            => $nilaiKomunikasi,
            'kerjasama'             => $nilaiKerjasama,
            'inovatif'              => $nilaiInovatif,
            'kepastian_waktu'       => $nilaiKepastianWaktu,
            'akurasi'               => $nilaiAkurasi,
            'tanggungjawab'         => $nilaiTanggungjawab,
            'kesopanan_keramahan'   => $nilaiKesopananKeramahan,
            'kesesuaian_atribut'    => $nilaiAtribut,
            'kepatuhan_sop'         => $nilaiKepatuhanSop,
            'kepuasan_pelanggan'    => $nilaiKepuasan,
            'kepuasan_manual'       => $nilaiKepuasanDb ?? null,
            'sumber_kepuasan'       => $sumberKepuasan,
            'kategori_tambahan'     => $kategoriTambahanHasil,

            'info_kehadiran'   => $infoKehadiran,
            'info_disiplin'    => $infoDisiplin,
            'info_survey_int'  => $infoSurveyInt,
            'info_kepastian'   => $infoKepastian,
            'info_akurasi'     => $infoAkurasi,
            'info_laporan'     => $infoLaporan,
            'info_checklist'   => $infoChecklist,
            'info_sop'         => $infoSop,
            'info_survey_ext'  => $infoSurveyExt,
            'jumlah_ganti'     => $jumlahGanti,
        ];
    }

    /**
     * Hitung nilai (skala 0-100) untuk satu kategori survey internal,
     * berdasarkan kolom survey_pertanyaan.kategori (bukan tebakan kata kunci).
     *
     * @param \Illuminate\Support\Collection $semuaJawaban Jawaban rating (sudah difilter tipe='rating' & numerik)
     * @param string $kategoriKey Salah satu dari SurveyPertanyaan::KATEGORI_LIST
     */
    private function avgRatingUntukKategori($semuaJawaban, string $kategoriKey): ?float
    {
        $grup = $semuaJawaban->filter(fn($j) => $j->pertanyaan?->kategori === $kategoriKey);

        if ($grup->isEmpty()) {
            return null;
        }

        return round(($grup->avg(fn($j) => (float) $j->jawaban) / 5) * 100, 2);
    }

    // ════════════════════════════════════════════════════════
    // SELESAIKAN SEMUA DRAFT
    // ════════════════════════════════════════════════════════
    public function selesaikanSemua(Request $request)
    {
        $wilayahId = $this->wilayahId();
        $periode   = $request->input('periode', self::periodeSekarang());

        $drafts = EvaluasiPetugas::where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->where('status', 'draft')
            ->whereNotNull('jumlah_nilai')
            ->get();

        $jumlah = 0;
        foreach ($drafts as $ev) {
            $ev->status = 'selesai';
            $ev->hitungKomposit();
            $ev->save();
            $jumlah++;
        }

        if ($jumlah === 0) {
            return back()->with('info', 'Tidak ada evaluasi draft yang siap diselesaikan.');
        }

        return back()->with('success', "{$jumlah} evaluasi berhasil diselesaikan.");
    }
}