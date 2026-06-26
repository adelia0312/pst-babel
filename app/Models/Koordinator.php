<?php

namespace App\Models;

/**
 * Model khusus untuk role Koordinator.
 * Tetap pakai tabel 'users', otomatis filter role=koordinator.
 */
class Koordinator extends User
{
    protected static function booted(): void
    {
        static::addGlobalScope('role', function ($builder) {
            $builder->where('role', 'koordinator');
        });

        static::creating(function ($model) {
            $model->role = 'koordinator';
        });
    }
}