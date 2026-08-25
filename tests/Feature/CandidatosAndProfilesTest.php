<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidatosAndProfilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class]);
    }

    public function test_authenticated_user_can_list_candidatos(): void
    {
        $user = User::where('role', 'visualizador')->first();

        $response = $this->actingAs($user)->get('/candidatos');
        $response->assertStatus(200);

        // Verify seeded profiles exist
        $this->assertDatabaseHas('candidatos', [
            'nombre_completo' => 'Federico Sisterna',
            'es_propio' => true,
            'estado_politico' => 'candidato',
        ]);

        $this->assertDatabaseHas('candidatos', [
            'nombre_completo' => 'Carlos Morales',
            'estado_politico' => 'opositor',
        ]);
    }

    public function test_filtering_candidatos_by_estado_politico(): void
    {
        $user = User::where('role', 'consultor')->first();

        $response = $this->actingAs($user)->get('/candidatos?estado=opositor');
        $response->assertStatus(200);
    }

    public function test_consultor_can_create_a_candidate(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $ciclo = CicloCampana::first();

        $response = $this->actingAs($consultor)->post('/candidatos', [
            'nombre_completo' => 'Nuevo Candidato Test',
            'partido_coalicion' => 'Alianza Progreso',
            'cargo_aspirado' => 'Concejal',
            'estado_politico' => 'candidato',
            'ciclo_campana_id' => $ciclo->id,
            'es_propio' => false,
            'color_hex' => '#06b6d4',
        ]);

        $this->assertDatabaseHas('candidatos', ['nombre_completo' => 'Nuevo Candidato Test']);
        $response->assertRedirect();
    }

    public function test_visualizador_cannot_create_a_candidate(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();
        $ciclo = CicloCampana::first();

        $response = $this->actingAs($visualizador)->post('/candidatos', [
            'nombre_completo' => 'Intento Ilegal',
            'partido_coalicion' => 'Partido X',
            'estado_politico' => 'candidato',
            'ciclo_campana_id' => $ciclo->id,
        ]);

        // Handled by CheckCanWrite middleware -> back with error
        $this->assertDatabaseMissing('candidatos', ['nombre_completo' => 'Intento Ilegal']);
    }
}