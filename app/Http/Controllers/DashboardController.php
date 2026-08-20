<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\NotaPrensa;
use App\Models\Publicacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Tablero Central enfocado en el Perfil del Cliente / Candidato Propio.
     */
    public function index(Request $request): Response
    {
        $candidatoId = $request->input('candidato_id');

        // Obtener el candidato objetivo: por ID solicitado o el candidato propio por defecto
        $candidato = $candidatoId
            ? Candidato::with(['perfilesSociales', 'territorio', 'cicloCampana'])->find($candidatoId)
            : Candidato::where('es_propio', true)->with(['perfilesSociales', 'territorio', 'cicloCampana'])->first();

        // Fallback si no hay candidato propio marcado
        if (!$candidato) {
            $candidato = Candidato::with(['perfilesSociales', 'territorio', 'cicloCampana'])->first();
        }

        // Listado de todos los candidatos para selector rápido si se desea cambiar
        $todosCandidatos = Candidato::select('id', 'nombre_completo', 'partido_coalicion', 'cargo_aspirado', 'estado_politico', 'color_hex', 'es_propio', 'avatar_url')
            ->get();

        if (!$candidato) {
            return Inertia::render('Dashboard', [
                'candidato' => null,
                'candidatos_lista' => [],
                'stats' => [],
                'redes_desglose' => [],
                'ultimas_publicaciones' => [],
                'ultimas_notas_prensa' => [],
                'benchmark' => null,
            ]);
        }

        // Publicaciones del candidato
        $publicaciones = Publicacion::where('candidato_id', $candidato->id)
            ->with(['perfilSocial', 'ejeTematico'])
            ->latest('fecha_publicacion')
            ->get();

        // Métricas directas del Perfil del Cliente
        $totalSeguidores = $candidato->perfilesSociales->sum('seguidores_actuales');
        $totalVistas = $publicaciones->sum('total_vistas');
        $totalLikes = $publicaciones->sum('total_likes');
        $totalComentarios = $publicaciones->sum('total_comentarios');
        $totalCompartidos = $publicaciones->sum('total_compartidos');
        $totalPauta = $publicaciones->sum('monto_invertido_pauta');
        $totalPosts = $publicaciones->count();

        $interaccionesTotales = $totalLikes + $totalComentarios + $totalCompartidos;
        $engagementRate = $totalVistas > 0
            ? round(($interaccionesTotales / $totalVistas) * 100, 1)
            : ($totalSeguidores > 0 ? round(($interaccionesTotales / $totalSeguidores) * 100, 1) : 0);

        $humorPromedio = $publicaciones->whereNotNull('termometro_humor_social')->avg('termometro_humor_social');
        $humorPromedioFormateado = $humorPromedio ? number_format($humorPromedio, 1) : '4.5';

        // Ratio de Penetración Territorial sobre el Padrón
        $padronElectoral = $candidato->territorio?->padron_electoral ?? 0;
        $ratioPenetracion = $padronElectoral > 0
            ? round(($totalSeguidores / $padronElectoral) * 100, 1)
            : 0;

        // Desglose por Red Social del Candidato
        $redesDesglose = $candidato->perfilesSociales->map(function ($perfil) use ($publicaciones) {
            $postsRed = $publicaciones->where('perfil_social_id', $perfil->id);
            return [
                'id' => $perfil->id,
                'plataforma' => $perfil->plataforma,
                'handle_usuario' => $perfil->handle_usuario,
                'seguidores' => $perfil->seguidores_actuales,
                'publicaciones_count' => $postsRed->count(),
                'vistas_acumuladas' => $postsRed->sum('total_vistas'),
                'likes_acumulados' => $postsRed->sum('total_likes'),
                'comentarios_acumulados' => $postsRed->sum('total_comentarios'),
            ];
        });

        // Últimas 5 publicaciones del Cliente
        $ultimasPublicaciones = $publicaciones->take(5)->map(function ($p) use ($candidato) {
            return [
                'id' => $p->id,
                'candidato' => [
                    'id' => $candidato->id,
                    'nombre_completo' => $candidato->nombre_completo,
                    'avatar_url' => $candidato->avatar_url,
                    'estado_politico' => $candidato->estado_politico,
                    'color_hex' => $candidato->color_hex,
                    'es_propio' => $candidato->es_propio,
                ],
                'perfil_social' => [
                    'plataforma' => $p->perfilSocial?->plataforma ?? 'instagram',
                    'handle_usuario' => $p->perfilSocial?->handle_usuario ?? '',
                ],
                'plataforma' => $p->perfilSocial?->plataforma ?? 'instagram',
                'fecha_relativa' => $p->fecha_publicacion ? $p->fecha_publicacion->diffForHumans() : 'Reciente',
                'fecha_publicacion' => $p->fecha_publicacion ? $p->fecha_publicacion->format('d/m/Y H:i') : '',
                'tipo_formato' => $p->tipo_formato,
                'tipo_pauta' => $p->tipo_pauta,
                'monto_invertido_pauta' => $p->monto_invertido_pauta,
                'contenido_resumen' => $p->contenido_resumen,
                'total_likes' => $p->total_likes,
                'total_vistas' => $p->total_vistas,
                'total_comentarios' => $p->total_comentarios,
                'total_compartidos' => $p->total_compartidos,
                'termometro_humor_social' => $p->termometro_humor_social,
                'eje_tematico' => $p->ejeTematico ? [
                    'nombre' => $p->ejeTematico->nombre,
                    'color_badge' => $p->ejeTematico->color_badge,
                ] : null,
                'figuras_acompanantes' => $p->figuras_acompanantes ?? [],
                'comentarios_destacados' => $p->comentarios_destacados ?? [],
            ];
        });

        // Notas de prensa donde se menciona al candidato
        $notasPrensa = NotaPrensa::where('candidato_id', $candidato->id)
            ->with('medioPrensa')
            ->latest('fecha_publicacion')
            ->take(4)
            ->get()
            ->map(function ($nota) {
                return [
                    'id' => $nota->id,
                    'medio_nombre' => $nota->medioPrensa?->nombre ?? 'Medio Digital',
                    'medio_tipo' => $nota->medioPrensa?->tipo_medio ?? 'digital',
                    'titulo' => $nota->titulo,
                    'url_nota' => $nota->url_nota,
                    'tono_mencion' => $nota->tono_mencion,
                    'es_portada' => $nota->es_tapa_o_principal,
                    'fecha' => $nota->fecha_publicacion ? $nota->fecha_publicacion->format('d/m/Y') : '',
                    'resumen' => $nota->resumen,
                ];
            });

        // Benchmark contextual resumido (sin ruido: solo 1 métrica de posición competitiva)
        $todasPublicaciones = Publicacion::all();
        $totalVistasEcosistema = $todasPublicaciones->sum('total_vistas');
        $shareOfVoicePropio = $totalVistasEcosistema > 0
            ? round(($totalVistas / $totalVistasEcosistema) * 100, 1)
            : 0;

        return Inertia::render('Dashboard', [
            'candidato' => [
                'id' => $candidato->id,
                'nombre_completo' => $candidato->nombre_completo,
                'partido_coalicion' => $candidato->partido_coalicion,
                'cargo_aspirado' => $candidato->cargo_aspirado,
                'estado_politico' => $candidato->estado_politico,
                'color_hex' => $candidato->color_hex,
                'es_propio' => $candidato->es_propio,
                'avatar_url' => $candidato->avatar_url,
                'bio_resumen' => $candidato->bio_resumen,
                'territorio_nombre' => $candidato->territorio?->nombre ?? 'Territorio General',
                'padron_electoral' => $padronElectoral,
                'ciclo_nombre' => $candidato->cicloCampana?->nombre ?? 'Campaña 2025',
            ],
            'candidatos_lista' => $todosCandidatos,
            'stats' => [
                'total_seguidores' => number_format($totalSeguidores),
                'total_seguidores_raw' => $totalSeguidores,
                'total_vistas' => number_format($totalVistas),
                'total_vistas_raw' => $totalVistas,
                'total_publicaciones' => $totalPosts,
                'engagement_promedio' => $engagementRate . '%',
                'inversion_pauta_total' => $totalPauta,
                'humor_social_promedio' => $humorPromedioFormateado,
                'ratio_penetracion' => $ratioPenetracion . '%',
                'share_of_voice' => $shareOfVoicePropio . '%',
            ],
            'redes_desglose' => $redesDesglose,
            'ultimas_publicaciones' => $ultimasPublicaciones,
            'ultimas_notas_prensa' => $notasPrensa,
        ]);
    }
}
