<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanTemplate extends Model
{
    protected $table = 'laporan_templates';

    protected $fillable = [
        'judul',
        'deskripsi',
        'tipe',
        'opsi',
        'wajib',
        'urutan',
        'aktif',
        'berlaku_mulai',  // ← TAMBAHAN: tanggal pertanyaan mulai berlaku
    ];

    protected $casts = [
        'opsi'          => 'array',
        'wajib'         => 'boolean',
        'aktif'         => 'boolean',
        'berlaku_mulai' => 'date',   // ← TAMBAHAN
    ];

    /**
     * Scope: hanya template yang aktif, diurutkan.
     * (Tidak berubah — digunakan di tempat yang tidak perlu filter tanggal)
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true)->orderBy('urutan');
    }

    /**
     * Scope: template aktif yang BERLAKU pada tanggal tertentu.
     *
     * Digunakan di form petugas (create/edit) dan tampilan koordinator detail,
     * agar pertanyaan baru tidak muncul di laporan lama.
     *
     * Aturan:
     *   - berlaku_mulai IS NULL  → pertanyaan lama, selalu tampil
     *   - berlaku_mulai <= $tgl  → pertanyaan baru, sudah berlaku pada tanggal itu
     *   - berlaku_mulai > $tgl   → pertanyaan baru, belum berlaku → tidak tampil
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string|\Carbon\Carbon $tanggal  Tanggal laporan (Y-m-d atau Carbon)
     */
    public function scopeAktifPadaTanggal($query, $tanggal)
    {
        return $query->where('aktif', true)
                     ->where(function ($q) use ($tanggal) {
                         $q->whereNull('berlaku_mulai')
                           ->orWhere('berlaku_mulai', '<=', $tanggal);
                     })
                     ->orderBy('urutan');
    }
}