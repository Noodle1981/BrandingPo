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
        Schema::create('presupuesto_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_campana_id')->constrained('ciclo_campanas')->onDelete('cascade');
            $table->foreignId('candidato_id')->nullable()->constrained('candidatos')->onDelete('cascade');
            $table->string('categoria'); // 'pauta_digital', 'via_publica', 'produccion_audiovisual', 'eventos_territoriales', 'honorarios', 'contingencias'
            $table->decimal('monto_asignado', 12, 2);
            $table->decimal('monto_ejecutado', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_partidas');
    }
};
