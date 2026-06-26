<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model SurveyPertanyaan
 *
 * Letak file: app/Models/SurveyPertanyaan.php
 * Status     : FILE LAMA — diperbarui (tambah 'jenis' di fillable)
 */
class SurveyPertanyaan extends Model
{
    protected $table = 'survey_pertanyaan';

    protected $fillable = [
        'pertanyaan', 'tipe', 'opsi_pilihan', 'urutan', 'is_active',
        'jenis', // ← 'eksternal' | 'internal' | 'semua'
        'kategori', // ← BARU: 'komunikasi' | 'kerja_sama' | 'inovatif' | 'kesopanan_keramahan' (khusus internal)
    ];

    protected $casts = [
        'opsi_pilihan' => 'array',
        'is_active'    => 'boolean',
    ];

    /**
     * CATATAN PERUBAHAN (2026-06-20):
     * KATEGORI_LIST sebelumnya adalah konstanta hardcode 4 kategori.
     * Sekarang kategori adalah data dinamis di tabel kategori_penilaian
     * (lihat App\Models\KategoriPenilaian) supaya admin bisa menambah
     * kategori baru dari UI tanpa developer mengubah kode.
     *
     * KATEGORI_LIST dipertahankan sebagai METHOD STATIS (bukan const)
     * dengan nama sama persis dan mengembalikan bentuk array yang sama
     * ([kode => nama]), supaya semua pemanggilan lama seperti
     * SurveyPertanyaan::KATEGORI_LIST tetap berfungsi.
     *
     * PHP mengizinkan const diakses sebagai properti statis, tetapi
     * TIDAK mengizinkan const dipanggil sebagai method — karena kode
     * lama memakainya sebagai array literal (mis. array_keys(...),
     * implode(',', ...)), kita ganti jadi method dan sesuaikan SETIAP
     * pemanggilan KATEGORI_LIST di controller (lihat
     * AdminSurveyController & NilaiEvaluasiController) agar memanggil
     * KATEGORI_LIST() sebagai method, bukan konstanta.
     */
    public static function KATEGORI_LIST(): array
    {
        return \App\Models\KategoriPenilaian::listAktifUntukForm();
    }

    public static function labelKategori(?string $kategori): string
    {
        if (!$kategori) {
            return '— Belum diberi kategori —';
        }

        $list = self::KATEGORI_LIST();
        return $list[$kategori] ?? '— Belum diberi kategori —';
    }

    // ── Relasi ─────────────────────────────────────────────────────

    public function jawaban()
    {
        return $this->hasMany(SurveyJawaban::class, 'pertanyaan_id');
    }

    // ── Scope helper ───────────────────────────────────────────────

    /**
     * Ambil pertanyaan yang berlaku untuk survey eksternal.
     * (jenis = 'eksternal' atau 'semua')
     */
    public function scopeUntukEksternal($query)
    {
        return $query->whereIn('jenis', ['eksternal', 'semua']);
    }

    /**
     * Ambil pertanyaan yang berlaku untuk survey internal.
     * (jenis = 'internal' atau 'semua')
     */
    public function scopeUntukInternal($query)
    {
        return $query->whereIn('jenis', ['internal', 'semua']);
    }
}