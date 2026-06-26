<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizTriwulan extends Model
{
    protected $table = 'quiz_triwulan';

    protected $fillable = [
        'materi_triwulan_id', 'pertanyaan',
        'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'jawaban',
    ];

    public function materi()
    {
        return $this->belongsTo(MateriTriwulan::class, 'materi_triwulan_id');
    }
}