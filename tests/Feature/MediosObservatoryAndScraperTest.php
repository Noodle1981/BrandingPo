<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\MedioPrensa;
use App\Models\NotaPrensa;
use App\Models\PerfilSocial;
use App\Models\Territorio;
use App\Models\User;
use App\Models\Workspace;
use App\Services\GenericMediaScraperService;
use App\Services\SocialProfileScraperService;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediosObservatoryAndScraperTest extends TestCase
{
    use RefreshDatabase;

    protected Workspace $workspace;
    protected Candidato $candidatoPropio;
    protected User $admin;
    protected User $consultor;
    protected User $visualizador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserSeeder::class,
            PoliticaSeeder::class,
            PublicacionSeeder::class,
        ]);

        $this->workspace = Workspace::first();
        $this->admin = User::where('role', 'admin')->first();
        $this->consultor = User::where('role', 'consultor')->first();
        $this->visualizador = User::where('role', 'visualizador')->first();
        $this->candidatoPropio = Candidato::where('workspace_id', $this->workspace->id)
            ->where('es_propio', true)
            ->first();
    }

    public function test_viewing_medios_observatory_renders_threshold_and_stats(): void
    {
        // 1. With 0 media, threshold should not be met
        $res = $this->actingAs($this->visualizador)->get('/medios');
        $res->assertStatus(200);
        $res->assertInertia(fn ($page) => $page
            ->component('Medios/Index')
            ->where('umbral.total_medios', 0)
            ->where('umbral.cumple_umbral', false)
            ->where('umbral.faltantes', 5)
        );

        // 2. Create 5 media outlets
        for ($i = 1; $i <= 5; $i++) {
            MedioPrensa::create([
                'workspace_id' => $this->workspace->id,
                'nombre' => "Medio {$i}",
                'tipo_medio' => 'digital',
                'url_sitio' => "https://medio{$i}.com.ar",
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
            ]);
        }

        $res2 = $this->actingAs($this->visualizador)->get('/medios');
        $res2->assertStatus(200);
        $res2->assertInertia(fn ($page) => $page
            ->where('umbral.total_medios', 5)
            ->where('umbral.cumple_umbral', true)
            ->where('umbral.faltantes', 0)
        );
    }

    public function test_consultor_can_crud_medio_prensa(): void
    {
        // CREATE
        $payload = [
            'nombre' => 'Diario El Zonda',
            'tipo_medio' => 'digital',
            'url_sitio' => 'https://www.diarioelzonda.com.ar',
            'url_facebook' => 'https://www.facebook.com/diarioelzonda',
            'avatar_url' => 'https://www.diarioelzonda.com.ar/logo.png',
            'feed_rss_url' => 'https://www.diarioelzonda.com.ar/feed',
            'alcance_tipo' => 'provincial',
            'sesgo_editorial_estimado' => 'opositor',
        ];

        $resCreate = $this->actingAs($this->consultor)->post('/medios', $payload);
        $resCreate->assertRedirect(route('medios.index'));

        $this->assertDatabaseHas('medios_prensa', [
            'workspace_id' => $this->workspace->id,
            'nombre' => 'Diario El Zonda',
            'feed_rss_url' => 'https://www.diarioelzonda.com.ar/feed',
        ]);

        $medio = MedioPrensa::where('nombre', 'Diario El Zonda')->first();

        // UPDATE
        $resUpdate = $this->actingAs($this->consultor)->put("/medios/{$medio->id}", array_merge($payload, [
            'nombre' => 'Diario El Zonda Actualizado',
            'sesgo_editorial_estimado' => 'independiente',
        ]));
        $resUpdate->assertRedirect(route('medios.index'));

        $this->assertDatabaseHas('medios_prensa', [
            'id' => $medio->id,
            'nombre' => 'Diario El Zonda Actualizado',
            'sesgo_editorial_estimado' => 'independiente',
        ]);

        // DELETE
        $resDelete = $this->actingAs($this->consultor)->delete("/medios/{$medio->id}");
        $resDelete->assertRedirect(route('medios.index'));

        $this->assertDatabaseMissing('medios_prensa', [
            'id' => $medio->id,
        ]);
    }

    public function test_visualizador_cannot_mutate_medios_or_notas(): void
    {
        $medio = MedioPrensa::create([
            'workspace_id' => $this->workspace->id,
            'nombre' => 'Medio Test',
            'tipo_medio' => 'digital',
            'alcance_tipo' => 'local',
            'sesgo_editorial_estimado' => 'independiente',
        ]);

        // Post medio
        $this->actingAs($this->visualizador)
            ->post('/medios', [
                'nombre' => 'Intento Ilegal',
                'tipo_medio' => 'digital',
                'alcance_tipo' => 'local',
                'sesgo_editorial_estimado' => 'independiente',
            ])
            ->assertStatus(403);

        // Put medio
        $this->actingAs($this->visualizador)
            ->put("/medios/{$medio->id}", [
                'nombre' => 'Intento Update',
                'tipo_medio' => 'digital',
                'alcance_tipo' => 'local',
                'sesgo_editorial_estimado' => 'independiente',
            ])
            ->assertStatus(403);

        // Delete medio
        $this->actingAs($this->visualizador)
            ->delete("/medios/{$medio->id}")
            ->assertStatus(403);
    }

    public function test_ssrf_prevention_on_medio_urls(): void
    {
        $this->actingAs($this->consultor)
            ->post('/medios', [
                'nombre' => 'Malicious SSRF Outlet',
                'tipo_medio' => 'digital',
                'url_sitio' => 'http://169.254.169.254/latest/meta-data',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
            ])
            ->assertSessionHasErrors(['url_sitio']);

        $this->actingAs($this->consultor)
            ->post('/medios', [
                'nombre' => 'Localhost SSRF Outlet',
                'tipo_medio' => 'digital',
                'url_sitio' => 'http://localhost:8000/internal-admin',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
            ])
            ->assertSessionHasErrors(['url_sitio']);
    }

    public function test_generic_media_scraper_detects_candidate_mentions_and_sentiment(): void
    {
        $scraper = app(GenericMediaScraperService::class);
        $candidatos = Candidato::where('workspace_id', $this->workspace->id)
            ->with('perfilesSociales')
            ->get();

        // 1. Detección por Nombre Completo
        $texto1 = "El intendente Federico Sisterna inauguró obras de pavimentación en el departamento.";
        $mencion1 = $scraper->detectarMencionCandidato($texto1, $candidatos);

        $this->assertNotNull($mencion1);
        $this->assertEquals($this->candidatoPropio->id, $mencion1['candidato']->id);
        $this->assertEquals('titular', $mencion1['tipo_mencion']);

        // Evaluación de tono favorable
        $eval1 = $scraper->evaluarTonoYSentimiento("Federico Sisterna inauguró obras", "Avance histórico en pavimentación", 'web');
        $this->assertEquals('favorable', $eval1['tono']);
        $this->assertGreaterThan(0, $eval1['score']);

        // 2. Detección de tono crítico con palabras clave
        $eval2 = $scraper->evaluarTonoYSentimiento("Denuncian colapso y demoras en el sistema", "Polémica por falta de inversión", 'web');
        $this->assertEquals('critico', $eval2['tono']);
        $this->assertLessThan(0, $eval2['score']);

        // 3. Evaluación de Facebook con alerta roja de enojo (😡 > 15%)
        $reaccionesCriticas = [
            'likes' => 10,
            'love' => 2,
            'haha' => 1,
            'wow' => 0,
            'sad' => 5,
            'angry' => 20, // 20 / 38 = 52.6% > 15%
        ];
        $eval3 = $scraper->evaluarTonoYSentimiento("Reclamo vecinal", "Vecinos protestan", 'facebook', $reaccionesCriticas);
        $this->assertEquals('critico', $eval3['tono']);
        $this->assertLessThan(0, $eval3['score']);
    }

    public function test_sincronizar_medio_endpoint_executes_and_redirects(): void
    {
        $medio = MedioPrensa::create([
            'workspace_id' => $this->workspace->id,
            'nombre' => 'Diario Test Sync',
            'tipo_medio' => 'digital',
            'url_sitio' => 'https://www.diariosync.com.ar',
            'alcance_tipo' => 'provincial',
            'sesgo_editorial_estimado' => 'independiente',
        ]);

        $res = $this->actingAs($this->consultor)->post("/medios/{$medio->id}/sincronizar");
        $res->assertRedirect(route('medios.index'));
        $res->assertSessionHas('success');

        $medio->refresh();
        $this->assertNotNull($medio->ultima_sincronizacion_at);
    }
}
