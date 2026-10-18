<?php

namespace App\Services;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\Publicacion;
use App\Models\Territorio;
use Illuminate\Http\Request;

class CandidatoPresenterService
{
    /**
     * Preparar payload homogéneo para la ficha de detalle (Candidato Propio o Rival Opositor).
     * Garantiza paridad analítica total: canales, Punto Cero, Tiers, padrón, feed y ejes.
     */
    public function prepararPayloadDetalle(Candidato $candidato, Request $request): array
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidato->loadMissing(['perfilesSociales', 'territorio', 'cicloCampana']);

        // Plataformas estándar a auditar
        $plataformasEstandar = [
            'instagram' => ['nombre' => 'Instagram', 'formato_default' => 'Reel'],
            'facebook' => ['nombre' => 'Facebook', 'formato_default' => 'Post/Foto'],
            'threads' => ['nombre' => 'Threads', 'formato_default' => 'Post'],
            'tiktok' => ['nombre' => 'TikTok', 'formato_default' => 'Video Corto'],
            'x_twitter' => ['nombre' => 'X (Twitter)', 'formato_default' => 'Tweet'],
            'youtube' => ['nombre' => 'YouTube', 'formato_default' => 'Video/Shorts'],
            'linkedin' => ['nombre' => 'LinkedIn', 'formato_default' => 'Artículo'],
        ];

        // Mapear cada plataforma asegurando que exista en la vista (con semáforo de 4 estados: Azul, Verde, Rojo, Gris)
        $redesMapeadas = collect($plataformasEstandar)->map(function ($info, $key) use ($candidato) {
            $perfil = $candidato->perfilesSociales->firstWhere('plataforma', $key);

            $existe = (bool) $perfil && ! empty($perfil->handle_usuario);
            $estaActivo = $perfil ? (bool) $perfil->esta_activo : false;
            $estaVerificado = $perfil ? (bool) $perfil->esta_verificado : false;

            if (! $existe) {
                // ⚪ Sin uso / No configurada (Gris)
                $colorEstado = 'gris';
                $estadoTexto = 'Configurar';
            } elseif ($estaVerificado) {
                // 🔵 Certificada / Verificada (Azul)
                $colorEstado = 'azul';
                $estadoTexto = 'Verificada';
            } elseif ($estaActivo) {
                // 🟢 Activa con movimiento de campaña (Verde)
                $colorEstado = 'verde';
                $estadoTexto = 'Activa';
            } else {
                // 🔴 Inactiva / Sin movimiento (Rojo)
                $colorEstado = 'rojo';
                $estadoTexto = 'Inactiva';
            }

            $seguidoresActuales = $perfil ? (int) $perfil->seguidores_actuales : 0;
            $seguidoresBaseline = $perfil ? (int) $perfil->seguidores_punto_cero : 0;
            $crecimientoSeguidores = $seguidoresActuales - $seguidoresBaseline;

            $postsActuales = $perfil ? (int) $perfil->publicaciones_totales : 0;
            $postsBaseline = $perfil ? (int) $perfil->publicaciones_punto_cero : 0;
            $crecimientoPosts = $postsActuales - $postsBaseline;

            $meGustaActuales = $perfil ? (int) $perfil->me_gusta_totales : 0;
            $meGustaBaseline = $perfil ? (int) $perfil->me_gusta_punto_cero : 0;
            $crecimientoMeGusta = $meGustaActuales - $meGustaBaseline;

            $viewsActuales = $perfil ? (int) $perfil->visualizaciones_totales : 0;
            $viewsBaseline = $perfil ? (int) $perfil->visualizaciones_punto_cero : 0;
            $crecimientoViews = $viewsActuales - $viewsBaseline;

            return [
                'key' => $key,
                'nombre' => $info['nombre'],
                'color_estado' => $colorEstado,
                'estado_texto' => $estadoTexto,
                'perfil_id' => $perfil?->id,
                'existe' => (bool) $perfil,
                'esta_activo' => $estaActivo,
                'esta_verificado' => $estaVerificado,
                'handle_usuario' => $perfil?->handle_usuario ?? '',
                'url_perfil' => $perfil?->url_perfil ?? '',
                'foto_perfil_url' => $perfil?->foto_perfil_url,
                'seguidores_actuales' => $seguidoresActuales,
                'seguidos_actuales' => $perfil ? (int) $perfil->seguidos_actuales : 0,
                'publicaciones_totales' => $postsActuales,
                'me_gusta_totales' => $meGustaActuales,
                'visualizaciones_totales' => $viewsActuales,
                // Punto Cero (Baseline Inicial)
                'fecha_punto_cero' => $perfil?->fecha_punto_cero ? $perfil->fecha_punto_cero->format('Y-m-d') : date('Y-m-d'),
                'seguidores_punto_cero' => $seguidoresBaseline,
                'seguidos_punto_cero' => $perfil ? (int) $perfil->seguidos_punto_cero : 0,
                'publicaciones_punto_cero' => $postsBaseline,
                'me_gusta_punto_cero' => $meGustaBaseline,
                'visualizaciones_punto_cero' => $viewsBaseline,
                'notas_punto_cero' => $perfil?->notas_punto_cero ?? '',
                'crecimiento_neto_seguidores' => $crecimientoSeguidores,
                'crecimiento_neto_posts' => $crecimientoPosts,
                'crecimiento_neto_me_gusta' => $crecimientoMeGusta,
                'crecimiento_neto_visualizaciones' => $crecimientoViews,
                'ultima_auditoria_at' => $perfil?->ultima_auditoria_at ? $perfil->ultima_auditoria_at->diffForHumans() : null,
                'ultima_auditoria_fecha' => $perfil?->ultima_auditoria_at ? $perfil->ultima_auditoria_at->format('d/m/Y H:i') : null,
                'delta_seguidores_hoy' => (int) ($perfil?->delta_seguidores_24h ?? 0),
                'delta_seguidos_hoy' => (int) ($perfil?->delta_seguidos_24h ?? 0),
                'delta_posts_hoy' => (int) ($perfil?->delta_posts_24h ?? 0),
                'delta_me_gusta_hoy' => (int) ($perfil?->delta_me_gusta_24h ?? 0),
                'delta_views_hoy' => (int) ($perfil?->delta_views_24h ?? 0),
            ];
        })->values();

