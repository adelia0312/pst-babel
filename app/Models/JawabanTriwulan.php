<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanTriwulan extends Model
{
    protected $table = 'jawaban_triwulan';

    protected $fillable = [
        'materi_triwulan_id', 'petugas_id', 'periode',
        'status', 'skor', 'jawaban_detail', 'dikerjakan_at',
    ];

    protected $casts = [
        'jawaban_detail' => 'array',
        'dikerjakan_at'  => 'datetime',
    ];

    public function materi()
    {
        return $this->belongsTo(MateriTriwulan::class, 'materi_triwulan_id');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class);
    }
}