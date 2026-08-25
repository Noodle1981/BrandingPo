<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EventoCalendario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarioController extends Controller
{
    /**
     * Agenda de Campaña & Timeline de Eventos del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $tipo = $request->input('tipo');
        $candidatoId = $request->input('candidato_id');

        $query = EventoCalendario::where('workspace_id', $workspace->id)
            ->with(['candidato', 'cicloCampana'])
            ->orderBy('fecha_inicio');

        if ($tipo) {
            $query->where('tipo_evento', $tipo);
        }

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        $eventos = $query->get()->map(function ($e) {
            return [
                'id' => $e->id,
                'titulo' => $e->titulo,
                'fecha_inicio' => $e->fecha_inicio?->format('d/m/Y H:i'),
                'fecha_fin' => $e->fecha_fin?->format('d/m/Y H:i'),
                'tipo_evento' => $e->tipo_evento,
                'lugar' => $e->lugar,
                'estado' => $e->estado,
                'notas' => $e->notas,
                'candidato' => [
                    'id' => $e->candidato?->id,
                    'nombre' => $e->candidato?->nombre_completo,
                    'es_propio' => $e->candidato?->es_propio,
                ],
                'ciclo' => $e->cicloCampana?->nombre,
            ];
        });

        $ciclos = CicloCampana::where('workspace_id', $workspace->id)
            ->orderByDesc('anio')
            ->get(['id', 'nombre', 'anio']);

        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'es_propio']);

        return Inertia::render('Calendario/Index', [
            'eventos' => $eventos,
            'ciclos' => $ciclos,
            'candidatos' => $candidatos,
            'filtros' => [
                'tipo' => $tipo,
                'candidato_id' => $candidatoId,
            ],
            'tipos_disponibles' => [
                ['key' => 'acto', 'label' => 'Acto Público / Cierre'],
                ['key' => 'debate', 'label' => 'Debate Televisivo'],
                ['key' => 'pauta_vencimiento', 'label' => 'Vencimiento de Pauta'],
                ['key' => 'caravana', 'label' => 'Caravana / Recorrida'],
                ['key' => 'rueda_prensa', 'label' => 'Rueda de Prensa'],
                ['key' => 'reunion_privada', 'label' => 'Reunión Estratégica'],
            ],
        ]);
    }

    /**
     * Registrar evento en el calendario.
     */
    public function store(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'ciclo_campana_id' => ['required', 'exists:ciclo_campanas,id'],
            'candidato_id' => ['nullable', 'exists:candidatos,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'tipo_evento' => ['required', 'string'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:programado,realizado,cancelado'],
            'notas' => ['nullable', 'string'],
        ]);

        EventoCalendario::create([
            'workspace_id' => $workspace->id,
            ...$validated,
        ]);

        return redirect()->route('calendario.index')
            ->with('success', 'Evento programado en el calendario de campaña.');
    }

    /**
     * Eliminar evento del calendario.
     */
    public function destroy(EventoCalendario $evento): RedirectResponse
    {
        $evento->delete();

        return redirect()->route('calendario.index')
            ->with('success', 'Evento eliminado del calendario.');
    }
}
