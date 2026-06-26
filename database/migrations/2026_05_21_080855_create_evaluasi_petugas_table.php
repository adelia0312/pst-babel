<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_petugas', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('petugas_id')->constrained('petugas')->onDelete('cascade');
            $table->foreignId('koordinator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wilayah_id')->constrained('wilayah')->onDelete('cascade');

            // Periode evaluasi
            $table->string('periode');        // e.g. "2026-05" (YYYY-MM) atau "2026-Q2"
            $table->enum('tipe_periode', ['bulanan', 'triwulan'])->default('bulanan');
            $table->date('tanggal_evaluasi');

            // ── I. SIKAP KERJA ──────────────────────────────────
            // Otomatis dari sistem
            $table->decimal('nilai_kehadiran',  5, 2)->nullable();
            $table->decimal('nilai_disiplin',   5, 2)->nullable();
            // Manual oleh koordinator
            $table->decimal('nilai_komunikasi', 5, 2)->nullable();
            $table->decimal('nilai_kerjasama',  5, 2)->nullable();
            $table->decimal('nilai_inovatif',   5, 2)->nullable();

            // ── II.A KINERJA INDIKATOR HASIL ────────────────────
            // Otomatis
            $table->decimal('nilai_kepastian_waktu', 5, 2)->nullable();
            // Manual
            $table->decimal('nilai_akurasi',         5, 2)->nullable();

            // ── II.B KINERJA INDIKATOR PROSES ───────────────────
            // Manual
            $table->decimal('nilai_kejelasan',              5, 2)->nullable();
            // Otomatis (tanggungjawab dari laporan shift)
            $table->decimal('nilai_tanggungjawab',          5, 2)->nullable();
            // Manual
            $table->decimal('nilai_kelengkapan_sarpras',    5, 2)->nullable();
            $table->decimal('nilai_kesopanan',              5, 2)->nullable();
            $table->decimal('nilai_keramahan',              5, 2)->nullable();
            $table->decimal('nilai_kesesuaian_atribut',     5, 2)->nullable();

            // ── III. MUTU PELAYANAN ──────────────────────────────
            // Otomatis
            $table->decimal('nilai_kepatuhan_sop',          5, 2)->nullable();
            // Manual
            $table->decimal('nilai_kepuasan_pelanggan',     5, 2)->nullable();

            // ── NILAI KOMPOSIT (dihitung otomatis) ───────────────
            $table->decimal('rata_sikap_kerja',        5, 2)->nullable();
            $table->decimal('rata_indikator_hasil',    5, 2)->nullable();
            $table->decimal('rata_indikator_proses',   5, 2)->nullable();
            $table->decimal('rata_mutu_pelayanan',     5, 2)->nullable();
            $table->decimal('jumlah_nilai',            5, 2)->nullable();
            $table->string('grade', 2)->nullable(); // SB, B, C, K, SK

            // ── META ─────────────────────────────────────────────
            $table->enum('status', ['draft', 'selesai'])->default('draft');
            $table->text('catatan')->nullable();

            $table->timestamps();

            // Satu koordinator hanya bisa submit 1 evaluasi per petugas per periode
            $table->unique(['petugas_id', 'periode', 'tipe_periode'], 'unique_eval_periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_petugas');
    }
};