<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom berlaku_mulai ke tabel laporan_templates.
     *
     * Kolom ini mencatat tanggal pertanyaan mulai berlaku.
     * - NULL  : pertanyaan lama (sebelum fitur ini ada), berlaku untuk semua laporan
     * - Date  : pertanyaan hanya muncul di laporan dengan tanggal >= berlaku_mulai
     *
     * Dengan ini, pertanyaan yang ditambah di bulan Juni tidak akan muncul
     * di form/tampilan laporan petugas yang tanggalnya masih bulan Mei.
     */
    public function up(): void
    {
        Schema::table('laporan_templates', function (Blueprint $table) {
            // Ditambah setelah kolom 'aktif', nullable agar data lama tetap valid
            $table->date('berlaku_mulai')->nullable()->after('aktif')
                  ->comment('Tanggal pertanyaan mulai berlaku. NULL = berlaku untuk semua tanggal (pertanyaan lama).');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_templates', function (Blueprint $table) {
            $table->dropColumn('berlaku_mulai');
        });
    }
};