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
                case 'linkedin':
                    return $this->scrapeLinkedIn($url, $result);
                default:
                    return $this->scrapeOpenGraphGenerico($url, $result);
            }
        } catch (\Throwable $e) {
            Log::warning("Error scraping perfil social ({$url}): " . $e->getMessage());
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
            $result['handle_usuario'] = '@' . ltrim($username, '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])->timeout(8)->get($url);

        if (!$response->successful()) {
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

        $result['success'] = !empty($result['foto_perfil_url']) || !is_null($result['seguidores']);
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
        if ($username && !in_array(strtolower($username), ['pages', 'profile.php', 'groups'])) {
            $result['handle_usuario'] = '@' . ltrim($username, '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
        ])->timeout(8)->get($url);

        if (!$response->successful()) {
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

        $result['success'] = !empty($result['foto_perfil_url']) || !is_null($result['seguidores']) || !empty($result['handle_usuario']);
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
        if (!empty($handleMatch[1])) {
            $result['handle_usuario'] = '@' . ltrim($handleMatch[1], '@');
        }

        $response = Http::withHeaders([
            'User-Agent' => 'Twitterbot/1.0',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
        ])->timeout(8)->get($url);

        if (!$response->successful()) {
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

        $result['success'] = !empty($result['foto_perfil_url']) || !is_null($result['seguidores']) || !empty($result['handle_usuario']);
        $mensajeParts = [];
        if (!is_null($result['seguidores'])) $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.') . ' seguidores';
        if (!is_null($result['seguidos'])) $mensajeParts[] = number_format($result['seguidos'], 0, ',', '.') . ' seguidos';
        if (!empty($result['total_likes'])) $mensajeParts[] = number_format($result['total_likes'], 0, ',', '.') . ' Me Gusta';

        $result['mensaje'] = $result['success']
            ? '¡Datos de TikTok extraídos (' . implode(', ', $mensajeParts) . ')!'
            : 'TikTok protegió la lectura. Puedes completar los números manualmente.';

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
                $result['seguidores'] = (int)preg_replace('/[^\d]/u', '', $subMatch[1]);
            } elseif (preg_match('/([\d\.,KMkm\s]+(?:mil)?)\s*(?:suscriptores|subscribers)/iu', $html, $subMatch)) {
                $result['seguidores'] = $this->parseFormattedNumber($subMatch[1]);
            }

            if (preg_match('/"videoCountText":\s*"([^"]+)"/i', $html, $vidMatch)) {
                $result['publicaciones'] = (int)preg_replace('/[^\d]/u', '', $vidMatch[1]);
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
                        if (!empty($about['subscriberCountText']) && (is_null($result['seguidores']) || $result['seguidores'] === 0)) {
                            $result['seguidores'] = (int)preg_replace('/[^\d]/u', '', $about['subscriberCountText']);
                        }
                        if (!empty($about['videoCountText'])) {
                            $result['publicaciones'] = (int)preg_replace('/[^\d]/u', '', $about['videoCountText']);
                        }
                        if (!empty($about['viewCountText'])) {
                            $viewsClean = (int)preg_replace('/[^\d]/u', '', $about['viewCountText']);
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

        $result['success'] = !empty($result['foto_perfil_url']) || !is_null($result['seguidores']) || !empty($result['handle_usuario']);
        $mensajeParts = [];
        if (!is_null($result['seguidores'])) $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.') . ' suscriptores';
        if (!is_null($result['publicaciones'])) $mensajeParts[] = number_format($result['publicaciones'], 0, ',', '.') . ' videos';
        if (!empty($result['visualizaciones_totales'])) $mensajeParts[] = number_format($result['visualizaciones_totales'], 0, ',', '.') . ' visualizaciones';

        $result['mensaje'] = $result['success']
            ? '¡Datos de YouTube extraídos (' . implode(', ', $mensajeParts) . ')!'
            : 'YouTube protegió la lectura. Puedes completar los números manualmente.';

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

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'WhatsApp/2.21.12.21 A',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8',
            ])->timeout(8)->get($url);

            if (!$response->successful() || strlen($response->body()) < 500) {
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

        $result['success'] = !empty($result['foto_perfil_url']) || !is_null($result['seguidores']) || !empty($result['handle_usuario']);
        $mensajeParts = [];
        if (!is_null($result['seguidores'])) $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.') . ' seguidores';
        if (!is_null($result['seguidos'])) $mensajeParts[] = number_format($result['seguidos'], 0, ',', '.') . ' seguidos';
        if (!is_null($result['publicaciones'])) $mensajeParts[] = number_format($result['publicaciones'], 0, ',', '.') . ' posts';

        $result['mensaje'] = $result['success']
            ? '¡Datos de X / Twitter extraídos (' . (implode(', ', $mensajeParts) ?: $result['handle_usuario']) . ')!'
            : 'X protegió la lectura. Puedes completar los números manualmente.';

        return $result;
    }

    /**
     * Extractor para LinkedIn.
     */
    protected function scrapeLinkedIn(string $url, array $result): array
    {
        // 1. Extraer slug/handle de la URL
        preg_match('/linkedin\.com\/in\/([a-zA-Z0-9_\.\-]+)/i', $url, $handleMatch);
        if (!empty($handleMatch[1])) {
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
                        $result['seguidores'] = (int)$clean;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        $result['success'] = !empty($result['foto_perfil_url']) || !is_null($result['seguidores']) || !empty($result['handle_usuario']);
        $mensajeParts = [];
        if (!is_null($result['seguidores'])) $mensajeParts[] = number_format($result['seguidores'], 0, ',', '.') . ' contactos/seguidores';
        if (!empty($result['nombre_completo'])) $mensajeParts[] = $result['nombre_completo'];

        $result['mensaje'] = $result['success']
            ? '¡Datos de LinkedIn extraídos (' . implode(', ', $mensajeParts) . ')!'
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
     * Parsear cadenas numéricas como "9,4 mil", "9466", "1.359", "14.5K", "1.2M".
     */
    protected function parseFormattedNumber(string $str): int
    {
        $str = trim(strtolower($str));

        // Formato en español "9,4 mil" o "9.4 mil"
        if (preg_match('/([\d\.,]+)\s*mil/i', $str, $m)) {
            $num = (float)str_replace(',', '.', $m[1]);
            return (int)round($num * 1000);
        }

        if (preg_match('/^([\d\.,]+)[Kk]$/', $str, $m)) {
            $num = (float)str_replace(',', '.', $m[1]);
            return (int)round($num * 1000);
        }

        if (preg_match('/^([\d\.,]+)[Mm]$/', $str, $m)) {
            $num = (float)str_replace(',', '.', $m[1]);
            return (int)round($num * 1000000);
        }

        // Si tiene formato "9.466" o "1,359"
        $clean = str_replace([' ', ','], '', $str);
        if (substr_count($clean, '.') === 1 && strlen(substr($clean, strpos($clean, '.') + 1)) === 3) {
            $clean = str_replace('.', '', $clean);
        }

        return (int)filter_var($clean, FILTER_SANITIZE_NUMBER_INT);
    }
}
