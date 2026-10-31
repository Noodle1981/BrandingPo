<?php

namespace App\Services;

use App\Http\Resources\CandidatoResource;
use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\Publicacion;
use Carbon\Carbon;

class PublicacionFeedService
{
    /**
     * Construye y serializa todos los datos necesarios para el Feed interactivo de publicaciones.
     */
    public function obtenerFeedData(int $workspaceId, array $filtros): array
    {
        $candidatoId = $filtros['candidato_id'] ?? null;
        $plataforma = $filtros['plataforma'] ?? null;
        $tipoPauta = $filtros['tipo_pauta'] ?? null;
        $ejeTematicoId = $filtros['eje_tematico_id'] ?? null;
        $anio = $filtros['anio'] ?? null;
        $mes = $filtros['mes'] ?? null;
        $search = $filtros['search'] ?? null;
        $filtro = $filtros['filtro'] ?? null;
        $orden = $filtros['orden'] ?? 'recientes';
        $soloHuella = ! empty($filtros['solo_huella']) && filter_var($filtros['solo_huella'], FILTER_VALIDATE_BOOLEAN);

        // Años y meses disponibles en base a datos reales del workspace
        $baseFechasQuery = Publicacion::delWorkspace($workspaceId)
            ->whereNotNull('fecha_publicacion')
            ->dePropios();

        if ($candidatoId) {
            $baseFechasQuery->where('candidato_id', $candidatoId);
        }

        if ($plataforma) {
            $platforms = match ($plataforma) {
                'x_twitter', 'twitter' => ['x_twitter', 'twitter'],
                default => [$plataforma],
            };
            $baseFechasQuery->whereHas('perfilSocial', fn ($q) => $q->whereIn('plataforma', $platforms));
        }

        $nombresMeses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
        ];

