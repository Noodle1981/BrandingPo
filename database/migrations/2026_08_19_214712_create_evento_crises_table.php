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
        Schema::create('eventos_crisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidato_id')->constrained('candidatos')->onDelete('cascade');
            $table->string('titulo');
            $table->dateTime('fecha_evento')->index();
            $table->string('nivel_gravedad')->default('moderado'); // 'leve', 'moderado', 'critico'
            $table->unsignedInteger('minutos_tiempo_respuesta')->default(0);
            $table->text('estrategia_contencion')->nullable();
            $table->string('estado')->default('abierto'); // 'abierto', 'en_contencion', 'resuelto'
            $table->string('impacto_estimado')->nullable(); // 'Bajo', 'Medio', 'Alto', 'Catastrófico'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_crisis');
    }
};
