<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $table = 'petugas';

    protected $fillable = [
        'user_id',
        'wilayah_id',
        'shift',
        'status'
    ];

    // Scope: hanya petugas aktif
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    // Scope: hanya petugas nonaktif
    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function jawaban()
{
    return $this->hasMany(Jawaban::class);
}
}