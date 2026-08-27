<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\NotaPrensa;
use App\Models\Publicacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Tablero Central enfocado en el Perfil del Cliente / Candidato Propio del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidatoId = $request->input('candidato_id');

        // Obtener el candidato objetivo dentro del workspace: por ID solicitado o el candidato propio por defecto
        $candidatoQuery = Candidato::where('workspace_id', $workspace->id)
            ->with(['perfilesSociales', 'territorio', 'cicloCampana']);

        $candidato = $candidatoId
            ? (clone $candidatoQuery)->find($candidatoId)
            : (clone $candidatoQuery)->where('es_propio', true)->first();

        // Fallback si no hay candidato propio marcado en el workspace
        if (! $candidato) {
            $candidato = (clone $candidatoQuery)->first();
        }

        // Listado de candidatos del workspace para selector
        $todosCandidatos = Candidato::where('workspace_id', $workspace->id)
            ->select('id', 'nombre_completo', 'partido_coalicion', 'cargo_aspirado', 'estado_politico', 'color_hex', 'es_propio', 'avatar_url')
            ->get();

        if (! $candidato) {
            return Inertia::render('Dashboard', [
                'candidato' => null,
                'candidatos_lista' => [],
                'stats' => [],
                'redes_desglose' => [],
                'ultimas_publicaciones' => [],
                'ultimas_notas_prensa' => [],
                'benchmark' => null,
            ]);
        }

        // Publicaciones del candidato en este workspace
        $publicaciones = Publicacion::where('workspace_id', $workspace->id)
            ->where('candidato_id', $candidato->id)
            ->with(['perfilSocial', 'ejeTematico'])
            ->latest('fecha_publicacion')
            ->get();

        $ejes = \App\Models\EjeTematico::where('workspace_id', $workspace->id)->get();

        // Métricas directas del Perfil del Cliente
        $totalSeguidores = (int) $candidato->perfilesSociales->sum('seguidores_actuales');
        $totalSeguidoresPuntoCero = (int) $candidato->perfilesSociales->sum('seguidores_punto_cero');
        $crecimientoNetoTotalSeguidores = $totalSeguidores - $totalSeguidoresPuntoCero;
        $crecimientoPctTotalSeguidores = $totalSeguidoresPuntoCero > 0
            ? round(($crecimientoNetoTotalSeguidores / $totalSeguidoresPuntoCero) * 100, 1)
            : 0;

        $totalVistas = (int) $publicaciones->sum('total_vistas');
        $totalLikes = (int) $publicaciones->sum('total_likes');
        $totalComentarios = (int) $publicaciones->sum('total_comentarios');
        $totalCompartidos = (int) $publicaciones->sum('total_compartidos');
        $totalRepublicados = (int) $publicaciones->sum('total_republicados');
        $totalGuardados = (int) $publicaciones->sum('total_guardados');
        $totalPauta = (float) $publicaciones->sum('monto_invertido_pauta');
        $totalPosts = $publicaciones->count();

        $interaccionesTotales = $totalLikes + $totalComentarios + $totalCompartidos + $totalRepublicados;
        $scoreImpactoTotal = ($totalLikes * 1) + ($totalComentarios * 3) + ($totalCompartidos * 5) + ($totalRepublicados * 10);

        $engagementRate = $totalVistas > 0
            ? round(($interaccionesTotales / $totalVistas) * 100, 2)
            : ($totalSeguidores > 0 ? round(($interaccionesTotales / $totalSeguidores) * 100, 2) : 0);

        $humorPromedio = $publicaciones->whereNotNull('termometro_humor_social')->avg('termometro_humor_social');
        $humorPromedioFormateado = $humorPromedio ? number_format($humorPromedio, 1) : '4.8';

        // Ratio de Penetración Territorial sobre el Padrón
        $padronElectoral = $candidato->territorio?->padron_electoral ?? 0;
        $ratioPenetracion = $padronElectoral > 0
            ? round(($totalSeguidores / $padronElectoral) * 100, 1)
            : 0;

        // 1. Desglose por Red Social del Candidato con Métricas y Enlace
        $plataformasColores = [
            'instagram' => '#E4405F',
            'facebook' => '#1877F2',
            'threads' => '#000000',
            'tiktok' => '#00F2FE',
            'x_twitter' => '#000000',
            'youtube' => '#FF0000',
            'linkedin' => '#0A66C2',
        ];

        $now = \Carbon\Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $redesDesglose = $candidato->perfilesSociales->map(function ($perfil) use ($publicaciones, $startOfMonth, $plataformasColores) {
            $postsRed = $publicaciones->where('perfil_social_id', $perfil->id);
            $likesRed = (int) $postsRed->sum('total_likes');
            $comentariosRed = (int) $postsRed->sum('total_comentarios');
            $compartidosRed = (int) $postsRed->sum('total_compartidos');
            $republicadosRed = (int) $postsRed->sum('total_republicados');
            $vistasRed = (int) $postsRed->sum('total_vistas');
            $intRed = $likesRed + $comentariosRed + $compartidosRed + $republicadosRed;

            $seguidoresActuales = (int) $perfil->seguidores_actuales;
            $seguidoresPuntoCero = (int) $perfil->seguidores_punto_cero;
            $crecimientoSeguidores = $seguidoresActuales - $seguidoresPuntoCero;

            $postsMes = $postsRed->filter(function ($p) use ($startOfMonth) {
                return $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo($startOfMonth);
            })->count();

            $erRed = ($seguidoresActuales > 0 && $postsRed->count() > 0)
                ? round((($intRed / $postsRed->count()) / $seguidoresActuales) * 100, 2)
                : 0;

            return [
                'id' => $perfil->id,
                'plataforma' => $perfil->plataforma,
                'handle_usuario' => $perfil->handle_usuario,
                'url_perfil' => $perfil->url_perfil,
                'esta_activo' => (bool) $perfil->esta_activo,
                'esta_verificado' => (bool) $perfil->esta_verificado,
                'seguidores' => $seguidoresActuales,
                'seguidores_punto_cero' => $seguidoresPuntoCero,
                'crecimiento_neto_seguidores' => $crecimientoSeguidores,
                'publicaciones_count' => $postsRed->count(),
                'publicaciones_mes' => $postsMes,
                'vistas_acumuladas' => $vistasRed,
                'likes_acumulados' => $likesRed,
                'comentarios_acumulados' => $comentariosRed,
                'interacciones_acumuladas' => $intRed,
                'tasa_engagement' => $erRed,
                'color' => $plataformasColores[$perfil->plataforma] ?? '#06b6d4',
            ];
        })->values();

        // 2. Gráfico Donut: Distribución de Interacciones por Red Social
        $distribucionPlataformas = $redesDesglose->map(function ($red) use ($interaccionesTotales) {
            $pct = $interaccionesTotales > 0
                ? round(($red['interacciones_acumuladas'] / $interaccionesTotales) * 100, 1)
                : 0;

            return [
                'plataforma' => $red['plataforma'],
                'nombre' => ucfirst(str_replace('_', ' ', $red['plataforma'])),
                'interacciones' => $red['interacciones_acumuladas'],
                'porcentaje' => $pct,
                'color' => $red['color'],
            ];
        });

        // 3. Gráfico de Barras: Rendimiento por Formato (Reels vs Fotos vs Posts vs Videos)
        $formatosAgrupados = $publicaciones->groupBy(function ($p) {
            return $p->tipo_formato ?: 'Post';
        });

        $rendimientoPorFormato = $formatosAgrupados->map(function ($posts, $formato) {
            $count = $posts->count();
            $vistasTotal = $posts->sum('total_vistas');
            $likesTotal = $posts->sum('total_likes');
            $comentariosTotal = $posts->sum('total_comentarios');
            $compartidosTotal = $posts->sum('total_compartidos');
            $intTotal = $likesTotal + $comentariosTotal + $compartidosTotal;

            $promVistas = $count > 0 ? round($vistasTotal / $count) : 0;
            $promInt = $count > 0 ? round($intTotal / $count) : 0;
            $er = $vistasTotal > 0 ? round(($intTotal / $vistasTotal) * 100, 2) : 0;

            return [
                'formato' => $formato,
                'cantidad' => $count,
                'total_vistas' => $vistasTotal,
                'total_interacciones' => $intTotal,
                'promedio_vistas' => $promVistas,
                'promedio_interacciones' => $promInt,
                'tasa_engagement' => $er,
            ];
        })->values()->sortByDesc('total_interacciones')->values();

        // 4. Gráfico de Ejes Temáticos
        $distribucionEjes = $ejes->map(function ($eje) use ($publicaciones) {
            $postsEje = $publicaciones->where('eje_tematico_id', $eje->id);
            $vistas = (int) $postsEje->sum('total_vistas');
            $likes = (int) $postsEje->sum('total_likes');
            $comentarios = (int) $postsEje->sum('total_comentarios');
            $compartidos = (int) $postsEje->sum('total_compartidos');
            $republicados = (int) $postsEje->sum('total_republicados');
            $intTotal = $likes + $comentarios + $compartidos + $republicados;

            $humor = $postsEje->whereNotNull('termometro_humor_social')->avg('termometro_humor_social');

            return [
                'id' => $eje->id,
                'nombre' => $eje->nombre,
                'color_badge' => $eje->color_badge ?: '#06b6d4',
                'posts_count' => $postsEje->count(),
                'total_vistas' => $vistas,
                'total_interacciones' => $intTotal,
                'humor_promedio' => $humor ? round($humor, 1) : 4.5,
            ];
        })->filter(fn ($e) => $e['posts_count'] > 0)->values()->sortByDesc('total_interacciones')->values();

        // 5. Histórico Consolidado Time-Series (Evolución de Seguidores y Vistas)
        $perfilesIds = $candidato->perfilesSociales->pluck('id');
        $medicionesHistoricas = \App\Models\PerfilSocialMetrica::whereIn('perfil_social_id', $perfilesIds)
            ->orderBy('fecha', 'asc')
            ->get();

        $historicoAgrupado = $medicionesHistoricas->groupBy(function ($m) {
            return $m->fecha ? $m->fecha->format('d/m') : '';
        })->map(function ($items, $fecha) {
            return [
                'fecha' => $fecha,
                'seguidores' => $items->sum('seguidores'),
                'vistas' => $items->sum('visualizaciones_acumuladas'),
                'interacciones' => $items->sum('interacciones_totales'),
            ];
        })->values();

        // Fallback dinámico si aún no hay mediciones diarias cron grabadas
        if ($historicoAgrupado->count() < 4) {
            $diasMuestra = 7;
            $historicoAgrupado = collect();
            for ($i = $diasMuestra - 1; $i >= 0; $i--) {
                $f = $now->copy()->subDays($i);
                $prog = ($diasMuestra - $i) / $diasMuestra;
                $segStep = (int) ($totalSeguidoresPuntoCero + ($crecimientoNetoTotalSeguidores * $prog));
                $vistasStep = (int) ($totalVistas * $prog * 0.85);
                $intStep = (int) ($interaccionesTotales * $prog * 0.85);

                $historicoAgrupado->push([
                    'fecha' => $f->format('d/m'),
                    'seguidores' => $segStep,
                    'vistas' => $vistasStep,
                    'interacciones' => $intStep,
                ]);
            }
        }

        // 6. Orgánico vs Pauta
        $postsOrganicos = $publicaciones->filter(fn ($p) => $p->tipo_pauta === 'organico' || (float) $p->monto_invertido_pauta <= 0);
        $postsPautados = $publicaciones->filter(fn ($p) => $p->tipo_pauta !== 'organico' && (float) $p->monto_invertido_pauta > 0);

        $vistasOrg = (int) $postsOrganicos->sum('total_vistas');
        $vistasPag = (int) $postsPautados->sum('total_vistas');
        $intOrg = (int) $postsOrganicos->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
        $intPag = (int) $postsPautados->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));

        $costoPorInteraccion = ($totalPauta > 0 && $intPag > 0) ? round($totalPauta / $intPag, 2) : 0;
        $cpmEstimado = ($totalPauta > 0 && $vistasPag > 0) ? round(($totalPauta / $vistasPag) * 1000, 2) : 0;

        $organicoVsPauta = [
            'total_posts_organicos' => $postsOrganicos->count(),
            'total_posts_pautados' => $postsPautados->count(),
            'vistas_organicas' => $vistasOrg,
            'vistas_pagadas' => $vistasPag,
            'interacciones_organicas' => $intOrg,
            'interacciones_pautadas' => $intPag,
            'porcentaje_vistas_organicas' => $totalVistas > 0 ? round(($vistasOrg / $totalVistas) * 100, 1) : 100,
            'porcentaje_vistas_pagadas' => $totalVistas > 0 ? round(($vistasPag / $totalVistas) * 100, 1) : 0,
            'inversion_total' => $totalPauta,
            'costo_por_interaccion' => $costoPorInteraccion,
            'cpm_estimado' => $cpmEstimado,
        ];

        // 7. Top Publicaciones Destacadas
        $topPublicaciones = $publicaciones->sortByDesc(function ($p) {
            return ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10);
        })->take(4)->map(function ($p) use ($candidato) {
            return [
                'id' => $p->id,
                'candidato' => [
                    'id' => $candidato->id,
                    'nombre_completo' => $candidato->nombre_completo,
                    'avatar_url' => $candidato->avatar_url,
                    'estado_politico' => $candidato->estado_politico,
                    'color_hex' => $candidato->color_hex,
                    'es_propio' => $candidato->es_propio,
                ],
                'perfil_social' => [
                    'id' => $p->perfilSocial?->id,
                    'plataforma' => $p->perfilSocial?->plataforma ?? 'instagram',
                    'handle_usuario' => $p->perfilSocial?->handle_usuario ?? '',
                ],
                'plataforma' => $p->perfilSocial?->plataforma ?? 'instagram',
                'fecha_relativa' => $p->fecha_publicacion ? $p->fecha_publicacion->diffForHumans() : 'Reciente',
                'fecha_publicacion' => $p->fecha_publicacion ? $p->fecha_publicacion->format('d/m/Y H:i') : '',
                'tipo_formato' => $p->tipo_formato,
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido_pauta' => $p->monto_invertido_pauta,
                'contenido_resumen' => $p->contenido_resumen,
                'total_likes' => $p->total_likes,
                'total_vistas' => $p->total_vistas,
                'total_comentarios' => $p->total_comentarios,
                'total_compartidos' => $p->total_compartidos,
                'total_republicados' => $p->total_republicados,
                'score_impacto' => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10),
                'termometro_humor_social' => $p->termometro_humor_social,
                'eje_tematico' => $p->ejeTematico ? [
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                ] : null,
                'url_post' => $p->url_post,
                'media_url' => $p->media_url,
            ];
        })->values();

        // Últimas 5 publicaciones del Cliente para el feed
        $ultimasPublicaciones = $publicaciones->take(5)->map(function ($p) use ($candidato) {
            return [
                'id' => $p->id,
                'candidato' => [
                    'id' => $candidato->id,
                    'nombre_completo' => $candidato->nombre_completo,
                    'avatar_url' => $candidato->avatar_url,
                    'estado_politico' => $candidato->estado_politico,
                    'color_hex' => $candidato->color_hex,
                    'es_propio' => $candidato->es_propio,
                ],
                'perfil_social' => [
                    'id' => $p->perfilSocial?->id,
                    'plataforma' => $p->perfilSocial?->plataforma ?? 'instagram',
                    'handle_usuario' => $p->perfilSocial?->handle_usuario ?? '',
                ],
                'plataforma' => $p->perfilSocial?->plataforma ?? 'instagram',
                'fecha_relativa' => $p->fecha_publicacion ? $p->fecha_publicacion->diffForHumans() : 'Reciente',
                'fecha_publicacion' => $p->fecha_publicacion ? $p->fecha_publicacion->format('d/m/Y H:i') : '',
                'tipo_formato' => $p->tipo_formato,
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido_pauta' => $p->monto_invertido_pauta,
                'contenido_resumen' => $p->contenido_resumen,
                'total_likes' => $p->total_likes,
                'total_vistas' => $p->total_vistas,
                'total_comentarios' => $p->total_comentarios,
                'total_compartidos' => $p->total_compartidos,
                'total_republicados' => $p->total_republicados,
                'termometro_humor_social' => $p->termometro_humor_social,
                'eje_tematico' => $p->ejeTematico ? [
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                ] : null,
                'figuras_acompanantes' => $p->figuras_acompanantes ?? [],
                'comentarios_destacados' => $p->comentarios_destacados ?? [],
                'url_post' => $p->url_post,
                'media_url' => $p->media_url,
            ];
        });

        // Notas de prensa donde se menciona al candidato
        $notasPrensa = NotaPrensa::where('workspace_id', $workspace->id)
            ->where('candidato_id', $candidato->id)
            ->with('medioPrensa')
            ->latest('fecha_publicacion')
            ->take(4)
            ->get()
            ->map(function ($nota) {
                return [
                    'id' => $nota->id,
                    'medio_nombre' => $nota->medioPrensa?->nombre ?? 'Medio Digital',
                    'medio_tipo' => $nota->medioPrensa?->tipo_medio ?? 'digital',
                    'titulo' => $nota->titulo,
                    'url_nota' => $nota->url_nota,
                    'tono_mencion' => $nota->tono_mencion,
                    'es_portada' => $nota->es_tapa_o_principal,
                    'fecha' => $nota->fecha_publicacion ? $nota->fecha_publicacion->format('d/m/Y') : '',
                    'resumen' => $nota->resumen,
                ];
            });

        // Benchmark contextual resumido dentro del workspace
        $todasPublicaciones = Publicacion::where('workspace_id', $workspace->id)->get();
        $totalVistasEcosistema = $todasPublicaciones->sum('total_vistas');
        $shareOfVoicePropio = $totalVistasEcosistema > 0
            ? round(($totalVistas / $totalVistasEcosistema) * 100, 1)
            : 0;

        return Inertia::render('Dashboard', [
            'candidato' => [
                'id' => $candidato->id,
                'nombre_completo' => $candidato->nombre_completo,
                'partido_coalicion' => $candidato->partido_coalicion,
                'cargo_aspirado' => $candidato->cargo_aspirado,
                'estado_politico' => $candidato->estado_politico,
                'color_hex' => $candidato->color_hex,
                'es_propio' => $candidato->es_propio,
                'avatar_url' => $candidato->avatar_url,
                'bio_resumen' => $candidato->bio_resumen,
                'territorio_nombre' => $candidato->territorio?->nombre ?? 'Territorio General',
                'padron_electoral' => $padronElectoral,
                'ciclo_nombre' => $candidato->cicloCampana?->nombre ?? 'Campaña 2025',
            ],
            'candidatos_lista' => $todosCandidatos,
            'stats' => [
                'total_seguidores' => number_format($totalSeguidores),
                'total_seguidores_raw' => $totalSeguidores,
                'crecimiento_neto_seguidores' => $crecimientoNetoTotalSeguidores,
                'crecimiento_pct_seguidores' => $crecimientoPctTotalSeguidores,
                'score_impacto_total' => number_format($scoreImpactoTotal),
                'score_impacto_raw' => $scoreImpactoTotal,
                'total_vistas' => number_format($totalVistas),
                'total_vistas_raw' => $totalVistas,
                'total_publicaciones' => $totalPosts,
                'engagement_promedio' => $engagementRate.'%',
                'inversion_pauta_total' => $totalPauta,
                'humor_social_promedio' => $humorPromedioFormateado,
                'ratio_penetracion' => $ratioPenetracion.'%',
                'share_of_voice' => $shareOfVoicePropio.'%',
            ],
            'redes_desglose' => $redesDesglose,
            'distribucion_plataformas' => $distribucionPlataformas,
            'rendimiento_por_formato' => $rendimientoPorFormato,
            'distribucion_ejes' => $distribucionEjes,
            'historico_mediciones' => $historicoAgrupado,
            'organico_vs_pauta' => $organicoVsPauta,
            'top_publicaciones' => $topPublicaciones,
            'ultimas_publicaciones' => $ultimasPublicaciones,
            'ultimas_notas_prensa' => $notasPrensa,
        ]);
    }
}
