<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah dua kolom baru di tabel evaluasi_petugas:
 *
 *  nilai_kepuasan_manual  — nilai yang diisi koordinator secara manual
 *                           jika tidak ada SKM dari pengunjung (nullable)
 *
 *  sumber_kepuasan        — keterangan sumber nilai kepuasan_pelanggan:
 *                           'skm_eksternal' | 'manual' | 'kosong'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluasi_petugas', function (Blueprint $table) {
            $table->float('nilai_kepuasan_manual')
                ->nullable()
                ->after('nilai_kepuasan_pelanggan')
                ->comment('Input manual koordinator jika SKM eksternal kosong (0–100)');

            $table->string('sumber_kepuasan', 30)
                ->nullable()
                ->after('nilai_kepuasan_manual')
                ->comment('skm_eksternal | manual | kosong');
        });
    }

    public function down(): void
    {
        Schema::table('evaluasi_petugas', function (Blueprint $table) {
            $table->dropColumn(['nilai_kepuasan_manual', 'sumber_kepuasan']);
        });
    }
};