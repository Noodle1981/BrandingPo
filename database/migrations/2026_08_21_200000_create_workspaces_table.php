<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear la tabla principal de Workspaces (un workspace = una campaña / un cliente).
     */
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                                           // "Campaña Sisterna - Albardón 2025"
            $table->string('slug')->unique();                                   // "sisterna-albardon-2025"
            $table->string('nivel_politico')->default('intendente');            // 'intendente','gobernador','legislador_nacional','legislador_provincial','senador','concejal'
            $table->string('provincia')->default('San Juan');                   // provincia principal de operación
            $table->string('plan')->default('profesional');                     // 'basico','profesional','war_room' (para billing futuro)
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla pivot: un usuario puede pertenecer a varios workspaces con distintos roles
        Schema::create('workspace_user', function (Blueprint $table) {
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('consultor');                       // 'admin','consultor','visualizador' dentro de ESTE workspace
            $table->primary(['workspace_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_user');
        Schema::dropIfExists('workspaces');
    }
};
