<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAndRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@brandingpo.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@brandingpo.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_quick_login_works_for_all_roles(): void
    {
        // Admin
        $resAdmin = $this->post('/quick-login', ['role' => 'admin']);
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);

        // Consultor
        $resConsultor = $this->post('/quick-login', ['role' => 'consultor']);
        $this->assertEquals('consultor', auth()->user()->role);

        // Visualizador
        $resVis = $this->post('/quick-login', ['role' => 'visualizador']);
        $this->assertEquals('visualizador', auth()->user()->role);
    }

    public function test_visualizador_cannot_access_user_management(): void
    {
        $visualizador = User::factory()->create(['role' => 'visualizador']);

        $response = $this->actingAs($visualizador)->get('/usuarios');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/usuarios');
        $response->assertStatus(200);
    }
}
