<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->string('sesi')->default('pagi'); // pagi / siang
            // 15 item checklist dari SOP PST
            $table->boolean('item_01')->default(false); // Atribut lengkap (blazer, name tag, pin)
            $table->boolean('item_02')->default(false); // Nyalakan komputer loket + buku tamu
            $table->boolean('item_03')->default(false); // Nyalakan mesin antrean
            $table->boolean('item_04')->default(false); // Layar monitor antrean menyala
            $table->boolean('item_05')->default(false); // Aplikasi LOKET terhubung mesin antrean
            $table->boolean('item_06')->default(false); // PC Pustaka Digital menyala
            $table->boolean('item_07')->default(false); // Swivel monitor ruang tunggu menyala
            $table->boolean('item_08')->default(false); // Melayani sesuai SOP
            $table->boolean('item_09')->default(false); // Buka WA + cek & balas WA
            $table->boolean('item_10')->default(false); // Input laporan online di Silastik
            $table->boolean('item_11')->default(false); // Input antrean permintaan data (jika ada)
            $table->boolean('item_12')->default(false); // Isi presensi
            $table->boolean('item_13')->default(false); // Cek kotak pengaduan
            $table->boolean('item_14')->default(false); // Cek dashboard buku tamu
            $table->boolean('item_15')->default(false); // (Sesi Siang) Matikan semua TI, lampu, AC
            $table->text('catatan')->nullable();
            $table->enum('status', ['draft', 'submit', 'verified'])->default('draft');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tanggal', 'sesi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_harian');
    }
};