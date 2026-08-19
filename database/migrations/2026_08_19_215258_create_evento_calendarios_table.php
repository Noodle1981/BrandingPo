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
        Schema::create('eventos_calendario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_campana_id')->constrained('ciclo_campanas')->onDelete('cascade');
            $table->foreignId('candidato_id')->nullable()->constrained('candidatos')->onDelete('cascade');
            $table->string('titulo');
            $table->dateTime('fecha_inicio')->index();
            $table->dateTime('fecha_fin')->nullable();
            $table->string('tipo_evento')->default('acto'); // 'acto', 'caravana', 'debate', 'pauta_vencimiento', 'rueda_prensa', 'reunion_privada'
            $table->string('lugar')->nullable();
            $table->string('estado')->default('programado'); // 'programado', 'realizado', 'cancelado'
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_calendario');
    }
};
