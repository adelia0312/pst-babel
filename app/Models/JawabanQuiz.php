<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanQuiz extends Model
{
    protected $table = 'jawaban_quiz';

    protected $fillable = [
        'tugas_id',
        'petugas_id',
        'quiz_id',
        'jawaban',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}