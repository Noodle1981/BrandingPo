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
                case 'facebook':
                    return $this->scrapeFacebook($url, $result);
                case 'tiktok':
                    return $this->scrapeTikTok($url, $result);
                case 'youtube':
                    return $this->scrapeYouTube($url, $result);
                case 'x_twitter':
                    return $this->scrapeXTwitter($url, $result);
                case 'threads':
                    return $this->scrapeThreads($url, $result);
                case 'linkedin':
                    return $this->scrapeLinkedIn($url, $result);
                default:
                    return $this->scrapeOpenGraphGenerico($url, $result);
            }
        } catch (\Throwable $e) {
            Log::warning("Error scraping perfil social ({$url}): ".$e->getMessage());
            $result['mensaje'] = 'Error de conexión. Puedes completar los números manualmente.';

            return $result;
        }
    }

    /**
     * Extractor para Instagram (usando User-Agents que reciben metadatos completos).
     */
    protected function scrapeInstagram(string $url, array $result): array
    {
        // 1. Extraer username de la URL
        preg_match('/instagram\.com\/([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        $username = $handleMatch[1] ?? '';
        if ($username) {
            $result['handle_usuario'] = '@'.ltrim($username, '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->timeout(8)->get($url);

        if (! $response->successful()) {
            $response = Http::withHeaders([
                'User-Agent' => 'WhatsApp/2.21.12.21 A',
            ])->timeout(8)->get($url);
        }

        $html = $response->body();

        // 2. Extraer og:description / meta description
        $description = '';
        if (preg_match('/<meta[^>]+(?:property="og:description"|name="description")[^>]+content="([^"]+)"/i', $html, $descMatch)) {
            $description = html_entity_decode($descMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $result['raw_description'] = $description;
        }

        if ($description) {
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:Followers|seguidores)/i', $description, $m)) {
                $result['seguidores'] = $this->parseFormattedNumber($m[1]);
            }
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:Following|seguidos)/i', $description, $m)) {
                $result['seguidos'] = $this->parseFormattedNumber($m[1]);
            }
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:Posts|publicaciones)/i', $description, $m)) {
                $result['publicaciones'] = $this->parseFormattedNumber($m[1]);
            }
            if (preg_match('/from\s+([^(@•]+)/i', $description, $nm)) {
                $result['nombre_completo'] = trim(html_entity_decode($nm[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }
        }

        // 3. Extraer Foto de perfil (og:image)
        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 4. Extraer og:title
        if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $titleMatch)) {
            $title = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (empty($result['nombre_completo']) && preg_match('/^([^(•]+)/', $title, $tm)) {
                $result['nombre_completo'] = trim($tm[1]);
            }
        }

        $result['success'] = ! empty($result['foto_perfil_url']) || ! is_null($result['seguidores']);
        $result['mensaje'] = $result['success']
            ? '¡Datos de Instagram leídos exitosamente!'
            : 'Instagram protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Extractor para Facebook (Páginas y Perfiles públicos).
     */
    protected function scrapeFacebook(string $url, array $result): array
    {
        preg_match('/facebook\.com\/([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        $username = $handleMatch[1] ?? '';
        if ($username && ! in_array(strtolower($username), ['pages', 'profile.php', 'groups'])) {
            $result['handle_usuario'] = '@'.ltrim($username, '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
        ])->timeout(8)->get($url);

        if (! $response->successful()) {
            $response = Http::withHeaders([
                'User-Agent' => 'facebookexternalhit/1.1',
            ])->timeout(8)->get($url);
        }

        $html = $response->body();

        // 1. Extraer Foto de perfil (og:image)
        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 2. Extraer Título (Nombre)
        if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $titleMatch)) {
            $title = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/^([^|•\-]+)/', $title, $tm)) {
                $result['nombre_completo'] = trim($tm[1]);
            }
        }

        // 3. Extraer Descripción y métricas
        $description = '';
        if (preg_match('/<meta[^>]+(?:property="og:description"|name="description")[^>]+content="([^"]+)"/i', $html, $descMatch)) {
            $description = html_entity_decode($descMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $result['raw_description'] = $description;
        }

        if ($description) {
            // "9466 Me gusta" o "9,4 mil seguidores" o "9.4K followers"
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:seguidores|followers|me gusta|likes)/i', $description, $m)) {
                $result['seguidores'] = $this->parseFormattedNumber($m[1]);
            }
            // "58 seguidos" o "58 following"
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:seguidos|following)/i', $description, $m)) {
                $result['seguidos'] = $this->parseFormattedNumber($m[1]);
            }
        }

        $result['success'] = ! empty($result['foto_perfil_url']) || ! is_null($result['seguidores']) || ! empty($result['handle_usuario']);
        $result['mensaje'] = $result['success']
            ? '¡Datos de Facebook leídos exitosamente!'
            : 'Facebook protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Extractor para TikTok.
     */
    protected function scrapeTikTok(string $url, array $result): array
    {
        preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        if (! empty($handleMatch[1])) {
            $result['handle_usuario'] = '@'.ltrim($handleMatch[1], '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
        ])->timeout(8)->get($url);

        if (! $response->successful()) {
            $response = Http::withHeaders([
                'User-Agent' => 'WhatsApp/2.21.12.21 A',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(8)->get($url);
        }

        $html = $response->body();

        // 1. Extraer Foto de perfil (og:image)
        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // 2. Extraer Título (Nombre)
        if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $titleMatch)) {
            $title = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/^([^|•\-]+?)(?:\s+en\s+TikTok|\s+on\s+TikTok|$)/i', $title, $tm)) {
                $result['nombre_completo'] = trim($tm[1]);
            }
        }

        // 3. Extraer Descripción y métricas (Seguidores, Siguiendo/Seguidos, Me Gusta)
        if (preg_match('/<meta[^>]+(?:property="og:description"|name="description")[^>]+content="([^"]+)"/i', $html, $descMatch)) {
            $desc = html_entity_decode($descMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $result['raw_description'] = $desc;

            // Seguidores (ej. "1678 seguidores" o "1.6K Followers")
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:Followers|Seguidores)/iu', $desc, $m)) {
                $result['seguidores'] = $this->parseFormattedNumber($m[1]);
            }

            // Siguiendo / Seguidos (ej. "259 siguiendo" o "259 Following")
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:Following|Siguiendo|Seguidos)/iu', $desc, $m)) {
                $result['seguidos'] = $this->parseFormattedNumber($m[1]);
            }

            // Me gusta / Likes (ej. "7063 me gusta" o "7063 Likes")
            if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:Likes|Me gusta)/iu', $desc, $m)) {
                $result['total_likes'] = $this->parseFormattedNumber($m[1]);
                $result['me_gusta_totales'] = $result['total_likes'];
                $result['me_gusta_punto_cero'] = $result['total_likes'];
            }
        }

        $result['success'] = ! empty($result['foto_perfil_url']) || ! is_null($result['seguidores']) || ! empty($result['handle_usuario']);
        $mensajeParts = [];
        if (! is_null($result['seguidores'])) {
            $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.').' seguidores';
        }
        if (! is_null($result['seguidos'])) {
            $mensajeParts[] = number_format($result['seguidos'], 0, ',', '.').' seguidos';
        }
        if (! empty($result['total_likes'])) {
            $mensajeParts[] = number_format($result['total_likes'], 0, ',', '.').' Me Gusta';
        }

        $result['mensaje'] = $result['success']
            ? '¡Datos de TikTok extraídos ('.implode(', ', $mensajeParts).')!'
            : 'TikTok protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Extractor para YouTube.
     */
    protected function scrapeYouTube(string $url, array $result): array
    {
        preg_match('/youtube\.com\/(@[a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        if (! empty($handleMatch[1])) {
            $result['handle_usuario'] = $handleMatch[1];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept-Language' => 'es-419,es;q=0.9,en;q=0.8',
            ])->timeout(10)->get($url);

            $html = $response->body();

            // 1. Foto de perfil (og:image)
            if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)) {
                $result['foto_perfil_url'] = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            // 2. Nombre del canal (og:title)
            if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $titleMatch)) {
                $result['nombre_completo'] = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            // 3. Parsear texto en cabecera inicial (suscriptores, videos)
            if (preg_match('/"subscriberCountText":\s*"([^"]+)"/i', $html, $subMatch)) {
                $result['seguidores'] = (int) preg_replace('/[^\d]/u', '', $subMatch[1]);
            } elseif (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:suscriptores|subscribers)/iu', $html, $subMatch)) {
                $result['seguidores'] = $this->parseFormattedNumber($subMatch[1]);
            }

            if (preg_match('/"videoCountText":\s*"([^"]+)"/i', $html, $vidMatch)) {
                $result['publicaciones'] = (int) preg_replace('/[^\d]/u', '', $vidMatch[1]);
            }

            // 4. Obtener datos detallados (Visualizaciones totales) mediante Innertube API
            if (preg_match('/"continuationCommand"\s*:\s*{\s*"token"\s*:\s*"([^"]+)"/i', $html, $tokenMatch)) {
                try {
                    $postRes = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Content-Type' => 'application/json',
                    ])->timeout(6)->post('https://www.youtube.com/youtubei/v1/browse', [
                        'context' => [
                            'client' => [
                                'clientName' => 'WEB',
                                'clientVersion' => '2.20240101.00.00',
                                'hl' => 'es-419',
                                'gl' => 'AR',
                            ],
                        ],
                        'continuation' => $tokenMatch[1],
                    ]);

                    $json = $postRes->json();
                    $about = $json['onResponseReceivedEndpoints'][0]['appendContinuationItemsAction']['continuationItems'][0]['aboutChannelRenderer']['metadata']['aboutChannelViewModel'] ?? null;

                    if ($about) {
                        if (! empty($about['subscriberCountText']) && (is_null($result['seguidores']) || $result['seguidores'] === 0)) {
                            $result['seguidores'] = (int) preg_replace('/[^\d]/u', '', $about['subscriberCountText']);
                        }
                        if (! empty($about['videoCountText'])) {
                            $result['publicaciones'] = (int) preg_replace('/[^\d]/u', '', $about['videoCountText']);
                        }
                        if (! empty($about['viewCountText'])) {
                            $viewsClean = (int) preg_replace('/[^\d]/u', '', $about['viewCountText']);
                            $result['visualizaciones_totales'] = $viewsClean;
                            $result['visualizaciones_punto_cero'] = $viewsClean;
                        }
                    }
                } catch (\Exception $e) {
                    // Silencioso
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        $result['success'] = ! empty($result['foto_perfil_url']) || ! is_null($result['seguidores']) || ! empty($result['handle_usuario']);
        $mensajeParts = [];
        if (! is_null($result['seguidores'])) {
            $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.').' suscriptores';
        }
        if (! is_null($result['publicaciones'])) {
            $mensajeParts[] = number_format($result['publicaciones'], 0, ',', '.').' videos';
        }
        if (! empty($result['visualizaciones_totales'])) {
            $mensajeParts[] = number_format($result['visualizaciones_totales'], 0, ',', '.').' visualizaciones';
        }

        $result['mensaje'] = $result['success']
            ? '¡Datos de YouTube extraídos ('.implode(', ', $mensajeParts).')!'
            : 'YouTube protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Extractor para X / Twitter.
     */
    protected function scrapeXTwitter(string $url, array $result): array
    {
        preg_match('/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)/i', $url, $handleMatch);
        if (! empty($handleMatch[1])) {
            $result['handle_usuario'] = '@'.$handleMatch[1];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'WhatsApp/2.21.12.21 A',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
            ])->timeout(8)->get($url);

            if (! $response->successful() || strlen($response->body()) < 500) {
                $response = Http::withHeaders([
                    'User-Agent' => 'Twitterbot/1.0',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])->timeout(8)->get($url);
            }

            $html = $response->body();

            // 1. Extraer Foto de Perfil (og:image / twitter:image)
            if (preg_match('/<meta[^>]+(?:property="og:image"|name="twitter:image")[^>]+content="([^"]+)"/i', $html, $imgMatch)
                || preg_match('/<meta[^>]+content="([^"]+)"[^>]+(?:property="og:image"|name="twitter:image")/i', $html, $imgMatch)) {
                $photoUrl = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $photoUrl = preg_replace('/_(?:200x200|normal)\./i', '_400x400.', $photoUrl);
                $result['foto_perfil_url'] = $photoUrl;
            }

            // 2. Extraer Título (Nombre completo)
            if (preg_match('/<meta[^>]+(?:property="og:title"|name="title")[^>]+content="([^"]+)"/i', $html, $titleMatch)
                || preg_match('/<meta[^>]+content="([^"]+)"[^>]+(?:property="og:title"|name="title")/i', $html, $titleMatch)) {
                $title = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (preg_match('/^([^(@•]+)/i', $title, $tm)) {
                    $result['nombre_completo'] = trim($tm[1]);
                }
            }

            // 3. Posts / Publicaciones acumuladas
            if (preg_match('/<meta[^>]+name="twitter:label1"[^>]+content="Posts"[^>]*>.*?<meta[^>]+name="twitter:data1"[^>]+content="([^"]+)"/is', $html, $postsMatch)) {
                $result['publicaciones'] = $this->parseFormattedNumber($postsMatch[1]);
            }

            // 4. Descripción y métricas (Seguidores, Seguidos)
            if (preg_match('/<meta[^>]+(?:property="og:description"|name="description")[^>]+content="([^"]+)"/i', $html, $descMatch)
                || preg_match('/<meta[^>]+content="([^"]+)"[^>]+(?:property="og:description"|name="description")/i', $html, $descMatch)) {
                $desc = html_entity_decode($descMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $result['raw_description'] = $desc;

                // Seguidores (ej. "253 followers" o "253 seguidores" o "2.5K followers")
                if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:followers|seguidores)/iu', $desc, $m)) {
                    $result['seguidores'] = $this->parseFormattedNumber($m[1]);
                }

                // Seguidos / Siguiendo (ej. "500 following" o "500 siguiendo" o "500 seguidos")
                if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:following|siguiendo|seguidos)/iu', $desc, $m)) {
                    $result['seguidos'] = $this->parseFormattedNumber($m[1]);
                }
            }
        } catch (\Exception $e) {
            // Manejo silencioso
        }

        $result['success'] = ! empty($result['foto_perfil_url']) || ! is_null($result['seguidores']) || ! empty($result['handle_usuario']);
        $mensajeParts = [];
        if (! is_null($result['seguidores'])) {
            $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.').' seguidores';
        }
        if (! is_null($result['seguidos'])) {
            $mensajeParts[] = number_format($result['seguidos'], 0, ',', '.').' seguidos';
        }
        if (! is_null($result['publicaciones'])) {
            $mensajeParts[] = number_format($result['publicaciones'], 0, ',', '.').' posts';
        }

        $result['mensaje'] = $result['success']
            ? '¡Datos de X / Twitter extraídos ('.(implode(', ', $mensajeParts) ?: $result['handle_usuario']).')!'
            : 'X protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Extractor para Threads (@threads.net).
     */
    protected function scrapeThreads(string $url, array $result): array
    {
        preg_match('/(?:threads\.net|threads\.com)\/@([a-zA-Z0-9_\.]+)/i', $url, $handleMatch);
        if (! empty($handleMatch[1])) {
            $result['handle_usuario'] = '@'.$handleMatch[1];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Twitterbot/1.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
            ])->timeout(8)->get($url);

            $html = $response->body();

            if (preg_match('/<meta[^>]+(?:property="og:image"|name="twitter:image")[^>]+content="([^"]+)"/i', $html, $imgMatch)) {
                $avatar = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (! str_contains($avatar, 'rsrc.php')) {
                    $result['foto_perfil_url'] = $avatar;
                }
            }

            if (preg_match('/([\d\.,KMkm]+)\s*(?:seguidores|followers)/i', $html, $segMatch)) {
                $result['seguidores'] = $this->parseFormattedNumber($segMatch[1]);
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        $result['success'] = ! empty($result['handle_usuario']) || ! empty($result['foto_perfil_url']) || ! is_null($result['seguidores']);
        $mensajeParts = [];
        if (! is_null($result['seguidores'])) {
            $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.').' seguidores';
        }

        $result['mensaje'] = $result['success']
            ? '¡Datos de Threads extraídos ('.(implode(', ', $mensajeParts) ?: $result['handle_usuario']).')!'
            : 'Threads protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Extractor para LinkedIn.
     */
    protected function scrapeLinkedIn(string $url, array $result): array
    {
        // 1. Extraer slug/handle de la URL
        preg_match('/linkedin\.com\/in\/([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        if (! empty($handleMatch[1])) {
            $result['handle_usuario'] = $handleMatch[1];
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Twitterbot/1.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
            ]);
            $html = curl_exec($ch);
            curl_close($ch);

            if ($html && strlen($html) > 500) {
                // 2. Extraer foto de perfil (og:image / twitter:image / media.licdn.com)
                if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)
                    || preg_match('/<meta[^>]+content="([^"]+)"[^>]+property="og:image"/i', $html, $imgMatch)
                    || preg_match('/<meta[^>]+name="twitter:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)
                    || preg_match('/(https:\/\/media\.licdn\.com\/dms\/image\/[^\s"\'<>]+)/i', $html, $imgMatch)) {
                    $photoUrl = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $result['foto_perfil_url'] = $photoUrl;
                }

                // 3. Extraer Nombre completo (og:title)
                if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $titleMatch)
                    || preg_match('/<meta[^>]+content="([^"]+)"[^>]+property="og:title"/i', $html, $titleMatch)) {
                    $title = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    if (preg_match('/^([^\|\-]+)/i', $title, $tm)) {
                        $result['nombre_completo'] = trim($tm[1]);
                    }
                }

                // 4. Extraer Contactos / Seguidores de la meta description
                if (preg_match('/<meta[^>]+name="description"[^>]+content="([^"]+)"/i', $html, $descMatch)
                    || preg_match('/<meta[^>]+content="([^"]+)"[^>]+name="description"/i', $html, $descMatch)
                    || preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]+)"/i', $html, $descMatch)) {
                    $desc = html_entity_decode($descMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $result['raw_description'] = $desc;

                    if (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:contactos|seguidores|connections|followers)/iu', $desc, $m)) {
                        $clean = str_replace([' ', ','], '', $m[1]);
                        $result['seguidores'] = (int) $clean;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        $result['success'] = ! empty($result['foto_perfil_url']) || ! is_null($result['seguidores']) || ! empty($result['handle_usuario']);
        $mensajeParts = [];
        if (! is_null($result['seguidores'])) {
            $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.').' contactos/seguidores';
        }
        if (! empty($result['nombre_completo'])) {
            $mensajeParts[] = $result['nombre_completo'];
        }

        $result['mensaje'] = $result['success']
            ? '¡Datos de LinkedIn extraídos ('.implode(', ', $mensajeParts).')!'
            : 'LinkedIn protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Fallback OpenGraph genérico.
     */
    protected function scrapeOpenGraphGenerico(string $url, array $result): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
        ])->timeout(6)->get($url);

        $html = $response->body();

        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)) {
            $result['foto_perfil_url'] = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $result['success'] = true;

        return $result;
    }

    /**
     * Extraer datos públicos de una publicación (Instagram, TikTok, YouTube, etc.)
     */
    public function scrapePost(string $input, ?string $plataforma = null): array
    {
        $input = trim($input);
        $result = [
            'success' => false,
            'url_post' => '',
            'plataforma' => $plataforma ?? 'instagram',
            'tipo_formato' => 'Reel',
            'contenido_resumen' => '',
            'total_likes' => 0,
            'total_comentarios' => 0,
            'total_vistas' => 0,
            'fecha_publicacion' => date('Y-m-d\TH:i'),
            'media_url' => '',
            'handle_autor' => '',
            'mensaje' => '',
        ];

        if (empty($input)) {
            $result['mensaje'] = 'Ingresa una URL o código de inserción.';

            return $result;
        }

        // 1. Extraer URL limpia si el usuario pegó un <iframe>, <blockquote> o HTML embed
        $url = $input;
        if (preg_match('/(?:twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/status\/(\d+)/i', $input, $tm)) {
            $url = $tm[0];
            if (! str_starts_with($url, 'http')) {
                $url = 'https://'.$url;
            }
        } elseif (preg_match('/plugins\/(?:post|video)\.php\?href=([^&"\']+)/i', $input, $m)) {
            $url = urldecode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        } elseif (preg_match('/data-instgrm-permalink="([^"]+)"/i', $input, $m)) {
            $url = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/src="([^"]+)"/i', $input, $m)) {
            $src = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/href=([^&]+)/i', $src, $hm)) {
                $url = urldecode($hm[1]);
            } else {
                $url = $src;
            }
        } elseif (preg_match('/href="([^"]+)"/i', $input, $m)) {
            $url = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/https?:\/\/[^\s"\'<>]+/i', $input, $m)) {
            $url = $m[0];
        }

        // Limpiar parámetros de tracking innecesarios
        $cleanUrl = preg_replace('/\?(?:utm_source|igsh|igsi|utm_medium|utm_campaign|ref_src|ref_url|s)=[^&]*&?/i', '?', $url);
        $cleanUrl = preg_replace('/(\?|&)(?:twsrc|twcamp|tweetembed|twterm|twgr|twcon)=[^&]*/i', '', $cleanUrl);
        $cleanUrl = rtrim($cleanUrl, '?&');
        $result['url_post'] = $cleanUrl;

        // 2. Detectar plataforma
        if (str_contains($cleanUrl, 'instagram.com')) {
            $result['plataforma'] = 'instagram';

            return $this->scrapeInstagramPost($cleanUrl, $result);
        } elseif (str_contains($cleanUrl, 'facebook.com') || str_contains($cleanUrl, 'fb.watch') || str_contains($cleanUrl, 'fb.com')) {
            $result['plataforma'] = 'facebook';

            return $this->scrapeFacebookPost($cleanUrl, $result);
        } elseif (str_contains($cleanUrl, 'twitter.com') || str_contains($cleanUrl, 'x.com')) {
            $result['plataforma'] = 'x_twitter';

            return $this->scrapeTwitterPost($cleanUrl, $result);
        } elseif (str_contains($cleanUrl, 'threads.net') || str_contains($cleanUrl, 'threads.com')) {
            $result['plataforma'] = 'threads';

            return $this->scrapeThreadsPost($cleanUrl, $result);
        } elseif (str_contains($cleanUrl, 'youtube.com') || str_contains($cleanUrl, 'youtu.be')) {
            $result['plataforma'] = 'youtube';

            return $this->scrapeYouTubePost($cleanUrl, $result);
        } elseif (str_contains($cleanUrl, 'tiktok.com')) {
            $result['plataforma'] = 'tiktok';

            return $this->scrapeTikTokPost($cleanUrl, $result);
        }

        return $this->scrapeGenericPost($cleanUrl, $result);
    }

    /**
     * Scraping especializado de una publicación o hilo de Threads.
     */
    protected function scrapeThreadsPost(string $url, array $result): array
    {
        $result['plataforma'] = 'threads';
        $result['tipo_formato'] = 'Post';

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            $html = curl_exec($ch);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            if (! empty($effectiveUrl)) {
                $cleanEffective = preg_replace('/\?(?:xmt|utm_source|igsh|igsi)=[^&]*&?/i', '', $effectiveUrl);
                $cleanEffective = rtrim($cleanEffective, '?&');
                $result['url_post'] = $cleanEffective;

                if (preg_match('/(?:threads\.net|threads\.com)\/@([a-zA-Z0-9_\.]+)/i', $cleanEffective, $hm)) {
                    $result['handle_autor'] = '@'.$hm[1];
                }
            }

            if (preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]*)"/i', $html, $mDesc)
                || preg_match('/<meta[^>]+name="description"[^>]+content="([^"]*)"/i', $html, $mDesc)) {
                $desc = html_entity_decode($mDesc[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (! str_contains($desc, 'Únete a Threads') && ! str_contains($desc, 'Join Threads')) {
                    $result['contenido_resumen'] = trim($desc);
                }
            }

            if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]*)"/i', $html, $mImg)) {
                $img = html_entity_decode($mImg[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (! str_contains($img, 'rsrc.php')) {
                    $result['media_url'] = $img;
                }
            }

            if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]*)"/i', $html, $mTitle)) {
                $title = html_entity_decode($mTitle[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (empty($result['handle_autor']) && preg_match('/\(@([a-zA-Z0-9_\.]+)\)/i', $title, $tm)) {
                    $result['handle_autor'] = '@'.$tm[1];
                }
            }

            if (preg_match('/([\d\.,KMkm]+)\s*(?:likes|Me gusta)/i', $html, $lm)) {
                $result['total_likes'] = $this->parseFormattedNumber($lm[1]);
            }
            if (preg_match('/([\d\.,KMkm]+)\s*(?:replies|respuestas|comentarios)/i', $html, $rm)) {
                $result['total_comentarios'] = $this->parseFormattedNumber($rm[1]);
            }
        } catch (\Throwable $e) {
            // Silencioso
        }

        $result['success'] = ! empty($result['handle_autor']) || ! empty($result['contenido_resumen']) || ! empty($result['media_url']);
        $result['mensaje'] = $result['success']
            ? '¡Publicación de Threads extraída exitosamente!'
            : 'Threads protegió la lectura. Puedes completar los datos manualmente.';

        return $result;
    }

    /**
     * Scraping especializado de una publicación de X / Twitter.
     */
    protected function scrapeTwitterPost(string $url, array $result): array
    {
        $result['plataforma'] = 'x_twitter';
        $result['tipo_formato'] = 'Post';

        try {
            // 1. Consultar endpoint oficial oEmbed de Twitter/X
            $oembedUrl = 'https://publish.twitter.com/oembed?url='.urlencode($url).'&omit_script=true';
            $resp = Http::timeout(6)->get($oembedUrl);

            if ($resp->successful()) {
                $data = $resp->json();
                if (! empty($data['author_name'])) {
                    $result['handle_autor'] = $data['author_name'];
                }
                if (! empty($data['author_url']) && preg_match('/(?:twitter\.com|x\.com)\/([a-zA-Z0-9_]+)/i', $data['author_url'], $am)) {
                    $result['handle_autor'] = '@'.$am[1];
                }
                if (! empty($data['html'])) {
                    // Extraer texto limpio del <p> dentro del <blockquote>
                    if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $data['html'], $pm)) {
                        $cleanText = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $pm[1]));
                        $result['contenido_resumen'] = html_entity_decode(trim($cleanText), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    }
                }
            }

            // 2. OpenGraph fallback para media_url si existe
            $response = Http::withHeaders(['User-Agent' => 'Twitterbot/1.0'])->timeout(6)->get($url);
            $html = $response->body();
            if (empty($result['media_url']) && preg_match('/<meta[^>]+(?:property="og:image"|name="twitter:image")[^>]+content="([^"]*)"/i', $html, $mImg)) {
                $result['media_url'] = html_entity_decode($mImg[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            // 3. Extraer fecha nativa directa del Tweet ID (Twitter/X Snowflake Algorithm)
            if (preg_match('/status\/(\d+)/i', $url, $tm)) {
                $tweetId = (int) $tm[1];
                $epoch = 1288834974657;
                $tsMs = ($tweetId >> 22) + $epoch;
                $ts = (int) round($tsMs / 1000);
                if ($ts > 1000000000 && $ts < 2500000000) {
                    $result['fecha_publicacion'] = date('Y-m-d\TH:i', $ts);
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        $result['success'] = ! empty($result['contenido_resumen']) || ! empty($result['media_url']);
        $result['mensaje'] = $result['success']
            ? '¡Publicación de X / Twitter extraída exitosamente!'
            : 'X protegió la lectura. Puedes completar los datos manualmente.';

        return $result;
    }

    /**
     * Scraping especializado de una publicación, Reel o Video de Facebook.
     */
    protected function scrapeFacebookPost(string $url, array $result): array
    {
        $result['plataforma'] = 'facebook';

        if (preg_match('/(?:\/share\/r\/|\/reel\/|\/reels\/)/i', $url)) {
            $result['tipo_formato'] = 'Reel';
        } elseif (preg_match('/(?:\/share\/v\/|\/watch\/|\/videos\/)/i', $url)) {
            $result['tipo_formato'] = 'Video';
        } elseif (preg_match('/(?:\/share\/p\/|\/photos\/|\/photo\b)/i', $url)) {
            $result['tipo_formato'] = 'Foto';
        } else {
            $result['tipo_formato'] = 'Post';
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ]);
            $html = curl_exec($ch);
            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);

            if ($finalUrl && $finalUrl !== $url) {
                $result['url_post'] = $finalUrl;
                if (str_contains($finalUrl, '/reel/')) {
                    $result['tipo_formato'] = 'Reel';
                }
            }

            // og:image
            if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]*)"/i', $html, $mImg)) {
                $result['media_url'] = html_entity_decode($mImg[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            // og:title & og:description
            $rawTitle = '';
            if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]*)"/i', $html, $mTitle)) {
                $rawTitle = html_entity_decode($mTitle[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $rawDesc = '';
            if (preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]*)"/i', $html, $mDesc)) {
                $rawDesc = html_entity_decode($mDesc[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            // Extraer Reproducciones / Vistas
            if (preg_match('/([\d\.,KMkm]+)\s*(?:reproducciones|views|reproducción)/iu', $rawTitle.' '.$rawDesc, $m)) {
                $result['total_vistas'] = $this->parseFormattedNumber($m[1]);
            }

            // Extraer Reacciones / Likes
            if (preg_match('/([\d\.,KMkm]+)\s*(?:reacciones|reactions|me gusta|likes)/iu', $rawTitle.' '.$rawDesc, $m)) {
                $result['total_likes'] = $this->parseFormattedNumber($m[1]);
            }

            // Extraer Comentarios
            if (preg_match('/([\d\.,KMkm]+)\s*(?:comentarios|comments)/iu', $rawTitle.' '.$rawDesc, $m)) {
                $result['total_comentarios'] = $this->parseFormattedNumber($m[1]);
            }

            // Extraer Autor y Copy
            if (str_contains($rawTitle, '|')) {
                $parts = explode('|', $rawTitle);
                if (count($parts) >= 3) {
                    $result['handle_autor'] = trim(end($parts));
                    $result['contenido_resumen'] = trim($parts[1]);
                } elseif (count($parts) === 2) {
                    if (preg_match('/(?:reproducciones|reacciones)/i', $parts[0])) {
                        $result['contenido_resumen'] = trim($parts[1]);
                    } else {
                        $result['contenido_resumen'] = trim($parts[0]);
                        $result['handle_autor'] = trim($parts[1]);
                    }
                }
            }

            if (empty($result['contenido_resumen']) && ! empty($rawDesc)) {
                $result['contenido_resumen'] = $rawDesc;
            }

            // Extraer fecha real de publicación de Facebook
            $extractedDate = $this->extractPublicationDate($html, 'facebook');
            if ($extractedDate) {
                $result['fecha_publicacion'] = $extractedDate;
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        $result['success'] = ! empty($result['contenido_resumen']) || $result['total_likes'] > 0 || ! empty($result['media_url']);
        $result['mensaje'] = $result['success']
            ? '¡Datos del post de Facebook extraídos exitosamente!'
            : 'Facebook protegió la lectura. Puedes completar los datos manualmente.';

        return $result;
    }

    /**
     * Scraping especializado de una publicación o Reel de Instagram.
     */
    protected function scrapeInstagramPost(string $url, array $result): array
    {
        // Detectar formato
        if (str_contains(strtolower($url), '/reel/') || str_contains(strtolower($url), '/reels/')) {
            $result['tipo_formato'] = 'Reel';
        } elseif (str_contains(strtolower($url), '/p/')) {
            $result['tipo_formato'] = 'Foto';
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
        ])->timeout(8)->get($url);

        if (! $response->successful()) {
            $response = Http::withHeaders([
                'User-Agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
            ])->timeout(8)->get($url);
        }

        $html = $response->body();

        $rawDesc = '';
        if (preg_match('/<meta[^>]+(?:property="og:description"|name="description")[^>]+content="([^"]+)"/i', $html, $descMatch)) {
            $rawDesc = html_entity_decode($descMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $rawTitle = '';
        if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $titleMatch)) {
            $rawTitle = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Portada / Imagen
        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $imgMatch)) {
            $result['media_url'] = html_entity_decode($imgMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if ($rawDesc || $rawTitle) {
            // Extraer Likes
            if (preg_match('/([\d\.,KMkm]+)\s*(?:likes|Me gusta)/i', $rawDesc, $m)) {
                $result['total_likes'] = $this->parseFormattedNumber($m[1]);
            }

            // Extraer Comentarios
            if (preg_match('/([\d\.,KMkm]+)\s*(?:comments|comentarios)/i', $rawDesc, $m)) {
                $result['total_comentarios'] = $this->parseFormattedNumber($m[1]);
            }

            // Extraer Handle y Fecha
            if (preg_match('/-\s*([a-zA-Z0-9_\.\-]+)\s*(?:el|on)\s*([a-zA-Z0-9\s,]+):/i', $rawDesc, $m)) {
                $result['handle_autor'] = '@'.ltrim($m[1], '@');
                $time = strtotime(trim($m[2]));
                if ($time) {
                    $result['fecha_publicacion'] = date('Y-m-d\TH:i', $time);
                }
            }

            // Extraer el Copy completo
            if (preg_match('/:\s*["“]([\s\S]+)["”]\.?\s*$/', $rawDesc, $m)) {
                $result['contenido_resumen'] = trim($m[1]);
            } elseif (preg_match('/en Instagram:\s*["“]([\s\S]+)["”]\.?\s*$/i', $rawTitle, $m)) {
                $result['contenido_resumen'] = trim($m[1]);
            } elseif (preg_match('/["“]([\s\S]+)["”]/', $rawTitle, $m)) {
                $result['contenido_resumen'] = trim($m[1]);
            }

            // Extraer fecha real de publicación de Instagram
            $extractedDate = $this->extractPublicationDate($html, 'instagram');
            if ($extractedDate) {
                $result['fecha_publicacion'] = $extractedDate;
            }
        }

        $result['success'] = ! empty($result['contenido_resumen']) || $result['total_likes'] > 0 || ! empty($result['media_url']);
        $result['mensaje'] = $result['success']
            ? '¡Datos del post de Instagram extraídos exitosamente!'
            : 'No se pudieron extraer los datos automáticamente. Puedes completarlos a mano.';

        return $result;
    }

    /**
     * Scraping especializado de un video de TikTok (usando oEmbed oficial y OpenGraph).
     */
    protected function scrapeTikTokPost(string $url, array $result): array
    {
        $result['plataforma'] = 'tiktok';
        $result['tipo_formato'] = 'Video Corto';

        try {
            // 1. Consultar endpoint oficial oEmbed de TikTok
            $oembedUrl = 'https://www.tiktok.com/oembed?url='.urlencode($url);
            $oembedResp = Http::timeout(6)->get($oembedUrl);

            if ($oembedResp->successful()) {
                $oembed = $oembedResp->json();
                if (! empty($oembed['title'])) {
                    $result['contenido_resumen'] = trim($oembed['title']);
                }
                if (! empty($oembed['thumbnail_url'])) {
                    $result['media_url'] = $oembed['thumbnail_url'];
                }
                if (! empty($oembed['author_unique_id'])) {
                    $result['handle_autor'] = '@'.ltrim($oembed['author_unique_id'], '@');
                } elseif (! empty($oembed['author_name'])) {
                    $result['handle_autor'] = $oembed['author_name'];
                }
            }

            // Extraer fecha nativa directa del Video ID (TikTok Snowflake Algorithm)
            if (preg_match('/video\/(\d+)/i', $url, $vm)) {
                $videoId = $vm[1];
                $ts = (int) ($videoId >> 32);
                if ($ts > 1000000000 && $ts < 2500000000) {
                    $result['fecha_publicacion'] = date('Y-m-d\TH:i', $ts);
                }
            }

            // 2. Intentar leer HTML con Bot Agent para extraer contadores si están disponibles
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $html = curl_exec($ch);
            curl_close($ch);

            if (! empty($html)) {
                if (empty($result['media_url']) && preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]*)"/i', $html, $mImg)) {
                    $result['media_url'] = html_entity_decode($mImg[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                if (empty($result['contenido_resumen']) && preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]*)"/i', $html, $mDesc)) {
                    $result['contenido_resumen'] = html_entity_decode($mDesc[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }

                // Universal Data rehydration check
                if (preg_match('/<script[^>]+id="__UNIVERSAL_DATA_FOR_REHYDRATION__"[^>]*>(.*?)<\/script>/is', $html, $mJson)) {
                    $uData = json_decode($mJson[1], true);
                    $stats = $uData['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct']['stats'] ?? null;
                    if ($stats) {
                        if (isset($stats['diggCount'])) {
                            $result['total_likes'] = (int) $stats['diggCount'];
                        }
                        if (isset($stats['commentCount'])) {
                            $result['total_comentarios'] = (int) $stats['commentCount'];
                        }
                        if (isset($stats['shareCount'])) {
                            $result['total_compartidos'] = (int) $stats['shareCount'];
                        }
                        if (isset($stats['collectCount'])) {
                            $result['total_guardados'] = (int) $stats['collectCount'];
                        }
                        if (isset($stats['playCount'])) {
                            $result['total_vistas'] = (int) $stats['playCount'];
                        }
                    }
                }

                $extractedDate = $this->extractPublicationDate($html, 'tiktok');
                if ($extractedDate) {
                    $result['fecha_publicacion'] = $extractedDate;
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        $result['success'] = ! empty($result['contenido_resumen']) || ! empty($result['media_url']) || $result['total_likes'] > 0;
        $result['mensaje'] = $result['success']
            ? '¡Video de TikTok extraído exitosamente!'
            : 'TikTok protegió la lectura. Puedes completar los datos manualmente.';

        return $result;
    }

    /**
     * Scraping de YouTube Video / Shorts.
     */
    protected function scrapeYouTubePost(string $url, array $result): array
    {
        $result['tipo_formato'] = str_contains($url, '/shorts/') ? 'Shorts' : 'Video';
        $response = Http::withHeaders(['User-Agent' => 'Twitterbot/1.0'])->timeout(6)->get($url);
        $html = $response->body();

        if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $m)) {
            $result['contenido_resumen'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $m)) {
            $result['media_url'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $extractedDate = $this->extractPublicationDate($html, 'youtube');
        if ($extractedDate) {
            $result['fecha_publicacion'] = $extractedDate;
        }

        $result['success'] = ! empty($result['contenido_resumen']);
        $result['mensaje'] = $result['success'] ? '¡Datos de YouTube extraídos!' : 'No se pudo leer YouTube.';

        return $result;
    }

    /**
     * Scraping genérico OpenGraph para otras redes.
     */
    protected function scrapeGenericPost(string $url, array $result): array
    {
        $response = Http::withHeaders(['User-Agent' => 'Twitterbot/1.0'])->timeout(6)->get($url);
        $html = $response->body();

        if (preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]+)"/i', $html, $m)) {
            $result['contenido_resumen'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $m)) {
            $result['contenido_resumen'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $m)) {
            $result['media_url'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $extractedDate = $this->extractPublicationDate($html, 'generic');
        if ($extractedDate) {
            $result['fecha_publicacion'] = $extractedDate;
        }

        $result['success'] = ! empty($result['contenido_resumen']);
        $result['mensaje'] = $result['success'] ? '¡Metadatos extraídos!' : 'URL no accesible.';

        return $result;
    }

    /**
     * Parsear cadenas numéricas como "9,4 mil", "9466", "1.359", "14.5K", "1.2M".
     */
    protected function parseFormattedNumber(string $str): int
    {
        $str = trim(strtolower($str));

        // Formato en español "9,4 mil" o "9.4 mil"
        if (preg_match('/([\d\.,]+)\s*mil/i', $str, $m)) {
            $num = (float) str_replace(',', '.', $m[1]);

            return (int) round($num * 1000);
        }

        if (preg_match('/^([\d\.,]+)[Kk]$/', $str, $m)) {
            $num = (float) str_replace(',', '.', $m[1]);

            return (int) round($num * 1000);
        }

        if (preg_match('/^([\d\.,]+)[Mm]$/', $str, $m)) {
            $num = (float) str_replace(',', '.', $m[1]);

            return (int) round($num * 1000000);
        }

        // Si tiene formato "9.466" o "1,359"
        $clean = str_replace([' ', ','], '', $str);
        if (substr_count($clean, '.') === 1 && strlen(substr($clean, strpos($clean, '.') + 1)) === 3) {
            $clean = str_replace('.', '', $clean);
        }

        return (int) filter_var($clean, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Canonicalizar la URL de una publicación para garantizar unicidad absoluta en el sistema.
     */
    public static function canonicalizePostUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);
        if (preg_match('/plugins\/(?:post|video)\.php\?href=([^&"\']+)/i', $url, $m)) {
            $url = urldecode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        } elseif (preg_match('/src="([^"]+)"/i', $url, $m)) {
            $src = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/href=([^&]+)/i', $src, $hm)) {
                $url = urldecode($hm[1]);
            } else {
                $url = $src;
            }
        } elseif (preg_match('/https?:\/\/[^\s"\'<>]+/i', $url, $m)) {
            $url = $m[0];
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $parsed = parse_url($url);
        if (! $parsed || empty($parsed['host'])) {
            return $url;
        }

        $host = strtolower($parsed['host']);
        $host = preg_replace('/^www\./', '', $host);
        $path = $parsed['path'] ?? '/';
        $query = $parsed['query'] ?? '';

        // Normalizar dominios comunes
        if (in_array($host, ['twitter.com', 'mobile.twitter.com', 'x.com'])) {
            $host = 'x.com';
        } elseif (in_array($host, ['threads.com', 'threads.net', 'www.threads.net', 'm.threads.net'])) {
            $host = 'threads.net';
        } elseif (in_array($host, ['instagr.am', 'instagram.com', 'm.instagram.com'])) {
            $host = 'instagram.com';
        } elseif (in_array($host, ['facebook.com', 'm.facebook.com', 'fb.watch', 'web.facebook.com', 'fb.com'])) {
            $host = 'facebook.com';
        } elseif (in_array($host, ['youtu.be', 'youtube.com', 'm.youtube.com'])) {
            $host = 'youtube.com';
        } elseif (in_array($host, ['tiktok.com', 'm.tiktok.com', 'vm.tiktok.com'])) {
            $host = 'tiktok.com';
        }

        // Limpieza de tracking params en query string
        $queryParamsToKeep = [];
        if (! empty($query)) {
            parse_str($query, $params);
            $trackingKeys = [
                'igsh', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                'ref', 'ref_src', 'ref_url', 'fbclid', 'gclid', 's', 't', 'mibextid', 'rdid',
                'is_from_webapp', 'sender_device', 'share_id', 'feature', 'app', 'locale',
                'twclid', 'si', '_r', '_d', 'checksum', 'source_ve_path', 'embed_source'
            ];

            foreach ($params as $k => $v) {
                if (! in_array(strtolower($k), $trackingKeys)) {
                    $queryParamsToKeep[$k] = $v;
                }
            }
        }

        // Normalización específica por red social
        if ($host === 'youtube.com' && preg_match('#^/([a-zA-Z0-9_\-]{11})#', $path, $ytMatch)) {
            $path = '/watch';
            $queryParamsToKeep['v'] = $ytMatch[1];
        } elseif ($host === 'instagram.com') {
            if (preg_match('#/(p|reel|tv)/([a-zA-Z0-9_\-]+)#i', $path, $igMatch)) {
                $path = '/'.strtolower($igMatch[1]).'/'.$igMatch[2];
                $queryParamsToKeep = [];
            }
        } elseif ($host === 'threads.net') {
            if (preg_match('#/(@[a-zA-Z0-9_\.\-]+)/post/([a-zA-Z0-9_\-]+)#i', $path, $thMatch)) {
                $path = '/'.$thMatch[1].'/post/'.$thMatch[2];
                $queryParamsToKeep = [];
            } elseif (preg_match('#/share/([a-zA-Z0-9_\-]+)#i', $path, $thMatch)) {
                $path = '/share/'.$thMatch[1];
                $queryParamsToKeep = [];
            }
        } elseif ($host === 'x.com') {
            if (preg_match('#/([a-zA-Z0-9_]+)/status/([0-9]+)#i', $path, $xMatch)) {
                $path = '/'.$xMatch[1].'/status/'.$xMatch[2];
                $queryParamsToKeep = [];
            }
        } elseif ($host === 'tiktok.com') {
            if (preg_match('#/(@[a-zA-Z0-9_\.\-]+)/video/([0-9]+)#i', $path, $ttMatch)) {
                $path = '/'.$ttMatch[1].'/video/'.$ttMatch[2];
                $queryParamsToKeep = [];
            }
        }

        $cleanPath = rtrim($path, '/');
        if (empty($cleanPath)) {
            $cleanPath = '/';
        }

        $canonical = 'https://'.$host.$cleanPath;
        if (! empty($queryParamsToKeep)) {
            ksort($queryParamsToKeep);
            $canonical .= '?'.http_build_query($queryParamsToKeep);
        }

        return $canonical;
    }

    /**
     * Extraer la fecha y hora real de publicación original de la red social.
     */
    public function extractPublicationDate(string $html, string $plataforma = 'generic'): ?string
    {
        try {
            // 1. Timestamps de Facebook (publish_time / creation_time)
            if (preg_match('/"(?:publish_time|creation_time)"\s*:\s*(\d{9,11})/i', $html, $m)) {
                $ts = (int) $m[1];
                if ($ts > 1000000000 && $ts < 2500000000) {
                    return date('Y-m-d\TH:i', $ts);
                }
            }

            // 2. Timestamps de Instagram / TikTok (taken_at_timestamp / createTime)
            if (preg_match('/"(?:taken_at_timestamp|createTime)"\s*:\s*(\d{9,11})/i', $html, $m)) {
                $ts = (int) $m[1];
                if ($ts > 1000000000 && $ts < 2500000000) {
                    return date('Y-m-d\TH:i', $ts);
                }
            }

            // 3. Meta tags estándar (article:published_time, og:article:published_time, datePublished)
            if (preg_match('/<meta[^>]+(?:property="article:published_time"|property="og:article:published_time"|itemprop="datePublished"|name="publish_date"|name="pubdate")[^>]+content="([^"]+)"/i', $html, $m)) {
                $carbon = \Carbon\Carbon::parse($m[1]);
                return $carbon->format('Y-m-d\TH:i');
            }

            // 4. Schema.org / JSON-LD ("datePublished": "...", "uploadDate": "...")
            if (preg_match('/"(?:datePublished|uploadDate|created_at|dateCreated)"\s*:\s*"([^"]+)"/i', $html, $m)) {
                $carbon = \Carbon\Carbon::parse($m[1]);
                return $carbon->format('Y-m-d\TH:i');
            }

            // 5. Fallback para created_time en Facebook/Instagram
            if (preg_match('/"created_time"\s*:\s*(\d{9,11})/i', $html, $m)) {
                $ts = (int) $m[1];
                if ($ts > 1000000000 && $ts < 2500000000) {
                    return date('Y-m-d\TH:i', $ts);
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        return null;
    }
}
