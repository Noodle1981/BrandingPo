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
        'workspace_id',
        'medio_prensa_id',
        'candidato_id',
        'origen_tipo',
        'tipo_mencion',
        'fecha_publicacion',
        'titulo',
        'url_nota',
        'resumen',
        'tono_mencion',
        'es_tapa_o_principal',
        'interacciones_en_redes_del_medio',
        'reacciones_desglose',
        'puntuacion_sentimiento',
        'raw_post_id',
        'respuesta_replica_candidato',
    ];

    protected $casts = [
        'fecha_publicacion' => 'date',
        'es_tapa_o_principal' => 'boolean',
        'interacciones_en_redes_del_medio' => 'integer',
        'reacciones_desglose' => 'array',
        'puntuacion_sentimiento' => 'float',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function medioPrensa(): BelongsTo
    {
        return $this->belongsTo(MedioPrensa::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
