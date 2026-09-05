<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\Publicacion;
use App\Models\User;
use App\Services\SocialProfileScraperService;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PautaDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    /**
     * Verificar que el scraper detecta parámetros de Meta Ads o UTM pagos en la URL.
     */
    public function test_scraper_detecta_parametros_de_pauta_en_url(): void
    {
        $scraper = new SocialProfileScraperService();

        // 1. URL con fbclid
        $urlConFbclid = 'https://instagram.com/reel/DcrP2plT5TP?fbclid=IwAR2X_example_meta_token';
        $res = $scraper->scrapePost($urlConFbclid, 'instagram');

        $this->assertTrue($res['sospecha_pauta']);
        $this->assertEquals('organico_impulsado', $res['tipo_pauta_sugerido']);
        $this->assertStringContainsString('fbclid', $res['motivo_sospecha_pauta']);
        $this->assertStringNotContainsString('fbclid', $res['url_post']);

        // 2. URL con ad_id
        $urlConAdId = 'https://www.facebook.com/watch/?v=123456789&ad_id=987654321';
        $resAd = $scraper->scrapePost($urlConAdId, 'facebook');

        $this->assertTrue($resAd['sospecha_pauta']);
        $this->assertEquals('organico_impulsado', $resAd['tipo_pauta_sugerido']);
        $this->assertStringNotContainsString('ad_id', $resAd['url_post']);

        // 3. URL limpia orgánica estándar
        $urlLimpia = 'https://instagram.com/p/C-exampleClean';
        $resLimpia = $scraper->scrapePost($urlLimpia, 'instagram');

        $this->assertFalse($resLimpia['sospecha_pauta']);
        $this->assertEquals('organico', $resLimpia['tipo_pauta_sugerido']);
    }

    /**
     * Verificar que la sincronización individual detecta un salto abrupto en post orgánico y sugiere boost.
     */
    public function test_sincronizacion_detecta_salto_de_reacciones_y_sugiere_boost(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $candidato = Candidato::first();
        $perfil = $candidato->perfilesSociales->first();

        // Crear una publicación orgánica con 50 likes
        $pub = Publicacion::create([
            'workspace_id' => $candidato->workspace_id,
            'candidato_id' => $candidato->id,
            'perfil_social_id' => $perfil->id,
            'fecha_publicacion' => now()->subDays(2),
            'tipo_formato' => 'Reel',
            'tipo_pauta' => 'organico',
            'monto_invertido_pauta' => 0,
            'url_post' => 'https://instagram.com/p/DcrP2plT5TP',
            'contenido_resumen' => 'Reel con pauta posterior para test',
            'total_likes' => 50,
            'total_comentarios' => 10,
            'total_vistas' => 1000,
        ]);

        // Simular respuesta del scraper con 203 likes (salto de +153)
        $scraperMock = $this->createMock(SocialProfileScraperService::class);
        $scraperMock->method('scrapePost')->willReturn([
            'success' => true,
            'total_likes' => 203,
            'total_comentarios' => 12,
            'total_vistas' => 5000,
            'media_url' => '',
            'sospecha_pauta' => false,
        ]);
        $this->app->instance(SocialProfileScraperService::class, $scraperMock);

        $response = $this->actingAs($consultor)
            ->postJson("/publicaciones/{$pub->id}/sincronizar");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'total_likes' => 203,
                'delta_likes' => 153,
                'sospecha_pauta' => true,
                'tipo_pauta_sugerido' => 'organico_impulsado',
            ]);

        $this->assertDatabaseHas('publicaciones', [
            'id' => $pub->id,
            'total_likes' => 203,
        ]);
    }
}
