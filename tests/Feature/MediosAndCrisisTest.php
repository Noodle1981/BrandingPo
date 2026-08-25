<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\EventoCrisis;
use App\Models\MedioPrensa;
use App\Models\User;
use Database\Seeders\MediosAndCrisisSeeder;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediosAndCrisisTest extends TestCase
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

    public function test_authenticated_user_can_view_medios_observatory(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();

        $response = $this->actingAs($visualizador)->get('/medios');
        $response->assertStatus(200);

        $this->assertDatabaseHas('medios_prensa', [
            'nombre' => 'La Voz del Interior',
        ]);

        $this->assertDatabaseHas('notas_prensa', [
            'tono_mencion' => 'favorable',
        ]);
    }

    public function test_authenticated_user_can_view_crisis_center(): void
    {
        $consultor = User::where('role', 'consultor')->first();

        $response = $this->actingAs($consultor)->get('/crisis');
        $response->assertStatus(200);

        $this->assertDatabaseHas('eventos_crisis', [
            'nivel_gravedad' => 'critico',
        ]);
    }

    public function test_consultor_can_store_clipping_and_crisis(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $medio = MedioPrensa::first();
        $candidato = Candidato::first();

        // 1. Clipping
        $responseNota = $this->actingAs($consultor)->post('/medios/clipping', [
            'medio_prensa_id' => $medio->id,
            'candidato_id' => $candidato->id,
            'fecha_publicacion' => now()->toDateString(),
            'titulo' => 'Nota de Prueba Consultor',
            'tono_mencion' => 'favorable',
        ]);
        $responseNota->assertRedirect(route('medios.index'));
        $this->assertDatabaseHas('notas_prensa', ['titulo' => 'Nota de Prueba Consultor']);

        // 2. Crisis
        $responseCrisis = $this->actingAs($consultor)->post('/crisis', [
            'candidato_id' => $candidato->id,
            'titulo' => 'Crisis de Prueba',
            'fecha_evento' => now()->toDateTimeString(),
            'nivel_gravedad' => 'moderado',
            'estado' => 'abierto',
            'minutos_tiempo_respuesta' => 15,
        ]);
        $responseCrisis->assertRedirect(route('crisis.index'));
        $this->assertDatabaseHas('eventos_crisis', ['titulo' => 'Crisis de Prueba']);
    }

    public function test_consultor_can_resolve_crisis(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $crisis = EventoCrisis::where('estado', '!=', 'resuelto')->first();

        $response = $this->actingAs($consultor)->put("/crisis/{$crisis->id}", [
            'estado' => 'resuelto',
            'estrategia_contencion' => 'Solucionado con éxito.',
        ]);

        $response->assertRedirect(route('crisis.index'));
        $this->assertDatabaseHas('eventos_crisis', [
            'id' => $crisis->id,
            'estado' => 'resuelto',
        ]);
    }

    public function test_visualizador_cannot_mutate_clipping_or_crisis(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();
        $medio = MedioPrensa::first();
        $candidato = Candidato::first();

        $response = $this->actingAs($visualizador)->post('/medios/clipping', [
            'medio_prensa_id' => $medio->id,
            'candidato_id' => $candidato->id,
            'fecha_publicacion' => now()->toDateString(),
            'titulo' => 'Intento Ilegal Visualizador',
            'tono_mencion' => 'favorable',
        ]);

        $this->assertDatabaseMissing('notas_prensa', ['titulo' => 'Intento Ilegal Visualizador']);
    }
}
