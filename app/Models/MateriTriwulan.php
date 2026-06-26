<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriTriwulan extends Model
{
    protected $table = 'materi_triwulan';

    protected $fillable = [
        'wilayah_id', 'koordinator_id', 'judul', 'deskripsi',
        'periode', 'file', 'link',
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function koordinator()
    {
        return $this->belongsTo(User::class, 'koordinator_id');
    }

    public function quiz()
    {
        return $this->hasMany(QuizTriwulan::class, 'materi_triwulan_id');
    }

    public function jawaban()
    {
        return $this->hasMany(JawabanTriwulan::class, 'materi_triwulan_id');
    }

    // Relasi ke banyak file lampiran
    public function files()
    {
        return $this->hasMany(MateriTriwulanFile::class, 'materi_triwulan_id');
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