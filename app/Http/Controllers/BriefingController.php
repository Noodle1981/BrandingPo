<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\CicloCampana;
use App\Models\InformeEjecutivo;
use App\Models\Publicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BriefingController extends Controller
{
    /**
     * Centro de Informes & Briefings Ejecutivos del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $cicloId = $request->input('ciclo_id');

        $query = InformeEjecutivo::where('workspace_id', $workspace->id)
            ->with('cicloCampana')
            ->orderByDesc('fecha_generacion');

        if ($cicloId) {
            $query->where('ciclo_campana_id', $cicloId);
        }

        $informes = $query->get()->map(function ($inf) {
            return [
                'id' => $inf->id,
                'titulo' => $inf->titulo,
                'fecha_generacion' => $inf->fecha_generacion?->format('d/m/Y'),
                'periodo_cubierto' => $inf->periodo_cubierto,
                'resumen_ejecutivo' => $inf->resumen_ejecutivo,
                'metricas_snapshot' => $inf->metricas_clave_snapshot,
                'conclusiones' => $inf->conclusiones_estrategicas,
                'ciclo' => $inf->cicloCampana?->nombre,
                'ciclo_campana_id' => $inf->ciclo_campana_id,
            ];
        });

        $ciclos = CicloCampana::where('workspace_id', $workspace->id)
            ->orderByDesc('anio')
            ->get(['id', 'nombre', 'anio']);

        return Inertia::render('Briefings/Index', [
            'informes' => $informes,
            'ciclos' => $ciclos,
            'filtros' => [
                'ciclo_id' => $cicloId,
            ],
        ]);
    }

    /**
     * Vista imprimible / PDF-ready del briefing ejecutivo.
     */
    public function show(InformeEjecutivo $informe): Response
    {
        $informe->load('cicloCampana');

        return Inertia::render('Briefings/Show', [
            'informe' => [
                'id' => $informe->id,
                'titulo' => $informe->titulo,
                'fecha_generacion' => $informe->fecha_generacion?->format('d/m/Y'),
                'periodo_cubierto' => $informe->periodo_cubierto,
                'resumen_ejecutivo' => $informe->resumen_ejecutivo,
                'metricas_snapshot' => $informe->metricas_clave_snapshot,
                'conclusiones' => $informe->conclusiones_estrategicas,
                'ciclo' => $informe->cicloCampana?->nombre,
            ],
        ]);
    }

    /**
     * Generar / Guardar un nuevo informe ejecutivo.
     */
    public function store(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'ciclo_campana_id' => ['required', 'exists:ciclo_campanas,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'periodo_cubierto' => ['required', 'string', 'max:255'],
            'resumen_ejecutivo' => ['required', 'string'],
            'conclusiones_estrategicas' => ['nullable', 'string'],
        ]);

        $publicaciones = Publicacion::where('workspace_id', $workspace->id)->get();

        $snapshot = [
            'total_publicaciones' => $publicaciones->count(),
            'total_vistas' => $publicaciones->sum('total_vistas'),
            'total_pauta_invertida' => (float) $publicaciones->sum('monto_invertido_pauta'),
            'fecha_corte' => now()->toDateTimeString(),
        ];

        $informe = InformeEjecutivo::create([
            'workspace_id' => $workspace->id,
            'ciclo_campana_id' => $validated['ciclo_campana_id'],
            'titulo' => $validated['titulo'],
            'fecha_generacion' => now()->toDateString(),
            'periodo_cubierto' => $validated['periodo_cubierto'],
            'resumen_ejecutivo' => $validated['resumen_ejecutivo'],
            'metricas_clave_snapshot' => $snapshot,
            'conclusiones_estrategicas' => $validated['conclusiones_estrategicas'] ?? null,
        ]);

        return redirect()->route('briefings.index')
            ->with('success', "Informe '{$informe->titulo}' generado exitosamente.");
    }
}
