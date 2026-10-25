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
        'workspace_id',
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
     * Workspace al que pertenece este registro.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

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

    /**
     * Obtener la URL del avatar del candidato con fallback automático a fotos scrapeadas de sus redes.
     */
    public function getAvatarUrlAttribute(?string $value): ?string
    {
        if ($value && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'))) {
            return $value;
        }

        if ($value && file_exists(public_path(ltrim($value, '/')))) {
            return $value;
        }

        // Si no hay avatar propio o el archivo local no existe, buscar foto scrapeada de sus canales sociales
        $perfiles = $this->relationLoaded('perfilesSociales')
            ? $this->perfilesSociales
            : $this->perfilesSociales()->get();

        $ordenPlataformas = ['facebook', 'instagram', 'tiktok', 'threads'];
        foreach ($ordenPlataformas as $red) {
            $perfil = $perfiles->firstWhere('plataforma', $red);
            if ($perfil && ! empty($perfil->foto_perfil_url) && file_exists(public_path(ltrim($perfil->foto_perfil_url, '/')))) {
                return $perfil->foto_perfil_url;
            }
        }

        foreach ($perfiles as $perfil) {
            if (! empty($perfil->foto_perfil_url) && file_exists(public_path(ltrim($perfil->foto_perfil_url, '/')))) {
                return $perfil->foto_perfil_url;
            }
        }

        return $value;
    }
}
