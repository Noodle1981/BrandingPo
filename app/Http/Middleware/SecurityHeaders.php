<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Inyectar cabeceras HTTP de seguridad recomendadas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Forzar HSTS en conexiones HTTPS o entorno de producción
        if ($request->isSecure() || app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy adaptado para Inertia + Vite + Google Fonts + Redes
        if (app()->environment('local', 'testing')) {
            // En desarrollo se permite el servidor de Vite HMR (localhost / 127.0.0.1 en puertos dinámicos y WebSockets)
            $csp = [
                "default-src 'self' http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* http://127.0.0.1:*",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com http://localhost:* http://127.0.0.1:*",
                "font-src 'self' https://fonts.gstatic.com data: http://localhost:* http://127.0.0.1:*",
                "img-src 'self' data: blob: https: http://localhost:* http://127.0.0.1:*",
                "connect-src 'self' https: ws: wss: http://localhost:* http://127.0.0.1:* ws://localhost:* ws://127.0.0.1:*",
                "frame-ancestors 'self'",
            ];
        } else {
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: blob: https:",
                "connect-src 'self' https: ws: wss:",
                "frame-ancestors 'self'",
            ];
        }
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}
