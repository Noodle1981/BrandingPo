<?php

namespace App\Http\Controllers;

use App\Helpers\SecurityHelper;
use App\Helpers\WorkspaceHelper;
use App\Models\Candidato;
use App\Models\MedioPrensa;
use App\Models\NotaPrensa;
use App\Services\GenericMediaScraperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MediosController extends Controller
{
    public function __construct(
        protected GenericMediaScraperService $mediaScraper
    ) {}

    /**
     * Observatorio de Medios Tradicionales & Clipping Informativo del Workspace Activo.
     */
    public function index(Request $request): Response
    {
        $workspace = WorkspaceHelper::activo($request);
        $candidatoId = $request->input('candidato_id');
        $tono = $request->input('tono');
        $medioId = $request->input('medio_id');
        $origenTipo = $request->input('origen_tipo');
        $pestanaActiva = $request->input('pestana', 'directorio');

        // 1. Consulta de Medios del Workspace con conteos
        $medios = MedioPrensa::where('workspace_id', $workspace->id)
            ->withCount([
                'notasPrensa',
                'notasPrensa as notas_web_count' => fn ($q) => $q->where('origen_tipo', 'web'),
                'notasPrensa as notas_fb_count' => fn ($q) => $q->where('origen_tipo', 'facebook'),
            ])
            ->orderBy('nombre')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'nombre' => $m->nombre,
                    'tipo_medio' => $m->tipo_medio,
                    'url_sitio' => $m->url_sitio,
                    'url_facebook' => $m->url_facebook,
                    'avatar_url' => $m->avatar_url,
                    'feed_rss_url' => $m->feed_rss_url,
                    'alcance_tipo' => $m->alcance_tipo,
                    'sesgo_editorial_estimado' => $m->sesgo_editorial_estimado,
                    'notas_prensa_count' => $m->notas_prensa_count,
                    'notas_web_count' => $m->notas_web_count,
                    'notas_fb_count' => $m->notas_fb_count,
                    'ultima_sincronizacion_at' => $m->ultima_sincronizacion_at?->format('d/m/Y H:i'),
                    'ultima_sincronizacion_diff' => $m->ultima_sincronizacion_at?->diffForHumans(),
                ];
            });

        // 2. Consulta de Candidatos para filtros y asignación de menciones
        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->with('perfilesSociales')
            ->orderByDesc('es_propio')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'es_propio', 'avatar_url', 'color_hex', 'cargo_aspirado']);

        // 3. Consulta de Notas de Prensa con filtros aplicados
        $query = NotaPrensa::where('workspace_id', $workspace->id)
            ->with(['medioPrensa', 'candidato'])
            ->orderByDesc('fecha_publicacion');

        if ($candidatoId) {
            $query->where('candidato_id', $candidatoId);
        }

        if ($tono) {
            $query->where('tono_mencion', $tono);
        }

        if ($medioId) {
            $query->where('medio_prensa_id', $medioId);
        }

        if ($origenTipo) {
            $query->where('origen_tipo', $origenTipo);
        }

        $notas = $query->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'titulo' => $n->titulo,
                'resumen' => $n->resumen,
                'url_nota' => $n->url_nota,
                'fecha' => $n->fecha_publicacion?->format('d/m/Y'),
                'fecha_raw' => $n->fecha_publicacion?->format('Y-m-d'),
                'origen_tipo' => $n->origen_tipo ?? 'web',
                'tipo_mencion' => $n->tipo_mencion ?? 'titular',
                'tono_mencion' => $n->tono_mencion,
                'puntuacion_sentimiento' => (float) ($n->puntuacion_sentimiento ?? 0),
                'es_tapa_o_principal' => (bool) $n->es_tapa_o_principal,
                'interacciones' => (int) $n->interacciones_en_redes_del_medio,
                'reacciones_desglose' => $n->reacciones_desglose,
                'respuesta_replica' => $n->respuesta_replica_candidato,
                'medio' => [
                    'id' => $n->medioPrensa?->id,
                    'nombre' => $n->medioPrensa?->nombre,
                    'tipo_medio' => $n->medioPrensa?->tipo_medio,
                    'sesgo' => $n->medioPrensa?->sesgo_editorial_estimado,
                    'avatar_url' => $n->medioPrensa?->avatar_url,
                ],
                'candidato' => [
                    'id' => $n->candidato?->id,
                    'nombre_completo' => $n->candidato?->nombre_completo,
                    'es_propio' => (bool) $n->candidato?->es_propio,
                    'avatar_url' => $n->candidato?->avatar_url,
                    'color_hex' => $n->candidato?->color_hex ?? '#06b6d4',
                ],
            ];
        });

        // 4. Estadísticas Globales del Observatorio
        $todasNotas = NotaPrensa::where('workspace_id', $workspace->id)->get();
        $totalFavorables = $todasNotas->where('tono_mencion', 'favorable')->count();
        $totalNeutras = $todasNotas->where('tono_mencion', 'neutro')->count();
        $totalCriticas = $todasNotas->where('tono_mencion', 'critico')->count();
        $totalWeb = $todasNotas->where('origen_tipo', 'web')->count();
        $totalFacebook = $todasNotas->where('origen_tipo', 'facebook')->count();

        // 5. Agregación de Sentimientos en Facebook
        $notasFb = $todasNotas->where('origen_tipo', 'facebook');
        $sumLikes = 0;
        $sumLove = 0;
        $sumHaha = 0;
        $sumWow = 0;
        $sumSad = 0;
        $sumAngry = 0;

        foreach ($notasFb as $n) {
            $rd = $n->reacciones_desglose;
            if (is_array($rd)) {
                $sumLikes += (int) ($rd['likes'] ?? 0);
                $sumLove += (int) ($rd['love'] ?? 0);
                $sumHaha += (int) ($rd['haha'] ?? 0);
                $sumWow += (int) ($rd['wow'] ?? 0);
                $sumSad += (int) ($rd['sad'] ?? 0);
                $sumAngry += (int) ($rd['angry'] ?? 0);
            }
        }

        $totalReaccionesFb = $sumLikes + $sumLove + $sumHaha + $sumWow + $sumSad + $sumAngry;
        $porcentajeEnojoFb = $totalReaccionesFb > 0 ? round(($sumAngry / $totalReaccionesFb) * 100, 1) : 0;
        $alertaCrisisFb = $porcentajeEnojoFb >= 15.0;

        // 6. Umbral Operativo (Mínimo 5 Medios)
        $totalMediosRegistrados = $medios->count();
        $cumpleUmbral = $totalMediosRegistrados >= 5;
        $progresoUmbral = min(100, round(($totalMediosRegistrados / 5) * 100));

        return Inertia::render('Medios/Index', [
            'medios' => $medios,
            'notas' => $notas,
            'candidatos' => $candidatos,
            'pestana_activa' => $pestanaActiva,
            'filtros' => [
                'candidato_id' => $candidatoId,
                'tono' => $tono,
                'medio_id' => $medioId,
                'origen_tipo' => $origenTipo,
            ],
            'umbral' => [
                'total_medios' => $totalMediosRegistrados,
                'minimo_requerido' => 5,
                'cumple_umbral' => $cumpleUmbral,
                'porcentaje_progreso' => $progresoUmbral,
                'faltantes' => max(0, 5 - $totalMediosRegistrados),
            ],
            'resumen_tonos' => [
                'favorables' => $totalFavorables,
                'neutras' => $totalNeutras,
                'criticas' => $totalCriticas,
                'total' => $todasNotas->count(),
                'web_count' => $totalWeb,
                'facebook_count' => $totalFacebook,
            ],
            'sentimientos_facebook' => [
                'total_reacciones' => $totalReaccionesFb,
                'likes' => $sumLikes,
                'love' => $sumLove,
                'haha' => $sumHaha,
                'wow' => $sumWow,
                'sad' => $sumSad,
                'angry' => $sumAngry,
                'porcentaje_enojo' => $porcentajeEnojoFb,
                'alerta_crisis' => $alertaCrisisFb,
            ],
        ]);
    }

    /**
     * Registrar un nuevo medio de prensa en el workspace activo.
     */
    public function storeMedio(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_medio' => ['required', 'in:digital,impreso,radio,tv'],
            'url_sitio' => ['nullable', 'url', 'max:500'],
            'url_facebook' => ['nullable', 'url', 'max:500'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
            'feed_rss_url' => ['nullable', 'url', 'max:500'],
            'alcance_tipo' => ['required', 'in:local,provincial,nacional'],
            'sesgo_editorial_estimado' => ['required', 'in:oficialista,independiente,opositor'],
        ]);

        if (! empty($validated['url_sitio']) && ! SecurityHelper::esUrlSegura($validated['url_sitio'])) {
            return back()->withErrors(['url_sitio' => 'La URL del portal web no es válida o apunta a un host restringido.']);
        }

        if (! empty($validated['url_facebook']) && ! SecurityHelper::esUrlSegura($validated['url_facebook'])) {
            return back()->withErrors(['url_facebook' => 'La URL de Facebook no es válida o apunta a un host restringido.']);
        }

        MedioPrensa::create(array_merge($validated, [
            'workspace_id' => $workspace->id,
            'territorio_id' => $workspace->territorios()->first()?->id,
        ]));

        return redirect()->route('medios.index')
            ->with('success', "Medio de prensa '{$validated['nombre']}' registrado exitosamente.");
    }

    /**
     * Actualizar datos de un medio de prensa.
     */
    public function updateMedio(Request $request, MedioPrensa $medio): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($medio, $workspace);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_medio' => ['required', 'in:digital,impreso,radio,tv'],
            'url_sitio' => ['nullable', 'url', 'max:500'],
            'url_facebook' => ['nullable', 'url', 'max:500'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
            'feed_rss_url' => ['nullable', 'url', 'max:500'],
            'alcance_tipo' => ['required', 'in:local,provincial,nacional'],
            'sesgo_editorial_estimado' => ['required', 'in:oficialista,independiente,opositor'],
        ]);

        if (! empty($validated['url_sitio']) && ! SecurityHelper::esUrlSegura($validated['url_sitio'])) {
            return back()->withErrors(['url_sitio' => 'La URL del portal web no es válida o apunta a un host restringido.']);
        }

        if (! empty($validated['url_facebook']) && ! SecurityHelper::esUrlSegura($validated['url_facebook'])) {
            return back()->withErrors(['url_facebook' => 'La URL de Facebook no es válida o apunta a un host restringido.']);
        }

        $medio->update($validated);

        return redirect()->route('medios.index')
            ->with('success', "Medio de prensa '{$medio->nombre}' actualizado.");
    }

    /**
     * Eliminar medio de prensa y sus notas asociadas.
     */
    public function destroyMedio(Request $request, MedioPrensa $medio): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($medio, $workspace);

        $nombre = $medio->nombre;
        $medio->delete();

        return redirect()->route('medios.index')
            ->with('success', "Medio '{$nombre}' eliminado del observatorio.");
    }

    /**
     * Autodescubrir feed RSS y avatar de Facebook en 1 clic.
     */
    public function detectarFuentes(Request $request): JsonResponse
    {
        $request->validate([
            'url_sitio' => ['nullable', 'url', 'max:500'],
            'url_facebook' => ['nullable', 'url', 'max:500'],
        ]);

        $urlSitio = $request->input('url_sitio');
        $urlFacebook = $request->input('url_facebook');

        if (empty($urlSitio) && empty($urlFacebook)) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Debes ingresar al menos la URL del portal web o de Facebook para analizar.',
            ], 422);
        }

        $resultado = $this->mediaScraper->autodescubrirFuentes($urlSitio ?: '', $urlFacebook);

        return response()->json($resultado);
    }

    /**
     * Sincronizar un medio individualmente.
     */
    public function sincronizarMedio(Request $request, MedioPrensa $medio): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($medio, $workspace);

        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->with('perfilesSociales')
            ->get();

        $stats = $this->mediaScraper->sincronizarMedio($medio, $candidatos);

        $msg = "Sincronización de '{$medio->nombre}' completada: {$stats['notas_analizadas']} notas analizadas, {$stats['menciones_detectadas']} menciones encontradas ({$stats['nuevas_notas']} nuevas).";

        return redirect()->route('medios.index')
            ->with('success', $msg);
    }

    /**
     * Sincronizar todos los medios registrados en el workspace activo.
     */
    public function sincronizarTodos(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $medios = MedioPrensa::where('workspace_id', $workspace->id)->get();
        if ($medios->isEmpty()) {
            return redirect()->route('medios.index')
                ->with('error', 'No hay medios registrados para sincronizar.');
        }

        $candidatos = Candidato::where('workspace_id', $workspace->id)
            ->with('perfilesSociales')
            ->get();

        $totalAnalizadas = 0;
        $totalMenciones = 0;
        $totalNuevas = 0;

        foreach ($medios as $m) {
            $stats = $this->mediaScraper->sincronizarMedio($m, $candidatos);
            $totalAnalizadas += $stats['notas_analizadas'];
            $totalMenciones += $stats['menciones_detectadas'];
            $totalNuevas += $stats['nuevas_notas'];
        }

        $msg = "Sincronización masiva finalizada ({$medios->count()} medios): {$totalAnalizadas} notas revisadas, {$totalMenciones} menciones detectadas ({$totalNuevas} nuevas registradas).";

        return redirect()->route('medios.index')
            ->with('success', $msg);
    }

    /**
     * Registrar una nueva nota manual en el clipping.
     */
    public function storeNota(Request $request): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);

        $validated = $request->validate([
            'medio_prensa_id' => ['required', Rule::exists('medios_prensa', 'id')->where('workspace_id', $workspace->id)],
            'candidato_id' => ['nullable', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
            'origen_tipo' => ['nullable', 'in:web,facebook'],
            'tipo_mencion' => ['nullable', 'in:etiqueta_directa,titular,cuerpo'],
            'fecha_publicacion' => ['required', 'date'],
            'titulo' => ['required', 'string', 'max:500'],
            'resumen' => ['nullable', 'string'],
            'url_nota' => ['nullable', 'url', 'max:500'],
            'tono_mencion' => ['required', 'in:favorable,neutro,critico'],
            'puntuacion_sentimiento' => ['nullable', 'numeric', 'between:-1,1'],
            'es_tapa_o_principal' => ['boolean'],
            'interacciones_en_redes_del_medio' => ['nullable', 'integer', 'min:0'],
            'respuesta_replica_candidato' => ['nullable', 'string'],
        ]);

        NotaPrensa::create([
            'workspace_id' => $workspace->id,
            'medio_prensa_id' => $validated['medio_prensa_id'],
            'candidato_id' => $validated['candidato_id'] ?? null,
            'origen_tipo' => $validated['origen_tipo'] ?? 'web',
            'tipo_mencion' => $validated['tipo_mencion'] ?? 'titular',
            'fecha_publicacion' => $validated['fecha_publicacion'],
            'titulo' => $validated['titulo'],
            'resumen' => $validated['resumen'] ?? null,
            'url_nota' => $validated['url_nota'] ?? null,
            'tono_mencion' => $validated['tono_mencion'],
            'puntuacion_sentimiento' => (float) ($validated['puntuacion_sentimiento'] ?? 0),
            'es_tapa_o_principal' => $request->boolean('es_tapa_o_principal'),
            'interacciones_en_redes_del_medio' => (int) ($validated['interacciones_en_redes_del_medio'] ?? 0),
            'respuesta_replica_candidato' => $validated['respuesta_replica_candidato'] ?? null,
        ]);

        return redirect()->route('medios.index')
            ->with('success', 'Nota de prensa agregada al clipping exitosamente.');
    }

    /**
     * Actualizar una nota existente en el clipping.
     */
    public function updateNota(Request $request, NotaPrensa $nota): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($nota, $workspace);

        $validated = $request->validate([
            'medio_prensa_id' => ['required', Rule::exists('medios_prensa', 'id')->where('workspace_id', $workspace->id)],
            'candidato_id' => ['nullable', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
            'origen_tipo' => ['nullable', 'in:web,facebook'],
            'tipo_mencion' => ['nullable', 'in:etiqueta_directa,titular,cuerpo'],
            'fecha_publicacion' => ['required', 'date'],
            'titulo' => ['required', 'string', 'max:500'],
            'resumen' => ['nullable', 'string'],
            'url_nota' => ['nullable', 'url', 'max:500'],
            'tono_mencion' => ['required', 'in:favorable,neutro,critico'],
            'puntuacion_sentimiento' => ['nullable', 'numeric', 'between:-1,1'],
            'es_tapa_o_principal' => ['boolean'],
            'interacciones_en_redes_del_medio' => ['nullable', 'integer', 'min:0'],
            'respuesta_replica_candidato' => ['nullable', 'string'],
        ]);

        $nota->update(array_merge($validated, [
            'es_tapa_o_principal' => $request->boolean('es_tapa_o_principal'),
            'interacciones_en_redes_del_medio' => (int) ($validated['interacciones_en_redes_del_medio'] ?? 0),
            'puntuacion_sentimiento' => (float) ($validated['puntuacion_sentimiento'] ?? 0),
        ]));

        return redirect()->route('medios.index')
            ->with('success', 'Nota de prensa actualizada.');
    }

    /**
     * Eliminar nota de prensa del clipping.
     */
    public function destroyNota(Request $request, NotaPrensa $nota): RedirectResponse
    {
        $workspace = WorkspaceHelper::activo($request);
        WorkspaceHelper::validarPertenencia($nota, $workspace);

        $nota->delete();

        return redirect()->route('medios.index')
            ->with('success', 'Nota eliminada del clipping.');
    }
}
