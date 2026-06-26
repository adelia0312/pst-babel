<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jawaban extends Model
{
    protected $table = 'jawaban';

    protected $fillable = [
        'tugas_id',
        'petugas_id',
        'skor',
        'status',
        'file',
        'link',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class);
    }

    /**
     * Jawaban per soal quiz yang terkait dengan submission ini
     */
    public function jawabanQuiz()
    {
        return $this->hasMany(JawabanQuiz::class, 'petugas_id', 'petugas_id')
                    ->where('tugas_id', $this->tugas_id);
    }
}