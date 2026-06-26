<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX BUG: constraint unik lama (user_id, tanggal, sesi) salah sasaran.
 *
 * Sejak update_absensi_for_qr_system, satu sesi ('pagi'/'siang') punya DUA
 * baris terpisah: satu untuk jenis_scan masuk_*, satu untuk keluar_*.
 * Dengan constraint lama, begitu baris "masuk_pagi" (sesi=pagi) tersimpan,
 * baris "keluar_pagi" (sesi=pagi juga) GAGAL disimpan karena dianggap
 * duplikat — padahal jenis_scan-nya berbeda. Akibatnya scan keluar bisa
 * gagal dengan error database (constraint violation) di endpoint /scan-qr.
 *
 * Fix: ganti jadi unik per (user_id, tanggal, jenis_scan), sesuai cara
 * AbsensiService::validasiUrutan() sudah mengecek duplikat di level
 * aplikasi (baris 195-203 — base pada jenis_scan, bukan sesi).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Pastikan kolom user_id punya index sendiri SEBELUM index unik lama
        // di-drop. Tanpa ini, MySQL menolak drop index lama dengan error
        // "Cannot drop index ... needed in a foreign key constraint",
        // karena foreign key user_id masih bergantung pada index unik lama
        // sebagai index pendukungnya satu-satunya.
        try {
            Schema::table('absensi', function (Blueprint $table) {
                $table->index('user_id', 'absensi_user_id_index');
            });
        } catch (\Throwable $e) {
            // Index dengan nama ini sudah ada (migration ini diulang setelah
            // sempat berhasil sebagian) — aman diabaikan.
        }

        // Drop index unik lama (sekarang aman, FK user_id sudah punya index lain)
        try {
            Schema::table('absensi', function (Blueprint $table) {
                $table->dropUnique('absensi_user_id_tanggal_sesi_unique');
            });
        } catch (\Throwable $e) {
            // Index lama sudah tidak ada — aman diabaikan.
        }

        // Tambah index unik baru: per (user_id, tanggal, jenis_scan)
        try {
            Schema::table('absensi', function (Blueprint $table) {
                $table->unique(['user_id', 'tanggal', 'jenis_scan'], 'absensi_user_tanggal_jenisscan_unique');
            });
        } catch (\Throwable $e) {
            // Index baru sudah ada — aman diabaikan.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('absensi', function (Blueprint $table) {
                $table->dropUnique('absensi_user_tanggal_jenisscan_unique');
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('absensi', function (Blueprint $table) {
                $table->unique(['user_id', 'tanggal', 'sesi']);
            });
        } catch (\Throwable $e) {
        }

        try {
            Schema::table('absensi', function (Blueprint $table) {
                $table->dropIndex('absensi_user_id_index');
            });
        } catch (\Throwable $e) {
        }
    }
};
