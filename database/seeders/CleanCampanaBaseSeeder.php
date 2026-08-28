<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\MedioPrensa;
use App\Models\PerfilSocial;
use App\Models\Territorio;
use App\Models\User;
use App\Models\Workspace;
use App\Services\DemographicIntelligenceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CleanCampanaBaseSeeder extends Seeder
{
    /**
     * Seeder de Base Limpia para Inicio de Campaña Real.
     * Carga:
     * - 3 Usuarios base (Admin, Consultor, Visualizador)
     * - 1 Workspace Activo
     * - Territorios (San Juan + 19 Departamentos con inteligencia demográfica INDEC)
     * - Ciclos de Campaña (2025 Activo)
     * - Ejes Temáticos de Campaña
     * - Medios de Prensa Locales (para selector de clipping)
     * - Ficha de Candidato Propio en Blanco (Lista para fijar Punto Cero)
     * - SIN publicaciones, SIN notas, SIN crisis, SIN rivales falsos.
     */
    public function run(): void
    {
        $demoService = new DemographicIntelligenceService;

        // ─────────────────────────────────────────────────────────
        // 1. USUARIOS DEL SISTEMA
        // ─────────────────────────────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@brandingpo.com'],
            [
                'name' => 'Administrador General',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $consultor = User::updateOrCreate(
            ['email' => 'consultor@brandingpo.com'],
            [
                'name' => 'Consultor Estratégico',
                'password' => Hash::make('password'),
                'role' => 'consultor',
            ]
        );

        $visualizador = User::updateOrCreate(
            ['email' => 'visualizador@brandingpo.com'],
            [
                'name' => 'Visualizador Ejecutivo',
                'password' => Hash::make('password'),
                'role' => 'visualizador',
            ]
        );

        // ─────────────────────────────────────────────────────────
        // 2. WORKSPACE INICIAL
        // ─────────────────────────────────────────────────────────
        $workspace = Workspace::updateOrCreate(
            ['slug' => 'sisterna-albardon-2025'],
            [
                'nombre' => 'Campaña Sisterna — Albardón 2025',
                'nivel_politico' => 'intendente',
                'provincia' => 'San Juan',
                'plan' => 'war_room',
                'activo' => true,
            ]
        );

        // Vincular usuarios al workspace
        foreach ([$admin, $consultor, $visualizador] as $u) {
            $workspace->usuarios()->syncWithoutDetaching([
                $u->id => ['role' => $u->role === 'admin' ? 'admin' : ($u->role === 'consultor' ? 'consultor' : 'visualizador')],
            ]);
            $u->update(['active_workspace_id' => $workspace->id]);
        }

        // ─────────────────────────────────────────────────────────
        // 3. TERRITORIOS (San Juan + 19 Departamentos)
        // ─────────────────────────────────────────────────────────
        $provincia = Territorio::updateOrCreate(
            ['nombre' => 'Provincia de San Juan', 'workspace_id' => $workspace->id],
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

        $departamentosData = $demoService->getSanJuanDepartamentosFallback();
        $departamentosCreados = [];

        foreach ($departamentosData as $d) {
            $piramide = $demoService->generarPiramideEtaria($d['poblacion_total'], $d['padron_electoral']);
            $dep = Territorio::updateOrCreate(
                ['nombre' => "Departamento {$d['nombre']}", 'workspace_id' => $workspace->id],
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

        $albardon = $departamentosCreados['albardón'] ?? Territorio::where('workspace_id', $workspace->id)->first();

        // ─────────────────────────────────────────────────────────
        // 4. CICLOS DE CAMPAÑA
        // ─────────────────────────────────────────────────────────
        $ciclo2025 = CicloCampana::updateOrCreate(
            ['anio' => 2025, 'workspace_id' => $workspace->id],
            [
                'nombre' => 'Elecciones Legislativas 2025',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'estado' => 'finalizada',
                'es_activo' => false,
            ]
        );

        $ciclo2027 = CicloCampana::updateOrCreate(
            ['anio' => 2027, 'workspace_id' => $workspace->id],
            [
                'nombre' => 'Campaña Ejecutiva & Municipal 2026 / 2027',
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2027-12-31',
                'estado' => 'activa',
                'es_activo' => true,
            ]
        );

        // ─────────────────────────────────────────────────────────
        // 5. EJES TEMÁTICOS (5 Pilares Estratégicos & 16 Sub-ejes)
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
                ['slug' => $eje['slug'], 'workspace_id' => $workspace->id],
                $eje
            );
        }

        // ─────────────────────────────────────────────────────────
        // 6. MEDIOS DE PRENSA LOCALES (Para Observatorio y Clipping)
        // ─────────────────────────────────────────────────────────
        $medios = [
            [
                'nombre' => 'Diario de Cuyo',
                'tipo_medio' => 'digital',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
                'url_sitio' => 'https://www.diariodecuyo.com.ar',
            ],
            [
                'nombre' => 'Tiempo de San Juan',
                'tipo_medio' => 'digital',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
                'url_sitio' => 'https://www.tiempodesanjuan.com',
            ],
            [
                'nombre' => 'Telesol Diario',
                'tipo_medio' => 'tv',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
                'url_sitio' => 'https://telesoldiario.com',
            ],
            [
                'nombre' => 'Radio Sarmiento (AM 1120 / FM 104.7)',
                'tipo_medio' => 'radio',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
                'url_sitio' => 'https://www.sarmiento.com.ar',
            ],
            [
                'nombre' => 'Canal 13 San Juan TV',
                'tipo_medio' => 'tv',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
                'url_sitio' => 'https://www.canal13sanjuan.com',
            ],
            [
                'nombre' => 'Huarpe Digital',
                'tipo_medio' => 'digital',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
                'url_sitio' => 'https://www.diariohuarpe.com',
            ],
        ];

        foreach ($medios as $m) {
            MedioPrensa::updateOrCreate(
                ['nombre' => $m['nombre'], 'workspace_id' => $workspace->id],
                $m
            );
        }

        // ─────────────────────────────────────────────────────────
        // 7. CANDIDATO PROPIO (Base en Blanco — Listo para Punto Cero)
        // ─────────────────────────────────────────────────────────
        $propio = Candidato::updateOrCreate(
            [
                'nombre_completo' => 'Federico Sisterna',
                'workspace_id' => $workspace->id,
            ],
            [
                'ciclo_campana_id' => $ciclo2027->id,
                'territorio_id' => $albardon->id,
                'partido_coalicion' => 'Ahora Albardón',
                'cargo_aspirado' => 'Candidato a Intendente',
                'estado_politico' => 'candidato',
                'color_hex' => '#06b6d4',
                'es_propio' => true,
                'avatar_url' => '',
                'bio_resumen' => 'Espacio "Ahora Albardón", alternativa ciudadana para transformar el departamento con obras, producción y cercanía vecinal.',
            ]
        );

        // Inicializar canales de redes en blanco (inactivos)
        $plataformas = ['instagram', 'facebook', 'threads', 'tiktok', 'x_twitter', 'youtube', 'linkedin'];
        foreach ($plataformas as $plat) {
            PerfilSocial::updateOrCreate(
                ['candidato_id' => $propio->id, 'plataforma' => $plat],
                [
                    'handle_usuario' => '',
                    'url_perfil' => '',
                    'foto_perfil_url' => '',
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
                    'notas_punto_cero' => 'Punto Cero pendiente de fijación.',
                ]
            );
        }
    }
}
