<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menampung banyak file lampiran per MateriTriwulan (materi
     * triwulan koordinator). Kolom `file` di tabel `materi_triwulan` tetap
     * dipertahankan agar data lama yang sudah tersimpan sebelum perubahan
     * ini tidak hilang / tetap bisa ditampilkan.
     */
    public function up(): void
    {
        Schema::create('materi_triwulan_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('materi_triwulan_id');
            $table->string('file');           // path di storage (disk: public)
            $table->string('nama_asli')->nullable(); // nama asli file saat diupload
            $table->timestamps();

            $table->foreign('materi_triwulan_id')->references('id')->on('materi_triwulan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_triwulan_files');
    }
};