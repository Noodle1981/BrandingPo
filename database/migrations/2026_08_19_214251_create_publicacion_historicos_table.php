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
        Schema::create('publicacion_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacion_id')->constrained('publicaciones')->onDelete('cascade');
            $table->dateTime('fecha_corte')->index();
            $table->unsignedBigInteger('vistas_corte')->default(0);
            $table->unsignedBigInteger('likes_corte')->default(0);
            $table->unsignedInteger('comentarios_corte')->default(0);
            $table->unsignedInteger('compartidos_corte')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicacion_historicos');
    }
};
