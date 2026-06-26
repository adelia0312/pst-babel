<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluasiPetugas extends Model
{
    protected $table = 'evaluasi_petugas';

    protected $fillable = [
        'petugas_id', 'koordinator_id', 'wilayah_id',
        'periode', 'tipe_periode', 'tanggal_evaluasi',
        // Sikap kerja
        'nilai_kehadiran', 'nilai_disiplin',
        'nilai_komunikasi', 'nilai_kerjasama', 'nilai_inovatif',
        // Indikator hasil
        'nilai_kepastian_waktu', 'nilai_akurasi',
        // Indikator proses
        'nilai_kejelasan', 'nilai_tanggungjawab',
        'nilai_kelengkapan_sarpras',
        'nilai_kesopanan_keramahan', // ← gabungan Kesopanan & Keramahan (1 kategori survey internal)
        'nilai_kesesuaian_atribut',
        // Mutu pelayanan
        'nilai_kepatuhan_sop', 'nilai_kepuasan_pelanggan',
        'nilai_kepuasan_manual', 'sumber_kepuasan',
        // Komposit
        'rata_sikap_kerja', 'rata_indikator_hasil',
        'rata_indikator_proses', 'rata_mutu_pelayanan',
        'jumlah_nilai', 'grade',
        // Meta
        'status', 'catatan',
    ];

    protected $casts = [
        'tanggal_evaluasi'          => 'date',
        'nilai_kehadiran'           => 'float',
        'nilai_disiplin'            => 'float',
        'nilai_komunikasi'          => 'float',
        'nilai_kerjasama'           => 'float',
        'nilai_inovatif'            => 'float',
        'nilai_kepastian_waktu'     => 'float',
        'nilai_akurasi'             => 'float',
        'nilai_kejelasan'           => 'float',
        'nilai_tanggungjawab'       => 'float',
        'nilai_kelengkapan_sarpras' => 'float',
        'nilai_kesopanan_keramahan' => 'float',
        'nilai_kesesuaian_atribut'  => 'float',
        'nilai_kepatuhan_sop'       => 'float',
        'nilai_kepuasan_pelanggan'  => 'float',
        'nilai_kepuasan_manual'     => 'float',
        'rata_sikap_kerja'          => 'float',
        'rata_indikator_hasil'      => 'float',
        'rata_indikator_proses'     => 'float',
        'rata_mutu_pelayanan'       => 'float',
        'jumlah_nilai'              => 'float',
    ];

    // ── Relasi ────────────────────────────────────────────────
    public function petugas()
    {
        return $this->belongsTo(Petugas::class);
    }

    public function koordinator()
    {
        return $this->belongsTo(User::class, 'koordinator_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    /**
     * Nilai kategori TAMBAHAN (di luar 4 kategori bawaan yang sudah
     * punya kolom sendiri seperti nilai_komunikasi, dst). Setiap baris
     * menyimpan snapshot nama & komponen kategori pada saat triwulan
     * ini dihitung — lihat App\Models\EvaluasiKategoriNilai.
     */
    public function kategoriNilai()
    {
        return $this->hasMany(EvaluasiKategoriNilai::class);
    }

    // ── Grade helper ──────────────────────────────────────────
    // Sesuai keterangan Excel resmi BPS Babel:
    //   SB > 95 | B 86–95 | C 66–85 | K 51–65 | SK < 50
    public static function hitungGrade(?float $nilai): string
    {
        if ($nilai === null) return '-';
        if ($nilai > 95)    return 'SB';
        if ($nilai >= 86)   return 'B';
        if ($nilai >= 66)   return 'C';
        if ($nilai >= 51)   return 'K';
        return 'SK';
    }

    public static function labelGrade(string $grade): string
    {
        return match($grade) {
            'SB' => 'Sangat Baik',
            'B'  => 'Baik',
            'C'  => 'Cukup',
            'K'  => 'Kurang',
            'SK' => 'Sangat Kurang',
            default => '-',
        };
    }

    // ── Hitung komposit ───────────────────────────────────────
    //
    // Mengikuti rumus Excel resmi BPS Babel (Triwulan I 2026):
    //
    //   rata_sikap_kerja      = avg(kehadiran, disiplin, komunikasi, kerjasama, inovatif, [kategori tambahan di komponen ini])
    //   rata_indikator_hasil  = avg(kepastian_waktu, akurasi, [kategori tambahan di komponen ini])
    //   rata_indikator_proses = avg(tanggungjawab, kesopanan_keramahan, kesesuaian_atribut, [kategori tambahan di komponen ini])
    //   rata_mutu_pelayanan   = avg(kepatuhan_sop, kepuasan_pelanggan, [kategori tambahan di komponen ini])
    //
    //   jumlah_nilai = avg(rata_sikap, rata_hasil, rata_proses, rata_mutu)
    //                — rata-rata biasa dari 4 komponen, tanpa pembobotan —
    //
    // Catatan: Kesopanan & Keramahan digabung jadi 1 kategori survey
    // internal (bukan lagi 2 kolom terpisah), karena keduanya selalu
    // dinilai bersama oleh rekan kerja lewat pertanyaan kategori yang
    // sama. Lihat SurveyPertanyaan::KATEGORI_LIST().
    //
    // CATATAN (2026-06-20): selain kolom nilai_* tetap di atas, sebuah
    // evaluasi bisa juga punya nilai dari KATEGORI TAMBAHAN yang admin
    // buat lewat menu Kategori Penilaian — disimpan sebagai baris di
    // tabel evaluasi_kategori_nilai (relasi kategoriNilai()), bukan
    // kolom baru di sini. Setiap baris itu menyimpan SNAPSHOT komponen
    // induknya saat triwulan tersebut dihitung (komponen_snapshot),
    // sehingga jika baris ini sudah ada DAN evaluasi belum tersimpan ke
    // DB (model baru/belum punya id), relasi tidak dipanggil — ini
    // memastikan evaluasi yang belum pernah punya kategori tambahan
    // hasil hitungnya TIDAK berubah dari sebelumnya.
    //
    // Label "30%/25%/25%/20%" di tampilan hanya keterangan informatif
    // dari dokumen kebijakan, bukan dipakai dalam perhitungan ini.
    // ─────────────────────────────────────────────────────────
    public function hitungKomposit(): void
    {
        // Ambil nilai kategori tambahan (jika evaluasi ini sudah tersimpan
        // dan punya baris di evaluasi_kategori_nilai). Dikelompokkan per
        // komponen_snapshot supaya tinggal digabung ke avgOf() masing-masing
        // komponen di bawah.
        $tambahanPerKomponen = $this->exists
            ? $this->kategoriNilai()->whereNotNull('nilai')->get()->groupBy('komponen_snapshot')
            : collect();

        $nilaiTambahan = fn(string $komponen) => $tambahanPerKomponen
            ->get($komponen, collect())
            ->pluck('nilai')
            ->all();

        // Rata-rata sub-indikator per komponen
        $this->rata_sikap_kerja = $this->avgOf([
            $this->nilai_kehadiran,
            $this->nilai_disiplin,
            $this->nilai_komunikasi,
            $this->nilai_kerjasama,
            $this->nilai_inovatif,
            ...$nilaiTambahan('sikap_kerja'),
        ]);

        $this->rata_indikator_hasil = $this->avgOf([
            $this->nilai_kepastian_waktu,
            $this->nilai_akurasi,
            ...$nilaiTambahan('indikator_hasil'),
        ]);

        $this->rata_indikator_proses = $this->avgOf([
            $this->nilai_tanggungjawab,
            $this->nilai_kesopanan_keramahan,
            $this->nilai_kesesuaian_atribut,
            ...$nilaiTambahan('indikator_proses'),
        ]);

        $this->rata_mutu_pelayanan = $this->avgOf([
            $this->nilai_kepatuhan_sop,
            $this->nilai_kepuasan_pelanggan,
            ...$nilaiTambahan('mutu_pelayanan'),
        ]);

        // Jumlah nilai = rata-rata biasa dari 4 komponen (sesuai Excel)
        $this->jumlah_nilai = $this->avgOf([
            $this->rata_sikap_kerja,
            $this->rata_indikator_hasil,
            $this->rata_indikator_proses,
            $this->rata_mutu_pelayanan,
        ]);

        $this->grade = self::hitungGrade($this->jumlah_nilai);
    }

    // Helper: rata-rata dari array, abaikan null
    private function avgOf(array $values): ?float
    {
        $valid = collect($values)->filter(fn($v) => $v !== null);
        return $valid->isNotEmpty() ? round($valid->avg(), 4) : null;
    }
}