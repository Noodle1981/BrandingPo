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
            $table->unsignedBigInteger('visualizaciones_totales')->default(0)->after('me_gusta_totales');
            $table->unsignedBigInteger('visualizaciones_punto_cero')->default(0)->after('me_gusta_punto_cero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perfil_socials', function (Blueprint $table) {
            $table->dropColumn(['visualizaciones_totales', 'visualizaciones_punto_cero']);
        });
    }
};
