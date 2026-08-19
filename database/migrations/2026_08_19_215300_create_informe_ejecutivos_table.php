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
        Schema::create('informes_ejecutivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_campana_id')->constrained('ciclo_campanas')->onDelete('cascade');
            $table->string('titulo');
            $table->date('fecha_generacion')->index();
            $table->string('periodo_cubierto'); // ej. "Semana 34 - Agosto 2026", "Cierre de Ciclo 2025"
            $table->text('resumen_ejecutivo');
            $table->json('metricas_clave_snapshot')->nullable();
            $table->text('conclusiones_estrategicas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes_ejecutivos');
    }
};
