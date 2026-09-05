<?php

namespace Tests\Feature;

use App\Helpers\SecurityHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verificar que las cabeceras HTTP de seguridad estén presentes en las respuestas.
     */
    public function test_respuestas_web_incluyen_cabeceras_de_seguridad(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Content-Security-Policy');
    }

    /**
     * Verificar prevención de SSRF contra IPs privadas, localhost y metadatos de nube.
     */
    public function test_helper_seguridad_bloquea_urls_ssrf_maliciosas(): void
    {
        // Rechazar localhost y loopback
        $this->assertFalse(SecurityHelper::esUrlSegura('http://localhost:8000'));
        $this->assertFalse(SecurityHelper::esUrlSegura('http://127.0.0.1/admin'));
        $this->assertFalse(SecurityHelper::esUrlSegura('http://0.0.0.0/'));

        // Rechazar IP de metadatos AWS / Azure / GCP
        $this->assertFalse(SecurityHelper::esUrlSegura('http://169.254.169.254/latest/meta-data'));

        // Rechazar redes privadas RFC 1918
        $this->assertFalse(SecurityHelper::esUrlSegura('http://10.0.0.1/secrets'));
        $this->assertFalse(SecurityHelper::esUrlSegura('http://192.168.1.1/router'));
        $this->assertFalse(SecurityHelper::esUrlSegura('http://172.16.0.1/api'));

        // Rechazar esquemas no HTTP
        $this->assertFalse(SecurityHelper::esUrlSegura('file:///etc/passwd'));
        $this->assertFalse(SecurityHelper::esUrlSegura('ftp://server/file'));
        $this->assertFalse(SecurityHelper::esUrlSegura('javascript:alert(1)'));

        // Aceptar URLs públicas válidas
        $this->assertTrue(SecurityHelper::esUrlSegura('https://www.google.com'));
        $this->assertTrue(SecurityHelper::esUrlSegura('https://instagram.com/p/abc123'));
    }

    /**
     * Verificar que quick-login esté bloqueado en producción.
     */
    public function test_quick_login_bloqueado_en_entorno_produccion(): void
    {
        App::detectEnvironment(fn () => 'production');

        $response = $this->withoutMiddleware()
            ->post('/quick-login', ['role' => 'admin']);

        $response->assertStatus(403);
    }

    /**
     * Verificar que quick-login valida los roles permitidos en desarrollo.
     */
    public function test_quick_login_valida_rol_permitido(): void
    {
        $response = $this->post('/quick-login', ['role' => 'super_hacker_rol']);

        $response->assertSessionHasErrors('role');
    }

    /**
     * Verificar aislamiento multi-tenant: prevenir IDOR en candidatos entre workspaces.
     */
    public function test_bloqueo_acceso_cross_workspace_candidato(): void
    {
        $ws1 = \App\Models\Workspace::create(['nombre' => 'Campaña San Juan', 'slug' => 'san-juan']);
        $ws2 = \App\Models\Workspace::create(['nombre' => 'Campaña Mendoza', 'slug' => 'mendoza']);

        $cicloWs2 = \App\Models\CicloCampana::create([
            'workspace_id' => $ws2->id,
            'nombre' => 'Elecciones Mendoza 2027',
            'anio' => 2027,
            'es_activo' => true,
        ]);

        $user = User::create([
            'name' => 'Consultor WS1',
            'email' => 'consultor.ws1@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultor',
            'active_workspace_id' => $ws1->id,
        ]);
        $ws1->usuarios()->attach($user->id, ['role' => 'consultor']);

        $candidatoWs2 = \App\Models\Candidato::create([
            'workspace_id' => $ws2->id,
            'ciclo_campana_id' => $cicloWs2->id,
            'nombre_completo' => 'Rival en Mendoza',
            'partido_coalicion' => 'Frente Mendoza',
            'estado_politico' => 'opositor',
            'es_propio' => false,
        ]);

        // Intentar ver candidato del workspace 2 desde sesión del workspace 1
        $response = $this->actingAs($user)->get("/candidatos/{$candidatoWs2->id}");
        $response->assertStatus(403);

        // Intentar eliminar candidato del workspace 2 desde workspace 1
        $responseDelete = $this->actingAs($user)->delete("/candidatos/{$candidatoWs2->id}");
        $responseDelete->assertStatus(403);

        // Confirmar que el candidato no fue eliminado
        $this->assertDatabaseHas('candidatos', ['id' => $candidatoWs2->id]);
    }

    /**
     * Verificar aislamiento multi-tenant: prevenir manipulación de publicaciones de otro workspace.
     */
    public function test_bloqueo_acceso_cross_workspace_publicacion(): void
    {
        $ws1 = \App\Models\Workspace::create(['nombre' => 'Campaña A', 'slug' => 'campana-a']);
        $ws2 = \App\Models\Workspace::create(['nombre' => 'Campaña B', 'slug' => 'campana-b']);

        $cicloWs2 = \App\Models\CicloCampana::create([
            'workspace_id' => $ws2->id,
            'nombre' => 'Elecciones 2027 B',
            'anio' => 2027,
            'es_activo' => true,
        ]);

        $user = User::create([
            'name' => 'Admin Campaña A',
            'email' => 'admin.a@example.com',
            'password' => bcrypt('password'),
            'role' => 'consultor',
            'active_workspace_id' => $ws1->id,
        ]);
        $ws1->usuarios()->attach($user->id, ['role' => 'consultor']);

        $candidatoWs2 = \App\Models\Candidato::create([
            'workspace_id' => $ws2->id,
            'ciclo_campana_id' => $cicloWs2->id,
            'nombre_completo' => 'Candidato B',
            'partido_coalicion' => 'Partido B',
            'estado_politico' => 'candidato',
            'es_propio' => true,
        ]);

        $perfilWs2 = \App\Models\PerfilSocial::create([
            'candidato_id' => $candidatoWs2->id,
            'plataforma' => 'instagram',
            'handle_usuario' => 'candidatob',
            'esta_activo' => true,
            'esta_verificado' => false,
        ]);

        $pubWs2 = \App\Models\Publicacion::create([
            'workspace_id' => $ws2->id,
            'candidato_id' => $candidatoWs2->id,
            'perfil_social_id' => $perfilWs2->id,
            'plataforma' => 'instagram',
            'contenido_resumen' => 'Publicación secreta de Campaña B',
            'fecha_publicacion' => now(),
            'tipo_formato' => 'Post',
            'tipo_pauta' => 'organico',
        ]);

        // Intentar eliminar publicación de ws2 desde usuario de ws1
        $response = $this->actingAs($user)->delete("/publicaciones/{$pubWs2->id}");
        $response->assertStatus(403);

        $this->assertDatabaseHas('publicaciones', ['id' => $pubWs2->id]);
    }
}
