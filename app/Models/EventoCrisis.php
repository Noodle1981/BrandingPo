<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoCrisis extends Model
{
    use HasFactory;

    protected $table = 'eventos_crisis';

    protected $fillable = [
        'candidato_id',
        'titulo',
        'fecha_evento',
        'nivel_gravedad',
        'minutos_tiempo_respuesta',
        'estrategia_contencion',
        'estado',
        'impacto_estimado',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'minutos_tiempo_respuesta' => 'integer',
    ];

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
