<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $fillable = [
        'nama',
        'lokasi',
        'alamat',
        'status',
    ];

    protected static function booted(): void
    {
        static::created(function (Wilayah $wilayah) {
            WilayahSurveyToken::firstOrGenerate($wilayah->id);
        });
    }

    public function petugas()
    {
        return $this->hasMany(Petugas::class, 'wilayah_id');
    }

    public function koordinator()
    {
        return $this->hasMany(Koordinator::class, 'wilayah_id');
    }
}