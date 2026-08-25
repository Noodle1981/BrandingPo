<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PoliticaSeeder;
use Database\Seeders\PublicacionSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpactoElectoralTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserSeeder::class, PoliticaSeeder::class, PublicacionSeeder::class]);
    }

    public function test_usuario_puede_acceder_a_la_matriz_de_impacto_electoral(): void
    {
        $user = User::where('role', 'consultor')->first();

        $response = $this->actingAs($user)
            ->get(route('territorios.impacto-electoral'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Territorios/ImpactoElectoral')
            ->has('candidato')
            ->has('territorioActivo')
            ->has('departamentos')
            ->has('piramide')
            ->has('stats')
            ->has('redesImpacto')
            ->has('oportunidadesPauta')
        );
    }
}
