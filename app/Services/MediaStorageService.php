<?php

namespace App\Services;

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

        // Si no es un enlace HTTP(S), retornar intacto
        if (! str_starts_with($urlExterna, 'http://') && ! str_starts_with($urlExterna, 'https://')) {
            return $urlExterna;
        }

        try {
            // Descargar imagen con User-Agent de crawler social para evitar bloqueos
            $response = Http::withHeaders([
                'User-Agent' => 'Twitterbot/1.0',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])->timeout(10)->get($urlExterna);

            if (! $response->successful()) {
                $response = Http::withHeaders([
                    'User-Agent' => 'WhatsApp/2.21.12.21 A',
                ])->timeout(10)->get($urlExterna);
            }

            if ($response->successful()) {
                $body = $response->body();

                // Verificar que no sea un payload vacío o error HTML
                if (strlen($body) > 300 && ! str_starts_with($body, '<!DOCTYPE html') && ! str_starts_with($body, '<html')) {
                    // Determinar extensión según Content-Type o default jpg
                    $contentType = $response->header('Content-Type');
                    $extension = 'jpg';
                    if (str_contains($contentType, 'png')) {
                        $extension = 'png';
                    } elseif (str_contains($contentType, 'webp')) {
                        $extension = 'webp';
                    }

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
}
