<?php

namespace App\Services\Media;

use App\Helpers\SecurityHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MediaFeedParser
{
    public const GOOGLEBOT_USER_AGENT = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

    /**
     * Consultar y parsear XML (RSS, Atom o Google News Sitemap).
     */
    public function consultarYParsearXml(string $xmlUrl, ?string $siteUrl = null): array
    {
        try {
            $res = Http::withHeaders([
                'User-Agent' => self::GOOGLEBOT_USER_AGENT,
                'Accept' => 'application/rss+xml,application/xml,text/xml;q=0.9,*/*;q=0.8',
            ])->timeout(8)->get($xmlUrl);

            if (! $res->successful()) {
                return [];
            }

            return $this->parsearFeedXml($res->body(), $siteUrl ?: $xmlUrl);
        } catch (\Throwable $e) {
            Log::info("No se pudo obtener XML desde {$xmlUrl}: ".$e->getMessage());

            return [];
        }
    }

    /**
     * Parsear cuerpo XML reconociendo RSS 2.0, Atom o Sitemap News.
     */
    public function parsearFeedXml(string $xmlString, string $baseUrl): array
    {
        $items = [];
        $xmlString = trim($xmlString);

        if (empty($xmlString)) {
            return [];
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            libxml_clear_errors();

            return [];
        }

        // Caso 1: RSS Estándar (channel -> item)
        if (isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $titulo = trim((string) $item->title);
                $link = trim((string) $item->link);
                $desc = trim((string) ($item->description ?? $item->children('content', true)->encoded ?? ''));
                $pubDate = trim((string) $item->pubDate);

                if (! empty($titulo) && ! empty($link)) {
                    $items[] = [
                        'titulo' => html_entity_decode(strip_tags($titulo), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'resumen' => html_entity_decode(strip_tags($desc), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'url_nota' => $this->resolverUrlRelativa($baseUrl, $link),
                        'fecha_publicacion' => $pubDate ? \Carbon\Carbon::parse($pubDate) : now(),
                        'es_tapa' => false,
                        'interacciones' => 0,
                    ];
                }
            }
        }
        // Caso 2: Atom (feed -> entry)
        elseif (isset($xml->entry)) {
            foreach ($xml->entry as $entry) {
                $titulo = trim((string) $entry->title);
                $link = (string) ($entry->link['href'] ?? $entry->id ?? '');
                $desc = trim((string) ($entry->summary ?? $entry->content ?? ''));
                $pubDate = trim((string) ($entry->published ?? $entry->updated ?? ''));

                if (! empty($titulo) && ! empty($link)) {
                    $items[] = [
                        'titulo' => html_entity_decode(strip_tags($titulo), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'resumen' => html_entity_decode(strip_tags($desc), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'url_nota' => $this->resolverUrlRelativa($baseUrl, $link),
                        'fecha_publicacion' => $pubDate ? \Carbon\Carbon::parse($pubDate) : now(),
                        'es_tapa' => false,
                        'interacciones' => 0,
                    ];
                }
            }
        }
        // Caso 3: Google News Sitemap (urlset -> url -> news:news)
        elseif (isset($xml->url)) {
            foreach ($xml->url as $urlNode) {
                $loc = trim((string) $urlNode->loc);
                $news = $urlNode->children('news', true)->news ?? null;
                $title = $news ? (string) $news->title : '';
                $pubDate = $news ? (string) $news->publication_date : (string) $urlNode->lastmod;

                if (! empty($loc) && ! empty($title)) {
                    $items[] = [
                        'titulo' => html_entity_decode(strip_tags($title), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'resumen' => '',
                        'url_nota' => $this->resolverUrlRelativa($baseUrl, $loc),
                        'fecha_publicacion' => $pubDate ? \Carbon\Carbon::parse($pubDate) : now(),
                        'es_tapa' => false,
                        'interacciones' => 0,
                    ];
                }
            }
        }

        libxml_clear_errors();

        return array_slice($items, 0, 50);
    }

    /**
     * Extraer titulares y enlaces desde el HTML de la portada web oficial.
     */
    public function parsearPortadaWeb(string $urlWeb): array
    {
        try {
            $res = Http::withHeaders([
                'User-Agent' => self::GOOGLEBOT_USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(8)->get($urlWeb);

            if (! $res->successful()) {
                return [];
            }

            $html = $res->body();
            $items = [];

            // Buscar etiquetas de artículos con encabezados h1, h2, h3 que contengan enlaces
            if (preg_match_all('/<(?:article|div)[^>]*class=["\'][^"\']*(?:noticia|nota|articulo|post|entry|card|item)[^"\']*["\'][^>]*>(.*?)<\/(?:article|div)>/is', $html, $bloques)) {
                foreach (array_slice($bloques[1], 0, 30) as $bloque) {
                    if (preg_match('/<h[1-3][^>]*>\s*<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>\s*<\/h[1-3]>/is', $bloque, $match)
                        || preg_match('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>\s*<h[1-3][^>]*>(.*?)<\/h[1-3]>\s*<\/a>/is', $bloque, $match)) {
                        $link = trim($match[1]);
                        $titulo = trim(strip_tags($match[2]));

                        if (! empty($titulo) && strlen($titulo) > 15 && ! empty($link)) {
                            $items[] = [
                                'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                                'resumen' => '',
                                'url_nota' => $this->resolverUrlRelativa($urlWeb, $link),
                                'fecha_publicacion' => now(),
                                'es_tapa' => count($items) === 0,
                                'interacciones' => 0,
                            ];
                        }
                    }
                }
            }

            // Fallback si no encontró bloques estructurados: buscar h1, h2 directos con enlaces
            if (empty($items)) {
                if (preg_match_all('/<h[1-2][^>]*>\s*<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>\s*<\/h[1-2]>/is', $html, $matches, PREG_SET_ORDER)) {
                    foreach (array_slice($matches, 0, 20) as $idx => $m) {
                        $titulo = trim(strip_tags($m[2]));
                        if (strlen($titulo) > 15) {
                            $items[] = [
                                'titulo' => html_entity_decode($titulo, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                                'resumen' => '',
                                'url_nota' => $this->resolverUrlRelativa($urlWeb, $m[1]),
                                'fecha_publicacion' => now(),
                                'es_tapa' => $idx === 0,
                                'interacciones' => 0,
                            ];
                        }
                    }
                }
            }

            return $items;
        } catch (\Throwable $e) {
            Log::info("No se pudo parsear portada HTML de {$urlWeb}: ".$e->getMessage());

            return [];
        }
    }

    /**
     * Resuelve rutas relativas contra la URL base.
     */
    public function resolverUrlRelativa(string $baseUrl, string $relUrl): string
    {
        $relUrl = trim($relUrl);
        if (str_starts_with($relUrl, 'http://') || str_starts_with($relUrl, 'https://')) {
            return $relUrl;
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';
        $baseRoot = "{$scheme}://{$host}{$port}";

        if (str_starts_with($relUrl, '//')) {
            return "{$scheme}:{$relUrl}";
        }

        if (str_starts_with($relUrl, '/')) {
            return $baseRoot.$relUrl;
        }

        return rtrim($baseUrl, '/').'/'.$relUrl;
    }
}
