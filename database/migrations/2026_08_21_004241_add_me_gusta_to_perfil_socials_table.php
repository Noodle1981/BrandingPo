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
            $table->unsignedBigInteger('me_gusta_totales')->default(0)->after('publicaciones_totales');
            $table->unsignedBigInteger('me_gusta_punto_cero')->default(0)->after('publicaciones_punto_cero');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perfil_socials', function (Blueprint $table) {
            $table->dropColumn(['me_gusta_totales', 'me_gusta_punto_cero']);
        });
    }
};
