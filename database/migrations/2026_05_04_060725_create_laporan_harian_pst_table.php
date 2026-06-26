<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harian_pst', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->onDelete('set null');
            $table->date('tanggal');
            $table->enum('hari', ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']);
            $table->enum('sesi', ['Pagi', 'Siang']);
            $table->string('nama_petugas_pst');

            // Kolom sesuai form laporan
            $table->text('tamu_kunjungan_langsung')->nullable();
            $table->enum('sudah_input_kunjungan_sbe', ['Sudah', 'Tidak ada kunjungan langsung'])->default('Tidak ada kunjungan langsung');
            $table->text('tamu_konsultasi_wa')->nullable();
            $table->enum('sudah_input_konsultasi_wa_sbe', ['Sudah', 'Belum', 'Tidak ada konsultasi via WA'])->default('Tidak ada konsultasi via WA');
            $table->text('tamu_konsultasi_silastik')->nullable();
            $table->enum('sudah_akhiri_konsultasi_silastik', ['Sudah', 'Belum', 'Tidak ada konsultasi via Silastik'])->default('Tidak ada konsultasi via Silastik');
            $table->text('surat_masuk')->nullable();
            $table->enum('sudah_input_surat_sbe', ['Sudah', 'Tidak ada surat yang masuk'])->default('Tidak ada surat yang masuk');
            $table->text('list_data_diminta')->nullable();
            $table->enum('data_belum_diberikan_antrean', ['Seluruh data sudah diberikan', 'Tidak ada permintaan data', 'Belum diinput'])->default('Tidak ada permintaan data');
            $table->string('bps_news_pertama')->nullable();
            $table->enum('pc_tidak_menyala', ['Ya', 'Tidak'])->default('Ya');
            $table->text('catatan')->nullable();

            // Status review
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('catatan_koordinator')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harian_pst');
    }
};