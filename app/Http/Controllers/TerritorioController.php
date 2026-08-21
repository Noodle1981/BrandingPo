<?php

namespace App\Http\Controllers;

use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\Territorio;
use App\Services\DemographicIntelligenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TerritorioController extends Controller
{
    public function __construct(
        protected DemographicIntelligenceService $demographicService
    ) {}

    /**
     * Mapa de Situación Territorial & Inteligencia Demográfica del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $territorioId = $request->query('territorio_id');

        $nivelPolitico = $workspace->nivel_politico ?? 'intendente';
        $esGobernador = in_array($nivelPolitico, ['gobernador', 'senador', 'legislador_nacional']);

        // Territorios pertenecientes a este workspace
        $provincia = Territorio::where('workspace_id', $workspace->id)
            ->where(function ($q) {
                $q->where('tipo', 'provincia')->orWhereNull('parent_id');
            })
            ->first();

        // Si no hay provincia creada en el workspace pero hay alguna en la BD o fallback
        if (!$provincia) {
            $provincia = Territorio::where('tipo', 'provincia')->first();
        }

        $departamentosQuery = Territorio::where('workspace_id', $workspace->id);
        if ($provincia) {
            $departamentos = (clone $departamentosQuery)
                ->where('parent_id', $provincia->id)
                ->with(['candidatoPropio.perfilesSociales', 'candidatos' => function ($q) {
                    $q->with('perfilesSociales');
                }])
                ->orderBy('nombre')
                ->get();
        } else {
            $departamentos = (clone $departamentosQuery)
                ->with(['candidatoPropio.perfilesSociales', 'candidatos' => function ($q) {
                    $q->with('perfilesSociales');
                }])
                ->orderBy('nombre')
                ->get();
        }

        // Determinar candidato propio del workspace
        $candidatoPropio = Candidato::where('workspace_id', $workspace->id)
            ->where('es_propio', true)
            ->with('territorio')
            ->first();

        // Determinar territorio activo según el nivel político
        if ($esGobernador && !$territorioId) {
            $territorioActivo = $provincia ?: $departamentos->first();
        } elseif ($territorioId) {
            $territorioActivo = $departamentos->firstWhere('id', $territorioId) ?: ($provincia ?: $departamentos->first());
        } else {
            $territorioActivoId = $candidatoPropio?->territorio_id ?: $departamentos->first()?->id;
            $territorioActivo = $departamentos->firstWhere('id', $territorioActivoId) ?: ($provincia ?: $departamentos->first());
        }

        // Generar o recuperar pirámide etaria del territorio activo
        $piramide = null;
        $estrategia = null;
        if ($territorioActivo) {
            $piramide = $territorioActivo->piramide_etaria ?: $this->demographicService->generarPiramideEtaria(
                $territorioActivo->poblacion_total,
                $territorioActivo->padron_electoral
            );
            $estrategia = $this->demographicService->recomendarEstrategiaDigital(
                $piramide,
                (float)($territorioActivo->poblacion_urbana_pct ?: 70)
            );
        }

        // Resumen Provincial / Macro
        $padronProvincialTotal = $departamentos->sum('padron_electoral') ?: ($provincia?->padron_electoral ?: 610000);
        $poblacionProvincialTotal = $departamentos->sum('poblacion_total') ?: ($provincia?->poblacion_total ?: 820000);

        return Inertia::render('Territorios/Index', [
            'nivel_politico' => $nivelPolitico,
            'nivel_label' => $workspace->nivel_politico_label,
            'es_gobernador' => $esGobernador,
            'provincia' => $provincia,
            'departamentos' => $departamentos->map(function ($d) {
                $candidato = $d->candidatoPropio;
                $totalSeguidores = $candidato ? $candidato->perfilesSociales->where('esta_activo', true)->sum('seguidores_actuales') : 0;
                $coberturaPadronPct = ($d->padron_electoral > 0 && $totalSeguidores > 0)
                    ? round(($totalSeguidores / $d->padron_electoral) * 100, 1)
                    : 0;

                return [
                    'id' => $d->id,
                    'nombre' => $d->nombre,
                    'tipo' => $d->tipo,
                    'codigo_indec' => $d->codigo_indec,
                    'latitud' => $d->latitud,
                    'longitud' => $d->longitud,
                    'poblacion_total' => (int)$d->poblacion_total,
                    'padron_electoral' => (int)$d->padron_electoral,
                    'poblacion_urbana_pct' => (float)$d->poblacion_urbana_pct,
                    'poblacion_rural_pct' => (float)$d->poblacion_rural_pct,
                    'hogares_nbi_pct' => (float)$d->hogares_nbi_pct,
                    'candidato_propio' => $candidato ? [
                        'id' => $candidato->id,
                        'nombre_completo' => $candidato->nombre_completo,
                        'cargo_aspirado' => $candidato->cargo_aspirado,
                        'avatar_url' => $candidato->avatar_url,
                        'total_seguidores' => $totalSeguidores,
                        'cobertura_padron_pct' => $coberturaPadronPct,
                    ] : null,
                    'total_candidatos' => $d->candidatos->count(),
                ];
            }),
            'territorio_activo' => $territorioActivo ? [
                'id' => $territorioActivo->id,
                'nombre' => $territorioActivo->nombre,
                'tipo' => $territorioActivo->tipo,
                'codigo_indec' => $territorioActivo->codigo_indec,
                'latitud' => $territorioActivo->latitud,
                'longitud' => $territorioActivo->longitud,
                'poblacion_total' => (int)$territorioActivo->poblacion_total,
                'padron_electoral' => (int)$territorioActivo->padron_electoral,
                'poblacion_urbana_pct' => (float)$territorioActivo->poblacion_urbana_pct,
                'poblacion_rural_pct' => (float)$territorioActivo->poblacion_rural_pct,
                'hogares_nbi_pct' => (float)$territorioActivo->hogares_nbi_pct,
                'piramide' => $piramide,
                'estrategia' => $estrategia,
                'candidato_propio' => $territorioActivo->candidatoPropio ? [
                    'id' => $territorioActivo->candidatoPropio->id,
                    'nombre_completo' => $territorioActivo->candidatoPropio->nombre_completo,
                    'cargo_aspirado' => $territorioActivo->candidatoPropio->cargo_aspirado,
                    'avatar_url' => $territorioActivo->candidatoPropio->avatar_url,
                ] : null,
            ] : null,
            'provincias' => $this->demographicService->getProvinciasArgentinas(),
            'metricas_macro' => [
                'padron_total_provincial' => $padronProvincialTotal,
                'poblacion_total_provincial' => $poblacionProvincialTotal,
                'total_departamentos' => $departamentos->count(),
                'departamentos_con_candidato' => $departamentos->filter(fn($d) => $d->candidatoPropio !== null)->count(),
            ],
        ]);
    }

    /**
     * Autodetectar datos demográficos y coordenadas con 1 clic (Georef / INDEC).
     */
    public function autoDetect(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $this->demographicService->consultarGeoref(
            $request->input('nombre'),
            $request->input('provincia', 'San Juan')
        );

        if (!empty($data['poblacion_total']) && !empty($data['padron_electoral'])) {
            $data['piramide'] = $this->demographicService->generarPiramideEtaria(
                (int)$data['poblacion_total'],
                (int)$data['padron_electoral']
            );
            $data['estrategia'] = $this->demographicService->recomendarEstrategiaDigital(
                $data['piramide'],
                (float)($data['poblacion_urbana_pct'] ?? 70)
            );
        }

        return response()->json($data);
    }

    /**
     * Guardar nuevo territorio en el workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'in:municipio,departamento,provincia,seccion'],
            'parent_id' => ['nullable', 'exists:territorios,id'],
            'codigo_indec' => ['nullable', 'string', 'max:50'],
            'poblacion_total' => ['nullable', 'integer', 'min:0'],
            'padron_electoral' => ['nullable', 'integer', 'min:0'],
            'poblacion_urbana_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'poblacion_rural_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hogares_nbi_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
        ]);

        $poblacion = (int)($validated['poblacion_total'] ?? 0);
        $padron = (int)($validated['padron_electoral'] ?? 0);
        $piramide = $this->demographicService->generarPiramideEtaria($poblacion, $padron);

        $territorio = Territorio::create([
            'workspace_id' => $workspace->id,
            ...$validated,
            'piramide_etaria' => $piramide,
        ]);

        return redirect()->back()
            ->with('success', "Territorio {$territorio->nombre} registrado exitosamente en {$workspace->nombre}.");
    }

    /**
     * Actualizar territorio existente.
     */
    public function update(Request $request, Territorio $territorio): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'in:municipio,departamento,provincia,seccion'],
            'parent_id' => ['nullable', 'exists:territorios,id'],
            'codigo_indec' => ['nullable', 'string', 'max:50'],
            'poblacion_total' => ['nullable', 'integer', 'min:0'],
            'padron_electoral' => ['nullable', 'integer', 'min:0'],
            'poblacion_urbana_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'poblacion_rural_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hogares_nbi_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
        ]);

        $poblacion = (int)($validated['poblacion_total'] ?? $territorio->poblacion_total);
        $padron = (int)($validated['padron_electoral'] ?? $territorio->padron_electoral);
        $piramide = $this->demographicService->generarPiramideEtaria($poblacion, $padron);

        $territorio->update([
            ...$validated,
            'piramide_etaria' => $piramide,
        ]);

        return redirect()->back()
            ->with('success', "Territorio {$territorio->nombre} y pirámide demográfica actualizados.");
    }
}
