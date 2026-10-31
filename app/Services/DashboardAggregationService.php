<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\NotaPrensa;
use App\Models\PerfilSocialMetrica;
use App\Models\Publicacion;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DashboardAggregationService
{
    public function __construct(
        protected PoliticaEngagementService $politicaEngagement
    ) {}

    /**
     * Calcula y compila el payload completo para el Dashboard del candidato y workspace.
     */
    public function compilarPayload(Workspace $workspace, Candidato $candidato, Collection $todosCandidatos, string $periodo = 'todos'): array
    {
        // 1. Todas las publicaciones históricas del candidato en este workspace
        $todasPublicacionesCandidato = Publicacion::delWorkspace($workspace)
            ->deCandidato($candidato)
            ->with(['perfilSocial', 'ejeTematico', 'pautaEventos'])
            ->latest('fecha_publicacion')
            ->get();

        $ejes = EjeTematico::delWorkspace($workspace)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        // 2. Normalización del Score de Impacto y Agrupación Mensual
        $mesActualYm = Carbon::now()->format('Y-m');
        $publicacionesCronologicas = $todasPublicacionesCandidato->sortBy('fecha_publicacion');
        $desgloseMensual = $this->calcularDesgloseMensual($publicacionesCronologicas, $mesActualYm);

        // Períodos disponibles para el selector
        $periodosDisponibles = collect([
            [
                'clave' => 'todos',
                'nombre' => '🌐 Campaña Completa (Histórico)',
                'es_actual' => false,
                'total_posts' => $todasPublicacionesCandidato->count(),
            ],
        ]);

        foreach ($desgloseMensual->sortByDesc('clave_mes') as $m) {
            $esActual = $m['clave_mes'] === $mesActualYm;
            $periodosDisponibles->push([
                'clave' => $m['clave_mes'],
                'nombre' => $esActual ? "📅 {$m['nombre_mes']} (Mes Actual)" : "📅 {$m['nombre_mes']}",
                'es_actual' => $esActual,
                'total_posts' => $m['total_posts'],
            ]);
        }

        // Determinar período activo y filtrar publicaciones
        $periodoValido = $periodosDisponibles->firstWhere('clave', $periodo);
        $periodoActivo = $periodoValido ? $periodo : 'todos';

        $publicaciones = $periodoActivo !== 'todos'
            ? $todasPublicacionesCandidato->filter(fn ($p) => $p->fecha_publicacion && $p->fecha_publicacion->format('Y-m') === $periodoActivo)->values()
            : $todasPublicacionesCandidato;

        // 3. Desduplicación de audiencia por Tiers
        $tiersData = $this->calcularTiersAudiencia($candidato);
        $seguidoresNetosEstimados = $tiersData['seguidores_netos'];
        $tiersDesglose = $tiersData['desglose'];

        // 4. Estadísticas y KPIs de Rendimiento
        $stats = $this->calcularKpisEstadisticos(
            $workspace,
            $candidato,
            $publicaciones,
            $publicacionesCronologicas,
            $desgloseMensual,
            $periodoActivo,
            $mesActualYm,
            $seguidoresNetosEstimados,
            $tiersDesglose
        );

        // 5. Redes sociales, formatos y plataformas
        $redesDesglose = $this->calcularRedesDesglose($candidato, $publicaciones);
        $distribucionPlataformas = $this->calcularDistribucionPlataformas($redesDesglose, $stats['interacciones_totales_raw']);
        $rendimientoPorFormato = $this->calcularRendimientoPorFormato($publicaciones);
        $formatosPorRed = $this->calcularFormatosPorRed($candidato, $publicaciones);
        $distribucionEjes = $this->calcularDistribucionEjes($ejes, $publicaciones);

        // 6. Time-Series e Histórico de Mediciones
        $timeSeries = $this->calcularHistoricoTimeSeries($candidato, $todasPublicacionesCandidato, $stats);
        $organicoVsPauta = $this->calcularOrganicoVsPauta($publicaciones);

        // 7. Publicaciones destacadas, recientes y clipping de medios
        $topPublicaciones = $this->calcularTopPublicaciones($candidato, $publicaciones);
        $ultimasPublicaciones = $this->calcularUltimasPublicaciones($candidato, $publicaciones);
        $ultimasNotasPrensa = $this->obtenerUltimasNotasPrensa($workspace, $candidato);
        $hitosBooster = $this->calcularHitosBooster($publicaciones);

        $padronElectoral = (int) ($candidato->padron_electoral ?: ($candidato->territorio?->padron_electoral ?? 0));

        return [
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
            'periodo_activo' => $periodoActivo,
            'periodos_disponibles' => $periodosDisponibles->values(),
            'stats' => $stats,
            'desglose_mensual' => $desgloseMensual,
            'redes_desglose' => $redesDesglose,
            'distribucion_plataformas' => $distribucionPlataformas,
            'rendimiento_por_formato' => $rendimientoPorFormato,
            'formatos_por_red' => $formatosPorRed,
            'distribucion_ejes' => $distribucionEjes,
            'historico_mediciones' => $timeSeries['historico_agrupado'],
            'series_por_red' => $timeSeries['series_por_red'],
            'hitos_booster' => $hitosBooster,
            'organico_vs_pauta' => $organicoVsPauta,
            'top_publicaciones' => $topPublicaciones,
            'ultimas_publicaciones' => $ultimasPublicaciones,
            'ultimas_notas_prensa' => $ultimasNotasPrensa,
        ];
    }

    /**
     * Agrupación mensual cronológica de publicaciones.
     */
    protected function calcularDesgloseMensual(Collection $publicacionesCronologicas, string $mesActualYm): Collection
    {
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

        $gruposPorMes = $publicacionesCronologicas->groupBy(fn ($p) => $p->fecha_publicacion ? $p->fecha_publicacion->format('Y-m') : 'sin_fecha')
            ->reject(fn ($posts, $key) => $key === 'sin_fecha');

        $desgloseMensual = $gruposPorMes->map(function ($postsMes, $keyYm) use ($mesesEspanol, $mesesCortos) {
            $partes = explode('-', $keyYm);
            $ano = $partes[0] ?? date('Y');
            $mesInt = (int) ($partes[1] ?? 1);
            $nombreMes = ($mesesEspanol[$mesInt] ?? $keyYm)." {$ano}";
            $cortoMes = $mesesCortos[$mesInt] ?? $keyYm;

            $cantPosts = $postsMes->count();
            $scoreMes = (int) $postsMes->sum(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10));
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

        if (! $desgloseMensual->firstWhere('clave_mes', $mesActualYm)) {
            $partesNow = explode('-', $mesActualYm);
            $anoNow = $partesNow[0] ?? date('Y');
            $mesIntNow = (int) ($partesNow[1] ?? 1);
            $desgloseMensual->push([
                'clave_mes' => $mesActualYm,
                'nombre_mes' => ($mesesEspanol[$mesIntNow] ?? $mesActualYm)." {$anoNow}",
                'mes_corto' => $mesesCortos[$mesIntNow] ?? $mesActualYm,
                'ano' => (int) $anoNow,
                'total_posts' => 0,
                'score_total' => 0,
                'score_promedio_post' => 0,
                'total_vistas' => 0,
                'total_interacciones' => 0,
            ]);
        }

        return $desgloseMensual->sortBy('clave_mes')->values();
    }

    /**
     * Deduplicación de audiencia por Tiers.
     */
    protected function calcularTiersAudiencia(Candidato $candidato): array
    {
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
                $tierNombre = 'Tier 1 (Red Principal Activa)';
            } else {
                $esMeta = in_array($plataforma, ['facebook', 'instagram', 'threads']);
                $tieneMetaPrevia = count(array_intersect($plataformasProcesadas, ['facebook', 'instagram', 'threads'])) > 0;
                $factorIncremental = ($esMeta && $tieneMetaPrevia) ? 0.35 : 0.55;
                $tierNombre = ($esMeta && $tieneMetaPrevia) ? "Tier {$tierNumero} (Meta / Solapado)" : "Tier {$tierNumero} (Nueva Audiencia)";
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

        $totalSeguidores = (int) $candidato->perfilesSociales->sum('seguidores_actuales');
        if ($seguidoresNetosEstimados <= 0) {
            $seguidoresNetosEstimados = $totalSeguidores;
        }

        return [
            'seguidores_netos' => $seguidoresNetosEstimados,
            'desglose' => $tiersDesglose,
        ];
    }

    /**
     * Cálculo de KPIs Estadísticos del Dashboard.
     */
    protected function calcularKpisEstadisticos(
        Workspace $workspace,
        Candidato $candidato,
        Collection $publicaciones,
        Collection $publicacionesCronologicas,
        Collection $desgloseMensual,
        string $periodoActivo,
        string $mesActualYm,
        int $seguidoresNetosEstimados,
        array $tiersDesglose
    ): array {
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
        $totalPauta = (float) $publicaciones->sum('monto_invertido_pauta');
        $totalPosts = $publicaciones->count();

        $interaccionesTotales = $totalLikes + $totalComentarios + $totalCompartidos + $totalRepublicados;
        $scoreImpactoTotal = ($totalLikes * 1) + ($totalComentarios * 3) + ($totalCompartidos * 5) + ($totalRepublicados * 10);

        $postsOrganicos = $publicaciones->filter(fn ($p) => ! in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION));
        $scoreImpactoOrganicoPuro = (int) $postsOrganicos->sum(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10));

        $mesesActivos = max(1, $desgloseMensual->filter(fn ($m) => $m['total_posts'] > 0)->count());
        $scorePromedioPorPost = $totalPosts > 0 ? (int) round($scoreImpactoTotal / $totalPosts) : 0;
        $scorePromedioMensual = $periodoActivo === 'todos' ? (int) round($scoreImpactoTotal / $mesesActivos) : $scoreImpactoTotal;
        $vistasPromedioPorPost = $totalPosts > 0 ? (int) round($totalVistas / $totalPosts) : 0;

        if ($periodoActivo === 'todos') {
            $fechaPrimera = $publicacionesCronologicas->first()?->fecha_publicacion;
            $fechaUltima = $publicacionesCronologicas->last()?->fecha_publicacion ?? Carbon::now();
            $diasCampanaActiva = $fechaPrimera ? max(1, (int) $fechaPrimera->diffInDays($fechaUltima) + 1) : 1;
        } else {
            try {
                $diasCampanaActiva = Carbon::createFromFormat('Y-m', $periodoActivo)->daysInMonth;
            } catch (\Throwable $e) {
                $diasCampanaActiva = 30;
            }
        }
        $scorePromedioDiario = (int) round($scoreImpactoTotal / max(1, $diasCampanaActiva));

        // Tendencia mensual
        $mesParaTendenciaYm = $periodoActivo !== 'todos' ? $periodoActivo : $mesActualYm;
        $mesReferenciaItem = $desgloseMensual->firstWhere('clave_mes', $mesParaTendenciaYm)
            ?? $desgloseMensual->filter(fn ($m) => $m['clave_mes'] <= $mesParaTendenciaYm)->last()
            ?? $desgloseMensual->last();

        $tendenciaScoreMes = null;
        if ($mesReferenciaItem) {
            $idxActual = $desgloseMensual->search(fn ($m) => $m['clave_mes'] === $mesReferenciaItem['clave_mes']);
            if ($idxActual !== false && $idxActual > 0) {
                $mesAnterior = $desgloseMensual[$idxActual - 1];
                $diffScoreTotal = $mesReferenciaItem['score_total'] - $mesAnterior['score_total'];
                $diffPct = $mesAnterior['score_total'] > 0
                    ? round(($diffScoreTotal / $mesAnterior['score_total']) * 100, 1)
                    : 0;

                $tendenciaScoreMes = [
                    'mes_actual' => $mesReferenciaItem['mes_corto'],
                    'mes_anterior' => $mesAnterior['mes_corto'],
                    'score_actual' => $mesReferenciaItem['score_total'],
                    'score_anterior' => $mesAnterior['score_total'],
                    'variacion_pts' => $diffScoreTotal,
                    'variacion_pct' => $diffPct,
                ];
            }
        }

        // Análisis integral con PoliticaEngagementService
        $analisisGlobal = $this->politicaEngagement->calcularTasaAceptacionReal([
            'seguidores_canal' => $seguidoresNetosEstimados,
            'likes' => $totalLikes,
            'comentarios' => $totalComentarios,
            'compartidos' => $totalCompartidos,
            'republicados' => $totalRepublicados,
            'es_pauta' => $totalPauta > 0,
        ]);

        $engagementRate = $analisisGlobal['tap_politica_real'];
        $scoreTraccionGlobal = $analisisGlobal['score_traccion_indexado'];
        $engagementCalidadTexto = $analisisGlobal['etiqueta_calidad'];

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

        $padronElectoral = (int) ($candidato->padron_electoral ?: ($candidato->territorio?->padron_electoral ?? 0));
        $ratioPenetracionNeta = $padronElectoral > 0 ? round(($seguidoresNetosEstimados / $padronElectoral) * 100, 1) : 0;
        $ratioPenetracionBruta = $padronElectoral > 0 ? round(($totalSeguidores / $padronElectoral) * 100, 1) : 0;

        $factorEngagementObjetivo = 0.5;
        $scoreImpactoMeta = (int) max(500, round($seguidoresNetosEstimados * $factorEngagementObjetivo));

        // Meta proporcional de score por post
        if ($padronElectoral > 0) {
            $factorPadron = $padronElectoral <= 50000 ? 0.005 : ($padronElectoral <= 200000 ? 0.004 : 0.0025);
            $pctTexto = $padronElectoral <= 50000 ? '0.5% del padrón' : ($padronElectoral <= 200000 ? '0.4% del padrón' : '0.25% del padrón');
            $scorePromedioPostMeta = (int) max(20, round($padronElectoral * $factorPadron));
            $origenMetaScore = 'padron';
            $metaScoreBaseTexto = $pctTexto;
        } else {
            $scorePromedioPostMeta = (int) max(20, round($seguidoresNetosEstimados * 0.03));
            $origenMetaScore = 'seguidores';
            $metaScoreBaseTexto = '3% de seguidores';
        }

        $scorePromedioPostPct = $scorePromedioPostMeta > 0 ? round(($scorePromedioPorPost / $scorePromedioPostMeta) * 100, 1) : 0;
        $scorePromedioPostEstado = $scorePromedioPostPct >= 100 ? 'exitoso' : ($scorePromedioPostPct >= 60 ? 'solido' : 'bajo');
        $scorePromedioPostEstadoTexto = $scorePromedioPostPct >= 100 ? 'Exitoso' : ($scorePromedioPostPct >= 60 ? 'Sólido' : 'Por mejorar');

        // Meta proporcional mensual
        if ($padronElectoral > 0) {
            $factorPadronMensual = $padronElectoral <= 50000 ? 0.10 : ($padronElectoral <= 200000 ? 0.06 : 0.03);
            $pctTextoMensual = $padronElectoral <= 50000 ? '10% del padrón' : ($padronElectoral <= 200000 ? '6% del padrón' : '3% del padrón');
            $scorePromedioMensualMeta = (int) max(100, round($padronElectoral * $factorPadronMensual));
            $origenMetaMensual = 'padron';
            $metaMensualBaseTexto = $pctTextoMensual;
        } else {
            $scorePromedioMensualMeta = (int) max(100, round($seguidoresNetosEstimados * 0.30));
            $origenMetaMensual = 'seguidores';
            $metaMensualBaseTexto = '30% de seguidores';
        }

        $scorePromedioMensualPct = $scorePromedioMensualMeta > 0 ? round(($scorePromedioMensual / $scorePromedioMensualMeta) * 100, 1) : 0;
        $scorePromedioMensualEstado = $scorePromedioMensualPct >= 100 ? 'exitoso' : ($scorePromedioMensualPct >= 60 ? 'solido' : 'bajo');
        $scorePromedioMensualEstadoTexto = $scorePromedioMensualPct >= 100 ? 'Exitoso' : ($scorePromedioMensualPct >= 60 ? 'Sólido' : 'Por mejorar');

        // Récord histórico mensual
        $mejorMesItem = $desgloseMensual->sortByDesc('score_total')->first();
        $recordMensualScore = $mejorMesItem ? (int) $mejorMesItem['score_total'] : $scoreImpactoTotal;
        $recordMensualNombre = $mejorMesItem ? $mejorMesItem['mes_corto'].' '.$mejorMesItem['ano'] : '';
        $recordMensualCorto = $mejorMesItem ? $mejorMesItem['mes_corto'] : '';

        // Meta total de campaña (Presión Electoral Total)
        if ($padronElectoral > 0) {
            $metaScoreCampana = $padronElectoral;
            $metaCampanaBaseTexto = '100% del padrón electoral';
        } else {
            $metaScoreCampana = (int) max(1000, round($seguidoresNetosEstimados * 2.0));
            $metaCampanaBaseTexto = '2x comunidad de seguidores';
        }

        $avanceCampanaPadronPct = $metaScoreCampana > 0 ? round(($scoreImpactoTotal / $metaScoreCampana) * 100, 1) : 0;
        $pctPadronCubiertoPorTiers = $padronElectoral > 0 ? round(($seguidoresNetosEstimados / $padronElectoral) * 100, 1) : 0;

        $scoreImpactoBaseTexto = number_format($seguidoresNetosEstimados, 0, ',', '.')
            .' únicos netos (Tiers) × '.$factorEngagementObjetivo
            .' — cubre '.$pctPadronCubiertoPorTiers.'% del padrón';

        $scoreImpactoPct = $scoreImpactoMeta > 0 ? round(($scoreImpactoTotal / $scoreImpactoMeta) * 100, 1) : 0;
        $scoreImpactoEstado = $scoreImpactoPct >= 100 ? 'optimo' : ($scoreImpactoPct >= 60 ? 'mantenimiento' : ($scoreImpactoPct >= 35 ? 'creciendo' : 'frio'));
        $scoreImpactoEstadoTexto = $scoreImpactoPct >= 100 ? 'Óptimo' : ($scoreImpactoPct >= 60 ? 'Mantenimiento' : ($scoreImpactoPct >= 35 ? 'Creciendo' : 'Bajo impacto'));

        $totalVistasEcosistema = Publicacion::delWorkspace($workspace)->sum('total_vistas');
        $shareOfVoicePropio = $totalVistasEcosistema > 0 ? round(($totalVistas / $totalVistasEcosistema) * 100, 1) : 0;

        return [
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
            'total_publicaciones' => $totalPosts,
            'interacciones_totales' => number_format($interaccionesTotales),
            'interacciones_totales_raw' => $interaccionesTotales,
            'total_likes' => number_format($totalLikes),
            'total_comentarios' => number_format($totalComentarios),
            'total_compartidos' => number_format($totalCompartidos),
            'engagement_promedio' => $engagementRate.'%',
            'score_traccion_promedio' => $scoreTraccionGlobal,
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
        ];
    }

    /**
     * Desglose por Red Social del Candidato con Métricas y Enlace.
     */
    protected function calcularRedesDesglose(Candidato $candidato, Collection $publicaciones): Collection
    {
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
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $perfilesPorPlataforma = $candidato->perfilesSociales->keyBy('plataforma');

        return collect($plataformasOrden)->map(function ($plat) use ($perfilesPorPlataforma, $publicaciones, $startOfMonth, $plataformasColores) {
            $perfil = $perfilesPorPlataforma->get($plat);
            $tieneHandle = $perfil && (! empty(trim($perfil->handle_usuario ?? '')) || ! empty(trim($perfil->url_perfil ?? '')));

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

                $postsMes = $postsRed->filter(fn ($p) => $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo($startOfMonth))->count();

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
    }

    /**
     * Distribución de Interacciones por Red Social (Gráfico Donut).
     */
    protected function calcularDistribucionPlataformas(Collection $redesDesglose, int $interaccionesTotales): Collection
    {
        $redesActivasInteraccion = $redesDesglose->filter(fn ($red) => (bool) $red['esta_activo'] || (int) $red['interacciones_acumuladas'] > 0 || ! empty($red['handle_usuario']))
            ->sortByDesc('interacciones_acumuladas')
            ->values();

        if ($redesActivasInteraccion->isEmpty()) {
            $redesActivasInteraccion = $redesDesglose->take(3);
        }

        return $redesActivasInteraccion->map(function ($red) use ($interaccionesTotales) {
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
    }

    /**
     * Rendimiento por Formato (Barras).
     */
    protected function calcularRendimientoPorFormato(Collection $publicaciones): Collection
    {
        $formatosAgrupados = $publicaciones->groupBy(fn ($p) => $p->tipo_formato ?: 'Post');

        return $formatosAgrupados->map(function ($posts, $formato) {
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
    }

    /**
     * Matriz Cruzada de Formato por Red.
     */
    protected function calcularFormatosPorRed(Candidato $candidato, Collection $publicaciones): Collection
    {
        $todosFormatos = $publicaciones->pluck('tipo_formato')->filter()->unique()->values();

        return $candidato->perfilesSociales
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
    }

    /**
     * Gráfico de Ejes Temáticos.
     */
    protected function calcularDistribucionEjes(Collection $ejes, Collection $publicaciones): Collection
    {
        return $ejes->map(function ($eje) use ($publicaciones) {
            $postsEje = $publicaciones->where('eje_tematico_id', $eje->id);
            $count = $postsEje->count();
            $vistas = (int) $postsEje->sum('total_vistas');
            $likes = (int) $postsEje->sum('total_likes');
            $comentarios = (int) $postsEje->sum('total_comentarios');
            $compartidos = (int) $postsEje->sum('total_compartidos');
            $republicados = (int) $postsEje->sum('total_republicados');
            $intTotal = $likes + $comentarios + $compartidos + $republicados;
            $scoreImpactoPonderado = ($likes * 1) + ($comentarios * 3) + ($compartidos * 5) + ($republicados * 10);

            $scoreTraccionPromedio = $count > 0
                ? (int) round($postsEje->avg(fn ($p) => $p->analisis_traccion['score_traccion_indexado'] ?? 50))
                : 50;

            $humor = $postsEje->whereNotNull('termometro_humor_social')->avg('termometro_humor_social');

            return [
                'id' => $eje->id,
                'pilar_principal' => $eje->pilar_principal,
                'nombre' => $eje->nombre,
                'color_badge' => $eje->color_badge ?: '#06b6d4',
                'icono' => $eje->icono,
                'posts_count' => $count,
                'total_vistas' => $vistas,
                'total_interacciones' => $intTotal,
                'score_impacto_ponderado' => $scoreImpactoPonderado,
                'score_traccion_promedio' => $scoreTraccionPromedio,
                'humor_promedio' => $humor ? round($humor, 1) : 4.5,
            ];
        })->filter(fn ($e) => $e['posts_count'] > 0)->values()->sortByDesc('score_impacto_ponderado')->values();
    }

    /**
     * Histórico time-series consolidado.
     */
    protected function calcularHistoricoTimeSeries(Candidato $candidato, Collection $postsTimeline, array $stats): array
    {
        $plataformasColores = [
            'instagram' => '#E4405F',
            'facebook' => '#1877F2',
            'threads' => '#000000',
            'tiktok' => '#00F2FE',
            'x_twitter' => '#000000',
            'youtube' => '#FF0000',
            'linkedin' => '#0A66C2',
        ];

        $perfilesActivos = $candidato->perfilesSociales->filter(fn ($p) => (bool) $p->esta_activo || (int) $p->seguidores_actuales > 0);
        $perfilesIds = $perfilesActivos->pluck('id');
        $medicionesHistoricas = PerfilSocialMetrica::whereIn('perfil_social_id', $perfilesIds)
            ->orderBy('fecha', 'asc')
            ->get();

        $fechasUnicas = $medicionesHistoricas->pluck('fecha')
            ->concat($postsTimeline->pluck('fecha_publicacion'))
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

                $postsHastaFecha = $postsTimeline->filter(fn ($p) => $p->fecha_publicacion && $p->fecha_publicacion->format('Y-m-d') <= $fechaStr);
                $totalPuntosDia = (int) $postsHastaFecha->sum(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10));
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
                    $scoreRed = (int) $postsRedHastaFecha->sum(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10));
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
            $now = Carbon::now();
            $diasMuestra = 7;
            $historicoAgrupado = [];
            $totalVistasHistoricas = (int) $postsTimeline->sum('total_vistas');
            $scoreImpactoHistorico = (int) $postsTimeline->sum(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10));
            $interaccionesHistoricas = (int) $postsTimeline->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));

            for ($i = $diasMuestra - 1; $i >= 0; $i--) {
                $f = $now->copy()->subDays($i);
                $prog = ($diasMuestra - $i) / $diasMuestra;
                $segStep = (int) ($stats['total_seguidores_raw'] - ($stats['crecimiento_neto_seguidores'] * (1 - $prog)));
                $vistasStep = (int) ($totalVistasHistoricas * $prog * 0.85);
                $intStep = (int) ($interaccionesHistoricas * $prog * 0.85);
                $puntosStep = (int) ($scoreImpactoHistorico * $prog * 0.85);

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

        return [
            'historico_agrupado' => $historicoAgrupado,
            'series_por_red' => $seriesPorRed,
        ];
    }

    /**
     * Métricas de Orgánico vs Pauta.
     */
    protected function calcularOrganicoVsPauta(Collection $publicaciones): array
    {
        $postsOrganicos = $publicaciones->filter(fn ($p) => ! in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION));
        $postsPautados = $publicaciones->filter(fn ($p) => in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION) && (float) $p->monto_invertido_pauta > 0);

        $postsImpulsadosDetalle = $postsPautados->map(function ($p) {
            $ultimoEvento = $p->pautaEventos->first();

            if ($ultimoEvento) {
                $baseOrganicaLikes = (int) $ultimoEvento->likes_snapshot;
                $ganadosPautaLikes = max(0, (int) $p->total_likes - $baseOrganicaLikes);
                $baseOrganicaVistas = (int) $ultimoEvento->vistas_snapshot;
                $ganadosPautaVistas = max(0, (int) $p->total_vistas - $baseOrganicaVistas);
                $fechaBooster = $ultimoEvento->fecha_evento ? $ultimoEvento->fecha_evento->format('d/m/Y') : null;
                $costoPorLikeAtribuible = $ultimoEvento->costo_por_like;
            } else {
                $baseOrganicaLikes = (int) round($p->total_likes * 0.70);
                $ganadosPautaLikes = max(0, $p->total_likes - $baseOrganicaLikes);
                $baseOrganicaVistas = (int) ($p->vistas_organicas > 0 ? $p->vistas_organicas : round($p->total_vistas * 0.70));
                $ganadosPautaVistas = max(0, (int) ($p->vistas_pagadas > 0 ? $p->vistas_pagadas : $p->total_vistas - $baseOrganicaVistas));
                $fechaBooster = $p->fecha_publicacion ? $p->fecha_publicacion->format('d/m/Y') : 'Activo';
                $costoPorLikeAtribuible = $ganadosPautaLikes > 0 && (float) $p->monto_invertido_pauta > 0
                    ? round((float) $p->monto_invertido_pauta / $ganadosPautaLikes, 2)
                    : null;
            }

            $totalInteraccionesPost = (int) ($p->total_likes + $p->total_comentarios + $p->total_compartidos + ($p->total_republicados ?? 0));
            $pctOrg = $p->total_likes > 0 ? round(($baseOrganicaLikes / $p->total_likes) * 100, 1) : 100;
            $pctPauta = $p->total_likes > 0 ? round(($ganadosPautaLikes / $p->total_likes) * 100, 1) : 0;

            return [
                'id' => $p->id,
                'titulo' => Str::limit($p->contenido_resumen ?: 'Post Impulsado', 65),
                'url_post' => $p->url_post,
                'media_url' => $p->media_url,
                'plataforma' => $p->plataforma ?: $p->perfilSocial?->plataforma ?: 'facebook',
                'tipo_formato' => $p->tipo_formato ?: 'Post',
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido' => (float) $p->monto_invertido_pauta,
                'total_likes' => (int) $p->total_likes,
                'base_organica_likes' => $baseOrganicaLikes,
                'ganados_pauta_likes' => $ganadosPautaLikes,
                'total_vistas' => (int) $p->total_vistas,
                'base_organica_vistas' => $baseOrganicaVistas,
                'ganados_pauta_vistas' => $ganadosPautaVistas,
                'total_interacciones' => $totalInteraccionesPost,
                'pct_organico' => $pctOrg,
                'pct_pautado' => $pctPauta,
                'costo_por_like' => $costoPorLikeAtribuible,
                'fecha_booster' => $fechaBooster,
                'estado' => $p->tipo_pauta === 'organico_impulsado' ? 'Booster Activo' : 'Dark Post Activo',
            ];
        })->values();

        $totalPauta = (float) $publicaciones->sum('monto_invertido_pauta');
        $vistasOrg = (int) $postsOrganicos->sum('total_vistas') + (int) $postsImpulsadosDetalle->sum('base_organica_vistas');
        $vistasPag = (int) $postsImpulsadosDetalle->sum('ganados_pauta_vistas');
        $intOrg = (int) $postsOrganicos->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0)) + (int) $postsImpulsadosDetalle->sum('base_organica_likes');
        $intPag = (int) $postsImpulsadosDetalle->sum('ganados_pauta_likes');

        $totalIntGeneral = $intOrg + $intPag;
        $totalVistasGeneral = $vistasOrg + $vistasPag;

        $costoPorInteraccion = ($totalPauta > 0 && $intPag > 0) ? round($totalPauta / $intPag, 2) : 0;
        $cpmEstimado = ($totalPauta > 0 && $vistasPag > 0) ? round(($totalPauta / $vistasPag) * 1000, 2) : 0;

        return [
            'total_posts_organicos' => $postsOrganicos->count(),
            'total_posts_pautados' => $postsPautados->count(),
            'vistas_organicas' => $vistasOrg,
            'vistas_pagadas' => $vistasPag,
            'interacciones_organicas' => $intOrg,
            'interacciones_pautadas' => $intPag,
            'porcentaje_interacciones_organicas' => $totalIntGeneral > 0 ? round(($intOrg / $totalIntGeneral) * 100, 1) : 100,
            'porcentaje_interacciones_pautadas' => $totalIntGeneral > 0 ? round(($intPag / $totalIntGeneral) * 100, 1) : 0,
            'porcentaje_vistas_organicas' => $totalVistasGeneral > 0 ? round(($vistasOrg / $totalVistasGeneral) * 100, 1) : 100,
            'porcentaje_vistas_pagadas' => $totalVistasGeneral > 0 ? round(($vistasPag / $totalVistasGeneral) * 100, 1) : 0,
            'inversion_total' => $totalPauta,
            'costo_por_interaccion' => $costoPorInteraccion,
            'cpm_estimado' => $cpmEstimado,
            'posts_impulsados' => $postsImpulsadosDetalle,
            'primer_post_impulsado' => $postsImpulsadosDetalle->first(),
        ];
    }

    /**
     * Top Publicaciones Destacadas.
     */
    protected function calcularTopPublicaciones(Candidato $candidato, Collection $publicaciones): Collection
    {
        return $publicaciones->sortByDesc(fn ($p) => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10))
            ->take(4)
            ->map(fn ($p) => $this->formatearPublicacionCard($p, $candidato))
            ->values();
    }

    /**
     * Últimas publicaciones del candidato para el feed.
     */
    protected function calcularUltimasPublicaciones(Candidato $candidato, Collection $publicaciones): Collection
    {
        return $publicaciones->take(5)
            ->map(fn ($p) => $this->formatearPublicacionCard($p, $candidato))
            ->values();
    }

    /**
     * Formateador unificado de tarjeta de publicación.
     */
    protected function formatearPublicacionCard(Publicacion $p, Candidato $candidato): array
    {
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
            'figuras_acompanantes' => $p->figuras_acompanantes ?? [],
            'comentarios_destacados' => $p->comentarios_destacados ?? [],
            'url_post' => $p->url_post,
            'media_url' => $p->media_url,
        ];
    }

    /**
     * Obtener últimas notas de prensa que mencionan al candidato.
     */
    protected function obtenerUltimasNotasPrensa(Workspace $workspace, Candidato $candidato): Collection
    {
        return NotaPrensa::delWorkspace($workspace)
            ->deCandidato($candidato)
            ->with('medioPrensa')
            ->recientes()
            ->take(4)
            ->get()
            ->map(fn ($nota) => [
                'id' => $nota->id,
                'medio_nombre' => $nota->medioPrensa?->nombre ?? 'Medio Digital',
                'medio_tipo' => $nota->medioPrensa?->tipo_medio ?? 'digital',
                'titulo' => $nota->titulo,
                'url_nota' => $nota->url_nota,
                'tono_mencion' => $nota->tono_mencion,
                'es_portada' => $nota->es_tapa_o_principal,
                'fecha' => $nota->fecha_publicacion ? $nota->fecha_publicacion->format('d/m/Y') : '',
                'resumen' => $nota->resumen,
            ]);
    }

    /**
     * Hitos de Booster / Pauta para la línea de tiempo.
     */
    protected function calcularHitosBooster(Collection $publicaciones): Collection
    {
        return $publicaciones->filter(fn ($p) => in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION) && (float) $p->monto_invertido_pauta > 0)
            ->map(fn ($p) => [
                'id' => $p->id,
                'fecha' => $p->fecha_publicacion ? $p->fecha_publicacion->format('d/m') : '',
                'fecha_raw' => $p->fecha_publicacion ? $p->fecha_publicacion->format('Y-m-d') : '',
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido' => (float) $p->monto_invertido_pauta,
                'monto_formateado' => '$'.number_format((float) $p->monto_invertido_pauta, 0, ',', '.'),
                'plataforma' => $p->plataforma ?: $p->perfilSocial?->plataforma ?: 'instagram',
                'titulo' => mb_substr($p->contenido_resumen ?: 'Post Impulsado', 0, 45).'...',
                'score_impacto' => ($p->total_likes * 1) + ($p->total_comentarios * 3) + ($p->total_compartidos * 5) + ((int) ($p->total_republicados ?? 0) * 10),
            ])->values();
    }
}
