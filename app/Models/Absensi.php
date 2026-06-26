<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = [
        'user_id', 'wilayah_id', 'tanggal', 'sesi', 'jenis_scan',
        'status', 'status_kehadiran', 'keterlambatan_menit',
        'jam_masuk', 'jam_keluar',
        'latitude', 'longitude', 'foto_selfie', 'catatan',
        'verified_status', 'verified_by', 'verified_at',
        'device_info', 'qr_token_used',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'verified_at' => 'datetime',
        'latitude'    => 'float',
        'longitude'   => 'float',
        'keterlambatan_menit' => 'integer',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function wilayah() { return $this->belongsTo(Wilayah::class); }
    public function verifier(){ return $this->belongsTo(User::class, 'verified_by'); }

    // Helper label status kehadiran
    public function getLabelStatusKehadiranAttribute(): string
    {
        return match($this->status_kehadiran) {
            'tepat_waktu' => 'Tepat Waktu',
            'toleransi'   => 'Toleransi',
            'terlambat'   => 'Terlambat',
            'tidak_scan_keluar' => 'Tidak Scan Keluar',
            'alpha'       => 'Alpha',
            default       => ucfirst($this->status_kehadiran ?? '-'),
        };
    }

    public function getLabelJenisScanAttribute(): string
    {
        return match($this->jenis_scan) {
            'masuk_pagi'   => 'Masuk Pagi',
            'keluar_pagi'  => 'Keluar Pagi',
            'masuk_siang'  => 'Masuk Siang',
            'keluar_siang' => 'Keluar Siang',
            default        => $this->jenis_scan ?? '-',
        };
    }

    public function getJamAttribute(): ?string
    {
        return $this->jam_masuk ?? $this->jam_keluar;
    }
}
