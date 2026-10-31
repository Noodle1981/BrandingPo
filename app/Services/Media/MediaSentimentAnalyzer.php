<?php

namespace App\Services\Media;

use Illuminate\Support\Collection;

class MediaSentimentAnalyzer
{
    /**
     * Motor de Detección de Menciones: busca nombre completo, apellidos y handles (@) de los candidatos.
     */
    public function detectarMencionCandidato(string $texto, Collection $candidatos): ?array
    {
        $textoLimpio = mb_strtolower($texto, 'UTF-8');

        foreach ($candidatos as $candidato) {
            $nombreCompleto = mb_strtolower($candidato->nombre_completo, 'UTF-8');
            $partesNombre = preg_split('/\s+/', $nombreCompleto);
            $apellido = end($partesNombre);

            // 1. Coincidencia de Handle de Redes (@usuario)
            if ($candidato->relationLoaded('perfilesSociales') || method_exists($candidato, 'perfilesSociales')) {
                foreach ($candidato->perfilesSociales as $perfil) {
                    $handle = ltrim(mb_strtolower($perfil->usuario_handle ?? '', 'UTF-8'), '@');
                    if ($handle && (str_contains($textoLimpio, '@'.$handle) || str_contains($textoLimpio, $handle))) {
                        return [
                            'candidato' => $candidato,
                            'tipo_mencion' => 'etiqueta_directa',
                        ];
                    }
                }
            }

            // 2. Coincidencia por Nombre Completo (ej: "Federico Sisterna")
            if (str_contains($textoLimpio, $nombreCompleto)) {
                return [
                    'candidato' => $candidato,
                    'tipo_mencion' => 'titular',
                ];
            }

            // 3. Coincidencia por Apellido representativo (longitud >= 4)
            if (mb_strlen($apellido, 'UTF-8') >= 4 && preg_match('/\b'.preg_quote($apellido, '/').'\b/iu', $textoLimpio)) {
                return [
                    'candidato' => $candidato,
                    'tipo_mencion' => 'cuerpo',
                ];
            }
        }

        return null;
    }

    /**
     * Evaluar el Tono Editorial (favorable, neutro, crítico) y Puntuación Numérica (-1.00 a +1.00).
     */
    public function evaluarTonoYSentimiento(string $titulo, string $resumen, string $origen, ?array $reacciones = null): array
    {
        $textoCompleto = mb_strtolower($titulo.' '.$resumen, 'UTF-8');

        // Si es Facebook y tenemos desglose de reacciones emocionales:
        if ($origen === 'facebook' && is_array($reacciones)) {
            $likes = (int) ($reacciones['likes'] ?? 0);
            $love = (int) ($reacciones['love'] ?? 0);
            $haha = (int) ($reacciones['haha'] ?? 0);
            $wow = (int) ($reacciones['wow'] ?? 0);
            $sad = (int) ($reacciones['sad'] ?? 0);
            $angry = (int) ($reacciones['angry'] ?? 0);

            $totalReacciones = $likes + $love + $haha + $wow + $sad + $angry;

            if ($totalReacciones > 0) {
                $porcentajeEnojo = ($angry / $totalReacciones) * 100;
                $porcentajePositivo = (($likes + $love) / $totalReacciones) * 100;

                // Regla B.4 / B.8: Alerta roja inmediata si 😡 > 15%
                if ($porcentajeEnojo >= 15 || $angry > ($likes + $love)) {
                    $score = round(-0.50 - ($porcentajeEnojo / 200), 2);

                    return [
                        'tono' => 'critico',
                        'score' => max(-1.00, $score),
                    ];
                }

                if ($porcentajePositivo >= 60) {
                    $score = round(0.40 + ($porcentajePositivo / 200), 2);

                    return [
                        'tono' => 'favorable',
                        'score' => min(1.00, $score),
                    ];
                }
            }
        }

        // Análisis léxico en español por palabras clave
        $palabrasFavorables = [
            'inauguró', 'inaugura', 'pavimentación', 'obras', 'avance', 'crecimiento',
            'apoyo', 'lidera', 'éxito', 'felicitó', 'premio', 'mejora', 'acuerdo',
            'inversión', 'solución', 'récord', 'transforma', 'impulsa', 'respaldo',
        ];

        $palabrasCriticas = [
            'denuncia', 'denuncian', 'colapso', 'polémica', 'escándalo', 'imputado',
            'corrupción', 'demoras', 'crítica', 'reclamo', 'freno', 'crisis',
            'cuestionan', 'irregularidad', 'conflicto', 'ajuste', 'paro', 'rechazo',
        ];

        $puntosPositivos = 0;
        $puntosNegativos = 0;

        foreach ($palabrasFavorables as $palabra) {
            if (str_contains($textoCompleto, $palabra)) {
                $puntosPositivos++;
            }
        }

        foreach ($palabrasCriticas as $palabra) {
            if (str_contains($textoCompleto, $palabra)) {
                $puntosNegativos++;
            }
        }

        if ($puntosNegativos > $puntosPositivos) {
            $score = -0.30 - (min($puntosNegativos, 5) * 0.12);

            return [
                'tono' => 'critico',
                'score' => max(-1.00, round($score, 2)),
            ];
        }

        if ($puntosPositivos > $puntosNegativos) {
            $score = 0.30 + (min($puntosPositivos, 5) * 0.12);

            return [
                'tono' => 'favorable',
                'score' => min(1.00, round($score, 2)),
            ];
        }

        return [
            'tono' => 'neutro',
            'score' => 0.00,
        ];
    }
}
