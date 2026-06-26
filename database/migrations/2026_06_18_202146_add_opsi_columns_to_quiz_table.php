<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX: Tabel 'quiz' awal (2026_04_14_024715_create_quiz_table) hanya punya
 * kolom 'pertanyaan' & 'jawaban', sedangkan App\Models\Quiz dan
 * TugasController::store()/update() sudah lama menulis ke kolom
 * 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d' yang TIDAK ADA di skema DB.
 *
 * Akibatnya: setiap kali admin menambah soal quiz pada form Tambah/Edit
 * Tugas (kolom "Materi & Pembelajaran"), proses simpan akan gagal dengan
 * SQL error "Unknown column 'opsi_a'" — tugas/materi tidak tersimpan.
 *
 * Migration ini menambahkan kolom yang hilang tersebut. Memakai
 * hasColumn() supaya aman dijalankan walau di sebagian server kolom ini
 * sudah pernah ditambahkan manual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz', function (Blueprint $table) {
            if (! Schema::hasColumn('quiz', 'opsi_a')) {
                $table->string('opsi_a')->nullable()->after('pertanyaan');
            }
            if (! Schema::hasColumn('quiz', 'opsi_b')) {
                $table->string('opsi_b')->nullable()->after('opsi_a');
            }
            if (! Schema::hasColumn('quiz', 'opsi_c')) {
                $table->string('opsi_c')->nullable()->after('opsi_b');
            }
            if (! Schema::hasColumn('quiz', 'opsi_d')) {
                $table->string('opsi_d')->nullable()->after('opsi_c');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz', function (Blueprint $table) {
            foreach (['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d'] as $col) {
                if (Schema::hasColumn('quiz', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
