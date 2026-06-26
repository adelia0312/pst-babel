<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Materi/Quiz yang dibuat koordinator per triwulan
        Schema::create('materi_triwulan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wilayah_id');
            $table->unsignedBigInteger('koordinator_id'); // user_id koordinator
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('periode'); // e.g. "2026-TW2"
            $table->string('file')->nullable();
            $table->string('link')->nullable();
            $table->timestamps();
        });

        // Soal quiz untuk materi triwulan
        Schema::create('quiz_triwulan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('materi_triwulan_id');
            $table->text('pertanyaan');
            $table->string('opsi_a')->nullable();
            $table->string('opsi_b')->nullable();
            $table->string('opsi_c')->nullable();
            $table->string('opsi_d')->nullable();
            $table->string('jawaban'); // kunci jawaban: a/b/c/d
            $table->timestamps();
        });

        // Jawaban petugas untuk quiz triwulan
        Schema::create('jawaban_triwulan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('materi_triwulan_id');
            $table->unsignedBigInteger('petugas_id');
            $table->string('periode');
            $table->enum('status', ['belum', 'sudah'])->default('belum');
            $table->integer('skor')->nullable(); // 0-100
            $table->json('jawaban_detail')->nullable(); // pilihan per soal
            $table->timestamp('dikerjakan_at')->nullable();
            $table->timestamps();

            $table->unique(['materi_triwulan_id', 'petugas_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_triwulan');
        Schema::dropIfExists('quiz_triwulan');
        Schema::dropIfExists('materi_triwulan');
    }
};