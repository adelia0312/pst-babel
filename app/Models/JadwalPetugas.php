<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPetugas extends Model
{
    protected $table = 'jadwal_petugas';

    protected $fillable = [
        'user_id',
        'wilayah_id',
        'tanggal',
        'shift',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }
}