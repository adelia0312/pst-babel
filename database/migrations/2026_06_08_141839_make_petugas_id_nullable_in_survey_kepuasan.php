<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jadikan petugas_id nullable di tabel survey_kepuasan.
 *
 * Alasan: survey bisa masuk tanpa jadwal petugas hari ini
 * (responden scan barcode/link saat tidak ada petugas terjadwal).
 * Controller sudah mengisi petugas_id = null dalam kasus ini,
 * tapi kolom DB masih NOT NULL → error 1048.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_kepuasan', function (Blueprint $table) {
            // Lepas foreign key lama dulu (nama constraint default Laravel)
            $table->dropForeign(['petugas_id']);

            // Ubah kolom jadi nullable, lalu pasang kembali FK
            $table->unsignedBigInteger('petugas_id')->nullable()->change();

            $table->foreign('petugas_id')
                  ->references('id')
                  ->on('petugas')
                  ->onDelete('set null');   // kalau petugas dihapus, set null bukan cascade
        });
    }

    public function down(): void
    {
        Schema::table('survey_kepuasan', function (Blueprint $table) {
            $table->dropForeign(['petugas_id']);

            $table->unsignedBigInteger('petugas_id')->nullable(false)->change();

            $table->foreign('petugas_id')
                  ->references('id')
                  ->on('petugas')
                  ->onDelete('cascade');
        });
    }
};