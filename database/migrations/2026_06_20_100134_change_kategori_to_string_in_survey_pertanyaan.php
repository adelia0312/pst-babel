<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Ubah survey_pertanyaan.kategori dari ENUM ke VARCHAR
 *
 * Latar belakang:
 *   Kolom kategori sebelumnya ENUM('komunikasi','kerja_sama','inovatif',
 *   'kesopanan_keramahan'). Karena kategori sekarang adalah data dinamis
 *   di tabel kategori_penilaian (lihat migration
 *   2026_06_20_090000_create_kategori_penilaian_table), kolom ini harus
 *   bisa menampung kode kategori APA PUN yang admin buat — bukan hanya
 *   4 pilihan tetap.
 *
 *   Catatan teknis: project ini TIDAK memasang doctrine/dbal, sehingga
 *   $table->string('kategori')->change() akan gagal/error di Laravel.
 *   Migration ini sengaja memakai DB::statement (raw SQL MODIFY COLUMN)
 *   supaya tidak perlu menambah dependency baru ataupun mengubah
 *   composer.json — sesuai permintaan untuk tidak mengubah konfigurasi
 *   apa pun di luar yang diperlukan.
 *
 *   Data lama otomatis aman: MODIFY COLUMN dari ENUM ke VARCHAR tidak
 *   mengubah nilai yang sudah tersimpan, hanya tipe kolomnya.
 *
 * Letak file: database/migrations/2026_06_20_090200_change_kategori_to_string_in_survey_pertanyaan.php
 * Status     : FILE BARU
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE survey_pertanyaan
             MODIFY COLUMN kategori VARCHAR(50) NULL
             COMMENT 'Kode kategori penilaian (lihat tabel kategori_penilaian). Null untuk pertanyaan eksternal.'"
        );
    }

    public function down(): void
    {
        // Kembalikan ke ENUM 4 nilai lama. Jika ada data dengan kode
        // kategori baru (di luar 4 nilai ini) saat rollback dijalankan,
        // MySQL akan otomatis menyimpannya sebagai '' (nilai enum tidak
        // valid) — risiko ini disengaja karena rollback berarti memang
        // ingin kembali ke skema lama yang hanya mendukung 4 kategori.
        DB::statement(
            "ALTER TABLE survey_pertanyaan
             MODIFY COLUMN kategori ENUM('komunikasi','kerja_sama','inovatif','kesopanan_keramahan') NULL
             COMMENT 'Kategori penilaian untuk survey internal. Null untuk pertanyaan eksternal.'"
        );
    }
};