<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Territorio;
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

        // 1. Territorio Provincial (Madre)
        $provincia = Territorio::updateOrCreate(
            ['nombre' => 'Provincia de San Juan'],
            [
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

        // 2. Departamentos de San Juan (19 Departamentos)
        $departamentosData = $demoService->getSanJuanDepartamentosFallback();
        $departamentosCreados = [];

        foreach ($departamentosData as $d) {
            $piramide = $demoService->generarPiramideEtaria($d['poblacion_total'], $d['padron_electoral']);
            $dep = Territorio::updateOrCreate(
                ['nombre' => "Departamento {$d['nombre']}"],
                [
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

        $albardon = $departamentosCreados['albardón'] ?? Territorio::first();

        // 3. Ciclos de Campaña (Años)
        $ciclo2023 = CicloCampana::firstOrCreate(
            ['anio' => 2023],
            [
                'nombre' => 'Campaña Provincial & Municipal 2023',
                'fecha_inicio' => '2023-01-01',
                'fecha_fin' => '2023-12-31',
                'estado' => 'finalizada',
                'es_activo' => false,
            ]
        );

        $ciclo2025 = CicloCampana::firstOrCreate(
            ['anio' => 2025],
            [
                'nombre' => 'Elecciones Legislativas & Renovación 2025 / 2027',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'es_activo' => true,
            ]
        );

        // 4. Ejes Temáticos
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
            EjeTematico::firstOrCreate(['slug' => $eje['slug']], $eje);
        }

        // 5. Candidatos Políticos Representativos

        // Candidato 1: PROPIO (Federico Sisterna - Albardón)
        $propio = Candidato::updateOrCreate(
            [
                'es_propio' => true,
            ],
            [
                'nombre_completo' => 'Federico Sisterna',
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

        // Candidato 2: RIVAL OPOSITOR MUNICIPAL (Albardón)
        $rival = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Carlos Morales',
                'ciclo_campana_id' => $ciclo2025->id,
            ],
            [
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
    }
}
