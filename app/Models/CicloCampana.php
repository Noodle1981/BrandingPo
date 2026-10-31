<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloCampana extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'anio',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'es_activo',
    ];

    protected $casts = [
        'anio' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_activo' => 'boolean',
    ];

    /**
     * Workspace al que pertenece este registro.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Candidatos participantes en este ciclo de campaña.
     */
    public function candidatos(): HasMany
    {
        return $this->hasMany(Candidato::class);
    }

    /**
     * Scope para filtrar ciclos de un workspace.
     */
    public function scopeDelWorkspace(\Illuminate\Database\Eloquent\Builder $query, int|Workspace $workspace): \Illuminate\Database\Eloquent\Builder
    {
        $workspaceId = $workspace instanceof Workspace ? $workspace->id : $workspace;

        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope para ciclo activo.
     */
    public function scopeActivo(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('es_activo', true);
    }
}
