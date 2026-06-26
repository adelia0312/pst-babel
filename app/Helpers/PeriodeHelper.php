<?php

namespace App\Helpers;

use Carbon\Carbon;

class PeriodeHelper
{
    /**
     * Ubah string periode (YYYY-MM atau YYYY-TW#) menjadi label yang terbaca manusia.
     *
     * Contoh:
     *   "2026-06"   → "Juni 2026"
     *   "2026-TW2"  → "Triwulan 2 Tahun 2026"
     *   null / ""   → ""
     *
     * @param  string|null  $periode
     * @param  string       $format   Format Carbon untuk mode bulanan (default translatedFormat 'F Y')
     * @return string
     */
    public static function label(?string $periode, string $format = 'F Y'): string
    {
        if (!$periode) {
            return '';
        }

        // Format triwulan: YYYY-TW{1-4}
        if (preg_match('/^(\d{4})-TW([1-4])$/', $periode, $m)) {
            return "Triwulan {$m[2]} Tahun {$m[1]}";
        }

        // Format bulanan: YYYY-MM
        if (preg_match('/^\d{4}-\d{2}$/', $periode)) {
            return Carbon::createFromFormat('Y-m', $periode)->translatedFormat($format);
        }

        // Fallback: kembalikan apa adanya
        return $periode;
    }

    /**
     * Sama seperti label() tapi menggunakan isoFormat Carbon.
     *
     * @param  string|null  $periode
     * @param  string       $isoFormat  Format isoFormat (default 'MMMM YYYY')
     * @return string
     */
    public static function isoLabel(?string $periode, string $isoFormat = 'MMMM YYYY'): string
    {
        if (!$periode) {
            return '';
        }

        if (preg_match('/^(\d{4})-TW([1-4])$/', $periode, $m)) {
            return "Triwulan {$m[2]} Tahun {$m[1]}";
        }

        if (preg_match('/^\d{4}-\d{2}$/', $periode)) {
            return Carbon::createFromFormat('Y-m', $periode)->isoFormat($isoFormat);
        }

        return $periode;
    }
}