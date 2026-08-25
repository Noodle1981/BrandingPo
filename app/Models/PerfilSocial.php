<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PerfilSocial extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidato_id',
        'plataforma',
        'handle_usuario',
        'url_perfil',
        'seguidores_actuales',
        'publicaciones_totales',
        'me_gusta_totales',
        'visualizaciones_totales',
        'esta_verificado',
        'esta_activo',
        'seguidos_actuales',
        'foto_perfil_url',
        'fecha_punto_cero',
        'seguidores_punto_cero',
        'seguidos_punto_cero',
        'publicaciones_punto_cero',
        'me_gusta_punto_cero',
        'visualizaciones_punto_cero',
        'notas_punto_cero',
        'demografia_interna_propia',
        'ultima_auditoria_at',
        'delta_seguidores_24h',
        'delta_seguidos_24h',
        'delta_posts_24h',
        'delta_me_gusta_24h',
        'delta_views_24h',
    ];

    protected $casts = [
        'seguidores_actuales' => 'integer',
        'publicaciones_totales' => 'integer',
        'me_gusta_totales' => 'integer',
        'visualizaciones_totales' => 'integer',
        'seguidos_actuales' => 'integer',
        'esta_verificado' => 'boolean',
        'esta_activo' => 'boolean',
        'fecha_punto_cero' => 'date',
        'seguidores_punto_cero' => 'integer',
        'seguidos_punto_cero' => 'integer',
        'publicaciones_punto_cero' => 'integer',
        'me_gusta_punto_cero' => 'integer',
        'visualizaciones_punto_cero' => 'integer',
        'demografia_interna_propia' => 'array',
        'ultima_auditoria_at' => 'datetime',
        'delta_seguidores_24h' => 'integer',
        'delta_seguidos_24h' => 'integer',
        'delta_posts_24h' => 'integer',
        'delta_me_gusta_24h' => 'integer',
        'delta_views_24h' => 'integer',
    ];

    /**
     * Candidato propietario de esta red social.
     */
    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }

    /**
     * Historial de auditorías y mediciones time-series.
     */
    public function metricas(): HasMany
    {
        return $this->hasMany(PerfilSocialMetrica::class)->orderByDesc('fecha');
    }

    /**
     * Historial de auditorías y mediciones time-series orden cronológico ascendente.
     */
    public function metricasHistoricas(): HasMany
    {
        return $this->hasMany(PerfilSocialMetrica::class)->orderBy('fecha', 'asc');
    }

    /**
     * Última medición registrada.
     */
    public function ultimaMetrica(): HasOne
    {
        return $this->hasOne(PerfilSocialMetrica::class)->latestOfMany('fecha');
    }

    /**
     * Registrar una nueva lectura / auditoría de métricas para este perfil social.
     */
    public function registrarMedicion(array $data, string $fuente = 'manual'): PerfilSocialMetrica
    {
        $hoy = now()->toDateString();

        $seguidores = isset($data['seguidores']) && $data['seguidores'] !== null ? (int) $data['seguidores'] : (int) $this->seguidores_actuales;
        $seguidos = isset($data['seguidos']) && $data['seguidos'] !== null ? (int) $data['seguidos'] : (int) $this->seguidos_actuales;
        $publicaciones = isset($data['publicaciones']) && $data['publicaciones'] !== null ? (int) $data['publicaciones'] : (int) $this->publicaciones_totales;
        $meGusta = isset($data['me_gusta_totales']) && $data['me_gusta_totales'] !== null ? (int) $data['me_gusta_totales'] : (int) $this->me_gusta_totales;
        $vistas = isset($data['visualizaciones_totales']) && $data['visualizaciones_totales'] !== null ? (int) $data['visualizaciones_totales'] : (int) $this->visualizaciones_totales;

        // Buscar última medición previa a hoy
        $medicionAnterior = $this->metricas()
            ->where('fecha', '<', $hoy)
            ->first();

        $baseSeguidores = $medicionAnterior ? $medicionAnterior->seguidores : (int) ($this->seguidores_actuales ?: $this->seguidores_punto_cero);
        $baseSeguidos = $medicionAnterior ? $medicionAnterior->seguidos : (int) ($this->seguidos_actuales ?: $this->seguidos_punto_cero);
        $basePosts = $medicionAnterior ? $medicionAnterior->publicaciones_totales : (int) ($this->publicaciones_totales ?: $this->publicaciones_punto_cero);

        $deltaSeguidoresDia = $seguidores - $baseSeguidores;
        $deltaSeguidosDia = $seguidos - $baseSeguidos;
        $deltaPostsDia = $publicaciones - $basePosts;

        $deltaSeguidoresNeto = $seguidores - (int) $this->seguidores_punto_cero;
        $deltaPostsNeto = $publicaciones - (int) $this->publicaciones_punto_cero;

        // Crear o actualizar la medición de hoy
        $metrica = PerfilSocialMetrica::updateOrCreate(
            [
                'perfil_social_id' => $this->id,
                'fecha' => $hoy,
            ],
            [
                'seguidores' => $seguidores,
                'seguidos' => $seguidos,
                'publicaciones_totales' => $publicaciones,
                'me_gusta_totales' => $meGusta,
                'visualizaciones_totales' => $vistas,
                'crecimiento_seguidores_dia' => $deltaSeguidoresDia,
                'crecimiento_seguidos_dia' => $deltaSeguidosDia,
                'crecimiento_posts_dia' => $deltaPostsDia,
                'crecimiento_seguidores_neto' => $deltaSeguidoresNeto,
                'crecimiento_posts_neto' => $deltaPostsNeto,
                'fuente' => $fuente,
                'raw_metadata' => $data['raw_metadata'] ?? null,
            ]
        );

        // Actualizar datos actuales del perfil social
        $updateFields = [
            'seguidores_actuales' => $seguidores,
            'seguidos_actuales' => $seguidos,
            'publicaciones_totales' => $publicaciones,
            'me_gusta_totales' => $meGusta,
            'visualizaciones_totales' => $vistas,
            'ultima_auditoria_at' => now(),
            'delta_seguidores_24h' => $deltaSeguidoresDia,
            'delta_seguidos_24h' => $deltaSeguidosDia,
            'delta_posts_24h' => $deltaPostsDia,
            'delta_me_gusta_24h' => $meGusta - (int) ($this->me_gusta_totales ?: $this->me_gusta_punto_cero),
            'delta_views_24h' => $vistas - (int) ($this->visualizaciones_totales ?: $this->visualizaciones_punto_cero),
        ];

        if (! empty($data['foto_perfil_url'])) {
            $updateFields['foto_perfil_url'] = $data['foto_perfil_url'];
        }

        $this->update($updateFields);

        return $metrica;
    }
}
