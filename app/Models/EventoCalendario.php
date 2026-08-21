<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoCalendario extends Model
{
    use HasFactory;

    protected $table = 'eventos_calendario';

    protected $fillable = [
        'workspace_id',
        'ciclo_campana_id',
        'candidato_id',
        'titulo',
        'fecha_inicio',
        'fecha_fin',
        'tipo_evento',
        'lugar',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
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
