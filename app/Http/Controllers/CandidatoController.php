<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\PerfilSocial;
use App\Models\Territorio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CandidatoController extends Controller
{
    /**
     * Catálogo de la Oposición y Candidatos Rivales (Competencia).
     */
    public function index(Request $request): Response
    {
        $cicloId = $request->input('ciclo_id');
        $estado = $request->input('estado');

        // Filtrar exclusivamente a los opositores / rivales para no mezclar con el propio
        $query = Candidato::where('es_propio', false)
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

        $ciclos = CicloCampana::orderByDesc('anio')->get(['id', 'anio', 'nombre', 'es_activo']);
        $territorios = Territorio::orderBy('nombre')->get(['id', 'nombre', 'tipo']);

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
     * Vista de Gestión Exclusiva del Perfil Propio (Cliente / Campaña).
     */
    public function miCandidato(Request $request): Response
    {
        $cicloActivo = CicloCampana::where('es_activo', true)->first() ?? CicloCampana::first();
        $territorioDefault = Territorio::first();

        // Buscar o inicializar el candidato propio
        $candidato = Candidato::where('es_propio', true)
            ->with(['perfilesSociales', 'territorio', 'cicloCampana'])
            ->first();

        if (!$candidato) {
            $candidato = Candidato::create([
                'nombre_completo' => 'Mi Candidato (Cliente)',
                'partido_coalicion' => 'Frente de Campaña',
                'cargo_aspirado' => 'Candidato a Intendente',
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

            // Determinar color de la pestaña:
            // 🔵 'azul' si está certificada/verificada
            // 🟠 'naranja' si está activa/vinculada
            // 🔴 'rojo' si no la tiene / inactiva
            $colorEstado = $estaVerificado ? 'azul' : ($estaActivo ? 'naranja' : 'rojo');

            $seguidoresActuales = $perfil ? (int)$perfil->seguidores_actuales : 0;
            $seguidoresBaseline = $perfil ? (int)$perfil->seguidores_punto_cero : 0;
            $crecimientoSeguidores = $seguidoresActuales - $seguidoresBaseline;

            $postsActuales = $perfil ? (int)$perfil->publicaciones_totales : 0;
            $postsBaseline = $perfil ? (int)$perfil->publicaciones_punto_cero : 0;
            $crecimientoPosts = $postsActuales - $postsBaseline;

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
                // Punto Cero (Baseline Inicial)
                'fecha_punto_cero' => $perfil?->fecha_punto_cero ? $perfil->fecha_punto_cero->format('Y-m-d') : date('Y-m-d'),
                'seguidores_punto_cero' => $seguidoresBaseline,
                'seguidos_punto_cero' => $perfil ? (int)$perfil->seguidos_punto_cero : 0,
                'publicaciones_punto_cero' => $postsBaseline,
                'notas_punto_cero' => $perfil?->notas_punto_cero ?? '',
                'crecimiento_neto_seguidores' => $crecimientoSeguidores,
                'crecimiento_neto_posts' => $crecimientoPosts,
            ];
        })->values();

        $ciclos = CicloCampana::orderByDesc('anio')->get(['id', 'anio', 'nombre', 'es_activo']);
        $territorios = Territorio::orderBy('nombre')->get(['id', 'nombre', 'tipo', 'poblacion_total', 'padron_electoral']);

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
            'fecha_punto_cero' => ['nullable', 'date'],
            'seguidores_punto_cero' => ['nullable', 'integer', 'min:0'],
            'seguidos_punto_cero' => ['nullable', 'integer', 'min:0'],
            'publicaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
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
                'seguidores_actuales' => (int)($validated['seguidores_actuales'] ?? 0),
                'seguidos_actuales' => (int)($validated['seguidos_actuales'] ?? 0),
                'publicaciones_totales' => (int)($validated['publicaciones_totales'] ?? 0),
                'fecha_punto_cero' => $validated['fecha_punto_cero'] ?? now(),
                'seguidores_punto_cero' => (int)($validated['seguidores_punto_cero'] ?? $validated['seguidores_actuales'] ?? 0),
                'seguidos_punto_cero' => (int)($validated['seguidos_punto_cero'] ?? $validated['seguidos_actuales'] ?? 0),
                'publicaciones_punto_cero' => (int)($validated['publicaciones_punto_cero'] ?? $validated['publicaciones_totales'] ?? 0),
                'notas_punto_cero' => $validated['notas_punto_cero'] ?? null,
            ]
        );

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
            'fecha_punto_cero' => ['nullable', 'date'],
            'seguidores_punto_cero' => ['nullable', 'integer', 'min:0'],
            'seguidos_punto_cero' => ['nullable', 'integer', 'min:0'],
            'publicaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
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
     * Ficha técnica de un candidato.
     */
    public function show(Candidato $candidato): Response
    {
        $candidato->load(['cicloCampana', 'territorio', 'perfilesSociales']);

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
                'ciclo_campana' => $candidato->cicloCampana,
                'territorio' => $candidato->territorio,
                'perfiles_sociales' => $candidato->perfilesSociales,
                'total_seguidores' => $candidato->perfilesSociales->sum('seguidores_actuales'),
            ],
        ]);
    }

    /**
     * Registrar un nuevo candidato opositor / rival.
     */
    public function store(Request $request): RedirectResponse
    {
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

        return redirect()->route('candidatos.index')
            ->with('success', "Candidato opositor {$candidato->nombre_completo} registrado exitosamente.");
    }

    /**
     * Actualizar datos del candidato.
     */
    public function update(Request $request, Candidato $candidato): RedirectResponse
    {
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

        $candidato->update([
            'nombre_completo' => $validated['nombre_completo'],
            'partido_coalicion' => $validated['partido_coalicion'],
            'cargo_aspirado' => $validated['cargo_aspirado'] ?? null,
            'estado_politico' => $validated['estado_politico'],
            'ciclo_campana_id' => $validated['ciclo_campana_id'],
            'territorio_id' => $validated['territorio_id'] ?? null,
            'color_hex' => $validated['color_hex'] ?? $candidato->color_hex,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'bio_resumen' => $validated['bio_resumen'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', "Perfil de {$candidato->nombre_completo} actualizado correctamente.");
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
}
