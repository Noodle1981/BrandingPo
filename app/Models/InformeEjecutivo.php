<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformeEjecutivo extends Model
{
    use HasFactory;

    protected $table = 'informes_ejecutivos';

    protected $fillable = [
        'workspace_id',
        'ciclo_campana_id',
        'titulo',
        'fecha_generacion',
        'periodo_cubierto',
        'resumen_ejecutivo',
        'metricas_clave_snapshot',
        'conclusiones_estrategicas',
    ];

    protected $casts = [
        'fecha_generacion' => 'date',
        'metricas_clave_snapshot' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function cicloCampana(): BelongsTo
    {
        return $this->belongsTo(CicloCampana::class);
    }

    /**
     * Scope para filtrar informes ejecutivos de un workspace específico.
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
