<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Publicacion;
use App\Services\SocialProfileScraperService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicacionController extends Controller
{
    /**
     * Extraer datos públicos de una publicación (Instagram, TikTok, YouTube, etc.) con 1 clic.
     */
    public function scrapePost(Request $request, SocialProfileScraperService $scraper): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'string'],
            'plataforma' => ['nullable', 'string'],
        ]);

        $data = $scraper->scrapePost($validated['url'], $validated['plataforma'] ?? 'instagram');

        return response()->json($data);
    }

    /**
     * Sincronizar en vivo una publicación individual.
     */
    public function sincronizarIndividual(Publicacion $publicacion, SocialProfileScraperService $scraper): JsonResponse
    {
        if (empty($publicacion->url_post)) {
            return response()->json(['success' => false, 'mensaje' => 'La publicación no tiene enlace URL.']);
        }

        $scraped = $scraper->scrapePost($publicacion->url_post, $publicacion->perfilSocial?->plataforma ?? $publicacion->plataforma);
        if (! $scraped['success']) {
            return response()->json(['success' => false, 'mensaje' => $scraped['mensaje'] ?? 'No se pudo leer la URL pública.']);
        }

        $oldLikes = (int)$publicacion->total_likes;
        $oldComments = (int)$publicacion->total_comentarios;
        $freshLikes = (int)($scraped['total_likes'] ?? $oldLikes);
        $freshComments = (int)($scraped['total_comentarios'] ?? $oldComments);

        $deltaLikes = max(0, $freshLikes - $oldLikes);
        $deltaComments = max(0, $freshComments - $oldComments);

        $aiEmocional = $this->calcularInteligenciaEmocional([], $freshLikes);

        $publicacion->update([
            'total_likes' => $freshLikes,
            'total_comentarios' => $freshComments,
            'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
            'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
            'termometro_humor_social' => $aiEmocional['termometro_humor_social'],
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Sincronizado',
            'delta_likes' => $deltaLikes,
            'delta_comentarios' => $deltaComments,
            'total_likes' => $freshLikes,
            'total_comentarios' => $freshComments,
            'url_post' => $publicacion->url_post,
            'fecha' => $publicacion->fecha_publicacion?->format('d/m/Y') ?? 'Reciente',
            'resumen' => substr($publicacion->contenido_resumen, 0, 45) . (strlen($publicacion->contenido_resumen) > 45 ? '...' : ''),
        ]);
    }

    /**
     * Sincronizar en vivo métricas de publicaciones en la ventana activa de 15 días.
     * Si la publicación tiene >= 16 días, su métrica queda congelada como histórico consolidado.
     */
    public function sincronizarRecientes(Request $request, PerfilSocial $perfilSocial, SocialProfileScraperService $scraper): RedirectResponse|JsonResponse
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
                $oldLikes = (int)$pub->total_likes;
                $oldComments = (int)$pub->total_comentarios;
                $freshLikes = (int)($scraped['total_likes'] ?? $oldLikes);
                $freshComments = (int)($scraped['total_comentarios'] ?? $oldComments);

                // Si encontramos datos más frescos o mayores
                if ($freshLikes > $oldLikes || $freshComments > $oldComments || $freshLikes > 0) {
                    $deltaLikes = max(0, $freshLikes - $oldLikes);
                    $deltaComments = max(0, $freshComments - $oldComments);
                    $nuevosLikes += $deltaLikes;
                    $nuevosComentarios += $deltaComments;

                    // Recalcular reacciones emocionales con los nuevos likes
                    $aiEmocional = $this->calcularInteligenciaEmocional([], $freshLikes);

                    $pub->update([
                        'total_likes' => $freshLikes,
                        'total_comentarios' => $freshComments,
                        'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
                        'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
                        'termometro_humor_social' => $aiEmocional['termometro_humor_social'],
                    ]);

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
        $search = $request->input('search');
        $filtro = $request->input('filtro'); // 'propio' | 'oposicion'

        $query = Publicacion::where('workspace_id', $workspace->id)
            ->with(['candidato', 'perfilSocial', 'ejeTematico'])
            ->orderByDesc('fecha_publicacion');

        if ($filtro === 'propio') {
            $query->whereHas('candidato', fn ($q) => $q->where('es_propio', true));
        } elseif ($filtro === 'oposicion') {
            $query->whereHas('candidato', fn ($q) => $q->where('es_propio', false));
        }

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        if ($plataforma) {
            $query->whereHas('perfilSocial', fn ($q) => $q->where('plataforma', $plataforma));
        }

        if ($tipoPauta) {
            $query->where('tipo_pauta', $tipoPauta);
        }

        if ($search) {
            $query->where('contenido_resumen', 'like', "%{$search}%");
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
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                ] : null,
                'plataforma' => $p->perfilSocial?->plataforma,
                'fecha_publicacion' => $p->fecha_publicacion?->format('d/m/Y H:i'),
                'fecha_relativa' => $p->fecha_publicacion?->diffForHumans(),
                'tipo_formato' => $p->tipo_formato,
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido_pauta' => (float)$p->monto_invertido_pauta,
                'vistas_organicas' => $p->vistas_organicas,
                'vistas_pagadas' => $p->vistas_pagadas,
                'url_post' => $p->url_post,
                'media_url' => $p->media_url,
                'contenido_resumen' => $p->contenido_resumen,
                'total_vistas' => $p->total_vistas,
                'total_likes' => $p->total_likes,
                'total_comentarios' => $p->total_comentarios,
                'total_compartidos' => $p->total_compartidos,
                'total_guardados' => $p->total_guardados,
                'reacciones_detalladas' => $p->reacciones_detalladas,
                'sentimiento_predominante' => $p->sentimiento_predominante,
                'figuras_acompanantes' => $p->figuras_acompanantes,
                'comentarios_destacados' => $p->comentarios_destacados,
                'termometro_humor_social' => $p->termometro_humor_social,
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

        $candidatos = $candidatosQuery->get(['id', 'nombre_completo', 'estado_politico', 'es_propio', 'avatar_url']);

        $ejes = EjeTematico::where('workspace_id', $workspace->id)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'color_badge']);

        return Inertia::render('Publicaciones/Feed', [
            'publicaciones' => $publicaciones,
            'candidatos' => $candidatos,
            'ejes' => $ejes,
            'filtros' => [
                'filtro' => $filtro,
                'candidato_id' => $candidatoId,
                'plataforma' => $plataforma,
                'tipo_pauta' => $tipoPauta,
                'search' => $search,
            ],
            'stats_resumen' => [
                'total_posts' => $publicaciones->count(),
                'total_vistas' => $publicaciones->sum('total_vistas'),
                'total_pauta_invertida' => $publicaciones->sum('monto_invertido_pauta'),
            ]
        ]);
    }

    /**
     * Guardar una nueva publicación.
     */
    public function store(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'candidato_id' => ['required', 'exists:candidatos,id'],
            'perfil_social_id' => ['nullable', 'exists:perfil_socials,id'],
            'plataforma' => ['nullable', 'string'],
            'eje_tematico_id' => ['nullable', 'exists:eje_tematicos,id'],
            'eje_tematico_nombre' => ['nullable', 'string', 'max:255'],
            'fecha_publicacion' => ['required', 'date'],
            'tipo_formato' => ['required', 'string'],
            'tipo_pauta' => ['required', 'string'],
            'monto_invertido_pauta' => ['nullable', 'numeric', 'min:0'],
            'url_post' => ['nullable', 'url', 'max:1000'],
            'media_url' => ['nullable', 'url', 'max:1000'],
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
            'total_guardados' => ['nullable', 'integer', 'min:0'],
            'termometro_humor_social' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comentario_destacado' => ['nullable', 'string'],
            'figura_acompanante' => ['nullable', 'string'],
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
                    'handle_usuario' => '@' . strtolower(str_replace(' ', '', $candidato->nombre_completo)),
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
                    'slug' => \Illuminate\Support\Str::slug($ejeNombre),
                    'color_badge' => '#06b6d4',
                    'descripcion' => 'Eje temático: ' . $ejeNombre,
                ]
            );
            $ejeId = $eje->id;
        }

        $vistasOrg = (int)($validated['vistas_organicas'] ?? 0);
        $vistasPag = (int)($validated['vistas_pagadas'] ?? 0);
        $totalVistas = $vistasOrg + $vistasPag;

        $comentariosDestacados = ! empty($validated['comentario_destacado'])
            ? [$validated['comentario_destacado']]
            : [];

        $figuras = ! empty($validated['figura_acompanante'])
            ? array_map('trim', explode(',', $validated['figura_acompanante']))
            : [];

        $aiEmocional = $this->calcularInteligenciaEmocional($validated, (int)($validated['total_likes'] ?? 0));

        $publicacion = Publicacion::create([
            'workspace_id' => $workspace->id,
            'candidato_id' => $validated['candidato_id'],
            'perfil_social_id' => $perfilSocialId,
            'eje_tematico_id' => $ejeId,
            'fecha_publicacion' => $validated['fecha_publicacion'],
            'tipo_formato' => $validated['tipo_formato'],
            'tipo_pauta' => $validated['tipo_pauta'],
            'monto_invertido_pauta' => $validated['tipo_pauta'] !== 'organico' ? ($validated['monto_invertido_pauta'] ?? 0) : 0,
            'url_post' => $validated['url_post'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
            'vistas_organicas' => $vistasOrg,
            'vistas_pagadas' => $vistasPag,
            'contenido_resumen' => $validated['contenido_resumen'],
            'total_vistas' => $totalVistas,
            'total_likes' => $aiEmocional['total_likes'],
            'total_comentarios' => (int)($validated['total_comentarios'] ?? 0),
            'total_compartidos' => (int)($validated['total_compartidos'] ?? 0),
            'total_guardados' => (int)($validated['total_guardados'] ?? 0),
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
    public function update(Request $request, Publicacion $publicacion): RedirectResponse
    {
        $validated = $request->validate([
            'contenido_resumen' => ['required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'url_post' => ['nullable', 'url', 'max:1000'],
            'media_url' => ['nullable', 'url', 'max:1000'],
            'tipo_formato' => ['required', 'string'],
            'tipo_pauta' => ['required', 'string'],
            'monto_invertido_pauta' => ['nullable', 'numeric', 'min:0'],
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
            'total_guardados' => ['nullable', 'integer', 'min:0'],
            'eje_tematico_id' => ['nullable', 'exists:eje_tematicos,id'],
            'termometro_humor_social' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $aiEmocional = $this->calcularInteligenciaEmocional($validated, (int)($validated['total_likes'] ?? $publicacion->total_likes));

        $updateData = [
            'contenido_resumen' => $validated['contenido_resumen'],
            'url_post' => $validated['url_post'] ?? null,
            'media_url' => $validated['media_url'] ?? null,
            'tipo_formato' => $validated['tipo_formato'],
            'tipo_pauta' => $validated['tipo_pauta'],
            'monto_invertido_pauta' => $validated['tipo_pauta'] !== 'organico' ? ($validated['monto_invertido_pauta'] ?? 0) : 0,
            'eje_tematico_id' => $validated['eje_tematico_id'] ?? null,
            'total_vistas' => (int)($validated['total_vistas'] ?? $publicacion->total_vistas),
            'total_likes' => $aiEmocional['total_likes'],
            'total_comentarios' => (int)($validated['total_comentarios'] ?? $publicacion->total_comentarios),
            'total_compartidos' => (int)($validated['total_compartidos'] ?? $publicacion->total_compartidos),
            'total_guardados' => (int)($validated['total_guardados'] ?? $publicacion->total_guardados),
            'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
            'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
            'termometro_humor_social' => $validated['termometro_humor_social'] ?? $aiEmocional['termometro_humor_social'],
            'insights_internos_propios' => array_merge($publicacion->insights_internos_propios ?? [], $aiEmocional['insights_internos_propios']),
        ];

        if (! empty($validated['fecha_publicacion'])) {
            $updateData['fecha_publicacion'] = $validated['fecha_publicacion'];
        }

        $publicacion->update($updateData);

        return redirect()->back()
            ->with('success', 'Publicación actualizada correctamente.');
    }

    /**
     * Motor de Inteligencia Emocional & Sentimiento Cuantificado.
     */
    private function calcularInteligenciaEmocional(array $data, int $totalLikesFallback = 0): array
    {
        $meGusta = (int)($data['me_gusta'] ?? 0);
        $meEncanta = (int)($data['me_encanta'] ?? 0);
        $meImporta = (int)($data['me_importa'] ?? 0);
        $meDivierte = (int)($data['me_divierte'] ?? 0);
        $meAsombra = (int)($data['me_asombra'] ?? 0);
        $meEntristece = (int)($data['me_entristece'] ?? 0);
        $meEnoja = (int)($data['me_enoja'] ?? 0);

        $totalReacciones = $meGusta + $meEncanta + $meImporta + $meDivierte + $meAsombra + $meEntristece + $meEnoja;

        if ($totalReacciones === 0 && $totalLikesFallback > 0) {
            $totalReacciones = $totalLikesFallback;
            $meGusta = (int)($totalLikesFallback * 0.7);
            $meEncanta = (int)($totalLikesFallback * 0.2);
            $meEnoja = (int)($totalLikesFallback * 0.1);
        }

        $positivas = $meGusta + $meEncanta + $meImporta;
        $negativas = $meEnoja + $meEntristece;
        $humorViral = $meDivierte;

        $indiceAprobacion = $totalReacciones > 0
            ? round((($positivas - $negativas) / $totalReacciones) * 100, 1)
            : 0;

        $ratioIndignacion = $totalReacciones > 0
            ? round(($meEnoja / $totalReacciones) * 100, 1)
            : 0;

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
