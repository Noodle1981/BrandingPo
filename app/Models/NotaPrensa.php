<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaPrensa extends Model
{
    use HasFactory;

    protected $table = 'notas_prensa';

    protected $fillable = [
        'workspace_id',
        'medio_prensa_id',
        'candidato_id',
        'origen_tipo',
        'tipo_mencion',
        'fecha_publicacion',
        'titulo',
        'url_nota',
        'resumen',
        'tono_mencion',
        'es_tapa_o_principal',
        'interacciones_en_redes_del_medio',
        'reacciones_desglose',
        'puntuacion_sentimiento',
        'raw_post_id',
        'respuesta_replica_candidato',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'es_tapa_o_principal' => 'boolean',
        'interacciones_en_redes_del_medio' => 'integer',
        'reacciones_desglose' => 'array',
        'puntuacion_sentimiento' => 'float',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function medioPrensa(): BelongsTo
    {
        return $this->belongsTo(MedioPrensa::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }

    /**
     * Scope para filtrar notas de un workspace específico.
     */
    public function scopeDelWorkspace(Builder $query, int|Workspace $workspace): Builder
    {
        $workspaceId = $workspace instanceof Workspace ? $workspace->id : $workspace;

        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope para filtrar por tono de mención (favorable, neutral, critico).
     */
    public function scopePorTono(Builder $query, ?string $tono): Builder
    {
        return $tono ? $query->where('tono_mencion', $tono) : $query;
    }

    /**
     * Scope para filtrar notas de un candidato específico.
     */
    public function scopeDeCandidato(Builder $query, int|Candidato $candidato): Builder
    {
        $candidatoId = $candidato instanceof Candidato ? $candidato->id : $candidato;

        return $query->where('candidato_id', $candidatoId);
    }

    /**
     * Scope para ordenar notas por fecha de publicación descendente.
     */
    public function scopeRecientes(Builder $query): Builder
    {
        return $query->orderByDesc('fecha_publicacion')->orderByDesc('id');
    }
}