        $ciclos = CicloCampana::where('workspace_id', $workspace->id)
            ->orderByDesc('anio')
            ->get(['id', 'anio', 'nombre', 'es_activo']);

        $territorios = Territorio::where('workspace_id', $workspace->id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'tipo', 'poblacion_total', 'padron_electoral']);

        $publicaciones = Publicacion::where('workspace_id', $workspace->id)
            ->where('candidato_id', $candidato->id)
            ->with(['perfilSocial', 'ejeTematico', 'pautaEventos'])
            ->orderByDesc('fecha_publicacion')
            ->get()
            ->map(function ($p) use ($candidato) {
                return [
                    'id' => $p->id,
                    'candidato' => [
                        'id' => $candidato->id,
                        'nombre_completo' => $candidato->nombre_completo,
                        'partido_coalicion' => $candidato->partido_coalicion,
                        'estado_politico' => $candidato->estado_politico,
                        'es_propio' => (bool) $candidato->es_propio,
                        'color_hex' => $candidato->color_hex,
                        'avatar_url' => $candidato->avatar_url,
                    ],
                    'perfil_social' => [
                        'id' => $p->perfilSocial?->id,
                        'plataforma' => $p->perfilSocial?->plataforma,
                        'handle_usuario' => $p->perfilSocial?->handle_usuario,
                    ],
                    'perfil_social_id' => $p->perfil_social_id,
                    'plataforma' => $p->perfilSocial?->plataforma,
                    'eje_tematico_id' => $p->eje_tematico_id,
                    'eje_tematico' => $p->ejeTematico ? [
                        'id' => $p->ejeTematico->id,
                        'pilar_principal' => $p->ejeTematico->pilar_principal,
                        'nombre' => $p->ejeTematico->nombre,
                        'color_badge' => $p->ejeTematico->color_badge,
                        'icono' => $p->ejeTematico->icono,
                    ] : null,
                    'fecha_publicacion' => $p->fecha_publicacion?->format('d/m/Y H:i'),
                    'fecha_publicacion_raw' => $p->fecha_publicacion?->format('Y-m-d\TH:i'),
                    'fecha_carga' => $p->created_at?->format('Y-m-d'),
                    'fecha_relativa' => $p->fecha_publicacion?->diffForHumans(),
                    'fecha_confirmada' => (bool) $p->fecha_confirmada,
                    'tipo_formato' => $p->tipo_formato,
                    'tipo_pauta' => $p->tipo_pauta,
                    'monto_invertido_pauta' => (float) $p->monto_invertido_pauta,
                    'vistas_organicas' => (int) $p->vistas_organicas,
                    'vistas_pagadas' => (int) $p->vistas_pagadas,
                    'url_post' => $p->url_post,
                    'media_url' => $p->media_url,
                    'contenido_resumen' => $p->contenido_resumen,
                    'total_vistas' => (int) $p->total_vistas,
                    'total_likes' => (int) $p->total_likes,
                    'total_comentarios' => (int) $p->total_comentarios,
                    'total_compartidos' => (int) $p->total_compartidos,
                    'total_guardados' => (int) $p->total_guardados,
                    'reacciones_detalladas' => $p->reacciones_detalladas,
                    'sentimiento_predominante' => $p->sentimiento_predominante,
                    'figuras_acompanantes' => $p->figuras_acompanantes,
                    'comentarios_destacados' => $p->comentarios_destacados,
                    'termometro_humor_social' => $p->termometro_humor_social,
                    'score_impacto_organico' => $p->score_impacto_organico,
                    'analisis_traccion' => $p->analisis_traccion,
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

        $ejes = EjeTematico::where('workspace_id', $workspace->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'pilar_principal', 'nombre', 'slug', 'color_badge', 'icono', 'orden']);

        // Desduplicación por TIERS para Seguidores Únicos Reales
        $perfilesActivos = $candidato->perfilesSociales
            ->filter(fn ($p) => (bool) $p->esta_activo && (int) $p->seguidores_actuales > 0)
            ->sortByDesc('seguidores_actuales')
            ->values();

        $seguidoresNetosEstimados = 0;
        $plataformasProcesadas = [];
        $tiersDesglose = [];

        foreach ($perfilesActivos as $idx => $perfil) {
            $seguidoresRed = (int) $perfil->seguidores_actuales;
            $plataforma = $perfil->plataforma;
            $tierNumero = $idx + 1;

            if ($idx === 0) {
                $factorIncremental = 1.0;
                $tierNombre = 'Tier 1 (Red Principal)';
            } else {
                $esMeta = in_array($plataforma, ['facebook', 'instagram', 'threads']);
                $tieneMetaPrevia = count(array_intersect($plataformasProcesadas, ['facebook', 'instagram', 'threads'])) > 0;

                if ($esMeta && $tieneMetaPrevia) {
                    $factorIncremental = 0.35;
                    $tierNombre = "Tier {$tierNumero} (Meta / Solapado)";
                } else {
                    $factorIncremental = 0.55;
                    $tierNombre = "Tier {$tierNumero} (Nueva Audiencia)";
                }
            }

            $seguidoresUnicosAportados = (int) round($seguidoresRed * $factorIncremental);
            $seguidoresNetosEstimados += $seguidoresUnicosAportados;
            $plataformasProcesadas[] = $plataforma;

            $tiersDesglose[] = [
                'tier' => $tierNumero,
                'nombre' => $tierNombre,
                'plataforma' => $plataforma,
                'handle' => $perfil->handle_usuario,
                'seguidores_brutos' => $seguidoresRed,
                'seguidores_unicos' => $seguidoresUnicosAportados,
                'factor_incremental_pct' => round($factorIncremental * 100),
                'esta_activo' => true,
            ];
        }

        $totalSeguidoresBruto = (int) $candidato->perfilesSociales->where('esta_activo', true)->sum('seguidores_actuales');
        if ($seguidoresNetosEstimados <= 0) {
            $seguidoresNetosEstimados = $totalSeguidoresBruto;
        }

        $padronElectoral = (int) ($candidato->territorio?->padron_electoral ?: ($candidato->padron_electoral ?? 0));
        $penetracionNetaPct = $padronElectoral > 0 ? round(($seguidoresNetosEstimados / $padronElectoral) * 100, 1) : 0;
        $penetracionBrutaPct = $padronElectoral > 0 ? round(($totalSeguidoresBruto / $padronElectoral) * 100, 1) : 0;

        return [
            'candidato' => [
                'id' => $candidato->id,
                'nombre_completo' => $candidato->nombre_completo,
                'partido_coalicion' => $candidato->partido_coalicion,
                'cargo_aspirado' => $candidato->cargo_aspirado,
                'estado_politico' => $candidato->estado_politico,
                'color_hex' => $candidato->color_hex ?? ($candidato->es_propio ? '#06b6d4' : '#8b5cf6'),
                'avatar_url' => $candidato->avatar_url,
                'bio_resumen' => $candidato->bio_resumen,
                'es_propio' => (bool) $candidato->es_propio,
                'ciclo_campana_id' => $candidato->ciclo_campana_id,
                'territorio_id' => $candidato->territorio_id,
                'territorio' => $candidato->territorio,
                'total_seguidores' => $totalSeguidoresBruto,
                'total_seguidores_netos' => $seguidoresNetosEstimados,
                'total_seguidores_bruto' => $totalSeguidoresBruto,
                'penetracion_neta_pct' => $penetracionNetaPct,
                'penetracion_bruta_pct' => $penetracionBrutaPct,
                'tiers_desglose' => $tiersDesglose,
                'total_publicaciones' => $candidato->perfilesSociales->where('esta_activo', true)->sum('publicaciones_totales'),
                'perfiles_count' => $candidato->perfilesSociales->count(),
                'perfiles' => $candidato->perfilesSociales->map(fn ($p) => [
                    'id' => $p->id,
                    'plataforma' => $p->plataforma,
                    'handle_usuario' => $p->handle_usuario,
                    'seguidores_actuales' => $p->seguidores_actuales,
                    'esta_verificado' => $p->esta_verificado,
                    'esta_activo' => $p->esta_activo,
                ]),
            ],
            'redes' => $redesMapeadas,
            'ciclos' => $ciclos,
            'territorios' => $territorios,
            'publicaciones' => $publicaciones,
            'ejes' => $ejes,
        ];
    }
}
