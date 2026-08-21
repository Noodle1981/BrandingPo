<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Workspace activo actualmente seleccionado por el usuario.
     * Un consultor puede tener acceso a varios workspaces; este campo
     * recuerda cuál está usando en esta sesión.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_workspace_id')
                ->nullable()
                ->after('role')
                ->constrained('workspaces')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_workspace_id']);
            $table->dropColumn('active_workspace_id');
        });
    }
};
