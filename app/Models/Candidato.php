<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'ciclo_campana_id',
        'territorio_id',
        'nombre_completo',
        'partido_coalicion',
        'cargo_aspirado',
        'estado_politico',
        'color_hex',
        'es_propio',
        'avatar_url',
        'bio_resumen',
    ];

    protected $casts = [
        'es_propio' => 'boolean',
    ];

    /**
     * Ciclo de campaña / Año al que pertenece el candidato.
     */
    public function cicloCampana(): BelongsTo
    {
        return $this->belongsTo(CicloCampana::class);
    }

    /**
     * Territorio asignado.
     */
    public function territorio(): BelongsTo
    {
        return $this->belongsTo(Territorio::class);
    }

    /**
     * Perfiles en redes sociales asociados a este candidato.
     */
    public function perfilesSociales(): HasMany
    {
        return $this->hasMany(PerfilSocial::class);
    }

    /**
     * Scope para filtrar candidatos por estado político.
     */
    public function scopePorEstado(Builder $query, ?string $estado): Builder
    {
        if ($estado) {
            return $query->where('estado_politico', $estado);
        }
        return $query;
    }

    /**
     * Scope para filtrar candidatos por ciclo de campaña.
     */
    public function scopePorCiclo(Builder $query, ?int $cicloId): Builder
    {
        if ($cicloId) {
            return $query->where('ciclo_campana_id', $cicloId);
        }
        return $query;
    }
}
