<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
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

        $propio = $candidatos->get('Martín Rodríguez');
        $rival = $candidatos->get('Carlos Morales');
        $precandidata = $candidatos->get('Lucía Fernández');
        $electo = $candidatos->get('Esteban Rossi');

        $now = Carbon::now();

        $postsData = [
            // --- 1. MARTÍN RODRÍGUEZ (PROPIO) ---
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
                    'Se nota el avance en los barrios.'
                ]
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'facebook',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 80000,
                'eje' => $ejes->get('seguridad'),
                'fecha' => $now->copy()->subDays(1),
                'contenido' => "Presentamos 30 nuevos móviles de Prevención Barrial y el nuevo Centro de Monitoreo Urbano con Inteligencia Artificial. Cuidar a las familias es nuestra prioridad número uno.",
                'vistas_org' => 45000,
                'vistas_pag' => 115000,
                'likes' => 22400,
                'comentarios' => 520,
                'compartidos' => 1420,
                'humor' => 4,
                'figuras' => ['Ministro de Seguridad'],
                'comentarios_top' => [
                    'Muy necesario para las paradas de colectivo.',
                    'Que patrullen más por la zona sur por favor.'
                ]
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'x_twitter',
                'tipo_formato' => 'Tweet',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('innovacion'),
                'fecha' => $now->copy()->subDays(2),
                'contenido' => "A partir de hoy, el 100% de las habilitaciones comerciales se realizan de forma 100% digital en menos de 48 hs. Desburocratizar es apoyar al que emprende y genera trabajo.",
                'vistas_org' => 34500,
                'vistas_pag' => 0,
                'likes' => 3200,
                'comentarios' => 184,
                'compartidos' => 450,
                'humor' => 5,
                'figuras' => [],
                'comentarios_top' => ['Gran medida para los que tenemos pymes.']
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'tiktok',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 30000,
                'eje' => $ejes->get('economia'),
                'fecha' => $now->copy()->subDays(3),
                'contenido' => "Un día en la nueva Escuela Municipal de Oficios Digitales: más de 800 jóvenes capacitándose en programación y diseño.",
                'vistas_org' => 25000,
                'vistas_pag' => 60000,
                'likes' => 18200,
                'comentarios' => 490,
                'compartidos' => 930,
                'humor' => 5,
                'figuras' => ['Influencer Tech Local'],
                'comentarios_top' => ['¿Dónde me anoto para el próximo curso?']
            ],
            [
                'candidato' => $propio,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Carrusel',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('salud'),
                'fecha' => $now->copy()->subDays(4),
                'contenido' => "Inauguración del nuevo Centro de Salud 24hs con guardia pediátrica. La salud pública de calidad más cerca de tu casa.",
                'vistas_org' => 28000,
                'vistas_pag' => 0,
                'likes' => 4100,
                'comentarios' => 95,
                'compartidos' => 190,
                'humor' => 4,
                'figuras' => [],
                'comentarios_top' => ['Felicitaciones a todo el equipo médico.']
            ],

            // --- 2. CARLOS MORALES (RIVAL OPOSITOR) ---
            [
                'candidato' => $rival,
                'plataforma' => 'facebook',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 150000,
                'eje' => $ejes->get('seguridad'),
                'fecha' => $now->copy()->subHours(8),
                'contenido' => "Basta de excusas y relatos: la inseguridad no da tregua en los barrios. Tenemos el plan para recuperar el orden y la tranquilidad que los vecinos exigen.",
                'vistas_org' => 40000,
                'vistas_pag' => 150000,
                'likes' => 14500,
                'comentarios' => 840,
                'compartidos' => 1920,
                'humor' => 3,
                'figuras' => ['Senador Nacional'],
                'comentarios_top' => [
                    'Mano dura con la delincuencia ya.',
                    'Queremos ver propuestas concretas no solo quejas.'
                ]
            ],
            [
                'candidato' => $rival,
                'plataforma' => 'x_twitter',
                'tipo_formato' => 'Tweet',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('economia'),
                'fecha' => $now->copy()->subDays(1),
                'contenido' => "Hilo 🧵 sobre el aumento desmedido de tasas municipales en el último trimestre. El municipio gasta en eventos superfluos mientras ahoga al comerciante.",
                'vistas_org' => 75000,
                'vistas_pag' => 0,
                'likes' => 6400,
                'comentarios' => 920,
                'compartidos' => 1800,
                'humor' => 2,
                'figuras' => [],
                'comentarios_top' => ['Totalmente de acuerdo, los impuestos están altísimos.']
            ],
            [
                'candidato' => $rival,
                'plataforma' => 'tiktok',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 50000,
                'eje' => $ejes->get('obras'),
                'fecha' => $now->copy()->subDays(3),
                'contenido' => "¿Dónde están las cloacas prometidas? Fuimos al Barrio Los Olivos a mostrar la realidad que el municipio no quiere ver.",
                'vistas_org' => 20000,
                'vistas_pag' => 75000,
                'likes' => 11200,
                'comentarios' => 410,
                'compartidos' => 620,
                'humor' => 3,
                'figuras' => [],
                'comentarios_top' => ['Es verdad, ahí siempre se inunda.']
            ],
            [
                'candidato' => $rival,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Foto',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('innovacion'),
                'fecha' => $now->copy()->subDays(5),
                'contenido' => "Reunión de trabajo con cámaras comerciales y pymes. Escuchar a los que producen es el primer paso para gobernar bien.",
                'vistas_org' => 18000,
                'vistas_pag' => 0,
                'likes' => 2100,
                'comentarios' => 80,
                'compartidos' => 60,
                'humor' => 4,
                'figuras' => ['Pdte. Cámara Comercio'],
                'comentarios_top' => ['Fuerza Carlos!']
            ],

            // --- 3. LUCÍA FERNÁNDEZ (PRECANDIDATA EMERGENTE) ---
            [
                'candidato' => $precandidata,
                'plataforma' => 'tiktok',
                'tipo_formato' => 'Video',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('innovacion'),
                'fecha' => $now->copy()->subHours(12),
                'contenido' => "5 cosas que podríamos solucionar en el transporte público con la mitad del presupuesto de publicidad oficial 🚍🚴‍♀️",
                'vistas_org' => 180000,
                'vistas_pag' => 0,
                'likes' => 38000,
                'comentarios' => 1200,
                'compartidos' => 4500,
                'humor' => 5,
                'figuras' => [],
                'comentarios_top' => [
                    'La única que habla de las frecuencias de los colectivos!',
                    'Muy buena data, ojalá se haga.'
                ]
            ],
            [
                'candidato' => $precandidata,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Reel',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 25000,
                'eje' => $ejes->get('economia'),
                'fecha' => $now->copy()->subDays(2),
                'contenido' => "Propuesta de Ley de Primer Empleo Joven y créditos semilla para universitarios y técnicos de la ciudad.",
                'vistas_org' => 22000,
                'vistas_pag' => 40000,
                'likes' => 9400,
                'comentarios' => 280,
                'compartidos' => 540,
                'humor' => 4,
                'figuras' => ['Centro de Estudiantes'],
                'comentarios_top' => ['Hacen falta más oportunidades para los recién egresados.']
            ],
            [
                'candidato' => $precandidata,
                'plataforma' => 'x_twitter',
                'tipo_formato' => 'Tweet',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('salud'),
                'fecha' => $now->copy()->subDays(4),
                'contenido' => "Presentamos en el Concejo Deliberante el proyecto de Salud Mental Comunitaria y centros de escucha barrial. La salud mental no puede esperar.",
                'vistas_org' => 16000,
                'vistas_pag' => 0,
                'likes' => 1800,
                'comentarios' => 64,
                'compartidos' => 220,
                'humor' => 5,
                'figuras' => [],
                'comentarios_top' => ['Excelente iniciativa Lucía.']
            ],

            // --- 4. ESTEBAN ROSSI (INTENDENTE ELECTO) ---
            [
                'candidato' => $electo,
                'plataforma' => 'facebook',
                'tipo_formato' => 'Foto',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('innovacion'),
                'fecha' => $now->copy()->subHours(18),
                'contenido' => "Avanzamos con los equipos técnicos en la transición de gobierno. El 10 de diciembre asumimos el compromiso de transformar la ciudad con diálogo y gestión.",
                'vistas_org' => 45000,
                'vistas_pag' => 0,
                'likes' => 8200,
                'comentarios' => 410,
                'compartidos' => 380,
                'humor' => 5,
                'figuras' => ['Equipo de Transición'],
                'comentarios_top' => ['Éxitos en la nueva gestión intendente!']
            ],
            [
                'candidato' => $electo,
                'plataforma' => 'instagram',
                'tipo_formato' => 'Carrusel',
                'tipo_pauta' => 'pauta_paga',
                'monto' => 40000,
                'eje' => $ejes->get('obras'),
                'fecha' => $now->copy()->subDays(2),
                'contenido' => "Plan de Obras Prioritarias 2025: articulación regional metropolitana para resolver el tratamiento de residuos y conectividad vial.",
                'vistas_org' => 28000,
                'vistas_pag' => 50000,
                'likes' => 10500,
                'comentarios' => 310,
                'compartidos' => 420,
                'humor' => 4,
                'figuras' => ['Intendentes del Gran Córdoba'],
                'comentarios_top' => ['Importantísimo trabajar en conjunto con las ciudades vecinas.']
            ],
            [
                'candidato' => $electo,
                'plataforma' => 'linkedin',
                'tipo_formato' => 'Articulo',
                'tipo_pauta' => 'organico',
                'monto' => 0,
                'eje' => $ejes->get('economia'),
                'fecha' => $now->copy()->subDays(6),
                'contenido' => "Desafíos de la gobernanza metropolitana y atracción de inversiones en el nuevo ciclo político regional.",
                'vistas_org' => 12000,
                'vistas_pag' => 0,
                'likes' => 1400,
                'comentarios' => 90,
                'compartidos' => 160,
                'humor' => 5,
                'figuras' => [],
                'comentarios_top' => ['Gran visión de articulación público-privada.']
            ],
        ];

        foreach ($postsData as $data) {
            $candidato = $data['candidato'];
            if (! $candidato) continue;

            $perfil = $candidato->perfilesSociales->firstWhere('plataforma', $data['plataforma'])
                ?? $candidato->perfilesSociales->first();

            if (! $perfil) continue;

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
                'url_post' => "https://{$data['plataforma']}.com/post/demo-" . rand(1000, 9999),
                'contenido_resumen' => $data['contenido'],
                'total_vistas' => $totalVistas,
                'total_likes' => $data['likes'],
                'total_comentarios' => $data['comentarios'],
                'total_compartidos' => $data['compartidos'],
                'total_guardados' => (int)($data['likes'] * 0.15),
                'reacciones_detalladas' => [
                    'me_gusta' => (int)($data['likes'] * 0.65),
                    'me_encanta' => (int)($data['likes'] * 0.20),
                    'me_divierte' => (int)($data['likes'] * 0.05),
                    'me_asombra' => (int)($data['likes'] * 0.05),
                    'me_enoja' => (int)($data['likes'] * 0.05),
                ],
                'sentimiento_predominante' => $data['humor'] >= 4 ? 'positivo' : ($data['humor'] === 3 ? 'neutro' : 'negativo'),
                'figuras_acompanantes' => $data['figuras'],
                'comentarios_destacados' => $data['comentarios_top'],
                'termometro_humor_social' => $data['humor'],
            ]);
        }
    }
}
