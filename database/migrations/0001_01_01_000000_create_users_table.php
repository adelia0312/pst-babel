<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('username')->unique(); // Ini yang digunakan untuk login
        $table->string('password');
        $table->enum('role', ['admin', 'koordinator', 'petugas'])->default('petugas');
        $table->string('no_hp')->nullable();
        $table->unsignedBigInteger('wilayah_id')->nullable();
        $table->boolean('is_active')->default(true);
        $table->rememberToken();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};