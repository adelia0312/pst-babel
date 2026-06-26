<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->onDelete('set null');
            $table->date('tanggal');
            $table->enum('sesi', ['pagi', 'siang', 'malam']);
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('foto_selfie')->nullable();   // path foto
            $table->text('catatan')->nullable();
            $table->enum('verified_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->string('device_info')->nullable();   // info HP petugas
            $table->timestamps();

            // Satu petugas hanya bisa absen sekali per tanggal per sesi
            $table->unique(['user_id', 'tanggal', 'sesi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
