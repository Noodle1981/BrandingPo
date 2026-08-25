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
        Schema::create('perfil_social_metricas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_social_id')->constrained('perfil_socials')->onDelete('cascade');
            $table->date('fecha')->index();
            $table->unsignedBigInteger('seguidores')->default(0);
            $table->unsignedBigInteger('seguidos')->default(0);
            $table->unsignedInteger('publicaciones_totales')->default(0);
            $table->unsignedBigInteger('me_gusta_totales')->default(0);
            $table->unsignedBigInteger('visualizaciones_totales')->default(0);
            $table->bigInteger('crecimiento_seguidores_dia')->default(0);
            $table->bigInteger('crecimiento_seguidos_dia')->default(0);
            $table->integer('crecimiento_posts_dia')->default(0);
            $table->bigInteger('crecimiento_seguidores_neto')->default(0);
            $table->integer('crecimiento_posts_neto')->default(0);
            $table->string('fuente')->default('manual'); // 'manual', 'auto_scraper', 'cron_24h'
            $table->json('raw_metadata')->nullable();
            $table->timestamps();

            $table->unique(['perfil_social_id', 'fecha']);
        });

        Schema::table('perfil_socials', function (Blueprint $table) {
            $table->timestamp('ultima_auditoria_at')->nullable()->after('demografia_interna_propia');
            $table->bigInteger('delta_seguidores_24h')->default(0)->after('ultima_auditoria_at');
            $table->bigInteger('delta_seguidos_24h')->default(0)->after('delta_seguidores_24h');
            $table->integer('delta_posts_24h')->default(0)->after('delta_seguidos_24h');
            $table->bigInteger('delta_me_gusta_24h')->default(0)->after('delta_posts_24h');
            $table->bigInteger('delta_views_24h')->default(0)->after('delta_me_gusta_24h');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perfil_socials', function (Blueprint $table) {
            $table->dropColumn([
                'ultima_auditoria_at',
                'delta_seguidores_24h',
                'delta_seguidos_24h',
                'delta_posts_24h',
                'delta_me_gusta_24h',
                'delta_views_24h',
            ]);
        });

        Schema::dropIfExists('perfil_social_metricas');
    }
};
