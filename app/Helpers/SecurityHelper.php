<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SecurityHelper
{
    /**
     * Valida que una URL sea segura para peticiones salientes (Prevención de SSRF).
     * Rechaza esquemas no HTTP(S), hosts locales, IPs privadas y rangos reservados.
     */
    public static function esUrlSegura(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $parts = parse_url(trim($url));
        if (! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host']);

        // Bloqueo de localhost y nombres de host internos conocidos
        if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.internal')
            || str_ends_with($host, '.lan')) {
            return false;
        }

        // Si es una IP directa o se puede resolver por DNS
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Rechazar rangos privados (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16) y reservados (169.254.0.0/16 cloud metadata)
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        if (filter_var($ip, FILTER_VALIDATE_IP, $flags) === false) {
            return false;
        }

        return true;
    }

    /**
     * Registro de auditoría para eventos de seguridad.
     */
    public static function logEvento(string $evento, array $contexto = []): void
    {
        $payload = array_merge([
            'usuario_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'timestamp' => now()->toIso8601String(),
        ], $contexto);

        try {
            Log::channel('security')->warning("[SEGURIDAD] {$evento}", $payload);
        } catch (\Throwable) {
            Log::warning("[SEGURIDAD] {$evento}", $payload);
        }
    }
}
