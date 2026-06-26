<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_harian', function (Blueprint $table) {
            // Hapus kolom item_01 – item_15 lama
            for ($i = 1; $i <= 15; $i++) {
                $col = 'item_' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $table->dropColumn($col);
            }
            // Ganti dengan JSON: { template_id: true/false, ... }
            $table->json('items')->nullable()->after('sesi');
        });
    }

    public function down(): void
    {
        Schema::table('checklist_harian', function (Blueprint $table) {
            $table->dropColumn('items');
            for ($i = 1; $i <= 15; $i++) {
                $col = 'item_' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $table->boolean($col)->default(false);
            }
        });
    }
};