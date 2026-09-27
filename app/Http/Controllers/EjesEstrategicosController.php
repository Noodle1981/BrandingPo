<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\NotaPrensa;
use App\Models\Publicacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EjesEstrategicosController extends Controller
{
    /**
     * Tablero de Situación Estratégica de los 3 Ejes Principales de Campaña.
     * Permite filtrar por mes o consultar el histórico acumulado.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);

        // Candidato propio del workspace
        $candidatoPropio = Candidato::where('workspace_id', $workspace->id)
            ->where('es_propio', true)
            ->with(['territorio', 'perfilesSociales'])
            ->first();

        if (! $candidatoPropio) {
            $candidatoPropio = Candidato::where('workspace_id', $workspace->id)
                ->with(['territorio', 'perfilesSociales'])
                ->first();
        }

        // Si no hay candidato, devolver estructura vacía segura
        if (! $candidatoPropio) {
            return Inertia::render('Ejes/Index', [
                'candidato' => null,
                'periodo_seleccionado' => 'todos',
                'periodos_disponibles' => [],
                'pilares' => [],
                'bloque_institucional' => null,
                'balance_discursivo' => [
                    'total_posts' => 0,
                    'pct_en_pilares' => 0,
                    'pct_institucional' => 0,
                    'diagnostico' => 'Sin publicaciones registradas en la campaña.',
                    'tipo_alerta' => 'info',
                ],
                'evolucion_historica' => [],
                'territorio' => null,
            ]);
        }

        // Todas las publicaciones históricas del candidato en este workspace
        $todasPublicaciones = Publicacion::where('workspace_id', $workspace->id)
            ->where('candidato_id', $candidatoPropio->id)
            ->with(['perfilSocial', 'ejeTematico'])
            ->latest('fecha_publicacion')
            ->get();

        // ─────────────────────────────────────────────────────────────
        // 1. GENERACIÓN DE PERIODOS DISPONIBLES (HISTÓRICO + MESES)
        // ─────────────────────────────────────────────────────────────
        $mesesEspanol = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        $mesesCortos = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];

        $mesActualYm = Carbon::now()->format('Y-m');

        // Agrupación mensual para períodos
        $gruposPorMes = $todasPublicaciones->sortBy('fecha_publicacion')->groupBy(function ($p) {
            return $p->fecha_publicacion ? $p->fecha_publicacion->format('Y-m') : 'sin_fecha';
        })->reject(fn ($posts, $key) => $key === 'sin_fecha');

        $desglosePeriodos = $gruposPorMes->map(function ($postsMes, $keyYm) use ($mesesEspanol, $mesActualYm) {
            $partes = explode('-', $keyYm);
            $ano = $partes[0] ?? date('Y');
            $mesInt = (int) ($partes[1] ?? 1);
            $nombreMes = ($mesesEspanol[$mesInt] ?? $keyYm) . " {$ano}";

            return [
                'clave' => $keyYm,
                'nombre' => $nombreMes . ($keyYm === $mesActualYm ? ' (En curso)' : ''),
                'es_actual' => $keyYm === $mesActualYm,
                'total_posts' => $postsMes->count(),
            ];
        })->values();

        // Asegurar que el mes en curso esté presente en los filtros
        if (! $desglosePeriodos->firstWhere('clave', $mesActualYm)) {
            $partesNow = explode('-', $mesActualYm);
            $anoNow = $partesNow[0] ?? date('Y');
            $mesIntNow = (int) ($partesNow[1] ?? 1);
            $desglosePeriodos->push([
                'clave' => $mesActualYm,
                'nombre' => ($mesesEspanol[$mesIntNow] ?? $mesActualYm) . " {$anoNow} (En curso)",
                'es_actual' => true,
                'total_posts' => 0,
            ]);
        }

        $desglosePeriodos = $desglosePeriodos->sortByDesc('clave')->values();

        $periodosDisponibles = collect([
            [
                'clave' => 'todos',
                'nombre' => '🌐 Campaña Completa (Histórico)',
                'es_actual' => false,
                'total_posts' => $todasPublicaciones->count(),
            ],
        ])->concat($desglosePeriodos)->values();

        // Determinar período activo
        $periodoSeleccionado = $request->query('mes', 'todos');
        if (! $periodosDisponibles->firstWhere('clave', $periodoSeleccionado)) {
            $periodoSeleccionado = 'todos';
        }

        // ─────────────────────────────────────────────────────────────
        // 2. FILTRADO DE PUBLICACIONES POR PERÍODO
        // ─────────────────────────────────────────────────────────────
        if ($periodoSeleccionado === 'todos') {
            $publicacionesPeriodo = $todasPublicaciones;
        } else {
            $publicacionesPeriodo = $todasPublicaciones->filter(function ($p) use ($periodoSeleccionado) {
                return $p->fecha_publicacion && $p->fecha_publicacion->format('Y-m') === $periodoSeleccionado;
            })->values();
        }

        // ─────────────────────────────────────────────────────────────
        // 3. ESTRUCTURA Y CALIBRACIÓN DE LOS 3 EJES DE CAMPAÑA
        // ─────────────────────────────────────────────────────────────
        $ejesCatalogados = EjeTematico::where('workspace_id', $workspace->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        $definicionPilares = [
            [
                'id' => 1,
                'clave_slug' => 'orden',
                'pilar_principal' => '1. Infraestructura y Seguridad Local (El Eje del Orden)',
                'titulo_corto' => 'El Eje del Orden',
                'subtitulo' => 'Infraestructura, Conectividad y Seguridad Local',
                'icono' => 'Shield',
                'color' => '#06b6d4',
                'color_bg' => 'bg-cyan-500/10 dark:bg-cyan-500/20',
                'color_border' => 'border-cyan-500/30',
                'color_text' => 'text-cyan-600 dark:text-cyan-400',
                'demanda_central' => 'Falta de asfalto, calles inundables, zonas oscuras y prevención del delito.',
                'solucion_propuesta' => 'Pavimentación barrio por barrio, luminarias LED, patrulla y monitoreo urbano.',
                'target_etario' => 'Adultos (30-49) & Adultos Mayores (50+)',
                'prioridad_voto' => 'Orden, tranquilidad y obras tangibles que mejoran la calidad de vida diaria.',
            ],
            [
                'id' => 2,
                'clave_slug' => 'futuro',
                'pilar_principal' => '2. Desarrollo, Empleo y Comercio (El Eje del Futuro)',
                'titulo_corto' => 'El Eje del Futuro',
                'subtitulo' => 'Desarrollo, Empleo, Juventud y Comercio de Barrio',
                'icono' => 'Briefcase',
                'color' => '#10b981',
                'color_bg' => 'bg-emerald-500/10 dark:bg-emerald-500/20',
                'color_border' => 'border-emerald-500/30',
                'color_text' => 'text-emerald-600 dark:text-emerald-400',
                'demanda_central' => 'Desocupación joven, asfixia tributaria a pymes y comercios, y falta de oportunidades.',
                'solucion_propuesta' => 'Burocracia Cero en habilitaciones, exención de tasas municipales y polos tecnológicos.',
                'target_etario' => 'Jóvenes (16-29) & Trabajadores Activos (25-45)',
                'prioridad_voto' => 'Esperanza, trabajo formal, digitalización y dinamismo de la economía local.',
            ],
            [
                'id' => 3,
                'clave_slug' => 'humano',
                'pilar_principal' => '3. Cercanía, Salud y Comunidad (El Eje Humano)',
                'titulo_corto' => 'El Eje Humano',
                'subtitulo' => 'Cercanía Barrial, Salud 24hs, Plazas y Comunidad',
                'icono' => 'HeartHandshake',
                'color' => '#f59e0b',
                'color_bg' => 'bg-amber-500/10 dark:bg-amber-500/20',
                'color_border' => 'border-amber-500/30',
                'color_text' => 'text-amber-600 dark:text-amber-400',
                'demanda_central' => 'Salitas de salud sin médicos ni insumos, plazas abandonadas y lejanía de las autoridades.',
                'solucion_propuesta' => 'Atención 24hs en CAPS, recuperación de espacios verdes y municipio en el barrio.',
                'target_etario' => 'Familias, Mujeres, Niñez y Adultos Mayores',
                'prioridad_voto' => 'Empatía, contención, salud primaria digna y vida en comunidad.',
            ],
        ];

        $totalPostsPeriodo = $publicacionesPeriodo->count();

        // Procesar los 3 Pilares Principales
        $pilaresCalculados = collect($definicionPilares)->map(function ($def) use ($publicacionesPeriodo, $ejesCatalogados, $totalPostsPeriodo) {
            // Ejes temáticos de este pilar
            $subejesDelPilar = $ejesCatalogados->filter(function ($e) use ($def) {
                return $e->pilar_principal === $def['pilar_principal'];
            });
            $subejesIds = $subejesDelPilar->pluck('id')->toArray();

            // Publicaciones de este pilar
            $postsDelPilar = $publicacionesPeriodo->filter(function ($p) use ($subejesIds) {
                return in_array($p->eje_tematico_id, $subejesIds);
            })->values();

            $cantPosts = $postsDelPilar->count();
            $sharePct = $totalPostsPeriodo > 0 ? round(($cantPosts / $totalPostsPeriodo) * 100, 1) : 0;

            $vistasTotales = (int) $postsDelPilar->sum('total_vistas');
            $vistasOrganicas = (int) $postsDelPilar->sum('vistas_organicas');
            $vistasPagadas = (int) $postsDelPilar->sum('vistas_pagadas');

            $likes = (int) $postsDelPilar->sum('total_likes');
            $comentarios = (int) $postsDelPilar->sum('total_comentarios');
            $compartidos = (int) $postsDelPilar->sum('total_compartidos');
            $republicados = (int) $postsDelPilar->sum('total_republicados');
            $totalInteracciones = $likes + $comentarios + $compartidos + $republicados;

            // Score de Impacto Ponderado: Like(1), Comentario(3), Compartido(5), Repost(10)
            $scoreImpacto = ($likes * 1) + ($comentarios * 3) + ($compartidos * 5) + ($republicados * 10);
            $scoreTraccionPromedio = $cantPosts > 0
                ? (int) round($postsDelPilar->avg(fn ($p) => $p->analisis_traccion['score_traccion_indexado'] ?? 50))
                : 50;

            // Reacciones Detalladas y Humor Social
            $reacLikes = (int) $postsDelPilar->sum('me_gusta');
            $reacLove = (int) $postsDelPilar->sum('me_encanta');
            $reacCare = (int) $postsDelPilar->sum('me_importa');
            $reacHaha = (int) $postsDelPilar->sum('me_divierte');
            $reacWow = (int) $postsDelPilar->sum('me_asombra');
            $reacSad = (int) $postsDelPilar->sum('me_entristece');
            $reacAngry = (int) $postsDelPilar->sum('me_enoja');
            $totalReacciones = $reacLikes + $reacLove + $reacCare + $reacHaha + $reacWow + $reacSad + $reacAngry;

            $pctIndignacion = $totalReacciones > 0 ? round(($reacAngry / $totalReacciones) * 100, 1) : 0;
            $alertaCrisis = $pctIndignacion > 15.0;

            $aprobacionNeta = $totalReacciones > 0
                ? round(((($reacLikes + $reacLove + $reacCare) - $reacAngry) / $totalReacciones) * 100, 1)
                : 85.0;

            $humorAvg = $postsDelPilar->whereNotNull('termometro_humor_social')->avg('termometro_humor_social');

            // Formato más frecuente / efectivo
            $formatosGroup = $postsDelPilar->groupBy('tipo_formato')->map->count();
            $formatoDestacado = $formatosGroup->sortDesc()->keys()->first() ?? 'Reel';

            // Desglose de sub-ejes específicos
            $desgloseSubejes = $subejesDelPilar->map(function ($subeje) use ($postsDelPilar, $cantPosts) {
                $postsSub = $postsDelPilar->where('eje_tematico_id', $subeje->id);
                $cSub = $postsSub->count();
                $vSub = (int) $postsSub->sum('total_vistas');
                $scoreSub = (int) $postsSub->sum(function ($p) {
                    return ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10);
                });

                return [
                    'id' => $subeje->id,
                    'nombre' => $subeje->nombre,
                    'slug' => $subeje->slug,
                    'icono' => $subeje->icono,
                    'color_badge' => $subeje->color_badge ?: '#06b6d4',
                    'descripcion' => $subeje->descripcion,
                    'posts_count' => $cSub,
                    'total_vistas' => $vSub,
                    'score_impacto' => $scoreSub,
                    'porcentaje_del_pilar' => $cantPosts > 0 ? round(($cSub / $cantPosts) * 100, 1) : 0,
                ];
            })->values()->sortByDesc('posts_count')->values();

            // Top 3 publicaciones más influyentes del pilar
            $topPublicaciones = $postsDelPilar->sortByDesc(function ($p) {
                return ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10);
            })->take(3)->map(function ($p) {
                return [
                    'id' => $p->id,
                    'plataforma' => $p->plataforma,
                    'texto_extracto' => mb_strimwidth($p->texto_publicacion ?? 'Publicación sin texto', 0, 95, '...'),
                    'media_url' => $p->media_url,
                    'url_post' => $p->url_post,
                    'fecha_publicacion' => $p->fecha_publicacion ? $p->fecha_publicacion->format('d/m/Y') : 'Reciente',
                    'tipo_pauta' => $p->tipo_pauta,
                    'total_vistas' => (int) $p->total_vistas,
                    'total_likes' => (int) $p->total_likes,
                    'total_comentarios' => (int) $p->total_comentarios,
                    'eje_nombre' => $p->ejeTematico?->nombre ?? 'General',
                ];
            })->values();

            return array_merge($def, [
                'posts_count' => $cantPosts,
                'share_porcentaje' => $sharePct,
                'total_vistas' => $vistasTotales,
                'vistas_organicas' => $vistasOrganicas,
                'vistas_pagadas' => $vistasPagadas,
                'total_interacciones' => $totalInteracciones,
                'score_impacto' => $scoreImpacto,
                'score_traccion_promedio' => $scoreTraccionPromedio,
                'reacciones' => [
                    'me_gusta' => $reacLikes,
                    'me_encanta' => $reacLove,
                    'me_importa' => $reacCare,
                    'me_divierte' => $reacHaha,
                    'me_asombra' => $reacWow,
                    'me_entristece' => $reacSad,
                    'me_enoja' => $reacAngry,
                    'total' => $totalReacciones,
                ],
                'pct_indignacion' => $pctIndignacion,
                'alerta_crisis' => $alertaCrisis,
                'aprobacion_neta' => $aprobacionNeta,
                'humor_promedio' => $humorAvg ? round($humorAvg, 1) : 4.5,
                'formato_destacado' => $formatoDestacado,
                'subejes' => $desgloseSubejes,
                'top_publicaciones' => $topPublicaciones,
            ]);
        });

        // ─────────────────────────────────────────────────────────────
        // 4. BLOQUE SECUNDARIO: GESTIÓN INSTITUCIONAL & OTROS
        // ─────────────────────────────────────────────────────────────
        $subejesInstitucionales = $ejesCatalogados->filter(function ($e) {
            return $e->pilar_principal === 'Gestión Institucional & Otros' || ! $e->es_eje_campana;
        });
        $subejesInstIds = $subejesInstitucionales->pluck('id')->toArray();

        // También publicaciones sin eje temático asignado caen en institucional/otros
        $postsInstitucionales = $publicacionesPeriodo->filter(function ($p) use ($subejesInstIds) {
            return in_array($p->eje_tematico_id, $subejesInstIds) || empty($p->eje_tematico_id);
        })->values();

        $cantPostsInst = $postsInstitucionales->count();
        $sharePctInst = $totalPostsPeriodo > 0 ? round(($cantPostsInst / $totalPostsPeriodo) * 100, 1) : 0;
        $vistasInst = (int) $postsInstitucionales->sum('total_vistas');
        $intInst = (int) $postsInstitucionales->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + ((int) ($p->total_republicados ?? 0)));

        $bloqueInstitucional = [
            'titulo' => 'Gestión Institucional & Actos de Gobierno',
            'subtitulo' => 'Trámites, convenios, protocolo y publicaciones fuera de los 3 ejes de campaña',
            'posts_count' => $cantPostsInst,
            'share_porcentaje' => $sharePctInst,
            'total_vistas' => $vistasInst,
            'total_interacciones' => $intInst,
            'subejes' => $subejesInstitucionales->map(function ($sub) use ($postsInstitucionales) {
                return [
                    'id' => $sub->id,
                    'nombre' => $sub->nombre,
                    'icono' => $sub->icono,
                    'color_badge' => '#64748b',
                    'posts_count' => $postsInstitucionales->where('eje_tematico_id', $sub->id)->count(),
                ];
            })->values(),
        ];

        // ─────────────────────────────────────────────────────────────
        // 5. DIAGNÓSTICO ESTRATÉGICO & BALANCE DISCURSIVO
        // ─────────────────────────────────────────────────────────────
        $postsEnTresPilares = $pilaresCalculados->sum('posts_count');
        $pctEnPilares = $totalPostsPeriodo > 0 ? round(($postsEnTresPilares / $totalPostsPeriodo) * 100, 1) : 0;

        $pilarMenor = $pilaresCalculados->sortBy('share_porcentaje')->first();
        $pilarMayor = $pilaresCalculados->sortByDesc('share_porcentaje')->first();

        $diagnosticoTexto = '';
        $tipoAlerta = 'success';

        if ($totalPostsPeriodo === 0) {
            $diagnosticoTexto = 'No hay publicaciones registradas para el período seleccionado. Carga contenidos para activar la auditoría.';
            $tipoAlerta = 'info';
        } elseif ($sharePctInst > 30.0) {
            $diagnosticoTexto = "⚠️ Alerta de Dilución: El {$sharePctInst}% del contenido está en temas de protocolo o gestión institucional. Se aconseja reenfocar la comunicación en los 3 ejes ganadores.";
            $tipoAlerta = 'warning';
        } elseif ($pilarMenor && $pilarMenor['share_porcentaje'] < 15.0) {
            $diagnosticoTexto = "⚡ Desbalance de Agenda: {$pilarMenor['titulo_corto']} solo tiene el {$pilarMenor['share_porcentaje']}% de cobertura. Conviene reforzar propuestas en este pilar para no descuidar su segmento electoral.";
            $tipoAlerta = 'warning';
        } elseif ($pilaresCalculados->contains('alerta_crisis', true)) {
            $pilarCrisis = $pilaresCalculados->firstWhere('alerta_crisis', true);
            $diagnosticoTexto = "🚨 Alerta Roja de Rechazo: {$pilarCrisis['titulo_corto']} supera el 15% de indignación ({$pilarCrisis['pct_indignacion']}%). Revisar tono y comentarios del debate ciudadano.";
            $tipoAlerta = 'danger';
        } else {
            $diagnosticoTexto = "🎯 Campaña Focalizada: El {$pctEnPilares}% de la comunicación está alineada a los 3 Ejes Principales con excelente equilibrio discursivo.";
            $tipoAlerta = 'success';
        }

        $balanceDiscursivo = [
            'total_posts' => $totalPostsPeriodo,
            'posts_en_pilares' => $postsEnTresPilares,
            'posts_institucionales' => $cantPostsInst,
            'pct_en_pilares' => $pctEnPilares,
            'pct_institucional' => $sharePctInst,
            'diagnostico' => $diagnosticoTexto,
            'tipo_alerta' => $tipoAlerta,
            'pilar_lider' => $pilarMayor ? $pilarMayor['titulo_corto'] : null,
            'pilar_a_reforzar' => $pilarMenor ? $pilarMenor['titulo_corto'] : null,
        ];

        // ─────────────────────────────────────────────────────────────
        // 6. EVOLUCIÓN HISTÓRICA TIME-SERIES MENSUAL POR PILAR
        // ─────────────────────────────────────────────────────────────
        $mesesTimeline = $gruposPorMes->keys()->sort()->values();
        $evolucionHistorica = $mesesTimeline->map(function ($ym) use ($gruposPorMes, $mesesCortos, $ejesCatalogados) {
            $postsMes = $gruposPorMes->get($ym, collect());
            $partes = explode('-', $ym);
            $ano = $partes[0] ?? date('Y');
            $mesInt = (int) ($partes[1] ?? 1);
            $etiqueta = ($mesesCortos[$mesInt] ?? $ym) . ' ' . substr($ano, 2, 2);

            $postsOrden = $postsMes->filter(fn ($p) => $p->ejeTematico?->pilar_principal === '1. Infraestructura y Seguridad Local (El Eje del Orden)')->count();
            $postsFuturo = $postsMes->filter(fn ($p) => $p->ejeTematico?->pilar_principal === '2. Desarrollo, Empleo y Comercio (El Eje del Futuro)')->count();
            $postsHumano = $postsMes->filter(fn ($p) => $p->ejeTematico?->pilar_principal === '3. Cercanía, Salud y Comunidad (El Eje Humano)')->count();
            $postsInst = $postsMes->filter(fn ($p) => $p->ejeTematico?->pilar_principal === 'Gestión Institucional & Otros' || empty($p->eje_tematico_id))->count();

            return [
                'clave_mes' => $ym,
                'etiqueta' => $etiqueta,
                'orden' => $postsOrden,
                'futuro' => $postsFuturo,
                'humano' => $postsHumano,
                'institucional' => $postsInst,
                'total' => $postsMes->count(),
            ];
        })->values();

        // ─────────────────────────────────────────────────────────────
        // 7. CONTEXTO TERRITORIAL & DEMOGRÁFICO
        // ─────────────────────────────────────────────────────────────
        $territorio = $candidatoPropio->territorio ?: $workspace->territorios()->first();
        $territorioData = $territorio ? [
            'id' => $territorio->id,
            'nombre' => $territorio->nombre,
            'tipo' => $territorio->tipo,
            'padron_electoral' => (int) $territorio->padron_electoral,
            'poblacion_total' => (int) $territorio->poblacion_total,
            'piramide_etaria' => $territorio->piramide_etaria,
        ] : null;

        // ─────────────────────────────────────────────────────────────
        // 8. NOTAS DE PRENSA (CLIPPING) EN EL PERÍODO
        // ─────────────────────────────────────────────────────────────
        $notasQuery = NotaPrensa::where('workspace_id', $workspace->id)
            ->where('candidato_mencionado_id', $candidatoPropio->id)
            ->latest('fecha_publicacion');

        if ($periodoSeleccionado !== 'todos') {
            $notasQuery->whereRaw("strftime('%Y-%m', fecha_publicacion) = ?", [$periodoSeleccionado]);
        }
        $notasPrensa = $notasQuery->take(5)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'titulo' => $n->titulo,
                'medio_nombre' => $n->medioPrensa?->nombre ?? 'Medio Digital',
                'tono_editorial' => $n->tono_editorial,
                'sentimiento' => $n->sentimiento,
                'fecha' => $n->fecha_publicacion ? $n->fecha_publicacion->format('d/m/Y') : 'Reciente',
                'url_nota' => $n->url_nota,
            ];
        });

        return Inertia::render('Ejes/Index', [
            'candidato' => [
                'id' => $candidatoPropio->id,
                'nombre_completo' => $candidatoPropio->nombre_completo,
                'cargo_postula' => $candidatoPropio->cargo_postula,
                'partido_coalicion' => $candidatoPropio->partido_coalicion,
                'foto_url' => $candidatoPropio->foto_url,
            ],
            'periodo_seleccionado' => $periodoSeleccionado,
            'periodos_disponibles' => $periodosDisponibles,
            'pilares' => $pilaresCalculados,
            'bloque_institucional' => $bloqueInstitucional,
            'balance_discursivo' => $balanceDiscursivo,
            'evolucion_historica' => $evolucionHistorica,
            'territorio' => $territorioData,
            'notas_prensa' => $notasPrensa,
        ]);
    }
}
