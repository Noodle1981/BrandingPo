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
}
