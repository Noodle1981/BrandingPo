<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\EventoCalendario;
use App\Models\Publicacion;
use App\Models\Workspace;

class CalendarioService
{
    /**
     * Construye y calcula los eventos, diagnósticos por eje y filtros de la Agenda de Campaña.
     */
    public function obtenerAgendaData(Workspace $workspace, array $filtros): array
    {
        $tipo = $filtros['tipo'] ?? null;
        $candidatoId = $filtros['candidato_id'] ?? null;
        $ejeTematicoId = $filtros['eje_tematico_id'] ?? null;

        $query = EventoCalendario::delWorkspace($workspace)
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

        $publicacionesPorEje = Publicacion::delWorkspace($workspace)
            ->whereNotNull('eje_tematico_id')
            ->get(['id', 'eje_tematico_id', 'total_likes', 'total_comentarios', 'total_vistas', 'score_interacciones']);

        $eventosPorEje = EventoCalendario::delWorkspace($workspace)
            ->whereNotNull('eje_tematico_id')
            ->get(['id', 'eje_tematico_id']);

        $ejes = EjeTematico::delWorkspace($workspace)
            ->ordenados()
            ->get()
            ->map(function ($eje) use ($publicacionesPorEje, $eventosPorEje) {
                $pubsEje = $publicacionesPorEje->where('eje_tematico_id', $eje->id);
                $evsEje = $eventosPorEje->where('eje_tematico_id', $eje->id);

                $totalPubs = $pubsEje->count();
                $totalEventos = $evsEje->count();
                $totalScore = $pubsEje->sum('score_interacciones');
                $avgScore = $totalPubs > 0 ? round($totalScore / $totalPubs, 1) : 0;
                $totalInteracciones = $pubsEje->sum(fn ($p) => (int) $p->total_likes + (int) $p->total_comentarios);

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

        $candidatos = Candidato::delWorkspace($workspace)
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'es_propio']);

        return [
            'eventos' => $eventos,
            'ciclos' => $ciclos,
            'candidatos' => $candidatos,
            'ejes' => $ejes,
            'stats_ejes' => $statsEjes,
            'filtros' => [
                'tipo' => $tipo,
                'candidato_id' => $candidatoId ? (int) $candidatoId : null,
                'eje_tematico_id' => $ejeTematicoId ? (int) $ejeTematicoId : null,
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
        ];
    }
}
