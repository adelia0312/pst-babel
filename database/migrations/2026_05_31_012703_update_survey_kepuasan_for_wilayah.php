<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_kepuasan', function (Blueprint $table) {
            $table->foreignId('wilayah_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('wilayah')
                  ->onDelete('cascade');

            $table->string('sumber')->default('barcode')->after('status');
        });

        Schema::create('wilayah_survey_token', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wilayah_id')
                  ->unique()
                  ->constrained('wilayah')
                  ->onDelete('cascade');
            $table->string('token_barcode', 64)->unique();
            $table->string('token_link', 64)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah_survey_token');

        Schema::table('survey_kepuasan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('wilayah_id');
            $table->dropColumn('sumber');
        });
    }
};