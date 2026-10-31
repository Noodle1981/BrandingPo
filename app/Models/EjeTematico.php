<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EjeTematico extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'pilar_principal',
        'es_eje_campana',
        'nombre',
        'slug',
        'color_badge',
        'icono',
        'orden',
        'descripcion',
    ];

    protected $casts = [
        'es_eje_campana' => 'boolean',
    ];

    /**
     * Workspace al que pertenece este registro.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Scope para filtrar ejes temáticos de un workspace específico.
     */
    public function scopeDelWorkspace(Builder $query, int|Workspace $workspace): Builder
    {
        $workspaceId = $workspace instanceof Workspace ? $workspace->id : $workspace;

        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope para ordenar por secuencia configurada y luego por id.
     */
    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden')->orderBy('id');
    }
}
