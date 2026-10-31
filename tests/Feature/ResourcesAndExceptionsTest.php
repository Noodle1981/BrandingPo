<?php

namespace Tests\Feature;

use App\Exceptions\ExternalServiceException;
use App\Http\Resources\CandidatoResource;
use App\Http\Resources\MedioResource;
use App\Http\Resources\PublicacionResource;
use App\Http\Resources\TerritorioResource;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\MedioPrensa;
use App\Models\Publicacion;
use App\Models\Territorio;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ResourcesAndExceptionsTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $workspace;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workspace = Workspace::create([
            'nombre' => 'Workspace Test Resources',
            'slug' => 'workspace-test-resources',
            'activo' => true,
        ]);

        $this->user = User::factory()->create([
            'active_workspace_id' => $this->workspace->id,
            'role' => 'admin',
        ]);
    }

    public function test_candidato_resource_transforma_campos_de_manera_segura(): void
    {
        $ciclo = CicloCampana::create([
            'workspace_id' => $this->workspace->id,
            'nombre' => 'Campaña 2027',
            'anio' => 2027,
            'es_activo' => true,
        ]);

        $candidato = Candidato::create([
            'workspace_id' => $this->workspace->id,
            'ciclo_campana_id' => $ciclo->id,
            'nombre_completo' => 'Juan Perez',
            'partido_coalicion' => 'Frente Electoral',
            'cargo_aspirado' => 'Intendente',
            'estado_politico' => 'candidato',
            'color_hex' => '#06b6d4',
            'es_propio' => true,
        ]);

        $resource = new CandidatoResource($candidato);
        $data = $resource->resolve();

        $this->assertEquals($candidato->id, $data['id']);
        $this->assertEquals('Juan Perez', $data['nombre_completo']);
        $this->assertTrue($data['es_propio']);
        $this->assertEquals('Intendente', $data['cargo_aspirado']);
        $this->assertArrayHasKey('color_identidad', $data);
        $this->assertArrayNotHasKey('deleted_at', $data);
    }

    public function test_medio_resource_transforma_metricas_y_avatar(): void
    {
        $medio = MedioPrensa::create([
            'workspace_id' => $this->workspace->id,
            'nombre' => 'Diario de Cuyo',
            'tipo_medio' => 'web',
            'alcance_tipo' => 'provincial',
            'url_sitio' => 'https://diariodecuyo.com.ar',
        ]);

        $resource = new MedioResource($medio);
        $data = $resource->resolve();

        $this->assertEquals('Diario de Cuyo', $data['nombre']);
        $this->assertEquals('web', $data['tipo_medio']);
        $this->assertArrayHasKey('notas_prensa_count', $data);
    }

    public function test_territorio_resource_serializa_metricas_censales(): void
    {
        $territorio = Territorio::create([
            'workspace_id' => $this->workspace->id,
            'nombre' => 'Capital',
            'tipo' => 'departamento',
            'padron_electoral' => 95000,
            'poblacion_total' => 120000,
        ]);

        $resource = new TerritorioResource($territorio);
        $data = $resource->resolve();

        $this->assertEquals('Capital', $data['nombre']);
        $this->assertEquals(95000, $data['padron_electoral']);
        $this->assertEquals(120000, $data['poblacion_total']);
    }

    public function test_manejo_centralizado_de_external_service_exception_en_json(): void
    {
        Route::get('/test-external-exception', function () {
            throw new ExternalServiceException('Meta Graph API no responde.', 'Meta');
        });

        $response = $this->actingAs($this->user)
            ->getJson('/test-external-exception');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'mensaje' => 'Meta Graph API no responde.',
                'servicio' => 'Meta',
                'codigo_error' => 'EXTERNAL_SERVICE_UNAVAILABLE',
            ]);
    }

    public function test_manejo_centralizado_de_connection_timeout_en_json(): void
    {
        Route::get('/test-connection-timeout', function () {
            throw new ConnectionException('Connection timed out after 5000 milliseconds');
        });

        $response = $this->actingAs($this->user)
            ->getJson('/test-connection-timeout');

        $response->assertStatus(504)
            ->assertJson([
                'success' => false,
                'codigo_error' => 'EXTERNAL_CONNECTION_TIMEOUT',
            ]);
    }
}
