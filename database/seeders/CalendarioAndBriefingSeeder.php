<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\CicloCampana;
use App\Models\EventoCalendario;
use App\Models\InformeEjecutivo;
use App\Models\PresupuestoPartida;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CalendarioAndBriefingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ciclo = CicloCampana::where('es_activo', true)->first() ?? CicloCampana::first();
        $candidatos = Candidato::all()->keyBy('nombre_completo');
        $propio = $candidatos->get('Martín Rodríguez');
        $rival = $candidatos->get('Carlos Morales');

        $now = Carbon::now();

        if (! $ciclo) {
            return;
        }

        // 1. Eventos del Calendario / Agenda de Campaña
        $eventos = [
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'titulo' => 'Acto Central de Cierre de Campaña Barrial',
                'fecha_inicio' => $now->copy()->addDays(5)->setTime(18, 30),
                'fecha_fin' => $now->copy()->addDays(5)->setTime(21, 30),
                'tipo_evento' => 'acto',
                'lugar' => 'Plaza Central San Martín',
                'estado' => 'programado',
                'notas' => 'Convocatoria masiva de centros vecinales y organizaciones sociales. Transmisión multired en vivo.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'titulo' => 'Gran Debate de Candidatos a Intendente',
                'fecha_inicio' => $now->copy()->addDays(8)->setTime(21, 0),
                'fecha_fin' => $now->copy()->addDays(8)->setTime(23, 0),
                'tipo_evento' => 'debate',
                'lugar' => 'Estudios Canal 12 (El Doce TV)',
                'estado' => 'programado',
                'notas' => 'Preparación de ejes: Seguridad, Transporte, Obras y Alivio Fiscal. Monitoreo en vivo de reacciones en redes.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'titulo' => 'Vencimiento y Rotación de Pauta en Meta & TikTok',
                'fecha_inicio' => $now->copy()->addDays(2)->setTime(10, 0),
                'fecha_fin' => $now->copy()->addDays(2)->setTime(12, 0),
                'tipo_evento' => 'pauta_vencimiento',
                'lugar' => 'War Room / Consola Ads',
                'estado' => 'programado',
                'notas' => 'Cierre de la campaña de luminarias barriales y reasignación de $80.000 a nuevos Reels de juventud.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $propio?->id,
                'titulo' => 'Caravana Vecinal por Distritos Sur y Oeste',
                'fecha_inicio' => $now->copy()->addDays(12)->setTime(15, 0),
                'fecha_fin' => $now->copy()->addDays(12)->setTime(19, 0),
                'tipo_evento' => 'caravana',
                'lugar' => 'Avenida de Mayo y Rotonda Sur',
                'estado' => 'programado',
                'notas' => 'Recorrido en camioneta abierta con paradas en 4 clubes barriales.',
            ],
            [
                'ciclo_campana_id' => $ciclo->id,
                'candidato_id' => $rival?->id,
                'titulo' => 'Conferencia de Prensa del Bloque Opositor',
                'fecha_inicio' => $now->copy()->addDays(3)->setTime(11, 0),
                'fecha_fin' => $now->copy()->addDays(3)->setTime(12, 30),
                'tipo_evento' => 'rueda_prensa',
                'lugar' => 'Hotel Boulevard',
                'estado' => 'programado',
                'notas' => 'Carlos Morales presentará balance crítico sobre presupuesto municipal.',
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
            'resumen_ejecutivo' => "La campaña del candidato propio Martín Rodríguez mantiene el liderazgo en Share of Voice digital con un 48.2% de las visualizaciones totales. La estrategia de micro-pauta barrial en Instagram Reels arrojó un CPV promedio de $0.75, un 28% más eficiente que la media histórica. El clima de humor social se consolidó en 4.6/5 estrellas tras la inauguración de luminarias y obras de conectividad vial.",
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
            'resumen_ejecutivo' => "Balance consolidado de la gestión de comunicación y marca política. Se alcanzaron más de 18 millones de visualizaciones orgánicas y pagadas en el trienio, con un crecimiento neto del 320% en la comunidad de seguidores del candidato. La gestión de crisis cerró con un tiempo promedio de respuesta de 35 minutos.",
            'metricas_clave_snapshot' => [
                'total_vistas_ciclo' => 18500000,
                'inversion_total_ejecutada' => 14250000,
                'crisis_resueltas' => 14,
                'tiempo_respuesta_promedio_min' => 35,
            ],
            'conclusiones_estrategicas' => "El activo digital y la base de datos de simpatizantes geolocalizados constituyen una ventaja táctica decisiva para la contienda electoral venidera.",
        ]);
    }
}
