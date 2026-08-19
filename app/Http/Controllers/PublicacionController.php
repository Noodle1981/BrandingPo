<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Publicacion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicacionController extends Controller
{
    /**
     * Muro interactivo estilo red social (Social Wall).
     */
    public function feed(Request $request): Response
    {
        $candidatoId = $request->input('candidato_id');
        $plataforma = $request->input('plataforma');
        $tipoPauta = $request->input('tipo_pauta');
        $search = $request->input('search');

        $query = Publicacion::with(['candidato', 'perfilSocial', 'ejeTematico'])
            ->orderByDesc('fecha_publicacion');

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

        $candidatos = Candidato::orderByDesc('es_propio')->orderBy('nombre_completo')->get(['id', 'nombre_completo', 'estado_politico', 'es_propio', 'avatar_url']);
        $ejes = EjeTematico::orderBy('nombre')->get(['id', 'nombre', 'color_badge']);

        return Inertia::render('Publicaciones/Feed', [
            'publicaciones' => $publicaciones,
            'candidatos' => $candidatos,
            'ejes' => $ejes,
            'filtros' => [
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
     * Consola ergonómica de carga rápida Fast-Flow.
     */
    public function fastFlow(): Response
    {
        $candidatos = Candidato::with('perfilesSociales')->orderByDesc('es_propio')->orderBy('nombre_completo')->get();
        $ejes = EjeTematico::orderBy('nombre')->get(['id', 'nombre', 'color_badge']);

        $ultimasCargas = Publicacion::with(['candidato', 'perfilSocial'])
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'candidato' => $p->candidato?->nombre_completo,
                'plataforma' => $p->perfilSocial?->plataforma,
                'tipo_formato' => $p->tipo_formato,
                'tipo_pauta' => $p->tipo_pauta,
                'monto' => $p->monto_invertido_pauta,
                'vistas' => $p->total_vistas,
                'likes' => $p->total_likes,
                'fecha' => $p->fecha_publicacion?->format('d/m H:i'),
            ]);

        return Inertia::render('Publicaciones/FastFlow', [
            'candidatos' => $candidatos,
            'ejes' => $ejes,
            'ultimas_cargas' => $ultimasCargas,
        ]);
    }

    /**
     * Guardar una publicación cargada desde Fast-Flow.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'candidato_id' => ['required', 'exists:candidatos,id'],
            'perfil_social_id' => ['required', 'exists:perfil_socials,id'],
            'eje_tematico_id' => ['nullable', 'exists:eje_tematicos,id'],
            'fecha_publicacion' => ['required', 'date'],
            'tipo_formato' => ['required', 'string'],
            'tipo_pauta' => ['required', 'in:organico,pauta_paga'],
            'monto_invertido_pauta' => ['nullable', 'numeric', 'min:0'],
            'vistas_organicas' => ['nullable', 'integer', 'min:0'],
            'vistas_pagadas' => ['nullable', 'integer', 'min:0'],
            'contenido_resumen' => ['required', 'string'],
            'total_likes' => ['nullable', 'integer', 'min:0'],
            'total_comentarios' => ['nullable', 'integer', 'min:0'],
            'total_compartidos' => ['nullable', 'integer', 'min:0'],
            'termometro_humor_social' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comentario_destacado' => ['nullable', 'string'],
            'figura_acompanante' => ['nullable', 'string'],
        ], [
            'candidato_id.required' => 'Debes seleccionar un candidato.',
            'perfil_social_id.required' => 'Debes seleccionar la red social.',
            'contenido_resumen.required' => 'El texto o resumen del post es obligatorio.',
        ]);

        $vistasOrg = (int)($validated['vistas_organicas'] ?? 0);
        $vistasPag = (int)($validated['vistas_pagadas'] ?? 0);
        $totalVistas = $vistasOrg + $vistasPag;
        $totalLikes = (int)($validated['total_likes'] ?? 0);

        $comentariosDestacados = ! empty($validated['comentario_destacado'])
            ? [$validated['comentario_destacado']]
            : [];

        $figuras = ! empty($validated['figura_acompanante'])
            ? array_map('trim', explode(',', $validated['figura_acompanante']))
            : [];

        $humor = (int)($validated['termometro_humor_social'] ?? 3);
        $sentimiento = $humor >= 4 ? 'positivo' : ($humor === 3 ? 'neutro' : 'negativo');

        Publicacion::create([
            'candidato_id' => $validated['candidato_id'],
            'perfil_social_id' => $validated['perfil_social_id'],
            'eje_tematico_id' => $validated['eje_tematico_id'] ?? null,
            'fecha_publicacion' => $validated['fecha_publicacion'],
            'tipo_formato' => $validated['tipo_formato'],
            'tipo_pauta' => $validated['tipo_pauta'],
            'monto_invertido_pauta' => $validated['monto_invertido_pauta'] ?? 0,
            'vistas_organicas' => $vistasOrg,
            'vistas_pagadas' => $vistasPag,
            'contenido_resumen' => $validated['contenido_resumen'],
            'total_vistas' => $totalVistas,
            'total_likes' => $totalLikes,
            'total_comentarios' => (int)($validated['total_comentarios'] ?? 0),
            'total_compartidos' => (int)($validated['total_compartidos'] ?? 0),
            'reacciones_detalladas' => [
                'me_gusta' => (int)($totalLikes * 0.7),
                'me_encanta' => (int)($totalLikes * 0.2),
                'me_enoja' => (int)($totalLikes * 0.1),
            ],
            'sentimiento_predominante' => $sentimiento,
            'figuras_acompanantes' => $figuras,
            'comentarios_destacados' => $comentariosDestacados,
            'termometro_humor_social' => $humor,
        ]);

        return redirect()->back()
            ->with('success', 'Publicación registrada con éxito en Fast-Flow.');
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
