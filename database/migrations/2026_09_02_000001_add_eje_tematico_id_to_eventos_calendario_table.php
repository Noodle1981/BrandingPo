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
        Schema::table('eventos_calendario', function (Blueprint $table) {
            $table->foreignId('eje_tematico_id')
                ->nullable()
                ->after('candidato_id')
                ->constrained('eje_tematicos')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos_calendario', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eje_tematico_id');
        });
    }
};
