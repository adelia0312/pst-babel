<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel laporan harian yang fleksibel.
     * Jawaban disimpan sebagai JSON { template_id: "jawaban", ... }
     * sehingga tidak perlu ubah schema saat admin tambah/hapus pertanyaan.
     */
    public function up(): void
    {
        Schema::create('laporan_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->onDelete('set null');

            // Otomatis diisi sistem, petugas tidak perlu input manual
            $table->string('nama_petugas');
            $table->date('tanggal');
            $table->string('hari');    // Senin - Sabtu, otomatis dari tanggal
            $table->enum('sesi', ['Pagi', 'Siang']);

            // Jawaban dinamis: key = laporan_template.id, value = string jawaban
            $table->json('jawaban')->nullable();

            // Status review
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('catatan_koordinator')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            // Satu petugas, satu sesi, satu hari = satu laporan
            $table->unique(['user_id', 'tanggal', 'sesi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harian');
    }
};