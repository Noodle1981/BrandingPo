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
        Schema::table('medios_prensa', function (Blueprint $table) {
            $table->string('url_facebook')->nullable()->after('url_sitio');
            $table->text('avatar_url')->nullable()->after('url_facebook');
            $table->string('feed_rss_url')->nullable()->after('avatar_url');
            $table->json('configuracion_scraping')->nullable()->after('feed_rss_url');
            $table->timestamp('ultima_sincronizacion_at')->nullable()->after('configuracion_scraping');
        });

        Schema::table('notas_prensa', function (Blueprint $table) {
            $table->string('origen_tipo')->default('web')->after('candidato_id'); // 'web', 'facebook'
            $table->string('tipo_mencion')->default('titular')->after('origen_tipo'); // 'etiqueta_directa', 'titular', 'cuerpo'
            $table->json('reacciones_desglose')->nullable()->after('interacciones_en_redes_del_medio');
            $table->decimal('puntuacion_sentimiento', 4, 2)->default(0.00)->after('reacciones_desglose'); // -1.00 a 1.00
            $table->string('raw_post_id')->nullable()->index()->after('puntuacion_sentimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_prensa', function (Blueprint $table) {
            $table->dropColumn([
                'origen_tipo',
                'tipo_mencion',
                'reacciones_desglose',
                'puntuacion_sentimiento',
                'raw_post_id',
            ]);
        });

        Schema::table('medios_prensa', function (Blueprint $table) {
            $table->dropColumn([
                'url_facebook',
                'avatar_url',
                'feed_rss_url',
                'configuracion_scraping',
                'ultima_sincronizacion_at',
            ]);
        });
    }
};
