<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\MedioPrensa;
use App\Models\NotaPrensa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediosController extends Controller
{
    /**
     * Observatorio de Medios Tradicionales & Clipping Informativo.
     */
    public function index(Request $request): Response
    {
        $candidatoId = $request->input('candidato_id');
        $tono = $request->input('tono');
        $medioId = $request->input('medio_id');

        $query = NotaPrensa::with(['medioPrensa', 'candidato'])
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

        $notas = $query->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'titulo' => $n->titulo,
                'resumen' => $n->resumen,
                'url_nota' => $n->url_nota,
                'fecha' => $n->fecha_publicacion?->format('d/m/Y'),
                'tono_mencion' => $n->tono_mencion,
                'es_tapa_o_principal' => $n->es_tapa_o_principal,
                'interacciones' => $n->interacciones_en_redes_del_medio,
                'respuesta_replica' => $n->respuesta_replica_candidato,
                'medio' => [
                    'id' => $n->medioPrensa?->id,
                    'nombre' => $n->medioPrensa?->nombre,
                    'tipo_medio' => $n->medioPrensa?->tipo_medio,
                    'sesgo' => $n->medioPrensa?->sesgo_editorial_estimado,
                ],
                'candidato' => [
                    'id' => $n->candidato?->id,
                    'nombre_completo' => $n->candidato?->nombre_completo,
                    'es_propio' => $n->candidato?->es_propio,
                    'avatar_url' => $n->candidato?->avatar_url,
                ],
            ];
        });

        $medios = MedioPrensa::withCount('notasPrensa')->orderBy('nombre')->get();
        $candidatos = Candidato::orderByDesc('es_propio')->orderBy('nombre_completo')->get(['id', 'nombre_completo', 'es_propio']);

        $todasNotas = NotaPrensa::all();
        $totalFavorables = $todasNotas->where('tono_mencion', 'favorable')->count();
        $totalNeutras = $todasNotas->where('tono_mencion', 'neutro')->count();
        $totalCriticas = $todasNotas->where('tono_mencion', 'critico')->count();

        return Inertia::render('Medios/Index', [
            'notas' => $notas,
            'medios' => $medios,
            'candidatos' => $candidatos,
            'filtros' => [
                'candidato_id' => $candidatoId,
                'tono' => $tono,
                'medio_id' => $medioId,
            ],
            'resumen_tonos' => [
                'favorables' => $totalFavorables,
                'neutras' => $totalNeutras,
                'criticas' => $totalCriticas,
                'total' => $todasNotas->count(),
            ]
        ]);
    }

    /**
     * Registrar una nueva nota en el clipping.
     */
    public function storeNota(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medio_prensa_id' => ['required', 'exists:medios_prensa,id'],
            'candidato_id' => ['nullable', 'exists:candidatos,id'],
            'fecha_publicacion' => ['required', 'date'],
            'titulo' => ['required', 'string', 'max:500'],
            'resumen' => ['nullable', 'string'],
            'url_nota' => ['nullable', 'url', 'max:500'],
            'tono_mencion' => ['required', 'in:favorable,neutro,critico'],
            'es_tapa_o_principal' => ['boolean'],
            'interacciones_en_redes_del_medio' => ['nullable', 'integer', 'min:0'],
            'respuesta_replica_candidato' => ['nullable', 'string'],
        ]);

        NotaPrensa::create([
            'medio_prensa_id' => $validated['medio_prensa_id'],
            'candidato_id' => $validated['candidato_id'] ?? null,
            'fecha_publicacion' => $validated['fecha_publicacion'],
            'titulo' => $validated['titulo'],
            'resumen' => $validated['resumen'] ?? null,
            'url_nota' => $validated['url_nota'] ?? null,
            'tono_mencion' => $validated['tono_mencion'],
            'es_tapa_o_principal' => $request->boolean('es_tapa_o_principal'),
            'interacciones_en_redes_del_medio' => (int)($validated['interacciones_en_redes_del_medio'] ?? 0),
            'respuesta_replica_candidato' => $validated['respuesta_replica_candidato'] ?? null,
        ]);

        return redirect()->route('medios.index')
            ->with('success', 'Nota de prensa agregada al clipping exitosamente.');
    }

    /**
     * Eliminar nota de prensa.
     */
    public function destroyNota(NotaPrensa $nota): RedirectResponse
    {
        $nota->delete();

        return redirect()->route('medios.index')
            ->with('success', 'Nota eliminada del clipping.');
    }
}
