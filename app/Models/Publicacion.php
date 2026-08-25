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
        'total_guardados' => 'integer',
        'termometro_humor_social' => 'integer',
        'reacciones_detalladas' => 'array',
        'figuras_acompanantes' => 'array',
        'comentarios_destacados' => 'array',
        'insights_internos_propios' => 'array',
    ];

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
