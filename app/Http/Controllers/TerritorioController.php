<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Http\Requests\Territorio\StoreTerritorioRequest;
use App\Http\Requests\Territorio\UpdateTerritorioRequest;
use App\Models\Territorio;
use App\Services\AdsImpactPredictorService;
use App\Services\DemographicIntelligenceService;
use App\Services\TerritorioImpactoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TerritorioController extends Controller
{
    public function __construct(
        protected DemographicIntelligenceService $demographicService,
        protected AdsImpactPredictorService $adsPredictorService,
        protected TerritorioImpactoService $impactoService
    ) {}

    /**
     * Mapa de Situación Territorial & Inteligencia Demográfica del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $territorioId = $request->query('territorio_id') ? (int) $request->query('territorio_id') : null;

        $mapaData = $this->impactoService->obtenerMapaData($workspace, $territorioId);

        return Inertia::render('Territorios/Index', $mapaData);
    }

    /**
     * Autodetectar datos demográficos y coordenadas con 1 clic (Georef / INDEC).
     */
    public function autoDetect(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $this->demographicService->consultarGeoref(
            $request->input('nombre'),
            $request->input('provincia', 'San Juan')
        );

        if (! empty($data['poblacion_total']) && ! empty($data['padron_electoral'])) {
            $data['piramide'] = $this->demographicService->generarPiramideEtaria(
                (int) $data['poblacion_total'],
                (int) $data['padron_electoral']
            );
            $data['estrategia'] = $this->demographicService->recomendarEstrategiaDigital(
                $data['piramide'],
                (float) ($data['poblacion_urbana_pct'] ?? 70.0)
            );
        }

        return response()->json($data);
    }

    /**
     * Guardar nuevo territorio.
     */
    public function store(StoreTerritorioRequest $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $validated = $request->validated();

        $poblacion = (int) ($validated['poblacion_total'] ?? 40000);
        $padron = (int) ($validated['padron_electoral'] ?? 30000);
        $piramide = $this->demographicService->generarPiramideEtaria($poblacion, $padron);

        $territorio = Territorio::create([
            'workspace_id' => $workspace->id,
            ...$validated,
            'poblacion_total' => $poblacion,
            'padron_electoral' => $padron,
            'poblacion_urbana_pct' => $validated['poblacion_urbana_pct'] ?? 70.0,
            'poblacion_rural_pct' => $validated['poblacion_rural_pct'] ?? 30.0,
            'hogares_nbi_pct' => $validated['hogares_nbi_pct'] ?? 15.0,
            'piramide_etaria' => $piramide,
        ]);

        return redirect()->back()
            ->with('success', "Territorio {$territorio->nombre} registrado satisfactoriamente.");
    }

    /**
     * Actualizar territorio y recalcular pirámide demográfica.
     */
    public function update(UpdateTerritorioRequest $request, Territorio $territorio): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($territorio, $workspace);

        $validated = $request->validated();

        $poblacion = (int) ($validated['poblacion_total'] ?? $territorio->poblacion_total);
        $padron = (int) ($validated['padron_electoral'] ?? $territorio->padron_electoral);
        $piramide = $this->demographicService->generarPiramideEtaria($poblacion, $padron);

        $territorio->update([
            ...$validated,
            'piramide_etaria' => $piramide,
        ]);

        return redirect()->back()
            ->with('success', "Territorio {$territorio->nombre} y pirámide demográfica actualizados.");
    }

    /**
     * Matriz de Impacto Territorial & Penetración Electoral.
     */
    public function impactoElectoral(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $territorioId = $request->query('territorio_id') ? (int) $request->query('territorio_id') : null;

        $impactoData = $this->impactoService->obtenerImpactoElectoralData($workspace, $territorioId);

        return Inertia::render('Territorios/ImpactoElectoral', $impactoData);
    }
}
