<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\PerfilSocialMetrica;
use App\Models\Publicacion;
use App\Models\PublicacionPautaEvento;
use App\Models\Territorio;
use App\Services\MediaStorageService;
use App\Services\SocialProfileScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CandidatoController extends Controller
{
    /**
     * Catálogo de la Oposición y Candidatos Rivales (Competencia) del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $cicloId = $request->input('ciclo_id');
        $estado = $request->input('estado');

        // Filtrar exclusivamente a los opositores / rivales del workspace
        $query = Candidato::where('workspace_id', $workspace->id)
            ->where('es_propio', false)
            ->with(['cicloCampana', 'territorio', 'perfilesSociales']);

        if ($cicloId) {
            $query->where('ciclo_campana_id', $cicloId);
        }

        if ($estado) {
            $query->where('estado_politico', $estado);
        }

        $candidatos = $query->orderBy('nombre_completo')
            ->get()
            ->map(function ($c) {
                $totalSeguidores = $c->perfilesSociales->sum('seguidores_actuales');

                return [
                    'id' => $c->id,
                    'nombre_completo' => $c->nombre_completo,
                    'partido_coalicion' => $c->partido_coalicion,
                    'cargo_aspirado' => $c->cargo_aspirado,
                    'estado_politico' => $c->estado_politico,
                    'color_hex' => $c->color_hex,
                    'es_propio' => false,
                    'avatar_url' => $c->avatar_url,
                    'bio_resumen' => $c->bio_resumen,
                    'ciclo_campana' => $c->cicloCampana?->nombre,
                    'ciclo_campana_id' => $c->ciclo_campana_id,
                    'territorio' => $c->territorio?->nombre,
                    'territorio_id' => $c->territorio_id,
                    'total_seguidores' => $totalSeguidores,
                    'perfiles_count' => $c->perfilesSociales->count(),
                    'perfiles' => $c->perfilesSociales->map(fn ($p) => [
                        'id' => $p->id,
                        'plataforma' => $p->plataforma,
                        'handle_usuario' => $p->handle_usuario,
                        'seguidores_actuales' => $p->seguidores_actuales,
                        'esta_verificado' => $p->esta_verificado,
                        'esta_activo' => $p->esta_activo,
                    ]),
                ];
            });

        $ciclos = CicloCampana::where('workspace_id', $workspace->id)
            ->orderByDesc('anio')
            ->get(['id', 'anio', 'nombre', 'es_activo']);

        $territorios = Territorio::where('workspace_id', $workspace->id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'tipo']);

        return Inertia::render('Candidatos/Index', [
            'candidatos' => $candidatos,
            'ciclos' => $ciclos,
            'territorios' => $territorios,
            'filtros' => [
                'ciclo_id' => $cicloId,
                'estado' => $estado,
            ],
            'estados_disponibles' => [
                ['key' => 'opositor', 'label' => 'Opositor Principal'],
                ['key' => 'candidato', 'label' => 'Candidato Rival Oficial'],
                ['key' => 'precandidato', 'label' => 'Precandidato (Interna Opositora)'],
                ['key' => 'intendente_electo', 'label' => 'Intendente Electo'],
                ['key' => 'gobernador_electo', 'label' => 'Gobernador Electo'],
                ['key' => 'en_funciones', 'label' => 'En Gestión'],
                ['key' => 'inactivo', 'label' => 'Inactivo'],
            ],
        ]);
    }

    /**
     * Benchmarking comparativo de crecimiento neto entre el candidato propio y los rivales.
     * Muestra quién crece más rápido en cada red social desde el Punto Cero.
     */
    public function benchmarking(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);

        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->with(['perfilesSociales', 'territorio'])
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre_completo,
                'partido' => $c->partido_coalicion,
                'cargo' => $c->cargo_aspirado,
                'es_propio' => $c->es_propio,
                'color' => $c->color_hex ?? ($c->es_propio ? '#06b6d4' : '#8b5cf6'),
                'avatar' => $c->avatar_url,
                'territorio' => $c->territorio?->nombre,
                'redes' => $c->perfilesSociales->map(fn ($p) => [
                    'plataforma' => $p->plataforma,
                    'handle' => $p->handle_usuario,
                    'seguidores_actuales' => $p->seguidores_actuales,
                    'seguidores_punto_cero' => $p->seguidores_punto_cero ?? 0,
                    'crecimiento_neto' => $p->seguidores_actuales - ($p->seguidores_punto_cero ?? 0),
                    'crecimiento_pct' => ($p->seguidores_punto_cero ?? 0) > 0
                        ? round((($p->seguidores_actuales - $p->seguidores_punto_cero) / $p->seguidores_punto_cero) * 100, 1)
                        : 0,
                    'esta_activo' => $p->esta_activo,
                    'fecha_punto_cero' => $p->fecha_punto_cero,
                ]),
            ]);

        return Inertia::render('Candidatos/Benchmarking', [
            'candidatos' => $candidatos,
            'workspace' => [
                'id' => $workspace->id,
                'nombre' => $workspace->nombre,
                'nivel_label' => $workspace->nivel_politico_label,
            ],
        ]);
    }

    /**
     * Vista de Gestión Exclusiva del Perfil Propio (Cliente / Campaña) del Workspace Activo.
     */
    public function miCandidato(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);

        $cicloActivo = CicloCampana::where('workspace_id', $workspace->id)->where('es_activo', true)->first()
            ?? CicloCampana::where('workspace_id', $workspace->id)->first();

        $territorioDefault = Territorio::where('workspace_id', $workspace->id)->first();

        // Buscar o inicializar el candidato propio del workspace
        $candidato = Candidato::where('workspace_id', $workspace->id)
            ->where('es_propio', true)
            ->with(['perfilesSociales', 'territorio', 'cicloCampana'])
            ->first();

        if (! $candidato) {
            $cargoPorDefecto = match ($workspace->nivel_politico) {
                'gobernador' => 'Candidato a Gobernador',
                'legislador_nacional' => 'Candidato a Diputado Nacional',
                'senador' => 'Candidato a Senador Nacional',
                'concejal' => 'Candidato a Concejal',
                default => 'Candidato a Intendente',
            };

            $candidato = Candidato::create([
                'workspace_id' => $workspace->id,
                'nombre_completo' => 'Mi Candidato ('.$workspace->nombre.')',
                'partido_coalicion' => 'Frente de Campaña',
                'cargo_aspirado' => $cargoPorDefecto,
                'estado_politico' => 'candidato',
                'ciclo_campana_id' => $cicloActivo?->id,
                'territorio_id' => $territorioDefault?->id,
                'color_hex' => '#06b6d4',
                'es_propio' => true,
                'bio_resumen' => 'Perfil del candidato oficial de campaña. Auditoría y seguimiento de crecimiento desde el Punto Cero.',
            ]);
            $candidato->load(['perfilesSociales', 'territorio', 'cicloCampana']);
        }

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

        // Mapear cada plataforma asegurando que exista en la vista (con semáforo de 4 estados: Verde=Activa, Rojo=Inactiva, Gris=Sin Uso/Configurar, Azul=Verificada)
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
                'perfil_id' => $perfil?->id,
                'existe' => (bool) $perfil,
                'esta_activo' => $estaActivo,
                'esta_verificado' => $estaVerificado,
                'handle_usuario' => $perfil?->handle_usuario ?? '',
                'url_perfil' => $perfil?->url_perfil ?? '',
                'foto_perfil_url' => $perfil?->foto_perfil_url ?? $candidato->avatar_url,
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
                        'es_propio' => true,
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
                    'pauta_eventos' => $p->pautaEventos ? $p->pautaEventos->map(function ($ev) {
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
                            'delta_likes_atribuibles' => $ev->delta_likes_atribuibles,
                            'delta_comentarios_atribuibles' => $ev->delta_comentarios_atribuibles,
                            'costo_por_like' => $ev->costo_por_like,
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

        $padronElectoral = (int) ($candidato->territorio?->padron_electoral ?? 0);
        $penetracionNetaPct = $padronElectoral > 0 ? round(($seguidoresNetosEstimados / $padronElectoral) * 100, 1) : 0;
        $penetracionBrutaPct = $padronElectoral > 0 ? round(($totalSeguidoresBruto / $padronElectoral) * 100, 1) : 0;

        return Inertia::render('Candidatos/MiPerfil', [
            'candidato' => [
                'id' => $candidato->id,
                'nombre_completo' => $candidato->nombre_completo,
                'partido_coalicion' => $candidato->partido_coalicion,
                'cargo_aspirado' => $candidato->cargo_aspirado,
                'estado_politico' => $candidato->estado_politico,
                'color_hex' => $candidato->color_hex,
                'avatar_url' => $candidato->avatar_url,
                'bio_resumen' => $candidato->bio_resumen,
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
            ],
            'redes' => $redesMapeadas,
            'ciclos' => $ciclos,
            'territorios' => $territorios,
            'publicaciones' => $publicaciones,
            'ejes' => $ejes,
        ]);
    }

    /**
     * Guardar o actualizar la configuración de una red social y su Punto Cero.
     */
    public function storePerfilSocial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'candidato_id' => ['required', 'exists:candidatos,id'],
            'plataforma' => ['required', 'string'],
            'handle_usuario' => ['required', 'string', 'max:255'],
            'url_perfil' => ['nullable', 'url', 'max:1000'],
            'foto_perfil_url' => ['nullable', 'url', 'max:1000'],
            'esta_activo' => ['required', 'boolean'],
            'esta_verificado' => ['required', 'boolean'],
            'seguidores_actuales' => ['nullable', 'integer', 'min:0'],
            'seguidos_actuales' => ['nullable', 'integer', 'min:0'],
            'publicaciones_totales' => ['nullable', 'integer', 'min:0'],
            'me_gusta_totales' => ['nullable', 'integer', 'min:0'],
            'visualizaciones_totales' => ['nullable', 'integer', 'min:0'],
            'fecha_punto_cero' => ['nullable', 'date'],
            'seguidores_punto_cero' => ['nullable', 'integer', 'min:0'],
            'seguidos_punto_cero' => ['nullable', 'integer', 'min:0'],
            'publicaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
            'me_gusta_punto_cero' => ['nullable', 'integer', 'min:0'],
            'visualizaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
            'notas_punto_cero' => ['nullable', 'string'],
        ]);

        $perfil = PerfilSocial::updateOrCreate(
            [
                'candidato_id' => $validated['candidato_id'],
                'plataforma' => $validated['plataforma'],
            ],
            [
                'handle_usuario' => $validated['handle_usuario'],
                'url_perfil' => $validated['url_perfil'] ?? null,
                'foto_perfil_url' => $validated['foto_perfil_url'] ?? null,
                'esta_activo' => $validated['esta_activo'],
                'esta_verificado' => $validated['esta_verificado'],
                'seguidores_actuales' => (int) ($validated['seguidores_actuales'] ?? $validated['seguidores_punto_cero'] ?? 0),
                'seguidos_actuales' => (int) ($validated['seguidos_actuales'] ?? $validated['seguidos_punto_cero'] ?? 0),
                'publicaciones_totales' => (int) ($validated['publicaciones_totales'] ?? $validated['publicaciones_punto_cero'] ?? 0),
                'me_gusta_totales' => (int) ($validated['me_gusta_totales'] ?? $validated['me_gusta_punto_cero'] ?? 0),
                'visualizaciones_totales' => (int) ($validated['visualizaciones_totales'] ?? $validated['visualizaciones_punto_cero'] ?? 0),
                'fecha_punto_cero' => $validated['fecha_punto_cero'] ?? now(),
                'seguidores_punto_cero' => (int) ($validated['seguidores_punto_cero'] ?? $validated['seguidores_actuales'] ?? 0),
                'seguidos_punto_cero' => (int) ($validated['seguidos_punto_cero'] ?? $validated['seguidos_actuales'] ?? 0),
                'publicaciones_punto_cero' => (int) ($validated['publicaciones_punto_cero'] ?? $validated['publicaciones_totales'] ?? 0),
                'me_gusta_punto_cero' => (int) ($validated['me_gusta_punto_cero'] ?? $validated['me_gusta_totales'] ?? 0),
                'visualizaciones_punto_cero' => (int) ($validated['visualizaciones_punto_cero'] ?? $validated['visualizaciones_totales'] ?? 0),
                'notas_punto_cero' => $validated['notas_punto_cero'] ?? null,
            ]
        );

        if (! empty($validated['foto_perfil_url'])) {
            $candidato = Candidato::find($validated['candidato_id']);
            if ($candidato) {
                $candidato->update(['avatar_url' => $validated['foto_perfil_url']]);
            }
        }

        // Registrar medición inicial en el histórico
        $perfil->registrarMedicion([
            'seguidores' => $perfil->seguidores_actuales,
            'seguidos' => $perfil->seguidos_actuales,
            'publicaciones' => $perfil->publicaciones_totales,
            'me_gusta_totales' => $perfil->me_gusta_totales,
            'visualizaciones_totales' => $perfil->visualizaciones_totales,
            'foto_perfil_url' => $perfil->foto_perfil_url,
        ], 'manual');

        return redirect()->back()
            ->with('success', "Canal {$validated['plataforma']} configurado y Punto Cero establecido.");
    }

    /**
     * Auto-extracción de datos desde el enlace público del perfil.
     */
    public function scrapePerfilSocial(Request $request, SocialProfileScraperService $scraper): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'url'],
            'plataforma' => ['required', 'string'],
        ]);

        $data = $scraper->scrapeProfile($request->input('url'), $request->input('plataforma'));

        return response()->json($data);
    }

    /**
     * Re-auditar / Refrescar métricas en vivo para un perfil social específico.
     */
    public function refrescarPerfilSocial(Request $request, PerfilSocial $perfilSocial, SocialProfileScraperService $scraper): JsonResponse|RedirectResponse
    {
        if (empty($perfilSocial->url_perfil)) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El canal no tiene una URL configurada para auditar.',
                ], 422);
            }

            return redirect()->back()->with('error', 'El canal no tiene una URL configurada para auditar.');
        }

        $scraped = $scraper->scrapeProfile($perfilSocial->url_perfil, $perfilSocial->plataforma);

        $metrica = $perfilSocial->registrarMedicion($scraped, 'auto_scraper');

        $deltaSeguidores = $metrica->crecimiento_seguidores_dia;
        $signo = $deltaSeguidores > 0 ? '+' : '';
        $deltaMsg = $deltaSeguidores != 0 ? " ({$signo}{$deltaSeguidores} seguidores hoy)" : '';
        $msg = "¡{$perfilSocial->plataforma} auditado con éxito!{$deltaMsg}";

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'mensaje' => $msg,
                'metrica' => $metrica,
                'perfil' => $perfilSocial->fresh(),
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Sincronización Maestra del Canal (Seguidores + Publicaciones activas en ventana <=15 días + snapshots de corte).
     */
    public function sincronizarCanal(
        Request $request,
        PerfilSocial $perfilSocial,
        SocialProfileScraperService $scraper,
        MediaStorageService $mediaStorage
    ): JsonResponse {
        @set_time_limit(180);
        $resultadoCanal = [
            'success' => true,
            'plataforma' => $perfilSocial->plataforma,
            'seguidores_actuales' => (int) $perfilSocial->seguidores_actuales,
            'delta_seguidores' => 0,
            'mensaje_seguidores' => '',
            'posts_total' => 0,
            'posts_actualizados' => 0,
            'nuevos_likes' => 0,
            'nuevos_comentarios' => 0,
            'cambios_pauta' => 0,
            'logs' => [],
        ];

        // PASO 1: Sincronizar Seguidores del Perfil Social (si tiene URL)
        if (! empty($perfilSocial->url_perfil)) {
            try {
                $scrapedPerfil = $scraper->scrapeProfile($perfilSocial->url_perfil, $perfilSocial->plataforma);
                $metrica = $perfilSocial->registrarMedicion($scrapedPerfil, 'sync_maestro');
                $deltaSeg = (int) $metrica->crecimiento_seguidores_dia;
                $signo = $deltaSeg > 0 ? '+' : '';
                $resultadoCanal['seguidores_actuales'] = (int) $metrica->seguidores;
                $resultadoCanal['delta_seguidores'] = $deltaSeg;
                $resultadoCanal['mensaje_seguidores'] = "Seguidores actualizados: " . number_format($metrica->seguidores, 0, ',', '.') . ($deltaSeg != 0 ? " ({$signo}{$deltaSeg} hoy)" : '');
            } catch (\Throwable $e) {
                $resultadoCanal['mensaje_seguidores'] = "No se pudo actualizar seguidores: " . $e->getMessage();
            }
        } else {
            $resultadoCanal['mensaje_seguidores'] = 'Canal sin URL de perfil para escanear seguidores.';
        }

        // PASO 2: Sincronizar publicaciones en ventana activa (<= 15 días)
        $fechaLimite = Carbon::now()->subDays(15)->startOfDay();
        $publicaciones = Publicacion::where('perfil_social_id', $perfilSocial->id)
            ->where('fecha_publicacion', '>=', $fechaLimite)
            ->whereNotNull('url_post')
            ->where('url_post', '!=', '')
            ->get();

        $resultadoCanal['posts_total'] = $publicaciones->count();

        foreach ($publicaciones as $pub) {
            try {
                $scrapedPost = $scraper->scrapePost($pub->url_post, $perfilSocial->plataforma);
                if (! empty($scrapedPost['success'])) {
                    $oldLikes = (int) $pub->total_likes;
                    $oldComments = (int) $pub->total_comentarios;
                    $freshLikes = (int) ($scrapedPost['total_likes'] ?? $oldLikes);
                    $freshComments = (int) ($scrapedPost['total_comentarios'] ?? $oldComments);

                    $deltaL = max(0, $freshLikes - $oldLikes);
                    $deltaC = max(0, $freshComments - $oldComments);

                    $resultadoCanal['nuevos_likes'] += $deltaL;
                    $resultadoCanal['nuevos_comentarios'] += $deltaC;

                    $pubUpdate = [
                        'total_likes' => $freshLikes,
                        'total_comentarios' => $freshComments,
                    ];

                    // Recalcular emociones con la función pública del PublicacionController
                    $aiEmocional = app(PublicacionController::class)->calcularInteligenciaEmocional([], $freshLikes, $perfilSocial->plataforma);
                    $pubUpdate['reacciones_detalladas'] = $aiEmocional['reacciones_detalladas'];
                    $pubUpdate['sentimiento_predominante'] = $aiEmocional['sentimiento_predominante'];
                    $pubUpdate['termometro_humor_social'] = $aiEmocional['termometro_humor_social'];

                    if (! empty($scrapedPost['media_url'])) {
                        $localMedia = $mediaStorage->guardarMediaLocal($scrapedPost['media_url'], $pub->media_url);
                        if ($localMedia) {
                            $pubUpdate['media_url'] = $localMedia;
                        }
                    }

                    $pub->update($pubUpdate);
                    $resultadoCanal['posts_actualizados']++;

                    $resultadoCanal['logs'][] = [
                        'status' => 'success',
                        'url' => $pub->url_post,
                        'resumen' => Str::limit($pub->contenido_resumen, 45),
                        'likes' => $freshLikes,
                        'deltaLikes' => $deltaL,
                        'comments' => $freshComments,
                        'deltaComments' => $deltaC,
                        'fecha' => $pub->fecha_publicacion?->format('d/m/Y') ?? 'Reciente',
                    ];
                } else {
                    $resultadoCanal['logs'][] = [
                        'status' => 'warning',
                        'url' => $pub->url_post,
                        'resumen' => Str::limit($pub->contenido_resumen, 45),
                        'error' => $scrapedPost['mensaje'] ?? 'Sin datos nuevos.',
                    ];
                }
            } catch (\Throwable $e) {
                $resultadoCanal['logs'][] = [
                    'status' => 'error',
                    'url' => $pub->url_post,
                    'resumen' => Str::limit($pub->contenido_resumen, 45),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($resultadoCanal);
    }

    /**
     * Actualizar perfil social existente.
     */
    public function updatePerfilSocial(Request $request, PerfilSocial $perfilSocial): RedirectResponse
    {
        $validated = $request->validate([
            'handle_usuario' => ['required', 'string', 'max:255'],
            'url_perfil' => ['nullable', 'url', 'max:1000'],
            'foto_perfil_url' => ['nullable', 'url', 'max:1000'],
            'esta_activo' => ['required', 'boolean'],
            'esta_verificado' => ['required', 'boolean'],
            'seguidores_actuales' => ['nullable', 'integer', 'min:0'],
            'seguidos_actuales' => ['nullable', 'integer', 'min:0'],
            'publicaciones_totales' => ['nullable', 'integer', 'min:0'],
            'me_gusta_totales' => ['nullable', 'integer', 'min:0'],
            'visualizaciones_totales' => ['nullable', 'integer', 'min:0'],
            'fecha_punto_cero' => ['nullable', 'date'],
            'seguidores_punto_cero' => ['nullable', 'integer', 'min:0'],
            'seguidos_punto_cero' => ['nullable', 'integer', 'min:0'],
            'publicaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
            'me_gusta_punto_cero' => ['nullable', 'integer', 'min:0'],
            'visualizaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
            'notas_punto_cero' => ['nullable', 'string'],
        ]);

        $perfilSocial->update($validated);

        return redirect()->back()
            ->with('success', "Datos y Punto Cero de {$perfilSocial->plataforma} actualizados correctamente.");
    }

    /**
     * Eliminar perfil social.
     */
    public function destroyPerfilSocial(PerfilSocial $perfilSocial): RedirectResponse
    {
        $plataforma = $perfilSocial->plataforma;
        $perfilSocial->delete();

        return redirect()->back()
            ->with('success', "Canal {$plataforma} desvinculado.");
    }

    /**
     * Ficha técnica y gestión de canales / Punto Cero de un candidato rival.
     */
    public function show(Request $request, Candidato $candidato): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidato->load(['cicloCampana', 'territorio', 'perfilesSociales']);

        $plataformasEstandar = [
            'instagram' => ['nombre' => 'Instagram', 'formato_default' => 'Reel'],
            'facebook' => ['nombre' => 'Facebook', 'formato_default' => 'Post/Foto'],
            'threads' => ['nombre' => 'Threads', 'formato_default' => 'Post'],
            'tiktok' => ['nombre' => 'TikTok', 'formato_default' => 'Video Corto'],
            'x_twitter' => ['nombre' => 'X (Twitter)', 'formato_default' => 'Tweet'],
            'youtube' => ['nombre' => 'YouTube', 'formato_default' => 'Video/Shorts'],
            'linkedin' => ['nombre' => 'LinkedIn', 'formato_default' => 'Artículo'],
        ];

        $redesMapeadas = collect($plataformasEstandar)->map(function ($info, $key) use ($candidato) {
            $perfil = $candidato->perfilesSociales->firstWhere('plataforma', $key);

            $existe = (bool) $perfil && ! empty($perfil->handle_usuario);
            $estaActivo = $perfil ? (bool) $perfil->esta_activo : false;
            $estaVerificado = $perfil ? (bool) $perfil->esta_verificado : false;

            if (! $existe) {
                // ⚪ Sin uso / No configurada (Gris)
                $colorEstado = 'gris';
            } elseif ($estaVerificado) {
                // 🔵 Certificada / Verificada (Azul)
                $colorEstado = 'azul';
            } elseif ($estaActivo) {
                // 🟢 Activa con movimiento de campaña (Verde)
                $colorEstado = 'verde';
            } else {
                // 🔴 Inactiva / Sin movimiento (Rojo)
                $colorEstado = 'rojo';
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
                'perfil_id' => $perfil?->id,
                'existe' => (bool) $perfil,
                'esta_activo' => $estaActivo,
                'esta_verificado' => $estaVerificado,
                'handle_usuario' => $perfil?->handle_usuario ?? '',
                'url_perfil' => $perfil?->url_perfil ?? '',
                'foto_perfil_url' => $perfil?->foto_perfil_url ?? $candidato->avatar_url,
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
            ];
        })->values();

        $ciclos = CicloCampana::where('workspace_id', $workspace->id)
            ->orderByDesc('anio')
            ->get(['id', 'anio', 'nombre', 'es_activo']);

        $territorios = Territorio::where('workspace_id', $workspace->id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'tipo', 'poblacion_total', 'padron_electoral']);

        return Inertia::render('Candidatos/Show', [
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
                'ciclo_campana_id' => $candidato->ciclo_campana_id,
                'territorio_id' => $candidato->territorio_id,
                'territorio' => $candidato->territorio,
                'total_seguidores' => $candidato->perfilesSociales->where('esta_activo', true)->sum('seguidores_actuales'),
                'total_publicaciones' => $candidato->perfilesSociales->where('esta_activo', true)->sum('publicaciones_totales'),
            ],
            'redes' => $redesMapeadas,
            'ciclos' => $ciclos,
            'territorios' => $territorios,
        ]);
    }

    /**
     * Registrar un nuevo candidato opositor / rival en el workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'partido_coalicion' => ['required', 'string', 'max:255'],
            'cargo_aspirado' => ['nullable', 'string', 'max:255'],
            'estado_politico' => ['required', 'string'],
            'ciclo_campana_id' => ['required', 'exists:ciclo_campanas,id'],
            'territorio_id' => ['nullable', 'exists:territorios,id'],
            'color_hex' => ['nullable', 'string', 'max:10'],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'bio_resumen' => ['nullable', 'string'],
        ]);

        $candidato = Candidato::create([
            'workspace_id' => $workspace->id,
            'nombre_completo' => $validated['nombre_completo'],
            'partido_coalicion' => $validated['partido_coalicion'],
            'cargo_aspirado' => $validated['cargo_aspirado'] ?? null,
            'estado_politico' => $validated['estado_politico'],
            'ciclo_campana_id' => $validated['ciclo_campana_id'],
            'territorio_id' => $validated['territorio_id'] ?? null,
            'color_hex' => $validated['color_hex'] ?? '#8b5cf6',
            'es_propio' => false,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'bio_resumen' => $validated['bio_resumen'] ?? null,
        ]);

        return redirect()->route('candidatos.show', $candidato->id)
            ->with('success', "Candidato rival {$candidato->nombre_completo} registrado. Ahora puedes vincular sus redes sociales y Punto Cero.");
    }

    /**
     * Actualizar datos del candidato y su territorio geográfico.
     */
    public function update(Request $request, Candidato $candidato): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'workspace_nombre' => ['nullable', 'string', 'max:255'],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'partido_coalicion' => ['required', 'string', 'max:255'],
            'cargo_aspirado' => ['nullable', 'string', 'max:255'],
            'estado_politico' => ['required', 'string'],
            'ciclo_campana_id' => ['nullable', 'exists:ciclo_campanas,id'],
            'territorio_id' => ['nullable'],
            'territorio_nombre' => ['nullable', 'string', 'max:255'],
            'padron_electoral' => ['nullable', 'integer', 'min:0'],
            'poblacion_total' => ['nullable', 'integer', 'min:0'],
            'tipo_territorio' => ['nullable', 'string', 'max:50'],
            'color_hex' => ['nullable', 'string', 'max:10'],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'bio_resumen' => ['nullable', 'string'],
        ]);

        if (! empty($validated['workspace_nombre'])) {
            $workspace->update(['nombre' => $validated['workspace_nombre']]);
        }

        $territorioId = $validated['territorio_id'] ?? $candidato->territorio_id;

        // Si se envió un nombre de territorio nuevo o editado
        if (! empty($validated['territorio_nombre'])) {
            $territorio = Territorio::updateOrCreate(
                ['id' => $territorioId ?: null, 'workspace_id' => $workspace->id],
                [
                    'workspace_id' => $workspace->id,
                    'nombre' => $validated['territorio_nombre'],
                    'padron_electoral' => $validated['padron_electoral'] ?? 0,
                    'poblacion_total' => $validated['poblacion_total'] ?? 0,
                    'tipo' => $validated['tipo_territorio'] ?? 'municipio',
                ]
            );
            $territorioId = $territorio->id;
        }

        $candidato->update([
            'nombre_completo' => $validated['nombre_completo'],
            'partido_coalicion' => $validated['partido_coalicion'],
            'cargo_aspirado' => $validated['cargo_aspirado'] ?? null,
            'estado_politico' => $validated['estado_politico'],
            'ciclo_campana_id' => $validated['ciclo_campana_id'] ?? $candidato->ciclo_campana_id,
            'territorio_id' => $territorioId,
            'color_hex' => $validated['color_hex'] ?? $candidato->color_hex,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'bio_resumen' => $validated['bio_resumen'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', "Datos y territorio geográfico de {$candidato->nombre_completo} actualizados correctamente.");
    }

    /**
     * Eliminar un candidato.
     */
    public function destroy(Candidato $candidato): RedirectResponse
    {
        $nombre = $candidato->nombre_completo;
        $candidato->delete();

        return redirect()->route('candidatos.index')
            ->with('success', "Candidato {$nombre} eliminado satisfactoriamente.");
    }

    /**
     * Rangos de referencia de la industria para campañas políticas digitales.
     */
    /**
     * Rangos de referencia de la industria escalonados por tramo de seguidores (Nano / Medio / Macro).
     */
    private const BENCHMARK_TIERS = [
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
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '4 a 7 posts/semana (Cercanía vecinal)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 6,
                'posts_semana_ideal' => 5,
                'engagement_min' => 0.8,
                'engagement_ideal' => 2.0,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '3 a 6 posts/semana (Fotos + Videos)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 4,
                'engagement_min' => 0.4,
                'engagement_ideal' => 1.0,
                'vistas_ratio_reel' => 0.05,
                'descripcion_cadencia' => '3 a 5 posts/semana (Masivo)',
            ],
        ],
        'threads' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 7,
                'posts_semana_ideal' => 5,
                'engagement_min' => 3.0,
                'engagement_ideal' => 7.0,
                'vistas_ratio_reel' => 0.15,
                'descripcion_cadencia' => '4 a 7 posts/semana (Conversación ágil y debate)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 6,
                'posts_semana_ideal' => 4,
                'engagement_min' => 1.5,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '3 a 6 posts/semana (Opinión y actualidad)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 4,
                'engagement_min' => 0.8,
                'engagement_ideal' => 2.0,
                'vistas_ratio_reel' => 0.06,
                'descripcion_cadencia' => '3 a 5 posts/semana (Contenido masivo)',
            ],
        ],
        'tiktok' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 10,
                'posts_semana_ideal' => 7,
                'engagement_min' => 5.0,
                'engagement_ideal' => 12.0,
                'vistas_ratio_reel' => 0.40,
                'descripcion_cadencia' => '5 a 10 videos cortos/semana (Viralidad joven)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 9,
                'posts_semana_ideal' => 7,
                'engagement_min' => 3.0,
                'engagement_ideal' => 7.0,
                'vistas_ratio_reel' => 0.30,
                'descripcion_cadencia' => '5 a 9 videos/semana',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 7,
                'posts_semana_ideal' => 5,
                'engagement_min' => 2.0,
                'engagement_ideal' => 5.0,
                'vistas_ratio_reel' => 0.20,
                'descripcion_cadencia' => '4 a 7 videos/semana',
            ],
        ],
        'youtube' => [
            'nano' => [
                'label' => 'Nano (<10k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 2.5,
                'engagement_ideal' => 6.0,
                'vistas_ratio_reel' => 0.30,
                'descripcion_cadencia' => '2 a 4 videos/Shorts semanales',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 1,
                'posts_semana_max' => 3,
                'posts_semana_ideal' => 2,
                'engagement_min' => 1.5,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.20,
                'descripcion_cadencia' => '1 a 3 videos/Shorts semanales',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 1,
                'posts_semana_max' => 2,
                'posts_semana_ideal' => 2,
                'engagement_min' => 1.0,
                'engagement_ideal' => 2.5,
                'vistas_ratio_reel' => 0.12,
                'descripcion_cadencia' => '1 a 2 videos semanales',
            ],
        ],
        'x_twitter' => [
            'nano' => [
                'label' => 'Nano (<10k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 15,
                'posts_semana_ideal' => 10,
                'engagement_min' => 1.0,
                'engagement_ideal' => 3.0,
                'vistas_ratio_reel' => 0.06,
                'descripcion_cadencia' => '5 a 15 tweets/semana (Opinión + Respuestas)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 7,
                'posts_semana_max' => 20,
                'posts_semana_ideal' => 12,
                'engagement_min' => 0.5,
                'engagement_ideal' => 1.5,
                'vistas_ratio_reel' => 0.04,
                'descripcion_cadencia' => '7 a 20 tweets/semana (Opinión + Hilos)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 10,
                'posts_semana_max' => 30,
                'posts_semana_ideal' => 15,
                'engagement_min' => 0.3,
                'engagement_ideal' => 1.0,
                'vistas_ratio_reel' => 0.02,
                'descripcion_cadencia' => '10 a 30 tweets/semana',
            ],
        ],
        'linkedin' => [
            'nano' => [
                'label' => 'Nano (<10k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 3.0,
                'engagement_ideal' => 7.0,
                'vistas_ratio_reel' => 0.15,
                'descripcion_cadencia' => '2 a 4 posts técnicos/semana',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 2.0,
                'engagement_ideal' => 5.0,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '2 a 4 posts técnicos/semana',
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
     * Dashboard Analítico Avanzado para un Canal de Red Social específico.
     * Implementa el paradigma Territorio-First (Padrón Electoral como Universo Rector).
     */
    public function metricasCanal(Request $request, PerfilSocial $perfilSocial): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidato = $perfilSocial->candidato()->with('territorio')->first();

        // Validar acceso al workspace
        if ($candidato->workspace_id !== $workspace->id) {
            abort(403, 'No autorizado para ver este perfil.');
        }

        $ejes = EjeTematico::where('workspace_id', $workspace->id)->get();

        // 1. Cargar mediciones históricas time-series (orden ascendente por fecha para el gráfico)
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

        // 7. Orgánico vs Pauta (Desglose Estratégico)
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

        // 9. Consistencia Mensual & Auditoría de Cadencia (Desde el primer mes activo)
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

        // Buscar el primer mes activo en la serie histórica
        $primerMesActivoIdx = null;
        foreach ($consistenciaRaw as $idx => $m) {
            if ($m['posts_count'] > 0) {
                $primerMesActivoIdx = $idx;
                break;
            }
        }

        $consistenciaMensual = [];
        if ($primerMesActivoIdx !== null) {
            // Solo incluir desde el primer mes de actividad en adelante (lo anterior a la campaña se omite)
            $mesesDesdeInicio = array_slice($consistenciaRaw, $primerMesActivoIdx);

            foreach ($mesesDesdeInicio as $m) {
                $cantPosts = $m['posts_count'];
                if ($cantPosts === 0) {
                    $estado = 'inactivo_perdido'; // Mes intermedio hueco / perdido en rojo
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
            // Si aún no hay publicaciones cargadas en ningún mes, se muestra el mes actual como pendiente
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

        // ══════════════════════════════════════════════════════════════════════════
        // 11. PARADIGMA TERRITORIO-FIRST: EL PADRÓN ELECTORAL COMO UNIVERSO RECTOR
        // ══════════════════════════════════════════════════════════════════════════
        $territorio = $candidato->territorio;
        $padronElectoral = (int) ($territorio?->padron_electoral ?: 24500);
        $poblacionTotal = (int) ($territorio?->poblacion_total ?: 31200);
        $nombreTerritorio = $territorio?->nombre ?: 'Territorio Asignado';

        // Metas de Campaña sobre el Padrón
        $metaCoberturaRegular = (int) round($padronElectoral * 0.30);   // 30% del padrón
        $metaCoberturaGanadora = (int) round($padronElectoral * 0.40);  // 40% del padrón
        $metaMovilizacionRegular = (int) round($padronElectoral * 0.08); // 8% del padrón
        $metaMovilizacionGanadora = (int) round($padronElectoral * 0.15); // 15% del padrón

        // Rendimiento en este mes / ciclo actual
        $postsEsteMesCollection = $publicaciones->filter(function ($p) use ($startOfMonth) {
            return $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo($startOfMonth);
        });

        // Si hay pocos posts este mes, tomar las últimas 8 publicaciones como muestra representativa
        $muestraReciente = $publicaciones->take(8);
        $vistasMuestra = (int) $muestraReciente->sum('total_vistas');
        $intMuestra = (int) $muestraReciente->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
        $promedioVistasPorPost = $muestraReciente->count() > 0 ? (int) round($vistasMuestra / $muestraReciente->count()) : 0;
        $promedioIntPorPost = $muestraReciente->count() > 0 ? (int) round($intMuestra / $muestraReciente->count()) : 0;

        // Vistas e interacciones acumuladas este mes
        $vistasEsteMes = (int) $postsEsteMesCollection->sum('total_vistas');
        if ($vistasEsteMes === 0 && $totalVistas > 0) {
            $vistasEsteMes = min($totalVistas, $promedioVistasPorPost * max(1, $postsEsteMes ?: 3));
        }
        $intEsteMes = (int) $postsEsteMesCollection->sum(fn ($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + (int) ($p->total_republicados ?? 0));
        if ($intEsteMes === 0 && $totalInteracciones > 0) {
            $intEsteMes = min($totalInteracciones, $promedioIntPorPost * max(1, $postsEsteMes ?: 3));
        }

        // Porcentajes de Cobertura y Movilización del Padrón
        $pctPadronAlcanzado = $padronElectoral > 0 ? round(($vistasEsteMes / $padronElectoral) * 100, 1) : 0;
        $pctPadronMovilizado = $padronElectoral > 0 ? round(($intEsteMes / $padronElectoral) * 100, 1) : 0;

        // Estado del Semáforo del Padrón
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

        // Calculadora de Ritmo Publicitario para alcanzar el Padrón
        $metaSemanalVistas = (int) round($metaCoberturaRegular / 4);
        $postsSemanaNecesarios = $promedioVistasPorPost > 0
            ? (int) max(1, ceil($metaSemanalVistas / $promedioVistasPorPost))
            : $benchmark['posts_semana_ideal'];
        $postsMesNecesarios = $promedioVistasPorPost > 0
            ? (int) max(4, ceil($metaCoberturaRegular / $promedioVistasPorPost))
            : ($benchmark['posts_semana_ideal'] * 4);

        $esAlcanzableSoloOrganico = ($promedioVistasPorPost * $benchmark['posts_semana_ideal'] * 4) >= $metaCoberturaRegular;

        // Detección de Éxito Viral en Post Individual (> 40% del padrón)
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
                'accion_sugerida' => "Este canal tiene tracción orgánica autosuficiente. Se aconseja pausar o reducir pauta aquí y redistribuir el presupuesto hacia canales con menor penetración.",
            ];
        } elseif ($postsPautados->count() > 0 && $intPautadas > 0 && $intOrganicas > $intPautadas * 2) {
            $alertaRedistribucionPauta = [
                'tipo' => 'optimizacion_organica',
                'mensaje' => "El rendimiento orgánico representa el {$organicoVsPauta['pct_interacciones_organicas']}% de la interacción total.",
                'accion_sugerida' => 'Concentrar la pauta en formatos tipo Reel con mensaje de propuesta o geolocalizar por circuitos con menor cobertura.',
            ];
        }

        // Costo por Elector Alcanzado (CEA)
        $electoresAlcanzadosEstimados = (int) min($padronElectoral, round($vistasEsteMes * 0.75));
        $costoPorElectorAlcanzado = ($inversionPauta > 0 && $electoresAlcanzadosEstimados > 0)
            ? round($inversionPauta / $electoresAlcanzadosEstimados, 2)
            : 0;

        // Semáforo del Padrón (Métricas Primarias de Campaña)
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
                        ? "🟡 Nivel regular de campaña (30% cubierto). Faltan " . number_format(max(0, $metaCoberturaGanadora - $vistasEsteMes), 0, ',', '.') . " visualizaciones para meta de victoria."
                        : "🔴 Cobertura insuficiente. Se requiere acelerar cadencia o pauta geolocalizada para superar el umbral del 30%."),
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
                    : "🟡 Movilización moderada. Fomentar debates, preguntas vecinales y contenido que incite comentarios.",
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
                    ? "🏆 Con tu promedio de " . number_format($promedioVistasPorPost, 0, ',', '.') . " vistas/post, alcanzás la meta del 30% publicando {$postsSemanaNecesarios} veces por semana solo de forma orgánica."
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

        // 12. Semáforo de Industria (Secundario / Referencia)
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
                'actual_formato' => number_format($promedioVistasReels, 0, ',', '.') . ' vistas prom.',
                'rango_ideal' => '≥ ' . number_format($vistasEsperadasBenchmark, 0, ',', '.') . ' vistas (' . ($vistasRatioEsperado * 100) . '% aud.)',
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
                'actual_formato' => ($publicaciones->count() > 0 ? number_format(round($totalInteracciones / $publicaciones->count()), 0, ',', '.') : '0') . ' / post',
                'rango_ideal' => '≥ ' . number_format(max(10, round($seguidoresActuales * 0.01)), 0, ',', '.') . ' int/post',
                'meta_valor' => max(10, round($seguidoresActuales * 0.01)),
                'estado' => ($publicaciones->count() > 0 && ($totalInteracciones / $publicaciones->count()) >= max(10, $seguidoresActuales * 0.01)) ? 'verde' : 'amarillo',
                'consejo' => 'Volumen de reacciones, compartidos y comentarios por publicación.',
            ],
        ];

        // 13. Demografía Interna de la Audiencia (Perfil Propio)
        $demografiaInterna = $perfilSocial->demografia_interna_propia;
        if (! $demografiaInterna && $perfilSocial->candidato->es_propio) {
            // Demografía fallback realista representativa para cuenta municipal en San Juan si no está cargada
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

        // Cruce Demográfico: Audiencia Digital vs Padrón Electoral con Ventana Móvil (30 días) y Récord Histórico
        $cruceDemografico = [];
        if ($territorio && $territorio->piramide_etaria && ! empty($demografiaInterna['franjas_etarias'])) {
            $gruposPadron = collect($territorio->piramide_etaria['grupos_etarios'] ?? []);
            
            // 1. Ventana Móvil Activa: Posts de los últimos 30 días
            $posts30d = $publicaciones->filter(function ($p) {
                return $p->fecha_publicacion && $p->fecha_publicacion->greaterThanOrEqualTo(now()->subDays(30));
            });
            // Si hay pocos posts en los últimos 30 días, tomar al menos los posts más recientes
            $muestraReciente = $posts30d->count() >= 3 ? $posts30d : ($publicaciones->take(10)->count() > 0 ? $publicaciones->take(10) : $publicaciones);
            $totalMuestraCount = $muestraReciente->count();

            $totalInteracciones30d = $muestraReciente->sum(fn ($p) => (int) $p->total_likes + (int) $p->total_comentarios + (int) $p->total_compartidos + (int) ($p->total_republicados ?? 0));
            $promedioInteracciones30d = $totalMuestraCount > 0 ? round($totalInteracciones30d / $totalMuestraCount, 1) : 0;
            $promedioVistas30d = $totalMuestraCount > 0 ? round($muestraReciente->avg('visualizaciones') ?: $promedioVistasPorPost) : $promedioVistasPorPost;

            // 2. Máximo Histórico Logrado (Mes Récord de Interacciones)
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

                // Cálculo Nominal contra el Padrón Electoral
                $electoresTotalesFranja = (int) round($padronElectoral * ($pctPadron / 100));
                $seguidoresEnFranja = (int) round($seguidoresActuales * ($pctAudiencia / 100));
                $coberturaPadronFranjaPct = $electoresTotalesFranja > 0
                    ? round(($seguidoresEnFranja / $electoresTotalesFranja) * 100, 1)
                    : 0;
                $electoresFaltantes = max(0, $electoresTotalesFranja - $seguidoresEnFranja);

                // Promedio actual (últimos 30 días) atribuible a esta franja
                $reacciones30d = round($promedioInteracciones30d * ($pctAudiencia / 100), 1);
                $vistas30d = round($promedioVistas30d * ($pctAudiencia / 100));

                // Récord histórico logrado atribuible a esta franja
                $reaccionesRecord = round($promedioInteraccionesPico * ($pctAudiencia / 100), 1);
                $pctVsRecord = $reaccionesRecord > 0 ? min(100, round(($reacciones30d / $reaccionesRecord) * 100)) : 100;

                // Diagnóstico Táctico de Necesidad de Pauta vs Cobertura Electoral
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

        // Plataformas estándar a auditar para selector en 1 fila
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

        // Color semáforo de la cuenta: Azul (Verificada), Verde (Activa), Rojo (Inactiva)
        $semaforoColor = $perfilSocial->esta_verificado ? 'azul' : ($perfilSocial->esta_activo ? 'verde' : 'rojo');

        return Inertia::render('Candidatos/MetricasCanal', [
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
        ]);
    }
}
