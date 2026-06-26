<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model WilayahSurveyToken
 *
 * Menyimpan 2 token permanen per wilayah:
 *   - token_barcode : digunakan untuk cetak QR / barcode fisik (oleh koordinator)
 *   - token_link    : digunakan untuk link online (dikirim petugas ke responden via WA/email)
 *
 * Kedua token ini TIDAK PERNAH berubah.
 * Jika admin menambah/mengubah pertanyaan, form survey otomatis berubah
 * karena pertanyaan diambil live dari database setiap kali responden membuka link/scan.
 */
class WilayahSurveyToken extends Model
{
    protected $table = 'wilayah_survey_token';

    protected $fillable = ['wilayah_id', 'token_barcode', 'token_link'];

    // ──────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Ambil atau buat token permanen untuk wilayah tertentu.
     * Dipanggil saat koordinator pertama kali mengakses halaman cetak barcode / buat link.
     */
    public static function firstOrGenerate(int $wilayahId): self
    {
        return self::firstOrCreate(
            ['wilayah_id' => $wilayahId],
            [
                'token_barcode' => self::generateUniqueToken('token_barcode'),
                'token_link'    => self::generateUniqueToken('token_link'),
            ]
        );
    }

    private static function generateUniqueToken(string $column): string
    {
        do {
            $token = Str::random(40);
        } while (self::where($column, $token)->exists());

        return $token;
    }
}