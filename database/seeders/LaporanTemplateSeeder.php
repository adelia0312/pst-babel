<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LaporanTemplate;

class LaporanTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Jangan duplikat jika sudah ada data
        if (LaporanTemplate::count() > 0) {
            $this->command->info('LaporanTemplate sudah ada data, seeder dilewati.');
            return;
        }

        $templates = [
            [
                'judul'     => 'Tamu Kunjungan Langsung',
                'deskripsi' => 'Jumlah dan keterangan tamu yang datang langsung ke kantor PST',
                'tipe'      => 'teks',
                'opsi'      => null,
                'wajib'     => true,
                'urutan'    => 1,
                'aktif'     => true,
            ],
            [
                'judul'     => 'Kunjungan via Telepon / Email',
                'deskripsi' => 'Permintaan data yang masuk melalui telepon atau email',
                'tipe'      => 'teks',
                'opsi'      => null,
                'wajib'     => true,
                'urutan'    => 2,
                'aktif'     => true,
            ],
            [
                'judul'     => 'Status Layanan PST',
                'deskripsi' => 'Kondisi layanan PST pada hari ini',
                'tipe'      => 'pilihan',
                'opsi'      => ['Berjalan Normal', 'Gangguan Ringan', 'Gangguan Berat', 'Tutup / Libur'],
                'wajib'     => true,
                'urutan'    => 3,
                'aktif'     => true,
            ],
            [
                'judul'     => 'Kendala / Catatan Harian',
                'deskripsi' => 'Isi jika ada kendala atau hal penting yang perlu dilaporkan',
                'tipe'      => 'teks',
                'opsi'      => null,
                'wajib'     => false,
                'urutan'    => 4,
                'aktif'     => true,
            ],
            [
                'judul'     => 'Jumlah Pengunjung Website',
                'deskripsi' => 'Pantauan jumlah pengunjung website BPS pada hari ini (jika tersedia)',
                'tipe'      => 'teks',
                'opsi'      => null,
                'wajib'     => false,
                'urutan'    => 5,
                'aktif'     => true,
            ],
        ];

        foreach ($templates as $data) {
            LaporanTemplate::create($data);
        }

        $this->command->info('5 template pertanyaan laporan harian berhasil ditambahkan.');
    }
}