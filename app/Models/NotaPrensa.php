<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaPrensa extends Model
{
    use HasFactory;

    protected $table = 'notas_prensa';

    protected $fillable = [
        'medio_prensa_id',
        'candidato_id',
        'fecha_publicacion',
        'titulo',
        'url_nota',
        'resumen',
        'tono_mencion',
        'es_tapa_o_principal',
        'interacciones_en_redes_del_medio',
        'respuesta_replica_candidato',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'es_tapa_o_principal' => 'boolean',
        'interacciones_en_redes_del_medio' => 'integer',
    ];

    public function medioPrensa(): BelongsTo
    {
        return $this->belongsTo(MedioPrensa::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
