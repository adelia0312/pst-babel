<?php

namespace App\Models;

/**
 * Model khusus untuk role Admin.
 * Tetap pakai tabel 'users', otomatis filter role=admin.
 */
class Admin extends User
{
    protected static function booted(): void
    {
        // Setiap query lewat Admin:: otomatis filter role=admin
        static::addGlobalScope('role', function ($builder) {
            $builder->where('role', 'admin');
        });

        // Setiap create lewat Admin:: otomatis set role=admin
        static::creating(function ($model) {
            $model->role = 'admin';
        });
    }
}