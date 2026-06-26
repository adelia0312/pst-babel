<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel `evaluasi_kategori_nilai`
 *
 * Latar belakang:
 *   Kolom nilai_komunikasi, nilai_kerjasama, dst di evaluasi_petugas
 *   adalah kolom TETAP — setiap kategori baru dari mitra butuh
 *   migration + ubah kode untuk tambah kolom baru. Itu tidak praktis
 *   kalau admin ingin bebas menambah kategori sendiri.
 *
 *   Tabel ini menyimpan nilai untuk KATEGORI TAMBAHAN (di luar 4
 *   kategori bawaan yang sudah punya kolom sendiri di evaluasi_petugas)
 *   sebagai baris, bukan kolom. Kategori baru = tambah baris kategori
 *   di kategori_penilaian, tidak perlu migration lagi.
 *
 *   PENTING — snapshot histori:
 *   nama_kategori_snapshot dan komponen_snapshot di-copy dari
 *   kategori_penilaian PADA SAAT nilai dihitung & disimpan untuk
 *   triwulan tersebut. Jika admin nanti mengedit nama kategori atau
 *   memindahkan komponen induknya, data evaluasi triwulan yang SUDAH
 *   tersimpan tidak ikut berubah — grafik histori per triwulan tetap
 *   menampilkan label & komponen yang berlaku saat triwulan itu
 *   dihitung, bukan yang berlaku sekarang.
 *
 * Letak file: database/migrations/2026_06_20_090100_create_evaluasi_kategori_nilai_table.php
 * Status     : FILE BARU
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_kategori_nilai', function (Blueprint $table) {
            $table->id();

            $table->foreignId('evaluasi_petugas_id')
                ->constrained('evaluasi_petugas')
                ->onDelete('cascade');

            // Referensi ke kategori saat ini. Nullable + nullOnDelete supaya
            // jika kategori suatu saat benar-benar dihapus dari master data,
            // riwayat nilai triwulan lama TIDAK ikut terhapus (cascade) —
            // hanya kehilangan link ke master, snapshot di bawah tetap utuh.
            $table->foreignId('kategori_penilaian_id')
                ->nullable()
                ->constrained('kategori_penilaian')
                ->nullOnDelete();

            // ── Snapshot (lihat catatan di atas) ──
            $table->string('kategori_kode_snapshot', 50);
            $table->string('nama_kategori_snapshot', 100);
            $table->enum('komponen_snapshot', [
                'sikap_kerja',
                'indikator_hasil',
                'indikator_proses',
                'mutu_pelayanan',
            ]);

            $table->decimal('nilai', 5, 2)->nullable();

            $table->timestamps();

            // Satu kategori hanya bisa punya 1 nilai per evaluasi
            $table->unique(
                ['evaluasi_petugas_id', 'kategori_kode_snapshot'],
                'unique_eval_kategori'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_kategori_nilai');
    }
};