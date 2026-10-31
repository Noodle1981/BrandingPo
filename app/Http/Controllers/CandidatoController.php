<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Http\Requests\Candidato\StoreCandidatoRequest;
use App\Http\Requests\Candidato\StorePerfilSocialRequest;
use App\Http\Requests\Candidato\UpdateCandidatoRequest;
use App\Http\Requests\Candidato\UpdatePerfilSocialRequest;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\PerfilSocial;
use App\Models\Territorio;
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

    /**
     * Redirección al perfil oficial de campaña.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('mi-candidato');
    }

    /**
     * Redirección al perfil oficial de campaña.
     */
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

        $cicloActivo = CicloCampana::delWorkspace($workspace)->activo()->first()
            ?? CicloCampana::delWorkspace($workspace)->first();

        $territorioDefault = Territorio::delWorkspace($workspace)->first();

        // Buscar o inicializar el candidato propio del workspace
        $candidato = Candidato::delWorkspace($workspace)
            ->propios()
            ->with(['perfilesSociales', 'territorio', 'cicloCampana'])
            ->first();

        if (! $candidato) {
            $cargoPorDefecto = match ($workspace->nivel_politico) {
                'gobernador' => 'Candidato a Gobernador',
                'legislador_nacional' => 'Candidato a Diputado Nacional',
                'senador' => 'Candidato a Senador Nacional',
                'concejal' => 'Candidato a Concejal',
                default => 'Candidato a Intendente',
            };

            $candidato = Candidato::create([
                'workspace_id' => $workspace->id,
                'nombre_completo' => 'Mi Candidato ('.$workspace->nombre.')',
                'partido_coalicion' => 'Frente de Campaña',
                'cargo_aspirado' => $cargoPorDefecto,
                'estado_politico' => 'candidato',
                'ciclo_campana_id' => $cicloActivo?->id,
                'territorio_id' => $territorioDefault?->id,
                'color_hex' => '#06b6d4',
                'es_propio' => true,
                'bio_resumen' => 'Perfil del candidato oficial de campaña. Auditoría y seguimiento de crecimiento desde el Punto Cero.',
            ]);
            $candidato->load(['perfilesSociales', 'territorio', 'cicloCampana']);
        }

        return Inertia::render('Candidatos/MiPerfil', $this->candidatoPresenter->prepararPayloadDetalle($candidato, $request));
    }

    /**
     * Guardar o actualizar la configuración de una red social y su Punto Cero.
     */
    public function storePerfilSocial(StorePerfilSocialRequest $request, MediaStorageService $mediaStorage): RedirectResponse
    {
        $validated = $request->validated();
        $this->canalSyncService->guardarPuntoCero($validated, $mediaStorage);

        return redirect()->back()
            ->with('success', "Canal {$validated['plataforma']} configurado y Punto Cero establecido.");
    }

    /**
     * Auto-extracción de datos desde el enlace público del perfil.
     */
    public function scrapePerfilSocial(Request $request, SocialProfileScraperService $scraper): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url'],
            'plataforma' => ['required', 'string'],
        ]);

        $data = $scraper->scrapeProfile($validated['url'], $validated['plataforma']);

        return response()->json($data);
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
            $status = ! empty($resultado['success']) ? 200 : 422;

            return response()->json($resultado, $status);
        }

        if (empty($resultado['success'])) {
            return redirect()->back()->with('error', $resultado['mensaje']);
        }

        return redirect()->back()->with('success', $resultado['mensaje']);
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

        $resultado = $this->canalSyncService->sincronizarCanalMaestro($perfilSocial, $scraper, $mediaStorage);

        return response()->json($resultado);
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
        $validated = $request->validated();

        $candidato = Candidato::create([
            'workspace_id' => $workspace->id,
            'nombre_completo' => $validated['nombre_completo'],
            'partido_coalicion' => $validated['partido_coalicion'],
            'cargo_aspirado' => $validated['cargo_aspirado'] ?? null,
            'estado_politico' => $validated['estado_politico'],
            'ciclo_campana_id' => $validated['ciclo_campana_id'],
            'territorio_id' => $validated['territorio_id'] ?? null,
            'color_hex' => $validated['color_hex'] ?? '#8b5cf6',
            'es_propio' => false,
            'avatar_url' => $validated['avatar_url'] ?? null,
            'bio_resumen' => $validated['bio_resumen'] ?? null,
        ]);

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

        $validated = $request->validated();

        if (! empty($validated['workspace_nombre']) && $candidato->es_propio) {
            $workspace->update(['nombre' => $validated['workspace_nombre']]);
        }

        $territorioId = $validated['territorio_id'] ?? $candidato->territorio_id;
        if (! empty($validated['territorio_nombre'])) {
            $padron = (int) ($validated['padron_electoral'] ?? 0);
            $poblacion = (int) ($validated['poblacion_total'] ?? ($padron > 0 ? (int) round($padron * 1.3) : 0));

            $territorio = Territorio::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'nombre' => $validated['territorio_nombre'],
                ],
                [
                    'tipo' => $validated['tipo_territorio'] ?? 'departamento',
                    'padron_electoral' => $padron,
                    'poblacion_total' => $poblacion,
                ]
            );
            $territorioId = $territorio->id;
        }

        $candidato->update([
            'nombre_completo' => $validated['nombre_completo'],
            'partido_coalicion' => $validated['partido_coalicion'],
            'cargo_aspirado' => $validated['cargo_aspirado'] ?? null,
            'estado_politico' => $validated['estado_politico'],
            'ciclo_campana_id' => $validated['ciclo_campana_id'] ?? $candidato->ciclo_campana_id,
            'territorio_id' => $territorioId,
            'color_hex' => $validated['color_hex'] ?? $candidato->color_hex,
            'avatar_url' => $validated['avatar_url'] ?? $candidato->avatar_url,
            'bio_resumen' => $validated['bio_resumen'] ?? $candidato->bio_resumen,
        ]);

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

        $payload = $this->canalMetricasService->calcularMetricas($request, $perfilSocial);

        return Inertia::render('Candidatos/MetricasCanal', $payload);
    }
}
