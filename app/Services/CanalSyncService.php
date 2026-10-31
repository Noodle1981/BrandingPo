<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\PerfilSocial;
use App\Models\Publicacion;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CanalSyncService
{
    public function __construct(
        protected PublicacionSyncService $publicacionSyncService
    ) {}

    /**
     * Guarda o actualiza la configuración de un canal social y establece el Punto Cero.
     */
    public function guardarPuntoCero(array $validated, MediaStorageService $mediaStorage): PerfilSocial
    {
        $perfilPrevio = PerfilSocial::where('candidato_id', $validated['candidato_id'])
            ->where('plataforma', $validated['plataforma'])
            ->first();

        $fotoPerfilLocal = $validated['foto_perfil_url'] ?? null;
        if (! empty($fotoPerfilLocal)) {
            $prefijo = 'perfil_'.$validated['plataforma'].'_'.$validated['candidato_id'];
            $fotoPerfilLocal = $mediaStorage->guardarAvatarLocal($fotoPerfilLocal, $prefijo, $perfilPrevio?->foto_perfil_url);

            $candidato = Candidato::find($validated['candidato_id']);
            if ($candidato && (empty($candidato->getRawOriginal('avatar_url')) || ! file_exists(public_path(ltrim($candidato->getRawOriginal('avatar_url'), '/'))))) {
                $candidato->update(['avatar_url' => $fotoPerfilLocal]);
            }
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

        return $perfil;
    }

    /**
     * Re-audita en vivo un canal social específico mediante scraping público.
     */
    public function auditarPerfil(
        PerfilSocial $perfilSocial,
        SocialProfileScraperService $scraper,
        MediaStorageService $mediaStorage
    ): array {
        if (empty($perfilSocial->url_perfil)) {
            return [
                'success' => false,
                'mensaje' => 'El canal no tiene una URL configurada para auditar.',
            ];
        }

        $scraped = $scraper->scrapeProfile($perfilSocial->url_perfil, $perfilSocial->plataforma);

        if (! empty($scraped['foto_perfil_url'])) {
            $prefijo = 'perfil_'.$perfilSocial->plataforma.'_'.$perfilSocial->candidato_id;
            $fotoLocal = $mediaStorage->guardarAvatarLocal($scraped['foto_perfil_url'], $prefijo, $perfilSocial->foto_perfil_url);
            if ($fotoLocal) {
                $scraped['foto_perfil_url'] = $fotoLocal;
                $perfilSocial->update(['foto_perfil_url' => $fotoLocal]);

                $candidato = $perfilSocial->candidato;
                if ($candidato && (empty($candidato->getRawOriginal('avatar_url')) || ! file_exists(public_path(ltrim($candidato->getRawOriginal('avatar_url'), '/'))))) {
                    $candidato->update(['avatar_url' => $fotoLocal]);
                }
            }
        }

        $metrica = $perfilSocial->registrarMedicion($scraped, 'auto_scraper');

        $deltaSeguidores = $metrica->crecimiento_seguidores_dia;
        $signo = $deltaSeguidores > 0 ? '+' : '';
        $deltaMsg = $deltaSeguidores != 0 ? " ({$signo}{$deltaSeguidores} seguidores hoy)" : '';
        $msg = "¡{$perfilSocial->plataforma} auditado con éxito!{$deltaMsg}";

        return [
            'success' => true,
            'mensaje' => $msg,
            'metrica' => $metrica,
            'perfil' => $perfilSocial->fresh(),
        ];
    }

    /**
     * Sincronización Maestra del Canal (Seguidores + Publicaciones en ventana activa <= 15 días).
     */
    public function sincronizarCanalMaestro(
        PerfilSocial $perfilSocial,
        SocialProfileScraperService $scraper,
        MediaStorageService $mediaStorage
    ): array {
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

        // PASO 1: Sincronizar Seguidores del Perfil Social
        if (! empty($perfilSocial->url_perfil)) {
            try {
                $scrapedPerfil = $scraper->scrapeProfile($perfilSocial->url_perfil, $perfilSocial->plataforma);
                if (! empty($scrapedPerfil['foto_perfil_url'])) {
                    $prefijo = 'perfil_'.$perfilSocial->plataforma.'_'.$perfilSocial->candidato_id;
                    $fotoLocal = $mediaStorage->guardarAvatarLocal($scrapedPerfil['foto_perfil_url'], $prefijo, $perfilSocial->foto_perfil_url);
                    if ($fotoLocal) {
                        $scrapedPerfil['foto_perfil_url'] = $fotoLocal;
                        $perfilSocial->update(['foto_perfil_url' => $fotoLocal]);

                        $candidato = $perfilSocial->candidato;
                        if ($candidato && (empty($candidato->getRawOriginal('avatar_url')) || ! file_exists(public_path(ltrim($candidato->getRawOriginal('avatar_url'), '/'))))) {
                            $candidato->update(['avatar_url' => $fotoLocal]);
                        }
                    }
                }
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

                    $aiEmocional = $this->publicacionSyncService->calcularInteligenciaEmocional([], $freshLikes, $perfilSocial->plataforma);
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

        return $resultadoCanal;
    }
}
