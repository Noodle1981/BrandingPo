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
        Schema::create('perfil_socials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->onDelete('cascade');
            $table->string('plataforma'); // 'facebook', 'instagram', 'x_twitter', 'tiktok', 'youtube', 'linkedin'
            $table->string('handle_usuario');
            $table->string('url_perfil')->nullable();
            $table->unsignedBigInteger('seguidores_actuales')->default(0);
            $table->unsignedInteger('publicaciones_totales')->default(0);
            $table->json('demografia_interna_propia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfil_socials');
    }
};
