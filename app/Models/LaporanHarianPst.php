<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model untuk tabel lama laporan_harian_pst.
 * Dipertahankan agar LaporanHarianController tidak crash saat boot.
 * Sistem laporan baru menggunakan LaporanHarianBaru + LaporanTemplate.
 */
class LaporanHarianPst extends Model
{
    protected $table = 'laporan_harian_pst';

    protected $fillable = [
        'user_id',
        'wilayah_id',
        'tanggal',
        'hari',
        'sesi',
        'nama_petugas_pst',
        'tamu_kunjungan_langsung',
        'sudah_input_kunjungan_sbe',
        'tamu_konsultasi_wa',
        'sudah_input_konsultasi_wa_sbe',
        'tamu_konsultasi_silastik',
        'sudah_akhiri_konsultasi_silastik',
        'surat_masuk',
        'sudah_input_surat_sbe',
        'list_data_diminta',
        'data_belum_diberikan_antrean',
        'bps_news_pertama',
        'pc_tidak_menyala',
        'catatan',
        'status',
        'catatan_koordinator',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}