<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LaporanHarianBaru extends Model
{
    protected $table = 'laporan_harian';

    protected $fillable = [
        'user_id', 'wilayah_id', 'nama_petugas',
        'tanggal', 'hari', 'sesi',
        'jawaban',
        'status', 'catatan_koordinator', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'reviewed_at' => 'datetime',
        'jawaban'     => 'array',
    ];

    /** Hari dalam Bahasa Indonesia dari tanggal */
    public static function namaHari(string $tanggal): string
    {
        $hari = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        return $hari[Carbon::parse($tanggal)->format('l')] ?? '-';
    }

    /** Jawaban untuk template tertentu */
    public function jawabUntuk(int $templateId): ?string
    {
        return $this->jawaban[$templateId] ?? null;
    }

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