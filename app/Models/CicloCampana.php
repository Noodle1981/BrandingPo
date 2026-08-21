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
}
