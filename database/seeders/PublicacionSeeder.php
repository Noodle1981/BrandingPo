<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\Publicacion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PublicacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $candidatos = Candidato::with('perfilesSociales')->get()->keyBy('nombre_completo');
        $ejes = EjeTematico::all()->keyBy('slug');

        if ($candidatos->isEmpty()) {
            return;
        }

        $propio = $candidatos->firstWhere('es_propio', true) ?? $candidatos->first();

        $now = Carbon::now();

        $postsData = [
            // --- MARTÍN RODRÍGUEZ / FEDERICO SISTERNA (PROPIO) ---
            [
                'candidato' => $propio,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Reel',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 45000,
                'eje' => $ejes->get('obras'),
                'fecha' => $now->copy()->subHours(4),
                'contenido' => "Recorriendo la nueva etapa de luminarias LED y pavimentación en el Barrio Norte. Más seguridad y mejor calidad de vida para cada vecino de la ciudad.\n\n#ObrasQueTransforman #HacemosCiudad",
                'vistas_org' => 38400,
                'vistas_pag' => 60000,
                'likes' => 12480,
                'comentarios' => 342,
                'compartidos' => 810,
                'humor' => 5,
                'figuras' => ['Gobernador Prov.'],
                'comentarios_top' => [
                    '¡Hacía años que esperábamos el asfalto! Excelente trabajo intendente.',
                    'Se nota el avance en los barrios.',
                ],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'facebook',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 80000,
                'eje' => $ejes->get('seguridad'),
                'fecha' => $now->copy()->subDays(1),
                'contenido' => 'Sumamos 40 nuevas cámaras de seguridad con inteligencia artificial conectadas al Centro de Monitoreo Urbano.',
                'vistas_org' => 55000,
                'vistas_pag' => 120000,
                'likes' => 18900,
                'comentarios' => 512,
                'compartidos' => 1430,
                'humor' => 4,
                'figuras' => ['Ministro de Seguridad'],
                'comentarios_top' => [
                    'Muy necesario para la zona comercial.',
                    'Ojalá pongan también en la plaza del barrio sur.',
                ],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'x_twitter',
                'tipo_formato' => 'Tweet',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('innovacion'),
                'fecha' => $now->copy()->subDays(2),
                'contenido' => 'A partir de hoy, el 100% de las habilitaciones comerciales se realizan de forma 100% digital en menos de 48 hs. Desburocratizar es apoyar al que emprende y genera trabajo.',
                'vistas_org' => 34500,
                'vistas_pag' => 0,
                'likes' => 3200,
                'comentarios' => 184,
                'compartidos' => 450,
                'humor' => 5,
                'figuras' => [],
                'comentarios_top' => ['Gran medida para los que tenemos pymes.'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'tiktok',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 30000,
                'eje' => $ejes->get('economia'),
                'fecha' => $now->copy()->subDays(3),
                'contenido' => 'Un día en la nueva Escuela Municipal de Oficios Digitales: más de 800 jóvenes capacitándose en programación y diseño.',
                'vistas_org' => 25000,
                'vistas_pag' => 60000,
                'likes' => 18200,
                'comentarios' => 490,
                'compartidos' => 930,
                'humor' => 5,
                'figuras' => ['Influencer Tech Local'],
                'comentarios_top' => ['¿Dónde me anoto para el próximo curso?'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Carrusel',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('salud'),
                'fecha' => $now->copy()->subDays(4),
                'contenido' => 'Inauguración del nuevo Centro de Salud 24hs con guardia pediátrica. La salud pública de calidad más cerca de tu casa.',
                'vistas_org' => 28000,
                'vistas_pag' => 0,
                'likes' => 4100,
                'comentarios' => 95,
                'compartidos' => 190,
                'humor' => 4,
                'figuras' => [],
                'comentarios_top' => ['Felicitaciones a todo el equipo médico.'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'threads',
                'tipo_formato' => 'Post',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('gestion'),
                'fecha' => $now->copy()->subDays(1),
                'contenido' => 'La gestión municipal moderna requiere escucha constante y respuesta rápida en territorio. Abrimos el debate con los vecinos.',
                'vistas_org' => 15400,
                'vistas_pag' => 0,
                'likes' => 840,
                'comentarios' => 65,
                'compartidos' => 45,
                'republicados' => 28,
                'guardados' => 18,
                'humor' => 5,
                'figuras' => ['Vecinos del centro'],
                'comentarios_top' => ['Excelente iniciativa para estar cerca de la gente.'],
            ],
        ];

        foreach ($postsData as $data) {
            $candidato = $data['candidato'];
            if (! $candidato) {
                continue;
            }

            $perfil = $candidato->perfilesSociales->firstWhere('plataforma', $data['plataforma'])
                ?? $candidato->perfilesSociales->first();

            if (! $perfil) {
                continue;
            }

            $isFb = $data['plataforma'] === 'facebook';
            $reacciones = $isFb ? [
                'me_gusta' => (int) ($data['likes'] * 0.65),
                'me_encanta' => (int) ($data['likes'] * 0.20),
                'me_divierte' => (int) ($data['likes'] * 0.05),
                'me_asombra' => (int) ($data['likes'] * 0.05),
                'me_enoja' => (int) ($data['likes'] * 0.05),
            ] : [
                'me_gusta' => $data['likes'],
                'me_encanta' => 0,
                'me_importa' => 0,
                'me_divierte' => 0,
                'me_asombra' => 0,
                'me_entristece' => 0,
                'me_enoja' => 0,
            ];

            $totalVistas = $data['vistas_org'] + $data['vistas_pag'];

            Publicacion::create([
                'candidato_id' => $candidato->id,
                'perfil_social_id' => $perfil->id,
                'eje_tematico_id' => $data['eje']?->id,
                'fecha_publicacion' => $data['fecha'],
                'tipo_formato' => $data['tipo_formato'],
                'tipo_pauta' => $data['tipo_pauta'],
                'monto_invertido_pauta' => $data['monto'],
                'vistas_organicas' => $data['vistas_org'],
                'vistas_pagadas' => $data['vistas_pag'],
                'url_post' => "https://{$data['plataforma']}.com/post/demo-".rand(1000, 9999),
                'contenido_resumen' => $data['contenido'],
                'total_vistas' => $totalVistas,
                'total_likes' => $data['likes'],
                'total_comentarios' => $data['comentarios'],
                'total_compartidos' => $data['compartidos'],
                'total_republicados' => $data['republicados'] ?? 0,
                'total_guardados' => $data['guardados'] ?? (int) ($data['likes'] * 0.15),
                'reacciones_detalladas' => $reacciones,
                'sentimiento_predominante' => $data['humor'] >= 4 ? 'positivo' : ($data['humor'] === 3 ? 'neutro' : 'negativo'),
                'figuras_acompanantes' => $data['figuras'],
                'comentarios_destacados' => $data['comentarios_top'],
                'termometro_humor_social' => $data['humor'],
                'insights_internos_propios' => [
                    'indice_aprobacion_neta' => $isFb ? 80.0 : 100.0,
                    'ratio_indignacion' => $isFb ? 5.0 : 0.0,
                    'alerta_crisis' => false,
                    'total_reacciones_positivas' => $data['likes'],
                    'total_reacciones_negativas' => 0,
                ],
            ]);
        }
    }
}
