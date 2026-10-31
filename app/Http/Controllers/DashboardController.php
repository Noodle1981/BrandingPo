<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Services\DashboardAggregationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardAggregationService $aggregationService
    ) {}

    /**
     * Tablero Central enfocado en el Perfil del Cliente / Candidato Propio del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidatoId = $request->input('candidato_id');

        // Obtener el candidato objetivo dentro del workspace: por ID solicitado o el candidato propio por defecto
        $candidatoQuery = Candidato::delWorkspace($workspace)
            ->with(['perfilesSociales', 'territorio', 'cicloCampana']);

        $candidato = $candidatoId
            ? (clone $candidatoQuery)->find($candidatoId)
            : (clone $candidatoQuery)->propios()->first();

        // Fallback si no hay candidato propio marcado en el workspace
        if (! $candidato) {
            $candidato = (clone $candidatoQuery)->first();
        }

        // Listado de candidatos del workspace para selector
        $todosCandidatos = Candidato::delWorkspace($workspace)
            ->select('id', 'nombre_completo', 'partido_coalicion', 'cargo_aspirado', 'estado_politico', 'color_hex', 'es_propio', 'avatar_url')
            ->get();

        if (! $candidato) {
            return Inertia::render('Dashboard', [
                'candidato' => null,
                'candidatos_lista' => [],
                'stats' => [],
                'redes_desglose' => [],
                'ultimas_publicaciones' => [],
                'ultimas_notas_prensa' => [],
                'benchmark' => null,
            ]);
        }

        $periodo = (string) $request->input('periodo', 'todos');
        $payload = $this->aggregationService->compilarPayload($workspace, $candidato, $todosCandidatos, $periodo);

        return Inertia::render('Dashboard', $payload);
    }
}
