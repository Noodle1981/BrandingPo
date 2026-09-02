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
        Schema::create('publicacion_pauta_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacion_id')->constrained('publicaciones')->onDelete('cascade');
            $table->string('tipo_pauta_anterior');
            $table->string('tipo_pauta_nuevo');
            $table->decimal('monto_anterior', 12, 2)->default(0);
            $table->decimal('monto_nuevo', 12, 2)->default(0);
            $table->dateTime('fecha_evento')->index();
            $table->unsignedBigInteger('seguidores_canal_snapshot')->default(0);
            $table->unsignedBigInteger('likes_snapshot')->default(0);
            $table->unsignedInteger('comentarios_snapshot')->default(0);
            $table->unsignedInteger('compartidos_snapshot')->default(0);
            $table->unsignedBigInteger('vistas_snapshot')->default(0);
            $table->unsignedInteger('republicados_snapshot')->default(0);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origen')->default('manual'); // 'manual' | 'auto_sync'
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicacion_pauta_eventos');
    }
};
