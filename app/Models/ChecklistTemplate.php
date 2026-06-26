<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistTemplate extends Model
{
    use SoftDeletes; // ← tambahan utama

    protected $table = 'checklist_template';

    protected $fillable = [
        'wilayah_id',
        'urutan',
        'label',
        'link',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relasi ───────────────────────────────────────────────────────

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    // ── Scope ────────────────────────────────────────────────────────

    /**
     * Template AKTIF untuk wilayah tertentu.
     * Dipakai di form isi checklist oleh petugas.
     * SoftDeletes secara otomatis mengecualikan baris yang sudah dihapus.
     */
    public function scopeForWilayah($query, $wilayahId)
    {
        return $query->where('wilayah_id', $wilayahId)
                     ->where('is_active', true)
                     ->orderBy('urutan');
    }

    /**
     * Template AKTIF global (admin, tanpa wilayah).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('wilayah_id')
                     ->where('is_active', true)
                     ->orderBy('urutan');
    }

    /**
     * Scope untuk keperluan historis — termasuk yang sudah soft-deleted.
     * Dipakai di halaman detail checklist agar label lama tetap tampil.
     *
     * Contoh:
     *   $labelsMap = ChecklistTemplate::forHistory(array_keys($itemsJson))
     *       ->pluck('label', 'id');
     */
    public function scopeForHistory($query, array $ids)
    {
        return $query->withTrashed()->whereIn('id', $ids);
    }
}