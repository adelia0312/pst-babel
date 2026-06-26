<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Tambah kolom `kategori` ke survey_pertanyaan
 *            + Gabung nilai_kesopanan & nilai_keramahan jadi 1 kolom
 *              nilai_kesopanan_keramahan di evaluasi_petugas
 *
 * Latar belakang:
 *   Sebelumnya, kategori pertanyaan survey internal (Komunikasi, Kerja
 *   Sama, Inovatif, Kesopanan & Keramahan) ditebak otomatis dari kata
 *   kunci di teks pertanyaan. Ini tidak akurat — jika admin menulis
 *   pertanyaan tanpa kata kunci yang dikenali, semua kategori jatuh ke
 *   nilai rata-rata yang sama (lihat NilaiEvaluasiController lama).
 *
 *   Sekarang admin WAJIB memilih kategori saat membuat/mengedit
 *   pertanyaan internal, sehingga pemetaan kategori → pertanyaan
 *   eksplisit, bukan tebakan.
 *
 *   Kesopanan & Keramahan digabung menjadi SATU kategori (sesuai
 *   keputusan internal BPS Babel), karena dalam praktiknya selalu
 *   dinilai bersama dan sumber datanya sama.
 *
 * Letak file: database/migrations/2026_06_19_100000_add_kategori_dan_gabung_kesopanan_keramahan.php
 * Status     : FILE BARU
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tambah kolom kategori ke survey_pertanyaan ─────────
        // Nullable karena kolom ini hanya relevan untuk pertanyaan
        // jenis 'internal'/'semua'. Pertanyaan eksternal tidak perlu
        // kategori (kepuasan pelanggan dihitung sebagai satu nilai).
        Schema::table('survey_pertanyaan', function (Blueprint $table) {
            $table->enum('kategori', [
                'komunikasi',
                'kerja_sama',
                'inovatif',
                'kesopanan_keramahan',
            ])->nullable()->after('jenis')
              ->comment('Kategori penilaian untuk survey internal. Null untuk pertanyaan eksternal.');
        });

        // ── 2. Gabung nilai_kesopanan & nilai_keramahan ───────────
        Schema::table('evaluasi_petugas', function (Blueprint $table) {
            $table->decimal('nilai_kesopanan_keramahan', 5, 2)
                  ->nullable()
                  ->after('nilai_tanggungjawab')
                  ->comment('Gabungan Kesopanan & Keramahan (1 kategori survey internal)');
        });

        // Migrasi data lama: ambil rata-rata dari nilai_kesopanan & nilai_keramahan
        // yang sudah tersimpan, supaya histori evaluasi sebelumnya tidak hilang.
        // Dibungkus pengecekan Schema::hasColumn supaya migration ini tetap aman
        // dijalankan meskipun kolom lama sudah tidak ada (misal di instalasi baru).
        if (Schema::hasColumn('evaluasi_petugas', 'nilai_kesopanan')
            && Schema::hasColumn('evaluasi_petugas', 'nilai_keramahan')) {
            DB::table('evaluasi_petugas')
                ->select('id', 'nilai_kesopanan', 'nilai_keramahan')
                ->where(function ($q) {
                    $q->whereNotNull('nilai_kesopanan')
                      ->orWhereNotNull('nilai_keramahan');
                })
                ->orderBy('id')
                ->get()
                ->each(function ($row) {
                    $values = array_filter(
                        [$row->nilai_kesopanan, $row->nilai_keramahan],
                        fn($v) => $v !== null
                    );
                    if (empty($values)) {
                        return;
                    }
                    $avg = round(array_sum($values) / count($values), 2);
                    DB::table('evaluasi_petugas')
                        ->where('id', $row->id)
                        ->update(['nilai_kesopanan_keramahan' => $avg]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('survey_pertanyaan', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });

        Schema::table('evaluasi_petugas', function (Blueprint $table) {
            $table->dropColumn('nilai_kesopanan_keramahan');
        });
    }
};