<?php

namespace App\Services\Scrapers;

use App\Helpers\SecurityHelper;
use App\Services\SocialProfileScraperService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialPostScraper
{
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

        // 1. Extraer URL limpia si el usuario pegó un <iframe>, <blockquote> o HTML embed.
        //    Se soportan todos los formatos: URL directa, iframe embed, blockquote de Instagram,
        //    plugin URL de Facebook (post.php / video.php), URL corta de fb.watch, etc.
        $url = self::extraerUrlDesdeInput($input);

        // Detección heurística de huella de pauta publicitaria multi-plataforma
        $analisisHuella = self::analizarHuellaPauta($url, $input);
        $tieneSospechaPauta = $analisisHuella['tiene_huella'];
        $motivoSospechaPauta = $analisisHuella['motivo'];

        $result['sospecha_pauta'] = $tieneSospechaPauta;
        $result['motivo_sospecha_pauta'] = $motivoSospechaPauta;
        $result['huella_pauta'] = $analisisHuella;
        $result['tipo_pauta_sugerido'] = $tieneSospechaPauta ? 'organico_impulsado' : 'organico';

        // Limpiar parámetros de tracking y de ads innecesarios de la URL canónica
        $cleanUrl = preg_replace('/([\?&])(?:utm_source|igsh|igsi|utm_medium|utm_campaign|ref_src|ref_url|s|fbclid|ad_id|adset_id|campaign_id|hsa_acc|hsa_cam|hsa_grp|hsa_ad|twsrc|twcamp|tweetembed|twterm|twgr|twcon)=[^&#\s]*/i', '$1', $url);
        // Limpiar separadores duplicados o terminales
        $cleanUrl = preg_replace('/(\?|&)+/', '$1', $cleanUrl);
        $cleanUrl = str_replace(['?&', '&&'], ['?', '&'], $cleanUrl);
        $cleanUrl = rtrim($cleanUrl, '?&');
        $result['url_post'] = $cleanUrl;

        // Validación de seguridad SSRF
        if (! SecurityHelper::esUrlSegura($cleanUrl)) {
            $result['mensaje'] = 'La URL de la publicación no es válida o apunta a un host no permitido.';

            return $result;
        }

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
     * Soporta posts, álbumes/carruseles, reels, videos y fotos sueltas.
     */
    protected function scrapeFacebookPost(string $url, array $result): array
    {
        $result['plataforma'] = 'facebook';

        // Detectar formato inicial por URL (se puede sobreescribir con datos del HTML)
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

            if (empty($html)) {
                $result['mensaje'] = 'Facebook no respondió. Puedes completar los datos manualmente.';

                return $result;
            }

            // Actualizar URL canónica si hubo redirección
            if ($finalUrl && $finalUrl !== $url) {
                $result['url_post'] = $finalUrl;
                if (str_contains($finalUrl, '/reel/')) {
                    $result['tipo_formato'] = 'Reel';
                }
            }

            // ── 1. Texto completo del post (sin truncamiento) ─────────────────────────────
            // Facebook incluye el texto íntegro en el payload JSON de la página bajo
            // el nodo "message":{"text":"..."} — esto evita el "..." de los og:description
            if (preg_match('/"message"\s*:\s*\{\s*"text"\s*:\s*"((?:\\\\.|[^"\\\\])*)"/s', $html, $mMsg)) {
                $fullText = json_decode('"'.$mMsg[1].'"');
                if (! empty($fullText) && is_string($fullText)) {
                    $result['contenido_resumen'] = trim($fullText);
                }
            }

            // ── 2. og:image (portada / miniatura del carrusel) ───────────────────────────
            if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]*)"/i', $html, $mImg)
                || preg_match('/<meta[^>]+content="([^"]*)"[^>]+property="og:image"/i', $html, $mImg)) {
                $result['media_url'] = html_entity_decode($mImg[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            // ── 3. og:title y og:description (metadatos primarios) ───────────────────────
            $rawTitle = '';
            if (preg_match('/<meta[^>]+(?:property="og:title"|name="twitter:title")[^>]+content="([^"]*)"/i', $html, $mTitle)
                || preg_match('/<meta[^>]+content="([^"]*)"[^>]+(?:property="og:title"|name="twitter:title")/i', $html, $mTitle)) {
                $rawTitle = html_entity_decode($mTitle[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $rawDesc = '';
            if (preg_match('/<meta[^>]+(?:property="og:description"|name="description")[^>]+content="([^"]*)"/i', $html, $mDesc)
                || preg_match('/<meta[^>]+content="([^"]*)"[^>]+(?:property="og:description"|name="description")/i', $html, $mDesc)) {
                $rawDesc = html_entity_decode($mDesc[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            // ── 4. Extracción del autor / handle ─────────────────────────────────────────
            // og:title en Facebook puede ser: "Nombre Página | Acción" o simplemente "Nombre Página"
            if (! empty($rawTitle)) {
                $titleClean = trim($rawTitle);
                // Filtrar titulos genéricos de Facebook (no son el nombre del autor)
                $genericTitles = ['facebook', 'log in or sign up to view', 'log in or sign up', 'iniciar sesión o registrarse'];
                if (! in_array(mb_strtolower($titleClean), $genericTitles)) {
                    // Si tiene separador "|" → la parte final suele ser el nombre de la página
                    if (str_contains($titleClean, '|')) {
                        $parts = explode('|', $titleClean);
                        $result['handle_autor'] = trim(end($parts));
                    } elseif (str_contains($titleClean, ' - ')) {
                        // "Feliz Día del Maestro - Federico Sisterna"
                        $parts = explode(' - ', $titleClean);
                        $result['handle_autor'] = trim(end($parts));
                    } else {
                        $result['handle_autor'] = $titleClean;
                    }
                }
            }

            // ── 5. Fallback: si no se extrajo el texto completo del JSON, usar og:description ──
            if (empty($result['contenido_resumen']) && ! empty($rawDesc)) {
                $result['contenido_resumen'] = $rawDesc;
            }

            // ── 6. Detección de Carrusel / Álbum ─────────────────────────────────────────
            // Un álbum tiene más de 1 nodo "__typename":"Photo" en el JSON de la página
            // o incluye "all_subattachments" / "CAROUSEL" en el payload
            if ($result['tipo_formato'] === 'Post' || $result['tipo_formato'] === 'Foto') {
                $isCarrusel = false;
                if (preg_match('/"all_subattachments"|"attached_story_layout"\s*:\s*"CAROUSEL"/i', $html)) {
                    $isCarrusel = true;
                }
                if (! $isCarrusel && preg_match_all('/"__typename"\s*:\s*"Photo"/i', $html, $photoNodes)
                    && count($photoNodes[0]) > 1) {
                    $isCarrusel = true;
                }
                if ($isCarrusel) {
                    $result['tipo_formato'] = 'Carrusel';
                }
            }

            // ── 7. Colección unificada de meta textos (para extracción de métricas) ───────
            $allMetaTexts = $rawTitle.' '.$rawDesc;
            if (preg_match_all('/<meta[^>]+content="([^"]*)"/i', $html, $allMetas)) {
                $allMetaTexts .= ' '.implode(' ', array_map(
                    fn ($t) => html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    $allMetas[1]
                ));
            }

            // ── 8. Reproducciones / Vistas ───────────────────────────────────────────────
            if (preg_match('/(\d[\d\.,KMkm]*)\s*(?:reproducciones|views|reproducción)/iu', $allMetaTexts, $m)) {
                $result['total_vistas'] = $this->parseFormattedNumber($m[1]);
            }
            if (empty($result['total_vistas'])) {
                $vViews = 0;
                $pPlays = 0;
                if (preg_match('/"video_view_count"\s*:\s*([0-9]+)/i', $html, $mViews)) {
                    $vViews = (int) $mViews[1];
                }
                if (preg_match('/"play_count"\s*:\s*([0-9]+)/i', $html, $mPlays)) {
                    $pPlays = (int) $mPlays[1];
                }
                if ($vViews > 0 || $pPlays > 0) {
                    $result['total_vistas'] = max($vViews, $pPlays);
                } elseif (preg_match('/"video_post_view_count"\s*:\s*([0-9]+)/i', $html, $mPostViews)) {
                    $result['total_vistas'] = (int) $mPostViews[1];
                }
            }

            // ── 9. Reacciones / Likes ────────────────────────────────────────────────────
            if (preg_match('/(\d[\d\.,KMkm]*)\s*(?:reacciones|reactions|me gusta|likes)/iu', $allMetaTexts, $m)) {
                $result['total_likes'] = $this->parseFormattedNumber($m[1]);
            }
            // Fallback JSON para reacciones totales
            if (empty($result['total_likes'])) {
                if (preg_match('/"reaction_count"\s*:\s*\{\s*"count"\s*:\s*([0-9]+)/i', $html, $mReact)
                    || preg_match('/"likeCount"\s*:\s*([0-9]+)/i', $html, $mReact)) {
                    $result['total_likes'] = (int) $mReact[1];
                }
            }

            // ── 10. Comentarios ──────────────────────────────────────────────────────────
            if (preg_match('/(\d[\d\.,KMkm]*)\s*(?:comentarios?|comments?)/iu', $allMetaTexts, $m)) {
                $result['total_comentarios'] = $this->parseFormattedNumber($m[1]);
            }
            if (empty($result['total_comentarios'])) {
                if (preg_match('/"(?:total_comment_count|comment_count|comments_count|total_comments)"\s*:\s*([0-9]+)/i', $html, $mComm)) {
                    $result['total_comentarios'] = (int) $mComm[1];
                } elseif (preg_match('/"(?:comments|comment_count)"\s*:\s*\{\s*"(?:total_count|count)"\s*:\s*([0-9]+)/i', $html, $mComm)) {
                    $result['total_comentarios'] = (int) $mComm[1];
                }
            }

            // ── 11. Fecha de publicación ─────────────────────────────────────────────────
            $extractedDate = $this->extractPublicationDate($html, 'facebook');
            if ($extractedDate) {
                $result['fecha_publicacion'] = $extractedDate;
            }

        } catch (\Exception $e) {
            // Silencioso — el usuario puede completar manualmente
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
        $result['tipo_formato'] = str_contains($url, '/photo/') ? 'Carrusel' : 'Video';

        // 0. Limpiar URL de parámetros UTM o query strings
        $cleanUrl = strtok($url, '?');
        $result['url_post'] = $cleanUrl;

        try {
            // 1. Extraer fecha nativa directa del Video/Photo ID (TikTok Snowflake Algorithm)
            if (preg_match('/(?:video|photo|v)\/(\d+)/i', $cleanUrl, $vm)) {
                $videoId = $vm[1];
                $ts = (int) ($videoId >> 32);
                if ($ts > 1000000000 && $ts < 2500000000) {
                    $result['fecha_publicacion'] = date('Y-m-d\TH:i', $ts);
                }
            }

            // 2. Extraer autor del handle en la URL si está presente
            if (preg_match('/tiktok\.com\/@([a-zA-Z0-9_\.\-]+)/i', $cleanUrl, $am)) {
                $result['handle_autor'] = '@'.ltrim($am[1], '@');
            }

            // 3. Consultar endpoint oficial oEmbed de TikTok (para fotos se convierte a /video/$id y con www. para compatibilidad con la API oEmbed)
            $oembedTargetUrl = preg_replace('/\/photo\/(\d+)/i', '/video/$1', $cleanUrl);
            if (str_contains($oembedTargetUrl, 'tiktok.com') && ! str_contains($oembedTargetUrl, 'www.tiktok.com')) {
                $oembedTargetUrl = str_replace('://tiktok.com', '://www.tiktok.com', $oembedTargetUrl);
            }
            $oembedUrl = 'https://www.tiktok.com/oembed?url='.urlencode($oembedTargetUrl);
            $oembedResp = Http::withoutVerifying()
                ->timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                ])
                ->get($oembedUrl);

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
                } elseif (! empty($oembed['author_name']) && empty($result['handle_autor'])) {
                    $result['handle_autor'] = $oembed['author_name'];
                }
            }

            // 4. Intentar leer HTML con Mobile Agent (para extraer contadores completos diggCount, playCount, commentCount, shareCount, collectCount)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $cleanUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: es-ES,es;q=0.9,en;q=0.8',
            ]);
            $html = curl_exec($ch);
            curl_close($ch);

            // Fallback con Bot Agent si mobile no devolvió HTML suficiente
            if (empty($html) || strlen($html) < 2000) {
                $ch2 = curl_init();
                curl_setopt($ch2, CURLOPT_URL, $cleanUrl);
                curl_setopt($ch2, CURLOPT_USERAGENT, 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)');
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch2, CURLOPT_TIMEOUT, 8);
                $html = curl_exec($ch2);
                curl_close($ch2);
            }

            if (! empty($html)) {
                // Verificar si TikTok indica explícitamente que el video no existe o fue borrado
                if (str_contains($html, 'Video currently unavailable') || str_contains($html, 'video-unavailable') || str_contains($html, 'Couldn\'t find this video')) {
                    $result['success'] = false;
                    $result['mensaje'] = 'La publicación no está disponible o fue eliminada en TikTok.';

                    return $result;
                }

                if (empty($result['media_url']) && preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]*)"/i', $html, $mImg)) {
                    $result['media_url'] = html_entity_decode($mImg[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                if (empty($result['contenido_resumen']) && preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]*)"/i', $html, $mDesc)) {
                    $candidateDesc = html_entity_decode($mDesc[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    if (! in_array(mb_strtolower(trim($candidateDesc)), ['tiktok | make your day', 'tiktok | alégrate el día', 'tiktok | alegrate el dia'])) {
                        $result['contenido_resumen'] = $candidateDesc;
                    }
                }

                // Universal Data rehydration check
                if (preg_match('/<script[^>]+id="__UNIVERSAL_DATA_FOR_REHYDRATION__"[^>]*>(.*?)<\/script>/is', $html, $mJson)) {
                    $uData = json_decode($mJson[1], true);
                    $stats = $uData['__DEFAULT_SCOPE__']['webapp.video-detail']['itemInfo']['itemStruct']['stats']
                        ?? $uData['__DEFAULT_SCOPE__']['webapp.photo-detail']['itemInfo']['itemStruct']['stats']
                        ?? null;
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

                // Extracción directa de métricas por regex sobre el HTML completo (SSR de TikTok)
                if (empty($result['total_likes']) && preg_match('/"diggCount"\s*:\s*([0-9]+)/i', $html, $mD)) {
                    $result['total_likes'] = (int) $mD[1];
                }
                if (empty($result['total_vistas']) && preg_match('/"playCount"\s*:\s*([0-9]+)/i', $html, $mP)) {
                    $result['total_vistas'] = (int) $mP[1];
                }
                if (empty($result['total_comentarios']) && preg_match('/"commentCount"\s*:\s*([0-9]+)/i', $html, $mC)) {
                    $result['total_comentarios'] = (int) $mC[1];
                }
                if (empty($result['total_compartidos']) && preg_match('/"shareCount"\s*:\s*([0-9]+)/i', $html, $mS)) {
                    $result['total_compartidos'] = (int) $mS[1];
                }
                if (empty($result['total_guardados']) && preg_match('/"collectCount"\s*:\s*([0-9]+)/i', $html, $mG)) {
                    $result['total_guardados'] = (int) $mG[1];
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
            ? '¡Publicación de TikTok extraída exitosamente!'
            : 'TikTok protegió la lectura o la publicación no está disponible. Puedes completar los datos manualmente.';

        // Advertencia si la publicación es anterior al inicio de auditoría de Julio
        if ($result['success'] && ! empty($result['fecha_publicacion'])) {
            $timePub = strtotime($result['fecha_publicacion']);
            $timeCorte = strtotime('2026-07-01');
            if ($timePub && $timePub < $timeCorte) {
                $result['mensaje'] .= ' (Nota: La fecha detectada es anterior al período de auditoría de Julio).';
            }
        }

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
                $carbon = Carbon::parse($m[1]);

                return $carbon->format('Y-m-d\TH:i');
            }

            // 4. Schema.org / JSON-LD ("datePublished": "...", "uploadDate": "...")
            if (preg_match('/"(?:datePublished|uploadDate|created_at|dateCreated)"\s*:\s*"([^"]+)"/i', $html, $m)) {
                $carbon = Carbon::parse($m[1]);

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


/**
     * Extraer y normalizar una URL limpia desde cualquier tipo de input del usuario.
     */
    public static function extraerUrlDesdeInput(string $input): string
    {
        $input = trim($input);
        if (empty($input)) {
            return '';
        }

        // ── Caso 1: Twitter/X status URL ──
        if (preg_match('/(?:twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/status\/(\d+)/i', $input, $tm)) {
            $url = $tm[0];

            return str_starts_with($url, 'http') ? $url : 'https://'.$url;
        }

        // ── Caso 2: Facebook plugin URL dentro de un <iframe src="..."> ──
        if (preg_match('/src=["\']([^"\']+plugins\/(?:post|video)\.php[^"\']*)["\']/', $input, $mSrc)) {
            $pluginSrc = html_entity_decode($mSrc[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/\bhref=([^&"\']+)/i', $pluginSrc, $mHref)) {
                return urldecode($mHref[1]);
            }
        }

        // ── Caso 3: Facebook plugin URL directa ──
        if (preg_match('/plugins\/(?:post|video)\.php[^"\'<\s]*\bhref=([^&"\'<\s]+)/i', $input, $mPlugin)) {
            return urldecode(html_entity_decode($mPlugin[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // ── Caso 4: Instagram blockquote embed ──
        if (preg_match('/data-instgrm-permalink="([^"]+)"/i', $input, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // ── Caso 5: Twitter/X blockquote embed ──
        if (str_contains($input, 'twitter-tweet') || str_contains($input, 'class="tweet"')) {
            if (preg_match_all('/href="(https?:\/\/(?:twitter\.com|x\.com)\/[^"]+)"/i', $input, $tweetLinks)) {
                $tweetUrl = end($tweetLinks[1]);
                if (str_contains($tweetUrl, '/status/')) {
                    return html_entity_decode($tweetUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
        }

        // ── Caso 6: Cualquier otro <iframe src="..."> ──
        if (preg_match('/(?:iframe|frame)[^>]+src=["\']([^"\']+)["\']/i', $input, $mFrame)) {
            return html_entity_decode($mFrame[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // ── Caso 7: href="..." genérico ──
        if (preg_match('/href=["\']([^"\']+)["\']/i', $input, $m)) {
            $candidate = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (filter_var($candidate, FILTER_VALIDATE_URL)) {
                return $candidate;
            }
        }

        // ── Caso 8: URL suelta en el texto ──
        if (preg_match('/https?:\/\/[^\s"\'<>]+/i', $input, $m)) {
            return rtrim($m[0], '.,;:)]}>');
        }

        return $input;
    }

    /**
     * Canonicalizar la URL de una publicación para garantizar unicidad absoluta en el sistema.
     */
    public static function canonicalizePostUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $url = self::extraerUrlDesdeInput(trim($url));

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

        $queryParamsToKeep = [];
        if (! empty($query)) {
            parse_str($query, $params);
            $trackingKeys = [
                'igsh', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                'ref', 'ref_src', 'ref_url', 'fbclid', 'gclid', 's', 't', 'mibextid', 'rdid',
                'is_from_webapp', 'sender_device', 'share_id', 'feature', 'app', 'locale',
                'twclid', 'si', '_r', '_d', 'checksum', 'source_ve_path', 'embed_source',
            ];

            foreach ($params as $k => $v) {
                if (! in_array(strtolower($k), $trackingKeys)) {
                    $queryParamsToKeep[$k] = $v;
                }
            }
        }

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
     * Motor Integral de Detección de Huella de Pauta Multi-plataforma.
     */
    public static function analizarHuellaPauta(?string $url, ?string $texto = null): array
    {
        $result = [
            'tiene_huella' => false,
            'red' => null,
            'tipo_detector' => null,
            'token_detectado' => null,
            'motivo' => '',
            'tipo_pauta_sugerido' => 'organico',
        ];

        $targetUrl = trim($url ?? '');
        $targetTexto = trim($texto ?? '');

        if (empty($targetUrl) && empty($targetTexto)) {
            return $result;
        }

        // 1. Huella Meta Ads
        if (! empty($targetUrl)) {
            if (preg_match('/[\?&](ad_id|adset_id|campaign_id|hsa_acc|hsa_cam|hsa_grp|hsa_ad|fbclid)=([^&#\s]+)/i', $targetUrl, $m)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'meta',
                    'tipo_detector' => 'parametro_url',
                    'token_detectado' => strtolower($m[1]),
                    'motivo' => "Token publicitario de Meta Ads detectado ({$m[1]}).",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
            if (preg_match('/(?:facebook\.com|fb\.com)\/(?:ads\/(?:experience|archive|library)|ad_library|boosted_post)/i', $targetUrl)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'meta',
                    'tipo_detector' => 'ad_library',
                    'token_detectado' => 'meta_ad_library',
                    'motivo' => 'Enlace originado en la Biblioteca de Anuncios de Meta (Ad Library).',
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
        }

        // 2. Huella TikTok Ads
        if (! empty($targetUrl)) {
            if (preg_match('/[\?&](ttclid|tt_campaign_id|tt_ad_id|tt_medium)=([^&#\s]+)/i', $targetUrl, $m)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'tiktok',
                    'tipo_detector' => 'parametro_url',
                    'token_detectado' => strtolower($m[1]),
                    'motivo' => "Token publicitario de TikTok Ads detectado ({$m[1]}).",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
            if (preg_match('/(?:library\.tiktok\.com\/ads|tiktok\.com\/business)/i', $targetUrl)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'tiktok',
                    'tipo_detector' => 'ad_library',
                    'token_detectado' => 'tiktok_commercial_library',
                    'motivo' => 'Enlace originado en TikTok Ads Library / Comercial.',
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
        }

        // 3. Huella X / Twitter Ads
        if (! empty($targetUrl)) {
            if (preg_match('/[\?&](twclid)=([^&#\s]+)/i', $targetUrl, $m) || preg_match('/twsrc=(?:ads|promoted)/i', $targetUrl)) {
                $tok = ! empty($m[1]) ? $m[1] : 'twsrc=ads';

                return [
                    'tiene_huella' => true,
                    'red' => 'x_twitter',
                    'tipo_detector' => 'parametro_url',
                    'token_detectado' => $tok,
                    'motivo' => "Token publicitario de X (Twitter Ads) detectado ({$tok}).",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
        }

        // 4. Huella Google / YouTube Ads
        if (! empty($targetUrl)) {
            if (preg_match('/[\?&](gclid|wbraid|gbraid)=([^&#\s]+)/i', $targetUrl, $m)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'youtube',
                    'tipo_detector' => 'parametro_url',
                    'token_detectado' => strtolower($m[1]),
                    'motivo' => "Click ID publicitario de Google / YouTube Ads detectado ({$m[1]}).",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
        }

        // 5. Huella LinkedIn Ads
        if (! empty($targetUrl)) {
            if (preg_match('/[\?&](li_fat_id)=([^&#\s]+)/i', $targetUrl, $m)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'linkedin',
                    'tipo_detector' => 'parametro_url',
                    'token_detectado' => strtolower($m[1]),
                    'motivo' => "Token de seguimiento publicitario de LinkedIn Ads detectado ({$m[1]}).",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
        }

        // 6. Huella Universal por Parámetros UTM de Inversión
        if (! empty($targetUrl)) {
            if (preg_match('/utm_medium=(paid|cpc|ads|ad|boost|promoted|sponsored|pauta|publicidad)/i', $targetUrl, $m)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'universal',
                    'tipo_detector' => 'utm_paid',
                    'token_detectado' => 'utm_medium='.$m[1],
                    'motivo' => "Parámetro UTM de campaña publicitaria paga (utm_medium={$m[1]}).",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
            if (preg_match('/utm_source=(meta_ads|facebook_ads|instagram_ads|tiktok_ads|google_ads|youtube_ads|twitter_ads|x_ads|linkedin_ads)/i', $targetUrl, $m)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'universal',
                    'tipo_detector' => 'utm_source_ads',
                    'token_detectado' => 'utm_source='.$m[1],
                    'motivo' => "Origen de tráfico declarado como red de anuncios ({$m[1]}).",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
        }

        // 7. Huella en Texto / Copy
        if (! empty($targetTexto)) {
            if (preg_match('/(?:#publicidad|#ad|#patrocinado|#sponsored|\bpatrocinado\b|\bcolaboraci[oó]n pagada\b|\bpaid partnership\b|\bpromoted post\b)/iu', $targetTexto, $m)) {
                return [
                    'tiene_huella' => true,
                    'red' => 'copy',
                    'tipo_detector' => 'texto_patrocinado',
                    'token_detectado' => $m[0],
                    'motivo' => "Mención explícita de pauta / patrocinio en el texto (\"{$m[0]}\").",
                    'tipo_pauta_sugerido' => 'organico_impulsado',
                ];
            }
        }

        return $result;
    }

}
