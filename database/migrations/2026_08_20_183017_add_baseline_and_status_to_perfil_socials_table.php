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
        Schema::table('perfil_socials', function (Blueprint $table) {
            $table->boolean('esta_verificado')->default(false)->after('publicaciones_totales');
            $table->boolean('esta_activo')->default(true)->after('esta_verificado');
            $table->unsignedBigInteger('seguidos_actuales')->default(0)->after('esta_activo');
            $table->string('foto_perfil_url')->nullable()->after('seguidos_actuales');
            $table->date('fecha_punto_cero')->nullable()->after('foto_perfil_url');
            $table->unsignedBigInteger('seguidores_punto_cero')->default(0)->after('fecha_punto_cero');
            $table->unsignedBigInteger('seguidos_punto_cero')->default(0)->after('seguidores_punto_cero');
            $table->unsignedInteger('publicaciones_punto_cero')->default(0)->after('seguidos_punto_cero');
            $table->text('notas_punto_cero')->nullable()->after('publicaciones_punto_cero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perfil_socials', function (Blueprint $table) {
            $table->dropColumn([
                'esta_verificado',
                'esta_activo',
                'seguidos_actuales',
                'foto_perfil_url',
                'fecha_punto_cero',
                'seguidores_punto_cero',
                'seguidos_punto_cero',
                'publicaciones_punto_cero',
                'notas_punto_cero',
            ]);
        });
    }
};
