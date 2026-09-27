<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('eje_tematicos', 'es_eje_campana')) {
            Schema::table('eje_tematicos', function (Blueprint $table) {
                $table->boolean('es_eje_campana')->default(true)->after('pilar_principal');
            });
        }

        // 1. Infraestructura y Seguridad Local (El Eje del Orden)
        $ordenSlugs = [
            'obras-e-infraestructura',
            'obras-publicas-e-infraestructura',
            'seguridad-ciudadana-y-prevencion',
            'movilidad-urbana-y-transporte',
            'transito-y-control-urbano',
            'medio-ambiente-y-sustentabilidad',
        ];
        DB::table('eje_tematicos')
            ->whereIn('slug', $ordenSlugs)
            ->update([
                'pilar_principal' => '1. Infraestructura y Seguridad Local (El Eje del Orden)',
                'es_eje_campana' => true,
            ]);

        // 2. Desarrollo, Empleo y Comercio (El Eje del Futuro)
        $futuroSlugs = [
            'produccion-y-empleo',
            'desarrollo-economico-y-empleo',
            'innovacion-y-capacitacion',
            'educacion-e-innovacion',
            'turismo-y-comercio',
            'juventud',
        ];
        DB::table('eje_tematicos')
            ->whereIn('slug', $futuroSlugs)
            ->update([
                'pilar_principal' => '2. Desarrollo, Empleo y Comercio (El Eje del Futuro)',
                'es_eje_campana' => true,
            ]);

        // 3. Cercanía, Salud y Comunidad (El Eje Humano)
        $humanoSlugs = [
            'salud-y-deportes',
            'infancia-y-adultos-mayores',
            'desarrollo-humano-y-social',
            'genero-e-inclusion-social',
            'cultura-y-eventos-comunitarios',
        ];
        DB::table('eje_tematicos')
            ->whereIn('slug', $humanoSlugs)
            ->update([
                'pilar_principal' => '3. Cercanía, Salud y Comunidad (El Eje Humano)',
                'es_eje_campana' => true,
            ]);

        // Gestión Institucional & Otros (Fuera de los 3 Ejes de Campaña)
        $institucionalSlugs = [
            'atencion-ciudadana-y-tramites-digitales',
            'transparencia-y-participacion-ciudadana',
            'politica-y-participacion',
        ];
        DB::table('eje_tematicos')
            ->whereIn('slug', $institucionalSlugs)
            ->update([
                'pilar_principal' => 'Gestión Institucional & Otros',
                'es_eje_campana' => false,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('eje_tematicos', 'es_eje_campana')) {
            Schema::table('eje_tematicos', function (Blueprint $table) {
                $table->dropColumn('es_eje_campana');
            });
        }
    }
};
