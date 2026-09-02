<?php

namespace Database\Seeders;

use App\Models\AlianzaPolitica;
use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\EventoCalendario;
use App\Models\EventoCrisis;
use App\Models\InformeEjecutivo;
use App\Models\MedioPrensa;
use App\Models\NotaPrensa;
use App\Models\PerfilSocial;
use App\Models\PerfilSocialMetrica;
use App\Models\PresupuestoPartida;
use App\Models\Publicacion;
use App\Models\Territorio;
use App\Models\User;
use App\Models\Workspace;
use App\Services\DemographicIntelligenceService;
use Illuminate\Database\Seeder;

class PoliticaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoService = new DemographicIntelligenceService;

        // ─────────────────────────────────────────────────────────
        // 0. WORKSPACE PRINCIPAL — Campaña Sisterna (Albardón 2025)
        // ─────────────────────────────────────────────────────────
        $workspace = Workspace::updateOrCreate(
            ['slug' => 'sisterna-albardon-2025'],
            [
                'nombre' => 'Campaña Sisterna — Albardón 2025',
                'nivel_politico' => 'intendente',
                'provincia' => 'San Juan',
                'plan' => 'profesional',
                'activo' => true,
            ]
        );

        // Asignar todos los users existentes al workspace
        User::all()->each(function (User $user) use ($workspace) {
            $roleToAssign = in_array($user->role, ['admin', 'consultor', 'visualizador']) ? $user->role : 'consultor';
            $workspace->usuarios()->syncWithoutDetaching([
                $user->id => ['role' => $roleToAssign],
            ]);
            if (! $user->active_workspace_id) {
                $user->update(['active_workspace_id' => $workspace->id]);
            }
        });

        // ─────────────────────────────────────────────────────────
        // 1. TERRITORIO PROVINCIAL (Madre)
        // ─────────────────────────────────────────────────────────
        $provincia = Territorio::updateOrCreate(
            ['nombre' => 'Provincia de San Juan'],
            [
                'workspace_id' => $workspace->id,
                'tipo' => 'provincia',
                'codigo_indec' => '70',
                'latitud' => -30.8653,
                'longitud' => -68.8894,
                'poblacion_total' => 818000,
                'padron_electoral' => 610000,
                'poblacion_urbana_pct' => 84.50,
                'poblacion_rural_pct' => 15.50,
                'hogares_nbi_pct' => 14.20,
            ]
        );
        $provincia->update([
            'piramide_etaria' => $demoService->generarPiramideEtaria(818000, 610000),
        ]);

        // ─────────────────────────────────────────────────────────
        // 2. DEPARTAMENTOS DE SAN JUAN (19 Departamentos)
        // ─────────────────────────────────────────────────────────
        $departamentosData = $demoService->getSanJuanDepartamentosFallback();
        $departamentosCreados = [];

        foreach ($departamentosData as $d) {
            $piramide = $demoService->generarPiramideEtaria($d['poblacion_total'], $d['padron_electoral']);
            $dep = Territorio::updateOrCreate(
                ['nombre' => "Departamento {$d['nombre']}"],
                [
                    'workspace_id' => $workspace->id,
                    'parent_id' => $provincia->id,
                    'tipo' => 'departamento',
                    'codigo_indec' => $d['codigo_indec'],
                    'latitud' => $d['latitud'],
                    'longitud' => $d['longitud'],
                    'poblacion_total' => $d['poblacion_total'],
                    'padron_electoral' => $d['padron_electoral'],
                    'poblacion_urbana_pct' => $d['poblacion_urbana_pct'],
                    'poblacion_rural_pct' => $d['poblacion_rural_pct'],
                    'hogares_nbi_pct' => $d['hogares_nbi_pct'],
                    'piramide_etaria' => $piramide,
                ]
            );
            $departamentosCreados[strtolower($d['nombre'])] = $dep;
        }

        $albardon = $departamentosCreados['albardón'] ?? Territorio::where('workspace_id', $workspace->id)->first();

        // ─────────────────────────────────────────────────────────
        // 3. CICLOS DE CAMPAÑA
        // ─────────────────────────────────────────────────────────
        $ciclo2023 = CicloCampana::updateOrCreate(
            ['anio' => 2023],
            [
                'workspace_id' => $workspace->id,
                'nombre' => 'Campaña Provincial & Municipal 2023',
                'fecha_inicio' => '2023-01-01',
                'fecha_fin' => '2023-12-31',
                'estado' => 'finalizada',
                'es_activo' => false,
            ]
        );

        $ciclo2025 = CicloCampana::updateOrCreate(
            ['anio' => 2025],
            [
                'workspace_id' => $workspace->id,
                'nombre' => 'Elecciones Legislativas & Renovación 2025 / 2027',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'es_activo' => true,
            ]
        );

        // ─────────────────────────────────────────────────────────
        // 4. EJES TEMÁTICOS (5 Pilares Estratégicos & 16 Sub-ejes)
        // ─────────────────────────────────────────────────────────
        $ejes = [
            // 🌿 PILAR 1: Ciudad Moderna y Sostenible
            [
                'pilar_principal' => '1. Ciudad Moderna y Sostenible',
                'nombre' => 'Medio Ambiente y Sustentabilidad',
                'slug' => 'medio-ambiente-y-sustentabilidad',
                'color_badge' => '#10b981',
                'icono' => 'Leaf',
                'orden' => 1,
                'descripcion' => 'Espacios verdes, reciclaje, energías limpias y cuidado ambiental.',
            ],
            [
                'pilar_principal' => '1. Ciudad Moderna y Sostenible',
                'nombre' => 'Obras e Infraestructura',
                'slug' => 'obras-e-infraestructura',
                'color_badge' => '#10b981',
                'icono' => 'HardHat',
                'orden' => 2,
                'descripcion' => 'Pavimentación, cloacas, red de agua, iluminación LED y urbanismo.',
            ],
            [
                'pilar_principal' => '1. Ciudad Moderna y Sostenible',
                'nombre' => 'Movilidad Urbana y Transporte',
                'slug' => 'movilidad-urbana-y-transporte',
                'color_badge' => '#10b981',
                'icono' => 'Bus',
                'orden' => 3,
                'descripcion' => 'Transporte público, ciclovías, conectividad barrial y accesibilidad.',
            ],

            // ⚡ PILAR 2: Desarrollo, Empleo y Futuro
            [
                'pilar_principal' => '2. Desarrollo, Empleo y Futuro',
                'nombre' => 'Producción y Empleo',
                'slug' => 'produccion-y-empleo',
                'color_badge' => '#06b6d4',
                'icono' => 'Briefcase',
                'orden' => 4,
                'descripcion' => 'Polo productivo, apoyo a pymes, primer empleo e industrias locales.',
            ],
            [
                'pilar_principal' => '2. Desarrollo, Empleo y Futuro',
                'nombre' => 'Innovación y Capacitación',
                'slug' => 'innovacion-y-capacitacion',
                'color_badge' => '#06b6d4',
                'icono' => 'Sparkles',
                'orden' => 5,
                'descripcion' => 'Polos tecnológicos, cursos de oficios, economía del conocimiento.',
            ],
            [
                'pilar_principal' => '2. Desarrollo, Empleo y Futuro',
                'nombre' => 'Turismo y Comercio',
                'slug' => 'turismo-y-comercio',
                'color_badge' => '#06b6d4',
                'icono' => 'ShoppingBag',
                'orden' => 6,
                'descripcion' => 'Rutas gastronómicas, hotelería, centros comerciales abiertos y ferias.',
            ],
            [
                'pilar_principal' => '2. Desarrollo, Empleo y Futuro',
                'nombre' => 'Juventud',
                'slug' => 'juventud',
                'color_badge' => '#06b6d4',
                'icono' => 'Zap',
                'orden' => 7,
                'descripcion' => 'Oportunidades para jóvenes, becas, arte urbano y espacios de encuentro.',
            ],

            // 🧡 PILAR 3: Cuidado, Bienestar y Comunidad
            [
                'pilar_principal' => '3. Cuidado, Bienestar y Comunidad',
                'nombre' => 'Salud y Deportes',
                'slug' => 'salud-y-deportes',
                'color_badge' => '#f59e0b',
                'icono' => 'Activity',
                'orden' => 8,
                'descripcion' => 'Salitas 24hs, prevención sanitaria, clubes barriales y torneos.',
            ],
            [
                'pilar_principal' => '3. Cuidado, Bienestar y Comunidad',
                'nombre' => 'Infancia y Adultos Mayores',
                'slug' => 'infancia-y-adultos-mayores',
                'color_badge' => '#f59e0b',
                'icono' => 'Users',
                'orden' => 9,
                'descripcion' => 'Centros de primera infancia, talleres de la tercera edad y contención.',
            ],
            [
                'pilar_principal' => '3. Cuidado, Bienestar y Comunidad',
                'nombre' => 'Género e Inclusión Social',
                'slug' => 'genero-e-inclusion-social',
                'color_badge' => '#f59e0b',
                'icono' => 'HeartHandshake',
                'orden' => 10,
                'descripcion' => 'Políticas de equidad, asistencia a víctimas, discapacidad y derechos.',
            ],
            [
                'pilar_principal' => '3. Cuidado, Bienestar y Comunidad',
                'nombre' => 'Cultura y Eventos Comunitarios',
                'slug' => 'cultura-y-eventos-comunitarios',
                'color_badge' => '#f59e0b',
                'icono' => 'Music',
                'orden' => 11,
                'descripcion' => 'Festivales populares, identidad local, artistas y talleres barriales.',
            ],

            // 🛡️ PILAR 4: Seguridad y Tranquilidad
            [
                'pilar_principal' => '4. Seguridad y Tranquilidad',
                'nombre' => 'Seguridad Ciudadana y Prevención',
                'slug' => 'seguridad-ciudadana-y-prevencion',
                'color_badge' => '#ef4444',
                'icono' => 'ShieldCheck',
                'orden' => 12,
                'descripcion' => 'Patrullaje inteligente, cámaras de videovigilancia, alarmas y centros de monitoreo.',
            ],
            [
                'pilar_principal' => '4. Seguridad y Tranquilidad',
                'nombre' => 'Tránsito y Control Urbano',
                'slug' => 'transito-y-control-urbano',
                'color_badge' => '#ef4444',
                'icono' => 'Car',
                'orden' => 13,
                'descripcion' => 'Seguridad vial, semaforización, controles vehiculares y orden en la vía pública.',
            ],

            // 🔮 PILAR 5: Gobierno Transparente y Eficiente
            [
                'pilar_principal' => '5. Gobierno Transparente y Eficiente',
                'nombre' => 'Atención Ciudadana y Trámites Digitales',
                'slug' => 'atencion-ciudadana-y-tramites-digitales',
                'color_badge' => '#8b5cf6',
                'icono' => 'Smartphone',
                'orden' => 14,
                'descripcion' => 'Municipio digital, reclamos por WhatsApp, simplificación de trámites.',
            ],
            [
                'pilar_principal' => '5. Gobierno Transparente y Eficiente',
                'nombre' => 'Transparencia y Participación Ciudadana',
                'slug' => 'transparencia-y-participacion-ciudadana',
                'color_badge' => '#8b5cf6',
                'icono' => 'Eye',
                'orden' => 15,
                'descripcion' => 'Presupuesto participativo, datos abiertos, auditorías y rendición de cuentas.',
            ],
            [
                'pilar_principal' => '5. Gobierno Transparente y Eficiente',
                'nombre' => 'Política y Participación',
                'slug' => 'politica-y-participacion',
                'color_badge' => '#8b5cf6',
                'icono' => 'Vote',
                'orden' => 16,
                'descripcion' => 'Consensos democráticos, diálogo institucional y articulación cívica.',
            ],
        ];

        foreach ($ejes as $eje) {
            EjeTematico::updateOrCreate(
                ['slug' => $eje['slug']],
                [
                    'workspace_id' => $workspace->id,
                    ...$eje,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────
        // 5. CANDIDATO PROPIO (Federico Sisterna - Albardón)
        // ─────────────────────────────────────────────────────────
        $propio = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Federico Sisterna',
                'workspace_id' => $workspace->id,
            ],
            [
                'ciclo_campana_id' => $ciclo2025->id,
                'territorio_id' => $albardon->id,
                'partido_coalicion' => 'Ahora Albardón',
                'cargo_aspirado' => 'Candidato a Intendente',
                'estado_politico' => 'candidato',
                'color_hex' => '#06b6d4',
                'es_propio' => true,
                'avatar_url' => 'https://scontent.cdninstagram.com/v/t51.82787-19/541928148_18336738628206464_5488714422900118483_n.jpg?stp=dst-jpg_s100x100_tt6&_nc_cat=108&ccb=7-5&_nc_sid=bf7eb4&efg=eyJ2ZW5jb2RlX3RhZyI6InByb2ZpbGVfcGljLnd3dy4xMDgwLkMzIn0%3D&_nc_ohc=3mQFxO7NnK4Q7kNvwFw4QeW&_nc_oc=Adod8T-HS0B_BkCVPoo2_FImtN4Y0lf0TeMr5jMEhUNep3dnRnYnEXT2wTMikDkzl2M&_nc_zt=24&_nc_ht=scontent.cdninstagram.com&_nc_gid=FnkUXnCqw-YKKl56nsQKXw&_nc_ss=7ba02&oh=00_AQGKUir4NYPBzJIu1gzxWnAOpDQe6WFLCgQanRsIrCthdA&oe=6A8D181F',
                'bio_resumen' => 'Espacio "Ahora Albardón", alternativa ciudadana para transformar el departamento con obras, producción y cercanía vecinal.',
            ]
        );

        $rival = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Carlos Morales',
                'workspace_id' => $workspace->id,
            ],
            [
                'ciclo_campana_id' => $ciclo2025->id,
                'territorio_id' => $albardon->id,
                'partido_coalicion' => 'Frente Opositor',
                'cargo_aspirado' => 'Candidato a Intendente',
                'estado_politico' => 'opositor',
                'color_hex' => '#8b5cf6',
                'es_propio' => false,
                'avatar_url' => 'https://ui-avatars.com/api/?name=Carlos+Morales&background=8b5cf6&color=fff',
                'bio_resumen' => 'Concejal y candidato opositor.',
            ]
        );

        $demografiaInstagram = [
            'fuente_datos' => 'meta_graph_api',
            'genero' => [
                'femenino_pct' => 54.2,
                'masculino_pct' => 45.8,
            ],
            'franjas_etarias' => [
                ['rango' => '16-17', 'categoria' => 'Primer Voto', 'pct' => 6.5],
                ['rango' => '18-29', 'categoria' => 'Jóvenes / Estudiantes', 'pct' => 34.0],
                ['rango' => '30-49', 'categoria' => 'Adultos / Productivos', 'pct' => 38.5],
                ['rango' => '50-69', 'categoria' => 'Adultos Mayores', 'pct' => 16.0],
                ['rango' => '70+', 'categoria' => 'Tercera Edad', 'pct' => 5.0],
            ],
            'ciudades_principales' => [
                ['ciudad' => 'Albardón', 'pct' => 68.4],
                ['ciudad' => 'Gran San Juan (Capital/Chimbas)', 'pct' => 22.1],
                ['ciudad' => 'Resto de San Juan', 'pct' => 9.5],
            ],
            'horarios_actividad' => [
                'dias_pico' => ['Martes', 'Jueves', 'Domingo'],
                'horas_pico' => ['13:00 - 14:30 hs', '20:30 - 22:30 hs'],
            ],
        ];

        $demografiaFacebook = [
            'fuente_datos' => 'meta_graph_api',
            'genero' => [
                'femenino_pct' => 58.0,
                'masculino_pct' => 42.0,
            ],
            'franjas_etarias' => [
                ['rango' => '16-17', 'categoria' => 'Primer Voto', 'pct' => 2.0],
                ['rango' => '18-29', 'categoria' => 'Jóvenes / Estudiantes', 'pct' => 18.0],
                ['rango' => '30-49', 'categoria' => 'Adultos / Productivos', 'pct' => 42.0],
                ['rango' => '50-69', 'categoria' => 'Adultos Mayores', 'pct' => 28.0],
                ['rango' => '70+', 'categoria' => 'Tercera Edad', 'pct' => 10.0],
            ],
            'ciudades_principales' => [
                ['ciudad' => 'Albardón', 'pct' => 74.0],
                ['ciudad' => 'Chimbas & Rivadavia', 'pct' => 18.0],
                ['ciudad' => 'San Juan Capital', 'pct' => 8.0],
            ],
            'horarios_actividad' => [
                'dias_pico' => ['Lunes', 'Miércoles', 'Sábado'],
                'horas_pico' => ['12:00 - 14:00 hs', '19:00 - 21:30 hs'],
            ],
        ];

        $redesPropio = [
            [
                'plataforma' => 'instagram',
                'handle_usuario' => '@federico__sisterna',
                'url_perfil' => 'https://www.instagram.com/federico__sisterna/',
                'foto_perfil_url' => $propio->avatar_url,
                'esta_activo' => true,
                'esta_verificado' => false,
                'seguidores_actuales' => 1359,
                'seguidos_actuales' => 588,
                'publicaciones_totales' => 64,
                'me_gusta_totales' => 0,
                'visualizaciones_totales' => 0,
                'fecha_punto_cero' => now()->subDays(60)->toDateString(),
                'seguidores_punto_cero' => 980,
                'seguidos_punto_cero' => 520,
                'publicaciones_punto_cero' => 38,
                'me_gusta_punto_cero' => 0,
                'visualizaciones_punto_cero' => 0,
                'notas_punto_cero' => 'Punto Cero oficial inicial: 38 publicaciones, 980 seguidores, 520 seguidos.',
                'demografia_interna_propia' => $demografiaInstagram,
            ],
            [
                'plataforma' => 'facebook',
                'handle_usuario' => '@ahoraalbardon',
                'url_perfil' => 'https://www.facebook.com/ahoraalbardon',
                'foto_perfil_url' => 'https://scontent.fmdz5-1.fna.fbcdn.net/v/t39.30808-1/518116244_620360811093741_2988785287798278700_n.jpg',
                'esta_activo' => true,
                'esta_verificado' => false,
                'seguidores_actuales' => 9466,
                'seguidos_actuales' => 58,
                'publicaciones_totales' => 0,
                'me_gusta_totales' => 0,
                'visualizaciones_totales' => 0,
                'fecha_punto_cero' => now()->subDays(60)->toDateString(),
                'seguidores_punto_cero' => 8600,
                'seguidos_punto_cero' => 50,
                'publicaciones_punto_cero' => 0,
                'me_gusta_punto_cero' => 0,
                'visualizaciones_punto_cero' => 0,
                'notas_punto_cero' => 'Punto Cero oficial Facebook: 8.600 seguidores (Me gusta), 50 seguidos.',
                'demografia_interna_propia' => $demografiaFacebook,
            ],
            [
                'plataforma' => 'tiktok',
                'handle_usuario' => '@fede.sisterna',
                'url_perfil' => '',
                'foto_perfil_url' => $propio->avatar_url,
                'esta_activo' => false,
                'esta_verificado' => false,
                'seguidores_actuales' => 0,
                'seguidos_actuales' => 0,
                'publicaciones_totales' => 0,
                'me_gusta_totales' => 0,
                'visualizaciones_totales' => 0,
                'fecha_punto_cero' => now()->toDateString(),
                'seguidores_punto_cero' => 0,
                'seguidos_punto_cero' => 0,
                'publicaciones_punto_cero' => 0,
                'me_gusta_punto_cero' => 0,
                'visualizaciones_punto_cero' => 0,
                'demografia_interna_propia' => null,
            ],
        ];
        foreach ($redesPropio as $r) {
            $perfilCreado = PerfilSocial::updateOrCreate(
                ['candidato_id' => $propio->id, 'plataforma' => $r['plataforma']],
                $r
            );

            // Poblar curva histórica evolutiva time-series (Punto Cero -> Hoy)
            if ($r['plataforma'] === 'instagram') {
                PerfilSocialMetrica::where('perfil_social_id', $perfilCreado->id)->delete();

                $hitosInstagram = [
                    ['dias' => 60, 'seguidores' => 980, 'seguidos' => 520, 'posts' => 38, 'neto' => 0, 'dia' => 0, 'fuente' => 'manual'],
                    ['dias' => 45, 'seguidores' => 1045, 'seguidos' => 534, 'posts' => 44, 'neto' => 65, 'dia' => 65, 'fuente' => 'cron_24h'],
                    ['dias' => 30, 'seguidores' => 1120, 'seguidos' => 550, 'posts' => 49, 'neto' => 140, 'dia' => 75, 'fuente' => 'cron_24h'],
                    ['dias' => 20, 'seguidores' => 1205, 'seguidos' => 562, 'posts' => 54, 'neto' => 225, 'dia' => 85, 'fuente' => 'cron_24h'],
                    ['dias' => 10, 'seguidores' => 1278, 'seguidos' => 575, 'posts' => 59, 'neto' => 298, 'dia' => 73, 'fuente' => 'cron_24h'],
                    ['dias' => 3,  'seguidores' => 1324, 'seguidos' => 582, 'posts' => 62, 'neto' => 344, 'dia' => 46, 'fuente' => 'cron_24h'],
                    ['dias' => 1,  'seguidores' => 1348, 'seguidos' => 586, 'posts' => 63, 'neto' => 368, 'dia' => 24, 'fuente' => 'cron_24h'],
                    ['dias' => 0,  'seguidores' => 1359, 'seguidos' => 588, 'posts' => 64, 'neto' => 379, 'dia' => 11, 'fuente' => 'auto_scraper'], // Sincronizado Hoy
                ];

                foreach ($hitosInstagram as $hito) {
                    $fechaMedicion = now()->subDays($hito['dias'])->toDateString();
                    PerfilSocialMetrica::updateOrCreate(
                        [
                            'perfil_social_id' => $perfilCreado->id,
                            'fecha' => $fechaMedicion,
                        ],
                        [
                            'seguidores' => $hito['seguidores'],
                            'seguidos' => $hito['seguidos'],
                            'publicaciones_totales' => $hito['posts'],
                            'me_gusta_totales' => 0,
                            'visualizaciones_totales' => 0,
                            'crecimiento_seguidores_dia' => $hito['dia'],
                            'crecimiento_seguidos_dia' => 2,
                            'crecimiento_posts_dia' => 1,
                            'crecimiento_seguidores_neto' => $hito['neto'],
                            'crecimiento_posts_neto' => $hito['posts'] - 38,
                            'fuente' => $hito['fuente'],
                        ]
                    );
                }

                $perfilCreado->update([
                    'ultima_auditoria_at' => now(),
                    'delta_seguidores_24h' => 11,
                ]);
            }

            if ($r['plataforma'] === 'facebook') {
                PerfilSocialMetrica::where('perfil_social_id', $perfilCreado->id)->delete();

                $hitosFacebook = [
                    ['dias' => 60, 'seguidores' => 8600, 'seguidos' => 50, 'posts' => 0, 'neto' => 0, 'dia' => 0, 'fuente' => 'manual'],
                    ['dias' => 45, 'seguidores' => 8780, 'seguidos' => 52, 'posts' => 0, 'neto' => 180, 'dia' => 180, 'fuente' => 'cron_24h'],
                    ['dias' => 30, 'seguidores' => 8990, 'seguidos' => 54, 'posts' => 0, 'neto' => 390, 'dia' => 210, 'fuente' => 'cron_24h'],
                    ['dias' => 15, 'seguidores' => 9210, 'seguidos' => 55, 'posts' => 0, 'neto' => 610, 'dia' => 220, 'fuente' => 'cron_24h'],
                    ['dias' => 5,  'seguidores' => 9380, 'seguidos' => 57, 'posts' => 0, 'neto' => 780, 'dia' => 170, 'fuente' => 'cron_24h'],
                    ['dias' => 0,  'seguidores' => 9466, 'seguidos' => 58, 'posts' => 0, 'neto' => 866, 'dia' => 86, 'fuente' => 'cron_24h'], // Sincronizado Hoy
                ];

                foreach ($hitosFacebook as $hito) {
                    $fechaMedicion = now()->subDays($hito['dias'])->toDateString();
                    PerfilSocialMetrica::updateOrCreate(
                        [
                            'perfil_social_id' => $perfilCreado->id,
                            'fecha' => $fechaMedicion,
                        ],
                        [
                            'seguidores' => $hito['seguidores'],
                            'seguidos' => $hito['seguidos'],
                            'publicaciones_totales' => $hito['posts'],
                            'me_gusta_totales' => 0,
                            'visualizaciones_totales' => 0,
                            'crecimiento_seguidores_dia' => $hito['dia'],
                            'crecimiento_seguidos_dia' => 1,
                            'crecimiento_posts_dia' => 0,
                            'crecimiento_seguidores_neto' => $hito['neto'],
                            'crecimiento_posts_neto' => 0,
                            'fuente' => $hito['fuente'],
                        ]
                    );
                }

                $perfilCreado->update([
                    'ultima_auditoria_at' => now(),
                    'delta_seguidores_24h' => 86,
                ]);
            }
        }

        // Asignar workspace a cualquier registro huérfano
        Publicacion::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
        NotaPrensa::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
        MedioPrensa::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
        EventoCrisis::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
        AlianzaPolitica::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
        EventoCalendario::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
        PresupuestoPartida::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
        InformeEjecutivo::whereNull('workspace_id')->update(['workspace_id' => $workspace->id]);
    }
}
