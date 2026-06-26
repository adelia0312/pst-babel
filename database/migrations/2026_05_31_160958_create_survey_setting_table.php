<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_setting', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default template
        DB::table('survey_setting')->insert([
            'key'        => 'template_pesan',
            'value'      => "Halo, terima kasih telah menggunakan layanan PST BPS Babel 🙏\n\nMohon luangkan 1 menit untuk mengisi survey kepuasan berikut. Penilaian Anda sangat membantu kami meningkatkan kualitas pelayanan.\n\n📋 Link Survey:\n{link}\n\nTerima kasih 😊",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_setting');
    }
};