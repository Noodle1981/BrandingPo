<?php

namespace App\Services;

use App\Http\Resources\CandidatoResource;
use App\Http\Resources\MedioResource;
use App\Models\Candidato;
use App\Models\MedioPrensa;
use App\Models\NotaPrensa;
use App\Models\Workspace;
use Carbon\Carbon;

class MediosClippingService
{
    /**
     * Construye y serializa todos los datos necesarios para el Observatorio de Medios y Clipping.
     */
    public function obtenerObservatorioData(Workspace $workspace, array $filtros): array
    {
        $candidatoId = $filtros['candidato_id'] ?? null;
        $tono = $filtros['tono'] ?? null;
        $medioId = $filtros['medio_id'] ?? null;
        $origenTipo = $filtros['origen_tipo'] ?? null;
        $pestanaActiva = $filtros['pestana'] ?? 'directorio';
        $anio = $filtros['anio'] ?? null;
        $mes = $filtros['mes'] ?? null;

        // 1. Años y Meses reales registrados en las notas de este workspace
        $baseFechasQuery = NotaPrensa::delWorkspace($workspace->id)
            ->whereNotNull('fecha_publicacion');

        if ($candidatoId) {
            $baseFechasQuery->where('candidato_id', $candidatoId);
        }
        if ($medioId) {
            $baseFechasQuery->where('medio_prensa_id', $medioId);
        }
        if ($tono) {
            $baseFechasQuery->where('tono_mencion', $tono);
        }
        if ($origenTipo) {
            $baseFechasQuery->where('origen_tipo', $origenTipo);
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

        // 2. Consulta de Medios del Workspace con conteos globales y conteo filtrado del período
        $medios = MedioPrensa::delWorkspace($workspace->id)
            ->withCount([
                'notasPrensa',
                'notasPrensa as notas_web_count' => fn ($q) => $q->where('origen_tipo', 'web'),
                'notasPrensa as notas_fb_count' => fn ($q) => $q->where('origen_tipo', 'facebook'),
                'notasPrensa as notas_filtradas_count' => function ($q) use ($anio, $mes, $candidatoId, $tono) {
                    if ($anio && $mes) {
                        $q->whereYear('fecha_publicacion', $anio)->whereMonth('fecha_publicacion', (int) $mes);
                    } elseif ($anio) {
                        $q->whereYear('fecha_publicacion', $anio);
                    } elseif ($mes) {
                        $q->whereMonth('fecha_publicacion', (int) $mes);
                    }
                    if ($candidatoId) {
                        $q->where('candidato_id', $candidatoId);
                    }
                    if ($tono) {
                        $q->where('tono_mencion', $tono);
                    }
                },
            ])
            ->orderBy('nombre')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nombre' => $m->nombre,
                    'tipo_medio' => $m->tipo_medio,
                    'url_sitio' => $m->url_sitio,
                    'url_facebook' => $m->url_facebook,
                    'avatar_url' => $m->avatar_url,
                    'feed_rss_url' => $m->feed_rss_url,
                    'alcance_tipo' => $m->alcance_tipo,
                    'sesgo_editorial_estimado' => $m->sesgo_editorial_estimado,
                    'notas_prensa_count' => $m->notas_prensa_count,
                    'notas_web_count' => $m->notas_web_count,
                    'notas_fb_count' => $m->notas_fb_count,
                    'notas_filtradas_count' => $m->notas_filtradas_count,
                    'ultima_sincronizacion_at' => $m->ultima_sincronizacion_at?->format('d/m/Y H:i'),
                    'ultima_sincronizacion_diff' => $m->ultima_sincronizacion_at?->diffForHumans(),
                ];
            });

        // 3. Consulta de Candidatos para filtros y asignación de menciones
        $candidatos = Candidato::delWorkspace($workspace->id)
            ->with('perfilesSociales')
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'es_propio', 'avatar_url', 'color_hex', 'cargo_aspirado']);

        // 4. Consulta de Notas de Prensa con filtros aplicados
        $query = NotaPrensa::delWorkspace($workspace->id)
            ->with(['medioPrensa', 'candidato'])
            ->orderByDesc('fecha_publicacion');

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        if ($tono) {
            $query->where('tono_mencion', $tono);
        }

        if ($medioId) {
            $query->where('medio_prensa_id', $medioId);
        }

        if ($origenTipo) {
            $query->where('origen_tipo', $origenTipo);
        }

        if ($anio && $mes) {
            $query->whereYear('fecha_publicacion', $anio)->whereMonth('fecha_publicacion', (int) $mes);
        } elseif ($anio) {
            $query->whereYear('fecha_publicacion', $anio);
        } elseif ($mes) {
            $query->whereMonth('fecha_publicacion', (int) $mes);
        }

        $notas = $query->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'titulo' => $n->titulo,
                'resumen' => $n->resumen,
                'url_nota' => $n->url_nota,
                'fecha' => $n->fecha_publicacion?->format('d/m/Y'),
                'fecha_raw' => $n->fecha_publicacion?->format('Y-m-d'),
                'origen_tipo' => $n->origen_tipo ?? 'web',
                'tipo_mencion' => $n->tipo_mencion ?? 'titular',
                'tono_mencion' => $n->tono_mencion,
                'puntuacion_sentimiento' => (float) ($n->puntuacion_sentimiento ?? 0),
                'es_tapa_o_principal' => (bool) $n->es_tapa_o_principal,
                'interacciones' => (int) $n->interacciones_en_redes_del_medio,
                'reacciones_desglose' => $n->reacciones_desglose,
                'respuesta_replica' => $n->respuesta_replica_candidato,
                'medio' => [
                    'id' => $n->medioPrensa?->id,
                    'nombre' => $n->medioPrensa?->nombre,
                    'tipo_medio' => $n->medioPrensa?->tipo_medio,
                    'sesgo' => $n->medioPrensa?->sesgo_editorial_estimado,
                    'avatar_url' => $n->medioPrensa?->avatar_url,
                ],
                'candidato' => [
                    'id' => $n->candidato?->id,
                    'nombre_completo' => $n->candidato?->nombre_completo,
                    'es_propio' => (bool) $n->candidato?->es_propio,
                    'avatar_url' => $n->candidato?->avatar_url,
                    'color_hex' => $n->candidato?->color_hex ?? '#06b6d4',
                ],
            ];
        });

        // 5. Estadísticas del Observatorio según el período y filtros aplicados
        $queryStats = NotaPrensa::delWorkspace($workspace->id);
        if ($candidatoId) {
            $queryStats->where('candidato_id', $candidatoId);
        }
        if ($medioId) {
            $queryStats->where('medio_prensa_id', $medioId);
        }
        if ($anio && $mes) {
            $queryStats->whereYear('fecha_publicacion', $anio)->whereMonth('fecha_publicacion', (int) $mes);
        } elseif ($anio) {
            $queryStats->whereYear('fecha_publicacion', $anio);
        } elseif ($mes) {
            $queryStats->whereMonth('fecha_publicacion', (int) $mes);
        }

        $notasStats = $queryStats->get();
        $totalFavorables = $notasStats->where('tono_mencion', 'favorable')->count();
        $totalNeutras = $notasStats->where('tono_mencion', 'neutro')->count();
        $totalCriticas = $notasStats->where('tono_mencion', 'critico')->count();
        $totalWeb = $notasStats->where('origen_tipo', 'web')->count();
        $totalFacebook = $notasStats->where('origen_tipo', 'facebook')->count();

        // 6. Agregación de Sentimientos en Facebook para el período y filtros
        $notasFb = $notasStats->where('origen_tipo', 'facebook');
        $sumLikes = 0;
        $sumLove = 0;
        $sumHaha = 0;
        $sumWow = 0;
        $sumSad = 0;
        $sumAngry = 0;

        foreach ($notasFb as $n) {
            $rd = $n->reacciones_desglose;
            if (is_array($rd)) {
                $sumLikes += (int) ($rd['likes'] ?? 0);
                $sumLove += (int) ($rd['love'] ?? 0);
                $sumHaha += (int) ($rd['haha'] ?? 0);
                $sumWow += (int) ($rd['wow'] ?? 0);
                $sumSad += (int) ($rd['sad'] ?? 0);
                $sumAngry += (int) ($rd['angry'] ?? 0);
            }
        }

        $totalReaccionesFb = $sumLikes + $sumLove + $sumHaha + $sumWow + $sumSad + $sumAngry;
        $porcentajeEnojoFb = $totalReaccionesFb > 0 ? round(($sumAngry / $totalReaccionesFb) * 100, 1) : 0;
        $alertaCrisisFb = $porcentajeEnojoFb >= 15.0;

        // 7. Umbral Operativo (Mínimo 5 Medios)
        $totalMediosRegistrados = $medios->count();
        $cumpleUmbral = $totalMediosRegistrados >= 5;
        $progresoUmbral = min(100, round(($totalMediosRegistrados / 5) * 100));

        return [
            'medios' => MedioResource::collection($medios)->resolve(),
            'notas' => $notas,
            'candidatos' => CandidatoResource::collection($candidatos)->resolve(),
            'pestana_activa' => $pestanaActiva,
            'filtros' => [
                'candidato_id' => $candidatoId ? (int) $candidatoId : null,
                'tono' => $tono ?: null,
                'medio_id' => $medioId ? (int) $medioId : null,
                'origen_tipo' => $origenTipo ?: null,
                'anio' => $anio ? (string) $anio : null,
                'mes' => $mes ? (string) $mes : null,
            ],
            'anios_disponibles' => $aniosDisponibles,
            'meses_disponibles' => $mesesDisponibles,
            'umbral' => [
                'total_medios' => $totalMediosRegistrados,
                'minimo_requerido' => 5,
                'cumple_umbral' => $cumpleUmbral,
                'porcentaje_progreso' => $progresoUmbral,
                'faltantes' => max(0, 5 - $totalMediosRegistrados),
            ],
            'resumen_tonos' => [
                'favorables' => $totalFavorables,
                'neutras' => $totalNeutras,
                'criticas' => $totalCriticas,
                'total' => $notasStats->count(),
                'web_count' => $totalWeb,
                'facebook_count' => $totalFacebook,
            ],
            'sentimientos_facebook' => [
                'total_reacciones' => $totalReaccionesFb,
                'likes' => $sumLikes,
                'love' => $sumLove,
                'haha' => $sumHaha,
                'wow' => $sumWow,
                'sad' => $sumSad,
                'angry' => $sumAngry,
                'porcentaje_enojo' => $porcentajeEnojoFb,
                'alerta_crisis' => $alertaCrisisFb,
            ],
        ];
    }

    /**
     * Ejecuta la sincronización masiva de todos los medios del workspace.
     */
    public function sincronizarTodos(Workspace $workspace, GenericMediaScraperService $mediaScraper): array
    {
        $medios = MedioPrensa::delWorkspace($workspace->id)->get();
        if ($medios->isEmpty()) {
            return [
                'success' => false,
                'mensaje' => 'No hay medios registrados para sincronizar.',
            ];
        }

        $candidatos = Candidato::delWorkspace($workspace->id)
            ->with('perfilesSociales')
            ->get();

        $totalAnalizadas = 0;
        $totalMenciones = 0;
        $totalNuevas = 0;

        foreach ($medios as $m) {
            $stats = $mediaScraper->sincronizarMedio($m, $candidatos);
            $totalAnalizadas += $stats['notas_analizadas'];
            $totalMenciones += $stats['menciones_detectadas'];
            $totalNuevas += $stats['nuevas_notas'];
        }

        return [
            'success' => true,
            'mensaje' => "Sincronización masiva finalizada ({$medios->count()} medios): {$totalAnalizadas} notas revisadas, {$totalMenciones} menciones detectadas ({$totalNuevas} nuevas registradas).",
        ];
    }

    /**
     * Crear una nueva nota de prensa con cálculo de interacciones.
     */
    public function guardarNota(Workspace $workspace, array $datos, ?array $reacciones, bool $esTapa): NotaPrensa
    {
        $totalInteracciones = is_array($reacciones) && ! empty($reacciones)
            ? array_sum(array_map('intval', $reacciones))
            : (int) ($datos['interacciones_en_redes_del_medio'] ?? 0);

        return NotaPrensa::create([
            'workspace_id' => $workspace->id,
            'medio_prensa_id' => $datos['medio_prensa_id'],
            'candidato_id' => $datos['candidato_id'] ?? null,
            'origen_tipo' => $datos['origen_tipo'] ?? 'web',
            'tipo_mencion' => $datos['tipo_mencion'] ?? 'titular',
            'fecha_publicacion' => $datos['fecha_publicacion'],
            'titulo' => $datos['titulo'],
            'resumen' => $datos['resumen'] ?? null,
            'url_nota' => $datos['url_nota'] ?? null,
            'tono_mencion' => $datos['tono_mencion'],
            'puntuacion_sentimiento' => (float) ($datos['puntuacion_sentimiento'] ?? 0),
            'es_tapa_o_principal' => $esTapa,
            'interacciones_en_redes_del_medio' => $totalInteracciones,
            'reacciones_desglose' => $reacciones,
            'respuesta_replica_candidato' => $datos['respuesta_replica_candidato'] ?? null,
        ]);
    }

    /**
     * Actualizar una nota existente en el clipping.
     */
    public function actualizarNota(NotaPrensa $nota, array $datos, ?array $reacciones, bool $esTapa): bool
    {
        $totalInteracciones = is_array($reacciones) && ! empty($reacciones)
            ? array_sum(array_map('intval', $reacciones))
            : (int) ($datos['interacciones_en_redes_del_medio'] ?? $nota->interacciones_en_redes_del_medio);

        return $nota->update(array_merge($datos, [
            'es_tapa_o_principal' => $esTapa,
            'interacciones_en_redes_del_medio' => $totalInteracciones,
            'reacciones_desglose' => $reacciones,
            'puntuacion_sentimiento' => (float) ($datos['puntuacion_sentimiento'] ?? 0),
        ]));
    }
}
