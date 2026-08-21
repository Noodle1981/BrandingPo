<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'nivel_politico',
        'provincia',
        'plan',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Usuarios que tienen acceso a este workspace.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role');
    }

    /**
     * Candidatos de este workspace (propio + contrincantes).
     */
    public function candidatos(): HasMany
    {
        return $this->hasMany(Candidato::class);
    }

    /**
     * Candidato propio (el cliente que paga).
     */
    public function candidatoPropio(): HasMany
    {
        return $this->hasMany(Candidato::class)->where('es_propio', true);
    }

    /**
     * Territorios cargados en este workspace.
     */
    public function territorios(): HasMany
    {
        return $this->hasMany(Territorio::class);
    }

    /**
     * Ciclos de campaña de este workspace.
     */
    public function ciclosCampana(): HasMany
    {
        return $this->hasMany(CicloCampana::class);
    }

    /**
     * Publicaciones (Fast-Flow) de este workspace.
     */
    public function publicaciones(): HasMany
    {
        return $this->hasMany(Publicacion::class);
    }

    /**
     * Notas de prensa (clipping) de este workspace.
     */
    public function notasPrensa(): HasMany
    {
        return $this->hasMany(NotaPrensa::class);
    }

    /**
     * Etiqueta legible del nivel político para mostrar en UI.
     */
    public function getNivelPoliticoLabelAttribute(): string
    {
        return match ($this->nivel_politico) {
            'intendente'            => '🏛️ Intendente / Municipio',
            'gobernador'            => '👑 Gobernador / Provincial',
            'legislador_nacional'   => '🇦🇷 Legislador Nacional',
            'legislador_provincial' => '📋 Legislador Provincial',
            'senador'               => '🏅 Senador',
            'concejal'              => '🏘️ Concejal',
            default                 => ucfirst($this->nivel_politico ?? 'Campaña'),
        };
    }
}
