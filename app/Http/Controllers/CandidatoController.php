<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Http\Requests\Candidato\ScrapePerfilSocialRequest;
use App\Http\Requests\Candidato\StoreCandidatoRequest;
use App\Http\Requests\Candidato\StorePerfilSocialRequest;
use App\Http\Requests\Candidato\UpdateCandidatoRequest;
use App\Http\Requests\Candidato\UpdatePerfilSocialRequest;
use App\Models\Candidato;
use App\Models\PerfilSocial;
use App\Services\CanalMetricasService;
use App\Services\CanalSyncService;
use App\Services\CandidatoPresenterService;
use App\Services\MediaStorageService;
use App\Services\SocialProfileScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CandidatoController extends Controller
{
    public function __construct(
        protected CandidatoPresenterService $candidatoPresenter,
        protected CanalMetricasService $canalMetricasService,
        protected CanalSyncService $canalSyncService
    ) {}

    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('mi-candidato');
    }

    public function benchmarking(Request $request): RedirectResponse
    {
        return redirect()->route('mi-candidato');
    }

    /**
     * Vista de Gestión Exclusiva del Perfil Propio (Cliente / Campaña) del Workspace Activo.
     */
    public function miCandidato(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidato = $this->candidatoPresenter->obtenerOInicializarPropio($workspace);

        return Inertia::render('Candidatos/MiPerfil', $this->candidatoPresenter->prepararPayloadDetalle($candidato, $request));
    }

    /**
     * Guardar o actualizar la configuración de una red social y su Punto Cero.
     */
    public function storePerfilSocial(StorePerfilSocialRequest $request, MediaStorageService $mediaStorage): RedirectResponse
    {
        $val = $request->validated();
        $this->canalSyncService->guardarPuntoCero($val, $mediaStorage);

        return redirect()->back()->with('success', "Canal {$val['plataforma']} configurado y Punto Cero establecido.");
    }

    /**
     * Auto-extracción de datos desde el enlace público del perfil.
     */
    public function scrapePerfilSocial(ScrapePerfilSocialRequest $request, SocialProfileScraperService $scraper): JsonResponse
    {
        $validated = $request->validated();

        return response()->json($scraper->scrapeProfile($validated['url'], $validated['plataforma']));
    }

    /**
     * Re-auditar / Refrescar métricas en vivo para un perfil social específico.
     */
    public function refrescarPerfilSocial(
        Request $request,
        PerfilSocial $perfilSocial,
        SocialProfileScraperService $scraper,
        MediaStorageService $mediaStorage
    ): JsonResponse|RedirectResponse {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);

        $resultado = $this->canalSyncService->auditarPerfil($perfilSocial, $scraper, $mediaStorage);

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json($resultado, ! empty($resultado['success']) ? 200 : 422);
        }

        $flashKey = ! empty($resultado['success']) ? 'success' : 'error';

        return redirect()->back()->with($flashKey, $resultado['mensaje']);
    }

    /**
     * Sincronización Maestra del Canal (Seguidores + Publicaciones activas en ventana <=15 días).
     */
    public function sincronizarCanal(
        Request $request,
        PerfilSocial $perfilSocial,
        SocialProfileScraperService $scraper,
        MediaStorageService $mediaStorage
    ): JsonResponse {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);

        return response()->json($this->canalSyncService->sincronizarCanalMaestro($perfilSocial, $scraper, $mediaStorage));
    }

    /**
     * Actualizar perfil social existente.
     */
    public function updatePerfilSocial(UpdatePerfilSocialRequest $request, PerfilSocial $perfilSocial): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);
        $perfilSocial->update($request->validated());

        return redirect()->back()
            ->with('success', "Datos y Punto Cero de {$perfilSocial->plataforma} actualizados correctamente.");
    }

    /**
     * Eliminar perfil social.
     */
    public function destroyPerfilSocial(Request $request, PerfilSocial $perfilSocial): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);
        $plataforma = $perfilSocial->plataforma;
        $perfilSocial->delete();

        return redirect()->back()
            ->with('success', "Canal {$plataforma} desvinculado.");
    }

    /**
     * Ficha técnica y gestión de canales / Punto Cero de un candidato rival.
     */
    public function show(Request $request, Candidato $candidato): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($candidato, $workspace);

        return Inertia::render('Candidatos/Show', $this->candidatoPresenter->prepararPayloadDetalle($candidato, $request));
    }

    /**
     * Registrar un nuevo candidato opositor / rival en el workspace.
     */
    public function store(StoreCandidatoRequest $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidato = $this->candidatoPresenter->crearRival($workspace, $request->validated());

        return redirect()->route('candidatos.show', $candidato->id)
            ->with('success', "Candidato rival {$candidato->nombre_completo} registrado. Ahora puedes vincular sus redes sociales y Punto Cero.");
    }

    /**
     * Actualizar datos del candidato y su territorio geográfico.
     */
    public function update(UpdateCandidatoRequest $request, Candidato $candidato): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($candidato, $workspace);
        $this->candidatoPresenter->actualizarCandidato($candidato, $workspace, $request->validated());

        return redirect()->back()
            ->with('success', "Datos y territorio geográfico de {$candidato->nombre_completo} actualizados correctamente.");
    }

    /**
     * Eliminar un candidato.
     */
    public function destroy(Request $request, Candidato $candidato): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($candidato, $workspace);
        $nombre = $candidato->nombre_completo;
        $candidato->delete();

        return redirect()->route('candidatos.index')
            ->with('success', "Candidato {$nombre} eliminado satisfactoriamente.");
    }

    /**
     * Dashboard Analítico Avanzado para un Canal de Red Social específico.
     */
    public function metricasCanal(Request $request, PerfilSocial $perfilSocial): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);

        return Inertia::render('Candidatos/MetricasCanal', $this->canalMetricasService->calcularMetricas($request, $perfilSocial));
    }
}
