<?php

namespace App\Services;

use App\Helpers\SecurityHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaStorageService
{
    /**
     * Descargar y guardar localmente una imagen externa de redes sociales.
     *
     * @param  string|null  $urlExterna  URL externa (ej: Instagram/Facebook CDN)
     * @param  string|null  $rutaActual  Ruta local existente para limpiar si cambia
     * @return string|null  Ruta pública accesible (ej: /storage/publicaciones/...)
     */
    public function guardarMediaLocal(?string $urlExterna, ?string $rutaActual = null): ?string
    {
        if (empty($urlExterna)) {
            return $rutaActual;
        }

        $urlExterna = trim($urlExterna);

        // Si ya es una ruta local en /storage/, retornarla intacta
        if (str_starts_with($urlExterna, '/storage/') || str_starts_with($urlExterna, 'storage/')) {
            return str_starts_with($urlExterna, '/') ? $urlExterna : '/'.$urlExterna;
        }

        // Validación de seguridad SSRF: solo admitir URLs públicas y esquemas seguros
        if (! SecurityHelper::esUrlSegura($urlExterna)) {
            return $rutaActual ?: $urlExterna;
        }

        try {
            // Descargar imagen con User-Agent de crawler social para evitar bloqueos (excluye SVG para prevenir XSS)
            $response = Http::withHeaders([
                'User-Agent' => 'Twitterbot/1.0',
                'Accept' => 'image/avif,image/webp,image/apng,image/jpeg,image/png;q=0.9,*/*;q=0.8',
            ])->timeout(10)->get($urlExterna);

            if (! $response->successful()) {
                $response = Http::withHeaders([
                    'User-Agent' => 'WhatsApp/2.21.12.21 A',
                    'Accept' => 'image/avif,image/webp,image/apng,image/jpeg,image/png;q=0.9,*/*;q=0.8',
                ])->timeout(10)->get($urlExterna);
            }

            if ($response->successful()) {
                $body = $response->body();
                $contentType = $response->header('Content-Type');

                // Validar binario de imagen real y obtener extensión segura
                $extension = $this->resolverExtensionSegura($body, $contentType);

                if ($extension !== null) {
                    $nombreArchivo = 'media_'.substr(md5($urlExterna), 0, 16).'_'.time().'.'.$extension;
                    $pathRelativo = 'publicaciones/'.$nombreArchivo;

                    // Guardar en el disco público de Laravel (storage/app/public/publicaciones/...)
                    Storage::disk('public')->put($pathRelativo, $body);

                    // Si existía un archivo previo local y es diferente, limpiarlo
                    if (! empty($rutaActual) && str_contains($rutaActual, '/storage/publicaciones/')) {
                        $archivoViejo = str_replace('/storage/', '', $rutaActual);
                        if (Storage::disk('public')->exists($archivoViejo)) {
                            Storage::disk('public')->delete($archivoViejo);
                        }
                    }

                    return '/storage/'.$pathRelativo;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo descargar la imagen localmente desde {$urlExterna}: ".$e->getMessage());
        }

        // Fallback: si falló la descarga, mantener la ruta anterior o la externa
        return $rutaActual ?: $urlExterna;
    }

    /**
     * Descargar y guardar localmente un avatar de perfil o candidato.
     *
     * @param  string|null  $urlExterna  URL de avatar externa
     * @param  string  $prefijo  Prefijo identificador (ej: candidato_10)
     * @param  string|null  $rutaActual  Ruta previa para limpiar
     * @return string|null  Ruta pública accesible (ej: /storage/avatars/...)
     */
    public function guardarAvatarLocal(?string $urlExterna, string $prefijo = 'avatar', ?string $rutaActual = null): ?string
    {
        if (empty($urlExterna)) {
            return $rutaActual;
        }

        $urlExterna = trim($urlExterna);

        if (str_starts_with($urlExterna, '/storage/') || str_starts_with($urlExterna, 'storage/')) {
            return str_starts_with($urlExterna, '/') ? $urlExterna : '/'.$urlExterna;
        }

        // Validación de seguridad SSRF: solo admitir URLs públicas y esquemas seguros
        if (! SecurityHelper::esUrlSegura($urlExterna)) {
            return $rutaActual ?: $urlExterna;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Twitterbot/1.0',
                'Accept' => 'image/avif,image/webp,image/apng,image/jpeg,image/png;q=0.9,*/*;q=0.8',
            ])->timeout(10)->get($urlExterna);

            if (! $response->successful()) {
                $response = Http::withHeaders([
                    'User-Agent' => 'WhatsApp/2.21.12.21 A',
                    'Accept' => 'image/avif,image/webp,image/apng,image/jpeg,image/png;q=0.9,*/*;q=0.8',
                ])->timeout(10)->get($urlExterna);
            }

            if ($response->successful()) {
                $body = $response->body();
                $contentType = $response->header('Content-Type');

                $extension = $this->resolverExtensionSegura($body, $contentType);

                if ($extension !== null) {
                    $nombreArchivo = $prefijo.'_'.time().'.'.$extension;
                    $pathRelativo = 'avatars/'.$nombreArchivo;

                    Storage::disk('public')->put($pathRelativo, $body);

                    if (! empty($rutaActual) && str_contains($rutaActual, '/storage/avatars/')) {
                        $archivoViejo = str_replace('/storage/', '', $rutaActual);
                        if (Storage::disk('public')->exists($archivoViejo)) {
                            Storage::disk('public')->delete($archivoViejo);
                        }
                    }

                    return '/storage/'.$pathRelativo;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("No se pudo descargar avatar localmente desde {$urlExterna}: ".$e->getMessage());
        }

        return $rutaActual ?: $urlExterna;
    }

    /**
     * Valida de forma estricta que el cuerpo descargado sea una imagen binaria legítima (prevención de SVG XSS y payloads maliciosos).
     * Retorna la extensión segura ('jpg', 'png', 'webp', 'gif') o null si es inválida/insegura.
     */
    private function resolverExtensionSegura(string $body, ?string $contentType): ?string
    {
        if (strlen($body) < 100) {
            return null;
        }

        // Rechazar si comienza con tags HTML, XML o SVG
        $inicio = strtolower(substr(ltrim($body), 0, 50));
        if (str_starts_with($inicio, '<!doctype') || str_starts_with($inicio, '<html') || str_starts_with($inicio, '<?xml') || str_starts_with($inicio, '<svg')) {
            return null;
        }

        $info = @getimagesizefromstring($body);
        if ($info && isset($info['mime'])) {
            return match ($info['mime']) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                default => null,
            };
        }

        // Detección complementaria por magic bytes
        if (str_starts_with($body, "\xFF\xD8\xFF")) {
            return 'jpg';
        }
        if (str_starts_with($body, "\x89PNG\r\n\x1a\n")) {
            return 'png';
        }
        if (str_starts_with($body, 'RIFF') && substr($body, 8, 4) === 'WEBP') {
            return 'webp';
        }

        return null;
    }
}
