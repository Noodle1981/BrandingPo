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

        // Creamos publicaciones distribuidas en Julio, Agosto y Septiembre para observar el rendimiento mensual y promedios reales
        $postsData = [
            // ==========================================
            // --- MES 1: JULIO (Inicio de Campaña) ---
            // ==========================================
            [
                'candidato' => $propio,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Carrusel',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('gestion'),
                'fecha' => $now->copy()->subMonths(2)->startOfMonth()->addDays(5)->setHour(11),
                'contenido' => "Lanzamos los foros barriales de participación ciudadana. Cada voz cuenta para diseñar el futuro de nuestra comunidad.\n\n#EscuchaActiva #TuVozImporta",
                'media_url' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 14200,
                'vistas_pag' => 0,
                'likes' => 1850,
                'comentarios' => 145,
                'compartidos' => 210,
                'republicados' => 45,
                'humor' => 4,
                'figuras' => ['Equipo de Campaña'],
                'comentarios_top' => ['Excelente inicio, necesitamos que vengan a Barrio Norte.'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'facebook',
                'tipo_formato' => 'Post',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('salud'),
                'fecha' => $now->copy()->subMonths(2)->startOfMonth()->addDays(14)->setHour(15),
                'contenido' => 'Fortaleciendo la atención primaria: visitamos el dispensario municipal y acordamos la ampliación del horario de farmacia.',
                'media_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 18600,
                'vistas_pag' => 0,
                'likes' => 2400,
                'comentarios' => 190,
                'compartidos' => 310,
                'republicados' => 60,
                'humor' => 4,
                'figuras' => ['Dra. Gómez (Salud)'],
                'comentarios_top' => ['Muy buena noticia para los jubilados de la zona.'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'x_twitter',
                'tipo_formato' => 'Tweet',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('economia'),
                'fecha' => $now->copy()->subMonths(2)->startOfMonth()->addDays(22)->setHour(9),
                'contenido' => 'Reunión de trabajo con la Cámara de Comercio local: acordamos un plan de incentivos fiscales para el empleo joven en la ciudad.',
                'media_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 11500,
                'vistas_pag' => 0,
                'likes' => 920,
                'comentarios' => 85,
                'compartidos' => 140,
                'republicados' => 35,
                'humor' => 5,
                'figuras' => ['Presidente Cámara Comercio'],
                'comentarios_top' => ['Fundamental generar trabajo genuino.'],
            ],

            // ==========================================
            // --- MES 2: AGOSTO (Intensificación de Campaña) ---
            // ==========================================
            [
                'candidato' => $propio,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Reel',
                'tipo_pauta' => 'organico_impulsado',
                'monto' => 35000,
                'eje' => $ejes->get('seguridad'),
                'fecha' => $now->copy()->subMonth()->startOfMonth()->addDays(4)->setHour(19),
                'contenido' => "Plan Integral de Seguridad Barrial: instalamos 25 tótems de auxilio y botón antipánico en corredores escolares y paradas de colectivos.\n\n#SeguridadInteligente #CuidarAlVecino",
                'media_url' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 32000,
                'vistas_pag' => 45000,
                'likes' => 6800,
                'comentarios' => 310,
                'compartidos' => 640,
                'republicados' => 120,
                'humor' => 4,
                'figuras' => ['Comisario Mayor'],
                'comentarios_top' => ['Hacía falta en la esquina de la escuela 12.'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'tiktok',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 25000,
                'eje' => $ejes->get('innovacion'),
                'fecha' => $now->copy()->subMonth()->startOfMonth()->addDays(12)->setHour(14),
                'contenido' => '¿Sabías que podés denunciar baches y luminarias rotas desde WhatsApp en 30 segundos? Mirá cómo funciona el bot municipal.',
                'media_url' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 22000,
                'vistas_pag' => 40000,
                'likes' => 11400,
                'comentarios' => 410,
                'compartidos' => 880,
                'republicados' => 180,
                'humor' => 5,
                'figuras' => ['Equipo de Modernización'],
                'comentarios_top' => ['Lo probé ayer y hoy ya pasaron a arreglar el foco.'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'facebook',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 50000,
                'eje' => $ejes->get('obras'),
                'fecha' => $now->copy()->subMonth()->startOfMonth()->addDays(20)->setHour(17),
                'contenido' => 'Avanzan a paso firme las obras del nuevo Parque Lineal Sur: 3 kilómetros de ciclovías, luminarias sustentables y espacio verde para toda la familia.',
                'media_url' => 'https://images.unsplash.com/photo-1541888946425-d0fbb186156a?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 41000,
                'vistas_pag' => 75000,
                'likes' => 14300,
                'comentarios' => 480,
                'compartidos' => 1120,
                'republicados' => 240,
                'humor' => 5,
                'figuras' => ['Secretario de Obras Públicas'],
                'comentarios_top' => ['Un sueño para los que vivimos en el barrio sur.'],
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'threads',
                'tipo_formato' => 'Post',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('gestion'),
                'fecha' => $now->copy()->subMonth()->startOfMonth()->addDays(26)->setHour(21),
                'contenido' => 'La gestión pública no se hace detrás de un escritorio. Se hace caminando cada cuadra, mirando a los ojos y cumpliendo la palabra empeñada.',
                'media_url' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 12500,
                'vistas_pag' => 0,
                'likes' => 1250,
                'comentarios' => 88,
                'compartidos' => 75,
                'republicados' => 42,
                'humor' => 5,
                'figuras' => [],
                'comentarios_top' => ['Totalmente de acuerdo.'],
            ],

            // ==========================================
            // --- MES 3: SEPTIEMBRE (Mes en Curso / Cierre y Tracción Máxima) ---
            // ==========================================
            [
                'candidato' => $propio,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Reel',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 45000,
                'eje' => $ejes->get('obras'),
                'fecha' => $now->copy()->subHours(4),
                'contenido' => "Recorriendo la nueva etapa de luminarias LED y pavimentación en el Barrio Norte. Más seguridad y mejor calidad de vida para cada vecino de la ciudad.\n\n#ObrasQueTransforman #HacemosCiudad",
                'media_url' => 'https://images.unsplash.com/photo-1541888946425-d0fbb186156a?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 38400,
                'vistas_pag' => 60000,
                'likes' => 12480,
                'comentarios' => 342,
                'compartidos' => 810,
                'republicados' => 195,
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
                'fecha' => $now->copy()->subDays(1)->setHour(12),
                'contenido' => 'Sumamos 40 nuevas cámaras de seguridad con inteligencia artificial conectadas al Centro de Monitoreo Urbano.',
                'media_url' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 55000,
                'vistas_pag' => 120000,
                'likes' => 18900,
                'comentarios' => 512,
                'compartidos' => 1430,
                'republicados' => 310,
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
                'fecha' => $now->copy()->subDays(2)->setHour(10),
                'contenido' => 'A partir de hoy, el 100% de las habilitaciones comerciales se realizan de forma 100% digital en menos de 48 hs. Desburocratizar es apoyar al que emprende y genera trabajo.',
                'media_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 34500,
                'vistas_pag' => 0,
                'likes' => 3200,
                'comentarios' => 184,
                'compartidos' => 450,
                'republicados' => 110,
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
                'fecha' => $now->copy()->subDays(3)->setHour(16),
                'contenido' => 'Un día en la nueva Escuela Municipal de Oficios Digitales: más de 800 jóvenes capacitándose en programación y diseño.',
                'media_url' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 25000,
                'vistas_pag' => 60000,
                'likes' => 18200,
                'comentarios' => 490,
                'compartidos' => 930,
                'republicados' => 280,
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
                'fecha' => $now->copy()->subDays(4)->setHour(18),
                'contenido' => 'Inauguración del nuevo Centro de Salud 24hs con guardia pediátrica. La salud pública de calidad más cerca de tu casa.',
                'media_url' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=800&auto=format&fit=crop&q=80',
                'vistas_org' => 28000,
                'vistas_pag' => 0,
                'likes' => 4100,
                'comentarios' => 95,
                'compartidos' => 190,
                'republicados' => 50,
                'humor' => 4,
                'figuras' => [],
                'comentarios_top' => ['Felicitaciones a todo el equipo médico.'],
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

            $totalVistas = (int) $data['vistas_org'] + (int) $data['vistas_pag'];
            $workspaceId = $candidato->workspace_id ?? \App\Models\Workspace::first()?->id;

            Publicacion::create([
                'workspace_id' => $workspaceId,
                'candidato_id' => $candidato->id,
                'perfil_social_id' => $perfil->id,
                'eje_tematico_id' => $data['eje']?->id,
                'fecha_publicacion' => $data['fecha'],
                'fecha_confirmada' => true,
                'tipo_formato' => $data['tipo_formato'],
                'tipo_pauta' => $data['tipo_pauta'],
                'monto_invertido_pauta' => $data['monto'],
                'vistas_organicas' => $data['vistas_org'],
                'vistas_pagadas' => $data['vistas_pag'],
                'url_post' => "https://{$data['plataforma']}.com/post/demo-".rand(1000, 9999),
                'media_url' => $data['media_url'] ?? null,
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
