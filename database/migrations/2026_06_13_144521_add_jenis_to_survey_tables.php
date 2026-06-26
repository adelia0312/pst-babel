<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Tambah kolom `jenis` ke survey_kepuasan dan survey_pertanyaan
 * + Tambah setting Survey Internal ke survey_setting
 *
 * Letak file: database/migrations/2026_06_13_000001_add_jenis_to_survey_tables.php
 * Status     : FILE BARU
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tambah kolom jenis ke survey_kepuasan ──────────────
        Schema::table('survey_kepuasan', function (Blueprint $table) {
            $table->enum('jenis', ['eksternal', 'internal'])
                  ->default('eksternal')
                  ->after('wilayah_id')
                  ->comment('eksternal = diisi pengunjung, internal = penilaian antar pegawai');
        });

        // ── 2. Tambah kolom jenis ke survey_pertanyaan ────────────
        Schema::table('survey_pertanyaan', function (Blueprint $table) {
            $table->enum('jenis', ['eksternal', 'internal', 'semua'])
                  ->default('eksternal')
                  ->after('is_active')
                  ->comment('Pertanyaan berlaku untuk jenis survey mana');
        });

        // ── 3. Tambah setting Survey Internal ke survey_setting ───
        $settings = [
            [
                'key'        => 'internal_periode_mode',
                'value'      => 'triwulan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'internal_periode_aktif',
                'value'      => self::periodeTriwulanSekarang(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'internal_override_aktif',
                'value'      => 'false',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'internal_override_label',
                'value'      => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('survey_setting')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    public function down(): void
    {
        Schema::table('survey_kepuasan', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });

        Schema::table('survey_pertanyaan', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });

        DB::table('survey_setting')->whereIn('key', [
            'internal_periode_mode',
            'internal_periode_aktif',
            'internal_override_aktif',
            'internal_override_label',
        ])->delete();
    }

    private static function periodeTriwulanSekarang(): string
    {
        $bulan = now()->month;
        $tw    = (int) ceil($bulan / 3);
        return now()->year . '-TW' . $tw;
    }
};