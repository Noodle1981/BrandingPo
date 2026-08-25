<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\Publicacion;
use App\Services\AdsImpactPredictorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AdsImpactPredictorService $predictorService
    ) {}

    /**
     * Tablero de Analítica Central & War Room Metrics del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);

        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->with(['perfilesSociales', 'cicloCampana'])
            ->get();

        $publicaciones = Publicacion::where('workspace_id', $workspace->id)
            ->with(['candidato', 'perfilSocial'])
            ->get();

        $totalVistas = $publicaciones->sum('total_vistas');
        $totalLikes = $publicaciones->sum('total_likes');
        $totalComentarios = $publicaciones->sum('total_comentarios');
        $totalCompartidos = $publicaciones->sum('total_compartidos');
        $totalPauta = $publicaciones->sum('monto_invertido_pauta');

        // Share of Voice por Candidato (Volumen de Vistas)
        $shareOfVoice = $candidatos->map(function ($c) use ($totalVistas, $publicaciones) {
            $vistasCand = $publicaciones->where('candidato_id', $c->id)->sum('total_vistas');
            $porcentaje = $totalVistas > 0 ? round(($vistasCand / $totalVistas) * 100, 1) : 0;

            return [
                'id' => $c->id,
                'nombre' => $c->nombre_completo,
                'es_propio' => $c->es_propio,
                'color' => $c->color_hex,
                'vistas' => $vistasCand,
                'porcentaje' => $porcentaje,
                'seguidores' => $c->perfilesSociales->sum('seguidores_actuales'),
                'posts_count' => $publicaciones->where('candidato_id', $c->id)->count(),
                'pauta_invertida' => $publicaciones->where('candidato_id', $c->id)->sum('monto_invertido_pauta'),
            ];
        });

        // Distribución por Plataforma
        $plataformasStats = [
            'instagram' => $publicaciones->filter(fn ($p) => $p->perfilSocial?->plataforma === 'instagram')->sum('total_vistas'),
            'facebook' => $publicaciones->filter(fn ($p) => $p->perfilSocial?->plataforma === 'facebook')->sum('total_vistas'),
            'tiktok' => $publicaciones->filter(fn ($p) => $p->perfilSocial?->plataforma === 'tiktok')->sum('total_vistas'),
            'x_twitter' => $publicaciones->filter(fn ($p) => $p->perfilSocial?->plataforma === 'x_twitter')->sum('total_vistas'),
            'youtube' => $publicaciones->filter(fn ($p) => $p->perfilSocial?->plataforma === 'youtube')->sum('total_vistas'),
            'linkedin' => $publicaciones->filter(fn ($p) => $p->perfilSocial?->plataforma === 'linkedin')->sum('total_vistas'),
        ];

        // Alcance Orgánico vs Pautado
        $vistasOrganicasTotales = $publicaciones->sum('vistas_organicas');
        $vistasPagadasTotales = $publicaciones->sum('vistas_pagadas');

        // Simulación inicial por defecto
        $simulacionInicial = $this->predictorService->predecirImpacto(50000, 'Reel', 'instagram');

        return Inertia::render('Analytics/Index', [
            'metricas_generales' => [
                'total_publicaciones' => $publicaciones->count(),
                'total_vistas' => $totalVistas,
                'total_likes' => $totalLikes,
                'total_comentarios' => $totalComentarios,
                'total_compartidos' => $totalCompartidos,
                'total_pauta' => $totalPauta,
                'vistas_organicas' => $vistasOrganicasTotales,
                'vistas_pagadas' => $vistasPagadasTotales,
            ],
            'share_of_voice' => $shareOfVoice,
            'plataformas_stats' => $plataformasStats,
            'candidatos' => $candidatos->map(fn ($c) => [
                'id' => $c->id,
                'nombre_completo' => $c->nombre_completo,
                'es_propio' => $c->es_propio,
                'color_hex' => $c->color_hex,
            ]),
            'simulacion_inicial' => $simulacionInicial,
        ]);
    }

    /**
     * API del Predictor de Impacto de Pauta con Porcentaje de Proximidad.
     */
    public function predictApi(Request $request): JsonResponse
    {
        $monto = (float) $request->input('monto', 50000);
        $formato = (string) $request->input('formato', 'Reel');
        $plataforma = (string) $request->input('plataforma', 'instagram');
        $candidatoId = $request->input('candidato_id') ? (int) $request->input('candidato_id') : null;

        $resultado = $this->predictorService->predecirImpacto($monto, $formato, $plataforma, $candidatoId);

        return response()->json($resultado);
    }
}
