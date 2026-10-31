<?php

namespace App\Services;

class CanalBenchmarkConfig
{
    /**
     * Rangos de referencia de la industria escalonados por tramo de seguidores (Nano / Medio / Macro).
     */
    public const TIERS = [
        'instagram' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 6,
                'posts_semana_ideal' => 5,
                'engagement_min' => 3.5,
                'engagement_ideal' => 7.0,
                'vistas_ratio_reel' => 0.18,
                'descripcion_cadencia' => '4 a 6 posts/semana (Comunidad hiperlocal)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 4,
                'engagement_min' => 1.5,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.12,
                'descripcion_cadencia' => '3 a 5 posts/semana (Reels prioritarios)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 4,
                'engagement_min' => 0.8,
                'engagement_ideal' => 2.0,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '3 a 5 posts/semana (Contenido masivo)',
            ],
        ],
        'facebook' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 7,
                'posts_semana_ideal' => 5,
                'engagement_min' => 2.0,
                'engagement_ideal' => 5.0,
                'vistas_ratio_reel' => 0.15,
                'descripcion_cadencia' => '4 a 7 posts/semana (Vecinal & Grupos)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 6,
                'posts_semana_ideal' => 4,
                'engagement_min' => 1.0,
                'engagement_ideal' => 3.0,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '3 a 6 posts/semana (Videos & Noticias)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 3,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 4,
                'engagement_min' => 0.5,
                'engagement_ideal' => 1.8,
                'vistas_ratio_reel' => 0.06,
                'descripcion_cadencia' => '3 a 5 publicaciones/semana (Anuncios & Live)',
            ],
        ],
        'tiktok' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 10,
                'posts_semana_ideal' => 7,
                'engagement_min' => 5.0,
                'engagement_ideal' => 10.0,
                'vistas_ratio_reel' => 0.35,
                'descripcion_cadencia' => '1 video diario (Espontaneidad & Juventud)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 8,
                'posts_semana_ideal' => 6,
                'engagement_min' => 3.5,
                'engagement_ideal' => 7.5,
                'vistas_ratio_reel' => 0.25,
                'descripcion_cadencia' => '4 a 8 videos/semana (Tendencias & Narrativa)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 7,
                'posts_semana_ideal' => 5,
                'engagement_min' => 2.5,
                'engagement_ideal' => 5.5,
                'vistas_ratio_reel' => 0.18,
                'descripcion_cadencia' => '4 a 7 videos/semana (Producción cuidada)',
            ],
        ],
        'threads' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 12,
                'posts_semana_ideal' => 7,
                'engagement_min' => 3.0,
                'engagement_ideal' => 6.0,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '1 a 2 hilos diarios (Debate & Opinión)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 10,
                'posts_semana_ideal' => 7,
                'engagement_min' => 1.8,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '1 a 2 hilos diarios (Reflexión política)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 4,
                'posts_semana_max' => 8,
                'posts_semana_ideal' => 6,
                'engagement_min' => 1.0,
                'engagement_ideal' => 2.5,
                'vistas_ratio_reel' => 0.05,
                'descripcion_cadencia' => '4 a 8 hilos/semana (Declaraciones de agenda)',
            ],
        ],
        'x_twitter' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 7,
                'posts_semana_max' => 15,
                'posts_semana_ideal' => 10,
                'engagement_min' => 1.5,
                'engagement_ideal' => 3.5,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '1 a 3 tweets/día (Micromensajes & Respuestas)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 7,
                'posts_semana_max' => 14,
                'posts_semana_ideal' => 10,
                'engagement_min' => 0.8,
                'engagement_ideal' => 2.0,
                'vistas_ratio_reel' => 0.06,
                'descripcion_cadencia' => '1 a 2 tweets/día (Agenda política en vivo)',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 5,
                'posts_semana_max' => 12,
                'posts_semana_ideal' => 8,
                'engagement_min' => 0.4,
                'engagement_ideal' => 1.2,
                'vistas_ratio_reel' => 0.04,
                'descripcion_cadencia' => '1 tweet diario (Voz institucional)',
            ],
        ],
        'youtube' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 5,
                'posts_semana_ideal' => 3,
                'engagement_min' => 3.0,
                'engagement_ideal' => 7.0,
                'vistas_ratio_reel' => 0.30,
                'descripcion_cadencia' => '2 a 3 videos/semana (Shorts prioritarios)',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 1,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 2.0,
                'engagement_ideal' => 5.0,
                'vistas_ratio_reel' => 0.20,
                'descripcion_cadencia' => '1 a 2 videos largos + 2 shorts/semana',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 1,
                'posts_semana_max' => 3,
                'posts_semana_ideal' => 2,
                'engagement_min' => 1.5,
                'engagement_ideal' => 3.5,
                'vistas_ratio_reel' => 0.12,
                'descripcion_cadencia' => '1 a 3 producciones principales/semana',
            ],
        ],
        'linkedin' => [
            'nano' => [
                'label' => 'Nano-influencer (<10k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 2.5,
                'engagement_ideal' => 5.5,
                'vistas_ratio_reel' => 0.10,
                'descripcion_cadencia' => '2 a 4 posts profesionales/semana',
            ],
            'medio' => [
                'label' => 'Medio (10k-100k)',
                'posts_semana_min' => 2,
                'posts_semana_max' => 4,
                'posts_semana_ideal' => 3,
                'engagement_min' => 1.5,
                'engagement_ideal' => 4.0,
                'vistas_ratio_reel' => 0.08,
                'descripcion_cadencia' => '2 a 4 publicaciones técnicas/semana',
            ],
            'macro' => [
                'label' => 'Macro (>100k)',
                'posts_semana_min' => 1,
                'posts_semana_max' => 3,
                'posts_semana_ideal' => 2,
                'engagement_min' => 1.0,
                'engagement_ideal' => 3.0,
                'vistas_ratio_reel' => 0.05,
                'descripcion_cadencia' => '1 a 3 posts ejecutivos/semana',
            ],
        ],
    ];

    public static function getBenchmark(string $plataforma, int $seguidores): array
    {
        $plataformaKey = strtolower($plataforma ?: 'instagram');
        $platformTiers = self::TIERS[$plataformaKey] ?? self::TIERS['instagram'];

        if ($seguidores <= 10000) {
            $tramoKey = 'nano';
        } elseif ($seguidores <= 100000) {
            $tramoKey = 'medio';
        } else {
            $tramoKey = 'macro';
        }

        $benchmark = $platformTiers[$tramoKey];
        $benchmark['tramo_activo'] = $tramoKey;
        $benchmark['tramo_label'] = $benchmark['label'];

        return $benchmark;
    }
}
