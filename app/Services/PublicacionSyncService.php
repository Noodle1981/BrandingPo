<?php

namespace App\Services;

use App\Models\PerfilSocial;
use App\Models\PerfilSocialMetrica;
use App\Models\Publicacion;
use App\Models\PublicacionPautaEvento;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PublicacionSyncService
{
    /**
     * Motor de Inteligencia Emocional & Sentimiento Cuantificado.
     */
    public function calcularInteligenciaEmocional(array $data, int $totalLikesFallback = 0, ?string $plataforma = null): array
    {
        $plat = strtolower($plataforma ?? $data['plataforma'] ?? '');

        // Extraer valores soportando nombres en español y nombres en inglés
        $meGusta = (int) ($data['me_gusta'] ?? ($data['reacciones_detalladas']['like'] ?? ($data['reacciones_detalladas']['me_gusta'] ?? 0)));
        $meEncanta = (int) ($data['me_encanta'] ?? ($data['reacciones_detalladas']['love'] ?? ($data['reacciones_detalladas']['me_encanta'] ?? 0)));
        $meImporta = (int) ($data['me_importa'] ?? ($data['reacciones_detalladas']['care'] ?? ($data['reacciones_detalladas']['me_importa'] ?? 0)));
        $meDivierte = (int) ($data['me_divierte'] ?? ($data['reacciones_detalladas']['haha'] ?? ($data['reacciones_detalladas']['me_divierte'] ?? 0)));
        $meAsombra = (int) ($data['me_asombra'] ?? ($data['reacciones_detalladas']['wow'] ?? ($data['reacciones_detalladas']['me_asombra'] ?? 0)));
        $meEntristece = (int) ($data['me_entristece'] ?? ($data['reacciones_detalladas']['sad'] ?? ($data['reacciones_detalladas']['me_entristece'] ?? 0)));
        $meEnoja = (int) ($data['me_enoja'] ?? ($data['reacciones_detalladas']['angry'] ?? ($data['reacciones_detalladas']['me_enoja'] ?? 0)));

        $totalReacciones = $meGusta + $meEncanta + $meImporta + $meDivierte + $meAsombra + $meEntristece + $meEnoja;

        // Si es Instagram, TikTok, X/Twitter o YouTube: no utilizan reacciones emocionales estilo Facebook
        if (in_array($plat, ['instagram', 'tiktok', 'x_twitter', 'youtube']) || ($plat !== 'facebook' && $totalReacciones === 0)) {
            $totalFinalLikes = $totalLikesFallback > 0 ? $totalLikesFallback : ($totalReacciones > 0 ? $totalReacciones : $meGusta);
            $termometro = (int) ($data['termometro_humor_social'] ?? 5);
            $sentimiento = $termometro >= 4 ? 'positivo' : ($termometro === 3 ? 'neutro' : 'negativo');

            return [
                'total_likes' => $totalFinalLikes,
                'reacciones_detalladas' => [
                    'me_gusta' => $totalFinalLikes,
                    'me_encanta' => 0,
                    'me_importa' => 0,
                    'me_divierte' => 0,
                    'me_asombra' => 0,
                    'me_entristece' => 0,
                    'me_enoja' => 0,
                ],
                'sentimiento_predominante' => $sentimiento,
                'termometro_humor_social' => $termometro,
                'insights_internos_propios' => [
                    'indice_aprobacion_neta' => 100.0,
                    'ratio_indignacion' => 0.0,
                    'alerta_crisis' => false,
                    'total_reacciones_positivas' => $totalFinalLikes,
                    'total_reacciones_negativas' => 0,
                    'total_reacciones_humor' => 0,
                ],
            ];
        }

        // Lógica de cálculo para Facebook o redes con desglose granular
        if ($totalReacciones === 0 && $totalLikesFallback > 0) {
            $totalReacciones = $totalLikesFallback;
            $meGusta = $totalLikesFallback;
        }

        $tieneDesgloseEmocional = ($meEncanta + $meImporta + $meDivierte + $meAsombra + $meEntristece + $meEnoja) > 0;

        $positivas = $meGusta + $meEncanta + $meImporta;
        $negativas = $meEnoja + $meEntristece;
        $humorViral = $meDivierte;

        $indiceAprobacion = ($tieneDesgloseEmocional && $totalReacciones > 0)
            ? round((($positivas - $negativas) / $totalReacciones) * 100, 1)
            : null;

        $ratioIndignacion = ($tieneDesgloseEmocional && $totalReacciones > 0)
            ? round(($meEnoja / $totalReacciones) * 100, 1)
            : 0.0;

        $alertaCrisis = $ratioIndignacion >= 15.0;

        if ($indiceAprobacion !== null) {
            if ($indiceAprobacion >= 65) {
                $termometro = 5;
                $sentimiento = 'positivo';
            } elseif ($indiceAprobacion >= 30) {
                $termometro = 4;
                $sentimiento = 'positivo';
            } elseif ($indiceAprobacion >= -10) {
                $termometro = 3;
                $sentimiento = 'neutro';
            } elseif ($indiceAprobacion >= -40) {
                $termometro = 2;
                $sentimiento = 'negativo';
            } else {
                $termometro = 1;
                $sentimiento = 'negativo';
            }
        } else {
            $termometro = (int) ($data['termometro_humor_social'] ?? 5);
            $sentimiento = 'positivo';
        }

        return [
            'total_likes' => $totalReacciones,
            'reacciones_detalladas' => [
                'me_gusta' => $meGusta,
                'me_encanta' => $meEncanta,
                'me_importa' => $meImporta,
                'me_divierte' => $meDivierte,
                'me_asombra' => $meAsombra,
                'me_entristece' => $meEntristece,
                'me_enoja' => $meEnoja,
            ],
            'sentimiento_predominante' => $sentimiento,
            'termometro_humor_social' => $termometro,
            'insights_internos_propios' => [
                'indice_aprobacion_neta' => $indiceAprobacion,
                'ratio_indignacion' => $ratioIndignacion,
                'alerta_crisis' => $alertaCrisis,
                'total_reacciones_positivas' => $positivas,
                'total_reacciones_negativas' => $negativas,
                'total_reacciones_humor' => $humorViral,
                'desglose_verificado' => $tieneDesgloseEmocional,
            ],
        ];
    }

    /**
     * Sincronizar en vivo una publicación individual.
     */
    public function sincronizarIndividual(Publicacion $publicacion, SocialProfileScraperService $scraper, MediaStorageService $mediaStorage): array
    {
        if (empty($publicacion->url_post)) {
            return ['success' => false, 'mensaje' => 'La publicación no tiene enlace URL.'];
        }

        $scraped = $scraper->scrapePost($publicacion->url_post, $publicacion->perfilSocial?->plataforma ?? $publicacion->plataforma);
        if (! $scraped['success']) {
            return ['success' => false, 'mensaje' => $scraped['mensaje'] ?? 'No se pudo leer la URL pública.'];
        }

        $oldLikes = (int) $publicacion->total_likes;
        $oldComments = (int) $publicacion->total_comentarios;
        $freshLikes = (! empty($scraped['total_likes']) && (int) $scraped['total_likes'] > 0) ? (int) $scraped['total_likes'] : $oldLikes;
        $freshComments = (! empty($scraped['total_comentarios']) && (int) $scraped['total_comentarios'] > 0) ? (int) $scraped['total_comentarios'] : $oldComments;

        $deltaLikes = max(0, $freshLikes - $oldLikes);
        $deltaComments = max(0, $freshComments - $oldComments);

        $plataforma = $publicacion->perfilSocial?->plataforma ?? $publicacion->plataforma ?? 'instagram';
        $aiEmocional = $this->calcularInteligenciaEmocional([], $freshLikes, $plataforma);

        $freshVistas = (! empty($scraped['total_vistas']) && (int) $scraped['total_vistas'] > 0) ? (int) $scraped['total_vistas'] : (int) $publicacion->total_vistas;

        $updateFields = [
            'total_likes' => $freshLikes,
            'total_comentarios' => $freshComments,
            'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
            'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
            'termometro_humor_social' => $aiEmocional['termometro_humor_social'],
        ];

        if ($freshVistas > 0) {
            $updateFields['total_vistas'] = $freshVistas;
        }

        // Guardar o actualizar la imagen descargándola localmente
        if (! empty($scraped['media_url'])) {
            $localMedia = $mediaStorage->guardarMediaLocal($scraped['media_url'], $publicacion->media_url);
            if ($localMedia) {
                $updateFields['media_url'] = $localMedia;
            }
        }

        $publicacion->update($updateFields);

        // Detección heurística de pauta durante la sincronización
        $sospechaPauta = false;
        $motivoPauta = '';

        if ($publicacion->tipo_pauta === 'organico') {
            // Caso 1: La URL tiene o tenía parámetros publicitarios
            if (! empty($scraped['sospecha_pauta'])) {
                $sospechaPauta = true;
                $motivoPauta = $scraped['motivo_sospecha_pauta'] ?? 'Se detectaron tokens de pauta activa en el enlace.';
            }
            // Caso 2: Crecimiento explosivo no lineal (deltaLikes >= 40 y duplicación de base previa)
            elseif ($deltaLikes >= 40 && ($oldLikes === 0 || ($deltaLikes / max(1, $oldLikes)) >= 0.75)) {
                $sospechaPauta = true;
                $motivoPauta = "Salto abrupto de interacciones (+{$deltaLikes} reacciones en sincronización). Crecimiento característico de post impulsado (Boost).";
            }
        }

        return [
            'success' => true,
            'mensaje' => 'Sincronizado exitosamente',
            'delta_likes' => $deltaLikes,
            'delta_comentarios' => $deltaComments,
            'total_likes' => $freshLikes,
            'total_comentarios' => $freshComments,
            'url_post' => $publicacion->url_post,
            'fecha' => $publicacion->fecha_publicacion?->format('d/m/Y') ?? 'Reciente',
            'resumen' => Str::limit($publicacion->contenido_resumen ?? '', 45),
            'sospecha_pauta' => $sospechaPauta,
            'motivo_sospecha_pauta' => $motivoPauta,
            'tipo_pauta_actual' => $publicacion->tipo_pauta,
            'tipo_pauta_sugerido' => $sospechaPauta ? 'organico_impulsado' : $publicacion->tipo_pauta,
        ];
    }

    /**
     * Sincronizar en vivo métricas de publicaciones en la ventana activa de 15 días.
     */
    public function sincronizarRecientes(PerfilSocial $perfilSocial, SocialProfileScraperService $scraper, MediaStorageService $mediaStorage): array
    {
        $fechaLimite = Carbon::now()->subDays(15)->startOfDay();

        $publicaciones = Publicacion::where('perfil_social_id', $perfilSocial->id)
            ->where('fecha_publicacion', '>=', $fechaLimite)
            ->whereNotNull('url_post')
            ->where('url_post', '!=', '')
            ->get();

        if ($publicaciones->isEmpty()) {
            return [
                'success' => false,
                'mensaje' => 'No hay publicaciones dentro de la ventana de los últimos 15 días para sincronizar.',
                'actualizadas' => 0,
                'nuevos_likes' => 0,
                'nuevos_comentarios' => 0,
            ];
        }

        $actualizadas = 0;
        $nuevosLikes = 0;
        $nuevosComentarios = 0;

        foreach ($publicaciones as $pub) {
            $scraped = $scraper->scrapePost($pub->url_post, $perfilSocial->plataforma);
            if ($scraped['success']) {
                $oldLikes = (int) $pub->total_likes;
                $oldComments = (int) $pub->total_comentarios;
                $freshLikes = (! empty($scraped['total_likes']) && (int) $scraped['total_likes'] > 0) ? (int) $scraped['total_likes'] : $oldLikes;
                $freshComments = (! empty($scraped['total_comentarios']) && (int) $scraped['total_comentarios'] > 0) ? (int) $scraped['total_comentarios'] : $oldComments;
                $freshVistas = (! empty($scraped['total_vistas']) && (int) $scraped['total_vistas'] > 0) ? (int) $scraped['total_vistas'] : (int) $pub->total_vistas;

                if ($freshLikes > $oldLikes || $freshComments > $oldComments || $freshLikes > 0 || ! empty($scraped['media_url'])) {
                    $deltaLikes = max(0, $freshLikes - $oldLikes);
                    $deltaComments = max(0, $freshComments - $oldComments);
                    $nuevosLikes += $deltaLikes;
                    $nuevosComentarios += $deltaComments;

                    $aiEmocional = $this->calcularInteligenciaEmocional([], $freshLikes, $perfilSocial->plataforma);

                    $pubUpdate = [
                        'total_likes' => $freshLikes,
                        'total_comentarios' => $freshComments,
                        'reacciones_detalladas' => $aiEmocional['reacciones_detalladas'],
                        'sentimiento_predominante' => $aiEmocional['sentimiento_predominante'],
                        'termometro_humor_social' => $aiEmocional['termometro_humor_social'],
                    ];

                    if ($freshVistas > 0) {
                        $pubUpdate['total_vistas'] = $freshVistas;
                    }

                    if (! empty($scraped['media_url'])) {
                        $localMedia = $mediaStorage->guardarMediaLocal($scraped['media_url'], $pub->media_url);
                        if ($localMedia) {
                            $pubUpdate['media_url'] = $localMedia;
                        }
                    }

                    $pub->update($pubUpdate);
                    $actualizadas++;
                }
            }
        }

        $mensaje = $actualizadas > 0
            ? "✨ Se sincronizaron {$actualizadas} publicaciones recientes (+{$nuevosLikes} likes y +{$nuevosComentarios} comentarios nuevos)."
            : "✨ Se verificaron {$publicaciones->count()} publicaciones de los últimos 15 días y ya estaban al día.";

        return [
            'success' => true,
            'mensaje' => $mensaje,
            'actualizadas' => $actualizadas,
            'nuevos_likes' => $nuevosLikes,
            'nuevos_comentarios' => $nuevosComentarios,
        ];
    }

    /**
     * Escaneo proactivo y masivo de Huellas de Pauta sobre publicaciones filtradas.
     */
    public function detectarHuellasPauta(int $workspaceId, array $filtros): array
    {
        $candidatoId = $filtros['candidato_id'] ?? null;
        $plataforma = $filtros['plataforma'] ?? null;
        $anio = $filtros['anio'] ?? null;
        $mes = $filtros['mes'] ?? null;
        $filtro = $filtros['filtro'] ?? null;

        $query = Publicacion::with('perfilSocial')
            ->where('workspace_id', $workspaceId)
            ->where('tipo_pauta', 'organico');

        if ($filtro === 'propio') {
            $query->whereHas('candidato', fn ($q) => $q->where('es_propio', true));
        } elseif ($filtro === 'oposicion') {
            $query->whereHas('candidato', fn ($q) => $q->where('es_propio', false));
        }

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        if ($plataforma) {
            $platforms = match ($plataforma) {
                'x_twitter', 'twitter' => ['x_twitter', 'twitter'],
                default => [$plataforma],
            };
            $query->whereHas('perfilSocial', fn ($q) => $q->whereIn('plataforma', $platforms));
        }

        if ($anio && $mes) {
            $query->whereYear('fecha_publicacion', $anio)
                ->whereMonth('fecha_publicacion', (int) $mes);
        } elseif ($anio) {
            $query->whereYear('fecha_publicacion', $anio);
        } elseif ($mes) {
            $query->whereMonth('fecha_publicacion', (int) $mes);
        }

        $publicaciones = $query->get();
        $totalRevisadas = $publicaciones->count();
        $conHuella = [];

        foreach ($publicaciones as $pub) {
            $analisis = SocialProfileScraperService::analizarHuellaPauta($pub->url_post, $pub->contenido_resumen);

            if ($analisis['tiene_huella']) {
                $insights = $pub->insights_internos_propios ?? [];
                $insights['huella_pauta'] = $analisis;
                $pub->update(['insights_internos_propios' => $insights]);

                $conHuella[] = [
                    'id' => $pub->id,
                    'resumen' => Str::limit($pub->contenido_resumen ?? 'Sin descripción', 40),
                    'plataforma' => $pub->perfilSocial?->plataforma ?? $pub->plataforma,
                    'red' => $analisis['red'],
                    'token' => $analisis['token_detectado'],
                    'motivo' => $analisis['motivo'],
                ];
            }
        }

        $totalDetectadas = count($conHuella);
        $mensaje = $totalDetectadas > 0
            ? "🎯 Se escanearon {$totalRevisadas} publicaciones orgánicas: ¡Se detectaron {$totalDetectadas} con huella de pauta!"
            : "✨ Se escanearon {$totalRevisadas} publicaciones orgánicas del filtro: ninguna presenta huella de pauta publicitaria.";

        return [
            'success' => true,
            'total_revisadas' => $totalRevisadas,
            'total_detectadas' => $totalDetectadas,
            'publicaciones_detectadas' => $conHuella,
            'mensaje' => $mensaje,
        ];
    }

    /**
     * Registrar snapshot de transición o ajuste presupuestario de pauta.
     */
    public function registrarEventoCambioPauta(Publicacion $publicacion, array $validated, ?int $userId): void
    {
        $huboCambioPauta = $validated['tipo_pauta'] !== $publicacion->tipo_pauta
            || (in_array($validated['tipo_pauta'], Publicacion::TIPOS_CON_INVERSION) && (float) ($validated['monto_invertido_pauta'] ?? 0) !== (float) $publicacion->monto_invertido_pauta);

        if (! $huboCambioPauta) {
            return;
        }

        $seguidoresCanal = PerfilSocialMetrica::where('perfil_social_id', $publicacion->perfil_social_id)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->value('seguidores')
            ?? $publicacion->perfilSocial?->seguidores_actuales
            ?? 0;

        PublicacionPautaEvento::create([
            'publicacion_id' => $publicacion->id,
            'tipo_pauta_anterior' => $publicacion->tipo_pauta,
            'tipo_pauta_nuevo' => $validated['tipo_pauta'],
            'monto_anterior' => (float) ($publicacion->monto_invertido_pauta ?? 0),
            'monto_nuevo' => $validated['tipo_pauta'] !== 'organico' ? (float) ($validated['monto_invertido_pauta'] ?? 0) : 0,
            'fecha_evento' => Carbon::now(),
            'seguidores_canal_snapshot' => (int) $seguidoresCanal,
            'likes_snapshot' => (int) $publicacion->total_likes,
            'comentarios_snapshot' => (int) $publicacion->total_comentarios,
            'compartidos_snapshot' => (int) $publicacion->total_compartidos,
            'vistas_snapshot' => (int) $publicacion->total_vistas,
            'republicados_snapshot' => (int) ($publicacion->total_republicados ?? 0),
            'registrado_por' => $userId,
            'origen' => 'manual',
            'notas' => "Transición de {$publicacion->tipo_pauta} a {$validated['tipo_pauta']} guardada por usuario.",
        ]);
    }
}
