<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\PerfilSocial;
use App\Models\PerfilSocialMetrica;
use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaPerfilesSocialesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    public function test_can_record_metrics_and_calculate_deltas(): void
    {
        $candidato = Candidato::where('es_propio', true)->first();
        $perfil = $candidato->perfilesSociales()->firstOrCreate(
            ['plataforma' => 'instagram'],
            ['handle_usuario' => '@candidato', 'esta_activo' => true]
        );
        $perfil->update([
            'seguidores_actuales' => 1000,
            'seguidores_punto_cero' => 1000,
            'publicaciones_totales' => 50,
            'publicaciones_punto_cero' => 50,
        ]);

        // First audit (e.g. +25 followers)
        $metrica = $perfil->registrarMedicion([
            'seguidores' => 1025,
            'seguidos' => 500,
            'publicaciones' => 52,
        ], 'manual');

        $this->assertEquals(25, $metrica->crecimiento_seguidores_dia);
        $this->assertEquals(25, $metrica->crecimiento_seguidores_neto);
        $this->assertEquals(2, $metrica->crecimiento_posts_dia);
        $this->assertEquals(1025, $perfil->fresh()->seguidores_actuales);
        $this->assertEquals(25, $perfil->fresh()->delta_seguidores_24h);
    }

    public function test_can_call_refrescar_perfil_social_endpoint(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $candidato = Candidato::where('es_propio', true)->first();
        $perfil = $candidato->perfilesSociales()->firstOrCreate(
            ['plataforma' => 'instagram'],
            [
                'handle_usuario' => '@federico__sisterna',
                'url_perfil' => 'https://www.instagram.com/federico__sisterna/',
                'seguidores_actuales' => 1359,
                'seguidores_punto_cero' => 1359,
                'esta_activo' => true,
            ]
        );

        $response = $this->actingAs($consultor)->postJson("/perfiles-sociales/{$perfil->id}/refrescar");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('perfil_social_metricas', [
            'perfil_social_id' => $perfil->id,
        ]);
    }

    public function test_auditar_perfiles_artisan_command_executes_successfully(): void
    {
        $candidato = Candidato::where('es_propio', true)->first();
        $candidato->perfilesSociales()->firstOrCreate(
            ['plataforma' => 'instagram'],
            [
                'handle_usuario' => '@federico__sisterna',
                'url_perfil' => 'https://www.instagram.com/federico__sisterna/',
                'esta_activo' => true,
            ]
        );

        $this->artisan('app:auditar-perfiles-sociales')
            ->assertSuccessful();
    }
}
