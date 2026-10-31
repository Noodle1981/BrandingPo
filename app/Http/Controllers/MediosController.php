<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Http\Requests\Medios\DetectarFuentesMedioRequest;
use App\Http\Requests\Medios\StoreMedioRequest;
use App\Http\Requests\Medios\StoreNotaRequest;
use App\Http\Requests\Medios\UpdateMedioRequest;
use App\Http\Requests\Medios\UpdateNotaRequest;
use App\Models\Candidato;
use App\Models\MedioPrensa;
use App\Models\NotaPrensa;
use App\Services\GenericMediaScraperService;
use App\Services\MediosClippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediosController extends Controller
{
    public function __construct(
        protected GenericMediaScraperService $mediaScraper,
        protected MediosClippingService $clippingService
    ) {}

    /**
     * Observatorio de Medios Tradicionales & Clipping Informativo del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $payload = $this->clippingService->obtenerObservatorioData($workspace, $request->all());

        return Inertia::render('Medios/Index', $payload);
    }

    /**
     * Registrar un nuevo medio de prensa en el workspace activo.
     */
    public function storeMedio(StoreMedioRequest $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $validated = $request->validated();

        MedioPrensa::create(array_merge($validated, [
            'workspace_id' => $workspace->id,
            'territorio_id' => $workspace->territorios()->first()?->id,
        ]));

        return redirect()->route('medios.index')
            ->with('success', "Medio de prensa '{$validated['nombre']}' registrado exitosamente.");
    }

    /**
     * Actualizar datos de un medio de prensa.
     */
    public function updateMedio(UpdateMedioRequest $request, MedioPrensa $medio): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($medio, $workspace);

        $validated = $request->validated();
        $medio->update($validated);

        return redirect()->route('medios.index')
            ->with('success', "Medio de prensa '{$medio->nombre}' actualizado.");
    }

    /**
     * Eliminar medio de prensa y sus notas asociadas.
     */
    public function destroyMedio(Request $request, MedioPrensa $medio): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($medio, $workspace);

        $nombre = $medio->nombre;
        $medio->delete();

        return redirect()->route('medios.index')
            ->with('success', "Medio '{$nombre}' eliminado del observatorio.");
    }

    /**
     * Autodescubrir feed RSS y avatar de Facebook en 1 clic.
     */
    public function detectarFuentes(DetectarFuentesMedioRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $urlSitio = $validated['url_sitio'] ?? null;
        $urlFacebook = $validated['url_facebook'] ?? null;

        if (empty($urlSitio) && empty($urlFacebook)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Debes ingresar al menos la URL del portal web o de Facebook para analizar.',
            ], 422);
        }

        $resultado = $this->mediaScraper->autodescubrirFuentes($urlSitio ?: '', $urlFacebook);

        return response()->json($resultado);
    }

    /**
     * Sincronizar un medio individualmente.
     */
    public function sincronizarMedio(Request $request, MedioPrensa $medio): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($medio, $workspace);

        $candidatos = Candidato::delWorkspace($workspace->id)
            ->with('perfilesSociales')
            ->get();

        $stats = $this->mediaScraper->sincronizarMedio($medio, $candidatos);

        $msg = "Sincronización de '{$medio->nombre}' completada: {$stats['notas_analizadas']} notas analizadas, {$stats['menciones_detectadas']} menciones encontradas ({$stats['nuevas_notas']} nuevas).";

        return redirect()->route('medios.index')
            ->with('success', $msg);
    }

    /**
     * Sincronizar todos los medios registrados en el workspace activo.
     */
    public function sincronizarTodos(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $resultado = $this->clippingService->sincronizarTodos($workspace, $this->mediaScraper);

        $flashKey = ! empty($resultado['success']) ? 'success' : 'error';

        return redirect()->route('medios.index')->with($flashKey, $resultado['mensaje']);
    }

    /**
     * Registrar una nueva nota manual en el clipping.
     */
    public function storeNota(StoreNotaRequest $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $this->clippingService->guardarNota(
            $workspace,
            $request->validated(),
            $request->input('reacciones_desglose'),
            $request->boolean('es_tapa_o_principal')
        );

        return redirect()->route('medios.index')
            ->with('success', 'Nota de prensa agregada al clipping exitosamente.');
    }

    /**
     * Actualizar una nota existente en el clipping.
     */
    public function updateNota(UpdateNotaRequest $request, NotaPrensa $nota): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($nota, $workspace);

        $this->clippingService->actualizarNota(
            $nota,
            $request->validated(),
            $request->input('reacciones_desglose'),
            $request->boolean('es_tapa_o_principal')
        );

        return redirect()->route('medios.index')
            ->with('success', 'Nota de prensa actualizada.');
    }

    /**
     * Eliminar nota de prensa del clipping.
     */
    public function destroyNota(Request $request, NotaPrensa $nota): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($nota, $workspace);

        $nota->delete();

        return redirect()->route('medios.index')
            ->with('success', 'Nota eliminada del clipping.');
    }
}
