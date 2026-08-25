<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicacionesAndFastFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    public function test_authenticated_user_can_view_social_feed(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();

        $response = $this->actingAs($visualizador)->get('/feed');
        $response->assertStatus(200);

        // Verify seeded publications exist
        $this->assertDatabaseHas('publicaciones', [
            'tipo_pauta' => 'pauta_paga',
            'monto_invertido_pauta' => 45000,
        ]);
    }

    public function test_feed_can_be_filtered_by_platform_and_pauta(): void
    {
        $consultor = User::where('role', 'consultor')->first();

        $response = $this->actingAs($consultor)->get('/feed?plataforma=instagram&tipo_pauta=pauta_paga');
        $response->assertStatus(200);
    }

    public function test_consultor_can_store_publication(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $candidato = Candidato::first();
        $perfil = $candidato->perfilesSociales->first();
        $eje = EjeTematico::first();

        $response = $this->actingAs($consultor)->post('/publicaciones', [
            'candidato_id' => $candidato->id,
            'perfil_social_id' => $perfil->id,
            'eje_tematico_id' => $eje->id,
            'fecha_publicacion' => now()->toDateTimeString(),
            'tipo_formato' => 'Reel',
            'tipo_pauta' => 'pauta_paga',
            'monto_invertido_pauta' => 35000,
            'vistas_organicas' => 12000,
            'vistas_pagadas' => 50000,
            'contenido_resumen' => 'Post de prueba con pauta pagada.',
            'total_likes' => 2400,
            'total_comentarios' => 120,
            'total_compartidos' => 310,
            'termometro_humor_social' => 5,
            'comentario_destacado' => 'Excelente propuesta para el municipio.',
            'figura_acompanante' => 'Gobernador Prov., Ministro',
        ]);

        $this->assertDatabaseHas('publicaciones', [
            'contenido_resumen' => 'Post de prueba con pauta pagada.',
            'total_vistas' => 62000, // 12000 + 50000
            'monto_invertido_pauta' => 35000,
        ]);
    }

    public function test_visualizador_cannot_post_publication(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();
        $candidato = Candidato::first();
        $perfil = $candidato->perfilesSociales->first();

        $response = $this->actingAs($visualizador)->post('/publicaciones', [
            'candidato_id' => $candidato->id,
            'perfil_social_id' => $perfil->id,
            'fecha_publicacion' => now()->toDateTimeString(),
            'tipo_formato' => 'Tweet',
            'tipo_pauta' => 'organico',
            'contenido_resumen' => 'Intento no autorizado de visualizador',
        ]);

        $this->assertDatabaseMissing('publicaciones', [
            'contenido_resumen' => 'Intento no autorizado de visualizador',
        ]);
    }
}
