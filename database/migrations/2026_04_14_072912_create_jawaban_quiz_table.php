<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel ini menyimpan pilihan jawaban petugas untuk setiap soal quiz.
 * Berbeda dengan tabel 'jawaban' yang menyimpan skor & status per tugas,
 * tabel ini menyimpan detail per soal (opsi mana yang dipilih).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_quiz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->onDelete('cascade');
            $table->foreignId('petugas_id')->constrained('petugas')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quiz')->onDelete('cascade');
            // Nilai yang dipilih: 'a', 'b', 'c', atau 'd'
            $table->string('jawaban', 1)->nullable();
            $table->timestamps();

            // Satu petugas hanya boleh menjawab satu soal satu kali per tugas
            $table->unique(['tugas_id', 'petugas_id', 'quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_quiz');
    }
};