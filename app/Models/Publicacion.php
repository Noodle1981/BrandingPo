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
        $vistas = (int) $this->total_vistas;
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
        return $query->where('tipo_pauta', 'pauta_paga')->where('monto_invertido_pauta', '>', 0);
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
}
