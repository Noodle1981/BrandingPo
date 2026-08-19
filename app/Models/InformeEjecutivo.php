<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformeEjecutivo extends Model
{
    use HasFactory;

    protected $table = 'informes_ejecutivos';

    protected $fillable = [
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

    public function cicloCampana(): BelongsTo
    {
        return $this->belongsTo(CicloCampana::class);
    }
}
