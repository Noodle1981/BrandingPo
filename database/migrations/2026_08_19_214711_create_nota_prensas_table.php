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
        Schema::create('notas_prensa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medio_prensa_id')->constrained('medios_prensa')->onDelete('cascade');
            $table->foreignId('candidato_id')->nullable()->constrained('candidatos')->onDelete('cascade');
            $table->date('fecha_publicacion')->index();
            $table->string('titulo');
            $table->string('url_nota')->nullable();
            $table->text('resumen')->nullable();
            $table->string('tono_mencion')->default('neutro'); // 'favorable', 'neutro', 'critico'
            $table->boolean('es_tapa_o_principal')->default(false);
            $table->unsignedInteger('interacciones_en_redes_del_medio')->default(0);
            $table->text('respuesta_replica_candidato')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_prensa');
    }
};
