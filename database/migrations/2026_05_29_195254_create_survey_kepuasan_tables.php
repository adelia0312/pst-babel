<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan');
            $table->enum('tipe', ['rating', 'pilihan', 'teks'])->default('rating');
            $table->json('opsi_pilihan')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('survey_kepuasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petugas_id')->constrained('petugas')->onDelete('cascade');
            $table->string('nama_responden')->nullable();
            $table->string('periode');
            $table->string('token')->unique();
            $table->enum('status', ['menunggu', 'selesai'])->default('menunggu');
            $table->timestamp('diisi_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_jawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('survey_kepuasan')->onDelete('cascade');
            $table->foreignId('pertanyaan_id')->constrained('survey_pertanyaan')->onDelete('cascade');
            $table->string('jawaban');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_jawaban');
        Schema::dropIfExists('survey_kepuasan');
        Schema::dropIfExists('survey_pertanyaan');
    }
};