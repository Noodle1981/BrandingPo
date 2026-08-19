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
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_campana_id')->constrained('ciclo_campanas')->onDelete('cascade');
            $table->foreignId('territorio_id')->nullable()->constrained('territorios')->onDelete('set null');
            $table->string('nombre_completo');
            $table->string('partido_coalicion');
            $table->string('cargo_aspirado')->nullable();
            $table->string('estado_politico')->default('candidato'); // 'precandidato', 'candidato', 'intendente_electo', 'gobernador_electo', 'en_funciones', 'opositor', 'inactivo'
            $table->string('color_hex')->default('#06b6d4');
            $table->boolean('es_propio')->default(false);
            $table->string('avatar_url')->nullable();
            $table->text('bio_resumen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatos');
    }
};
