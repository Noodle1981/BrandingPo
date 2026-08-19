<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\PresupuestoPartida;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PresupuestoController extends Controller
{
    /**
     * Tablero de Finanzas & Presupuesto de Campaña.
     */
    public function index(Request $request): Response
    {
        $cicloId = $request->input('ciclo_id');

        $query = PresupuestoPartida::with(['cicloCampana', 'candidato']);

        if ($cicloId) {
            $query->where('ciclo_campana_id', $cicloId);
        }

        $partidas = $query->get()->map(function ($p) {
            $asignado = (float)$p->monto_asignado;
            $ejecutado = (float)$p->monto_ejecutado;
            $porcentaje = $asignado > 0 ? round(($ejecutado / $asignado) * 100, 1) : 0;
            $saldo = $asignado - $ejecutado;

            return [
                'id' => $p->id,
                'categoria' => $p->categoria,
                'monto_asignado' => $asignado,
                'monto_ejecutado' => $ejecutado,
                'saldo_disponible' => $saldo,
                'porcentaje_ejecucion' => $porcentaje,
                'notas' => $p->notas,
                'candidato' => $p->candidato?->nombre_completo,
                'ciclo' => $p->cicloCampana?->nombre,
            ];
        });

        $totalAsignado = $partidas->sum('monto_asignado');
        $totalEjecutado = $partidas->sum('monto_ejecutado');
        $saldoTotal = $totalAsignado - $totalEjecutado;
        $porcentajeGlobal = $totalAsignado > 0 ? round(($totalEjecutado / $totalAsignado) * 100, 1) : 0;

        $ciclos = CicloCampana::orderByDesc('anio')->get(['id', 'nombre', 'anio']);
        $candidatos = Candidato::orderByDesc('es_propio')->orderBy('nombre_completo')->get(['id', 'nombre_completo', 'es_propio']);

        return Inertia::render('Presupuesto/Index', [
            'partidas' => $partidas,
            'ciclos' => $ciclos,
            'candidatos' => $candidatos,
            'filtros' => [
                'ciclo_id' => $cicloId,
            ],
            'resumen_financiero' => [
                'total_asignado' => $totalAsignado,
                'total_ejecutado' => $totalEjecutado,
                'saldo_disponible' => $saldoTotal,
                'porcentaje_global' => $porcentajeGlobal,
            ],
            'categorias_disponibles' => [
                ['key' => 'pauta_digital', 'label' => 'Pauta Digital (Meta/TikTok/Google)'],
                ['key' => 'via_publica', 'label' => 'Vía Pública & Cartelería'],
                ['key' => 'produccion_audiovisual', 'label' => 'Producción Audiovisual & Spots'],
                ['key' => 'eventos_territoriales', 'label' => 'Eventos Territoriales & Sonido'],
                ['key' => 'honorarios', 'label' => 'Honorarios & Consultoría'],
                ['key' => 'contingencias', 'label' => 'Fondo de Contingencia & Crisis'],
            ]
        ]);
    }

    /**
     * Registrar una nueva partida presupuestaria.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ciclo_campana_id' => ['required', 'exists:ciclo_campanas,id'],
            'candidato_id' => ['nullable', 'exists:candidatos,id'],
            'categoria' => ['required', 'string'],
            'monto_asignado' => ['required', 'numeric', 'min:0'],
            'monto_ejecutado' => ['nullable', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string'],
        ]);

        PresupuestoPartida::create([
            'ciclo_campana_id' => $validated['ciclo_campana_id'],
            'candidato_id' => $validated['candidato_id'] ?? null,
            'categoria' => $validated['categoria'],
            'monto_asignado' => $validated['monto_asignado'],
            'monto_ejecutado' => $validated['monto_ejecutado'] ?? 0,
            'notas' => $validated['notas'] ?? null,
        ]);

        return redirect()->route('presupuesto.index')
            ->with('success', 'Partida presupuestaria registrada con éxito.');
    }

    /**
     * Eliminar partida.
     */
    public function destroy(PresupuestoPartida $partida): RedirectResponse
    {
        $partida->delete();

        return redirect()->route('presupuesto.index')
            ->with('success', 'Partida eliminada.');
    }
}
