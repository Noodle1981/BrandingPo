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

    /**
     * Verificar que delta_likes_atribuibles calcula correctamente la diferencia
     * entre la métrica actual del post y el corte base del evento.
     */
    public function test_delta_likes_atribuibles_calcula_diferencia_correcta(): void
    {
        $candidato = Candidato::first();
        $perfil = $candidato->perfilesSociales->first();

        // Crear publicación con 98 likes y evento con base en 65 likes
        $pub = Publicacion::create([
            'workspace_id' => $candidato->workspace_id,
            'candidato_id' => $candidato->id,
            'perfil_social_id' => $perfil->id,
            'fecha_publicacion' => now()->subDays(3),
            'tipo_formato' => 'Reel',
            'tipo_pauta' => 'organico_impulsado',
            'monto_invertido_pauta' => 5000,
            'url_post' => 'https://facebook.com/reel/1724495535488555',
            'contenido_resumen' => 'Reel Facebook impulsado con corte',
            'total_likes' => 98,
            'total_comentarios' => 12,
            'total_vistas' => 3500,
        ]);

        $evento = \App\Models\PublicacionPautaEvento::create([
            'publicacion_id' => $pub->id,
            'tipo_pauta_anterior' => 'organico',
            'tipo_pauta_nuevo' => 'organico_impulsado',
            'monto_anterior' => 0,
            'monto_nuevo' => 5000,
            'fecha_evento' => now()->subDay(),
            'seguidores_canal_snapshot' => 1200,
            'likes_snapshot' => 65,
            'comentarios_snapshot' => 4,
            'vistas_snapshot' => 1000,
            'origen' => 'manual',
            'notas' => 'Corte booster de prueba',
        ]);

        // Verificamos el accesor del modelo directo
        $this->assertEquals(33, $evento->delta_likes_atribuibles);
        $this->assertEquals(8, $evento->delta_comentarios_atribuibles);
        $this->assertEquals(2500, $evento->delta_vistas_atribuibles);

        // Verificamos respuesta en el endpoint Feed
        $consultor = User::where('role', 'consultor')->first();
        $response = $this->actingAs($consultor)->get('/feed');
        $response->assertStatus(200);

        // Verificar que en las props de Inertia el delta es 33
        $page = $response->viewData('page');
        $feedPosts = collect($page['props']['publicaciones']['data'] ?? $page['props']['publicaciones'] ?? []);
        $postEnFeed = $feedPosts->firstWhere('id', $pub->id);

        $this->assertNotNull($postEnFeed);
        $this->assertNotEmpty($postEnFeed['pauta_eventos']);
        $this->assertEquals(33, $postEnFeed['pauta_eventos'][0]['delta_likes_atribuibles']);
        $this->assertEquals(65, $postEnFeed['pauta_eventos'][0]['likes_snapshot']);
    }
}
