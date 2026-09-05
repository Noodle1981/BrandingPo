<?php

namespace App\Services;

class PoliticaEngagementService
{
    /**
     * Rangos de referencia de TAP (Tasa de Aceptación Ponderada) por Tier electoral.
     * Basado en el volumen de seguidores de la cuenta auditada.
     */
    private const TIERS = [
        'micro' => [
            'min_seguidores' => 1_000,
            'max_seguidores' => 10_000,
            'organico' => ['min' => 5.0, 'max' => 10.0],
            'pauta'    => ['min' => 2.5, 'max' => 5.0],
        ],
        'medio' => [
            'min_seguidores' => 10_001,
            'max_seguidores' => 100_000,
            'organico' => ['min' => 3.5, 'max' => 6.0],
            'pauta'    => ['min' => 1.5, 'max' => 3.0],
        ],
        'macro' => [
            'min_seguidores' => 100_001,
            'max_seguidores' => 1_000_000,
            'organico' => ['min' => 2.0, 'max' => 3.5],
            'pauta'    => ['min' => 1.0, 'max' => 1.8],
        ],
        'mega' => [
            'min_seguidores' => 1_000_001,
            'max_seguidores' => PHP_INT_MAX,
            'organico' => ['min' => 1.0, 'max' => 2.0],
            'pauta'    => ['min' => 0.4, 'max' => 1.0],
        ],
    ];

    /**
     * Calcula la Tasa de Aceptación Política Real, normaliza por Tier y audita anomalías forenses.
     *
     * @param array{
     *     seguidores_canal?: int,
     *     likes?: int,
     *     comentarios?: int,
     *     compartidos?: int,
     *     republicados?: int,
     *     es_pauta?: bool,
     *     plataforma?: string
     * } $data
     * @return array{
     *     tier: string,
     *     seguidores_base: int,
     *     es_pauta: bool,
     *     interacciones_brutas: int,
     *     vtp_ponderado: float,
     *     er_tradicional: float,
     *     tap_politica_real: float,
     *     benchmark_esperado: array{min: float, max: float},
     *     score_traccion_indexado: int,
     *     sospecha_de_bots: bool,
     *     alertas_forenses: array<int, string>,
     *     etiqueta_calidad: string
     * }
     */
    public function calcularTasaAceptacionReal(array $data): array
    {
        $seguidores = max(1, (int) ($data['seguidores_canal'] ?? 1));
        $likes = max(0, (int) ($data['likes'] ?? 0));
        $comentarios = max(0, (int) ($data['comentarios'] ?? 0));
        $compartidos = max(0, (int) ($data['compartidos'] ?? 0));
        $republicados = max(0, (int) ($data['republicados'] ?? 0));
        $esPauta = (bool) ($data['es_pauta'] ?? false);

        // 1. Interacciones brutas y Volumen de Tracción Ponderada (VTP)
        // Pesos cívicos: Like = 1.0, Comentario = 3.0, Compartido = 5.0, Republicado = 10.0
        $totalInteraccionesBrutas = $likes + $comentarios + $compartidos + $republicados;

        $vtp = ($likes * 1.0)
            + ($comentarios * 3.0)
            + ($compartidos * 5.0)
            + ($republicados * 10.0);

        // 2. Tasa de Aceptación Tradicional vs. Ponderada (sobre seguidores del canal auditado)
        $erTradicional = round(($totalInteraccionesBrutas / $seguidores) * 100, 2);
        $tap = round(($vtp / $seguidores) * 100, 2);

        // 3. Normalización por Tier Electoral (Score indexado 0 a 100)
        $tier = $this->obtenerTier($seguidores);
        $benchmark = self::TIERS[$tier][$esPauta ? 'pauta' : 'organico'];

        $bMin = (float) $benchmark['min'];
        $bMax = (float) $benchmark['max'];
        $rango = max(0.1, $bMax - $bMin);

        // Si el TAP rinde en el mínimo esperado de su tier: 50 pts
        // Si el TAP rinde en el máximo esperado de su tier: 80 pts (Sobresaliente)
        // Si supera el máximo: escala hacia 100 pts.
        if ($tap <= $bMin) {
            $scoreNormalizado = (int) round(clamp(($tap / max(0.01, $bMin)) * 50, 0, 50));
        } else {
            $proporcionSobreMin = ($tap - $bMin) / $rango;
            $scoreNormalizado = (int) round(clamp(50 + ($proporcionSobreMin * 30), 50, 100));
        }

        // 4. Auditoría Forense de Anomalías y Bots (Sin depender de vistas ni APIs)
        $alertas = [];

        // Anomalía A: Granja de likes (muchos likes pasivos, escasa conversación)
        if ($likes >= 200 && $comentarios < max(2, (int) round($likes * 0.008))) {
            $alertas[] = 'Posible inyección de likes: ratio de comentarios sospechosamente bajo (<0.8%).';
        }

        // Anomalía B: Post estático sin recomendación ciudadana (muchos likes, sin difusión)
        if ($likes >= 350 && $compartidos < max(2, (int) round($likes * 0.01))) {
            $alertas[] = 'Falta de tracción cívica: menos del 1% de los usuarios compartieron el post.';
        }

        // Anomalía C: Spam de compartidos por cuentas títere o scripts
        if ($likes >= 50 && $compartidos > ($likes * 1.2) && $comentarios < 10) {
            $alertas[] = 'Anomalía de compartidos desproporcionados (>120% de los likes): posible acción coordinada.';
        }

        // Anomalía D: Audiencia fantasma / muerta
        if ($seguidores >= 50_000 && $tap < 0.15 && ! $esPauta) {
            $alertas[] = 'Ratio crítico de actividad: cuenta con posible base de seguidores inactiva o comprada.';
        }

        return [
            'tier' => $tier,
            'seguidores_base' => $seguidores,
            'es_pauta' => $esPauta,
            'interacciones_brutas' => $totalInteraccionesBrutas,
            'vtp_ponderado' => round($vtp, 1),
            'er_tradicional' => $erTradicional,
            'tap_politica_real' => $tap,
            'benchmark_esperado' => $benchmark,
            'score_traccion_indexado' => $scoreNormalizado,
            'sospecha_de_bots' => ! empty($alertas),
            'alertas_forenses' => $alertas,
            'etiqueta_calidad' => match (true) {
                $scoreNormalizado >= 75 => 'Tracción Sobresaliente',
                $scoreNormalizado >= 60 => 'Compromiso Sólido',
                $scoreNormalizado >= 40 => 'Estándar Electoral',
                default => 'Bajo Involucramiento',
            },
        ];
    }

    /**
     * Clasifica el volumen de seguidores en su Tier electoral correspondiente.
     */
    public function obtenerTier(int $seguidores): string
    {
        return match (true) {
            $seguidores >= 1_000_001 => 'mega',
            $seguidores >= 100_001   => 'macro',
            $seguidores >= 10_001    => 'medio',
            default                  => 'micro',
        };
    }
}
