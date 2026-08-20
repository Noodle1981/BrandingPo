<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialProfileScraperService
{
    /**
     * Intentar extraer datos públicos (foto, seguidores, seguidos, posts) a partir de la URL.
     */
    public function scrapeProfile(string $url, string $plataforma): array
    {
        $url = trim($url);
        $result = [
            'success' => false,
            'plataforma' => $plataforma,
            'handle_usuario' => '',
            'foto_perfil_url' => '',
            'seguidores' => null,
            'seguidos' => null,
            'publicaciones' => null,
            'nombre_completo' => '',
            'bio' => '',
            'raw_description' => '',
            'mensaje' => '',
        ];

        if (empty($url)) {
            $result['mensaje'] = 'La URL está vacía.';
            return $result;
        }

        try {
            switch ($plataforma) {
                case 'instagram':
                    return $this->scrapeInstagram($url, $result);
                case 'tiktok':
                    return $this->scrapeTikTok($url, $result);
                case 'youtube':
                    return $this->scrapeYouTube($url, $result);
                case 'x_twitter':
                    return $this->scrapeXTwitter($url, $result);
                case 'facebook':
                    return $this->scrapeFacebook($url, $result);
                default:
                    return $this->scrapeOpenGraphGenerico($url, $result);
            }
        } catch (\Throwable $e) {
            Log::warning("Error scraping perfil social ({$url}): " . $e->getMessage());
            $result['mensaje'] = 'No se pudo leer automáticamente debido a protecciones de la red social. Puedes ingresar los números manualmente.';
            return $result;
        }
    }

    /**
     * Extractor para Instagram (Meta tags & OpenGraph).
     * Formato clásico: "1,360 Followers, 578 Following, 64 Posts - See Instagram photos and videos..."
     */
    protected function scrapeInstagram(string $url, array $result): array
    {
        // Extraer handle de la URL
        preg_match('/instagram\.com\/([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        $username = $handleMatch[1] ?? '';
        if ($username) {
            $result['handle_usuario'] = '@' . ltrim($username, '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
        ])->timeout(8)->get($url);

        if (!$response->successful()) {
            // Intentar con bot user-agent
            $response = Http::withHeaders([
                'User-Agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            ])->timeout(8)->get($url);
        }

        $html = $response->body();

        // 1. Extraer og:description / meta description
        $description = '';
        if (preg_match('/<meta\s+(?:property|name)=["\'](?:og:description|description)["\']\s+content=["\']([^"\']+)["\']/i', $html, $descMatch)) {
            $description = html_entity_decode($descMatch[1]);
            $result['raw_description'] = $description;
        }

        // Parsear: "1,360 Followers, 578 Following, 64 Posts" o en español "1.360 seguidores, 578 seguidos, 64 publicaciones"
        if ($description) {
            // Seguidores
            if (preg_match('/([\d\.,KMkm]+)\s*(?:Followers|seguidores)/i', $description, $m)) {
                $result['seguidores'] = $this->parseFormattedNumber($m[1]);
            }
            // Seguidos
            if (preg_match('/([\d\.,KMkm]+)\s*(?:Following|seguidos)/i', $description, $m)) {
                $result['seguidos'] = $this->parseFormattedNumber($m[1]);
            }
            // Publicaciones / Posts
            if (preg_match('/([\d\.,KMkm]+)\s*(?:Posts|publicaciones)/i', $description, $m)) {
                $result['publicaciones'] = $this->parseFormattedNumber($m[1]);
            }
        }

        // 2. Extraer Foto de perfil (og:image)
        if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1]);
        }

        // 3. Extraer Nombre / Título
        if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']+)["\']/i', $html, $titleMatch)) {
            $title = html_entity_decode($titleMatch[1]);
            // Ejemplo: "Federico Sisterna (@federico__sisterna) • Instagram photos and videos"
            if (preg_match('/^([^(•]+)/', $title, $tm)) {
                $result['nombre_completo'] = trim($tm[1]);
            }
        }

        $result['success'] = !empty($result['foto_perfil_url']) || !is_null($result['seguidores']) || !empty($result['handle_usuario']);
        $result['mensaje'] = $result['success']
            ? '¡Datos extraídos con éxito desde Instagram!'
            : 'Instagram protegió la lectura directa. Puedes completar los campos manualmente.';

        return $result;
    }

    /**
     * Extractor para TikTok.
     */
    protected function scrapeTikTok(string $url, array $result): array
    {
        preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        if (!empty($handleMatch[1])) {
            $result['handle_usuario'] = '@' . ltrim($handleMatch[1], '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'facebookexternalhit/1.1',
        ])->timeout(8)->get($url);

        $html = $response->body();

        if (preg_match('/<meta\s+(?:property|name)=["\'](?:og:description|description)["\']\s+content=["\']([^"\']+)["\']/i', $html, $descMatch)) {
            $desc = html_entity_decode($descMatch[1]);
            if (preg_match('/([\d\.,KMkm]+)\s*(?:Followers|Seguidores)/i', $desc, $m)) {
                $result['seguidores'] = $this->parseFormattedNumber($m[1]);
            }
            if (preg_match('/([\d\.,KMkm]+)\s*(?:Likes|Me gusta)/i', $desc, $m)) {
                $result['total_likes'] = $this->parseFormattedNumber($m[1]);
            }
        }

        if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1]);
        }

        $result['success'] = true;
        return $result;
    }

    /**
     * Extractor para YouTube.
     */
    protected function scrapeYouTube(string $url, array $result): array
    {
        preg_match('/youtube\.com\/(@[a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        if (!empty($handleMatch[1])) {
            $result['handle_usuario'] = $handleMatch[1];
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->timeout(8)->get($url);

        $html = $response->body();

        if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1]);
        }

        if (preg_match('/<meta\s+property=["\']og:title["\']\s+content=["\']([^"\']+)["\']/i', $html, $titleMatch)) {
            $result['nombre_completo'] = html_entity_decode($titleMatch[1]);
        }

        $result['success'] = true;
        return $result;
    }

    /**
     * Extractor para X / Twitter.
     */
    protected function scrapeXTwitter(string $url, array $result): array
    {
        preg_match('/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)/i', $url, $handleMatch);
        if (!empty($handleMatch[1])) {
            $result['handle_usuario'] = '@' . $handleMatch[1];
        }
        $result['success'] = !empty($result['handle_usuario']);
        return $result;
    }

    /**
     * Extractor para Facebook.
     */
    protected function scrapeFacebook(string $url, array $result): array
    {
        preg_match('/facebook\.com\/([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        if (!empty($handleMatch[1])) {
            $result['handle_usuario'] = '@' . $handleMatch[1];
        }
        $result['success'] = !empty($result['handle_usuario']);
        return $result;
    }

    /**
     * Fallback OpenGraph genérico.
     */
    protected function scrapeOpenGraphGenerico(string $url, array $result): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->timeout(6)->get($url);

        $html = $response->body();

        if (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1]);
        }

        $result['success'] = true;
        return $result;
    }

    /**
     * Parsear cadenas numéricas como "1,360", "1.360", "14.5K", "1.2M".
     */
    protected function parseFormattedNumber(string $str): int
    {
        $str = trim(str_replace([' ', ','], '', $str));

        if (preg_match('/^([\d\.]+)[Kk]$/', $str, $m)) {
            return (int)((float)$m[1] * 1000);
        }

        if (preg_match('/^([\d\.]+)[Mm]$/', $str, $m)) {
            return (int)((float)$m[1] * 1000000);
        }

        // Si tiene puntos como separador de miles: "1.360"
        if (substr_count($str, '.') === 1 && strlen(substr($str, strpos($str, '.') + 1)) === 3) {
            $str = str_replace('.', '', $str);
        }

        return (int)filter_var($str, FILTER_SANITIZE_NUMBER_INT);
    }
}
