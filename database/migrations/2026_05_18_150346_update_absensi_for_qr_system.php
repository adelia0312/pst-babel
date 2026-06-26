<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi', function (Blueprint $table) {

            // Jenis scan
            $table->enum('jenis_scan', [
                'masuk_pagi',
                'keluar_pagi',
                'masuk_siang',
                'keluar_siang'
            ])->nullable()->after('sesi');

            // Token QR
            $table->string('qr_token_used', 64)
                  ->nullable()
                  ->after('jenis_scan');

            // Status kehadiran
            $table->enum('status_kehadiran', [
                'tepat_waktu',
                'toleransi',
                'terlambat',
                'tidak_scan_keluar',
                'alpha',
            ])->nullable()->after('status');

            // Menit keterlambatan
            $table->unsignedSmallInteger('keterlambatan_menit')
                  ->default(0)
                  ->after('status_kehadiran');
        });
    }

    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {

            $table->dropColumn([
                'jenis_scan',
                'qr_token_used',
                'status_kehadiran',
                'keterlambatan_menit'
            ]);
        });
    }
};