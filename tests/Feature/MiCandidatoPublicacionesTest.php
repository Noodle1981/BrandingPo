<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Publicacion;
use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiCandidatoPublicacionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    public function test_mi_candidato_page_loads_with_publicaciones_and_ejes(): void
    {
        $consultor = User::where('role', 'consultor')->first();

        $response = $this->actingAs($consultor)->get('/mi-candidato');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Candidatos/MiPerfil')
                ->has('candidato')
                ->has('redes')
                ->has('publicaciones')
                ->has('ejes')
        );
    }

    public function test_can_create_publication_for_social_network(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $candidato = Candidato::where('es_propio', true)->first();
        $eje = EjeTematico::first();

        $response = $this->actingAs($consultor)->post('/publicaciones', [
            'candidato_id' => $candidato->id,
            'plataforma' => 'instagram',
            'url_post' => 'https://www.instagram.com/reel/DaVanf_zIei/?utm_source=ig_web_copy_link&igsi=MzRlODBiNWFlZA==',
            'tipo_formato' => 'Reel',
            'tipo_pauta' => 'pauta_paga',
            'monto_invertido_pauta' => 20000,
            'vistas_organicas' => 2500,
            'vistas_pagadas' => 10000,
            'eje_tematico_id' => $eje?->id,
            'contenido_resumen' => 'Reel deportivo de actividades en el polideportivo municipal.',
            'total_likes' => 81,
            'total_comentarios' => 8,
            'total_compartidos' => 3,
            'fecha_publicacion' => now()->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('publicaciones', [
            'candidato_id' => $candidato->id,
            'url_post' => 'https://www.instagram.com/reel/DaVanf_zIei/?utm_source=ig_web_copy_link&igsi=MzRlODBiNWFlZA==',
            'tipo_formato' => 'Reel',
            'tipo_pauta' => 'pauta_paga',
            'total_likes' => 81,
            'total_comentarios' => 8,
        ]);
    }

    public function test_visualizador_cannot_create_publication(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();
        $candidato = Candidato::where('es_propio', true)->first();

        $response = $this->actingAs($visualizador)->post('/publicaciones', [
            'candidato_id' => $candidato->id,
            'plataforma' => 'instagram',
            'tipo_formato' => 'Reel',
            'tipo_pauta' => 'organico',
            'contenido_resumen' => 'Intento no autorizado de visualizador',
            'fecha_publicacion' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(403);
    }
}
