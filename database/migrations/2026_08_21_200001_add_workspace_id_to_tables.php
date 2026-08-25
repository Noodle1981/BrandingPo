<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar workspace_id a todas las tablas de datos de campaña.
     * Se agrega como nullable para no romper datos existentes.
     * El seeder llenará el campo con el workspace inicial.
     */
    public function up(): void
    {
        $tablas = [
            'candidatos',
            'ciclo_campanas',
            'territorios',
            'eje_tematicos',
            'publicaciones',
            'medios_prensa',
            'notas_prensa',
            'eventos_crisis',
            'alianzas_politicas',
            'eventos_calendario',
            'presupuesto_partidas',
            'informes_ejecutivos',
        ];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && ! Schema::hasColumn($tabla, 'workspace_id')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->foreignId('workspace_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('workspaces')
                        ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $tablas = [
            'candidatos',
            'ciclo_campanas',
            'territorios',
            'eje_tematicos',
            'publicaciones',
            'medios_prensa',
            'notas_prensa',
            'eventos_crisis',
            'alianzas_politicas',
            'eventos_calendario',
            'presupuesto_partidas',
            'informes_ejecutivos',
        ];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'workspace_id')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropForeign(['workspace_id']);
                    $table->dropColumn('workspace_id');
                });
            }
        }
    }
};
