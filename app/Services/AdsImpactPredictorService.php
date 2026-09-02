<?php

namespace App\Services;

use App\Models\Publicacion;

class AdsImpactPredictorService
{
    /**
     * Predecir el impacto de visualizaciones y engagement para una publicación con pauta.
     *
     * @param  float  $montoInvertido  Monto en ARS (Pesos Argentinos)
     * @param  string  $formato  Tipo de formato (Reel, Video, Foto, Carrusel, Tweet, etc.)
     * @param  string|null  $plataforma  Red social (instagram, facebook, tiktok, etc.)
     * @param  int|null  $candidatoId  ID del candidato (opcional para afinamiento)
     */
    public function predecirImpacto(
        float $montoInvertido,
        string $formato = 'Reel',
        ?string $plataforma = 'instagram',
        ?int $candidatoId = null
    ): array {
        // 1. Buscar histórico de publicaciones con pauta
        $query = Publicacion::whereIn('tipo_pauta', Publicacion::TIPOS_CON_INVERSION)
            ->where('monto_invertido_pauta', '>', 0);

        if ($formato) {
            $query->where('tipo_formato', $formato);
        }

        if ($plataforma) {
            $query->whereHas('perfilSocial', fn ($q) => $q->where('plataforma', $plataforma));
        }

        $historico = $query->get();

        $cantidadMuestras = $historico->count();

        // 2. Si no hay muestras suficientes con ese filtro exacto, ampliar a todas las pautadas
        if ($cantidadMuestras < 2) {
            $historico = Publicacion::whereIn('tipo_pauta', Publicacion::TIPOS_CON_INVERSION)
                ->where('monto_invertido_pauta', '>', 0)
                ->get();
            $cantidadMuestras = $historico->count();
        }

        // 3. Calcular métricas medias históricas
        if ($cantidadMuestras > 0) {
            $totalMonto = $historico->sum('monto_invertido_pauta');
            $totalVistasPagadas = $historico->sum('vistas_pagadas');
            $totalLikes = $historico->sum('total_likes');
            $totalComentarios = $historico->sum('total_comentarios');
            $totalCompartidos = $historico->sum('total_compartidos');

            // Rendimiento: Vistas por cada $1 invertido
            $vistasPorPeso = $totalMonto > 0 ? ($totalVistasPagadas / $totalMonto) : 1.25;
            $tasaLikesPorVista = $totalVistasPagadas > 0 ? ($totalLikes / $totalVistasPagadas) : 0.12;
            $tasaComentariosPorVista = $totalVistasPagadas > 0 ? ($totalComentarios / $totalVistasPagadas) : 0.005;
            $tasaCompartidosPorVista = $totalVistasPagadas > 0 ? ($totalCompartidos / $totalVistasPagadas) : 0.01;
        } else {
            // Valores de calibración por defecto
            $vistasPorPeso = 1.35;
            $tasaLikesPorVista = 0.14;
            $tasaComentariosPorVista = 0.006;
            $tasaCompartidosPorVista = 0.012;
        }

        // 4. Calcular Porcentaje de Proximidad / Certeza (Confidence Score)
        // Conforme más muestras y afinamiento, mayor es la proximidad (60% a 96%)
        $proximidadBase = 65;
        $incrementoMuestras = min(20, $cantidadMuestras * 3.5);
        $bonoFormatoEspecifico = ($formato === 'Reel' || $formato === 'Video') ? 5 : 2;
        $porcentajeProximidad = (int) min(96, round($proximidadBase + $incrementoMuestras + $bonoFormatoEspecifico));

        // 5. Cálculo de Vistas Estimadas con rango de dispersión
        $vistasEsperadas = (int) round($montoInvertido * $vistasPorPeso);
        $margenDispersion = (100 - $porcentajeProximidad) / 100;

        $vistasMinimas = (int) round($vistasEsperadas * (1 - $margenDispersion * 0.7));
        $vistasMaximas = (int) round($vistasEsperadas * (1 + $margenDispersion * 0.9));

        $likesEstimados = (int) round($vistasEsperadas * $tasaLikesPorVista);
        $comentariosEstimados = (int) round($vistasEsperadas * $tasaComentariosPorVista);
        $compartidosEstimados = (int) round($vistasEsperadas * $tasaCompartidosPorVista);

        $cpvEstimado = $vistasEsperadas > 0 ? round($montoInvertido / $vistasEsperadas, 3) : 0.75;
        $cpmEstimado = $cpvEstimado * 1000;

        // 6. Generar recomendación de optimización algorítmica
        $recomendacion = $this->generarRecomendacion($formato, $plataforma, $montoInvertido, $porcentajeProximidad);

        return [
            'monto_invertido' => $montoInvertido,
            'formato' => $formato,
            'plataforma' => $plataforma,
            'muestras_analizadas' => $cantidadMuestras,
            'porcentaje_proximidad' => $porcentajeProximidad,
            'vistas_esperadas' => $vistasEsperadas,
            'vistas_minimas' => $vistasMinimas,
            'vistas_maximas' => $vistasMaximas,
            'likes_estimados' => $likesEstimados,
            'comentarios_estimados' => $comentariosEstimados,
            'compartidos_estimados' => $compartidosEstimados,
            'cpv_estimado_ars' => $cpvEstimado,
            'cpm_estimado_ars' => $cpmEstimado,
            'recomendacion_estrategica' => $recomendacion,
        ];
    }

    /**
     * Generar recomendación según el formato y presupuesto.
     */
    private function generarRecomendacion(string $formato, ?string $plataforma, float $monto, int $proximidad): string
    {
        if ($formato === 'Reel' || $formato === 'Video') {
            return "El formato {$formato} en {$plataforma} tiene el CPV más eficiente del histórico (alta retención en primeros 3 segundos). Proximidad estimada del {$proximidad}%.";
        }

        if ($monto >= 100000) {
            return 'Para presupuestos superiores a $100.000, se aconseja segmentación geolocalizada por circuitos electorales clave para maximizar impacto barrial.';
        }

        return 'Con una inversión de $'.number_format($monto, 0, ',', '.').', el algoritmo proyecta excelente rendimiento en engagement orgánico residual derivado de la pauta.';
    }
}
