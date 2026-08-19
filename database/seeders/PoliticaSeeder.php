<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\PerfilSocial;
use App\Models\Territorio;
use Illuminate\Database\Seeder;

class PoliticaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Territorio
        $territorio = Territorio::firstOrCreate(
            ['nombre' => 'Departamento Capital / Gran Córdoba'],
            [
                'tipo' => 'municipio',
                'poblacion_total' => 1450000,
                'padron_electoral' => 1120000,
            ]
        );

        // 2. Ciclos de Campaña (Años)
        $ciclo2023 = CicloCampana::firstOrCreate(
            ['anio' => 2023],
            [
                'nombre' => 'Campaña Municipal 2023 (Gestión Institucional)',
                'fecha_inicio' => '2023-01-01',
                'fecha_fin' => '2023-12-31',
                'estado' => 'finalizada',
                'es_activo' => false,
            ]
        );

        $ciclo2025 = CicloCampana::firstOrCreate(
            ['anio' => 2025],
            [
                'nombre' => 'Elecciones Legislativas & Reelección 2025',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'activa',
                'es_activo' => true,
            ]
        );

        // 3. Ejes Temáticos
        $ejes = [
            [
                'nombre' => 'Seguridad Ciudadana & Prevención',
                'slug' => 'seguridad',
                'color_badge' => '#ef4444',
                'descripcion' => 'Patrullas barriales, cámaras, iluminación y lucha contra el delito.',
            ],
            [
                'nombre' => 'Obras Públicas & Urbanismo',
                'slug' => 'obras',
                'color_badge' => '#06b6d4',
                'descripcion' => 'Pavimentación, cloacas, espacios verdes y desagües pluviales.',
            ],
            [
                'nombre' => 'Economía Local & Empleo Joven',
                'slug' => 'economia',
                'color_badge' => '#10b981',
                'descripcion' => 'Fomento a comercios, parques industriales y capacitaciones.',
            ],
            [
                'nombre' => 'Salud & Desarrollo Social',
                'slug' => 'salud',
                'color_badge' => '#f59e0b',
                'descripcion' => 'Dispensarios 24hs, centros de primera infancia y asistencia alimentaria.',
            ],
            [
                'nombre' => 'Innovación & Transparencia',
                'slug' => 'innovacion',
                'color_badge' => '#8b5cf6',
                'descripcion' => 'Trámites digitales, gobierno abierto y modernización del estado.',
            ],
        ];

        foreach ($ejes as $eje) {
            EjeTematico::firstOrCreate(['slug' => $eje['slug']], $eje);
        }

        // 4. Candidatos Políticos Representativos

        // Candidato 1: PROPIO (Intendente en gestión / Reelección)
        $propio = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Martín Rodríguez',
                'ciclo_campana_id' => $ciclo2025->id,
            ],
            [
                'territorio_id' => $territorio->id,
                'partido_coalicion' => 'Hacemos Ciudad / Frente Cívico',
                'cargo_aspirado' => 'Intendente Municipal (Reelección)',
                'estado_politico' => 'en_funciones',
                'color_hex' => '#06b6d4',
                'es_propio' => true,
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80',
                'bio_resumen' => 'Intendente en ejercicio. Eje en obras de infraestructura barrial, sustentabilidad y digitalización de trámites municipales.',
            ]
        );

        $redesPropio = [
            ['plataforma' => 'facebook', 'handle_usuario' => '@martinrodriguez.oficial', 'seguidores_actuales' => 78500, 'publicaciones_totales' => 342],
            ['plataforma' => 'instagram', 'handle_usuario' => '@martinrodriguez_ok', 'seguidores_actuales' => 64200, 'publicaciones_totales' => 418],
            ['plataforma' => 'x_twitter', 'handle_usuario' => '@mrodriguez_ar', 'seguidores_actuales' => 28400, 'publicaciones_totales' => 890],
            ['plataforma' => 'tiktok', 'handle_usuario' => '@martin.rodriguez', 'seguidores_actuales' => 27300, 'publicaciones_totales' => 124],
        ];
        foreach ($redesPropio as $r) {
            PerfilSocial::updateOrCreate(
                ['candidato_id' => $propio->id, 'plataforma' => $r['plataforma']],
                $r
            );
        }

        // Candidato 2: RIVAL OPOSITOR PRINCIPAL
        $rival = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Carlos Morales',
                'ciclo_campana_id' => $ciclo2025->id,
            ],
            [
                'territorio_id' => $territorio->id,
                'partido_coalicion' => 'Unión por el Cambio',
                'cargo_aspirado' => 'Candidato a Intendente',
                'estado_politico' => 'opositor',
                'color_hex' => '#8b5cf6',
                'es_propio' => false,
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&auto=format&fit=crop&q=80',
                'bio_resumen' => 'Diputado Provincial y referente opositor. Discurso enfocado en denuncias sobre gasto público y propuestas duras de seguridad.',
            ]
        );

        $redesRival = [
            ['plataforma' => 'facebook', 'handle_usuario' => '@carlosmorales.cba', 'seguidores_actuales' => 61000, 'publicaciones_totales' => 280],
            ['plataforma' => 'instagram', 'handle_usuario' => '@carlosmorales_ok', 'seguidores_actuales' => 52600, 'publicaciones_totales' => 310],
            ['plataforma' => 'x_twitter', 'handle_usuario' => '@cmorales_politica', 'seguidores_actuales' => 19500, 'publicaciones_totales' => 650],
            ['plataforma' => 'tiktok', 'handle_usuario' => '@carlosmorales_cba', 'seguidores_actuales' => 9000, 'publicaciones_totales' => 64],
        ];
        foreach ($redesRival as $r) {
            PerfilSocial::updateOrCreate(
                ['candidato_id' => $rival->id, 'plataforma' => $r['plataforma']],
                $r
            );
        }

        // Candidato 3: PRECANDIDATA EMERGENTE (INTERNA)
        $precandidata = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Lucía Fernández',
                'ciclo_campana_id' => $ciclo2025->id,
            ],
            [
                'territorio_id' => $territorio->id,
                'partido_coalicion' => 'Evolución Joven & Vecinalismo',
                'cargo_aspirado' => 'Precandidata a Intendente',
                'estado_politico' => 'precandidato',
                'color_hex' => '#f59e0b',
                'es_propio' => false,
                'avatar_url' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&auto=format&fit=crop&q=80',
                'bio_resumen' => 'Concejal y líder universitaria. Fuerte tracción en TikTok e Instagram con temas de transporte, ambiente e inclusión.',
            ]
        );

        $redesPrecandidata = [
            ['plataforma' => 'instagram', 'handle_usuario' => '@lucia.fernandez_vecinal', 'seguidores_actuales' => 32100, 'publicaciones_totales' => 195],
            ['plataforma' => 'tiktok', 'handle_usuario' => '@luciafernandez_ok', 'seguidores_actuales' => 24800, 'publicaciones_totales' => 110],
            ['plataforma' => 'x_twitter', 'handle_usuario' => '@lucia_fdz', 'seguidores_actuales' => 11400, 'publicaciones_totales' => 420],
        ];
        foreach ($redesPrecandidata as $r) {
            PerfilSocial::updateOrCreate(
                ['candidato_id' => $precandidata->id, 'plataforma' => $r['plataforma']],
                $r
            );
        }

        // Candidato 4: INTENDENTE ELECTO (CASO TRANSICIÓN)
        $electo = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Esteban Rossi',
                'ciclo_campana_id' => $ciclo2025->id,
            ],
            [
                'territorio_id' => $territorio->id,
                'partido_coalicion' => 'Renovación Federal',
                'cargo_aspirado' => 'Intendente Electo',
                'estado_politico' => 'intendente_electo',
                'color_hex' => '#10b981',
                'es_propio' => false,
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&auto=format&fit=crop&q=80',
                'bio_resumen' => 'Intendente electo de municipio metropolitano. En proceso de transición de mandato y alianzas estratégicas.',
            ]
        );

        $redesElecto = [
            ['plataforma' => 'facebook', 'handle_usuario' => '@estebanrossi_electo', 'seguidores_actuales' => 39500, 'publicaciones_totales' => 210],
            ['plataforma' => 'instagram', 'handle_usuario' => '@rossi_esteban', 'seguidores_actuales' => 24200, 'publicaciones_totales' => 170],
            ['plataforma' => 'linkedin', 'handle_usuario' => '@esteban-rossi-gestion', 'seguidores_actuales' => 10000, 'publicaciones_totales' => 85],
        ];
        foreach ($redesElecto as $r) {
            PerfilSocial::updateOrCreate(
                ['candidato_id' => $electo->id, 'plataforma' => $r['plataforma']],
                $r
            );
        }
    }
}
