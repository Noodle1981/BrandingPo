<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\Publicacion;
use App\Models\User;
use App\Services\PoliticaEngagementService;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoliticaEngagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    /**
     * Verificar que el cálculo de tracción normaliza con justicia entre candidatos Micro y Mega.
     */
    public function test_normalizacion_por_tier_compara_justamente_micro_y_mega(): void
    {
        $servicio = new PoliticaEngagementService();

        // Candidato MICRO (5,000 seguidores) con buen desempeño orgánico (6.5% TAP)
        // 6.5% está cerca del punto medio [5.0 - 10.0] -> espera score ~62
        $resMicro = $servicio->calcularTasaAceptacionReal([
            'seguidores_canal' => 5_000,
            'likes' => 200,          // 200 * 1 = 200
            'comentarios' => 25,     // 25 * 3 = 75
            'compartidos' => 10,     // 10 * 5 = 50
            'republicados' => 0,     // VTP = 325. TAP = (325/5000)*100 = 6.5%
            'es_pauta' => false,
        ]);

        // Candidato MEGA (1,500,000 seguidores) con excelente desempeño para su escala (1.5% TAP)
        // 1.5% es el punto medio exacto de [1.0 - 2.0] -> espera score 50
        $resMega = $servicio->calcularTasaAceptacionReal([
            'seguidores_canal' => 1_500_000,
            'likes' => 15_000,       // 15000 * 1 = 15000
            'comentarios' => 1_500,  // 1500 * 3 = 4500
            'compartidos' => 600,    // 600 * 5 = 3000
            'republicados' => 0,     // VTP = 22500. TAP = (22500/1500000)*100 = 1.5%
            'es_pauta' => false,
        ]);

        $this->assertEquals('micro', $resMicro['tier']);
        $this->assertEquals('mega', $resMega['tier']);

        // Ambos obtienen scores comparables (en la zona sólida 50-70), en lugar de que el mega sea calificado como pésimo
        $this->assertGreaterThanOrEqual(50, $resMicro['score_traccion_indexado']);
        $this->assertGreaterThanOrEqual(50, $resMega['score_traccion_indexado']);
        $this->assertFalse($resMicro['sospecha_de_bots']);
        $this->assertFalse($resMega['sospecha_de_bots']);
    }

    /**
     * Verificar que la detección forense activa alerta ante compra de likes (muchos likes, 0 comentarios).
     */
    public function test_detector_forense_identifica_granja_de_likes_sinteticos(): void
    {
        $servicio = new PoliticaEngagementService();

        // 800 likes pero solo 1 comentario (< 0.8% de comentarios respecto a likes)
        $res = $servicio->calcularTasaAceptacionReal([
            'seguidores_canal' => 20_000,
            'likes' => 800,
            'comentarios' => 1,
            'compartidos' => 20,
            'republicados' => 0,
            'es_pauta' => false,
        ]);

        $this->assertTrue($res['sospecha_de_bots']);
        $this->assertNotEmpty($res['alertas_forenses']);
        $this->assertStringContainsString('likes', $res['alertas_forenses'][0]);
    }

    /**
     * Verificar que el Feed expone analisis_traccion y score_traccion_indexado en la API para Vue.
     */
    public function test_feed_expone_analisis_traccion_normalizado(): void
    {
        $consultor = User::where('role', 'consultor')->first();

        $response = $this->actingAs($consultor)->get('/feed');
        $response->assertStatus(200);

        $page = $response->viewData('page');
        $feedPosts = collect($page['props']['publicaciones']['data'] ?? $page['props']['publicaciones'] ?? []);

        $primerPost = $feedPosts->first();
        $this->assertNotNull($primerPost);
        $this->assertArrayHasKey('analisis_traccion', $primerPost);
        $this->assertArrayHasKey('score_traccion_indexado', $primerPost['analisis_traccion']);
        $this->assertArrayHasKey('sospecha_de_bots', $primerPost['analisis_traccion']);
        $this->assertArrayHasKey('tap_politica_real', $primerPost['analisis_traccion']);
        $this->assertGreaterThanOrEqual(0, $primerPost['analisis_traccion']['score_traccion_indexado']);
        $this->assertLessThanOrEqual(100, $primerPost['analisis_traccion']['score_traccion_indexado']);
    }
}
