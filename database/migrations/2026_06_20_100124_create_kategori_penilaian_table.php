<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Buat tabel `kategori_penilaian`
 *
 * Latar belakang:
 *   Sebelumnya, kategori pertanyaan survey internal (Komunikasi, Kerja
 *   Sama, Inovatif, Kesopanan & Keramahan) hanya berupa ENUM tetap di
 *   kolom survey_pertanyaan.kategori + konstanta hardcode di
 *   SurveyPertanyaan::KATEGORI_LIST. Akibatnya, setiap kali ada
 *   kategori baru dari mitra/internal, developer harus ubah enum,
 *   ubah kode, dan tambah kolom baru di evaluasi_petugas.
 *
 *   Tabel ini membuat kategori jadi data, bukan kode. Admin bisa
 *   menambah kategori baru dari UI dan langsung menentukan kategori
 *   itu masuk ke komponen penilaian mana (Sikap Kerja / Indikator
 *   Hasil / Indikator Proses / Mutu Pelayanan).
 *
 *   Baris untuk 4 kategori lama (komunikasi, kerja_sama, inovatif,
 *   kesopanan_keramahan) di-seed otomatis di migration ini supaya
 *   data lama tetap konsisten dan tidak ada downtime fitur.
 *
 * Letak file: database/migrations/2026_06_20_090000_create_kategori_penilaian_table.php
 * Status     : FILE BARU
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_penilaian', function (Blueprint $table) {
            $table->id();

            // Kode unik dipakai sebagai "value" di form & dicocokkan dengan
            // survey_pertanyaan.kategori (tetap string, bukan foreign id,
            // supaya kode lama yang membandingkan string kategori tidak perlu
            // diubah sama sekali).
            $table->string('kode', 50)->unique();
            $table->string('nama', 100);

            // Komponen induk penilaian. Tetap berupa 4 pilihan baku karena
            // struktur besar (Sikap Kerja, Indikator Hasil, Indikator
            // Proses, Mutu Pelayanan) sudah ditetapkan dalam kebijakan BPS
            // Babel dan jarang berubah — beda dengan kategori di dalamnya
            // yang memang perlu bisa bertambah.
            $table->enum('komponen', [
                'sikap_kerja',
                'indikator_hasil',
                'indikator_proses',
                'mutu_pelayanan',
            ]);

            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);

            // Sumber: 'bawaan' (4 kategori lama, sudah punya kolom tetap di
            // evaluasi_petugas) atau 'tambahan' (kategori baru dari mitra,
            // nilainya disimpan di tabel evaluasi_kategori_nilai, bukan
            // kolom evaluasi_petugas).
            $table->enum('sumber', ['bawaan', 'tambahan'])->default('tambahan');

            $table->timestamps();
        });

        // ── Seed 4 kategori lama supaya data & UI tetap konsisten ──────
        $now = now();
        DB::table('kategori_penilaian')->insert([
            [
                'kode' => 'komunikasi', 'nama' => 'Komunikasi',
                'komponen' => 'sikap_kerja', 'urutan' => 1,
                'is_active' => true, 'sumber' => 'bawaan',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'kode' => 'kerja_sama', 'nama' => 'Kerja Sama',
                'komponen' => 'sikap_kerja', 'urutan' => 2,
                'is_active' => true, 'sumber' => 'bawaan',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'kode' => 'inovatif', 'nama' => 'Inovatif',
                'komponen' => 'sikap_kerja', 'urutan' => 3,
                'is_active' => true, 'sumber' => 'bawaan',
                'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'kode' => 'kesopanan_keramahan', 'nama' => 'Kesopanan & Keramahan',
                'komponen' => 'indikator_proses', 'urutan' => 4,
                'is_active' => true, 'sumber' => 'bawaan',
                'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_penilaian');
    }
};