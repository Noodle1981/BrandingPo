<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\AlianzaPolitica;
use App\Models\Candidato;
use App\Models\EventoCrisis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrisisController extends Controller
{
    /**
     * Centro de Situación de Crisis & Matriz de Alianzas del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidatoId = $request->input('candidato_id');

        $queryCrisis = EventoCrisis::where('workspace_id', $workspace->id)
            ->with('candidato')
            ->orderByDesc('fecha_evento');

        $queryAlianzas = AlianzaPolitica::where('workspace_id', $workspace->id)
            ->with('candidato')
            ->orderBy('nombre_figura');

        if ($candidatoId) {
            $queryCrisis->where('candidato_id', $candidatoId);
            $queryAlianzas->where('candidato_id', $candidatoId);
        }

        $eventos = $queryCrisis->get()->map(function ($e) {
            return [
                'id' => $e->id,
                'titulo' => $e->titulo,
                'fecha' => $e->fecha_evento?->format('d/m/Y H:i'),
                'nivel_gravedad' => $e->nivel_gravedad,
                'minutos_tiempo_respuesta' => $e->minutos_tiempo_respuesta,
                'estrategia_contencion' => $e->estrategia_contencion,
                'estado' => $e->estado,
                'impacto_estimado' => $e->impacto_estimado,
                'candidato' => [
                    'id' => $e->candidato?->id,
                    'nombre_completo' => $e->candidato?->nombre_completo,
                    'es_propio' => $e->candidato?->es_propio,
                ],
            ];
        });

        $alianzas = $queryAlianzas->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'nombre_figura' => $a->nombre_figura,
                'cargo_o_rol' => $a->cargo_o_rol,
                'tipo_impacto' => $a->tipo_impacto,
                'notas_observacion' => $a->notas_observacion,
                'candidato' => [
                    'id' => $a->candidato?->id,
                    'nombre_completo' => $a->candidato?->nombre_completo,
                ],
            ];
        });

        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'es_propio']);

        $totalCriticos = $eventos->where('nivel_gravedad', 'critico')->where('estado', '!=', 'resuelto')->count();
        $totalModerados = $eventos->where('nivel_gravedad', 'moderado')->where('estado', '!=', 'resuelto')->count();
        $totalResueltos = $eventos->where('estado', 'resuelto')->count();
        $promedioRespuestaMin = (int)$eventos->avg('minutos_tiempo_respuesta');

        return Inertia::render('Crisis/Index', [
            'eventos' => $eventos,
            'alianzas' => $alianzas,
            'candidatos' => $candidatos,
            'filtros' => [
                'candidato_id' => $candidatoId,
            ],
            'semaforo' => [
                'criticos_activos' => $totalCriticos,
                'moderados_activos' => $totalModerados,
                'resueltos' => $totalResueltos,
                'promedio_tiempo_min' => $promedioRespuestaMin,
            ],
        ]);
    }

    /**
     * Registrar un nuevo incidente de crisis.
     */
    public function storeCrisis(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'candidato_id' => ['required', 'exists:candidatos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_evento' => ['required', 'date'],
            'nivel_gravedad' => ['required', 'in:leve,moderado,critico'],
            'minutos_tiempo_respuesta' => ['nullable', 'integer', 'min:0'],
            'estrategia_contencion' => ['nullable', 'string'],
            'estado' => ['required', 'in:abierto,en_contencion,resuelto'],
            'impacto_estimado' => ['nullable', 'string'],
        ]);

        EventoCrisis::create([
            'workspace_id' => $workspace->id,
            'candidato_id' => $validated['candidato_id'],
            'titulo' => $validated['titulo'],
            'fecha_evento' => $validated['fecha_evento'],
            'nivel_gravedad' => $validated['nivel_gravedad'],
            'minutos_tiempo_respuesta' => (int)($validated['minutos_tiempo_respuesta'] ?? 0),
            'estrategia_contencion' => $validated['estrategia_contencion'] ?? null,
            'estado' => $validated['estado'],
            'impacto_estimado' => $validated['impacto_estimado'] ?? 'Medio',
        ]);

        return redirect()->route('crisis.index')
            ->with('success', 'Incidente de crisis registrado en el semáforo.');
    }

    /**
     * Actualizar estado o contención de crisis.
     */
    public function updateCrisis(Request $request, EventoCrisis $crisis): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:abierto,en_contencion,resuelto'],
            'estrategia_contencion' => ['nullable', 'string'],
            'minutos_tiempo_respuesta' => ['nullable', 'integer', 'min:0'],
        ]);

        $crisis->update($validated);

        return redirect()->route('crisis.index')
            ->with('success', 'Estado del evento de crisis actualizado.');
    }

    /**
     * Registrar una alianza o figura de respaldo político.
     */
    public function storeAlianza(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'candidato_id' => ['required', 'exists:candidatos,id'],
            'nombre_figura' => ['required', 'string', 'max:255'],
            'cargo_o_rol' => ['required', 'string', 'max:255'],
            'tipo_impacto' => ['required', 'in:suma,resta,neutro'],
            'notas_observacion' => ['nullable', 'string'],
        ]);

        AlianzaPolitica::create([
            'workspace_id' => $workspace->id,
            ...$validated,
        ]);

        return redirect()->route('crisis.index')
            ->with('success', 'Figura política incorporada a la matriz de alianzas.');
    }

    /**
     * Eliminar alianza.
     */
    public function destroyAlianza(AlianzaPolitica $alianza): RedirectResponse
    {
        $alianza->delete();

        return redirect()->route('crisis.index')
            ->with('success', 'Alianza eliminada.');
    }
}
