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
        Schema::table('eje_tematicos', function (Blueprint $table) {
            $table->string('pilar_principal')->nullable()->after('workspace_id');
            $table->string('icono')->nullable()->after('color_badge');
            $table->integer('orden')->default(0)->after('icono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eje_tematicos', function (Blueprint $table) {
            $table->dropColumn(['pilar_principal', 'icono', 'orden']);
        });
    }
};
