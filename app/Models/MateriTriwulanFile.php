<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriTriwulanFile extends Model
{
    protected $table = 'materi_triwulan_files';

    protected $fillable = [
        'materi_triwulan_id',
        'file',
        'nama_asli',
    ];

    public function materi()
    {
        return $this->belongsTo(MateriTriwulan::class, 'materi_triwulan_id');
    }
}