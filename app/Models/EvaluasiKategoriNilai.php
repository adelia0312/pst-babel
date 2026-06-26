<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model EvaluasiKategoriNilai
 *
 * Menyimpan nilai kategori TAMBAHAN (kategori baru yang dibuat admin,
 * di luar 4 kategori bawaan yang masih punya kolom sendiri di
 * evaluasi_petugas seperti nilai_komunikasi, dst).
 *
 * PENTING: nama_kategori_snapshot & komponen_snapshot di-isi pada saat
 * baris ini dibuat (lihat EvaluasiPetugas::simpanNilaiKategoriTambahan)
 * dan TIDAK ikut berubah walaupun admin nanti mengedit nama/komponen
 * kategori aslinya di tabel kategori_penilaian. Ini yang menjaga grafik
 * histori per triwulan tetap akurat sesuai keadaan saat triwulan itu
 * dihitung.
 *
 * Letak file: app/Models/EvaluasiKategoriNilai.php
 * Status     : FILE BARU
 */
class EvaluasiKategoriNilai extends Model
{
    protected $table = 'evaluasi_kategori_nilai';

    protected $fillable = [
        'evaluasi_petugas_id',
        'kategori_penilaian_id',
        'kategori_kode_snapshot',
        'nama_kategori_snapshot',
        'komponen_snapshot',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'float',
    ];

    public function evaluasiPetugas()
    {
        return $this->belongsTo(EvaluasiPetugas::class);
    }

    public function kategoriPenilaian()
    {
        return $this->belongsTo(KategoriPenilaian::class);
    }
}