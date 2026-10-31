<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Añade índices compuestos optimizados para consultas del Dashboard, Feed y Clipping.
     */
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->index(['workspace_id', 'fecha_publicacion'], 'idx_pub_workspace_fecha');
            $table->index(['candidato_id', 'fecha_publicacion'], 'idx_pub_candidato_fecha');
            $table->index(['perfil_social_id', 'fecha_publicacion'], 'idx_pub_perfil_fecha');
            $table->index(['workspace_id', 'tipo_pauta'], 'idx_pub_workspace_pauta');
            $table->index(['workspace_id', 'eje_tematico_id'], 'idx_pub_workspace_eje');
        });

        Schema::table('notas_prensa', function (Blueprint $table) {
            $table->index(['workspace_id', 'fecha_publicacion'], 'idx_notas_workspace_fecha');
            $table->index(['medio_prensa_id', 'fecha_publicacion'], 'idx_notas_medio_fecha');
            $table->index(['candidato_id', 'fecha_publicacion'], 'idx_notas_candidato_fecha');
            $table->index(['workspace_id', 'tono_mencion'], 'idx_notas_workspace_tono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropIndex('idx_pub_workspace_fecha');
            $table->dropIndex('idx_pub_candidato_fecha');
            $table->dropIndex('idx_pub_perfil_fecha');
            $table->dropIndex('idx_pub_workspace_pauta');
            $table->dropIndex('idx_pub_workspace_eje');
        });

        Schema::table('notas_prensa', function (Blueprint $table) {
            $table->dropIndex('idx_notas_workspace_fecha');
            $table->dropIndex('idx_notas_medio_fecha');
            $table->dropIndex('idx_notas_candidato_fecha');
            $table->dropIndex('idx_notas_workspace_tono');
        });
    }
};
