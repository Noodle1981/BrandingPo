<?php

namespace App\Models;

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
}
