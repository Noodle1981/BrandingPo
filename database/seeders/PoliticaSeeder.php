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
        $demoService = new DemographicIntelligenceService();

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
            $workspace->usuarios()->syncWithoutDetaching([
                $user->id => ['role' => $user->role === 'admin' ? 'admin' : 'consultor'],
            ]);
            if (!$user->active_workspace_id) {
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
        // 4. EJES TEMÁTICOS
        // ─────────────────────────────────────────────────────────
        $ejes = [
            [
                'nombre' => 'Obras & Infraestructura Barrial',
                'slug' => 'obras',
                'color_badge' => '#06b6d4',
                'descripcion' => 'Pavimento, iluminación LED, plazas, cloacas y urbanización.',
            ],
            [
                'nombre' => 'Seguridad Ciudadana & Prevención',
                'slug' => 'seguridad',
                'color_badge' => '#ef4444',
                'descripcion' => 'Patrullas barriales, cámaras, monitoreo y tranquilidad vecinal.',
            ],
            [
                'nombre' => 'Producción, Empleo & Juventud',
                'slug' => 'produccion',
                'color_badge' => '#10b981',
                'descripcion' => 'Comercio local, primer empleo, apoyo a viñateros y emprendedores.',
            ],
            [
                'nombre' => 'Salud, Niñez & Cercanía Social',
                'slug' => 'salud',
                'color_badge' => '#f59e0b',
                'descripcion' => 'Dispensarios 24hs, asistencia alimentaria y centros de salud.',
            ],
            [
                'nombre' => 'Innovación, Deporte & Gobierno Abierto',
                'slug' => 'innovacion',
                'color_badge' => '#8b5cf6',
                'descripcion' => 'Polideportivos, cultura joven, trámites ágiles y transparencia.',
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
                'fecha_punto_cero' => now()->toDateString(),
                'seguidores_punto_cero' => 1359,
                'seguidos_punto_cero' => 588,
                'publicaciones_punto_cero' => 64,
                'me_gusta_punto_cero' => 0,
                'visualizaciones_punto_cero' => 0,
                'notas_punto_cero' => 'Punto Cero oficial inicial: 64 publicaciones, 1.359 seguidores, 588 seguidos.',
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
                'fecha_punto_cero' => now()->toDateString(),
                'seguidores_punto_cero' => 9466,
                'seguidos_punto_cero' => 58,
                'publicaciones_punto_cero' => 0,
                'me_gusta_punto_cero' => 0,
                'visualizaciones_punto_cero' => 0,
                'notas_punto_cero' => 'Punto Cero oficial Facebook: 9.466 seguidores (Me gusta), 58 seguidos.',
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
            ],
        ];
        foreach ($redesPropio as $r) {
            PerfilSocial::updateOrCreate(
                ['candidato_id' => $propio->id, 'plataforma' => $r['plataforma']],
                $r
            );
        }

        // ─────────────────────────────────────────────────────────
        // 6. RIVAL OPOSITOR MUNICIPAL (Albardón)
        // ─────────────────────────────────────────────────────────
        $rival = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Carlos Morales',
                'workspace_id' => $workspace->id,
            ],
            [
                'ciclo_campana_id' => $ciclo2025->id,
                'territorio_id' => $albardon->id,
                'partido_coalicion' => 'Frente Renovador Departamental',
                'cargo_aspirado' => 'Candidato a Intendente',
                'estado_politico' => 'opositor',
                'color_hex' => '#8b5cf6',
                'es_propio' => false,
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&auto=format&fit=crop&q=80',
                'bio_resumen' => 'Referente opositor en Albardón. Discurso enfocado en críticas a la gestión municipal y reclamos de servicios.',
            ]
        );

        $redesRival = [
            ['plataforma' => 'facebook', 'handle_usuario' => '@carlosmorales.albardon', 'seguidores_actuales' => 6100, 'publicaciones_totales' => 120, 'esta_activo' => true],
            ['plataforma' => 'instagram', 'handle_usuario' => '@carlosmorales_ok', 'seguidores_actuales' => 2400, 'publicaciones_totales' => 45, 'esta_activo' => true],
        ];
        foreach ($redesRival as $r) {
            PerfilSocial::updateOrCreate(
                ['candidato_id' => $rival->id, 'plataforma' => $r['plataforma']],
                $r
            );
        }

        // ─────────────────────────────────────────────────────────
        // 7. CANDIDATOS MONITOREADOS EN DEPARTAMENTOS CLAVE
        // ─────────────────────────────────────────────────────────
        $candidatosAliados = [
            [
                'dept' => 'capital',
                'nombre' => 'Susana Laciar',
                'partido' => 'Unidos por San Juan',
                'cargo' => 'Candidata a Intendente Capital',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&auto=format&fit=crop&q=80',
                'seguidores' => 42000,
            ],
            [
                'dept' => 'rawson',
                'nombre' => 'Carlos Munisaga',
                'partido' => 'Frente San Juan Adelante',
                'cargo' => 'Candidato a Intendente Rawson',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&auto=format&fit=crop&q=80',
                'seguidores' => 31500,
            ],
            [
                'dept' => 'rivadavia',
                'nombre' => 'Sergio Miodowsky',
                'partido' => 'Cambia Rivadavia',
                'cargo' => 'Candidato a Intendente Rivadavia',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&auto=format&fit=crop&q=80',
                'seguidores' => 28000,
            ],
            [
                'dept' => 'chimbas',
                'nombre' => 'Daniela Rodríguez',
                'partido' => 'Chimbas Te Quiero',
                'cargo' => 'Candidata a Intendente Chimbas',
                'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&auto=format&fit=crop&q=80',
                'seguidores' => 35000,
            ],
            [
                'dept' => 'pocito',
                'nombre' => 'Fabián Aballay',
                'partido' => 'Pocito Crece',
                'cargo' => 'Candidato a Intendente Pocito',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=300&auto=format&fit=crop&q=80',
                'seguidores' => 19500,
            ],
            [
                'dept' => 'caucete',
                'nombre' => 'Romina Rosas',
                'partido' => 'Frente Caucete Adelante',
                'cargo' => 'Candidata a Intendente Caucete',
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=300&auto=format&fit=crop&q=80',
                'seguidores' => 14200,
            ],
        ];

        foreach ($candidatosAliados as $aliado) {
            $depTarget = $departamentosCreados[$aliado['dept']] ?? null;
            if ($depTarget) {
                $cand = Candidato::updateOrCreate(
                    [
                        'nombre_completo' => $aliado['nombre'],
                        'workspace_id' => $workspace->id,
                    ],
                    [
                        'territorio_id' => $depTarget->id,
                        'ciclo_campana_id' => $ciclo2025->id,
                        'partido_coalicion' => $aliado['partido'],
                        'cargo_aspirado' => $aliado['cargo'],
                        'estado_politico' => 'candidato',
                        'color_hex' => '#10b981',
                        'es_propio' => false,
                        'avatar_url' => $aliado['avatar'],
                        'bio_resumen' => "Candidato a Intendente en {$depTarget->nombre}.",
                    ]
                );

                PerfilSocial::updateOrCreate(
                    ['candidato_id' => $cand->id, 'plataforma' => 'instagram'],
                    [
                        'handle_usuario' => '@' . strtolower(str_replace(' ', '', $aliado['nombre'])),
                        'esta_activo' => true,
                        'seguidores_actuales' => $aliado['seguidores'],
                        'publicaciones_totales' => 110,
                        'fecha_punto_cero' => now()->toDateString(),
                        'seguidores_punto_cero' => $aliado['seguidores'],
                    ]
                );
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