        $aniosDisponibles = (clone $baseFechasQuery)->pluck('fecha_publicacion')
            ->map(fn ($f) => Carbon::parse($f)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $fechasMesesQuery = clone $baseFechasQuery;
        if ($anio) {
            $fechasMesesQuery->whereYear('fecha_publicacion', $anio);
        }

        $mesesDisponibles = $fechasMesesQuery->pluck('fecha_publicacion')
            ->map(function ($f) use ($nombresMeses) {
                $numMes = Carbon::parse($f)->format('m');

                return [
                    'numero' => $numMes,
                    'nombre' => $nombresMeses[$numMes] ?? $numMes,
                ];
            })->unique('numero')->sortBy('numero')->values();

        // Consulta de publicaciones
        $query = Publicacion::delWorkspace($workspaceId)
            ->dePropios()
            ->conRelacionesCompletas();

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        if ($plataforma) {
            $platforms = match ($plataforma) {
                'x_twitter', 'twitter' => ['x_twitter', 'twitter'],
                default => [$plataforma],
            };
            $query->whereHas('perfilSocial', fn ($q) => $q->whereIn('plataforma', $platforms));
        }

        if ($tipoPauta === 'con_huella' || $soloHuella) {
            $query->where('tipo_pauta', 'organico')
                ->where(function ($q) {
                    $q->where('url_post', 'like', '%fbclid%')
                        ->orWhere('url_post', 'like', '%ad_id%')
                        ->orWhere('url_post', 'like', '%adset_id%')
                        ->orWhere('url_post', 'like', '%campaign_id%')
                        ->orWhere('url_post', 'like', '%ttclid%')
                        ->orWhere('url_post', 'like', '%twclid%')
                        ->orWhere('url_post', 'like', '%gclid%')
                        ->orWhere('url_post', 'like', '%li_fat_id%')
                        ->orWhere('url_post', 'like', '%utm_medium=%')
                        ->orWhere('url_post', 'like', '%facebook.com/ads%')
                        ->orWhere('contenido_resumen', 'like', '%#publicidad%')
                        ->orWhere('contenido_resumen', 'like', '%#ad%')
                        ->orWhere('contenido_resumen', 'like', '%#patrocinado%')
                        ->orWhere('contenido_resumen', 'like', '%patrocinado%')
                        ->orWhere('contenido_resumen', 'like', '%colaboración pagada%')
                        ->orWhere('contenido_resumen', 'like', '%colaboracion pagada%')
                        ->orWhere('contenido_resumen', 'like', '%paid partnership%')
                        ->orWhere('insights_internos_propios', 'like', '%huella_pauta%');
                });
        } elseif ($tipoPauta) {
            $query->where('tipo_pauta', $tipoPauta);
        }

        if ($ejeTematicoId) {
            $query->where('eje_tematico_id', $ejeTematicoId);
        }

        if ($anio && $mes) {
            $query->whereYear('fecha_publicacion', $anio)
                ->whereMonth('fecha_publicacion', (int) $mes);
        } elseif ($anio) {
            $query->whereYear('fecha_publicacion', $anio);
        } elseif ($mes) {
            $query->whereMonth('fecha_publicacion', (int) $mes);
        }

        if ($search) {
            $query->where('contenido_resumen', 'like', "%{$search}%");
        }

        if ($orden === 'antiguos') {
            $query->orderBy('fecha_publicacion', 'asc')->orderBy('id', 'asc');
        } elseif ($orden === 'interacciones') {
            $query->orderByDesc('total_likes')->orderByDesc('fecha_publicacion');
        } else {
            $query->orderByDesc('fecha_publicacion')->orderByDesc('id');
        }

        $publicaciones = $query->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'candidato' => [
                    'id' => $p->candidato?->id,
                    'nombre_completo' => $p->candidato?->nombre_completo,
                    'partido_coalicion' => $p->candidato?->partido_coalicion,
                    'estado_politico' => $p->candidato?->estado_politico,
                    'es_propio' => $p->candidato?->es_propio,
                    'color_hex' => $p->candidato?->color_hex,
                    'avatar_url' => $p->candidato?->avatar_url,
                ],
                'perfil_social' => [
                    'id' => $p->perfilSocial?->id,
                    'plataforma' => $p->perfilSocial?->plataforma,
                    'handle_usuario' => $p->perfilSocial?->handle_usuario,
                ],
                'eje_tematico' => $p->ejeTematico ? [
                    'id' => $p->ejeTematico->id,
                    'pilar_principal' => $p->ejeTematico->pilar_principal,
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                    'icono' => $p->ejeTematico->icono,
                ] : null,
                'plataforma' => $p->perfilSocial?->plataforma ?: 'facebook',
                'fecha_publicacion' => $p->fecha_publicacion?->format('d/m/Y H:i'),
                'fecha_publicacion_raw' => $p->fecha_publicacion?->format('Y-m-d\TH:i'),
                'fecha_publicacion_humana' => $p->fecha_publicacion ? ($p->fecha_publicacion->year === (int) date('Y') ? $p->fecha_publicacion->locale('es')->isoFormat('D [de] MMMM') : $p->fecha_publicacion->locale('es')->isoFormat('D [de] MMMM [de] YYYY')) : null,
                'fecha_carga' => $p->created_at?->format('Y-m-d'),
                'fecha_relativa' => $p->fecha_publicacion?->diffForHumans(),
                'fecha_confirmada' => (bool) $p->fecha_confirmada,
                'tipo_formato' => $p->tipo_formato,
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido_pauta' => (float) $p->monto_invertido_pauta,
                'vistas_organicas' => $p->vistas_organicas,
                'vistas_pagadas' => $p->vistas_pagadas,
                'total_vistas' => $p->total_vistas,
                'total_likes' => $p->total_likes,
                'total_comentarios' => $p->total_comentarios,
                'total_compartidos' => $p->total_compartidos,
                'total_republicados' => $p->total_republicados,
                'total_guardados' => $p->total_guardados,
                'score_impacto_organico' => $p->score_impacto_organico,
                'tasa_viralidad_pct' => $p->tasa_viralidad_pct,
                'analisis_traccion' => $p->analisis_traccion,
                'reacciones_detalladas' => $p->reacciones_detalladas,
                'aprobacion_neta_pct' => $p->aprobacion_neta_pct,
                'termometro_humor_social' => $p->termometro_humor_social,
                'sentimiento_predominante' => $p->sentimiento_predominante,
                'comentario_destacado' => $p->comentario_destacado,
                'figura_acompanante' => $p->figura_acompanante,
                'url_post' => $p->url_post,
                'media_url' => $p->media_url,
                'media_embed_url' => $p->media_embed_url,
                'contenido_resumen' => $p->contenido_resumen,
                'pauta_eventos' => $p->pautaEventos ? $p->pautaEventos->map(function ($ev) use ($p) {
                    $deltaLikes = max(0, (int) $p->total_likes - (int) $ev->likes_snapshot);
                    $deltaComentarios = max(0, (int) $p->total_comentarios - (int) $ev->comentarios_snapshot);
                    $costoPorLike = ($ev->monto_nuevo > 0 && $deltaLikes > 0)
                        ? round((float) $ev->monto_nuevo / $deltaLikes, 2)
                        : null;

                    return [
                        'id' => $ev->id,
                        'tipo_pauta_anterior' => $ev->tipo_pauta_anterior,
                        'tipo_pauta_nuevo' => $ev->tipo_pauta_nuevo,
                        'monto_anterior' => (float) $ev->monto_anterior,
                        'monto_nuevo' => (float) $ev->monto_nuevo,
                        'fecha_evento' => $ev->fecha_evento?->format('d/m/Y H:i'),
                        'fecha_evento_humana' => $ev->fecha_evento?->diffForHumans(),
                        'seguidores_canal_snapshot' => (int) $ev->seguidores_canal_snapshot,
                        'likes_snapshot' => (int) $ev->likes_snapshot,
                        'comentarios_snapshot' => (int) $ev->comentarios_snapshot,
                        'vistas_snapshot' => (int) $ev->vistas_snapshot,
                        'origen' => $ev->origen,
                        'delta_likes_atribuibles' => $deltaLikes,
                        'delta_comentarios_atribuibles' => $deltaComentarios,
                        'costo_por_like' => $costoPorLike,
                        'notas' => $ev->notas,
                    ];
                }) : [],
            ];
        });

        $candidatos = Candidato::delWorkspace($workspaceId)
            ->propios()
            ->ordenados()
            ->with('perfilesSociales')
            ->get();

        $ejes = EjeTematico::delWorkspace($workspaceId)
            ->ordenados()
            ->get(['id', 'pilar_principal', 'nombre', 'slug', 'color_badge', 'icono', 'orden']);

        return [
            'publicaciones' => $publicaciones,
            'candidatos' => CandidatoResource::collection($candidatos)->resolve(),
            'ejes' => $ejes,
            'anios_disponibles' => $aniosDisponibles,
            'meses_disponibles' => $mesesDisponibles,
            'filtros' => [
                'filtro' => $filtro,
                'candidato_id' => $candidatoId,
                'plataforma' => $plataforma,
                'tipo_pauta' => $tipoPauta,
                'eje_tematico_id' => $ejeTematicoId,
                'anio' => $anio,
                'mes' => $mes,
                'search' => $search,
                'orden' => $orden,
            ],
            'stats_resumen' => [
                'total_posts' => $publicaciones->count(),
                'total_vistas' => $publicaciones->sum('total_vistas'),
                'total_pauta_invertida' => $publicaciones->sum('monto_invertido_pauta'),
            ],
        ];
    }
}
