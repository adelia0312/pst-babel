<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom file & link ke tabel jawaban yang sudah ada,
 * sehingga petugas bisa upload file dan isi link saat submit tugas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jawaban', function (Blueprint $table) {
            // upload file dari petugas
            $table->string('file')->nullable()->after('status');
            // link yang diisi petugas
            $table->string('link')->nullable()->after('file');
        });
    }

    public function down(): void
    {
        Schema::table('jawaban', function (Blueprint $table) {
            $table->dropColumn(['file', 'link']);
        });
    }
};