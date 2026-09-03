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

        $ejes = \App\Models\EjeTematico::where('workspace_id', $workspace->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        // Métricas directas del Perfil del Candidato (Comunidad Bruta)
        $totalSeguidores = (int) $candidato->perfilesSociales->sum('seguidores_actuales');
        $totalSeguidoresPuntoCero = (int) $candidato->perfilesSociales->sum('seguidores_punto_cero');
        $crecimientoNetoTotalSeguidores = $totalSeguidores - $totalSeguidoresPuntoCero;
        $crecimientoPctTotalSeguidores = $totalSeguidoresPuntoCero > 0
            ? round(($crecimientoNetoTotalSeguidores / $totalSeguidoresPuntoCero) * 100, 1)
            : 0;

        // Métricas directas de Publicaciones
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

        // Score de Impacto calculado SOLO sobre posts orgánicos puros (sin pauta)
        $postsOrganicos = $publicaciones->filter(fn ($p) => ! in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION));
        $scoreImpactoOrganicoPuro = (int) $postsOrganicos->sum(function ($p) {
            return ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10);
        });

        // ─────────────────────────────────────────────────────────────
        // NORMALIZACIÓN DEL SCORE DE IMPACTO (PROMEDIOS Y TEMPORALIDAD)
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

        // Agrupación mensual cronológica de publicaciones
        $publicacionesCronologicas = $publicaciones->sortBy('fecha_publicacion');
        $gruposPorMes = $publicacionesCronologicas->groupBy(function ($p) {
            return $p->fecha_publicacion ? $p->fecha_publicacion->format('Y-m') : 'sin_fecha';
        })->reject(fn ($posts, $key) => $key === 'sin_fecha');

        $desgloseMensual = $gruposPorMes->map(function ($postsMes, $keyYm) use ($mesesEspanol, $mesesCortos) {
            $partes = explode('-', $keyYm);
            $ano = $partes[0] ?? date('Y');
            $mesInt = (int) ($partes[1] ?? 1);
            $nombreMes = ($mesesEspanol[$mesInt] ?? $keyYm) . " {$ano}";
            $cortoMes = $mesesCortos[$mesInt] ?? $keyYm;

            $cantPosts = $postsMes->count();
            $scoreMes = (int) $postsMes->sum(function ($p) {
                return ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10);
            });
            $promedioPorPostMes = $cantPosts > 0 ? (int) round($scoreMes / $cantPosts) : 0;
            $vistasMes = (int) $postsMes->sum('total_vistas');
            $likesMes = (int) $postsMes->sum('total_likes');
            $comentariosMes = (int) $postsMes->sum('total_comentarios');
            $compartidosMes = (int) $postsMes->sum('total_compartidos');
            $republicadosMes = (int) $postsMes->sum('total_republicados');

            return [
                'clave_mes' => $keyYm,
                'nombre_mes' => $nombreMes,
                'mes_corto' => $cortoMes,
                'ano' => (int) $ano,
                'total_posts' => $cantPosts,
                'score_total' => $scoreMes,
                'score_promedio_post' => $promedioPorPostMes,
                'total_vistas' => $vistasMes,
                'total_interacciones' => $likesMes + $comentariosMes + $compartidosMes + $republicadosMes,
            ];
        })->values();

        $mesesActivos = max(1, $desgloseMensual->count());
        $scorePromedioPorPost = $totalPosts > 0 ? (int) round($scoreImpactoTotal / $totalPosts) : 0;
        $scorePromedioMensual = (int) round($scoreImpactoTotal / $mesesActivos);

        // Días de campaña transcurridos entre primer y último post
        $fechaPrimera = $publicacionesCronologicas->first()?->fecha_publicacion;
        $fechaUltima = $publicacionesCronologicas->last()?->fecha_publicacion ?? \Carbon\Carbon::now();
        $diasCampanaActiva = $fechaPrimera ? max(1, (int) $fechaPrimera->diffInDays($fechaUltima) + 1) : 1;
        $scorePromedioDiario = (int) round($scoreImpactoTotal / $diasCampanaActiva);

        // Comparativa de tendencia: buscar el mes en curso (o el más reciente hasta hoy) y su mes previo
        $mesActualYm = \Carbon\Carbon::now()->format('Y-m');
        $mesActualItem = $desgloseMensual->firstWhere('clave_mes', $mesActualYm);

        if (! $mesActualItem) {
            // Si el mes en curso no tiene posts, tomar el último mes registrado hasta hoy
            $mesActualItem = $desgloseMensual->filter(fn ($m) => $m['clave_mes'] <= $mesActualYm)->last() ?? $desgloseMensual->last();
        }

        $tendenciaScoreMes = null;
        if ($mesActualItem) {
            $idxActual = $desgloseMensual->search(fn ($m) => $m['clave_mes'] === $mesActualItem['clave_mes']);
            if ($idxActual !== false && $idxActual > 0) {
                $mesAnterior = $desgloseMensual[$idxActual - 1];
                // Tendencia de Volumen de Puntos Totales del Mes (Agosto vs Julio)
                $diffScoreTotal = $mesActualItem['score_total'] - $mesAnterior['score_total'];
                $diffPct = $mesAnterior['score_total'] > 0
                    ? round(($diffScoreTotal / $mesAnterior['score_total']) * 100, 1)
                    : 0;

                $tendenciaScoreMes = [
                    'mes_actual' => $mesActualItem['mes_corto'],
                    'mes_anterior' => $mesAnterior['mes_corto'],
                    'score_actual' => $mesActualItem['score_total'],
                    'score_anterior' => $mesAnterior['score_total'],
                    'variacion_pts' => $diffScoreTotal,
                    'variacion_pct' => $diffPct,
                ];
            }
        }

        // ─────────────────────────────────────────────────────────────
        // DESDUPLICACIÓN DE AUDIENCIA POR TIERS (SOLO ELEMENTOS ACTIVOS)
        // ─────────────────────────────────────────────────────────────
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
                // Tier 1: Red Dominante Activa (100% base única)
                $factorIncremental = 1.0;
                $tierNombre = 'Tier 1 (Red Principal Activa)';
            } else {
                $esMeta = in_array($plataforma, ['facebook', 'instagram', 'threads']);
                $tieneMetaPrevia = count(array_intersect($plataformasProcesadas, ['facebook', 'instagram', 'threads'])) > 0;

                if ($esMeta && $tieneMetaPrevia) {
                    // Solapamiento cruzado dentro de Meta (~65% solapado -> 35% personas únicas adicionales)
                    $factorIncremental = 0.35;
                    $tierNombre = "Tier {$tierNumero} (Meta / Solapado)";
                } else {
                    // Plataformas fuera de Meta (TikTok, X, YouTube) aportan mayor novedad
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

        if ($seguidoresNetosEstimados <= 0) {
            $seguidoresNetosEstimados = $totalSeguidores;
        }

        $engagementRate = $totalVistas > 0
            ? round(($interaccionesTotales / $totalVistas) * 100, 2)
            : ($seguidoresNetosEstimados > 0 ? round(($interaccionesTotales / $seguidoresNetosEstimados) * 100, 2) : 0);

        $vistasPromedioPorPost = $totalPosts > 0 ? (int) round($totalVistas / $totalPosts) : 0;

        // Diagnóstico cualitativo de Engagement Rate
        if ($engagementRate >= 5.0) {
            $engagementCalidadTexto = 'Alto Involucramiento';
        } elseif ($engagementRate >= 2.5) {
            $engagementCalidadTexto = 'Sólido';
        } else {
            $engagementCalidadTexto = 'Moderado';
        }

        $humorPromedio = $publicaciones->whereNotNull('termometro_humor_social')->avg('termometro_humor_social');
        $humorPromedioRaw = $humorPromedio ? round((float) $humorPromedio, 1) : 5.0;
        $humorPromedioFormateado = number_format($humorPromedioRaw, 1);

        if ($humorPromedioRaw >= 4.5) {
            $humorClimaTexto = 'Muy Favorable';
            $humorClimaEstado = 'excelente';
        } elseif ($humorPromedioRaw >= 3.5) {
            $humorClimaTexto = 'Favorable';
            $humorClimaEstado = 'bueno';
        } elseif ($humorPromedioRaw >= 2.5) {
            $humorClimaTexto = 'Moderado';
            $humorClimaEstado = 'alerta';
        } else {
            $humorClimaTexto = 'Crítico';
            $humorClimaEstado = 'crisis';
        }

        // Ratio de Penetración Territorial sobre el Padrón (Neto Real vs Bruto)
        $padronElectoral = (int) ($candidato->padron_electoral ?: ($candidato->territorio?->padron_electoral ?? 0));
        $ratioPenetracionNeta = $padronElectoral > 0
            ? round(($seguidoresNetosEstimados / $padronElectoral) * 100, 1)
            : 0;
        $ratioPenetracionBruta = $padronElectoral > 0
            ? round(($totalSeguidores / $padronElectoral) * 100, 1)
            : 0;

        // Meta de Score de Impacto Total: Anclada a la audiencia real deduplicada por Tiers (cross-platform)
        $factorEngagementObjetivo = 0.5;
        $scoreImpactoMeta = (int) max(500, round($seguidoresNetosEstimados * $factorEngagementObjetivo));

        // ─────────────────────────────────────────────────────────────
        // META DE SCORE PROMEDIO POR POST (BENCHMARK TERRITORIAL PROPORCIONAL)
        // ─────────────────────────────────────────────────────────────
        // Escala proporcional y universal al padrón electoral del candidato:
        if ($padronElectoral > 0) {
            if ($padronElectoral <= 50000) {
                // Distritos municipales (hasta 50k): 0.5% del padrón (ej. 21.000 -> 105 pts)
                $factorPadron = 0.005;
                $pctTexto = '0.5% del padrón';
            } elseif ($padronElectoral <= 200000) {
                // Distritos medianos (50k a 200k): 0.4% del padrón (ej. 100.000 -> 400 pts)
                $factorPadron = 0.004;
                $pctTexto = '0.4% del padrón';
            } else {
                // Distritos grandes o provinciales (>200k): 0.25% del padrón (ej. 600.000 -> 1.500 pts)
                $factorPadron = 0.0025;
                $pctTexto = '0.25% del padrón';
            }

            $scorePromedioPostMeta = (int) max(20, round($padronElectoral * $factorPadron));
            $origenMetaScore = 'padron';
            $metaScoreBaseTexto = $pctTexto;
        } else {
            // Fallback inteligente si no hay padrón cargado: 3% de la comunidad de seguidores
            $scorePromedioPostMeta = (int) max(20, round($seguidoresNetosEstimados * 0.03));
            $origenMetaScore = 'seguidores';
            $metaScoreBaseTexto = '3% de seguidores';
        }

        $scorePromedioPostPct = $scorePromedioPostMeta > 0
            ? round(($scorePromedioPorPost / $scorePromedioPostMeta) * 100, 1)
            : 0;

        if ($scorePromedioPostPct >= 100) {
            $scorePromedioPostEstado = 'exitoso';
            $scorePromedioPostEstadoTexto = 'Exitoso';
        } elseif ($scorePromedioPostPct >= 60) {
            $scorePromedioPostEstado = 'solido';
            $scorePromedioPostEstadoTexto = 'Sólido';
        } else {
            $scorePromedioPostEstado = 'bajo';
            $scorePromedioPostEstadoTexto = 'Por mejorar';
        }

        // ─────────────────────────────────────────────────────────────
        // META DE SCORE PROMEDIO MENSUAL (BENCHMARK TERRITORIAL PROPORCIONAL)
        // ─────────────────────────────────────────────────────────────
        if ($padronElectoral > 0) {
            if ($padronElectoral <= 50000) {
                // Distritos municipales: 10% del padrón en impacto acumulado mensual (ej. 24.500 -> 2.450 pts)
                $factorPadronMensual = 0.10;
                $pctTextoMensual = '10% del padrón';
            } elseif ($padronElectoral <= 200000) {
                // Distritos medianos: 6% del padrón (ej. 100.000 -> 6.000 pts)
                $factorPadronMensual = 0.06;
                $pctTextoMensual = '6% del padrón';
            } else {
                // Distritos grandes o provinciales: 3% del padrón (ej. 600.000 -> 18.000 pts)
                $factorPadronMensual = 0.03;
                $pctTextoMensual = '3% del padrón';
            }

            $scorePromedioMensualMeta = (int) max(100, round($padronElectoral * $factorPadronMensual));
            $origenMetaMensual = 'padron';
            $metaMensualBaseTexto = $pctTextoMensual;
        } else {
            // Fallback si no hay padrón: 30% de la comunidad neta de seguidores
            $scorePromedioMensualMeta = (int) max(100, round($seguidoresNetosEstimados * 0.30));
            $origenMetaMensual = 'seguidores';
            $metaMensualBaseTexto = '30% de seguidores';
        }

        $scorePromedioMensualPct = $scorePromedioMensualMeta > 0
            ? round(($scorePromedioMensual / $scorePromedioMensualMeta) * 100, 1)
            : 0;

        if ($scorePromedioMensualPct >= 100) {
            $scorePromedioMensualEstado = 'exitoso';
            $scorePromedioMensualEstadoTexto = 'Exitoso';
        } elseif ($scorePromedioMensualPct >= 60) {
            $scorePromedioMensualEstado = 'solido';
            $scorePromedioMensualEstadoTexto = 'Sólido';
        } else {
            $scorePromedioMensualEstado = 'bajo';
            $scorePromedioMensualEstadoTexto = 'Por mejorar';
        }

        // ─────────────────────────────────────────────────────────────
        // RÉCORD HISTÓRICO MENSUAL Y META TOTAL DE CAMPAÑA (PADRÓN)
        // ─────────────────────────────────────────────────────────────
        // Mejor mes histórico de la campaña
        $mejorMesItem = $desgloseMensual->sortByDesc('score_total')->first();
        $recordMensualScore = $mejorMesItem ? (int) $mejorMesItem['score_total'] : $scoreImpactoTotal;
        $recordMensualNombre = $mejorMesItem ? $mejorMesItem['mes_corto'] . ' ' . $mejorMesItem['ano'] : '';
        $recordMensualCorto = $mejorMesItem ? $mejorMesItem['mes_corto'] : '';

        // Meta Total de Campaña: Presión Electoral Total (100% del Padrón Electoral)
        if ($padronElectoral > 0) {
            $metaScoreCampana = $padronElectoral;
            $metaCampanaBaseTexto = '100% del padrón electoral';
        } else {
            $metaScoreCampana = (int) max(1000, round($seguidoresNetosEstimados * 2.0));
            $metaCampanaBaseTexto = '2x comunidad de seguidores';
        }

        $avanceCampanaPadronPct = $metaScoreCampana > 0
            ? round(($scoreImpactoTotal / $metaScoreCampana) * 100, 1)
            : 0;

        // Contexto de padrón: qué % del universo electoral cubre la audiencia neta actual
        $pctPadronCubiertoPorTiers = $padronElectoral > 0
            ? round(($seguidoresNetosEstimados / $padronElectoral) * 100, 1)
            : 0;

        $scoreImpactoBaseTexto = number_format($seguidoresNetosEstimados, 0, ',', '.')
            . ' únicos netos (Tiers) × ' . $factorEngagementObjetivo
            . ' — cubre ' . $pctPadronCubiertoPorTiers . '% del padrón';

        $scoreImpactoPct = $scoreImpactoMeta > 0
            ? round(($scoreImpactoTotal / $scoreImpactoMeta) * 100, 1)
            : 0;

        // Semáforo ajustado: el umbral de "Óptimo" exige alcanzar el 100% de la meta basada en audiencia real
        if ($scoreImpactoPct >= 100) {
            $scoreImpactoEstado = 'optimo';
            $scoreImpactoEstadoTexto = 'Óptimo';
        } elseif ($scoreImpactoPct >= 60) {
            $scoreImpactoEstado = 'mantenimiento';
            $scoreImpactoEstadoTexto = 'Mantenimiento';
        } elseif ($scoreImpactoPct >= 35) {
            $scoreImpactoEstado = 'creciendo';
            $scoreImpactoEstadoTexto = 'Creciendo';
        } else {
            $scoreImpactoEstado = 'frio';
            $scoreImpactoEstadoTexto = 'Bajo impacto';
        }

        // 1. Desglose por Red Social del Candidato con Métricas y Enlace (7 Plataformas Oficiales)
        $plataformasColores = [
            'instagram' => '#E4405F',
            'facebook' => '#1877F2',
            'threads' => '#000000',
            'tiktok' => '#00F2FE',
            'x_twitter' => '#000000',
            'youtube' => '#FF0000',
            'linkedin' => '#0A66C2',
        ];

        $plataformasOrden = ['instagram', 'facebook', 'threads', 'tiktok', 'x_twitter', 'youtube', 'linkedin'];
        $now = \Carbon\Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $perfilesPorPlataforma = $candidato->perfilesSociales->keyBy('plataforma');

        $redesDesglose = collect($plataformasOrden)->map(function ($plat) use ($perfilesPorPlataforma, $publicaciones, $startOfMonth, $plataformasColores) {
            $perfil = $perfilesPorPlataforma->get($plat);
            $tieneHandle = $perfil && (!empty(trim($perfil->handle_usuario ?? '')) || !empty(trim($perfil->url_perfil ?? '')));

            if ($perfil && $tieneHandle) {
                $postsRed = $publicaciones->where('perfil_social_id', $perfil->id);
                $cantPosts = $postsRed->count();
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

                $erRed = ($seguidoresActuales > 0 && $cantPosts > 0)
                    ? round((($intRed / $cantPosts) / $seguidoresActuales) * 100, 2)
                    : 0;

                $estaActivo = (bool) $perfil->esta_activo;
                $estaVerificado = (bool) $perfil->esta_verificado;

                if ($estaVerificado) {
                    $estado = 'verificado';
                    $colorEstado = 'azul';
                    $estadoTexto = 'Verificada';
                } elseif ($estaActivo && ($cantPosts > 0 || $seguidoresActuales > 0)) {
                    $estado = 'activo';
                    $colorEstado = 'verde';
                    $estadoTexto = 'Activa';
                } else {
                    $estado = 'inactivo';
                    $colorEstado = 'rojo';
                    $estadoTexto = 'Inactiva';
                }

                return [
                    'id' => $perfil->id,
                    'plataforma' => $perfil->plataforma,
                    'handle_usuario' => $perfil->handle_usuario,
                    'url_perfil' => $perfil->url_perfil,
                    'esta_activo' => $estaActivo,
                    'esta_verificado' => $estaVerificado,
                    'estado' => $estado,
                    'color_estado' => $colorEstado,
                    'estado_texto' => $estadoTexto,
                    'seguidores' => $seguidoresActuales,
                    'seguidores_punto_cero' => $seguidoresPuntoCero,
                    'crecimiento_neto_seguidores' => $crecimientoSeguidores,
                    'publicaciones_count' => $cantPosts,
                    'publicaciones_mes' => $postsMes,
                    'vistas_acumuladas' => $vistasRed,
                    'likes_acumulados' => $likesRed,
                    'comentarios_acumulados' => $comentariosRed,
                    'interacciones_acumuladas' => $intRed,
                    'tasa_engagement' => $erRed,
                    'color' => $plataformasColores[$plat] ?? '#06b6d4',
                ];
            }

            return [
                'id' => $perfil?->id,
                'plataforma' => $plat,
                'handle_usuario' => null,
                'url_perfil' => null,
                'esta_activo' => false,
                'esta_verificado' => false,
                'estado' => 'sin_configurar',
                'color_estado' => 'gris',
                'estado_texto' => 'Configurar',
                'seguidores' => 0,
                'seguidores_punto_cero' => 0,
                'crecimiento_neto_seguidores' => 0,
                'publicaciones_count' => 0,
                'publicaciones_mes' => 0,
                'vistas_acumuladas' => 0,
                'likes_acumulados' => 0,
                'comentarios_acumulados' => 0,
                'interacciones_acumuladas' => 0,
                'tasa_engagement' => 0,
                'color' => $plataformasColores[$plat] ?? '#06b6d4',
            ];
        })->values();

        // 2. Gráfico Donut: Distribución de Interacciones por Red Social (solo redes activas/configuradas ordenadas por cuota)
        $redesActivasInteraccion = $redesDesglose->filter(function ($red) {
            return (bool) $red['esta_activo'] || (int) $red['interacciones_acumuladas'] > 0 || ! empty($red['handle_usuario']);
        })->sortByDesc('interacciones_acumuladas')->values();

        // Fallback si no hay perfiles activos aún
        if ($redesActivasInteraccion->isEmpty()) {
            $redesActivasInteraccion = $redesDesglose->take(3);
        }

        $distribucionPlataformas = $redesActivasInteraccion->map(function ($red) use ($interaccionesTotales) {
            $pct = $interaccionesTotales > 0
                ? round(($red['interacciones_acumuladas'] / $interaccionesTotales) * 100, 1)
                : 0;

            return [
                'plataforma' => $red['plataforma'],
                'nombre' => ucfirst(str_replace('_', ' ', $red['plataforma'])),
                'handle' => $red['handle_usuario'],
                'interacciones' => (int) $red['interacciones_acumuladas'],
                'porcentaje' => $pct,
                'color' => $red['color'],
            ];
        })->values();

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

        // 3.B. Matriz Cruzada: Formato utilizado por cada Red Social Activa
        $todosFormatos = $publicaciones->pluck('tipo_formato')->filter()->unique()->values();
        $formatosPorRed = $candidato->perfilesSociales
            ->filter(fn ($p) => (bool) $p->esta_activo || (int) $p->seguidores_actuales > 0 || $publicaciones->where('perfil_social_id', $p->id)->count() > 0)
            ->map(function ($perfil) use ($publicaciones, $todosFormatos) {
                $postsRed = $publicaciones->where('perfil_social_id', $perfil->id);
                $desglose = [];
                foreach ($todosFormatos as $fmt) {
                    $postsFmt = $postsRed->where('tipo_formato', $fmt);
                    $cant = $postsFmt->count();
                    if ($cant > 0) {
                        $vistas = (int) $postsFmt->sum('total_vistas');
                        $score = (int) $postsFmt->sum(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10));
                        $desglose[] = [
                            'formato' => $fmt,
                            'cantidad' => $cant,
                            'vistas' => $vistas,
                            'score' => $score,
                        ];
                    }
                }
                usort($desglose, fn ($a, $b) => $b['cantidad'] <=> $a['cantidad']);
                $formatoTop = ! empty($desglose) ? $desglose[0]['formato'] : 'Sin publicaciones';

                return [
                    'id' => $perfil->id,
                    'plataforma' => $perfil->plataforma,
                    'handle_usuario' => $perfil->handle_usuario,
                    'url_perfil' => $perfil->url_perfil,
                    'total_posts' => $postsRed->count(),
                    'total_vistas' => (int) $postsRed->sum('total_vistas'),
                    'total_score' => (int) $postsRed->sum(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10)),
                    'formato_top' => $formatoTop,
                    'formatos' => $desglose,
                ];
            })->values();

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
                'pilar_principal' => $eje->pilar_principal,
                'nombre' => $eje->nombre,
                'color_badge' => $eje->color_badge ?: '#06b6d4',
                'icono' => $eje->icono,
                'posts_count' => $postsEje->count(),
                'total_vistas' => $vistas,
                'total_interacciones' => $intTotal,
                'humor_promedio' => $humor ? round($humor, 1) : 4.5,
            ];
        })->filter(fn ($e) => $e['posts_count'] > 0)->values()->sortByDesc('total_interacciones')->values();

        // 5. Histórico Consolidado Time-Series (Evolución sin caídas artificiales con Forward-Fill)
        $perfilesActivos = $candidato->perfilesSociales->filter(fn ($p) => (bool) $p->esta_activo || (int) $p->seguidores_actuales > 0);
        $perfilesIds = $perfilesActivos->pluck('id');
        $medicionesHistoricas = \App\Models\PerfilSocialMetrica::whereIn('perfil_social_id', $perfilesIds)
            ->orderBy('fecha', 'asc')
            ->get();

        $fechasUnicas = $medicionesHistoricas->pluck('fecha')
            ->filter()
            ->map(fn ($f) => $f->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        $medicionesPorPerfil = $medicionesHistoricas->groupBy('perfil_social_id');

        $seriesPorRed = [];
        $historicoAgrupado = [];
        $ultimoValorSeguidores = [];
        $ultimoValorVistas = [];
        $ultimoValorInteracciones = [];

        foreach ($perfilesActivos as $perfil) {
            $ultimoValorSeguidores[$perfil->id] = (int) ($perfil->seguidores_punto_cero ?: $perfil->seguidores_actuales);
            $ultimoValorVistas[$perfil->id] = 0;
            $ultimoValorInteracciones[$perfil->id] = 0;
            $seriesPorRed[$perfil->plataforma] = [
                'plataforma' => $perfil->plataforma,
                'nombre' => ucfirst(str_replace('_', ' ', $perfil->plataforma)),
                'handle' => $perfil->handle_usuario,
                'color' => $plataformasColores[$perfil->plataforma] ?? '#06b6d4',
                'puntos' => [],
            ];
        }

        if ($fechasUnicas->count() >= 2) {
            foreach ($fechasUnicas as $fechaStr) {
                $fechaLabel = date('d/m', strtotime($fechaStr));
                $totalSeguidoresDia = 0;
                $totalVistasDia = 0;
                $totalInteraccionesDia = 0;

                // Publicaciones acumuladas hasta esta fecha
                $postsHastaFecha = $publicaciones->filter(function ($p) use ($fechaStr) {
                    return $p->fecha_publicacion && $p->fecha_publicacion->format('Y-m-d') <= $fechaStr;
                });
                $totalPuntosDia = (int) $postsHastaFecha->sum(function ($p) {
                    return ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10);
                });
                $vistasPostsHastaFecha = (int) $postsHastaFecha->sum('total_vistas');

                foreach ($perfilesActivos as $perfil) {
                    $pid = $perfil->id;
                    $medsPerfil = $medicionesPorPerfil->get($pid, collect());
                    $medFecha = $medsPerfil->first(fn ($m) => $m->fecha && $m->fecha->format('Y-m-d') === $fechaStr);

                    if ($medFecha) {
                        $ultimoValorSeguidores[$pid] = (int) $medFecha->seguidores;
                        $ultimoValorVistas[$pid] = (int) $medFecha->visualizaciones_totales;
                        $ultimoValorInteracciones[$pid] = (int) ($medFecha->interacciones_totales ?? $medFecha->me_gusta_totales ?? 0);
                    }

                    $postsRedHastaFecha = $postsHastaFecha->where('perfil_social_id', $pid);
                    $scoreRed = (int) $postsRedHastaFecha->sum(function ($p) {
                        return ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10);
                    });
                    $vistasRed = (int) $postsRedHastaFecha->sum('total_vistas');

                    $seriesPorRed[$perfil->plataforma]['puntos'][] = [
                        'fecha' => $fechaLabel,
                        'fecha_raw' => $fechaStr,
                        'seguidores' => $ultimoValorSeguidores[$pid],
                        'vistas' => $ultimoValorVistas[$pid] > 0 ? $ultimoValorVistas[$pid] : $vistasRed,
                        'interacciones' => $ultimoValorInteracciones[$pid] > 0 ? $ultimoValorInteracciones[$pid] : $scoreRed,
                        'puntos' => $scoreRed,
                    ];

                    $totalSeguidoresDia += $ultimoValorSeguidores[$pid];
                    $totalVistasDia += $ultimoValorVistas[$pid] > 0 ? $ultimoValorVistas[$pid] : $vistasRed;
                    $totalInteraccionesDia += $ultimoValorInteracciones[$pid] > 0 ? $ultimoValorInteracciones[$pid] : $scoreRed;
                }

                $historicoAgrupado[] = [
                    'fecha' => $fechaLabel,
                    'fecha_raw' => $fechaStr,
                    'seguidores' => $totalSeguidoresDia,
                    'vistas' => $totalVistasDia > 0 ? $totalVistasDia : $vistasPostsHastaFecha,
                    'interacciones' => $totalInteraccionesDia,
                    'puntos' => $totalPuntosDia,
                ];
            }
        } else {
            // Progresión continua de fallback si hay menos de 2 mediciones registradas
            $diasMuestra = 7;
            $historicoAgrupado = [];
            for ($i = $diasMuestra - 1; $i >= 0; $i--) {
                $f = $now->copy()->subDays($i);
                $prog = ($diasMuestra - $i) / $diasMuestra;
                $segStep = (int) ($totalSeguidoresPuntoCero + ($crecimientoNetoTotalSeguidores * $prog));
                $vistasStep = (int) ($totalVistas * $prog * 0.85);
                $intStep = (int) ($interaccionesTotales * $prog * 0.85);
                $puntosStep = (int) ($scoreImpactoTotal * $prog * 0.85);

                $fechaLabel = $f->format('d/m');
                $historicoAgrupado[] = [
                    'fecha' => $fechaLabel,
                    'fecha_raw' => $f->format('Y-m-d'),
                    'seguidores' => $segStep,
                    'vistas' => $vistasStep,
                    'interacciones' => $intStep,
                    'puntos' => $puntosStep,
                ];

                foreach ($perfilesActivos as $perfil) {
                    $segPerfilStep = (int) (($perfil->seguidores_punto_cero ?: $perfil->seguidores_actuales) + ((int) ($perfil->seguidores_actuales - $perfil->seguidores_punto_cero) * $prog));
                    $seriesPorRed[$perfil->plataforma]['puntos'][] = [
                        'fecha' => $fechaLabel,
                        'fecha_raw' => $f->format('Y-m-d'),
                        'seguidores' => $segPerfilStep,
                        'vistas' => (int) ($vistasStep / max(1, $perfilesActivos->count())),
                        'interacciones' => (int) ($intStep / max(1, $perfilesActivos->count())),
                        'puntos' => (int) ($puntosStep / max(1, $perfilesActivos->count())),
                    ];
                }
            }
        }

        // 6. Orgánico vs Pauta
        $postsOrganicos = $publicaciones->filter(fn ($p) => ! in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION));
        $postsPautados = $publicaciones->filter(fn ($p) => in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION) && (float) $p->monto_invertido_pauta > 0);

        $vistasOrg = (int) $postsOrganicos->sum('total_vistas');
        $vistasPag = (int) $postsPautados->sum('vistas_pagadas');
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
                    'id' => $p->ejeTematico->id,
                    'pilar_principal' => $p->ejeTematico->pilar_principal,
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                    'icono' => $p->ejeTematico->icono,
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
                    'id' => $p->ejeTematico->id,
                    'pilar_principal' => $p->ejeTematico->pilar_principal,
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                    'icono' => $p->ejeTematico->icono,
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

        // 8. Hitos de Pauta / Booster para la Línea de Tiempo del Gráfico
        $hitosBooster = $publicaciones->filter(function ($p) {
            return in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION) && (float) $p->monto_invertido_pauta > 0;
        })->map(function ($p) {
            return [
                'id' => $p->id,
                'fecha' => $p->fecha_publicacion ? $p->fecha_publicacion->format('d/m') : '',
                'fecha_raw' => $p->fecha_publicacion ? $p->fecha_publicacion->format('Y-m-d') : '',
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido' => (float) $p->monto_invertido_pauta,
                'monto_formateado' => '$' . number_format((float) $p->monto_invertido_pauta, 0, ',', '.'),
                'plataforma' => $p->plataforma ?: $p->perfilSocial?->plataforma ?: 'instagram',
                'titulo' => mb_substr($p->contenido_resumen ?: 'Post Impulsado', 0, 45) . '...',
                'score_impacto' => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10),
            ];
        })->values();

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
                'total_seguidores_netos' => number_format($seguidoresNetosEstimados),
                'total_seguidores_netos_raw' => $seguidoresNetosEstimados,
                'crecimiento_neto_seguidores' => $crecimientoNetoTotalSeguidores,
                'crecimiento_pct_seguidores' => $crecimientoPctTotalSeguidores,
                'score_impacto_total' => number_format($scoreImpactoTotal),
                'score_impacto_raw' => $scoreImpactoTotal,
                'score_promedio_post' => number_format($scorePromedioPorPost),
                'score_promedio_post_raw' => $scorePromedioPorPost,
                'score_promedio_post_meta' => number_format($scorePromedioPostMeta),
                'score_promedio_post_meta_raw' => $scorePromedioPostMeta,
                'score_promedio_post_pct' => $scorePromedioPostPct,
                'score_promedio_post_estado' => $scorePromedioPostEstado,
                'score_promedio_post_estado_texto' => $scorePromedioPostEstadoTexto,
                'origen_meta_score' => $origenMetaScore,
                'meta_score_base_texto' => $metaScoreBaseTexto,
                'score_promedio_mensual' => number_format($scorePromedioMensual),
                'score_promedio_mensual_raw' => $scorePromedioMensual,
                'score_promedio_mensual_meta' => number_format($scorePromedioMensualMeta),
                'score_promedio_mensual_meta_raw' => $scorePromedioMensualMeta,
                'score_promedio_mensual_pct' => $scorePromedioMensualPct,
                'score_promedio_mensual_estado' => $scorePromedioMensualEstado,
                'score_promedio_mensual_estado_texto' => $scorePromedioMensualEstadoTexto,
                'origen_meta_mensual' => $origenMetaMensual,
                'meta_mensual_base_texto' => $metaMensualBaseTexto,
                'score_promedio_diario' => number_format($scorePromedioDiario),
                'score_promedio_diario_raw' => $scorePromedioDiario,
                'record_mensual_score' => number_format($recordMensualScore),
                'record_mensual_score_raw' => $recordMensualScore,
                'record_mensual_nombre' => $recordMensualNombre,
                'record_mensual_corto' => $recordMensualCorto,
                'meta_score_campana' => number_format($metaScoreCampana),
                'meta_score_campana_raw' => $metaScoreCampana,
                'avance_campana_padron_pct' => $avanceCampanaPadronPct,
                'meta_campana_base_texto' => $metaCampanaBaseTexto,
                'dias_campana_activa' => $diasCampanaActiva,
                'meses_campana_activa' => $mesesActivos,
                'tendencia_score_mes' => $tendenciaScoreMes,
                'desglose_mensual' => $desgloseMensual,
                'score_impacto_organico_puro' => number_format($scoreImpactoOrganicoPuro),
                'score_impacto_organico_puro_raw' => $scoreImpactoOrganicoPuro,
                'score_impacto_meta' => number_format($scoreImpactoMeta),
                'score_impacto_meta_raw' => $scoreImpactoMeta,
                'score_impacto_pct' => $scoreImpactoPct,
                'score_impacto_estado' => $scoreImpactoEstado,
                'score_impacto_estado_texto' => $scoreImpactoEstadoTexto,
                'score_impacto_base_texto' => $scoreImpactoBaseTexto,
                'total_vistas' => number_format($totalVistas),
                'total_vistas_raw' => $totalVistas,
                'vistas_promedio_post' => number_format($vistasPromedioPorPost),
                'vistas_promedio_post_raw' => $vistasPromedioPorPost,
                'total_publicaciones' => $totalPosts,
                'engagement_promedio' => $engagementRate.'%',
                'engagement_calidad_texto' => $engagementCalidadTexto,
                'inversion_pauta_total' => $totalPauta,
                'humor_social_promedio' => $humorPromedioFormateado,
                'humor_social_promedio_raw' => $humorPromedioRaw,
                'humor_clima_texto' => $humorClimaTexto,
                'humor_clima_estado' => $humorClimaEstado,
                'ratio_penetracion' => $ratioPenetracionNeta.'%',
                'ratio_penetracion_raw' => $ratioPenetracionNeta,
                'ratio_penetracion_bruta' => $ratioPenetracionBruta.'%',
                'tiers_desglose' => $tiersDesglose,
                'share_of_voice' => $shareOfVoicePropio.'%',
            ],
            'desglose_mensual' => $desgloseMensual,
            'redes_desglose' => $redesDesglose,
            'distribucion_plataformas' => $distribucionPlataformas,
            'rendimiento_por_formato' => $rendimientoPorFormato,
            'formatos_por_red' => $formatosPorRed,
            'distribucion_ejes' => $distribucionEjes,
            'historico_mediciones' => $historicoAgrupado,
            'series_por_red' => $seriesPorRed,
            'hitos_booster' => $hitosBooster,
            'organico_vs_pauta' => $organicoVsPauta,
            'top_publicaciones' => $topPublicaciones,
            'ultimas_publicaciones' => $ultimasPublicaciones,
            'ultimas_notas_prensa' => $notasPrensa,
        ]);
    }
}
