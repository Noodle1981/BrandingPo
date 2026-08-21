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
        Schema::table('territorios', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('territorios')->nullOnDelete();
            $table->string('codigo_indec')->nullable()->after('tipo');
            $table->decimal('latitud', 10, 7)->nullable()->after('codigo_indec');
            $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
            $table->decimal('poblacion_urbana_pct', 5, 2)->default(75.00)->after('padron_electoral');
            $table->decimal('poblacion_rural_pct', 5, 2)->default(25.00)->after('poblacion_urbana_pct');
            $table->decimal('hogares_nbi_pct', 5, 2)->default(15.00)->after('poblacion_rural_pct');
            $table->json('piramide_etaria')->nullable()->after('hogares_nbi_pct');
            $table->json('circuitos_electorales')->nullable()->after('piramide_etaria');
            $table->json('meta_electoral')->nullable()->after('circuitos_electorales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('territorios', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'codigo_indec',
                'latitud',
                'longitud',
                'poblacion_urbana_pct',
                'poblacion_rural_pct',
                'hogares_nbi_pct',
                'piramide_etaria',
                'circuitos_electorales',
                'meta_electoral',
            ]);
        });
    }
};
