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
use App\Models\Publicacion;
use App\Models\Territorio;
use App\Services\CanalMetricasService;
use App\Services\CandidatoPresenterService;
use App\Services\MediaStorageService;
use App\Services\SocialProfileScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CandidatoController extends Controller
{
    public function __construct(
        protected CandidatoPresenterService $candidatoPresenter,
        protected CanalMetricasService $canalMetricasService
    ) {}

    /**
     * Redirección al perfil oficial de campaña (la plataforma se centra exclusivamente en el candidato propio).
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

        $cicloActivo = CicloCampana::where('workspace_id', $workspace->id)->where('es_activo', true)->first()
            ?? CicloCampana::where('workspace_id', $workspace->id)->first();

        $territorioDefault = Territorio::where('workspace_id', $workspace->id)->first();

        // Buscar o inicializar el candidato propio del workspace
        $candidato = Candidato::where('workspace_id', $workspace->id)
            ->where('es_propio', true)
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

        $perfilPrevio = PerfilSocial::where('candidato_id', $validated['candidato_id'])
            ->where('plataforma', $validated['plataforma'])
            ->first();

        $fotoPerfilLocal = $validated['foto_perfil_url'] ?? null;
        if (! empty($fotoPerfilLocal)) {
            $prefijo = 'perfil_'.$validated['plataforma'].'_'.$validated['candidato_id'];
            $fotoPerfilLocal = $mediaStorage->guardarAvatarLocal($fotoPerfilLocal, $prefijo, $perfilPrevio?->foto_perfil_url);
        }

        $perfil = PerfilSocial::updateOrCreate(
            [
                'candidato_id' => $validated['candidato_id'],
                'plataforma' => $validated['plataforma'],
            ],
            [
                'handle_usuario' => $validated['handle_usuario'],
                'url_perfil' => $validated['url_perfil'] ?? null,
                'foto_perfil_url' => $fotoPerfilLocal ?? null,
                'esta_activo' => $validated['esta_activo'],
                'esta_verificado' => $validated['esta_verificado'],
                'seguidores_actuales' => (int) ($validated['seguidores_actuales'] ?? $validated['seguidores_punto_cero'] ?? 0),
                'seguidos_actuales' => (int) ($validated['seguidos_actuales'] ?? $validated['seguidos_punto_cero'] ?? 0),
                'publicaciones_totales' => (int) ($validated['publicaciones_totales'] ?? $validated['publicaciones_punto_cero'] ?? 0),
                'me_gusta_totales' => (int) ($validated['me_gusta_totales'] ?? $validated['me_gusta_punto_cero'] ?? 0),
                'visualizaciones_totales' => (int) ($validated['visualizaciones_totales'] ?? $validated['visualizaciones_punto_cero'] ?? 0),
                'fecha_punto_cero' => $validated['fecha_punto_cero'] ?? now(),
                'seguidores_punto_cero' => (int) ($validated['seguidores_punto_cero'] ?? $validated['seguidores_actuales'] ?? 0),
                'seguidos_punto_cero' => (int) ($validated['seguidos_punto_cero'] ?? $validated['seguidos_actuales'] ?? 0),
                'publicaciones_punto_cero' => (int) ($validated['publicaciones_punto_cero'] ?? $validated['publicaciones_totales'] ?? 0),
                'me_gusta_punto_cero' => (int) ($validated['me_gusta_punto_cero'] ?? $validated['me_gusta_totales'] ?? 0),
                'visualizaciones_punto_cero' => (int) ($validated['visualizaciones_punto_cero'] ?? $validated['visualizaciones_totales'] ?? 0),
                'notas_punto_cero' => $validated['notas_punto_cero'] ?? null,
            ]
        );

        $perfil->registrarMedicion([
            'seguidores' => $perfil->seguidores_actuales,
            'seguidos' => $perfil->seguidos_actuales,
            'publicaciones' => $perfil->publicaciones_totales,
            'me_gusta_totales' => $perfil->me_gusta_totales,
            'visualizaciones_totales' => $perfil->visualizaciones_totales,
            'foto_perfil_url' => $perfil->foto_perfil_url,
        ], 'manual');

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
    public function refrescarPerfilSocial(Request $request, PerfilSocial $perfilSocial, SocialProfileScraperService $scraper): JsonResponse|RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);

        if (empty($perfilSocial->url_perfil)) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'mensaje' => 'El canal no tiene una URL configurada para auditar.',
                ], 422);
            }

            return redirect()->back()->with('error', 'El canal no tiene una URL configurada para auditar.');
        }

        $scraped = $scraper->scrapeProfile($perfilSocial->url_perfil, $perfilSocial->plataforma);

        $metrica = $perfilSocial->registrarMedicion($scraped, 'auto_scraper');

        $deltaSeguidores = $metrica->crecimiento_seguidores_dia;
        $signo = $deltaSeguidores > 0 ? '+' : '';
        $deltaMsg = $deltaSeguidores != 0 ? " ({$signo}{$deltaSeguidores} seguidores hoy)" : '';
        $msg = "¡{$perfilSocial->plataforma} auditado con éxito!{$deltaMsg}";

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'success' => true,
                'mensaje' => $msg,
                'metrica' => $metrica,
                'perfil' => $perfilSocial->fresh(),
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Sincronización Maestra del Canal (Seguidores + Publicaciones activas en ventana <=15 días + snapshots de corte).
     */
    public function sincronizarCanal(
        Request $request,
        PerfilSocial $perfilSocial,
        SocialProfileScraperService $scraper,
        MediaStorageService $mediaStorage
    ): JsonResponse {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);

        @set_time_limit(180);
        $resultadoCanal = [
            'success' => true,
            'plataforma' => $perfilSocial->plataforma,
            'seguidores_actuales' => (int) $perfilSocial->seguidores_actuales,
            'delta_seguidores' => 0,
            'mensaje_seguidores' => '',
            'posts_total' => 0,
            'posts_actualizados' => 0,
            'nuevos_likes' => 0,
            'nuevos_comentarios' => 0,
            'cambios_pauta' => 0,
            'logs' => [],
        ];

        // PASO 1: Sincronizar Seguidores del Perfil Social (si tiene URL)
        if (! empty($perfilSocial->url_perfil)) {
            try {
                $scrapedPerfil = $scraper->scrapeProfile($perfilSocial->url_perfil, $perfilSocial->plataforma);
                $metrica = $perfilSocial->registrarMedicion($scrapedPerfil, 'sync_maestro');
                $deltaSeg = (int) $metrica->crecimiento_seguidores_dia;
                $signo = $deltaSeg > 0 ? '+' : '';
                $resultadoCanal['seguidores_actuales'] = (int) $metrica->seguidores;
                $resultadoCanal['delta_seguidores'] = $deltaSeg;
                $resultadoCanal['mensaje_seguidores'] = 'Seguidores actualizados: '.number_format($metrica->seguidores, 0, ',', '.').($deltaSeg != 0 ? " ({$signo}{$deltaSeg} hoy)" : '');
            } catch (\Throwable $e) {
                $resultadoCanal['mensaje_seguidores'] = 'No se pudo actualizar seguidores: '.$e->getMessage();
            }
        } else {
            $resultadoCanal['mensaje_seguidores'] = 'Canal sin URL de perfil para escanear seguidores.';
        }

        // PASO 2: Sincronizar publicaciones en ventana activa (<= 15 días)
        $fechaLimite = Carbon::now()->subDays(15)->startOfDay();
        $publicaciones = Publicacion::where('perfil_social_id', $perfilSocial->id)
            ->where('fecha_publicacion', '>=', $fechaLimite)
            ->whereNotNull('url_post')
            ->where('url_post', '!=', '')
            ->get();

        $resultadoCanal['posts_total'] = $publicaciones->count();

        foreach ($publicaciones as $pub) {
            try {
                $scrapedPost = $scraper->scrapePost($pub->url_post, $perfilSocial->plataforma);
                if (! empty($scrapedPost['success'])) {
                    $oldLikes = (int) $pub->total_likes;
                    $oldComments = (int) $pub->total_comentarios;
                    $freshLikes = (! empty($scrapedPost['total_likes']) && (int) $scrapedPost['total_likes'] > 0) ? (int) $scrapedPost['total_likes'] : $oldLikes;
                    $freshComments = (! empty($scrapedPost['total_comentarios']) && (int) $scrapedPost['total_comentarios'] > 0) ? (int) $scrapedPost['total_comentarios'] : $oldComments;
                    $freshVistas = (! empty($scrapedPost['total_vistas']) && (int) $scrapedPost['total_vistas'] > 0) ? (int) $scrapedPost['total_vistas'] : (int) $pub->total_vistas;

                    $deltaL = max(0, $freshLikes - $oldLikes);
                    $deltaC = max(0, $freshComments - $oldComments);

                    $resultadoCanal['nuevos_likes'] += $deltaL;
                    $resultadoCanal['nuevos_comentarios'] += $deltaC;

                    $pubUpdate = [
                        'total_likes' => $freshLikes,
                        'total_comentarios' => $freshComments,
                    ];

                    if ($freshVistas > 0) {
                        $pubUpdate['total_vistas'] = $freshVistas;
                    }

                    $aiEmocional = app(PublicacionController::class)->calcularInteligenciaEmocional([], $freshLikes, $perfilSocial->plataforma);
                    $pubUpdate['reacciones_detalladas'] = $aiEmocional['reacciones_detalladas'];
                    $pubUpdate['sentimiento_predominante'] = $aiEmocional['sentimiento_predominante'];
                    $pubUpdate['termometro_humor_social'] = $aiEmocional['termometro_humor_social'];

                    if (! empty($scrapedPost['media_url'])) {
                        $localMedia = $mediaStorage->guardarMediaLocal($scrapedPost['media_url'], $pub->media_url);
                        if ($localMedia) {
                            $pubUpdate['media_url'] = $localMedia;
                        }
                    }

                    $pub->update($pubUpdate);
                    $resultadoCanal['posts_actualizados']++;

                    $resultadoCanal['logs'][] = [
                        'status' => 'success',
                        'url' => $pub->url_post,
                        'resumen' => Str::limit($pub->contenido_resumen, 45),
                        'likes' => $freshLikes,
                        'deltaLikes' => $deltaL,
                        'comments' => $freshComments,
                        'deltaComments' => $deltaC,
                        'fecha' => $pub->fecha_publicacion?->format('d/m/Y') ?? 'Reciente',
                    ];
                } else {
                    $resultadoCanal['logs'][] = [
                        'status' => 'warning',
                        'url' => $pub->url_post,
                        'resumen' => Str::limit($pub->contenido_resumen, 45),
                        'error' => $scrapedPost['mensaje'] ?? 'Sin datos nuevos.',
                    ];
                }
            } catch (\Throwable $e) {
                $resultadoCanal['logs'][] = [
                    'status' => 'error',
                    'url' => $pub->url_post,
                    'resumen' => Str::limit($pub->contenido_resumen, 45),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($resultadoCanal);
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

        // 1. Si se editó el nombre de la campaña/workspace (solo admin o propio)
        if (! empty($validated['workspace_nombre']) && $candidato->es_propio) {
            $workspace->update(['nombre' => $validated['workspace_nombre']]);
        }

        // 2. Actualizar o crear territorio geográfico del candidato
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
     * Implementa el paradigma Territorio-First (Padrón Electoral como Universo Rector).
     */
    public function metricasCanal(Request $request, PerfilSocial $perfilSocial): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($perfilSocial, $workspace);

        $payload = $this->canalMetricasService->calcularMetricas($request, $perfilSocial);

        return Inertia::render('Candidatos/MetricasCanal', $payload);
    }
}
