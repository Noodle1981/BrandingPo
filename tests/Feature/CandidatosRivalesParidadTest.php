<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\Publicacion;
use App\Models\User;
use App\Models\Workspace;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidatosRivalesParidadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    public function test_show_candidato_rival_loads_with_paridad_total(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $rival = Candidato::where('es_propio', false)->first();

        $this->assertNotNull($rival, 'Debe existir al menos un candidato rival en los seeders');

        $response = $this->actingAs($consultor)->get("/candidatos/{$rival->id}");
        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('Candidatos/Show')
            ->has('candidato')
            ->has('candidato.tiers_desglose')
            ->has('candidato.total_seguidores_netos')
            ->has('candidato.penetracion_neta_pct')
            ->has('redes')
            ->has('publicaciones')
            ->has('ejes')
            ->has('ciclos')
            ->has('territorios')
            ->where('candidato.id', $rival->id)
            ->where('candidato.es_propio', false)
        );
    }

    public function test_can_create_publication_for_candidato_rival(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $rival = Candidato::where('es_propio', false)->first();
        $eje = EjeTematico::first();

        $response = $this->actingAs($consultor)->post('/publicaciones', [
            'candidato_id' => $rival->id,
            'plataforma' => 'instagram',
            'url_post' => 'https://www.instagram.com/p/DB123456_RivalPost/',
            'tipo_formato' => 'Foto',
            'tipo_pauta' => 'organico',
            'monto_invertido_pauta' => 0,
            'eje_tematico_id' => $eje?->id,
            'contenido_resumen' => 'Post de campaña del candidato opositor en acto partidario.',
            'total_likes' => 150,
            'total_comentarios' => 25,
            'total_compartidos' => 10,
            'fecha_publicacion' => now()->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('publicaciones', [
            'candidato_id' => $rival->id,
            'url_post' => 'https://instagram.com/p/DB123456_RivalPost',
            'contenido_resumen' => 'Post de campaña del candidato opositor en acto partidario.',
            'total_likes' => 150,
        ]);
    }

    public function test_candidato_rival_feed_displays_rival_posts(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $rival = Candidato::where('es_propio', false)->first();
        $perfil = \App\Models\PerfilSocial::firstOrCreate(
            ['candidato_id' => $rival->id, 'plataforma' => 'facebook'],
            ['handle_usuario' => 'rival_facebook', 'esta_activo' => true]
        );

        // Crear una publicación específica para este rival
        Publicacion::create([
            'workspace_id' => $rival->workspace_id,
            'candidato_id' => $rival->id,
            'perfil_social_id' => $perfil->id,
            'plataforma' => 'facebook',
            'url_post' => 'https://facebook.com/rival/posts/999888777',
            'tipo_formato' => 'Post',
            'tipo_pauta' => 'organico',
            'contenido_resumen' => 'Discurso del rival sobre obras públicas',
            'total_likes' => 200,
            'total_comentarios' => 30,
            'fecha_publicacion' => now(),
        ]);

        $response = $this->actingAs($consultor)->get("/candidatos/{$rival->id}");
        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('Candidatos/Show')
            ->has('publicaciones')
            ->where('publicaciones.0.contenido_resumen', 'Discurso del rival sobre obras públicas')
        );
    }

    public function test_candidato_rival_cannot_be_viewed_from_another_workspace(): void
    {
        $consultor = User::where('role', 'consultor')->first();

        // Crear un workspace alternativo con otro candidato rival
        $otroWorkspace = Workspace::create([
            'nombre' => 'Campaña Rival Externa',
            'slug' => 'campana-rival-externa',
            'nivel_politico' => 'intendente',
            'pais' => 'Argentina',
            'provincia' => 'San Juan',
        ]);

        $ciclo = \App\Models\CicloCampana::create([
            'workspace_id' => $otroWorkspace->id,
            'nombre' => 'Elecciones 2027',
            'anio' => 2027,
            'es_activo' => true,
        ]);

        $candidatoExterno = Candidato::create([
            'workspace_id' => $otroWorkspace->id,
            'ciclo_campana_id' => $ciclo->id,
            'nombre_completo' => 'Rival Infiltrado Externo',
            'partido_coalicion' => 'Partido Ajeno',
            'estado_politico' => 'opositor',
            'color_hex' => '#ef4444',
            'es_propio' => false,
        ]);

        // Intentar acceder desde el workspace activo del consultor debe lanzar 403
        $response = $this->actingAs($consultor)->get("/candidatos/{$candidatoExterno->id}");
        $response->assertStatus(403);
    }

    public function test_visualizador_cannot_modify_rival_data(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();
        $rival = Candidato::where('es_propio', false)->first();

        $response = $this->actingAs($visualizador)->put("/candidatos/{$rival->id}", [
            'nombre_completo' => 'Nombre Modificado Ilegal',
            'partido_coalicion' => $rival->partido_coalicion,
            'estado_politico' => $rival->estado_politico,
        ]);

        $response->assertStatus(403);
    }
}
