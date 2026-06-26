<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $table = 'quiz';

    protected $fillable = [
        'tugas_id',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'jawaban'
    ];

    // Relasi ke Tugas
    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }
}