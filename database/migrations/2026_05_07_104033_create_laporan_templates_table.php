<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk template pertanyaan laporan harian.
     * Admin CRUD pertanyaan + opsi jawaban di sini.
     */
    public function up(): void
    {
        Schema::create('laporan_templates', function (Blueprint $table) {
            $table->id();
            $table->string('judul');                    // Judul/label pertanyaan, contoh: "Tamu Kunjungan Langsung"
            $table->text('deskripsi')->nullable();       // Penjelasan tambahan (opsional)
            $table->enum('tipe', ['teks', 'pilihan'])   // teks = textarea, pilihan = dropdown
                  ->default('teks');
            $table->json('opsi')->nullable();            // Opsi dropdown: ["Sudah","Tidak ada kunjungan langsung"]
            $table->boolean('wajib')->default(true);     // Apakah wajib diisi
            $table->integer('urutan')->default(0);       // Urutan tampil di form
            $table->boolean('aktif')->default(true);     // Bisa dinonaktifkan tanpa hapus
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_templates');
    }
};