<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveySetting extends Model
{
    protected $table    = 'survey_setting';
    protected $fillable = ['key', 'value'];

    /**
     * Ambil value berdasarkan key, dengan default jika tidak ada.
     */
    public static function get(string $key, string $default = ''): string
    {
        return self::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Simpan atau update value berdasarkan key.
     */
    public static function set(string $key, string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}