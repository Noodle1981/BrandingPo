<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Services\EjesEstrategicosService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EjesEstrategicosController extends Controller
{
    public function __construct(
        protected EjesEstrategicosService $ejesService
    ) {}

    /**
     * Tablero de Situación Estratégica de los 3 Ejes Principales de Campaña.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $periodo = (string) $request->query('mes', 'todos');

        $tableroData = $this->ejesService->obtenerTableroEstrategico($workspace, $periodo);

        return Inertia::render('Ejes/Index', $tableroData);
    }
}
