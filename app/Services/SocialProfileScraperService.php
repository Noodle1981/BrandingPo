<?php

namespace App\Services;

use App\Services\Scrapers\SocialPostScraper;
use App\Services\Scrapers\SocialProfileScraper;

class SocialProfileScraperService
{
    public function __construct(
        protected ?SocialProfileScraper $profileScraper = null,
        protected ?SocialPostScraper $postScraper = null
    ) {
        $this->profileScraper = $profileScraper ?? new SocialProfileScraper;
        $this->postScraper = $postScraper ?? new SocialPostScraper;
    }

    /**
     * Intentar extraer datos públicos (foto, seguidores, seguidos, posts) a partir de la URL.
     */
    public function scrapeProfile(string $url, string $plataforma): array
    {
        return $this->profileScraper->scrapeProfile($url, $plataforma);
    }

    /**
     * Extraer datos públicos de una publicación (Instagram, TikTok, YouTube, etc.)
     */
    public function scrapePost(string $input, ?string $plataforma = null): array
    {
        return $this->postScraper->scrapePost($input, $plataforma);
    }

    /**
     * Extraer la fecha y hora real de publicación original de la red social.
     */
    public function extractPublicationDate(string $html, string $plataforma = 'generic'): ?string
    {
        return $this->postScraper->extractPublicationDate($html, $plataforma);
    }

        /**
     * Extraer y normalizar una URL limpia desde cualquier tipo de input del usuario.
     */
    public static function extraerUrlDesdeInput(string $input): string
    {
        return SocialPostScraper::extraerUrlDesdeInput($input);
    }

    /**
     * Canonicalizar la URL de una publicación para garantizar unicidad absoluta en el sistema.
     */
    public static function canonicalizePostUrl(?string $url): ?string
    {
        return SocialPostScraper::canonicalizePostUrl($url);
    }

    /**
     * Motor Integral de Detección de Huella de Pauta Multi-plataforma.
     */
    public static function analizarHuellaPauta(?string $url, ?string $texto = null): array
    {
        return SocialPostScraper::analizarHuellaPauta($url, $texto);
    }
}
