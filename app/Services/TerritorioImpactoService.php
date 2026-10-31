<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Publicacion;
use App\Models\Territorio;
use App\Models\Workspace;

class TerritorioImpactoService
{
    public function __construct(
        protected DemographicIntelligenceService $demographicService
    ) {}

    /**
     * Construye y estructura los datos para el Mapa de Situación Territorial.
     */
    public function obtenerMapaData(Workspace $workspace, ?int $territorioId = null): array
    {
        $nivelPolitico = $workspace->nivel_politico ?? 'intendente';
        $esGobernador = in_array($nivelPolitico, ['gobernador', 'senador', 'legislador_nacional']);

        // Territorios pertenecientes a este workspace
        $provincia = Territorio::delWorkspace($workspace->id)
            ->where(function ($q) {
                $q->where('tipo', 'provincia')->orWhereNull('parent_id');
            })
            ->first();

        if (! $provincia) {
            $provincia = Territorio::where('tipo', 'provincia')->first();
        }

        $departamentosQuery = Territorio::delWorkspace($workspace->id);
        if ($provincia) {
            $departamentos = (clone $departamentosQuery)
                ->where('parent_id', $provincia->id)
                ->with(['candidatoPropio.perfilesSociales', 'candidatos' => function ($q) {
                    $q->with('perfilesSociales');
                }])
                ->ordenados()
                ->get();
        } else {
            $departamentos = (clone $departamentosQuery)
                ->with(['candidatoPropio.perfilesSociales', 'candidatos' => function ($q) {
                    $q->with('perfilesSociales');
                }])
                ->ordenados()
                ->get();
        }

        $candidatoPropio = Candidato::delWorkspace($workspace->id)
            ->propios()
            ->with('territorio')
            ->first();

        if ($esGobernador && ! $territorioId) {
            $territorioActivo = $provincia ?: $departamentos->first();
        } elseif ($territorioId) {
            $territorioActivo = $departamentos->firstWhere('id', $territorioId) ?: ($provincia ?: $departamentos->first());
        } else {
            $territorioActivoId = $candidatoPropio?->territorio_id ?: $departamentos->first()?->id;
            $territorioActivo = $departamentos->firstWhere('id', $territorioActivoId) ?: ($provincia ?: $departamentos->first());
        }

        $piramide = null;
        $estrategia = null;
        if ($territorioActivo) {
            $piramide = $territorioActivo->piramide_etaria ?: $this->demographicService->generarPiramideEtaria(
                $territorioActivo->poblacion_total,
                $territorioActivo->padron_electoral
            );
            $estrategia = $this->demographicService->recomendarEstrategiaDigital(
                $piramide,
                (float) ($territorioActivo->poblacion_urbana_pct ?: 70)
            );
        }

        $padronProvincialTotal = $departamentos->sum('padron_electoral') ?: ($provincia?->padron_electoral ?: 610000);
        $poblacionProvincialTotal = $departamentos->sum('poblacion_total') ?: ($provincia?->poblacion_total ?: 820000);

        return [
            'nivel_politico' => $nivelPolitico,
            'nivel_label' => $workspace->nivel_politico_label,
            'es_gobernador' => $esGobernador,
            'provincia' => $provincia,
            'departamentos' => $departamentos->map(function ($d) {
                $candidato = $d->candidatoPropio;
                $totalSeguidoresBruto = $candidato ? $candidato->perfilesSociales->where('esta_activo', true)->sum('seguidores_actuales') : 0;
                $totalSeguidoresDesduplicados = (int) round($totalSeguidoresBruto * 0.70);
                $coberturaPadronPct = ($d->padron_electoral > 0 && $totalSeguidoresDesduplicados > 0)
                    ? round(($totalSeguidoresDesduplicados / $d->padron_electoral) * 100, 1)
                    : 0;

                $semaforoCalor = 'rojo';
                if ($coberturaPadronPct >= 20) {
                    $semaforoCalor = 'verde';
                } elseif ($coberturaPadronPct >= 5) {
                    $semaforoCalor = 'amarillo';
                }

                $slugMapa = strtolower(trim(str_replace(['Departamento', 'departamento', ' '], ['', '', '_'], $d->nombre)));
                $slugMapa = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $slugMapa);
                $slugMapa = trim($slugMapa, '_');

                return [
                    'id' => $d->id,
                    'nombre' => $d->nombre,
                    'slug_mapa' => $slugMapa,
                    'tipo' => $d->tipo,
                    'codigo_indec' => $d->codigo_indec,
                    'latitud' => $d->latitud,
                    'longitud' => $d->longitud,
                    'poblacion_total' => (int) $d->poblacion_total,
                    'padron_electoral' => (int) $d->padron_electoral,
                    'poblacion_urbana_pct' => (float) $d->poblacion_urbana_pct,
                    'poblacion_rural_pct' => (float) $d->poblacion_rural_pct,
                    'hogares_nbi_pct' => (float) $d->hogares_nbi_pct,
                    'semaforo_calor' => $semaforoCalor,
                    'candidato_propio' => $candidato ? [
                        'id' => $candidato->id,
                        'nombre_completo' => $candidato->nombre_completo,
                        'cargo_aspirado' => $candidato->cargo_aspirado,
                        'avatar_url' => $candidato->avatar_url,
                        'total_seguidores_bruto' => $totalSeguidoresBruto,
                        'total_seguidores' => $totalSeguidoresDesduplicados,
                        'cobertura_padron_pct' => $coberturaPadronPct,
                    ] : null,
                    'total_candidatos' => $d->candidatos->count(),
                ];
            }),
            'territorio_activo' => $territorioActivo ? [
                'id' => $territorioActivo->id,
                'nombre' => $territorioActivo->nombre,
                'tipo' => $territorioActivo->tipo,
                'codigo_indec' => $territorioActivo->codigo_indec,
                'latitud' => $territorioActivo->latitud,
                'longitud' => $territorioActivo->longitud,
                'poblacion_total' => (int) $territorioActivo->poblacion_total,
                'padron_electoral' => (int) $territorioActivo->padron_electoral,
                'poblacion_urbana_pct' => (float) $territorioActivo->poblacion_urbana_pct,
                'poblacion_rural_pct' => (float) $territorioActivo->poblacion_rural_pct,
                'hogares_nbi_pct' => (float) $territorioActivo->hogares_nbi_pct,
                'piramide' => $piramide,
                'estrategia' => $estrategia,
                'candidato_propio' => $territorioActivo->candidatoPropio ? [
                    'id' => $territorioActivo->candidatoPropio->id,
                    'nombre_completo' => $territorioActivo->candidatoPropio->nombre_completo,
                    'cargo_aspirado' => $territorioActivo->candidatoPropio->cargo_aspirado,
                    'avatar_url' => $territorioActivo->candidatoPropio->avatar_url,
                ] : null,
            ] : null,
            'provincias' => $this->demographicService->getProvinciasArgentinas(),
            'metricas_macro' => [
                'padron_total_provincial' => $padronProvincialTotal,
                'poblacion_total_provincial' => $poblacionProvincialTotal,
                'total_departamentos' => $departamentos->count(),
                'departamentos_con_candidato' => $departamentos->filter(fn ($d) => $d->candidatoPropio !== null)->count(),
            ],
        ];
    }

    /**
     * Construye y estructura los datos para la Matriz de Impacto Territorial & Penetración Electoral.
     */
    public function obtenerImpactoElectoralData(Workspace $workspace, ?int $territorioId = null): array
    {
        $departamentos = Territorio::delWorkspace($workspace->id)
            ->ordenados()
            ->get();

        $candidatoPropio = Candidato::delWorkspace($workspace->id)
            ->propios()
            ->with(['perfilesSociales.metricas', 'territorio'])
            ->first();

        if ($territorioId) {
            $territorioActivo = $departamentos->firstWhere('id', $territorioId) ?: $candidatoPropio?->territorio;
        } else {
            $territorioActivo = $candidatoPropio?->territorio ?: $departamentos->first();
        }

        if (! $territorioActivo) {
            $territorioActivo = Territorio::where('tipo', 'provincia')->first() ?: Territorio::first();
        }

        $padronElectoral = (int) ($territorioActivo?->padron_electoral ?: 24500);
        $poblacionTotal = (int) ($territorioActivo?->poblacion_total ?: 31200);
        $metaVotos = (int) round($padronElectoral * 0.40);

        $piramide = $territorioActivo?->piramide_etaria ?: $this->demographicService->generarPiramideEtaria($poblacionTotal, $padronElectoral);

        $perfiles = $candidatoPropio ? $candidatoPropio->perfilesSociales : collect();
        $totalSeguidoresBruto = (int) $perfiles->sum('seguidores_actuales');

        $perfilesActivos = $perfiles
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

        if ($seguidoresNetosEstimados <= 0) {
            $seguidoresNetosEstimados = $totalSeguidoresBruto;
        }

        $totalSeguidores = $seguidoresNetosEstimados;
        $penetracionPadronPct = $padronElectoral > 0 ? round(($totalSeguidores / $padronElectoral) * 100, 2) : 0;
        $penetracionPadronBrutaPct = $padronElectoral > 0 ? round(($totalSeguidoresBruto / $padronElectoral) * 100, 2) : 0;
        $coberturaMetaPct = $metaVotos > 0 ? round(($totalSeguidores / $metaVotos) * 100, 2) : 0;

        $publicaciones = $candidatoPropio
            ? Publicacion::where('candidato_id', $candidatoPropio->id)->with('ejeTematico')->get()
            : collect();

        $totalPosts = $publicaciones->count();
        $totalLikes = (int) $publicaciones->sum('total_likes');
        $totalComentarios = (int) $publicaciones->sum('total_comentarios');
        $totalCompartidos = (int) $publicaciones->sum('total_compartidos');
        $totalRepublicados = (int) $publicaciones->sum('total_republicados');
        $totalGuardados = (int) $publicaciones->sum('total_guardados');
        $totalInteracciones = $totalLikes + $totalComentarios + $totalCompartidos + $totalRepublicados + $totalGuardados;
        $totalPautaInvertida = (float) $publicaciones->sum('monto_invertido_pauta');

        $scoreImpactoTotal = ($totalLikes * 1) + ($totalComentarios * 3) + ($totalCompartidos * 5) + ($totalRepublicados * 10);

        $perfilesConPosts = $perfiles->filter(function ($p) use ($publicaciones) {
            return $p->esta_activo || $publicaciones->where('perfil_social_id', $p->id)->count() > 0;
        });
        $cantRedesActivas = max(1, $perfilesConPosts->count() > 0 ? $perfilesConPosts->count() : $perfiles->count());
        $promedioPostsPorRed = round($totalPosts / $cantRedesActivas, 1);
        $scoreImpactoMeta = (int) max(500, round($promedioPostsPorRed * 500));
        $scoreImpactoPct = $scoreImpactoMeta > 0 ? round(($scoreImpactoTotal / $scoreImpactoMeta) * 100, 1) : 0;
        $scoreImpactoBaseTexto = "{$promedioPostsPorRed} posts prom. / {$cantRedesActivas} ".($cantRedesActivas === 1 ? 'red' : 'redes').' (x500 pts)';

        $promedioInteraccionesPorPost = $totalPosts > 0 ? round($totalInteracciones / $totalPosts, 1) : 0;
        $promedioLikesPorPost = $totalPosts > 0 ? round($totalLikes / $totalPosts, 1) : 0;
        $promedioComentariosPorPost = $totalPosts > 0 ? round($totalComentarios / $totalPosts, 1) : 0;

        $picoMaximoPost = $publicaciones->sortByDesc(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + $p->total_guardados)->first();
        $picoMaximoInteracciones = $picoMaximoPost
            ? (int) ($picoMaximoPost->total_likes + $picoMaximoPost->total_comentarios + $picoMaximoPost->total_compartidos + $picoMaximoPost->total_guardados)
            : 0;

        $tasaMovilizacionPromedioPct = $padronElectoral > 0 ? round(($promedioInteraccionesPorPost / $padronElectoral) * 100, 2) : 0;
        $tasaMovilizacionPicoPct = $padronElectoral > 0 ? round(($picoMaximoInteracciones / $padronElectoral) * 100, 2) : 0;

        $pastelPadron = [
            [
                'label' => 'Comunidad Única en Redes (Tiers)',
                'valor' => $totalSeguidores,
                'porcentaje' => $penetracionPadronPct,
                'color' => '#06b6d4',
            ],
            [
                'label' => 'Padrón No Alcanzado (Oportunidad)',
                'valor' => max(0, $padronElectoral - $totalSeguidores),
                'porcentaje' => round(max(0, 100 - $penetracionPadronPct), 1),
                'color' => '#334155',
            ],
        ];

        $pastelRedes = $perfiles->map(function ($p) use ($totalSeguidoresBruto) {
            $pct = $totalSeguidoresBruto > 0 ? round(($p->seguidores_actuales / $totalSeguidoresBruto) * 100, 1) : 0;

            return [
                'label' => ucfirst($p->plataforma),
                'plataforma' => $p->plataforma,
                'valor' => (int) $p->seguidores_actuales,
                'porcentaje' => $pct,
                'color' => match ($p->plataforma) {
                    'instagram' => '#E4405F',
                    'facebook' => '#1877F2',
                    'tiktok' => '#00F2FE',
                    'youtube' => '#FF0000',
                    'x_twitter' => '#64748b',
                    default => '#06b6d4',
                },
            ];
        })->filter(fn ($r) => $r['valor'] > 0)->values();

        $nucleoDuroEstimado = max(10, (int) round($promedioLikesPorPost * 1.3));
        $seguidoresPasivos = max(0, $totalSeguidores - $nucleoDuroEstimado);
        $expansionPautaEstimada = $totalPautaInvertida > 0 ? (int) round($totalPautaInvertida / 1.6) : 0;
        $silenciosoEstimado = max(0, $padronElectoral - $totalSeguidores - $expansionPautaEstimada);

        $pastelElectorado = [
            [
                'label' => 'Núcleo Duro Activo (Militancia Fiel)',
                'valor' => $nucleoDuroEstimado,
                'porcentaje' => $padronElectoral > 0 ? round(($nucleoDuroEstimado / $padronElectoral) * 100, 1) : 0,
                'color' => '#10b981',
                'desc' => 'Electores que interactúan y militan activamente',
            ],
            [
                'label' => 'Comunidad Pasiva (Seguidores Observadores)',
                'valor' => $seguidoresPasivos,
                'porcentaje' => $padronElectoral > 0 ? round(($seguidoresPasivos / $padronElectoral) * 100, 1) : 0,
                'color' => '#06b6d4',
                'desc' => 'Seguidores que leen y miran historias en silencio',
            ],
            [
                'label' => 'Votantes Conquistados por Pauta',
                'valor' => $expansionPautaEstimada,
                'porcentaje' => $padronElectoral > 0 ? round(($expansionPautaEstimada / $padronElectoral) * 100, 1) : 0,
                'color' => '#8b5cf6',
                'desc' => 'Vecinos fuera del círculo alcanzados por anuncios',
            ],
            [
                'label' => 'Padrón No Alcanzado (Indecisos)',
                'valor' => $silenciosoEstimado,
                'porcentaje' => $padronElectoral > 0 ? round(($silenciosoEstimado / $padronElectoral) * 100, 1) : 0,
                'color' => '#475569',
                'desc' => 'Electorado restante por conquistar en el departamento',
            ],
        ];

        $diasParaEleccion = 118;
        $semanasRestantes = max(1, round($diasParaEleccion / 7));
        $crecimientoSemanalPromedio = 35;
        $proyeccionOrganicaTotal = $totalSeguidores + ($crecimientoSemanalPromedio * $semanasRestantes);
        $proyeccionOrganicaPadronPct = $padronElectoral > 0 ? round(($proyeccionOrganicaTotal / $padronElectoral) * 100, 1) : 0;
        $brechaParaMeta = max(0, $metaVotos - $proyeccionOrganicaTotal);
        $pautaMensualSugerida = round(($brechaParaMeta * 1.6) / max(1, $semanasRestantes / 4));

        $inteligenciaTiempo = [
            'dias_para_eleccion' => $diasParaEleccion,
            'semanas_restantes' => $semanasRestantes,
            'ritmo_semanal_crecimiento' => $crecimientoSemanalPromedio,
            'proyeccion_organica_total' => $proyeccionOrganicaTotal,
            'proyeccion_organica_padron_pct' => $proyeccionOrganicaPadronPct,
            'brecha_meta_votos' => $brechaParaMeta,
            'pauta_mensual_sugerida_ars' => $pautaMensualSugerida,
            'dias_pico' => 'Martes, Jueves y Domingos',
            'horarios_prime' => '12:30 a 14:30 hs (Almuerzo) & 19:30 a 22:30 hs (Prime Time Nocturno)',
        ];

        $redesImpacto = $perfiles->map(function ($p) use ($padronElectoral, $publicaciones) {
            $postsRed = $publicaciones->where('perfil_social_id', $p->id);
            $totalIntRed = $postsRed->sum(fn ($post) => $post->total_likes + $post->total_comentarios + $post->total_compartidos + $post->total_guardados);
            $pautaRed = $postsRed->sum('monto_invertido_pauta');
            $seguidores = (int) $p->seguidores_actuales;
            $coberturaPct = $padronElectoral > 0 ? round(($seguidores / $padronElectoral) * 100, 2) : 0;

            $rangoObjetivo = match ($p->plataforma) {
                'tiktok' => '16 a 24 años (Voto Joven / Primer Votante)',
                'instagram' => '20 a 42 años (Jóvenes Adultos & Profesionales)',
                'facebook' => '40 a 70+ años (Familias & Adultos Mayores)',
                'youtube' => 'Transversal (Debates, Entrevistas y Formatos Largos)',
                default => 'Población General',
            };

            $estrategiaRol = match ($p->plataforma) {
                'tiktok' => 'Canal de Choque & Primer Voto: Viralizar contenido corto y dinámico.',
                'instagram' => 'Canal de Propuesta & Cercanía: Storytelling barrial y recorridas.',
                'facebook' => 'Canal de Anclaje Vecinal: Noticias de gestión, obras y streaming.',
                'youtube' => 'Canal de Profundidad: Entrevistas extensas y debates programáticos.',
                default => 'Canal de Difusión Institucional',
            };

            $estadoNivel = 'critico';
            if ($coberturaPct >= 30) {
                $estadoNivel = 'ganadora';
            } elseif ($coberturaPct >= 10) {
                $estadoNivel = 'regular';
            }

            $diagnostico = match ($p->plataforma) {
                'tiktok' => $coberturaPct >= 10 ? '🟢 Penetración óptima en jóvenes' : '🟡 Potencial para captar primer voto',
                'instagram' => $coberturaPct >= 8 ? '🟢 Comunidad consolidada en sector productivo' : '🟡 Foco en dinamizar historias y reels',
                'facebook' => $coberturaPct >= 6 ? '🟢 Buen anclaje barrial' : '🔴 Brecha en adultos: se recomienda pauta geolocalizada',
                default => '🟢 Presencia activa',
            };

            return [
                'id' => $p->id,
                'plataforma' => $p->plataforma,
                'handle_usuario' => $p->handle_usuario,
                'url_perfil' => $p->url_perfil,
                'foto_perfil_url' => $p->foto_perfil_url,
                'seguidores' => $seguidores,
                'cobertura_padron_pct' => $coberturaPct,
                'total_publicaciones' => $postsRed->count(),
                'total_interacciones' => $totalIntRed,
                'pauta_invertida' => $pautaRed,
                'rango_objetivo' => $rangoObjetivo,
                'estrategia_rol' => $estrategiaRol,
                'estado_nivel' => $estadoNivel,
                'diagnostico' => $diagnostico,
            ];
        });

        $redGanadora = $redesImpacto->firstWhere('estado_nivel', 'ganadora');
        $redCritica = $redesImpacto->firstWhere('estado_nivel', 'critico');
        $alertaBalanceRedes = null;

        if ($redGanadora && $redCritica) {
            $alertaBalanceRedes = [
                'mensaje' => "Tu canal de {$redGanadora['plataforma']} tiene excelente tracción ({$redGanadora['cobertura_padron_pct']}% del padrón), mientras que {$redCritica['plataforma']} requiere refuerzo.",
                'accion' => "Redirigir el 40% del presupuesto de pauta de {$redGanadora['plataforma']} hacia {$redCritica['plataforma']} para cerrar la brecha en votantes mayores.",
            ];
        }

        $cruceFranjas = collect($piramide['grupos_etarios'] ?? [])->map(function ($grupo) use ($totalSeguidores) {
            $electoresFranja = (int) $grupo['electores'];
            $pctFranjaPadron = (float) $grupo['porcentaje'];

            $factorPenetracion = match ($grupo['rango']) {
                '16-17', '18-29' => 0.45,
                '30-49' => 0.35,
                '50-69' => 0.15,
                default => 0.05,
            };

            $seguidoresEstimadosFranja = (int) round($totalSeguidores * $factorPenetracion);
            $coberturaFranjaPct = $electoresFranja > 0 ? round(($seguidoresEstimadosFranja / $electoresFranja) * 100, 1) : 0;

            return [
                'id' => $grupo['id'],
                'rango' => $grupo['rango'],
                'categoria' => $grupo['categoria'],
                'color_hex' => $grupo['color_hex'],
                'electores_padron' => $electoresFranja,
                'pct_padron' => $pctFranjaPadron,
                'seguidores_estimados' => $seguidoresEstimadosFranja,
                'cobertura_franja_pct' => $coberturaFranjaPct,
                'red_principal' => $grupo['red_principal'],
                'estado' => $coberturaFranjaPct >= 20 ? 'verde' : ($coberturaFranjaPct >= 8 ? 'amarillo' : 'rojo'),
            ];
        });

        $ahora = now();
        $candidatosBoost = $publicaciones->filter(function ($pub) use ($ahora) {
            if (! in_array($pub->tipo_pauta, ['organico', 'organico_impulsado'])) {
                return false;
            }
            $fecha = $pub->fecha_publicacion ?: $pub->created_at;
            if (! $fecha) {
                return false;
            }
            $horas = $fecha->diffInHours($ahora);

            return $horas >= 48 && $horas <= (7 * 24);
        });

        if ($candidatosBoost->isEmpty()) {
            $candidatosBoost = $publicaciones->filter(fn ($pub) => in_array($pub->tipo_pauta, ['organico', 'organico_impulsado']));
        }

        $oportunidadesPauta = $candidatosBoost->sortByDesc(function ($pub) use ($totalSeguidores) {
            $base = max($totalSeguidores, 500);

            return (($pub->total_likes + $pub->total_comentarios * 2) / $base) * 100;
        })->take(3)->values()->map(function ($pub) use ($padronElectoral, $ahora) {
            $interacciones = $pub->total_likes + $pub->total_comentarios + $pub->total_compartidos + $pub->total_guardados;
            $sugerenciaPauta = 25000;
            $alcanceEstimado = min($padronElectoral, (int) round($sugerenciaPauta / 1.6));
            $coberturaEstimadaPct = round(($alcanceEstimado / max($padronElectoral, 1)) * 100, 1);

            $fecha = $pub->fecha_publicacion ?: $pub->created_at;
            $horas = $fecha ? $fecha->diffInHours($ahora) : 72;
            $dias = round($horas / 24, 1);
            $enVentanaOro = ($horas >= 48 && $horas <= 168);

            return [
                'id' => $pub->id,
                'url_post' => $pub->url_post,
                'contenido_resumen' => $pub->contenido_resumen,
                'tipo_formato' => $pub->tipo_formato,
                'plataforma' => $pub->plataforma,
                'total_likes' => (int) $pub->total_likes,
                'total_comentarios' => (int) $pub->total_comentarios,
                'total_interacciones' => $interacciones,
                'eje_tematico' => $pub->ejeTematico?->nombre ?? 'General',
                'sugerencia_inversion_ars' => $sugerenciaPauta,
                'alcance_estimado_electores' => $alcanceEstimado,
                'cobertura_estimada_padron_pct' => $coberturaEstimadaPct,
                'horas_publicado' => $horas,
                'dias_publicado' => $dias,
                'en_ventana_oro' => $enVentanaOro,
                'estado_ventana' => $enVentanaOro ? "🟢 Ventana de Oro ({$dias}d de publicado)" : ($horas < 48 ? "🟡 En maduración ({$horas}h)" : "⚪ Vigencia extendida ({$dias}d)"),
                'justificacion' => "Tiene {$dias} días de maduración orgánica con {$pub->total_likes} likes y {$pub->total_comentarios} comentarios. Es el momento ideal para impulsarlo y alcanzar al {$coberturaEstimadaPct}% del padrón departamental.",
            ];
        });

        return [
            'candidato' => $candidatoPropio ? [
                'id' => $candidatoPropio->id,
                'nombre_completo' => $candidatoPropio->nombre_completo,
                'partido_coalicion' => $candidatoPropio->partido_coalicion,
                'cargo_aspirado' => $candidatoPropio->cargo_aspirado,
                'estado_politico' => $candidatoPropio->estado_politico,
                'avatar_url' => $candidatoPropio->avatar_url,
                'color_hex' => $candidatoPropio->color_hex,
            ] : null,
            'territorioActivo' => [
                'id' => $territorioActivo?->id,
                'nombre' => $territorioActivo?->nombre ?? 'Departamento Local',
                'tipo' => $territorioActivo?->tipo ?? 'departamento',
                'padron_electoral' => $padronElectoral,
                'poblacion_total' => $poblacionTotal,
                'poblacion_urbana_pct' => (float) ($territorioActivo?->poblacion_urbana_pct ?? 85),
                'poblacion_rural_pct' => (float) ($territorioActivo?->poblacion_rural_pct ?? 15),
                'meta_votos' => $metaVotos,
            ],
            'departamentos' => $departamentos->map(fn ($d) => [
                'id' => $d->id,
                'nombre' => $d->nombre,
                'padron_electoral' => (int) $d->padron_electoral,
                'poblacion_total' => (int) $d->poblacion_total,
            ]),
            'piramide' => $piramide,
            'cruceFranjas' => $cruceFranjas,
            'alertaBalanceRedes' => $alertaBalanceRedes,
            'stats' => [
                'padron_electoral' => $padronElectoral,
                'poblacion_total' => $poblacionTotal,
                'meta_votos' => $metaVotos,
                'total_seguidores_bruto' => $totalSeguidoresBruto,
                'total_seguidores_comunidad' => $totalSeguidores,
                'penetracion_padron_pct' => $penetracionPadronPct,
                'penetracion_padron_bruta_pct' => $penetracionPadronBrutaPct,
                'cobertura_meta_pct' => $coberturaMetaPct,
                'tiers_desglose' => $tiersDesglose,
                'score_impacto_total' => $scoreImpactoTotal,
                'score_impacto_meta' => $scoreImpactoMeta,
                'score_impacto_pct' => $scoreImpactoPct,
                'score_impacto_base_texto' => $scoreImpactoBaseTexto,
                'total_publicaciones' => $totalPosts,
                'total_interacciones' => $totalInteracciones,
                'total_likes' => $totalLikes,
                'total_comentarios' => $totalComentarios,
                'total_compartidos' => $totalCompartidos,
                'total_republicados' => $totalRepublicados,
                'total_pauta_invertida' => $totalPautaInvertida,
                'costo_por_interaccion' => $totalInteracciones > 0 ? round($totalPautaInvertida / $totalInteracciones, 2) : 0,
                'promedio_interacciones_por_post' => $promedioInteraccionesPorPost,
                'promedio_likes_por_post' => $promedioLikesPorPost,
                'promedio_comentarios_por_post' => $promedioComentariosPorPost,
                'pico_maximo_interacciones' => $picoMaximoInteracciones,
                'pico_maximo_post_resumen' => $picoMaximoPost?->contenido_resumen,
                'tasa_movilizacion_promedio_pct' => $tasaMovilizacionPromedioPct,
                'tasa_movilizacion_pico_pct' => $tasaMovilizacionPicoPct,
            ],
            'pasteles' => [
                'padron' => $pastelPadron,
                'redes' => $pastelRedes,
                'electorado' => $pastelElectorado,
            ],
            'inteligenciaTiempo' => $inteligenciaTiempo,
            'redesImpacto' => $redesImpacto,
            'oportunidadesPauta' => $oportunidadesPauta,
        ];
    }
}
