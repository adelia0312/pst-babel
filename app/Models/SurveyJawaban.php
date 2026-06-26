<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyJawaban extends Model
{
    protected $table = 'survey_jawaban';

    protected $fillable = ['survey_id', 'pertanyaan_id', 'jawaban'];

    public function survey()
    {
        return $this->belongsTo(SurveyKepuasan::class, 'survey_id');
    }

    public function pertanyaan()
    {
        return $this->belongsTo(SurveyPertanyaan::class, 'pertanyaan_id');
    }
}