<?php

namespace App\Services;

use App\Helpers\WorkspaceHelper;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Publicacion;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CanalMetricasService
{
    /**
     * Rangos de referencia de la industria escalonados por tramo de seguidores (Nano / Medio / Macro).
     */
    public const BENCHMARK_TIERS = [
        'instagram' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 6,
                'posts_semana_ideal' => 5,
                'engagement_min' => 3.5,
                'engagement_ideal' => 7.0,
                'vistas_ratio_reel' => 0.18,
                'descripcion_cadencia' => '4 a 6 posts/semana (Comunidad hiperlocal)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 4,
                'engagement_min' => 1.5,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.12,
                'descripcion_cadencia' => '3 a 5 posts/semana (Reels prioritarios)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 4,
                'engagement_min' => 0.8,
                'engagement_ideal' => 2.0,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '3 a 5 posts/semana (Contenido masivo)',
            ],
        ],
        'facebook' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 7,
                'posts_semana_ideal' => 5,
                'engagement_min' => 2.0,
                'engagement_ideal' => 5.0,
                'vistas_ratio_reel' => 0.15,
                'descripcion_cadencia' => '4 a 7 posts/semana (Vecinal & Grupos)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 7,
                'posts_semana_ideal' => 5,
                'engagement_min' => 1.0,
                'engagement_ideal' => 3.0,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '4 a 7 posts/semana (Conversación y Enlaces)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 10,
                'posts_semana_ideal' => 7,
                'engagement_min' => 0.5,
                'engagement_ideal' => 1.5,
                'vistas_ratio_reel' => 0.06,
                'descripcion_cadencia' => '5 a 10 posts/semana (Prensa & Transmisiones)',
            ],
        ],
        'tiktok' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 10,
                'posts_semana_ideal' => 7,
                'engagement_min' => 6.0,
                'engagement_ideal' => 12.0,
                'vistas_ratio_reel' => 0.35,
                'descripcion_cadencia' => '5 a 10 videos/semana (Volumen algorítmico)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 8,
                'posts_semana_ideal' => 6,
                'engagement_min' => 4.0,
                'engagement_ideal' => 8.0,
                'vistas_ratio_reel' => 0.25,
                'descripcion_cadencia' => '4 a 8 videos/semana (Tendencias & Narrativa)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 7,
                'posts_semana_ideal' => 5,
                'engagement_min' => 2.5,
                'engagement_ideal' => 5.5,
                'vistas_ratio_reel' => 0.18,
                'descripcion_cadencia' => '4 a 7 videos/semana (Producción cuidada)',
            ],
        ],
        'threads' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 12,
                'posts_semana_ideal' => 7,
                'engagement_min' => 3.0,
                'engagement_ideal' => 6.0,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '1 a 2 hilos diarios (Debate & Opinión)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 10,
                'posts_semana_ideal' => 7,
                'engagement_min' => 1.8,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '1 a 2 hilos diarios (Reflexión política)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 8,
                'posts_semana_ideal' => 6,
                'engagement_min' => 1.0,
                'engagement_ideal' => 2.5,
                'vistas_ratio_reel' => 0.05,
                'descripcion_cadencia' => '4 a 8 hilos/semana (Declaraciones de agenda)',
            ],
        ],
        'x_twitter' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 7,
                'posts_semana_max' => 15,
                'posts_semana_ideal' => 10,
                'engagement_min' => 1.5,
                'engagement_ideal' => 3.5,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '1 a 3 tweets/día (Micromensajes & Respuestas)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 7,
                'posts_semana_max' => 14,
                'posts_semana_ideal' => 10,
                'engagement_min' => 0.8,
                'engagement_ideal' => 2.0,
                'vistas_ratio_reel' => 0.06,
                'descripcion_cadencia' => '1 a 2 tweets/día (Agenda política en vivo)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 12,
                'posts_semana_ideal' => 8,
                'engagement_min' => 0.4,
                'engagement_ideal' => 1.2,
                'vistas_ratio_reel' => 0.04,
                'descripcion_cadencia' => '1 tweet diario (Voz institucional)',
            ],
        ],
        'youtube' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 4.0,
                'engagement_ideal' => 8.0,
                'vistas_ratio_reel' => 0.30,
                'descripcion_cadencia' => '2 a 4 shorts o videos/semana',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 2.5,
                'engagement_ideal' => 5.0,
                'vistas_ratio_reel' => 0.20,
                'descripcion_cadencia' => '2 a 4 videos o shorts/semana',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 1,
                'posts_semana_max' => 3,
                'posts_semana_ideal' => 2,
                'engagement_min' => 1.5,
                'engagement_ideal' => 3.5,
                'vistas_ratio_reel' => 0.12,
                'descripcion_cadencia' => '1 a 3 producciones principales/semana',
            ],
        ],
        'linkedin' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 2.5,
                'engagement_ideal' => 5.5,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '2 a 4 posts profesionales/semana',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 1.5,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '2 a 4 publicaciones técnicas/semana',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 1,
                'posts_semana_max' => 3,
                'posts_semana_ideal' => 2,
                'engagement_min' => 1.0,
                'engagement_ideal' => 3.0,
                'vistas_ratio_reel' => 0.05,
                'descripcion_cadencia' => '1 a 3 posts ejecutivos/semana',
            ],
        ],
    ];

    /**
     * Calcula y arma la estructura de datos completa para el dashboard analítico del canal.
     */
    public function calcularMetricas(Request $request, PerfilSocial $perfilSocial): array
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidato = $perfilSocial->candidato()->with('territorio')->first();

        $ejes = EjeTematico::where('workspace_id', $workspace->id)->get();

        // 1. Cargar mediciones históricas time-series
        $mediciones = $perfilSocial->metricasHistoricas()
            ->orderBy('fecha', 'asc')
            ->get();

        // 2. Cargar publicaciones de este canal
        $publicaciones = Publicacion::where('perfil_social_id', $perfilSocial->id)
            ->with(['ejeTematico'])
            ->orderByDesc('fecha_publicacion')
            ->get();

        // 3. Cálculos de Crecimiento & Punto Cero
        $seguidoresActuales = (int) $perfilSocial->seguidores_actuales;
        $seguidoresPuntoCero = (int) $perfilSocial->seguidores_punto_cero;
        $crecimientoNetoSeguidores = $seguidoresActuales - $seguidoresPuntoCero;
        $crecimientoPctSeguidores = $seguidoresPuntoCero > 0
            ? round(($crecimientoNetoSeguidores / $seguidoresPuntoCero) * 100, 2)
            : 0;

        $postsActuales = (int) $perfilSocial->publicaciones_totales;
        $postsPuntoCero = (int) $perfilSocial->publicaciones_punto_cero;
        $crecimientoNetoPosts = max(0, $postsActuales - $postsPuntoCero);

        // 4. Métricas de Engagement & Interacciones Globales
        $totalLikes = (int) $publicaciones->sum('total_likes');
        $totalComentarios = (int) $publicaciones->sum('total_comentarios');
        $totalCompartidos = (int) $publicaciones->sum('total_compartidos');
        $totalRepublicados = (int) $publicaciones->sum('total_republicados');
        $totalGuardados = (int) $publicaciones->sum('total_guardados');
        $totalInteracciones = $totalLikes + $totalComentarios + $totalCompartidos + $totalRepublicados;
        $scoreImpactoTotal = ($totalLikes * 1) + ($totalComentarios * 3) + ($totalCompartidos * 5) + ($totalRepublicados * 10);
        $totalVistas = (int) $publicaciones->sum('total_vistas');
        $totalPauta = (float) $publicaciones->sum('monto_invertido_pauta');

        // Tasa de engagement promedio por post vs seguidores
        $tasaEngagement = ($seguidoresActuales > 0 && $publicaciones->count() > 0)
            ? round((($totalInteracciones / $publicaciones->count()) / $seguidoresActuales) * 100, 2)
            : 0;

        // 5. Benchmark Adaptativo por Tramo de Audiencia
        $plataformaKey = strtolower($perfilSocial->plataforma ?? 'instagram');
        $platformTiers = self::BENCHMARK_TIERS[$plataformaKey] ?? self::BENCHMARK_TIERS['instagram'];

        if ($seguidoresActuales <= 10000) {
            $tramoKey = 'nano';
        } elseif ($seguidoresActuales <= 100000) {
            $tramoKey = 'medio';
        } else {
            $tramoKey = 'macro';
        }

        $benchmark = $platformTiers[$tramoKey];
        $benchmark['tramo_activo'] = $tramoKey;
        $benchmark['tramo_label'] = $benchmark['label'];

        // 6. Frecuencia y Cadencia de Publicación
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();

        $postsEstaSemana = $publicaciones->filter(function ($p) use ($startOfWeek) {
            return $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo($startOfWeek);
        })->count();

        $postsEsteMes = $publicaciones->filter(function ($p) use ($startOfMonth) {
            return $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo($startOfMonth);
        })->count();

        $primerPost = $publicaciones->sortBy('fecha_publicacion')->first()?->fecha_publicacion
            ?? $perfilSocial->fecha_punto_cero
            ?? $now->copy()->subDays(30);

        $diasActivo = max(7, $primerPost ? $primerPost->diffInDays($now) : 30);
        $semanasActivo = max(1, round($diasActivo / 7, 1));
        $mesesActivo = max(1, round($diasActivo / 30, 1));

        $promedioSemanalReal = round($publicaciones->count() / $semanasActivo, 1);
        $promedioMensualReal = round($publicaciones->count() / $mesesActivo, 1);

        $frecuenciaPublicacion = [
            'posts_esta_semana' => $postsEstaSemana,
            'posts_este_mes' => $postsEsteMes,
            'promedio_semanal_real' => $promedioSemanalReal,
            'promedio_mensual_real' => $promedioMensualReal,
            'meta_semanal_min' => $benchmark['posts_semana_min'],
            'meta_semanal_max' => $benchmark['posts_semana_max'],
            'meta_semanal_ideal' => $benchmark['posts_semana_ideal'],
            'descripcion_cadencia' => $benchmark['descripcion_cadencia'],
        ];

        // 7. Orgánico vs Pauta
        $postsOrganicos = $publicaciones->filter(function ($p) {
            return ! in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION) || (float) $p->monto_invertido_pauta <= 0;
        });

        $postsPautados = $publicaciones->filter(function ($p) {
            return in_array($p->tipo_pauta, Publicacion::TIPOS_CON_INVERSION) && (float) $p->monto_invertido_pauta > 0;
        });

        $intOrganicas = $postsOrganicos->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
        $intPautadas = $postsPautados->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
        $vistasOrganicas = $postsOrganicos->sum('total_vistas');
        $vistasPautadas = $postsPautados->sum('total_vistas');
        $inversionPauta = (float) $publicaciones->sum('monto_invertido_pauta');

        $costoPorInteraccion = ($inversionPauta > 0 && $intPautadas > 0)
            ? round($inversionPauta / $intPautadas, 2)
            : 0;

        $roiInteraccionesPorPeso = ($inversionPauta > 0)
            ? round($intPautadas / $inversionPauta, 2)
            : 0;

        $organicoVsPauta = [
            'total_posts_organicos' => $postsOrganicos->count(),
            'total_posts_pautados' => $postsPautados->count(),
            'interacciones_organicas' => $intOrganicas,
            'interacciones_pautadas' => $intPautadas,
            'pct_interacciones_organicas' => $totalInteracciones > 0 ? round(($intOrganicas / $totalInteracciones) * 100, 1) : 100,
            'pct_interacciones_pautadas' => $totalInteracciones > 0 ? round(($intPautadas / $totalInteracciones) * 100, 1) : 0,
            'vistas_organicas' => $vistasOrganicas,
            'vistas_pautadas' => $vistasPautadas,
            'inversion_total' => $inversionPauta,
            'costo_por_interaccion' => $costoPorInteraccion,
            'roi_interacciones_por_peso' => $roiInteraccionesPorPeso,
            'promedio_int_organico' => $postsOrganicos->count() > 0 ? round($intOrganicas / $postsOrganicos->count(), 1) : 0,
            'promedio_int_pautado' => $postsPautados->count() > 0 ? round($intPautadas / $postsPautados->count(), 1) : 0,
        ];

        // 8. Rendimiento por Formato de Contenido
        $rendimientoPorFormato = $publicaciones->groupBy(function ($p) {
            return strtolower($p->tipo_formato ?: 'foto');
        })->map(function ($items, $formato) {
            $count = $items->count();
            $totalInt = $items->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
            $totalV = $items->sum('total_vistas');
            $totalL = $items->sum('total_likes');
            $totalC = $items->sum('total_comentarios');
            $totalP = (float) $items->sum('monto_invertido_pauta');

            return [
                'formato' => ucfirst($formato),
                'tipo_formato' => ucfirst($formato),
                'cantidad' => $count,
                'cantidad_posts' => $count,
                'total_interacciones' => $totalInt,
                'promedio_interacciones' => $count > 0 ? round($totalInt / $count, 1) : 0,
                'total_vistas' => $totalV,
                'promedio_vistas' => $count > 0 ? round($totalV / $count, 1) : 0,
                'total_likes' => $totalL,
                'total_comentarios' => $totalC,
                'total_pauta' => $totalP,
            ];
        })->sortByDesc('promedio_interacciones')->values();

        // 9. Consistencia Mensual
        $mesesNombres = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
        $consistenciaRaw = [];
        $metaMensual = ($benchmark['posts_semana_ideal'] ?? 4) * 4;
        $minMensual = ($benchmark['posts_semana_min'] ?? 3) * 4;

        for ($i = 5; $i >= 0; $i--) {
            $mesFecha = Carbon::now()->subMonths($i);
            $mesYear = $mesFecha->year;
            $mesNum = $mesFecha->month;
            $mesKey = $mesFecha->format('Y-m');
            $nombreLabel = ($mesesNombres[$mesNum] ?? '').' '.$mesYear;

            $postsDelMes = $publicaciones->filter(function ($p) use ($mesYear, $mesNum) {
                return $p->fecha_publicacion && $p->fecha_publicacion->year === $mesYear && $p->fecha_publicacion->month === $mesNum;
            });

            $cantPosts = $postsDelMes->count();
            $intDelMes = $postsDelMes->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
            $pautaDelMes = (float) $postsDelMes->sum('monto_invertido_pauta');

            $consistenciaRaw[] = [
                'mes_key' => $mesKey,
                'mes_nombre' => $nombreLabel,
                'posts_count' => $cantPosts,
                'total_interacciones' => $intDelMes,
                'total_pauta' => $pautaDelMes,
                'meta_mensual' => $metaMensual,
                'min_mensual' => $minMensual,
                'pct_cumplimiento' => min(100, round(($cantPosts / max(1, $metaMensual)) * 100)),
            ];
        }

        $primerMesActivoIdx = null;
        foreach ($consistenciaRaw as $idx => $m) {
            if ($m['posts_count'] > 0) {
                $primerMesActivoIdx = $idx;
                break;
            }
        }

        $consistenciaMensual = [];
        if ($primerMesActivoIdx !== null) {
            $mesesDesdeInicio = array_slice($consistenciaRaw, $primerMesActivoIdx);

            foreach ($mesesDesdeInicio as $m) {
                $cantPosts = $m['posts_count'];
                if ($cantPosts === 0) {
                    $estado = 'inactivo_perdido';
                } elseif ($cantPosts >= $metaMensual) {
                    $estado = 'excelente';
                } elseif ($cantPosts >= $minMensual) {
                    $estado = 'adecuado';
                } else {
                    $estado = 'bajo';
                }
                $m['estado'] = $estado;
                $consistenciaMensual[] = $m;
            }
        } elseif (! empty($consistenciaRaw)) {
            $ultimo = end($consistenciaRaw);
            $ultimo['estado'] = 'inactivo_perdido';
            $consistenciaMensual = [$ultimo];
        }

        // 10. Promedio de Vistas por Reel / Video vs Benchmark
        $reelsYVideos = $publicaciones->filter(fn ($p) => in_array(strtolower($p->tipo_formato ?? ''), ['reel', 'video', 'shorts']));
        $totalReels = $reelsYVideos->count();
        $totalVistasReels = $reelsYVideos->sum('total_vistas');
        $promedioVistasReels = $totalReels > 0 ? round($totalVistasReels / $totalReels) : 0;
        $vistasRatioEsperado = $benchmark['vistas_ratio_reel'] ?? 0.18;
        $vistasEsperadasBenchmark = max(100, round($seguidoresActuales * $vistasRatioEsperado));
        $ratioCumplimientoVistas = $vistasEsperadasBenchmark > 0
            ? round(($promedioVistasReels / $vistasEsperadasBenchmark) * 100)
            : 100;

        $promedioVistasInfo = [
            'total_reels' => $totalReels,
            'total_vistas' => $totalVistasReels,
            'promedio_vistas_real' => $promedioVistasReels,
            'vistas_esperadas_benchmark' => $vistasEsperadasBenchmark,
            'ratio_esperado_pct' => round($vistasRatioEsperado * 100),
            'ratio_cumplimiento' => $ratioCumplimientoVistas,
            'cumple_benchmark' => $promedioVistasReels >= $vistasEsperadasBenchmark,
        ];

        // 11. Paradigma Territorio-First
        $territorio = $candidato->territorio;
        $padronElectoral = (int) ($territorio?->padron_electoral ?: 24500);
        $poblacionTotal = (int) ($territorio?->poblacion_total ?: 31200);
        $nombreTerritorio = $territorio?->nombre ?: 'Territorio Asignado';

        $metaCoberturaRegular = (int) round($padronElectoral * 0.30);
        $metaCoberturaGanadora = (int) round($padronElectoral * 0.40);
        $metaMovilizacionRegular = (int) round($padronElectoral * 0.08);
        $metaMovilizacionGanadora = (int) round($padronElectoral * 0.15);

        $postsEsteMesCollection = $publicaciones->filter(function ($p) use ($startOfMonth) {
            return $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo($startOfMonth);
        });

        $muestraReciente = $publicaciones->take(8);
        $vistasMuestra = (int) $muestraReciente->sum('total_vistas');
        $intMuestra = (int) $muestraReciente->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
        $promedioVistasPorPost = $muestraReciente->count() > 0 ? (int) round($vistasMuestra / $muestraReciente->count()) : 0;
        $promedioIntPorPost = $muestraReciente->count() > 0 ? (int) round($intMuestra / $muestraReciente->count()) : 0;

        $vistasEsteMes = (int) $postsEsteMesCollection->sum('total_vistas');
        if ($vistasEsteMes === 0 && $totalVistas > 0) {
            $vistasEsteMes = min($totalVistas, $promedioVistasPorPost * max(1, $postsEsteMes ?: 3));
        }
        $intEsteMes = (int) $postsEsteMesCollection->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
        if ($intEsteMes === 0 && $totalInteracciones > 0) {
            $intEsteMes = min($totalInteracciones, $promedioIntPorPost * max(1, $postsEsteMes ?: 3));
        }

        $pctPadronAlcanzado = $padronElectoral > 0 ? round(($vistasEsteMes / $padronElectoral) * 100, 1) : 0;
        $pctPadronMovilizado = $padronElectoral > 0 ? round(($intEsteMes / $padronElectoral) * 100, 1) : 0;

        $estadoCobertura = 'critico';
        if ($pctPadronAlcanzado >= 40) {
            $estadoCobertura = 'ganadora';
        } elseif ($pctPadronAlcanzado >= 30) {
            $estadoCobertura = 'regular';
        } elseif ($pctPadronAlcanzado >= 15) {
            $estadoCobertura = 'medio';
        }

        $estadoMovilizacion = 'critico';
        if ($pctPadronMovilizado >= 15) {
            $estadoMovilizacion = 'ganadora';
        } elseif ($pctPadronMovilizado >= 8) {
            $estadoMovilizacion = 'regular';
        } elseif ($pctPadronMovilizado >= 3) {
            $estadoMovilizacion = 'medio';
        }

        $metaSemanalVistas = (int) round($metaCoberturaRegular / 4);
        $postsSemanaNecesarios = $promedioVistasPorPost > 0
            ? (int) max(1, ceil($metaSemanalVistas / $promedioVistasPorPost))
            : $benchmark['posts_semana_ideal'];
        $postsMesNecesarios = $promedioVistasPorPost > 0
            ? (int) max(4, ceil($metaCoberturaRegular / $promedioVistasPorPost))
            : ($benchmark['posts_semana_ideal'] * 4);

        $esAlcanzableSoloOrganico = ($promedioVistasPorPost * $benchmark['posts_semana_ideal'] * 4) >= $metaCoberturaRegular;

        $postViral = $publicaciones->filter(function ($p) use ($padronElectoral) {
            return $padronElectoral > 0 && ($p->total_vistas / $padronElectoral) >= 0.40;
        })->sortByDesc('total_vistas')->first();

        $alertaRedistribucionPauta = null;
        if ($postViral) {
            $pctViral = round(($postViral->total_vistas / $padronElectoral) * 100, 1);
            $alertaRedistribucionPauta = [
                'tipo' => 'exito_viral',
                'post_id' => $postViral->id,
                'vistas' => (int) $postViral->total_vistas,
                'pct_padron' => $pctViral,
                'mensaje' => "🏆 ¡Éxito Viral! La publicación '{$postViral->contenido_resumen}' alcanzó el {$pctViral}% del padrón electoral ({$postViral->total_vistas} visualizaciones) en {$perfilSocial->plataforma}.",
                'accion_sugerida' => 'Este canal tiene tracción orgánica autosuficiente. Se aconseja pausar o reducir pauta aquí y redistribuir el presupuesto hacia canales con menor penetración.',
            ];
        } elseif ($postsPautados->count() > 0 && $intPautadas > 0 && $intOrganicas > $intPautadas * 2) {
            $alertaRedistribucionPauta = [
                'tipo' => 'optimizacion_organica',
                'mensaje' => "El rendimiento orgánico representa el {$organicoVsPauta['pct_interacciones_organicas']}% de la interacción total.",
                'accion_sugerida' => 'Concentrar la pauta en formatos tipo Reel con mensaje de propuesta o geolocalizar por circuitos con menor cobertura.',
            ];
        }

        $electoresAlcanzadosEstimados = (int) min($padronElectoral, round($vistasEsteMes * 0.75));
        $costoPorElectorAlcanzado = ($inversionPauta > 0 && $electoresAlcanzadosEstimados > 0)
            ? round($inversionPauta / $electoresAlcanzadosEstimados, 2)
            : 0;

        $semaforoPadron = [
            'cobertura' => [
                'titulo' => 'Cobertura del Padrón Electoral',
                'actual_vistas' => $vistasEsteMes,
                'pct_actual' => $pctPadronAlcanzado,
                'padron_total' => $padronElectoral,
                'meta_regular_pct' => 30,
                'meta_regular_vistas' => $metaCoberturaRegular,
                'meta_ganadora_pct' => 40,
                'meta_ganadora_vistas' => $metaCoberturaGanadora,
                'brecha_regular' => max(0, $metaCoberturaRegular - $vistasEsteMes),
                'brecha_ganadora' => max(0, $metaCoberturaGanadora - $vistasEsteMes),
                'estado' => $estadoCobertura,
                'diagnostico' => $pctPadronAlcanzado >= 40
                    ? "🟢 Rango de victoria alcanzado: penetración superior al 40% del electorado en {$nombreTerritorio}."
                    : ($pctPadronAlcanzado >= 30
                        ? '🟡 Nivel regular de campaña (30% cubierto). Faltan '.number_format(max(0, $metaCoberturaGanadora - $vistasEsteMes), 0, ',', '.').' visualizaciones para meta de victoria.'
                        : '🔴 Cobertura insuficiente. Se requiere acelerar cadencia o pauta geolocalizada para superar el umbral del 30%.'),
            ],
            'movilizacion' => [
                'titulo' => 'Movilización del Padrón (Interacciones)',
                'actual_interacciones' => $intEsteMes,
                'pct_actual' => $pctPadronMovilizado,
                'meta_regular_pct' => 8,
                'meta_regular_int' => $metaMovilizacionRegular,
                'meta_ganadora_pct' => 15,
                'meta_ganadora_int' => $metaMovilizacionGanadora,
                'brecha_regular' => max(0, $metaMovilizacionRegular - $intEsteMes),
                'estado' => $estadoMovilizacion,
                'diagnostico' => $pctPadronMovilizado >= 8
                    ? "🟢 Excelente capacidad de movilización cívica ({$pctPadronMovilizado}% del padrón interactúa)."
                    : '🟡 Movilización moderada. Fomentar debates, preguntas vecinales y contenido que incite comentarios.',
            ],
            'ritmo' => [
                'titulo' => 'Calculadora de Ritmo de Publicación',
                'promedio_vistas_post' => $promedioVistasPorPost,
                'posts_semana_actual' => $postsEstaSemana,
                'posts_semana_necesarios' => $postsSemanaNecesarios,
                'posts_mes_necesarios' => $postsMesNecesarios,
                'es_alcanzable_organico' => $esAlcanzableSoloOrganico,
                'estado' => $postsEstaSemana >= $postsSemanaNecesarios ? 'verde' : ($postsEstaSemana > 0 ? 'amarillo' : 'rojo'),
                'consejo' => $esAlcanzableSoloOrganico
                    ? '🏆 Con tu promedio de '.number_format($promedioVistasPorPost, 0, ',', '.')." vistas/post, alcanzás la meta del 30% publicando {$postsSemanaNecesarios} veces por semana solo de forma orgánica."
                    : "Con tu promedio actual necesitás {$postsSemanaNecesarios} posts/sem o complementar con pauta territorial.",
            ],
            'amplificacion' => [
                'titulo' => 'Amplificación & Eficiencia Viral',
                'total_compartidos' => $totalCompartidos,
                'total_republicados' => $totalRepublicados,
                'total_guardados' => $totalGuardados,
                'score_impacto_total' => $scoreImpactoTotal,
                'amplificacion_estimada' => ($totalCompartidos * 5) + ($totalRepublicados * 10),
                'costo_por_elector_ars' => $costoPorElectorAlcanzado,
                'electores_alcanzados_estimados' => $electoresAlcanzadosEstimados,
                'diagnostico' => 'Los compartidos y republicados expanden el mensaje hacia electores indecisos fuera de tu comunidad directa.',
            ],
        ];

        // 12. Semáforo de Industria
        $semaforoCadencia = $postsEstaSemana >= $benchmark['posts_semana_min'] ? 'verde' : ($postsEstaSemana === 0 ? 'rojo' : 'amarillo');
        $semaforoEngagement = $tasaEngagement >= $benchmark['engagement_min'] ? 'verde' : ($tasaEngagement < ($benchmark['engagement_min'] / 2) ? 'rojo' : 'amarillo');
        $semaforoVistas = ($totalReels > 0 && $promedioVistasReels >= $vistasEsperadasBenchmark) ? 'verde' : ($promedioVistasReels < ($vistasEsperadasBenchmark * 0.5) ? 'rojo' : 'amarillo');

        $semaforoObjetivos = [
            [
                'id' => 'cadencia_semanal',
                'titulo' => 'Cadencia Semanal',
                'actual' => $postsEstaSemana,
                'actual_formato' => "{$postsEstaSemana} posts esta semana",
                'rango_ideal' => "{$benchmark['posts_semana_min']} - {$benchmark['posts_semana_max']} posts/sem",
                'meta_valor' => $benchmark['posts_semana_ideal'],
                'estado' => $semaforoCadencia,
                'consejo' => $postsEstaSemana < $benchmark['posts_semana_min']
                    ? 'Aumentar frecuencia de publicación para mantener vigencia algorítmica.'
                    : 'Frecuencia dentro del rango óptimo para tu tramo de audiencia.',
            ],
            [
                'id' => 'engagement_rate',
                'titulo' => 'Tasa de Engagement',
                'actual' => $tasaEngagement,
                'actual_formato' => "{$tasaEngagement}%",
                'rango_ideal' => "{$benchmark['engagement_min']}% - {$benchmark['engagement_ideal']}%",
                'meta_valor' => $benchmark['engagement_ideal'],
                'estado' => $semaforoEngagement,
                'consejo' => $tasaEngagement >= $benchmark['engagement_min']
                    ? 'Excelente interacción y resonancia con la comunidad.'
                    : 'Fomentar llamadas a la acción (preguntas directas, encuestas, debates).',
            ],
            [
                'id' => 'vistas_reels',
                'titulo' => 'Alcance en Reels / Videos',
                'actual' => $promedioVistasReels,
                'actual_formato' => number_format($promedioVistasReels, 0, ',', '.').' vistas prom.',
                'rango_ideal' => '≥ '.number_format($vistasEsperadasBenchmark, 0, ',', '.').' vistas ('.($vistasRatioEsperado * 100).'% aud.)',
                'meta_valor' => $vistasEsperadasBenchmark,
                'estado' => $semaforoVistas,
                'consejo' => $promedioVistasReels >= $vistasEsperadasBenchmark
                    ? 'Los videos superan el benchmark de visualizaciones para tu audiencia.'
                    : 'Optimizar los primeros 3 segundos (gancho visual/hook) del contenido en video.',
            ],
            [
                'id' => 'promedio_interacciones',
                'titulo' => 'Interacciones Promedio',
                'actual' => $publicaciones->count() > 0 ? round($totalInteracciones / $publicaciones->count()) : 0,
                'actual_formato' => ($publicaciones->count() > 0 ? number_format(round($totalInteracciones / $publicaciones->count()), 0, ',', '.') : '0').' / post',
                'rango_ideal' => '≥ '.number_format(max(10, round($seguidoresActuales * 0.01)), 0, ',', '.').' int/post',
                'meta_valor' => max(10, round($seguidoresActuales * 0.01)),
                'estado' => ($publicaciones->count() > 0 && ($totalInteracciones / $publicaciones->count()) >= max(10, $seguidoresActuales * 0.01)) ? 'verde' : 'amarillo',
                'consejo' => 'Volumen de reacciones, compartidos y comentarios por publicación.',
            ],
        ];

        // 13. Demografía Interna de la Audiencia
        $demografiaInterna = $perfilSocial->demografia_interna_propia;
        if (! $demografiaInterna && $perfilSocial->candidato->es_propio) {
            $demografiaInterna = [
                'fuente_datos' => 'estimacion_territorial',
                'fecha_extraccion' => now()->format('Y-m-d'),
                'genero' => [
                    'femenino_pct' => 54.2,
                    'masculino_pct' => 44.8,
                    'no_binario_pct' => 1.0,
                ],
                'franjas_etarias' => [
                    ['rango' => '16-17', 'pct' => 4.5, 'categoria' => 'Primer Voto'],
                    ['rango' => '18-29', 'pct' => 38.0, 'categoria' => 'Juventud & Universitarios'],
                    ['rango' => '30-49', 'pct' => 36.5, 'categoria' => 'Adultos Jóvenes & Trabajadores'],
                    ['rango' => '50-69', 'pct' => 16.0, 'categoria' => 'Adultos Plenos'],
                    ['rango' => '70+', 'pct' => 5.0, 'categoria' => 'Adultos Mayores'],
                ],
                'ciudades_principales' => [
                    ['ciudad' => $nombreTerritorio, 'pct' => 64.5],
                    ['ciudad' => 'San Juan Capital', 'pct' => 21.0],
                    ['ciudad' => 'Chimbas / Rivadavia', 'pct' => 8.5],
                ],
                'horarios_actividad' => [
                    'dias_pico' => ['Martes', 'Jueves', 'Domingo'],
                    'horas_pico' => ['12:30 - 14:30', '19:30 - 22:30'],
                ],
            ];
        }

        // Cruce Demográfico
        $cruceDemografico = [];
        if ($territorio && $territorio->piramide_etaria && ! empty($demografiaInterna['franjas_etarias'])) {
            $gruposPadron = collect($territorio->piramide_etaria['grupos_etarios'] ?? []);

            $posts30d = $publicaciones->filter(function ($p) {
                return $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo(now()->subDays(30));
            });
            $muestraReciente = $posts30d->count() >= 3 ? $posts30d : ($publicaciones->take(10)->count() > 0 ? $publicaciones->take(10) : $publicaciones);
            $totalMuestraCount = $muestraReciente->count();

            $totalInteracciones30d = $muestraReciente->sum(fn ($p) => (int) $p->total_likes + (int) $p->total_comentarios + (int) $p->total_compartidos + (int) ($p->total_republicados ?? 0));
            $promedioInteracciones30d = $totalMuestraCount > 0 ? round($totalInteracciones30d / $totalMuestraCount, 1) : 0;
            $promedioVistas30d = $totalMuestraCount > 0 ? round($muestraReciente->avg('visualizaciones') ?: $promedioVistasPorPost) : $promedioVistasPorPost;

            $mesesAgrupados = $publicaciones->groupBy(function ($p) {
                return $p->fecha_publicacion ? $p->fecha_publicacion->format('Y-m') : '2025-01';
            })->map(function ($postsMes, $key) {
                $totalIntMes = $postsMes->sum(fn ($p) => (int) $p->total_likes + (int) $p->total_comentarios + (int) $p->total_compartidos + (int) ($p->total_republicados ?? 0));
                $promedioMes = $postsMes->count() > 0 ? round($totalIntMes / $postsMes->count(), 1) : 0;
                $nombreMes = $postsMes->first()->fecha_publicacion ? $postsMes->first()->fecha_publicacion->translatedFormat('F Y') : $key;

                return [
                    'clave' => $key,
                    'nombre_mes' => ucfirst($nombreMes),
                    'promedio_interacciones' => $promedioMes,
                    'total_posts' => $postsMes->count(),
                ];
            })->sortByDesc('promedio_interacciones');

            $mesPico = $mesesAgrupados->first();
            $promedioInteraccionesPico = $mesPico && $mesPico['promedio_interacciones'] > 0
                ? $mesPico['promedio_interacciones']
                : max($promedioInteracciones30d * 1.35, 15);
            $mesPicoNombre = $mesPico ? $mesPico['nombre_mes'] : 'Mes Récord Campaña';

            foreach ($demografiaInterna['franjas_etarias'] as $franja) {
                $grupoPadron = $gruposPadron->firstWhere('rango', $franja['rango'])
                    ?: $gruposPadron->first(fn ($g) => str_contains($g['rango'], substr($franja['rango'], 0, 2)));

                $pctPadron = $grupoPadron['porcentaje'] ?? 20.0;
                $pctAudiencia = (float) $franja['pct'];
                $brecha = round($pctAudiencia - $pctPadron, 1);

                $electoresTotalesFranja = (int) round($padronElectoral * ($pctPadron / 100));
                $seguidoresEnFranja = (int) round($seguidoresActuales * ($pctAudiencia / 100));
                $coberturaPadronFranjaPct = $electoresTotalesFranja > 0
                    ? round(($seguidoresEnFranja / $electoresTotalesFranja) * 100, 1)
                    : 0;
                $electoresFaltantes = max(0, $electoresTotalesFranja - $seguidoresEnFranja);

                $reacciones30d = round($promedioInteracciones30d * ($pctAudiencia / 100), 1);
                $vistas30d = round($promedioVistas30d * ($pctAudiencia / 100));

                $reaccionesRecord = round($promedioInteraccionesPico * ($pctAudiencia / 100), 1);
                $pctVsRecord = $reaccionesRecord > 0 ? min(100, round(($reacciones30d / $reaccionesRecord) * 100)) : 100;

                if ($coberturaPadronFranjaPct < 15) {
                    $requierePauta = true;
                    $pautaTipo = 'urgente';
                    $pautaBadge = '🚨 Requiere Pauta Urgente';
                    $diagnosticoPauta = "Burbuja orgánica: solo alcanzás al {$coberturaPadronFranjaPct}% del padrón de {$franja['rango']} años ({$seguidoresEnFranja} de {$electoresTotalesFranja} electores). Es obligatorio inyectar pauta para impactar a los {$electoresFaltantes} electores que no te siguen.";
                } elseif ($coberturaPadronFranjaPct < 30) {
                    $requierePauta = true;
                    $pautaTipo = 'moderada';
                    $pautaBadge = '⚡ Reforzar con Pauta Meta Ads';
                    $diagnosticoPauta = "Cobertura inicial ({$coberturaPadronFranjaPct}%). Inyectar pauta segmentada para captar los {$electoresFaltantes} electores antes de la elección.";
                } elseif ($coberturaPadronFranjaPct < 40) {
                    $requierePauta = false;
                    $pautaTipo = 'buena';
                    $pautaBadge = '🟢 Buena Cobertura Electoral';
                    $diagnosticoPauta = "Excelente penetración orgánica ({$coberturaPadronFranjaPct}% del padrón). La comunidad de {$franja['rango']} años tiene masa crítica electoral.";
                } else {
                    $requierePauta = false;
                    $pautaTipo = 'victoria';
                    $pautaBadge = '🏆 Cobertura de Victoria (+40%)';
                    $diagnosticoPauta = "Dominio electoral en {$franja['rango']} años ({$coberturaPadronFranjaPct}% del padrón). Mantener contenido y desviar presupuesto hacia franjas rezagadas.";
                }

                $resonanciaNivel = $pctAudiencia >= 30
                    ? 'Alta Resonancia 🔥'
                    : ($pctAudiencia >= 12
                        ? 'Interacción Moderada ⚡'
                        : 'En Desarrollo 🌱');

                $cruceDemografico[] = [
                    'rango' => $franja['rango'],
                    'categoria' => $franja['categoria'] ?? ($grupoPadron['categoria'] ?? ''),
                    'pct_padron' => $pctPadron,
                    'pct_audiencia' => $pctAudiencia,
                    'brecha' => $brecha,
                    'electores_totales_franja' => $electoresTotalesFranja,
                    'seguidores_en_franja' => $seguidoresEnFranja,
                    'cobertura_padron_franja_pct' => $coberturaPadronFranjaPct,
                    'electores_faltantes' => $electoresFaltantes,
                    'requiere_pauta' => $requierePauta,
                    'pauta_tipo' => $pautaTipo,
                    'pauta_badge' => $pautaBadge,
                    'diagnostico_pauta' => $diagnosticoPauta,
                    'reacciones_actuales_30d' => $reacciones30d,
                    'vistas_actuales_30d' => $vistas30d,
                    'reacciones_max_historico' => $reaccionesRecord,
                    'mes_record_nombre' => $mesPicoNombre,
                    'pct_vs_record' => $pctVsRecord,
                    'resonancia_nivel' => $resonanciaNivel,
                    'accion_sugerida' => $diagnosticoPauta,
                ];
            }
        }

        // 14. Distribución por Ejes Temáticos
        $distribucionEjes = $ejes->map(function ($eje) use ($publicaciones) {
            $postsDelEje = $publicaciones->where('eje_tematico_id', $eje->id);
            $totalInt = $postsDelEje->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));

            return [
                'id' => $eje->id,
                'nombre' => $eje->nombre,
                'color_badge' => $eje->color_badge,
                'total_posts' => $postsDelEje->count(),
                'total_interacciones' => $totalInt,
                'total_likes' => $postsDelEje->sum('total_likes'),
                'total_comentarios' => $postsDelEje->sum('total_comentarios'),
            ];
        })->filter(fn ($e) => $e['total_posts'] > 0)->values();

        // 15. Top 6 Publicaciones más destacadas
        $topPublicaciones = $publicaciones->sortByDesc(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0))
            ->take(6)
            ->values()
            ->map(function ($p) use ($padronElectoral) {
                $int = (int) ($p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
                $coberturaPostPct = $padronElectoral > 0 ? round(($p->total_vistas / $padronElectoral) * 100, 1) : 0;

                return [
                    'id' => $p->id,
                    'fecha_publicacion' => $p->fecha_publicacion?->format('d/m/Y H:i'),
                    'fecha_relativa' => $p->fecha_publicacion?->diffForHumans(),
                    'tipo_formato' => $p->tipo_formato,
                    'tipo_pauta' => $p->tipo_pauta,
                    'url_post' => $p->url_post,
                    'media_url' => $p->media_url,
                    'contenido_resumen' => $p->contenido_resumen,
                    'total_likes' => (int) $p->total_likes,
                    'total_comentarios' => (int) $p->total_comentarios,
                    'total_compartidos' => (int) $p->total_compartidos,
                    'total_republicados' => (int) ($p->total_republicados ?? 0),
                    'total_guardados' => (int) $p->total_guardados,
                    'score_impacto_organico' => (int) $p->score_impacto_organico,
                    'total_vistas' => (int) $p->total_vistas,
                    'total_interacciones' => $int,
                    'cobertura_padron_pct' => $coberturaPostPct,
                    'es_viral_territorial' => $coberturaPostPct >= 40,
                    'sentimiento_predominante' => $p->sentimiento_predominante,
                    'termometro_humor_social' => $p->termometro_humor_social,
                    'eje_tematico' => $p->ejeTematico ? [
                        'id' => $p->ejeTematico->id,
                        'pilar_principal' => $p->ejeTematico->pilar_principal,
                        'nombre' => $p->ejeTematico->nombre,
                        'color_badge' => $p->ejeTematico->color_badge,
                        'icono' => $p->ejeTematico->icono,
                    ] : null,
                ];
            });

        $plataformasEstandar = [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'threads' => 'Threads',
            'tiktok' => 'TikTok',
            'x_twitter' => 'X (Twitter)',
            'youtube' => 'YouTube',
            'linkedin' => 'LinkedIn',
        ];

        $canalesCandidato = collect($plataformasEstandar)->map(function ($nombre, $key) use ($candidato) {
            $p = $candidato->perfilesSociales->firstWhere('plataforma', $key);
            $tieneHandle = $p && ! empty(trim($p->handle_usuario ?? ''));
            $estaActivo = $p ? (bool) $p->esta_activo : false;
            $estaVerificado = $p ? (bool) $p->esta_verificado : false;

            if (! $tieneHandle) {
                $colorEstado = 'gris';
                $estadoTexto = 'Configurar';
            } elseif ($estaVerificado) {
                $colorEstado = 'azul';
                $estadoTexto = 'Verificada';
            } elseif ($estaActivo && ((int) $p->publicaciones_totales > 0 || (int) $p->seguidores_actuales > 0)) {
                $colorEstado = 'verde';
                $estadoTexto = 'Activa';
            } else {
                $colorEstado = 'rojo';
                $estadoTexto = 'Inactiva';
            }

            return [
                'key' => $key,
                'nombre' => $nombre,
                'perfil_id' => $p?->id,
                'handle_usuario' => $p?->handle_usuario ?? '',
                'color_estado' => $colorEstado,
                'estado_texto' => $estadoTexto,
                'seguidores' => $p ? (int) $p->seguidores_actuales : 0,
                'publicaciones' => $p ? (int) $p->publicaciones_totales : 0,
            ];
        })->values();

        $semaforoColor = $perfilSocial->esta_verificado ? 'azul' : ($perfilSocial->esta_activo ? 'verde' : 'rojo');

        return [
            'candidato' => [
                'id' => $candidato->id,
                'nombre_completo' => $candidato->nombre_completo,
                'partido_coalicion' => $candidato->partido_coalicion,
                'cargo_aspirado' => $candidato->cargo_aspirado,
                'estado_politico' => $candidato->estado_politico,
                'color_hex' => $candidato->color_hex,
                'avatar_url' => $candidato->avatar_url,
                'es_propio' => (bool) $candidato->es_propio,
                'territorio_nombre' => $nombreTerritorio,
                'padron_electoral' => $padronElectoral,
            ],
            'canalesCandidato' => $canalesCandidato,
            'perfilSocial' => [
                'id' => $perfilSocial->id,
                'plataforma' => $perfilSocial->plataforma,
                'handle_usuario' => $perfilSocial->handle_usuario,
                'url_perfil' => $perfilSocial->url_perfil,
                'foto_perfil_url' => $perfilSocial->foto_perfil_url,
                'esta_activo' => (bool) $perfilSocial->esta_activo,
                'esta_verificado' => (bool) $perfilSocial->esta_verificado,
                'semaforo_color' => $semaforoColor,
                'seguidores_actuales' => $seguidoresActuales,
                'seguidores_punto_cero' => $seguidoresPuntoCero,
                'seguidos_actuales' => (int) $perfilSocial->seguidos_actuales,
                'publicaciones_totales' => $postsActuales,
                'publicaciones_punto_cero' => $postsPuntoCero,
                'me_gusta_totales' => (int) $perfilSocial->me_gusta_totales,
                'fecha_punto_cero' => $perfilSocial->fecha_punto_cero?->format('d/m/Y'),
                'fecha_ultima_medicion' => $perfilSocial->ultima_auditoria_at?->format('d/m/Y H:i'),
                'fecha_ultima_medicion_relativa' => $perfilSocial->ultima_auditoria_at?->diffForHumans(),
            ],
            'territorioContexto' => [
                'nombre' => $nombreTerritorio,
                'padron_electoral' => $padronElectoral,
                'poblacion_total' => $poblacionTotal,
                'meta_regular_vistas' => $metaCoberturaRegular,
                'meta_ganadora_vistas' => $metaCoberturaGanadora,
            ],
            'stats' => [
                'seguidores_actuales' => $seguidoresActuales,
                'seguidores_punto_cero' => $seguidoresPuntoCero,
                'crecimiento_neto_seguidores' => $crecimientoNetoSeguidores,
                'crecimiento_pct_seguidores' => $crecimientoPctSeguidores,
                'posts_actuales' => $postsActuales,
                'posts_punto_cero' => $postsPuntoCero,
                'crecimiento_neto_posts' => $crecimientoNetoPosts,
                'total_publicaciones_registradas' => $publicaciones->count(),
                'total_interacciones' => $totalInteracciones,
                'total_likes' => $totalLikes,
                'total_comentarios' => $totalComentarios,
                'total_compartidos' => $totalCompartidos,
                'total_guardados' => $totalGuardados,
                'total_vistas' => $totalVistas,
                'total_pauta_invertida' => $totalPauta,
                'tasa_engagement' => $tasaEngagement,
                'promedio_likes_por_post' => $publicaciones->count() > 0 ? round($totalLikes / $publicaciones->count(), 1) : 0,
                'promedio_comentarios_por_post' => $publicaciones->count() > 0 ? round($totalComentarios / $publicaciones->count(), 1) : 0,
                'promedio_vistas_por_post' => $promedioVistasPorPost,
                'costo_por_elector_ars' => $costoPorElectorAlcanzado,
                'electores_alcanzados_estimados' => $electoresAlcanzadosEstimados,
            ],
            'semaforoPadron' => $semaforoPadron,
            'alertaRedistribucionPauta' => $alertaRedistribucionPauta,
            'demografiaAudiencia' => $demografiaInterna,
            'cruceDemografico' => $cruceDemografico,
            'benchmarks' => $benchmark,
            'frecuenciaPublicacion' => $frecuenciaPublicacion,
            'organicoVsPauta' => $organicoVsPauta,
            'rendimientoPorFormato' => $rendimientoPorFormato,
            'consistenciaMensual' => $consistenciaMensual,
            'promedioVistasInfo' => $promedioVistasInfo,
            'semaforoObjetivos' => $semaforoObjetivos,
            'historicoMediciones' => $mediciones->map(function ($m) {
                return [
                    'id' => $m->id,
                    'fecha' => $m->fecha?->format('d/m/Y') ?? $m->created_at?->format('d/m/Y'),
                    'fecha_corta' => $m->fecha?->format('d/m') ?? $m->created_at?->format('d/m'),
                    'seguidores' => (int) $m->seguidores,
                    'seguidos' => (int) $m->seguidos,
                    'publicaciones' => (int) $m->publicaciones_totales,
                    'me_gusta_totales' => (int) $m->me_gusta_totales,
                    'crecimiento_neto_seguidores' => (int) $m->crecimiento_seguidores_neto,
                    'crecimiento_neto_publicaciones' => (int) $m->crecimiento_posts_neto,
                ];
            }),
            'topPublicaciones' => $topPublicaciones,
            'distribucionEjes' => $distribucionEjes,
            'ejes' => $ejes,
        ];
    }
}
