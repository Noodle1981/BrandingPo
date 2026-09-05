<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\EventoCalendario;
use App\Models\Publicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CalendarioController extends Controller
{
    /**
     * Agenda de Campaña & Timeline de Eventos centrado en Ejes Temáticos.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $tipo = $request->input('tipo');
        $candidatoId = $request->input('candidato_id');
        $ejeTematicoId = $request->input('eje_tematico_id');

        $query = EventoCalendario::where('workspace_id', $workspace->id)
            ->with(['candidato', 'cicloCampana', 'ejeTematico'])
            ->orderBy('fecha_inicio');

        if ($tipo) {
            $query->where('tipo_evento', $tipo);
        }

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        if ($ejeTematicoId) {
            $query->where('eje_tematico_id', $ejeTematicoId);
        }

        $eventos = $query->get()->map(function ($e) {
            return [
                'id' => $e->id,
                'titulo' => $e->titulo,
                'fecha_inicio' => $e->fecha_inicio?->format('d/m/Y H:i'),
                'fecha_fin' => $e->fecha_fin?->format('d/m/Y H:i'),
                'fecha_iso' => $e->fecha_inicio?->toIso8601String(),
                'tipo_evento' => $e->tipo_evento,
                'lugar' => $e->lugar,
                'estado' => $e->estado,
                'notas' => $e->notas,
                'candidato' => [
                    'id' => $e->candidato?->id,
                    'nombre' => $e->candidato?->nombre_completo,
                    'es_propio' => $e->candidato?->es_propio,
                ],
                'eje_tematico' => $e->ejeTematico ? [
                    'id' => $e->ejeTematico->id,
                    'nombre' => $e->ejeTematico->nombre,
                    'pilar_principal' => $e->ejeTematico->pilar_principal,
                    'color_badge' => $e->ejeTematico->color_badge,
                    'icono' => $e->ejeTematico->icono,
                ] : null,
                'ciclo' => $e->cicloCampana?->nombre,
            ];
        });

        // Publicaciones del workspace para cruce y diagnóstico estratégico por eje
        $publicacionesPorEje = Publicacion::where('workspace_id', $workspace->id)
            ->whereNotNull('eje_tematico_id')
            ->get(['id', 'eje_tematico_id', 'total_likes', 'total_comentarios', 'total_vistas', 'score_interacciones']);

        // Eventos agendados agrupados por eje
        $eventosPorEje = EventoCalendario::where('workspace_id', $workspace->id)
            ->whereNotNull('eje_tematico_id')
            ->get(['id', 'eje_tematico_id']);

        $ejes = EjeTematico::where('workspace_id', $workspace->id)
            ->orderBy('orden')
            ->get()
            ->map(function ($eje) use ($publicacionesPorEje, $eventosPorEje) {
                $pubsEje = $publicacionesPorEje->where('eje_tematico_id', $eje->id);
                $evsEje = $eventosPorEje->where('eje_tematico_id', $eje->id);

                $totalPubs = $pubsEje->count();
                $totalEventos = $evsEje->count();
                $totalScore = $pubsEje->sum('score_interacciones');
                $avgScore = $totalPubs > 0 ? round($totalScore / $totalPubs, 1) : 0;
                $totalInteracciones = $pubsEje->sum(fn ($p) => (int) $p->total_likes + (int) $p->total_comentarios);

                // Diagnóstico inteligente:
                // - MAXIMIZAR (alto rendimiento/aprobación social): sostener publicaciones continuas.
                // - REFORZAR (falta cobertura en publicaciones o sin eventos agendados): agendar contenido urgente.
                // - EQUILIBRADO (presencia constante).
                if ($totalPubs >= 2 && ($avgScore >= 12 || $totalInteracciones >= 40)) {
                    $diagnostico = 'maximizar';
                    $diagnosticoTexto = 'Alto engagement. Mantener publicaciones.';
                    $diagnosticoBadge = '🚀 Maximizar';
                } elseif ($totalPubs <= 1 || $totalEventos === 0) {
                    $diagnostico = 'reforzar';
                    $diagnosticoTexto = 'Poca presencia. Agendar contenido urgente.';
                    $diagnosticoBadge = '⚠️ Reforzar';
                } else {
                    $diagnostico = 'equilibrado';
                    $diagnosticoTexto = 'Cobertura activa y equilibrada.';
                    $diagnosticoBadge = '✅ En Flujo';
                }

                return [
                    'id' => $eje->id,
                    'nombre' => $eje->nombre,
                    'pilar_principal' => $eje->pilar_principal,
                    'color_badge' => $eje->color_badge ?? '#06b6d4',
                    'icono' => $eje->icono ?? 'Flag',
                    'total_publicaciones' => $totalPubs,
                    'total_eventos' => $totalEventos,
                    'total_interacciones' => $totalInteracciones,
                    'score_promedio' => $avgScore,
                    'diagnostico' => $diagnostico,
                    'diagnostico_texto' => $diagnosticoTexto,
                    'diagnostico_badge' => $diagnosticoBadge,
                ];
            });

        $statsEjes = [
            'total_ejes' => $ejes->count(),
            'ejes_maximizar' => $ejes->where('diagnostico', 'maximizar')->count(),
            'ejes_reforzar' => $ejes->where('diagnostico', 'reforzar')->count(),
            'ejes_equilibrados' => $ejes->where('diagnostico', 'equilibrado')->count(),
        ];

        $ciclos = CicloCampana::where('workspace_id', $workspace->id)
            ->orderByDesc('anio')
            ->get(['id', 'nombre', 'anio']);

        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'es_propio']);

        return Inertia::render('Calendario/Index', [
            'eventos' => $eventos,
            'ciclos' => $ciclos,
            'candidatos' => $candidatos,
            'ejes' => $ejes,
            'stats_ejes' => $statsEjes,
            'filtros' => [
                'tipo' => $tipo,
                'candidato_id' => $candidatoId,
                'eje_tematico_id' => $ejeTematicoId,
            ],
            'tipos_disponibles' => [
                ['key' => 'publicacion_eje', 'label' => '📱 Publicación / Contenido de Eje'],
                ['key' => 'acto', 'label' => '🚩 Acto Territorial / Barrial'],
                ['key' => 'debate', 'label' => '📺 Debate / Entrevista de Eje'],
                ['key' => 'pauta_vencimiento', 'label' => '💰 Pauta & Anuncios de Eje'],
                ['key' => 'caravana', 'label' => '🚗 Caravana / Recorrida'],
                ['key' => 'rueda_prensa', 'label' => '🎙️ Rueda de Prensa / Anuncio'],
                ['key' => 'reunion_privada', 'label' => '🤝 Mesa Estratégica'],
            ],
        ]);
    }

    /**
     * Registrar evento en el calendario.
     */
    public function store(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'ciclo_campana_id' => ['required', Rule::exists('ciclo_campanas', 'id')->where('workspace_id', $workspace->id)],
            'candidato_id' => ['nullable', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
            'eje_tematico_id' => ['nullable', Rule::exists('eje_tematicos', 'id')->where('workspace_id', $workspace->id)],
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'tipo_evento' => ['required', 'string'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:programado,realizado,cancelado'],
            'notas' => ['nullable', 'string'],
        ]);

        if (empty($validated['candidato_id'])) {
            $propio = Candidato::where('workspace_id', $workspace->id)
                ->where('es_propio', true)
                ->first();
            $validated['candidato_id'] = $propio?->id;
        }

        EventoCalendario::create([
            'workspace_id' => $workspace->id,
            ...$validated,
        ]);

        return redirect()->route('calendario.index')
            ->with('success', 'Evento programado en la agenda de campaña.');
    }

    /**
     * Eliminar evento del calendario.
     */
    public function destroy(Request $request, EventoCalendario $evento): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($evento, $workspace);

        $evento->delete();

        return redirect()->route('calendario.index')
            ->with('success', 'Evento eliminado del calendario.');
    }
}

