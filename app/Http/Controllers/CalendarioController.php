<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Http\Requests\Calendario\StoreEventoCalendarioRequest;
use App\Models\Candidato;
use App\Models\EventoCalendario;
use App\Services\CalendarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarioController extends Controller
{
    public function __construct(
        protected CalendarioService $calendarioService
    ) {}

    /**
     * Agenda de Campaña & Timeline de Eventos centrado en Ejes Temáticos.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $agendaData = $this->calendarioService->obtenerAgendaData($workspace, $request->all());

        return Inertia::render('Calendario/Index', $agendaData);
    }

    /**
     * Registrar evento en el calendario.
     */
    public function store(StoreEventoCalendarioRequest $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $validated = $request->validated();

        if (empty($validated['candidato_id'])) {
            $propio = Candidato::delWorkspace($workspace)->propios()->first();
            $validated['candidato_id'] = $propio?->id;
        }

        EventoCalendario::create([
            'workspace_id' => $workspace->id,
            ...$validated,
        ]);

        return redirect()->route('calendario.index')
            ->with('success', 'Evento programado en la agenda de campaña.');
    }

    /**
     * Eliminar evento del calendario.
     */
    public function destroy(Request $request, EventoCalendario $evento): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($evento, $workspace);

        $evento->delete();

        return redirect()->route('calendario.index')
            ->with('success', 'Evento eliminado del calendario.');
    }
}
