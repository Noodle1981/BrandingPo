<?php

namespace Tests\Feature;

use App\Models\CicloCampana;
use App\Models\InformeEjecutivo;
use App\Models\PresupuestoPartida;
use App\Models\User;
use Database\Seeders\CalendarioAndBriefingSeeder;
use Database\Seeders\MediosAndCrisisSeeder;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioPresupuestoAndBriefingTest extends TestCase
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
            CalendarioAndBriefingSeeder::class,
        ]);
    }

    public function test_authenticated_user_can_view_calendario_presupuesto_and_briefings(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();

        $responseCal = $this->actingAs($visualizador)->get('/calendario');
        $responseCal->assertStatus(200);

        $responsePres = $this->actingAs($visualizador)->get('/presupuesto');
        $responsePres->assertStatus(200);

        $responseBrief = $this->actingAs($visualizador)->get('/briefings');
        $responseBrief->assertStatus(200);
    }

    public function test_consultor_can_create_calendar_event_and_budget_line(): void
    {
        $consultor = User::where('role', 'consultor')->first();
        $ciclo = CicloCampana::first();

        // 1. Calendario
        $responseCal = $this->actingAs($consultor)->post('/calendario', [
            'ciclo_campana_id' => $ciclo->id,
            'titulo' => 'Caravana Test Sprint 7',
            'fecha_inicio' => now()->addDays(2)->toDateTimeString(),
            'tipo_evento' => 'caravana',
            'lugar' => 'Distrito Norte',
            'estado' => 'programado',
        ]);
        $responseCal->assertRedirect(route('calendario.index'));
        $this->assertDatabaseHas('eventos_calendario', ['titulo' => 'Caravana Test Sprint 7']);

        // 2. Presupuesto
        $responsePres = $this->actingAs($consultor)->post('/presupuesto', [
            'ciclo_campana_id' => $ciclo->id,
            'categoria' => 'pauta_digital',
            'monto_asignado' => 500000,
            'monto_ejecutado' => 120000,
            'notas' => 'Prueba de partida presupuestaria',
        ]);
        $responsePres->assertRedirect(route('presupuesto.index'));
        $this->assertDatabaseHas('presupuesto_partidas', ['notas' => 'Prueba de partida presupuestaria']);

        // 3. Briefing
        $responseBrief = $this->actingAs($consultor)->post('/briefings', [
            'ciclo_campana_id' => $ciclo->id,
            'titulo' => 'Briefing Semanal Test',
            'periodo_cubierto' => 'Semana Test',
            'resumen_ejecutivo' => 'Resumen estratégico de prueba para el comando.',
        ]);
        $responseBrief->assertRedirect(route('briefings.index'));
        $this->assertDatabaseHas('informes_ejecutivos', ['titulo' => 'Briefing Semanal Test']);
    }

    public function test_briefing_show_page_renders_correctly(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();
        $informe = InformeEjecutivo::first();

        $response = $this->actingAs($visualizador)->get("/briefings/{$informe->id}");
        $response->assertStatus(200);
    }

    public function test_visualizador_cannot_mutate_presupuesto_or_calendario(): void
    {
        $visualizador = User::where('role', 'visualizador')->first();
        $ciclo = CicloCampana::first();

        $response = $this->actingAs($visualizador)->post('/presupuesto', [
            'ciclo_campana_id' => $ciclo->id,
            'categoria' => 'pauta_digital',
            'monto_asignado' => 999999,
        ]);

        $this->assertDatabaseMissing('presupuesto_partidas', ['monto_asignado' => 999999]);
    }
}