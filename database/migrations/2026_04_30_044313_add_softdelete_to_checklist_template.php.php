<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah soft delete ke tabel checklist_template.
 *
 * Masalah sebelumnya:
 *   - adminTemplateDestroy() langsung hapus row (->delete() tanpa SoftDeletes)
 *   - Saat template dihapus, checklist lama yang menyimpan ID template tersebut
 *     kehilangan labelnya — tampil kosong / error
 *   - Template juga "hilang" saat ganti bulan karena tidak ada mekanisme persist
 *
 * Solusi:
 *   - SoftDeletes: delete() hanya mengisi deleted_at, row tetap di DB
 *   - Template yang "dihapus" tidak muncul di form isi checklist (scope aktif)
 *   - Tapi label masih bisa diambil via ::withTrashed() untuk tampilan historis
 *   - Template TIDAK perlu dibuat ulang setiap bulan — tetap ada sampai
 *     admin benar-benar menghapusnya (dan bahkan setelah itu, historis aman)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_template', function (Blueprint $table) {
            $table->softDeletes(); // kolom deleted_at nullable timestamp
        });
    }

    public function down(): void
    {
        Schema::table('checklist_template', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};