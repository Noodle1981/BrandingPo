<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\PerfilSocial;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricasCanalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([\Database\Seeders\UserSeeder::class, \Database\Seeders\PoliticaSeeder::class, \Database\Seeders\PublicacionSeeder::class]);
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
            ->has('historicoMediciones')
            ->has('topPublicaciones')
        );
    }
}
