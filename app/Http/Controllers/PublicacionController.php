<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\PerfilSocialMetrica;
use App\Models\Publicacion;
use App\Models\PublicacionPautaEvento;
use App\Services\MediaStorageService;
use App\Services\SocialProfileScraperService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicacionController extends Controller
{
    /**
     * Extraer datos públicos de una publicación (Instagram, Facebook, TikTok, YouTube, etc.) con 1 clic.
     */
    public function scrapePost(Request $request, SocialProfileScraperService $scraper): JsonResponse
    {
        $inputUrl = $request->input('url') ?? $request->input('url_post') ?? '';
        $plataforma = $request->input('plataforma');
        $workspace = WorkspaceHelper::activo($request);

        if (empty($inputUrl)) {
            return response()->json([
                'success' => false,
                'error' => 'Ingresa una URL de publicación válida.',
            ], 422);
        }

        // Verificar si la publicación ya existe en el workspace
        $canonicalUrl = SocialProfileScraperService::canonicalizePostUrl($inputUrl) ?? $inputUrl;
        $duplicado = Publicacion::buscarDuplicado($workspace->id, $canonicalUrl);

        if ($duplicado) {
            return response()->json([
                'success' => false,
                'ya_registrada' => true,
                'publicacion_id' => $duplicado->id,
                'fecha_publicacion' => $duplicado->fecha_publicacion?->format('d/m/Y H:i'),
                'autor' => $duplicado->candidato?->nombre_completo,
                'plataforma' => $duplicado->perfilSocial?->plataforma,
                'mensaje' => '⚠️ Esta publicación ya se encuentra registrada en el sistema. No se pueden duplicar contenidos.',
            ]);
        }

        $data = $scraper->scrapePost($inputUrl, $plataforma);

        return response()->json([
            'success' => $data['success'] ?? false,
            'data' => $data,
            'mensaje' => $data['mensaje'] ?? '',
        ]);
    }

    /**
     * Sincronizar en vivo una publicación individual.
     */
    public function sincronizarIndividual(Publicacion $publicacion, SocialProfileScraperService $scraper, MediaStorageService $mediaStorage): JsonResponse
    {
        if (empty($publicacion->url_post)) {
            return response()->json(['success' => false, 'mensaje' => 'La publicación no tiene enlace URL.']);
        }

        $scraped = $scraper->scrapePost($publicacion->url_post, $publicacion->perfilSocial?->plataforma ?? $publicacion->plataforma);
        if (! $scraped['success']) {
            return response()->json(['success' => false, 'mensaje' => $scraped['mensaje'] ?? 'No se pudo leer la URL pública.']);
        }

        $oldLikes = (int) $publicacion->total_likes;
        $oldComments = (int) $publicacion->total_comentarios;
        $freshLikes = (int) ($scraped['total_likes'] ?? $oldLikes);
        $freshComments = (int) ($scraped['total_comentarios'] ?? $oldComments);

        $deltaLikes = max(0, $freshLikes - $oldLikes);
        $deltaComments = max(0, $freshComments - $oldComments);

        $plataforma = $publicacion->perfilSocial?->plataforma ?? $publicacion->plataforma ?? 'instagram';
        $aiEmocional = $this->calcularInteligenciaEmocional([], $freshLikes, $plataforma);

        $updateFields = [
            'total_likes' => $freshLikes,
            'total_comentarios' => $freshComments,
            'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
            'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
            'termometro_humor_social' => $aiEmocional['termometro_humor_social'],
        ];

        // Guardar o actualizar la imagen descargándola localmente
        if (! empty($scraped['media_url'])) {
            $localMedia = $mediaStorage->guardarMediaLocal($scraped['media_url'], $publicacion->media_url);
            if ($localMedia) {
                $updateFields['media_url'] = $localMedia;
            }
        }

        $publicacion->update($updateFields);

        return response()->json([
            'success' => true,
            'mensaje' => 'Sincronizado',
            'delta_likes' => $deltaLikes,
            'delta_comentarios' => $deltaComments,
            'total_likes' => $freshLikes,
            'total_comentarios' => $freshComments,
            'url_post' => $publicacion->url_post,
            'fecha' => $publicacion->fecha_publicacion?->format('d/m/Y') ?? 'Reciente',
            'resumen' => substr($publicacion->contenido_resumen, 0, 45).(strlen($publicacion->contenido_resumen) > 45 ? '...' : ''),
        ]);
    }

    /**
     * Sincronizar en vivo métricas de publicaciones en la ventana activa de 15 días.
     * Si la publicación tiene >= 16 días, su métrica queda congelada como histórico consolidado.
     */
    public function sincronizarRecientes(Request $request, PerfilSocial $perfilSocial, SocialProfileScraperService $scraper, MediaStorageService $mediaStorage): RedirectResponse|JsonResponse
    {
        $fechaLimite = Carbon::now()->subDays(15)->startOfDay();

        $publicaciones = Publicacion::where('perfil_social_id', $perfilSocial->id)
            ->where('fecha_publicacion', '>=', $fechaLimite)
            ->whereNotNull('url_post')
            ->where('url_post', '!=', '')
            ->get();

        if ($publicaciones->isEmpty()) {
            $msg = 'No hay publicaciones dentro de la ventana de los últimos 15 días para sincronizar.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'mensaje' => $msg, 'actualizadas' => 0]);
            }

            return redirect()->back()->with('warning', $msg);
        }

        $actualizadas = 0;
        $nuevosLikes = 0;
        $nuevosComentarios = 0;

        foreach ($publicaciones as $pub) {
            $scraped = $scraper->scrapePost($pub->url_post, $perfilSocial->plataforma);
            if ($scraped['success']) {
                $oldLikes = (int) $pub->total_likes;
                $oldComments = (int) $pub->total_comentarios;
                $freshLikes = (int) ($scraped['total_likes'] ?? $oldLikes);
                $freshComments = (int) ($scraped['total_comentarios'] ?? $oldComments);

                // Si encontramos datos más frescos o mayores
                if ($freshLikes > $oldLikes || $freshComments > $oldComments || $freshLikes > 0 || ! empty($scraped['media_url'])) {
                    $deltaLikes = max(0, $freshLikes - $oldLikes);
                    $deltaComments = max(0, $freshComments - $oldComments);
                    $nuevosLikes += $deltaLikes;
                    $nuevosComentarios += $deltaComments;

                    // Recalcular reacciones emocionales con los nuevos likes
                    $aiEmocional = $this->calcularInteligenciaEmocional([], $freshLikes, $perfilSocial->plataforma);

                    $pubUpdate = [
                        'total_likes' => $freshLikes,
                        'total_comentarios' => $freshComments,
                        'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
                        'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
                        'termometro_humor_social' => $aiEmocional['termometro_humor_social'],
                    ];

                    // Guardar o actualizar la imagen localmente
                    if (! empty($scraped['media_url'])) {
                        $localMedia = $mediaStorage->guardarMediaLocal($scraped['media_url'], $pub->media_url);
                        if ($localMedia) {
                            $pubUpdate['media_url'] = $localMedia;
                        }
                    }

                    $pub->update($pubUpdate);

                    $actualizadas++;
                }
            }
        }

        if ($actualizadas > 0) {
            $msg = "✨ Se sincronizaron {$actualizadas} publicaciones recientes (+{$nuevosLikes} likes y +{$nuevosComentarios} comentarios nuevos).";
        } else {
            $msg = "✨ Se verificaron {$publicaciones->count()} publicaciones de los últimos 15 días y ya estaban al día.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'mensaje' => $msg,
                'actualizadas' => $actualizadas,
                'nuevos_likes' => $nuevosLikes,
                'nuevos_comentarios' => $nuevosComentarios,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Muro interactivo estilo red social (Social Wall) del Workspace Activo.
     */
    public function feed(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidatoId = $request->input('candidato_id');
        $plataforma = $request->input('plataforma');
        $tipoPauta = $request->input('tipo_pauta');
        $ejeTematicoId = $request->input('eje_tematico_id');
        $anio = $request->input('anio');
        $mes = $request->input('mes');
        $search = $request->input('search');
        $filtro = $request->input('filtro'); // 'propio' | 'oposicion'

        // Obtener Años y Meses reales que existen en la base de datos para este workspace (fechas de origen fecha_publicacion)
        $baseFechasQuery = Publicacion::where('workspace_id', $workspace->id)
            ->whereNotNull('fecha_publicacion');

        if ($filtro === 'propio') {
            $baseFechasQuery->whereHas('candidato', fn ($q) => $q->where('es_propio', true));
        } elseif ($filtro === 'oposicion') {
            $baseFechasQuery->whereHas('candidato', fn ($q) => $q->where('es_propio', false));
        }

        if ($candidatoId) {
            $baseFechasQuery->where('candidato_id', $candidatoId);
        }

        if ($plataforma) {
            $platforms = match ($plataforma) {
                'x_twitter', 'twitter' => ['x_twitter', 'twitter'],
                default => [$plataforma],
            };
            $baseFechasQuery->whereHas('perfilSocial', fn ($q) => $q->whereIn('plataforma', $platforms));
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

        // Para los meses: si hay un año seleccionado, filtrar únicamente los meses con publicaciones de ese año
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

        $query = Publicacion::where('workspace_id', $workspace->id)
            ->with(['candidato', 'perfilSocial', 'ejeTematico', 'pautaEventos']);

        if ($filtro === 'propio') {
            $query->whereHas('candidato', fn ($q) => $q->where('es_propio', true));
        } elseif ($filtro === 'oposicion') {
            $query->whereHas('candidato', fn ($q) => $q->where('es_propio', false));
        }

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        if ($plataforma) {
            $platforms = match ($plataforma) {
                'x_twitter', 'twitter' => ['x_twitter', 'twitter'],
                default => [$plataforma],
            };

            $query->whereHas('perfilSocial', fn ($q) => $q->whereIn('plataforma', $platforms));
        }

        if ($tipoPauta) {
            $query->where('tipo_pauta', $tipoPauta);
        }

        if ($ejeTematicoId) {
            $query->where('eje_tematico_id', $ejeTematicoId);
        }

        if ($anio && $mes) {
            $query->whereYear('fecha_publicacion', $anio)
                ->whereMonth('fecha_publicacion', (int) $mes);
        } elseif ($anio) {
            $query->whereYear('fecha_publicacion', $anio);
        } elseif ($mes) {
            $query->whereMonth('fecha_publicacion', (int) $mes);
        }

        $rangoAprobacion = $request->input('rango_aprobacion');
        if ($rangoAprobacion === 'alta') {
            $query->where('aprobacion_neta_pct', '>=', 80);
        } elseif ($rangoAprobacion === 'media') {
            $query->whereBetween('aprobacion_neta_pct', [50, 79]);
        } elseif ($rangoAprobacion === 'baja') {
            $query->where('aprobacion_neta_pct', '<', 50);
        }

        if ($search) {
            $query->where('contenido_resumen', 'like', "%{$search}%");
        }

        $orden = $request->input('orden', 'recientes');
        if ($orden === 'antiguos') {
            $query->orderBy('fecha_publicacion', 'asc')->orderBy('id', 'asc');
        } elseif ($orden === 'interacciones') {
            $query->orderByDesc('total_likes')->orderByDesc('fecha_publicacion');
        } else {
            // Por defecto: Cronología de la red social (publicación más reciente arriba)
            $query->orderByDesc('fecha_publicacion')->orderByDesc('id');
        }

        $publicaciones = $query->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'candidato' => [
                    'id' => $p->candidato?->id,
                    'nombre_completo' => $p->candidato?->nombre_completo,
                    'partido_coalicion' => $p->candidato?->partido_coalicion,
                    'estado_politico' => $p->candidato?->estado_politico,
                    'es_propio' => $p->candidato?->es_propio,
                    'color_hex' => $p->candidato?->color_hex,
                    'avatar_url' => $p->candidato?->avatar_url,
                ],
                'perfil_social' => [
                    'id' => $p->perfilSocial?->id,
                    'plataforma' => $p->perfilSocial?->plataforma,
                    'handle_usuario' => $p->perfilSocial?->handle_usuario,
                ],
                'eje_tematico' => $p->ejeTematico ? [
                    'id' => $p->ejeTematico->id,
                    'pilar_principal' => $p->ejeTematico->pilar_principal,
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                    'icono' => $p->ejeTematico->icono,
                ] : null,
                'plataforma' => $p->perfilSocial?->plataforma ?: 'facebook',
                'fecha_publicacion' => $p->fecha_publicacion?->format('d/m/Y H:i'),
                'fecha_publicacion_raw' => $p->fecha_publicacion?->format('Y-m-d\TH:i'),
                'fecha_publicacion_humana' => $p->fecha_publicacion ? ($p->fecha_publicacion->year === (int) date('Y') ? $p->fecha_publicacion->locale('es')->isoFormat('D [de] MMMM') : $p->fecha_publicacion->locale('es')->isoFormat('D [de] MMMM [de] YYYY')) : null,
                'fecha_carga' => $p->created_at?->format('Y-m-d'),
                'fecha_relativa' => $p->fecha_publicacion?->diffForHumans(),
                'tipo_formato' => $p->tipo_formato,
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido_pauta' => (float) $p->monto_invertido_pauta,
                'vistas_organicas' => $p->vistas_organicas,
                'vistas_pagadas' => $p->vistas_pagadas,
                'total_vistas' => $p->total_vistas,
                'total_likes' => $p->total_likes,
                'total_comentarios' => $p->total_comentarios,
                'total_compartidos' => $p->total_compartidos,
                'total_republicados' => $p->total_republicados,
                'total_guardados' => $p->total_guardados,
                'score_impacto_organico' => $p->score_impacto_organico,
                'tasa_viralidad_pct' => $p->tasa_viralidad_pct,
                'reacciones_detalladas' => $p->reacciones_detalladas,
                'aprobacion_neta_pct' => $p->aprobacion_neta_pct,
                'termometro_humor_social' => $p->termometro_humor_social,
                'sentimiento_predominante' => $p->sentimiento_predominante,
                'comentario_destacado' => $p->comentario_destacado,
                'figura_acompanante' => $p->figura_acompanante,
                'url_post' => $p->url_post,
                'media_url' => $p->media_url,
                'media_embed_url' => $p->media_embed_url,
                'contenido_resumen' => $p->contenido_resumen,
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

        $candidatosQuery = Candidato::where('workspace_id', $workspace->id)
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo');

        if ($filtro === 'propio') {
            $candidatosQuery->where('es_propio', true);
        } elseif ($filtro === 'oposicion') {
            $candidatosQuery->where('es_propio', false);
        }

        $candidatos = $candidatosQuery->with('perfilesSociales')->get();

        $ejes = EjeTematico::where('workspace_id', $workspace->id)
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'pilar_principal', 'nombre', 'slug', 'color_badge', 'icono', 'orden']);

        return Inertia::render('Publicaciones/Feed', [
            'publicaciones' => $publicaciones,
            'candidatos' => $candidatos,
            'ejes' => $ejes,
            'anios_disponibles' => $aniosDisponibles,
            'meses_disponibles' => $mesesDisponibles,
            'filtros' => [
                'filtro' => $filtro,
                'candidato_id' => $candidatoId,
                'plataforma' => $plataforma,
                'tipo_pauta' => $tipoPauta,
                'eje_tematico_id' => $ejeTematicoId,
                'anio' => $anio,
                'mes' => $mes,
                'rango_aprobacion' => $rangoAprobacion,
                'search' => $search,
                'orden' => $orden,
            ],
            'stats_resumen' => [
                'total_posts' => $publicaciones->count(),
                'total_vistas' => $publicaciones->sum('total_vistas'),
                'total_pauta_invertida' => $publicaciones->sum('monto_invertido_pauta'),
            ],
        ]);
    }

    /**
     * Guardar una nueva publicación.
     */
    public function store(Request $request, MediaStorageService $mediaStorage): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        // Limpiar strings vacíos para evitar fallos de validación en campos relacionales opcionales
        $request->merge([
            'perfil_social_id' => $request->filled('perfil_social_id') ? $request->input('perfil_social_id') : null,
            'eje_tematico_id' => $request->filled('eje_tematico_id') ? $request->input('eje_tematico_id') : null,
        ]);

        $validated = $request->validate([
            'candidato_id' => ['required', 'exists:candidatos,id'],
            'perfil_social_id' => ['nullable', 'exists:perfil_socials,id'],
            'plataforma' => ['nullable', 'string'],
            'eje_tematico_id' => ['nullable', 'exists:eje_tematicos,id'],
            'eje_tematico_nombre' => ['nullable', 'string', 'max:255'],
            'fecha_publicacion' => ['required', 'date'],
            'tipo_formato' => ['required', 'string'],
            'tipo_pauta' => ['required', 'string', 'in:organico,organico_impulsado,pauta_paga,colaboracion_pagada'],
            'monto_invertido_pauta' => ['nullable', 'numeric', 'min:0'],
            'url_post' => ['nullable', 'string', 'max:1000'],
            'media_url' => ['nullable', 'string', 'max:1000'],
            'vistas_organicas' => ['nullable', 'integer', 'min:0'],
            'vistas_pagadas' => ['nullable', 'integer', 'min:0'],
            'contenido_resumen' => ['required', 'string'],
            'total_likes' => ['nullable', 'integer', 'min:0'],
            'me_gusta' => ['nullable', 'integer', 'min:0'],
            'me_encanta' => ['nullable', 'integer', 'min:0'],
            'me_importa' => ['nullable', 'integer', 'min:0'],
            'me_divierte' => ['nullable', 'integer', 'min:0'],
            'me_asombra' => ['nullable', 'integer', 'min:0'],
            'me_entristece' => ['nullable', 'integer', 'min:0'],
            'me_enoja' => ['nullable', 'integer', 'min:0'],
            'total_comentarios' => ['nullable', 'integer', 'min:0'],
            'total_compartidos' => ['nullable', 'integer', 'min:0'],
            'total_republicados' => ['nullable', 'integer', 'min:0'],
            'total_guardados' => ['nullable', 'integer', 'min:0'],
            'termometro_humor_social' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comentario_destacado' => ['nullable', 'string', 'max:500'],
            'figura_acompanante' => ['nullable', 'string', 'max:255'],
        ], [
            'candidato_id.required' => 'Debes seleccionar un candidato.',
            'contenido_resumen.required' => 'El texto o resumen del post es obligatorio.',
        ]);

        // Resolver o autogenerar el perfil social si vino por plataforma
        $perfilSocialId = $validated['perfil_social_id'] ?? null;
        if (! $perfilSocialId && ! empty($validated['plataforma'])) {
            $candidato = Candidato::findOrFail($validated['candidato_id']);
            $perfil = PerfilSocial::firstOrCreate(
                [
                    'candidato_id' => $candidato->id,
                    'plataforma' => $validated['plataforma'],
                ],
                [
                    'handle_usuario' => '@'.strtolower(str_replace(' ', '', $candidato->nombre_completo)),
                    'esta_activo' => true,
                    'esta_verificado' => false,
                ]
            );
            $perfilSocialId = $perfil->id;
        }

        if (! $perfilSocialId) {
            return redirect()->back()->withErrors(['perfil_social_id' => 'Debes seleccionar o indicar la red social.']);
        }

        // Resolver o autogenerar eje temático si se ingresó nombre
        $ejeId = $validated['eje_tematico_id'] ?? null;
        if (! $ejeId && ! empty($validated['eje_tematico_nombre'])) {
            $ejeNombre = trim($validated['eje_tematico_nombre']);
            $eje = EjeTematico::firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'nombre' => $ejeNombre,
                ],
                [
                    'slug' => Str::slug($ejeNombre),
                    'color_badge' => '#06b6d4',
                    'descripcion' => 'Eje temático: '.$ejeNombre,
                ]
            );
            $ejeId = $eje->id;
        }

        // Canonicalizar URL para unicidad consistente
        $canonicalUrl = ! empty($validated['url_post'])
            ? SocialProfileScraperService::canonicalizePostUrl($validated['url_post'])
            : null;

        // Guardar copia local de la imagen para evitar enlaces vencidos
        $mediaUrlLocal = ! empty($validated['media_url'])
            ? $mediaStorage->guardarMediaLocal($validated['media_url'])
            : null;

        // VERIFICAR UNICIDAD: Evitar publicaciones duplicadas en el workspace
        $duplicado = Publicacion::buscarDuplicado(
            $workspace->id,
            $canonicalUrl ?? $validated['url_post'] ?? null,
            (int) $validated['candidato_id'],
            (int) $perfilSocialId,
            $validated['fecha_publicacion'],
            $validated['contenido_resumen']
        );

        if ($duplicado) {
            return redirect()->back()->withInput()->withErrors([
                'url_post' => '⚠️ Esta publicación ya se encuentra registrada en el sistema. Las publicaciones deben ser únicas.',
            ]);
        }

        $vistasOrg = (int) ($validated['vistas_organicas'] ?? 0);
        $vistasPag = (int) ($validated['vistas_pagadas'] ?? 0);
        $totalVistas = $vistasOrg + $vistasPag;

        $comentariosDestacados = ! empty($validated['comentario_destacado'])
            ? [$validated['comentario_destacado']]
            : [];

        $figuras = ! empty($validated['figura_acompanante'])
            ? array_map('trim', explode(',', $validated['figura_acompanante']))
            : [];

        $plataformaResolvida = $validated['plataforma'] ?? $perfil?->plataforma ?? 'instagram';
        $aiEmocional = $this->calcularInteligenciaEmocional($validated, (int) ($validated['total_likes'] ?? 0), $plataformaResolvida);

        $publicacion = Publicacion::create([
            'workspace_id' => $workspace->id,
            'candidato_id' => $validated['candidato_id'],
            'perfil_social_id' => $perfilSocialId,
            'eje_tematico_id' => $ejeId,
            'fecha_publicacion' => $validated['fecha_publicacion'],
            'tipo_formato' => $validated['tipo_formato'],
            'tipo_pauta' => $validated['tipo_pauta'],
            'monto_invertido_pauta' => $validated['tipo_pauta'] !== 'organico' ? ($validated['monto_invertido_pauta'] ?? 0) : 0,
            'url_post' => $canonicalUrl ?? $validated['url_post'] ?? null,
            'media_url' => $mediaUrlLocal,
            'vistas_organicas' => $vistasOrg,
            'vistas_pagadas' => $vistasPag,
            'contenido_resumen' => $validated['contenido_resumen'],
            'total_vistas' => $totalVistas,
            'total_likes' => $aiEmocional['total_likes'],
            'total_comentarios' => (int) ($validated['total_comentarios'] ?? 0),
            'total_compartidos' => (int) ($validated['total_compartidos'] ?? 0),
            'total_republicados' => (int) ($validated['total_republicados'] ?? 0),
            'total_guardados' => (int) ($validated['total_guardados'] ?? 0),
            'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
            'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
            'figuras_acompanantes' => $figuras,
            'comentarios_destacados' => $comentariosDestacados,
            'termometro_humor_social' => $validated['termometro_humor_social'] ?? $aiEmocional['termometro_humor_social'],
            'insights_internos_propios' => $aiEmocional['insights_internos_propios'],
        ]);

        // Actualizar contador acumulado del perfil social
        $perfil = PerfilSocial::find($perfilSocialId);
        if ($perfil) {
            $totalPosts = Publicacion::where('perfil_social_id', $perfil->id)->count();
            if ($totalPosts > $perfil->publicaciones_totales) {
                $perfil->update(['publicaciones_totales' => $totalPosts]);
            }
        }

        return redirect()->back()
            ->with('success', 'Publicación registrada con éxito.');
    }

    /**
     * Actualizar una publicación existente.
     */
    public function update(Request $request, Publicacion $publicacion, MediaStorageService $mediaStorage): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'contenido_resumen' => ['required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'url_post' => ['nullable', 'string', 'max:1000'],
            'media_url' => ['nullable', 'string', 'max:1000'],
            'tipo_formato' => ['required', 'string'],
            'tipo_pauta' => ['required', 'string', 'in:organico,organico_impulsado,pauta_paga,colaboracion_pagada'],
            'monto_invertido_pauta' => ['nullable', 'numeric', 'min:0'],
            'vistas_organicas' => ['nullable', 'integer', 'min:0'],
            'vistas_pagadas' => ['nullable', 'integer', 'min:0'],
            'total_vistas' => ['nullable', 'integer', 'min:0'],
            'total_likes' => ['nullable', 'integer', 'min:0'],
            'me_gusta' => ['nullable', 'integer', 'min:0'],
            'me_encanta' => ['nullable', 'integer', 'min:0'],
            'me_importa' => ['nullable', 'integer', 'min:0'],
            'me_divierte' => ['nullable', 'integer', 'min:0'],
            'me_asombra' => ['nullable', 'integer', 'min:0'],
            'me_entristece' => ['nullable', 'integer', 'min:0'],
            'me_enoja' => ['nullable', 'integer', 'min:0'],
            'total_comentarios' => ['nullable', 'integer', 'min:0'],
            'total_compartidos' => ['nullable', 'integer', 'min:0'],
            'total_republicados' => ['nullable', 'integer', 'min:0'],
            'total_guardados' => ['nullable', 'integer', 'min:0'],
            'eje_tematico_id' => ['nullable', 'exists:eje_tematicos,id'],
            'termometro_humor_social' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        // Canonicalizar URL y validar unicidad
        $canonicalUrl = ! empty($validated['url_post'])
            ? SocialProfileScraperService::canonicalizePostUrl($validated['url_post'])
            : $publicacion->url_post;

        if (! empty($canonicalUrl)) {
            $duplicado = Publicacion::buscarDuplicado(
                $workspace->id,
                $canonicalUrl,
                $publicacion->candidato_id,
                $publicacion->perfil_social_id,
                $validated['fecha_publicacion'] ?? null,
                $validated['contenido_resumen'],
                $publicacion->id
            );

            if ($duplicado) {
                return redirect()->back()->withInput()->withErrors([
                    'url_post' => '⚠️ Ya existe otra publicación registrada con esta misma URL (ID #'.$duplicado->id.').',
                ]);
            }
        }

        // Guardar o actualizar copia local de la imagen
        $mediaUrlLocal = array_key_exists('media_url', $validated)
            ? $mediaStorage->guardarMediaLocal($validated['media_url'], $publicacion->media_url)
            : $publicacion->media_url;

        $plataformaResolvida = $publicacion->perfilSocial?->plataforma ?? $publicacion->plataforma ?? 'instagram';
        $aiEmocional = $this->calcularInteligenciaEmocional(
            $validated,
            (int) ($validated['total_likes'] ?? $publicacion->total_likes),
            $plataformaResolvida
        );

        $updateData = [
            'contenido_resumen' => $validated['contenido_resumen'],
            'url_post' => $canonicalUrl ?? $validated['url_post'] ?? null,
            'media_url' => $mediaUrlLocal,
            'tipo_formato' => $validated['tipo_formato'],
            'tipo_pauta' => $validated['tipo_pauta'],
            'monto_invertido_pauta' => $validated['tipo_pauta'] !== 'organico' ? ($validated['monto_invertido_pauta'] ?? 0) : 0,
            'eje_tematico_id' => $validated['eje_tematico_id'] ?? null,
            'vistas_organicas' => (int) ($validated['vistas_organicas'] ?? $publicacion->vistas_organicas),
            'vistas_pagadas' => (int) ($validated['vistas_pagadas'] ?? $publicacion->vistas_pagadas),
            'total_vistas' => (int) ($validated['total_vistas'] ?? $publicacion->total_vistas),
            'total_likes' => $aiEmocional['total_likes'],
            'total_comentarios' => (int) ($validated['total_comentarios'] ?? $publicacion->total_comentarios),
            'total_compartidos' => (int) ($validated['total_compartidos'] ?? $publicacion->total_compartidos),
            'total_republicados' => (int) ($validated['total_republicados'] ?? $publicacion->total_republicados),
            'total_guardados' => (int) ($validated['total_guardados'] ?? $publicacion->total_guardados),
            'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
            'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
            'termometro_humor_social' => $validated['termometro_humor_social'] ?? $aiEmocional['termometro_humor_social'],
            'insights_internos_propios' => array_merge($publicacion->insights_internos_propios ?? [], $aiEmocional['insights_internos_propios']),
        ];

        // Detectar cambio de tipo_pauta o cambio de presupuesto para registrar snapshot de corte
        $huboCambioPauta = $validated['tipo_pauta'] !== $publicacion->tipo_pauta
            || (in_array($validated['tipo_pauta'], Publicacion::TIPOS_CON_INVERSION) && (float) ($validated['monto_invertido_pauta'] ?? 0) !== (float) $publicacion->monto_invertido_pauta);

        if ($huboCambioPauta) {
            $seguidoresCanal = PerfilSocialMetrica::where('perfil_social_id', $publicacion->perfil_social_id)
                ->orderByDesc('fecha')
                ->orderByDesc('id')
                ->value('seguidores')
                ?? $publicacion->perfilSocial?->seguidores_actuales
                ?? 0;

            PublicacionPautaEvento::create([
                'publicacion_id' => $publicacion->id,
                'tipo_pauta_anterior' => $publicacion->tipo_pauta,
                'tipo_pauta_nuevo' => $validated['tipo_pauta'],
                'monto_anterior' => (float) ($publicacion->monto_invertido_pauta ?? 0),
                'monto_nuevo' => $validated['tipo_pauta'] !== 'organico' ? (float) ($validated['monto_invertido_pauta'] ?? 0) : 0,
                'fecha_evento' => Carbon::now(),
                'seguidores_canal_snapshot' => (int) $seguidoresCanal,
                'likes_snapshot' => (int) $publicacion->total_likes,
                'comentarios_snapshot' => (int) $publicacion->total_comentarios,
                'compartidos_snapshot' => (int) $publicacion->total_compartidos,
                'vistas_snapshot' => (int) $publicacion->total_vistas,
                'republicados_snapshot' => (int) ($publicacion->total_republicados ?? 0),
                'registrado_por' => auth()->id(),
                'origen' => 'manual',
                'notas' => "Transición de {$publicacion->tipo_pauta} a {$validated['tipo_pauta']} guardada por usuario.",
            ]);
        }

        if (! empty($validated['fecha_publicacion'])) {
            $updateData['fecha_publicacion'] = $validated['fecha_publicacion'];
        }

        $publicacion->update($updateData);

        return redirect()->back()
            ->with('success', 'Publicación actualizada correctamente.');
    }

    /**
     * Confirmación rápida de fecha de publicación (edición inline desde la tarjeta del Feed).
     * Solo actualiza fecha_publicacion sin re-calcular métricas emocionales.
     */
    public function actualizarFecha(Request $request, Publicacion $publicacion): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        // Verificar que la publicación pertenece al workspace activo
        abort_unless($publicacion->workspace_id === $workspace->id, 403);

        $validated = $request->validate([
            'fecha_publicacion' => ['required', 'date'],
        ]);

        $publicacion->update([
            'fecha_publicacion' => $validated['fecha_publicacion'],
        ]);

        return redirect()->back()->with('success', 'Fecha confirmada correctamente.');
    }

    /**
     * Motor de Inteligencia Emocional & Sentimiento Cuantificado.
     */
    public function calcularInteligenciaEmocional(array $data, int $totalLikesFallback = 0, ?string $plataforma = null): array
    {
        $plat = strtolower($plataforma ?? $data['plataforma'] ?? '');

        // Extraer valores soportando nombres en español y nombres en inglés
        $meGusta = (int) ($data['me_gusta'] ?? ($data['reacciones_detalladas']['like'] ?? ($data['reacciones_detalladas']['me_gusta'] ?? 0)));
        $meEncanta = (int) ($data['me_encanta'] ?? ($data['reacciones_detalladas']['love'] ?? ($data['reacciones_detalladas']['me_encanta'] ?? 0)));
        $meImporta = (int) ($data['me_importa'] ?? ($data['reacciones_detalladas']['care'] ?? ($data['reacciones_detalladas']['me_importa'] ?? 0)));
        $meDivierte = (int) ($data['me_divierte'] ?? ($data['reacciones_detalladas']['haha'] ?? ($data['reacciones_detalladas']['me_divierte'] ?? 0)));
        $meAsombra = (int) ($data['me_asombra'] ?? ($data['reacciones_detalladas']['wow'] ?? ($data['reacciones_detalladas']['me_asombra'] ?? 0)));
        $meEntristece = (int) ($data['me_entristece'] ?? ($data['reacciones_detalladas']['sad'] ?? ($data['reacciones_detalladas']['me_entristece'] ?? 0)));
        $meEnoja = (int) ($data['me_enoja'] ?? ($data['reacciones_detalladas']['angry'] ?? ($data['reacciones_detalladas']['me_enoja'] ?? 0)));

        $totalReacciones = $meGusta + $meEncanta + $meImporta + $meDivierte + $meAsombra + $meEntristece + $meEnoja;

        // Si es Instagram, TikTok, X/Twitter o YouTube: no utilizan reacciones emocionales estilo Facebook
        if (in_array($plat, ['instagram', 'tiktok', 'x_twitter', 'youtube']) || ($plat !== 'facebook' && $totalReacciones === 0)) {
            $totalFinalLikes = $totalLikesFallback > 0 ? $totalLikesFallback : ($totalReacciones > 0 ? $totalReacciones : $meGusta);
            $termometro = (int) ($data['termometro_humor_social'] ?? 5);
            $sentimiento = $termometro >= 4 ? 'positivo' : ($termometro === 3 ? 'neutro' : 'negativo');

            return [
                'total_likes' => $totalFinalLikes,
                'reacciones_detalladas' => [
                    'me_gusta' => $totalFinalLikes,
                    'me_encanta' => 0,
                    'me_importa' => 0,
                    'me_divierte' => 0,
                    'me_asombra' => 0,
                    'me_entristece' => 0,
                    'me_enoja' => 0,
                ],
                'sentimiento_predominante' => $sentimiento,
                'termometro_humor_social' => $termometro,
                'insights_internos_propios' => [
                    'indice_aprobacion_neta' => 100.0,
                    'ratio_indignacion' => 0.0,
                    'alerta_crisis' => false,
                    'total_reacciones_positivas' => $totalFinalLikes,
                    'total_reacciones_negativas' => 0,
                    'total_reacciones_humor' => 0,
                ],
            ];
        }

        // Lógica de cálculo para Facebook o redes con desglose granular
        if ($totalReacciones === 0 && $totalLikesFallback > 0) {
            $totalReacciones = $totalLikesFallback;
            $meGusta = $totalLikesFallback;
        }

        $positivas = $meGusta + $meEncanta + $meImporta;
        $negativas = $meEnoja + $meEntristece;
        $humorViral = $meDivierte;

        $indiceAprobacion = $totalReacciones > 0
            ? round((($positivas - $negativas) / $totalReacciones) * 100, 1)
            : 100.0;

        $ratioIndignacion = $totalReacciones > 0
            ? round(($meEnoja / $totalReacciones) * 100, 1)
            : 0.0;

        $alertaCrisis = $ratioIndignacion >= 15.0;

        if ($indiceAprobacion >= 65) {
            $termometro = 5;
            $sentimiento = 'positivo';
        } elseif ($indiceAprobacion >= 30) {
            $termometro = 4;
            $sentimiento = 'positivo';
        } elseif ($indiceAprobacion >= -10) {
            $termometro = 3;
            $sentimiento = 'neutro';
        } elseif ($indiceAprobacion >= -40) {
            $termometro = 2;
            $sentimiento = 'negativo';
        } else {
            $termometro = 1;
            $sentimiento = 'negativo';
        }

        return [
            'total_likes' => $totalReacciones,
            'reacciones_detalladas' => [
                'me_gusta' => $meGusta,
                'me_encanta' => $meEncanta,
                'me_importa' => $meImporta,
                'me_divierte' => $meDivierte,
                'me_asombra' => $meAsombra,
                'me_entristece' => $meEntristece,
                'me_enoja' => $meEnoja,
            ],
            'sentimiento_predominante' => $sentimiento,
            'termometro_humor_social' => $termometro,
            'insights_internos_propios' => [
                'indice_aprobacion_neta' => $indiceAprobacion,
                'ratio_indignacion' => $ratioIndignacion,
                'alerta_crisis' => $alertaCrisis,
                'total_reacciones_positivas' => $positivas,
                'total_reacciones_negativas' => $negativas,
                'total_reacciones_humor' => $humorViral,
            ],
        ];
    }

    /**
     * Eliminar publicación.
     */
    public function destroy(Publicacion $publicacion): RedirectResponse
    {
        $publicacion->delete();

        return redirect()->back()
            ->with('success', 'Publicación eliminada correctamente.');
    }
}
