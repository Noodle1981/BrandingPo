<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publicacion extends Model
{
    use HasFactory;

    protected $table = 'publicaciones';

    /** Tipos de pauta que implican inversión económica. */
    public const TIPOS_CON_INVERSION = ['organico_impulsado', 'pauta_paga', 'colaboracion_pagada'];

    protected $fillable = [
        'workspace_id',
        'candidato_id',
        'perfil_social_id',
        'eje_tematico_id',
        'fecha_publicacion',
        'tipo_formato',
        'tipo_pauta',
        'monto_invertido_pauta',
        'vistas_organicas',
        'vistas_pagadas',
        'url_post',
        'media_url',
        'contenido_resumen',
        'total_vistas',
        'total_likes',
        'total_comentarios',
        'total_compartidos',
        'total_republicados',
        'total_guardados',
        'reacciones_detalladas',
        'sentimiento_predominante',
        'figuras_acompanantes',
        'comentarios_destacados',
        'termometro_humor_social',
        'insights_internos_propios',
    ];

    protected $casts = [
        'fecha_publicacion' => 'datetime',
        'monto_invertido_pauta' => 'decimal:2',
        'vistas_organicas' => 'integer',
        'vistas_pagadas' => 'integer',
        'total_vistas' => 'integer',
        'total_likes' => 'integer',
        'total_comentarios' => 'integer',
        'total_compartidos' => 'integer',
        'total_republicados' => 'integer',
        'total_guardados' => 'integer',
        'termometro_humor_social' => 'integer',
        'reacciones_detalladas' => 'array',
        'figuras_acompanantes' => 'array',
        'comentarios_destacados' => 'array',
        'insights_internos_propios' => 'array',
    ];

    protected $appends = [
        'aprobacion_neta_pct',
        'score_impacto_organico',
        'tasa_viralidad_pct',
    ];

    /**
     * Score de Impacto Orgánico Ponderado (1pt Like, 3pts Comentario, 5pts Compartido, 10pts Republicado).
     */
    public function getScoreImpactoOrganicoAttribute(): int
    {
        $likes = (int) $this->total_likes;
        $comentarios = (int) $this->total_comentarios * 3;
        $compartidos = (int) $this->total_compartidos * 5;
        $republicados = (int) ($this->total_republicados ?? 0) * 10;

        return $likes + $comentarios + $compartidos + $republicados;
    }

    /**
     * Tasa de Viralidad Efectiva (%) sobre vistas.
     */
    public function getTasaViralidadPctAttribute(): float
    {
        $vistas = (int) ($this->vistas_organicas > 0 ? $this->vistas_organicas : $this->total_vistas);
        if ($vistas <= 0) {
            return 0.0;
        }

        $score = $this->score_impacto_organico;

        return (float) round(($score / $vistas) * 100, 2);
    }

    /**
     * Índice de Aprobación Neta (%) calculado o persistido.
     */
    public function getAprobacionNetaPctAttribute(): float
    {
        if (isset($this->insights_internos_propios['indice_aprobacion_neta'])) {
            return (float) $this->insights_internos_propios['indice_aprobacion_neta'];
        }

        $r = $this->reacciones_detalladas;
        if (! $r || ! is_array($r)) {
            return 100.0;
        }

        $pos = (int) ($r['me_gusta'] ?? 0) + (int) ($r['me_encanta'] ?? 0) + (int) ($r['me_importa'] ?? 0);
        $neg = (int) ($r['me_enoja'] ?? 0) + (int) ($r['me_entristece'] ?? 0);
        $tot = $pos + $neg + (int) ($r['me_divierte'] ?? 0) + (int) ($r['me_asombra'] ?? 0);

        if ($tot === 0) {
            return 100.0;
        }

        return (float) round((($pos - $neg) / $tot) * 100, 1);
    }

    /**
     * Workspace al que pertenece este registro.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Candidato autor de la publicación.
     */
    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }

    /**
     * Red social en la que se publicó.
     */
    public function perfilSocial(): BelongsTo
    {
        return $this->belongsTo(PerfilSocial::class);
    }

    /**
     * Eje temático o narrativa en que clasifica.
     */
    public function ejeTematico(): BelongsTo
    {
        return $this->belongsTo(EjeTematico::class);
    }

    /**
     * Histórico de cortes temporales.
     */
    public function historicos(): HasMany
    {
        return $this->hasMany(PublicacionHistorico::class);
    }

    /**
     * Scope para filtrar publicaciones con pauta publicitaria.
     */
    public function scopeConPauta(Builder $query): Builder
    {
        return $query->whereIn('tipo_pauta', self::TIPOS_CON_INVERSION)->where('monto_invertido_pauta', '>', 0);
    }

    /**
     * Scope para filtrar publicaciones 100% orgánicas (sin inversión publicitaria).
     */
    public function scopeOrganicoPuro(Builder $query): Builder
    {
        return $query->where('tipo_pauta', 'organico')->where(function ($q) {
            $q->whereNull('monto_invertido_pauta')->orWhere('monto_invertido_pauta', '<=', 0);
        });
    }

    /**
     * Scope para filtrar por plataforma.
     */
    public function scopePorPlataforma(Builder $query, ?string $plataforma): Builder
    {
        if ($plataforma) {
            return $query->whereHas('perfilSocial', fn ($q) => $q->where('plataforma', $plataforma));
        }

        return $query;
    }

    /**
     * Buscar si existe una publicación duplicada por URL canonicalizada o por huella digital de contenido/fecha.
     */
    public static function buscarDuplicado(
        int $workspaceId,
        ?string $url = null,
        ?int $candidatoId = null,
        ?int $perfilSocialId = null,
        ?string $fecha = null,
        ?string $contenido = null,
        ?int $ignoreId = null
    ): ?self {
        $baseQuery = static::where('workspace_id', $workspaceId);
        if ($ignoreId) {
            $baseQuery->where('id', '!=', $ignoreId);
        }

        // 1. Verificación por URL (si fue provista)
        if (! empty($url)) {
            $canonical = \App\Services\SocialProfileScraperService::canonicalizePostUrl($url) ?? $url;
            $rawClean = rtrim(trim($url), '/');
            $canonicalClean = rtrim($canonical, '/');

            // Buscar coincidencia exacta o por versión canonicalizada
            $encontradoPorUrl = (clone $baseQuery)->where(function ($q) use ($url, $canonical, $rawClean, $canonicalClean) {
                $q->where('url_post', $url)
                  ->orWhere('url_post', $canonical)
                  ->orWhere('url_post', $rawClean)
                  ->orWhere('url_post', $canonicalClean)
                  ->orWhere('url_post', $canonicalClean.'/')
                  ->orWhere('url_post', $rawClean.'/');
            })->first();

            if ($encontradoPorUrl) {
                return $encontradoPorUrl;
            }

            // Búsqueda inteligente por identificador único (Shortcode IG / Status ID X / Video ID TT)
            $parsedPath = parse_url($canonical, PHP_URL_PATH);
            if (! empty($parsedPath)) {
                $segments = array_values(array_filter(explode('/', $parsedPath)));
                $ultimoSegmento = end($segments);
                if ($ultimoSegmento && strlen($ultimoSegmento) >= 5 && ! in_array($ultimoSegmento, ['watch', 'post', 'video', 'share', 'reel', 'p'])) {
                    $encontradoPorSegmento = (clone $baseQuery)
                        ->whereNotNull('url_post')
                        ->where('url_post', 'LIKE', '%'.$ultimoSegmento.'%')
                        ->first();

                    if ($encontradoPorSegmento) {
                        return $encontradoPorSegmento;
                    }
                }
            }
        }

        // 2. Verificación por huella de contenido y fecha (para posts manuales o idénticos)
        if ($candidatoId && $perfilSocialId && ! empty($contenido)) {
            $contenidoLimpio = trim($contenido);
            $queryContenido = (clone $baseQuery)
                ->where('candidato_id', $candidatoId)
                ->where('perfil_social_id', $perfilSocialId)
                ->where('contenido_resumen', $contenidoLimpio);

            if (! empty($fecha)) {
                $fechaDia = date('Y-m-d', strtotime($fecha));
                $queryContenido->whereDate('fecha_publicacion', $fechaDia);
            }

            $encontradoPorContenido = $queryContenido->first();
            if ($encontradoPorContenido) {
                return $encontradoPorContenido;
            }
        }

        return null;
    }
}
