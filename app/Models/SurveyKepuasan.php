<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Model SurveyKepuasan
 *
 * Letak file: app/Models/SurveyKepuasan.php
 * Status     : FILE LAMA — diperbarui (tambah 'jenis' di fillable & casts)
 */
class SurveyKepuasan extends Model
{
    protected $table = 'survey_kepuasan';

    protected $fillable = [
        'petugas_id', 'wilayah_id', 'nama_responden', 'periode',
        'token', 'status', 'diisi_pada',
        'jenis', // ← BARU: 'eksternal' | 'internal'
    ];

    protected $casts = [
        'diisi_pada' => 'datetime',
    ];

    // ── Relasi ─────────────────────────────────────────────────────

    public function petugas()
    {
        return $this->belongsTo(Petugas::class);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function jawaban()
    {
        return $this->hasMany(SurveyJawaban::class, 'survey_id');
    }

    // ── Scope helper ───────────────────────────────────────────────

    /**
     * Scope hanya survey eksternal (pengunjung).
     */
    public function scopeEksternal($query)
    {
        return $query->where('jenis', 'eksternal');
    }

    /**
     * Scope hanya survey internal (antar pegawai).
     */
    public function scopeInternal($query)
    {
        return $query->where('jenis', 'internal');
    }

    // ── Static helpers ─────────────────────────────────────────────

    public static function buatToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    // ── Computed ───────────────────────────────────────────────────

    public function rataRating(): ?float
    {
        $ratings = $this->jawaban()
            ->whereHas('pertanyaan', fn($q) => $q->where('tipe', 'rating'))
            ->pluck('jawaban')
            ->filter(fn($v) => is_numeric($v))
            ->map(fn($v) => (float) $v);

        return $ratings->count() ? round($ratings->avg(), 2) : null;
    }
}