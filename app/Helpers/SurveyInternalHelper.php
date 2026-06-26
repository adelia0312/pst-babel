<?php

namespace App\Helpers;

use App\Models\SurveySetting;
use Carbon\Carbon;

/**
 * SurveyInternalHelper
 *
 * Mengatur logika kapan Survey Internal boleh diakses.
 * Mendukung dua mode:
 *   - triwulan : otomatis buka di minggu 1-2 bulan pertama setiap triwulan
 *   - manual   : admin menentukan sendiri periode yang aktif
 *
 * Override admin memungkinkan survey dibuka kapan saja (untuk demo/sidang).
 *
 * Letak file: app/Helpers/SurveyInternalHelper.php
 * Status     : FILE BARU
 */
class SurveyInternalHelper
{
    // ── Konstanta jendela waktu ────────────────────────────────────
    /**
     * Bulan pertama setiap triwulan (Januari, April, Juli, Oktober)
     */
    private const BULAN_AWAL_TRIWULAN = [1, 4, 7, 10];

    /**
     * Batas minggu ke berapa survey internal masih terbuka (mode triwulan)
     * Misalnya: 2 = terbuka di minggu ke-1 dan ke-2 bulan pertama triwulan
     */
    private const BATAS_MINGGU = 2;

    // ──────────────────────────────────────────────────────────────

    /**
     * Apakah Survey Internal saat ini bisa diakses oleh pegawai?
     * Mengecek override admin terlebih dahulu, lalu logika periode.
     */
    public static function bisaDiakses(): bool
    {
        $overrideAktif = SurveySetting::get('internal_override_aktif', '');

        // Jika admin pernah set override (true/false), ikuti keputusan admin
        if ($overrideAktif === 'true') {
            return true;
        }
        if ($overrideAktif === 'false') {
            return false;
        }

        // Belum pernah diset admin sama sekali → pakai logika mode otomatis
        $mode = SurveySetting::get('internal_periode_mode', 'triwulan');

        if ($mode === 'triwulan') {
            return self::dalamJendelaTrwulan();
        }

        return (bool) SurveySetting::get('internal_periode_aktif');
    }

    /**
     * Ambil string periode yang sedang aktif (untuk disimpan ke DB saat submit).
     * Contoh return: "2026-TW2"
     */
    public static function periodeAktif(): string
    {
        // Jika override aktif, gunakan periode yang di-set admin (atau hitung otomatis)
        if (SurveySetting::get('internal_override_aktif') === 'true') {
            $periodeManual = SurveySetting::get('internal_periode_aktif');
            return $periodeManual ?: self::periodeTriwulanSekarang();
        }

        $mode = SurveySetting::get('internal_periode_mode', 'triwulan');

        if ($mode === 'triwulan') {
            return self::periodeTriwulanSekarang();
        }

        return SurveySetting::get('internal_periode_aktif', self::periodeTriwulanSekarang());
    }

    /**
     * Label terbaca untuk periode aktif.
     * Contoh: "Triwulan 2 Tahun 2026"
     */
    public static function labelPeriodeAktif(): string
    {
        return PeriodeHelper::label(self::periodeAktif());
    }

    /**
     * Hitung string periode triwulan berdasarkan tanggal sekarang.
     * Contoh: "2026-TW2"
     */
    public static function periodeTriwulanSekarang(): string
    {
        $bulan = now()->month;
        $tw    = (int) ceil($bulan / 3);
        return now()->year . '-TW' . $tw;
    }

    /**
     * Apakah saat ini masuk dalam jendela waktu pengisian triwulan?
     * Default: minggu 1-2 di bulan pertama setiap triwulan.
     */
    public static function dalamJendelaTrwulan(): bool
    {
        $bulanSekarang = now()->month;
        $mingguSekarang = (int) ceil(now()->day / 7);

        return in_array($bulanSekarang, self::BULAN_AWAL_TRIWULAN)
            && $mingguSekarang <= self::BATAS_MINGGU;
    }

    /**
     * Apakah override admin sedang aktif?
     */
    public static function overrideAktif(): bool
    {
        return SurveySetting::get('internal_override_aktif') === 'true';
    }

    /**
     * Label alasan override (opsional, untuk audit trail).
     */
    public static function overrideLabel(): string
    {
        return SurveySetting::get('internal_override_label', '');
    }

    /**
     * Triwulan berikutnya yang akan dibuka (untuk pesan info ke pegawai).
     * Contoh: "Triwulan 3 Tahun 2026 (Juli 2026)"
     */
    public static function infoTriwulanBerikutnya(): string
    {
        $bulan     = now()->month;
        $tahun     = now()->year;
        $twSekarang = (int) ceil($bulan / 3);

        if ($twSekarang >= 4) {
            $twBerikutnya = 1;
            $tahunBerikutnya = $tahun + 1;
        } else {
            $twBerikutnya    = $twSekarang + 1;
            $tahunBerikutnya = $tahun;
        }

        $bulanMulai = ($twBerikutnya - 1) * 3 + 1;
        $namaBulan  = Carbon::create($tahunBerikutnya, $bulanMulai, 1)->translatedFormat('F Y');

        return "Triwulan {$twBerikutnya} Tahun {$tahunBerikutnya} ({$namaBulan})";
    }
}