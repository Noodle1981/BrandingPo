<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Services\ReporteEjecutivoService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteEjecutivoService $reporteService
    ) {}

    /**
     * Vista Web Imprimible optimizada (@media print en Executive Light)
     * del Reporte Ejecutivo Trimestral con Storytelling y Métricas Reales.
     */
    public function ejecutivo(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidatoId = $request->input('candidato_id');

        $candidatoQuery = Candidato::delWorkspace($workspace)
            ->with(['perfilesSociales', 'territorio', 'cicloCampana']);

        $candidato = $candidatoId
            ? (clone $candidatoQuery)->find($candidatoId)
            : (clone $candidatoQuery)->propio()->first();

        if (! $candidato) {
            $candidato = (clone $candidatoQuery)->firstOrFail();
        }

        $reporteData = $this->reporteService->generarReporteTrimestral($candidato);

        // Candidatos del workspace para permitir conmutar el reporte desde la vista
        $candidatosLista = Candidato::delWorkspace($workspace)
            ->get(['id', 'nombre_completo', 'cargo_aspirado', 'es_propio', 'avatar_url']);

        return Inertia::render('Reportes/Ejecutivo', [
            'reporte' => $reporteData,
            'candidatos_lista' => $candidatosLista,
        ]);
    }
}
