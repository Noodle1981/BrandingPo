<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlianzaPolitica extends Model
{
    use HasFactory;

    protected $table = 'alianzas_politicas';

    protected $fillable = [
        'workspace_id',
        'candidato_id',
        'nombre_figura',
        'cargo_o_rol',
        'tipo_impacto',
        'notas_observacion',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }

    /**
     * Scope para filtrar alianzas de un workspace específico.
     */
    public function scopeDelWorkspace(Builder $query, int|Workspace $workspace): Builder
    {
        $workspaceId = $workspace instanceof Workspace ? $workspace->id : $workspace;

        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope para filtrar alianzas por candidato.
     */
    public function scopeDeCandidato(Builder $query, int|Candidato $candidato): Builder
    {
        $candidatoId = $candidato instanceof Candidato ? $candidato->id : $candidato;

        return $query->where('candidato_id', $candidatoId);
    }
}
