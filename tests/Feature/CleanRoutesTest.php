<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_all_routes_render_ok_on_clean_db(): void
    {
        $admin = User::where('role', 'admin')->first();

        $routes = [
            '/' => 'Dashboard',
            '/dashboard' => 'Dashboard Direct',
            '/mi-candidato' => 'Mi Candidato',
            '/candidatos' => 'Oposición y Rivales',
            '/candidatos/benchmarking' => 'Benchmarking',
            '/territorios' => 'Territorio & Demografía',
            '/feed' => 'Feed Social',
            '/feed?filtro=propio' => 'Feed Propio',
            '/territorios/impacto-electoral' => 'Matriz de Impacto Territorial',
            '/medios' => 'Observatorio de Medios',
            '/crisis' => 'Centro de Crisis',
            '/predictor' => 'Predictor de Pauta',
            '/calendario' => 'Calendario',
            '/presupuesto' => 'Presupuesto',
            '/briefings' => 'Briefings',
            '/usuarios' => 'Gestión de Usuarios',
        ];

        foreach ($routes as $uri => $name) {
            $response = $this->actingAs($admin)->get($uri);
            $response->assertStatus(200);
            echo "✅ [200 OK] - $name ($uri)\n";
        }
    }
}
