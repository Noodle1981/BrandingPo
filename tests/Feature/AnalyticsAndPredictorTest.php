<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\User;
use App\Services\AdsImpactPredictorService;
use Database\Seeders\MediosAndCrisisSeeder;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsAndPredictorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserSeeder::class,
            PoliticaSeeder::class,
            PublicacionSeeder::class,
            MediosAndCrisisSeeder::class,
        ]);
    }

    public function test_authenticated_user_can_view_analytics_and_predictor(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();

        $responseAnalytics = $this->actingAs($visualizador)->get('/analytics');
        $responseAnalytics->assertStatus(200);

        $responsePredictor = $this->actingAs($visualizador)->get('/predictor');
        $responsePredictor->assertStatus(200);
    }

    public function test_predictor_service_calculates_impact_and_proximity(): void
    {
        $service = app(AdsImpactPredictorService::class);
        $resultado = $service->predecirImpacto(50000, 'Reel', 'instagram');

        $this->assertArrayHasKey('vistas_esperadas', $resultado);
        $this->assertArrayHasKey('porcentaje_proximidad', $resultado);
        $this->assertArrayHasKey('recomendacion_estrategica', $resultado);

        $this->assertGreaterThan(0, $resultado['vistas_esperadas']);
        $this->assertGreaterThanOrEqual(60, $resultado['porcentaje_proximidad']);
        $this->assertLessThanOrEqual(100, $resultado['porcentaje_proximidad']);
    }

    public function test_predict_api_endpoint_returns_json_simulation(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $candidato = Candidato::first();

        $response = $this->actingAs($consultor)->postJson('/analytics/predict', [
            'monto' => 80000,
            'formato' => 'Video',
            'plataforma' => 'facebook',
            'candidato_id' => $candidato->id,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'monto_invertido',
            'formato',
            'plataforma',
            'porcentaje_proximidad',
            'vistas_esperadas',
            'vistas_minimas',
            'vistas_maximas',
            'likes_estimados',
            'cpv_estimado_ars',
            'recomendacion_estrategica',
        ]);
    }
}
