<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Publicacion;
use App\Models\Territorio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                'id'        => $c->id,
                'nombre'    => $c->nombre_completo,
                'partido'   => $c->partido_coalicion,
                'cargo'     => $c->cargo_aspirado,
                'es_propio' => $c->es_propio,
                'color'     => $c->color_hex ?? ($c->es_propio ? '#06b6d4' : '#8b5cf6'),
                'avatar'    => $c->avatar_url,
                'territorio'=> $c->territorio?->nombre,
                'redes'     => $c->perfilesSociales->map(fn ($p) => [
                    'plataforma'            => $p->plataforma,
                    'handle'                => $p->handle_usuario,
                    'seguidores_actuales'   => $p->seguidores_actuales,
                    'seguidores_punto_cero' => $p->seguidores_punto_cero ?? 0,
                    'crecimiento_neto'      => $p->seguidores_actuales - ($p->seguidores_punto_cero ?? 0),
                    'crecimiento_pct'       => ($p->seguidores_punto_cero ?? 0) > 0
                        ? round((($p->seguidores_actuales - $p->seguidores_punto_cero) / $p->seguidores_punto_cero) * 100, 1)
                        : 0,
                    'esta_activo'           => $p->esta_activo,
                    'fecha_punto_cero'      => $p->fecha_punto_cero,
                ]),
            ]);

        return Inertia::render('Candidatos/Benchmarking', [
            'candidatos' => $candidatos,
            'workspace'  => [
                'id'           => $workspace->id,
                'nombre'       => $workspace->nombre,
                'nivel_label'  => $workspace->nivel_politico_label,
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

        if (!$candidato) {
            $cargoPorDefecto = match ($workspace->nivel_politico) {
                'gobernador' => 'Candidato a Gobernador',
                'legislador_nacional' => 'Candidato a Diputado Nacional',
                'senador' => 'Candidato a Senador Nacional',
                'concejal' => 'Candidato a Concejal',
                default => 'Candidato a Intendente',
            };

            $candidato = Candidato::create([
                'workspace_id' => $workspace->id,
                'nombre_completo' => 'Mi Candidato (' . $workspace->nombre . ')',
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
            'tiktok' => ['nombre' => 'TikTok', 'formato_default' => 'Video Corto'],
            'x_twitter' => ['nombre' => 'X (Twitter)', 'formato_default' => 'Tweet'],
            'youtube' => ['nombre' => 'YouTube', 'formato_default' => 'Video/Shorts'],
            'linkedin' => ['nombre' => 'LinkedIn', 'formato_default' => 'Artículo'],
        ];

        // Mapear cada plataforma asegurando que exista en la vista (con semáforo de color)
        $redesMapeadas = collect($plataformasEstandar)->map(function ($info, $key) use ($candidato) {
            $perfil = $candidato->perfilesSociales->firstWhere('plataforma', $key);

            $estaActivo = $perfil ? (bool)$perfil->esta_activo : false;
            $estaVerificado = $perfil ? (bool)$perfil->esta_verificado : false;
            $colorEstado = $estaVerificado ? 'azul' : ($estaActivo ? 'naranja' : 'rojo');

            $seguidoresActuales = $perfil ? (int)$perfil->seguidores_actuales : 0;
            $seguidoresBaseline = $perfil ? (int)$perfil->seguidores_punto_cero : 0;
            $crecimientoSeguidores = $seguidoresActuales - $seguidoresBaseline;

            $postsActuales = $perfil ? (int)$perfil->publicaciones_totales : 0;
            $postsBaseline = $perfil ? (int)$perfil->publicaciones_punto_cero : 0;
            $crecimientoPosts = $postsActuales - $postsBaseline;

            $meGustaActuales = $perfil ? (int)$perfil->me_gusta_totales : 0;
            $meGustaBaseline = $perfil ? (int)$perfil->me_gusta_punto_cero : 0;
            $crecimientoMeGusta = $meGustaActuales - $meGustaBaseline;

            $viewsActuales = $perfil ? (int)$perfil->visualizaciones_totales : 0;
            $viewsBaseline = $perfil ? (int)$perfil->visualizaciones_punto_cero : 0;
            $crecimientoViews = $viewsActuales - $viewsBaseline;

            return [
                'key' => $key,
                'nombre' => $info['nombre'],
                'color_estado' => $colorEstado,
                'perfil_id' => $perfil?->id,
                'existe' => (bool)$perfil,
                'esta_activo' => $estaActivo,
                'esta_verificado' => $estaVerificado,
                'handle_usuario' => $perfil?->handle_usuario ?? '',
                'url_perfil' => $perfil?->url_perfil ?? '',
                'foto_perfil_url' => $perfil?->foto_perfil_url ?? $candidato->avatar_url,
                'seguidores_actuales' => $seguidoresActuales,
                'seguidos_actuales' => $perfil ? (int)$perfil->seguidos_actuales : 0,
                'publicaciones_totales' => $postsActuales,
                'me_gusta_totales' => $meGustaActuales,
                'visualizaciones_totales' => $viewsActuales,
                // Punto Cero (Baseline Inicial)
                'fecha_punto_cero' => $perfil?->fecha_punto_cero ? $perfil->fecha_punto_cero->format('Y-m-d') : date('Y-m-d'),
                'seguidores_punto_cero' => $seguidoresBaseline,
                'seguidos_punto_cero' => $perfil ? (int)$perfil->seguidos_punto_cero : 0,
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
                'delta_seguidores_hoy' => (int)($perfil?->delta_seguidores_24h ?? 0),
                'delta_seguidos_hoy' => (int)($perfil?->delta_seguidos_24h ?? 0),
                'delta_posts_hoy' => (int)($perfil?->delta_posts_24h ?? 0),
                'delta_me_gusta_hoy' => (int)($perfil?->delta_me_gusta_24h ?? 0),
                'delta_views_hoy' => (int)($perfil?->delta_views_24h ?? 0),
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
            ->with(['perfilSocial', 'ejeTematico'])
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
                        'nombre' => $p->ejeTematico->nombre,
                        'color_badge' => $p->ejeTematico->color_badge,
                    ] : null,
                    'fecha_publicacion' => $p->fecha_publicacion?->format('d/m/Y H:i'),
                    'fecha_publicacion_raw' => $p->fecha_publicacion?->format('Y-m-d\TH:i'),
                    'fecha_relativa' => $p->fecha_publicacion?->diffForHumans(),
                    'tipo_formato' => $p->tipo_formato,
                    'tipo_pauta' => $p->tipo_pauta,
                    'monto_invertido_pauta' => (float)$p->monto_invertido_pauta,
                    'vistas_organicas' => (int)$p->vistas_organicas,
                    'vistas_pagadas' => (int)$p->vistas_pagadas,
                    'url_post' => $p->url_post,
                    'media_url' => $p->media_url,
                    'contenido_resumen' => $p->contenido_resumen,
                    'total_vistas' => (int)$p->total_vistas,
                    'total_likes' => (int)$p->total_likes,
                    'total_comentarios' => (int)$p->total_comentarios,
                    'total_compartidos' => (int)$p->total_compartidos,
                    'total_guardados' => (int)$p->total_guardados,
                    'reacciones_detalladas' => $p->reacciones_detalladas,
                    'sentimiento_predominante' => $p->sentimiento_predominante,
                    'figuras_acompanantes' => $p->figuras_acompanantes,
                    'comentarios_destacados' => $p->comentarios_destacados,
                    'termometro_humor_social' => $p->termometro_humor_social,
                ];
            });

        $ejes = EjeTematico::where('workspace_id', $workspace->id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'color_badge']);

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
                'total_seguidores' => $candidato->perfilesSociales->where('esta_activo', true)->sum('seguidores_actuales'),
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

        PerfilSocial::updateOrCreate(
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
                'seguidores_actuales' => (int)($validated['seguidores_actuales'] ?? $validated['seguidores_punto_cero'] ?? 0),
                'seguidos_actuales' => (int)($validated['seguidos_actuales'] ?? $validated['seguidos_punto_cero'] ?? 0),
                'publicaciones_totales' => (int)($validated['publicaciones_totales'] ?? $validated['publicaciones_punto_cero'] ?? 0),
                'me_gusta_totales' => (int)($validated['me_gusta_totales'] ?? $validated['me_gusta_punto_cero'] ?? 0),
                'visualizaciones_totales' => (int)($validated['visualizaciones_totales'] ?? $validated['visualizaciones_punto_cero'] ?? 0),
                'fecha_punto_cero' => $validated['fecha_punto_cero'] ?? now(),
                'seguidores_punto_cero' => (int)($validated['seguidores_punto_cero'] ?? $validated['seguidores_actuales'] ?? 0),
                'seguidos_punto_cero' => (int)($validated['seguidos_punto_cero'] ?? $validated['seguidos_actuales'] ?? 0),
                'publicaciones_punto_cero' => (int)($validated['publicaciones_punto_cero'] ?? $validated['publicaciones_totales'] ?? 0),
                'me_gusta_punto_cero' => (int)($validated['me_gusta_punto_cero'] ?? $validated['me_gusta_totales'] ?? 0),
                'visualizaciones_punto_cero' => (int)($validated['visualizaciones_punto_cero'] ?? $validated['visualizaciones_totales'] ?? 0),
                'notas_punto_cero' => $validated['notas_punto_cero'] ?? null,
            ]
        );

        if (!empty($validated['foto_perfil_url'])) {
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
    public function scrapePerfilSocial(Request $request, \App\Services\SocialProfileScraperService $scraper): \Illuminate\Http\JsonResponse
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
    public function refrescarPerfilSocial(Request $request, PerfilSocial $perfilSocial, \App\Services\SocialProfileScraperService $scraper): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (empty($perfilSocial->url_perfil)) {
            if ($request->expectsJson() && !$request->header('X-Inertia')) {
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
        $deltaMsg = $deltaSeguidores != 0 ? " ({$signo}{$deltaSeguidores} seguidores hoy)" : "";
        $msg = "¡{$perfilSocial->plataforma} auditado con éxito!{$deltaMsg}";

        if ($request->expectsJson() && !$request->header('X-Inertia')) {
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
            'tiktok' => ['nombre' => 'TikTok', 'formato_default' => 'Video Corto'],
            'x_twitter' => ['nombre' => 'X (Twitter)', 'formato_default' => 'Tweet'],
            'youtube' => ['nombre' => 'YouTube', 'formato_default' => 'Video/Shorts'],
            'linkedin' => ['nombre' => 'LinkedIn', 'formato_default' => 'Artículo'],
        ];

        $redesMapeadas = collect($plataformasEstandar)->map(function ($info, $key) use ($candidato) {
            $perfil = $candidato->perfilesSociales->firstWhere('plataforma', $key);

            $estaActivo = $perfil ? (bool)$perfil->esta_activo : false;
            $estaVerificado = $perfil ? (bool)$perfil->esta_verificado : false;
            $colorEstado = $estaVerificado ? 'azul' : ($estaActivo ? 'naranja' : 'rojo');

            $seguidoresActuales = $perfil ? (int)$perfil->seguidores_actuales : 0;
            $seguidoresBaseline = $perfil ? (int)$perfil->seguidores_punto_cero : 0;
            $crecimientoSeguidores = $seguidoresActuales - $seguidoresBaseline;

            $postsActuales = $perfil ? (int)$perfil->publicaciones_totales : 0;
            $postsBaseline = $perfil ? (int)$perfil->publicaciones_punto_cero : 0;
            $crecimientoPosts = $postsActuales - $postsBaseline;

            $meGustaActuales = $perfil ? (int)$perfil->me_gusta_totales : 0;
            $meGustaBaseline = $perfil ? (int)$perfil->me_gusta_punto_cero : 0;
            $crecimientoMeGusta = $meGustaActuales - $meGustaBaseline;

            $viewsActuales = $perfil ? (int)$perfil->visualizaciones_totales : 0;
            $viewsBaseline = $perfil ? (int)$perfil->visualizaciones_punto_cero : 0;
            $crecimientoViews = $viewsActuales - $viewsBaseline;

            return [
                'key' => $key,
                'nombre' => $info['nombre'],
                'color_estado' => $colorEstado,
                'perfil_id' => $perfil?->id,
                'existe' => (bool)$perfil,
                'esta_activo' => $estaActivo,
                'esta_verificado' => $estaVerificado,
                'handle_usuario' => $perfil?->handle_usuario ?? '',
                'url_perfil' => $perfil?->url_perfil ?? '',
                'foto_perfil_url' => $perfil?->foto_perfil_url ?? $candidato->avatar_url,
                'seguidores_actuales' => $seguidoresActuales,
                'seguidos_actuales' => $perfil ? (int)$perfil->seguidos_actuales : 0,
                'publicaciones_totales' => $postsActuales,
                'me_gusta_totales' => $meGustaActuales,
                'visualizaciones_totales' => $viewsActuales,
                // Punto Cero (Baseline Inicial)
                'fecha_punto_cero' => $perfil?->fecha_punto_cero ? $perfil->fecha_punto_cero->format('Y-m-d') : date('Y-m-d'),
                'seguidores_punto_cero' => $seguidoresBaseline,
                'seguidos_punto_cero' => $perfil ? (int)$perfil->seguidos_punto_cero : 0,
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

        $territorioId = $validated['territorio_id'] ?? $candidato->territorio_id;

        // Si se envió un nombre de territorio nuevo o editado
        if (!empty($validated['territorio_nombre'])) {
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
     * Dashboard Analítico Avanzado para un Canal de Red Social específico.
     */
    public function metricasCanal(Request $request, PerfilSocial $perfilSocial): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidato = $perfilSocial->candidato;

        // Validar acceso al workspace
        if ($candidato->workspace_id !== $workspace->id) {
            abort(403, 'No autorizado para ver este perfil.');
        }

        $ejes = EjeTematico::where('workspace_id', $workspace->id)->get();

        // 1. Cargar mediciones históricas time-series
        $mediciones = $perfilSocial->metricasHistoricas()
            ->orderBy('fecha_medicion', 'asc')
            ->get();

        // 2. Cargar publicaciones de este canal
        $publicaciones = Publicacion::where('perfil_social_id', $perfilSocial->id)
            ->with(['ejeTematico'])
            ->orderByDesc('fecha_publicacion')
            ->get();

        // 3. Cálculos de Crecimiento & Punto Cero
        $seguidoresActuales = (int)$perfilSocial->seguidores_actuales;
        $seguidoresPuntoCero = (int)$perfilSocial->seguidores_punto_cero;
        $crecimientoNetoSeguidores = $seguidoresActuales - $seguidoresPuntoCero;
        $crecimientoPctSeguidores = $seguidoresPuntoCero > 0
            ? round(($crecimientoNetoSeguidores / $seguidoresPuntoCero) * 100, 2)
            : 0;

        $postsActuales = (int)$perfilSocial->publicaciones_totales;
        $postsPuntoCero = (int)$perfilSocial->publicaciones_punto_cero;
        $crecimientoNetoPosts = max(0, $postsActuales - $postsPuntoCero);

        // 4. Métricas de Engagement & Interacciones
        $totalLikes = (int)$publicaciones->sum('total_likes');
        $totalComentarios = (int)$publicaciones->sum('total_comentarios');
        $totalCompartidos = (int)$publicaciones->sum('total_compartidos');
        $totalGuardados = (int)$publicaciones->sum('total_guardados');
        $totalInteracciones = $totalLikes + $totalComentarios + $totalCompartidos + $totalGuardados;
        $totalVistas = (int)$publicaciones->sum('total_vistas');
        $totalPauta = (float)$publicaciones->sum('monto_invertido_pauta');

        // Tasa de engagement promedio por post vs seguidores
        $tasaEngagement = ($seguidoresActuales > 0 && $publicaciones->count() > 0)
            ? round((($totalInteracciones / $publicaciones->count()) / $seguidoresActuales) * 100, 2)
            : 0;

        // Distribución por Ejes Temáticos
        $distribucionEjes = $ejes->map(function ($eje) use ($publicaciones) {
            $postsDelEje = $publicaciones->where('eje_tematico_id', $eje->id);
            $totalInt = $postsDelEje->sum(fn($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + $p->total_guardados);
            return [
                'id' => $eje->id,
                'nombre' => $eje->nombre,
                'color_badge' => $eje->color_badge,
                'total_posts' => $postsDelEje->count(),
                'total_interacciones' => $totalInt,
                'total_likes' => $postsDelEje->sum('total_likes'),
                'total_comentarios' => $postsDelEje->sum('total_comentarios'),
            ];
        })->filter(fn($e) => $e['total_posts'] > 0)->values();

        // Top 5 Publicaciones más destacadas
        $topPublicaciones = $publicaciones->sortByDesc(fn($p) => $p->total_likes + $p->total_comentarios + $p->total_compartidos + $p->total_guardados)
            ->take(5)
            ->values()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'fecha_publicacion' => $p->fecha_publicacion?->format('d/m/Y H:i'),
                    'fecha_relativa' => $p->fecha_publicacion?->diffForHumans(),
                    'tipo_formato' => $p->tipo_formato,
                    'tipo_pauta' => $p->tipo_pauta,
                    'url_post' => $p->url_post,
                    'media_url' => $p->media_url,
                    'contenido_resumen' => $p->contenido_resumen,
                    'total_likes' => (int)$p->total_likes,
                    'total_comentarios' => (int)$p->total_comentarios,
                    'total_compartidos' => (int)$p->total_compartidos,
                    'total_guardados' => (int)$p->total_guardados,
                    'total_interacciones' => (int)($p->total_likes + $p->total_comentarios + $p->total_compartidos + $p->total_guardados),
                    'sentimiento_predominante' => $p->sentimiento_predominante,
                    'termometro_humor_social' => $p->termometro_humor_social,
                    'eje_tematico' => $p->ejeTematico ? [
                        'nombre' => $p->ejeTematico->nombre,
                        'color_badge' => $p->ejeTematico->color_badge,
                    ] : null,
                ];
            });

        return Inertia::render('Candidatos/MetricasCanal', [
            'candidato' => [
                'id' => $candidato->id,
                'nombre_completo' => $candidato->nombre_completo,
                'partido_coalicion' => $candidato->partido_coalicion,
                'cargo_aspirado' => $candidato->cargo_aspirado,
                'estado_politico' => $candidato->estado_politico,
                'color_hex' => $candidato->color_hex,
                'avatar_url' => $candidato->avatar_url,
                'es_propio' => (bool)$candidato->es_propio,
            ],
            'perfilSocial' => [
                'id' => $perfilSocial->id,
                'plataforma' => $perfilSocial->plataforma,
                'handle_usuario' => $perfilSocial->handle_usuario,
                'url_perfil' => $perfilSocial->url_perfil,
                'foto_perfil_url' => $perfilSocial->foto_perfil_url,
                'esta_activo' => (bool)$perfilSocial->esta_activo,
                'es_verificado' => (bool)$perfilSocial->es_verificado,
                'semaforo_color' => $perfilSocial->semaforo_color,
                'seguidores_actuales' => $seguidoresActuales,
                'seguidores_punto_cero' => $seguidoresPuntoCero,
                'seguidos_actuales' => (int)$perfilSocial->seguidos_actuales,
                'publicaciones_totales' => $postsActuales,
                'publicaciones_punto_cero' => $postsPuntoCero,
                'me_gusta_totales' => (int)$perfilSocial->me_gusta_totales,
                'fecha_punto_cero' => $perfilSocial->fecha_punto_cero?->format('d/m/Y'),
                'fecha_ultima_medicion' => $perfilSocial->ultima_auditoria_at?->format('d/m/Y H:i'),
                'fecha_ultima_medicion_relativa' => $perfilSocial->ultima_auditoria_at?->diffForHumans(),
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
            ],
            'historicoMediciones' => $mediciones->map(function ($m) {
                return [
                    'id' => $m->id,
                    'fecha' => $m->fecha?->format('d/m/Y') ?? $m->created_at?->format('d/m/Y'),
                    'fecha_corta' => $m->fecha?->format('d/m') ?? $m->created_at?->format('d/m'),
                    'seguidores' => (int)$m->seguidores,
                    'seguidos' => (int)$m->seguidos,
                    'publicaciones' => (int)$m->publicaciones_totales,
                    'me_gusta_totales' => (int)$m->me_gusta_totales,
                    'crecimiento_neto_seguidores' => (int)$m->crecimiento_seguidores_neto,
                    'crecimiento_neto_publicaciones' => (int)$m->crecimiento_posts_neto,
                ];
            }),
            'topPublicaciones' => $topPublicaciones,
            'distribucionEjes' => $distribucionEjes,
            'ejes' => $ejes,
        ]);
    }
}
