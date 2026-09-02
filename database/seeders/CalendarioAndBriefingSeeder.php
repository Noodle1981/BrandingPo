<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EjeTematico;
use App\Models\EventoCalendario;
use App\Models\InformeEjecutivo;
use App\Models\PresupuestoPartida;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CalendarioAndBriefingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workspace = Workspace::first();
        $ciclo = CicloCampana::where('es_activo', true)->first() ?? CicloCampana::first();
        $candidatos = Candidato::all()->keyBy('nombre_completo');
        $propio = $candidatos->get('Martín Rodríguez') ?? Candidato::where('es_propio', true)->first();
        $rival = $candidatos->get('Carlos Morales') ?? Candidato::where('es_propio', false)->first();

        $ejes = EjeTematico::all()->keyBy('slug');
        $ejeObras = $ejes->get('obras-publicas-e-infraestructura') ?? $ejes->first();
        $ejeSeguridad = $ejes->get('seguridad-ciudadana-y-prevencion') ?? $ejes->skip(1)->first();
        $ejeSalud = $ejes->get('salud-y-deportes') ?? $ejes->skip(2)->first();
        $ejeJuventud = $ejes->get('juventud') ?? $ejes->skip(3)->first();
        $ejeEconomia = $ejes->get('desarrollo-economico-y-empleo') ?? $ejes->skip(4)->first();
        $ejeEducacion = $ejes->get('educacion-e-innovacion') ?? $ejes->skip(5)->first();

        $now = Carbon::now();

        if (! $ciclo || ! $workspace) {
            return;
        }

        // Limpiar eventos previos para re-seed limpio
        EventoCalendario::where('workspace_id', $workspace->id)->delete();

        // 1. Eventos del Calendario / Agenda de Campaña centrados en Ejes Temáticos
        $eventos = [
            // Publicación Planificada de Eje (Contenido Digital Estratégico)
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'eje_tematico_id' => $ejeSeguridad?->id,
                'titulo' => 'Lanzamiento Reel: Presentación de Cámaras y Anillo Digital',
                'fecha_inicio' => $now->copy()->addDays(1)->setTime(19, 0),
                'fecha_fin' => $now->copy()->addDays(1)->setTime(20, 0),
                'tipo_evento' => 'publicacion_eje',
                'lugar' => 'Instagram & TikTok Feed',
                'estado' => 'programado',
                'notas' => 'Video dinámico con dron y testimonios de comerciantes sobre botón antipánico. Impulsar con micro-pauta.',
            ],
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'eje_tematico_id' => $ejeSalud?->id,
                'titulo' => 'Carrusel Multired: Plan Salitas 24 Horas y Vacunación Barrial',
                'fecha_inicio' => $now->copy()->addDays(3)->setTime(12, 30),
                'fecha_fin' => $now->copy()->addDays(3)->setTime(13, 30),
                'tipo_evento' => 'publicacion_eje',
                'lugar' => 'Facebook & Instagram',
                'estado' => 'programado',
                'notas' => 'Eje con déficit en el feed: publicar infografía de 5 placas con puntos de atención primaria.',
            ],
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'eje_tematico_id' => $ejeJuventud?->id,
                'titulo' => 'TikTok Live & Story: Mano a Mano sobre Primer Empleo y Becas',
                'fecha_inicio' => $now->copy()->addDays(4)->setTime(20, 30),
                'fecha_fin' => $now->copy()->addDays(4)->setTime(21, 30),
                'tipo_evento' => 'publicacion_eje',
                'lugar' => 'TikTok & Instagram Live',
                'estado' => 'programado',
                'notas' => 'Preguntas y respuestas directas con jóvenes de entre 16 y 24 años sin libreto.',
            ],
            // Actos Territoriales con Cobertura Digital
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'eje_tematico_id' => $ejeObras?->id,
                'titulo' => 'Acto Central de Cierre de Campaña Barrial & Alumbrado',
                'fecha_inicio' => $now->copy()->addDays(5)->setTime(18, 30),
                'fecha_fin' => $now->copy()->addDays(5)->setTime(21, 30),
                'tipo_evento' => 'acto',
                'lugar' => 'Plaza Central San Martín',
                'estado' => 'programado',
                'notas' => 'Convocatoria masiva de centros vecinales. Transmisión multired en vivo y generación de 3 Reels post-evento.',
            ],
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'eje_tematico_id' => $ejeSeguridad?->id,
                'titulo' => 'Gran Debate de Candidatos: Eje Seguridad y Modernización',
                'fecha_inicio' => $now->copy()->addDays(8)->setTime(21, 0),
                'fecha_fin' => $now->copy()->addDays(8)->setTime(23, 0),
                'tipo_evento' => 'debate',
                'lugar' => 'Estudios Canal 12 (El Doce TV)',
                'estado' => 'programado',
                'notas' => 'Monitoreo de social listening en tiempo real. Equipo de Fast-Flow listo para subir cortes clave en Twitter y TikTok.',
            ],
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'eje_tematico_id' => $ejeEconomia?->id,
                'titulo' => 'Vencimiento y Rotación de Pauta: Microcréditos y Emprendedores',
                'fecha_inicio' => $now->copy()->addDays(2)->setTime(10, 0),
                'fecha_fin' => $now->copy()->addDays(2)->setTime(12, 0),
                'tipo_evento' => 'pauta_vencimiento',
                'lugar' => 'War Room / Consola Ads',
                'estado' => 'programado',
                'notas' => 'Cierre de la campaña de luminarias barriales y reasignación de $80.000 a nuevos anuncios de fomento productivo.',
            ],
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'eje_tematico_id' => $ejeEducacion?->id,
                'titulo' => 'Caravana y Encuentro en Polo Tecnológico Comunitario',
                'fecha_inicio' => $now->copy()->addDays(12)->setTime(15, 0),
                'fecha_fin' => $now->copy()->addDays(12)->setTime(19, 0),
                'tipo_evento' => 'caravana',
                'lugar' => 'Avenida de Mayo y Rotonda Sur',
                'estado' => 'programado',
                'notas' => 'Recorrido con paradas en 4 clubes barriales y transmisión de cobertura en Stories.',
            ],
            [
                'workspace_id' => $workspace->id,
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $rival?->id,
                'eje_tematico_id' => $ejeEconomia?->id,
                'titulo' => 'Conferencia de Prensa del Bloque Opositor',
                'fecha_inicio' => $now->copy()->addDays(3)->setTime(11, 0),
                'fecha_fin' => $now->copy()->addDays(3)->setTime(12, 30),
                'tipo_evento' => 'rueda_prensa',
                'lugar' => 'Hotel Boulevard',
                'estado' => 'programado',
                'notas' => 'Carlos Morales presentará balance crítico sobre presupuesto municipal. Preparar informe de réplica inmediata.',
            ],
        ];

        foreach ($eventos as $e) {
            EventoCalendario::create($e);
        }

        // 2. Partidas Presupuestarias
        $partidas = [
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'categoria' => 'pauta_digital',
                'monto_asignado' => 8500000,
                'monto_ejecutado' => 4850000,
                'notas' => 'Inversión en Meta Ads (Instagram/Facebook), TikTok Ads y Google Search.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'categoria' => 'via_publica',
                'monto_asignado' => 6000000,
                'monto_ejecutado' => 3900000,
                'notas' => 'Gigantografías en accesos principales, séxtuples y pantallas LED urbanas.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'categoria' => 'produccion_audiovisual',
                'monto_asignado' => 4200000,
                'monto_ejecutado' => 2950000,
                'notas' => 'Rodaje de spots de TV/Cine, fotografía de campaña y cápsulas para redes.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'categoria' => 'eventos_territoriales',
                'monto_asignado' => 3800000,
                'monto_ejecutado' => 2100000,
                'notas' => 'Escenarios, sonido, iluminación y logística de actos en barrios.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'categoria' => 'contingencias',
                'monto_asignado' => 2000000,
                'monto_ejecutado' => 450000,
                'notas' => 'Fondo de reserva para contención de crisis mediáticas y réplicas urgentes.',
            ],
        ];

        foreach ($partidas as $p) {
            PresupuestoPartida::create($p);
        }

        // 3. Informes / Briefings Ejecutivos
        InformeEjecutivo::create([
            'ciclo_campana_id' => $ciclo->id,
            'titulo' => 'Briefing Estratégico Semanal - Estado de Campaña & Rendimiento de Pauta',
            'fecha_generacion' => $now->copy()->subDays(1),
            'periodo_cubierto' => 'Semana 34 - Agosto 2026',
            'resumen_ejecutivo' => 'La campaña del candidato propio Martín Rodríguez mantiene el liderazgo en Share of Voice digital con un 48.2% de las visualizaciones totales. La estrategia de micro-pauta barrial en Instagram Reels arrojó un CPV promedio de $0.75, un 28% más eficiente que la media histórica. El clima de humor social se consolidó en 4.6/5 estrellas tras la inauguración de luminarias y obras de conectividad vial.',
            'metricas_clave_snapshot' => [
                'total_vistas_semana' => 1240000,
                'inversion_pauta_semana' => 155000,
                'cpv_promedio' => 0.75,
                'sentimiento_favorable' => 74,
                'sentimiento_neutro' => 18,
                'sentimiento_critico' => 8,
                'proximidad_algoritmica' => 92,
            ],
            'conclusiones_estrategicas' => "1. Reforzar pauta segmentada en distritos Oeste donde la oposición intentó instalar críticas sobre cloacas.\n2. Aprovechar el debate de Canal 12 para polarizar sobre obras concretas versus promesas sin financiamiento.\n3. Coordinar nueva bajada de campaña con el Gobernador para potenciar el eje de seguridad barrial.",
        ]);

        InformeEjecutivo::create([
            'ciclo_campana_id' => $ciclo->id,
            'titulo' => 'Informe Ejecutivo de Transición & Cierre de Ciclo Institucional',
            'fecha_generacion' => $now->copy()->subWeeks(2),
            'periodo_cubierto' => 'Ciclo Anterior 2023 - Balance de Mandato',
            'resumen_ejecutivo' => 'Balance consolidado de la gestión de comunicación y marca política. Se alcanzaron más de 18 millones de visualizaciones orgánicas y pagadas en el trienio, con un crecimiento neto del 320% en la comunidad de seguidores del candidato. La gestión de crisis cerró con un tiempo promedio de respuesta de 35 minutos.',
            'metricas_clave_snapshot' => [
                'total_vistas_ciclo' => 18500000,
                'inversion_total_ejecutada' => 14250000,
                'crisis_resueltas' => 14,
                'tiempo_respuesta_promedio_min' => 35,
            ],
            'conclusiones_estrategicas' => 'El activo digital y la base de datos de simpatizantes geolocalizados constituyen una ventaja táctica decisiva para la contienda electoral venidera.',
        ]);
    }
}
