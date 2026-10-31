<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoPartida extends Model
{
    use HasFactory;

    protected $table = 'presupuesto_partidas';

    protected $fillable = [
        'workspace_id',
        'ciclo_campana_id',
        'candidato_id',
        'categoria',
        'monto_asignado',
        'monto_ejecutado',
        'notas',
    ];

    protected $casts = [
        'monto_asignado' => 'decimal:2',
        'monto_ejecutado' => 'decimal:2',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function cicloCampana(): BelongsTo
    {
        return $this->belongsTo(CicloCampana::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }

    /**
     * Scope para filtrar partidas presupuestarias de un workspace específico.
     */
    public function scopeDelWorkspace(Builder $query, int|Workspace $workspace): Builder
    {
        $workspaceId = $workspace instanceof Workspace ? $workspace->id : $workspace;

        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope para filtrar por ciclo de campaña.
     */
    public function scopeDelCiclo(Builder $query, int|CicloCampana $ciclo): Builder
    {
        $cicloId = $ciclo instanceof CicloCampana ? $ciclo->id : $ciclo;

        return $query->where('ciclo_campana_id', $cicloId);
    }
}
