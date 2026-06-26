<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menampung banyak file lampiran per Tugas (materi reguler admin).
     * Kolom `file` di tabel `tugas` tetap dipertahankan agar data lama yang sudah
     * tersimpan sebelum perubahan ini tidak hilang / tetap bisa ditampilkan.
     */
    public function up(): void
    {
        Schema::create('tugas_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tugas_id');
            $table->string('file');           // path di storage (disk: public)
            $table->string('nama_asli')->nullable(); // nama asli file saat diupload
            $table->timestamps();

            $table->foreign('tugas_id')->references('id')->on('tugas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_files');
    }
};