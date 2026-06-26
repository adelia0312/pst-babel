<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'judul',
        'deskripsi',
        'deadline',
        'wilayah',
        'file',
        'link'
    ];

    protected $casts = [
        'deadline' => 'date'
    ];

    // Relasi ke Quiz
    public function quiz()
    {
        return $this->hasMany(Quiz::class, 'tugas_id');
    }

    // Relasi ke banyak file lampiran
    public function files()
    {
        return $this->hasMany(TugasFile::class, 'tugas_id');
    }

    /**
     * Gabungan semua file: kolom `file` legacy (jika ada) + relasi files().
     * Dipakai supaya data lama (sebelum fitur multi-file) tetap tampil.
     *
     * @return \Illuminate\Support\Collection
     */
    public function semuaFile()
    {
        $list = collect();

        if ($this->file) {
            $list->push((object) [
                'id'        => null,
                'file'      => $this->file,
                'nama_asli' => basename($this->file),
                'legacy'    => true,
            ]);
        }

        foreach ($this->files as $f) {
            $list->push((object) [
                'id'        => $f->id,
                'file'      => $f->file,
                'nama_asli' => $f->nama_asli ?: basename($f->file),
                'legacy'    => false,
            ]);
        }

        return $list;
    }
}