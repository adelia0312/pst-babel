<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistHarian extends Model
{
    protected $table = 'checklist_harian';

    protected $fillable = [
        'user_id', 'tanggal', 'sesi',
        'items', 'catatan', 'status', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'verified_at' => 'datetime',
        'items'       => 'array',
    ];

    // ── Helper: hitung item yang sudah diceklis ──
    public function totalChecked(): int
    {
        if (!$this->items) return 0;
        return collect($this->items)->filter()->count();
    }

    // ── Helper: hitung total item (semua key di items) ──
    public function totalItems(): int
    {
        if (!$this->items) return 0;
        return count($this->items);
    }

    // ── Helper: persentase item yang diceklis (0–100) ──
    public function pctChecked(): int
    {
        $total = $this->totalItems();
        if ($total === 0) return 0;
        return (int) round(($this->totalChecked() / $total) * 100);
    }

    // ── Relasi ──
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}