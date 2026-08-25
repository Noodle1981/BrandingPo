<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilSocialMetrica extends Model
{
    use HasFactory;

    protected $table = 'perfil_social_metricas';

    protected $fillable = [
        'perfil_social_id',
        'fecha',
        'seguidores',
        'seguidos',
        'publicaciones_totales',
        'me_gusta_totales',
        'visualizaciones_totales',
        'crecimiento_seguidores_dia',
        'crecimiento_seguidos_dia',
        'crecimiento_posts_dia',
        'crecimiento_seguidores_neto',
        'crecimiento_posts_neto',
        'fuente',
        'raw_metadata',
    ];

    protected $casts = [
        'fecha' => 'date',
        'seguidores' => 'integer',
        'seguidos' => 'integer',
        'publicaciones_totales' => 'integer',
        'me_gusta_totales' => 'integer',
        'visualizaciones_totales' => 'integer',
        'crecimiento_seguidores_dia' => 'integer',
        'crecimiento_seguidos_dia' => 'integer',
        'crecimiento_posts_dia' => 'integer',
        'crecimiento_seguidores_neto' => 'integer',
        'crecimiento_posts_neto' => 'integer',
        'raw_metadata' => 'array',
    ];

    /**
     * Perfil social auditado.
     */
    public function perfilSocial(): BelongsTo
    {
        return $this->belongsTo(PerfilSocial::class, 'perfil_social_id');
    }
}
