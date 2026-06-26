<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model KategoriPenilaian
 *
 * Kategori penilaian dinamis untuk survey internal. Menggantikan
 * konstanta hardcode SurveyPertanyaan::KATEGORI_LIST — admin bisa
 * menambah kategori baru dari UI dan memilih komponen induknya
 * (Sikap Kerja / Indikator Hasil / Indikator Proses / Mutu Pelayanan)
 * tanpa perlu developer mengubah kode.
 *
 * Letak file: app/Models/KategoriPenilaian.php
 * Status     : FILE BARU
 */
class KategoriPenilaian extends Model
{
    protected $table = 'kategori_penilaian';

    protected $fillable = [
        'kode', 'nama', 'komponen', 'urutan', 'is_active', 'sumber',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Label komponen untuk ditampilkan di UI.
     */
    public const KOMPONEN_LIST = [
        'sikap_kerja'       => 'Sikap Kerja',
        'indikator_hasil'   => 'Indikator Hasil',
        'indikator_proses'  => 'Indikator Proses',
        'mutu_pelayanan'    => 'Mutu Pelayanan',
    ];

    public static function labelKomponen(?string $komponen): string
    {
        return self::KOMPONEN_LIST[$komponen] ?? '— Belum ditentukan —';
    }

    /**
     * Daftar kategori aktif dalam bentuk [kode => nama], dipakai untuk
     * dropdown & pengelompokan di view — bentuk array ini SENGAJA dibuat
     * supaya kompatibel dengan view admin.survey.pertanyaan yang sudah
     * ada (sebelumnya memakai SurveyPertanyaan::KATEGORI_LIST), tanpa
     * perlu mengubah satu baris pun di Blade.
     */
    public static function listAktifUntukForm(): array
    {
        return self::where('is_active', true)
            ->orderBy('urutan')
            ->pluck('nama', 'kode')
            ->toArray();
    }

    /**
     * Sama seperti listAktifUntukForm() tapi mengembalikan model penuh,
     * dikelompokkan per komponen. Dipakai di halaman pengaturan kategori
     * dan untuk perhitungan komposit per komponen.
     */
    public static function aktifDikelompokkanPerKomponen()
    {
        return self::where('is_active', true)
            ->orderBy('urutan')
            ->get()
            ->groupBy('komponen');
    }

    // ── Relasi ─────────────────────────────────────────────────────

    public function pertanyaan()
    {
        return $this->hasMany(SurveyPertanyaan::class, 'kategori', 'kode');
    }

    public function nilaiEvaluasi()
    {
        return $this->hasMany(EvaluasiKategoriNilai::class);
    }
}