<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricasCanalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    public function test_usuario_puede_acceder_al_dashboard_de_metricas_del_canal()
    {
        $user = User::where('role', 'consultor')->first();
        $candidato = Candidato::where('es_propio', true)->first();

        $perfil = $candidato->perfilesSociales()->firstOrCreate(
            ['plataforma' => 'instagram'],
            [
                'handle_usuario' => '@federico__sisterna',
                'seguidores_actuales' => 2500,
                'seguidores_punto_cero' => 2000,
                'publicaciones_totales' => 45,
                'publicaciones_punto_cero' => 40,
                'esta_activo' => true,
            ]
        );

        $response = $this->actingAs($user)
            ->get(route('perfiles-sociales.metricas', $perfil));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Candidatos/MetricasCanal')
            ->has('candidato')
            ->has('perfilSocial')
            ->has('stats')
            ->has('benchmarks')
            ->has('frecuenciaPublicacion')
            ->has('organicoVsPauta')
            ->has('rendimientoPorFormato')
            ->has('consistenciaMensual')
            ->has('promedioVistasInfo')
            ->has('semaforoObjetivos')
            ->has('historicoMediciones')
            ->has('topPublicaciones')
            ->has('distribucionEjes')
        );
    }

    public function test_usuario_puede_acceder_al_dashboard_de_metricas_de_facebook_con_calculo_normalizado()
    {
        $user = User::where('role', 'consultor')->first();
        $candidato = Candidato::where('es_propio', true)->first();

        $perfilFb = $candidato->perfilesSociales()->firstOrCreate(
            ['plataforma' => 'facebook'],
            [
                'handle_usuario' => '@ahoraalbardon',
                'seguidores_actuales' => 9483,
                'seguidores_punto_cero' => 9000,
                'publicaciones_totales' => 120,
                'publicaciones_punto_cero' => 100,
                'esta_activo' => true,
            ]
        );

        $response = $this->actingAs($user)
            ->get(route('perfiles-sociales.metricas', $perfilFb));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Candidatos/MetricasCanal')
            ->where('perfilSocial.plataforma', 'facebook')
        );
    }

    public function test_usuario_puede_acceder_al_dashboard_de_metricas_de_tiktok_con_calculo_normalizado()
    {
        $user = User::where('role', 'consultor')->first();
        $candidato = Candidato::where('es_propio', true)->first();

        $perfilTt = $candidato->perfilesSociales()->firstOrCreate(
            ['plataforma' => 'tiktok'],
            [
                'handle_usuario' => '@federico.sisterna',
                'seguidores_actuales' => 1695,
                'seguidores_punto_cero' => 1500,
                'publicaciones_totales' => 20,
                'publicaciones_punto_cero' => 18,
                'esta_activo' => true,
            ]
        );

        $response = $this->actingAs($user)
            ->get(route('perfiles-sociales.metricas', $perfilTt));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Candidatos/MetricasCanal')
            ->where('perfilSocial.plataforma', 'tiktok')
        );
    }

    public function test_visualizador_puede_ver_dashboard_de_metricas_para_threads(): void
    {
        $user = User::where('role', 'visualizador')->first() ?? User::where('role', 'consultor')->first();
        $candidato = Candidato::where('es_propio', true)->first();

        $perfilThreads = $candidato->perfilesSociales()->updateOrCreate(
            ['plataforma' => 'threads'],
            [
                'handle_usuario' => '@federico.sisterna',
                'seguidores_actuales' => 2400,
                'seguidores_punto_cero' => 2000,
                'publicaciones_totales' => 15,
                'publicaciones_punto_cero' => 10,
                'esta_activo' => true,
            ]
        );

        $response = $this->actingAs($user)
            ->get(route('perfiles-sociales.metricas', $perfilThreads));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Candidatos/MetricasCanal')
            ->where('perfilSocial.plataforma', 'threads')
        );
    }
}
