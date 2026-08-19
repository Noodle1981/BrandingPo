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
     * Catálogo y tablero de candidatos políticos.
     */
    public function index(Request $request): Response
    {
        $cicloId = $request->input('ciclo_id');
        $estado = $request->input('estado');

        $query = Candidato::with(['cicloCampana', 'territorio', 'perfilesSociales']);

        if ($cicloId) {
            $query->where('ciclo_campana_id', $cicloId);
        }

        if ($estado) {
            $query->where('estado_politico', $estado);
        }

        $candidatos = $query->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
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
                    'es_propio' => $c->es_propio,
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
                ['key' => 'en_funciones', 'label' => 'En Funciones (Gestión)'],
                ['key' => 'candidato', 'label' => 'Candidato Oficial'],
                ['key' => 'precandidato', 'label' => 'Precandidato (Interna)'],
                ['key' => 'intendente_electo', 'label' => 'Intendente Electo'],
                ['key' => 'gobernador_electo', 'label' => 'Gobernador Electo'],
                ['key' => 'opositor', 'label' => 'Opositor'],
                ['key' => 'inactivo', 'label' => 'Inactivo'],
            ],
        ]);
    }

    /**
     * Ficha técnica detallada de un candidato.
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
     * Registrar un nuevo candidato político.
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
            'es_propio' => ['boolean'],
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
            'color_hex' => $validated['color_hex'] ?? '#06b6d4',
            'es_propio' => $request->boolean('es_propio'),
            'avatar_url' => $validated['avatar_url'] ?? null,
            'bio_resumen' => $validated['bio_resumen'] ?? null,
        ]);

        return redirect()->route('candidatos.index')
            ->with('success', "Candidato {$candidato->nombre_completo} registrado exitosamente.");
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
            'es_propio' => ['boolean'],
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
            'color_hex' => $validated['color_hex'] ?? '#06b6d4',
            'es_propio' => $request->boolean('es_propio'),
            'avatar_url' => $validated['avatar_url'] ?? null,
            'bio_resumen' => $validated['bio_resumen'] ?? null,
        ]);

        return redirect()->route('candidatos.index')
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
