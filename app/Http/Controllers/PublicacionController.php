<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Http\Requests\Publicacion\ActualizarFechaPublicacionRequest;
use App\Http\Requests\Publicacion\StorePublicacionRequest;
use App\Http\Requests\Publicacion\UpdatePublicacionRequest;
use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Publicacion;
use App\Services\MediaStorageService;
use App\Services\PublicacionFeedService;
use App\Services\PublicacionSyncService;
use App\Services\SocialProfileScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicacionController extends Controller
{
    public function __construct(
        protected PublicacionSyncService $syncService,
        protected PublicacionFeedService $feedService
    ) {}

    /**
     * Extraer datos públicos de una publicación con 1 clic.
     */
    public function scrapePost(Request $request, SocialProfileScraperService $scraper, MediaStorageService $mediaStorage): JsonResponse
    {
        $inputUrl = $request->input('url') ?? $request->input('url_post') ?? '';
        $plataforma = $request->input('plataforma');
        $workspace = WorkspaceHelper::activo($request);

        if (empty($inputUrl)) {
            return response()->json([
                'success' => false,
                'error' => 'Ingresa una URL de publicación válida.',
            ], 422);
        }

        $canonicalUrl = SocialProfileScraperService::canonicalizePostUrl($inputUrl) ?? $inputUrl;
        $duplicado = Publicacion::buscarDuplicado($workspace->id, $canonicalUrl);

        if ($duplicado) {
            return response()->json([
                'success' => false,
                'ya_registrada' => true,
                'publicacion_id' => $duplicado->id,
                'fecha_publicacion' => $duplicado->fecha_publicacion?->format('d/m/Y H:i'),
                'autor' => $duplicado->candidato?->nombre_completo,
                'plataforma' => $duplicado->perfilSocial?->plataforma,
                'mensaje' => '⚠️ Esta publicación ya se encuentra registrada en el sistema. No se pueden duplicar contenidos.',
            ]);
        }

        $data = $scraper->scrapePost($inputUrl, $plataforma);

        if (! empty($data['media_url'])) {
            $localMedia = $mediaStorage->guardarMediaLocal($data['media_url']);
            if ($localMedia) {
                $data['media_url'] = $localMedia;
            }
        }

        return response()->json([
            'success' => $data['success'] ?? false,
            'data' => $data,
            'mensaje' => $data['mensaje'] ?? '',
        ]);
    }

    /**
     * Sincronizar en vivo una publicación individual.
     */
    public function sincronizarIndividual(Request $request, Publicacion $publicacion, SocialProfileScraperService $scraper, MediaStorageService $mediaStorage): JsonResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($publicacion, $workspace);

        $resultado = $this->syncService->sincronizarIndividual($publicacion, $scraper, $mediaStorage);

        return response()->json($resultado);
    }

    /**
     * Sincronizar en vivo métricas de publicaciones en la ventana activa de 15 días.
     */
    public function sincronizarRecientes(Request $request, PerfilSocial $perfilSocial, SocialProfileScraperService $scraper, MediaStorageService $mediaStorage): RedirectResponse|JsonResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);

        $resultado = $this->syncService->sincronizarRecientes($perfilSocial, $scraper, $mediaStorage);

        if ($request->wantsJson()) {
            return response()->json($resultado);
        }

        if (! empty($resultado['success'])) {
            return redirect()->back()->with('success', $resultado['mensaje']);
        }

        return redirect()->back()->with('warning', $resultado['mensaje']);
    }

    /**
     * Escaneo proactivo y masivo de Huellas de Pauta sobre publicaciones filtradas.
     */
    public function detectarHuellasPauta(Request $request): JsonResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $resultado = $this->syncService->detectarHuellasPauta($workspace->id, $request->all());

        return response()->json($resultado);
    }

    /**
     * Muro interactivo estilo red social (Social Wall) del Workspace Activo.
     */
    public function feed(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $feedData = $this->feedService->obtenerFeedData($workspace->id, $request->all());

        return Inertia::render('Publicaciones/Feed', $feedData);
    }

    /**
     * Guardar una nueva publicación.
     */
    public function store(StorePublicacionRequest $request, MediaStorageService $mediaStorage): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        $validated = $request->validated();

        $perfilSocialId = $validated['perfil_social_id'] ?? null;
        if (! $perfilSocialId && ! empty($validated['plataforma'])) {
            $candidato = Candidato::findOrFail($validated['candidato_id']);
            $perfil = PerfilSocial::firstOrCreate(
                [
                    'candidato_id' => $candidato->id,
                    'plataforma' => $validated['plataforma'],
                ],
                [
                    'handle_usuario' => '@'.strtolower(str_replace(' ', '', $candidato->nombre_completo)),
                    'esta_activo' => true,
                    'esta_verificado' => false,
                ]
            );
            $perfilSocialId = $perfil->id;
        }

        if (! $perfilSocialId) {
            return redirect()->back()->withErrors(['perfil_social_id' => 'Debes seleccionar o indicar la red social.']);
        }

        $ejeId = $validated['eje_tematico_id'] ?? null;
        if (! $ejeId && ! empty($validated['eje_tematico_nombre'])) {
            $ejeNombre = trim($validated['eje_tematico_nombre']);
            $eje = EjeTematico::firstOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'nombre' => $ejeNombre,
                ],
                [
                    'slug' => Str::slug($ejeNombre),
                    'color_badge' => '#06b6d4',
                    'descripcion' => 'Eje temático: '.$ejeNombre,
                ]
            );
            $ejeId = $eje->id;
        }

        $canonicalUrl = ! empty($validated['url_post'])
            ? SocialProfileScraperService::canonicalizePostUrl($validated['url_post'])
            : null;

        $mediaUrlLocal = ! empty($validated['media_url'])
            ? $mediaStorage->guardarMediaLocal($validated['media_url'])
            : null;

        $duplicado = Publicacion::buscarDuplicado(
            $workspace->id,
            $canonicalUrl ?? $validated['url_post'] ?? null,
            (int) $validated['candidato_id'],
            (int) $perfilSocialId,
            $validated['fecha_publicacion'],
            $validated['contenido_resumen']
        );

        if ($duplicado) {
            return redirect()->back()->withInput()->withErrors([
                'url_post' => '⚠️ Esta publicación ya se encuentra registrada en el sistema. Las publicaciones deben ser únicas.',
            ]);
        }

        $this->syncService->guardarNuevaPublicacion(
            $workspace->id,
            $validated,
            $canonicalUrl,
            $mediaUrlLocal,
            (int) $perfilSocialId,
            $ejeId ? (int) $ejeId : null
        );

        return redirect()->back()
            ->with('success', 'Publicación registrada con éxito.');
    }

    /**
     * Actualizar una publicación existente.
     */
    public function update(UpdatePublicacionRequest $request, Publicacion $publicacion, MediaStorageService $mediaStorage): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($publicacion, $workspace);

        $validated = $request->validated();

        $canonicalUrl = ! empty($validated['url_post'])
            ? SocialProfileScraperService::canonicalizePostUrl($validated['url_post'])
            : $publicacion->url_post;

        if (! empty($canonicalUrl)) {
            $duplicado = Publicacion::buscarDuplicado(
                $workspace->id,
                $canonicalUrl,
                $publicacion->candidato_id,
                $publicacion->perfil_social_id,
                $validated['fecha_publicacion'] ?? null,
                $validated['contenido_resumen'],
                $publicacion->id
            );

            if ($duplicado) {
                return redirect()->back()->withInput()->withErrors([
                    'url_post' => '⚠️ Ya existe otra publicación registrada con esta misma URL (ID #'.$duplicado->id.').',
                ]);
            }
        }

        $mediaUrlLocal = array_key_exists('media_url', $validated)
            ? $mediaStorage->guardarMediaLocal($validated['media_url'], $publicacion->media_url)
            : $publicacion->media_url;

        $this->syncService->actualizarPublicacion(
            $publicacion,
            $validated,
            $canonicalUrl,
            $mediaUrlLocal,
            auth()->id()
        );

        return redirect()->back()
            ->with('success', 'Publicación actualizada correctamente.');
    }

    /**
     * Confirmación rápida de fecha de publicación.
     */
    public function actualizarFecha(ActualizarFechaPublicacionRequest $request, Publicacion $publicacion): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($publicacion, $workspace);

        $validated = $request->validated();

        $publicacion->update([
            'fecha_publicacion' => $validated['fecha_publicacion'],
            'fecha_confirmada' => true,
        ]);

        return redirect()->back()->with('success', 'Fecha confirmada correctamente.');
    }

    /**
     * Eliminar publicación.
     */
    public function destroy(Request $request, Publicacion $publicacion): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($publicacion, $workspace);

        $publicacion->delete();

        return redirect()->back()
            ->with('success', 'Publicación eliminada correctamente.');
    }

    /**
     * Helper de cálculo emocional (delegado al servicio).
     */
    public function calcularInteligenciaEmocional(array $data, int $totalLikesFallback = 0, ?string $plataforma = null): array
    {
        return $this->syncService->calcularInteligenciaEmocional($data, $totalLikesFallback, $plataforma);
    }
}
