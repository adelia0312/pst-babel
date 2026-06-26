<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Koordinator;
use App\Models\Petugas;
use App\Models\Wilayah;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        Admin::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'      => 'Admin PST',
                'password'  => 'admin123',
                'is_active' => true,
            ]
        );

        // Koordinator — ambil wilayah pertama jika ada
        $wilayahPangkal = Wilayah::where('nama', 'Pangkalpinang')->first();

        Koordinator::firstOrCreate(
            ['username' => 'koordinator'],
            [
                'name'       => 'Koordinator PST',
                'password'   => '123456',
                'wilayah_id' => $wilayahPangkal?->id,
                'is_active'  => true,
            ]
        );

        // Petugas contoh
        Petugas::firstOrCreate(
            ['username' => 'petugas'],
            [
                'name'       => 'Petugas PST',
                'password'   => '123456',
                'wilayah_id' => $wilayahPangkal?->id,
                'is_active'  => true,
            ]
        );
    }
}