<?php

namespace App\Models;
use App\Models\Petugas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'no_hp',
        'wilayah_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Relasi ──────────────────────────────────────────

    /**
     * Wilayah tempat user ini ditugaskan
     */
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
    public function petugas()
    {
    return $this->hasOne(Petugas::class);
    }

    // ── Scope helper ────────────────────────────────────

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeKoordinator($query)
    {
        return $query->where('role', 'koordinator');
    }

    public function scopePetugas($query)
    {
        return $query->where('role', 'petugas');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helper method ────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKoordinator(): bool
    {
        return $this->role === 'koordinator';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }
}