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
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->onDelete('cascade');
            $table->foreignId('perfil_social_id')->constrained('perfil_socials')->onDelete('cascade');
            $table->foreignId('eje_tematico_id')->nullable()->constrained('eje_tematicos')->onDelete('set null');
            $table->dateTime('fecha_publicacion')->index();
            $table->string('tipo_formato')->default('foto'); // 'reel', 'video', 'nota', 'carrusel', 'foto', 'tweet', 'shorts', 'articulo'
            $table->string('tipo_pauta')->default('organico'); // 'organico', 'pauta_paga'
            $table->decimal('monto_invertido_pauta', 12, 2)->default(0);
            $table->unsignedBigInteger('vistas_organicas')->default(0);
            $table->unsignedBigInteger('vistas_pagadas')->default(0);
            $table->string('url_post')->nullable();
            $table->string('media_url')->nullable();
            $table->text('contenido_resumen');
            $table->unsignedBigInteger('total_vistas')->default(0);
            $table->unsignedBigInteger('total_likes')->default(0);
            $table->unsignedInteger('total_comentarios')->default(0);
            $table->unsignedInteger('total_compartidos')->default(0);
            $table->unsignedInteger('total_guardados')->default(0);
            $table->json('reacciones_detalladas')->nullable(); // {me_gusta, me_encanta, me_importa, me_divierte, me_asombra, me_entristece, me_enoja}
            $table->string('sentimiento_predominante')->default('positivo'); // 'positivo', 'neutro', 'negativo'
            $table->json('figuras_acompanantes')->nullable();
            $table->json('comentarios_destacados')->nullable();
            $table->unsignedTinyInteger('termometro_humor_social')->default(3); // 1 a 5 estrellas
            $table->json('insights_internos_propios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicaciones');
    }
};
