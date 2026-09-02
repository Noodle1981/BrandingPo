<?php

namespace Database\Seeders;

use App\Models\AlianzaPolitica;
use App\Models\Candidato;
use App\Models\EventoCrisis;
use App\Models\MedioPrensa;
use App\Models\NotaPrensa;
use App\Models\Territorio;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MediosAndCrisisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $territorio = Territorio::first();
        $candidatos = Candidato::all()->keyBy('nombre_completo');

        $propio = $candidatos->firstWhere('es_propio', true) ?? $candidatos->first();

        $now = Carbon::now();

        // 1. Medios de Prensa
        $medios = [
            [
                'nombre' => 'La Voz del Interior',
                'tipo_medio' => 'digital',
                'url_sitio' => 'https://www.lavoz.com.ar',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
            ],
            [
                'nombre' => 'Cadena 3 Argentina',
                'tipo_medio' => 'radio',
                'url_sitio' => 'https://www.cadena3.com',
                'alcance_tipo' => 'nacional',
                'sesgo_editorial_estimado' => 'independiente',
            ],
            [
                'nombre' => 'El Doce TV (Canal 12)',
                'tipo_medio' => 'tv',
                'url_sitio' => 'https://eldoce.tv',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
            ],
            [
                'nombre' => 'Puntal Río Cuarto',
                'tipo_medio' => 'digital',
                'url_sitio' => 'https://www.puntal.com.ar',
                'alcance_tipo' => 'local',
                'sesgo_editorial_estimado' => 'independiente',
            ],
            [
                'nombre' => 'Hoy Día Córdoba',
                'tipo_medio' => 'impreso',
                'url_sitio' => 'https://hoydia.com.ar',
                'alcance_tipo' => 'local',
                'sesgo_editorial_estimado' => 'opositor',
            ],
            [
                'nombre' => 'Perfil Córdoba',
                'tipo_medio' => 'digital',
                'url_sitio' => 'https://www.perfil.com/cordoba',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'independiente',
            ],
            [
                'nombre' => 'Radio Mitre Córdoba',
                'tipo_medio' => 'radio',
                'url_sitio' => 'https://radiomitre.cienradios.com/cordoba',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'opositor',
            ],
            [
                'nombre' => 'Cba24N Multimedio SRT',
                'tipo_medio' => 'digital',
                'url_sitio' => 'https://www.cba24n.com.ar',
                'alcance_tipo' => 'provincial',
                'sesgo_editorial_estimado' => 'oficialista',
            ],
        ];

        $mediosMap = [];
        foreach ($medios as $m) {
            $mediosMap[$m['nombre']] = MedioPrensa::firstOrCreate(
                ['nombre' => $m['nombre']],
                array_merge($m, ['territorio_id' => $territorio?->id])
            );
        }

        // 2. Notas de Prensa (Clipping Informativo)
        $notas = [
            [
                'medio' => 'La Voz del Interior',
                'candidato' => $propio,
                'fecha' => $now->copy()->subDays(1),
                'titulo' => 'La Municipalidad finalizó la pavimentación del anillo norte y sumó 500 luminarias LED',
                'resumen' => 'El intendente encabezó el acto junto a vecinos y anunció una inversión complementaria para desagües pluviales.',
                'tono' => 'favorable',
                'tapa' => true,
                'interacciones' => 4500,
                'replica' => null,
            ],
            [
                'medio' => 'Radio Mitre Córdoba',
                'candidato' => $propio,
                'fecha' => $now->copy()->subDays(2),
                'titulo' => 'Oposición denuncia demoras en el sistema de turnos de salud municipal',
                'resumen' => 'Concejales del bloque opositor señalaron colapso en guardias periféricas durante el fin de semana.',
                'tono' => 'critico',
                'tapa' => false,
                'interacciones' => 6200,
                'replica' => 'Secretaría de Salud emitió comunicado demostrando aumento del 40% en atenciones por guardia pediátrica estacional.',
            ],
            [
                'medio' => 'Hoy Día Córdoba',
                'candidato' => $propio,
                'fecha' => $now->copy()->subDays(5),
                'titulo' => 'Polémica por costos de refacción en el parque central',
                'resumen' => 'Cuestionamientos sobre plazos de licitación y empresas adjudicatarias de las obras recreativas.',
                'tono' => 'critico',
                'tapa' => true,
                'interacciones' => 5400,
                'replica' => 'Publicación de pliegos y actas en el Portal de Transparencia Municipal con auditoría del Tribunal de Cuentas.',
            ],
            [
                'medio' => 'Cba24N Multimedio SRT',
                'candidato' => $propio,
                'fecha' => $now->copy()->subDays(6),
                'titulo' => 'Más de 10.000 egresados de cursos gratuitos en la Escuela Municipal de Oficios',
                'resumen' => 'Impacto directo en la inserción laboral de jóvenes y mujeres en oficios digitales y técnicos.',
                'tono' => 'favorable',
                'tapa' => false,
                'interacciones' => 2800,
                'replica' => null,
            ],
        ];

        foreach ($notas as $n) {
            $medioObj = $mediosMap[$n['medio']] ?? null;
            if (! $medioObj) {
                continue;
            }

            NotaPrensa::create([
                'medio_prensa_id' => $medioObj->id,
                'candidato_id' => $n['candidato']?->id,
                'fecha_publicacion' => $n['fecha'],
                'titulo' => $n['titulo'],
                'resumen' => $n['resumen'],
                'tono_mencion' => $n['tono'],
                'es_tapa_o_principal' => $n['tapa'],
                'interacciones_en_redes_del_medio' => $n['interacciones'],
                'respuesta_replica_candidato' => $n['replica'],
                'url_nota' => "{$medioObj->url_sitio}/noticia-demo-".rand(100, 999),
            ]);
        }

        // 3. Eventos de Crisis (Centro de Crisis)
        if ($propio) {
            EventoCrisis::create([
                'candidato_id' => $propio->id,
                'titulo' => 'Filtración de audio manipulado con IA atribuido al equipo de campaña',
                'fecha_evento' => $now->copy()->subHours(2),
                'nivel_gravedad' => 'critico',
                'minutos_tiempo_respuesta' => 15,
                'estrategia_contencion' => 'Desmentida inmediata con comunicado técnico pericial y denuncia ante fiscalía de ciberdelitos.',
                'estado' => 'activo',
                'impacto_estimado' => 'Alto',
            ]);

            EventoCrisis::create([
                'candidato_id' => $propio->id,
                'titulo' => 'Ataque coordinado en redes por interrupción momentánea de línea de trolebuses',
                'fecha_evento' => $now->copy()->subDays(3),
                'nivel_gravedad' => 'moderado',
                'minutos_tiempo_respuesta' => 35,
                'estrategia_contencion' => 'Despliegue de cuadrillas técnicas, transmisión en vivo de la normalización del servicio y respuesta en Twitter/X con mapa en tiempo real.',
                'estado' => 'resuelto',
                'impacto_estimado' => 'Bajo',
            ]);

            EventoCrisis::create([
                'candidato_id' => $propio->id,
                'titulo' => 'Reclamo vecinal en zona sur por frecuencia de recolección de poda',
                'fecha_evento' => $now->copy()->subDays(1),
                'nivel_gravedad' => 'leve',
                'minutos_tiempo_respuesta' => 45,
                'estrategia_contencion' => 'Operativo especial de limpieza exprés 24hs anunciado en redes barriales y centros vecinales.',
                'estado' => 'resuelto',
                'impacto_estimado' => 'Bajo',
            ]);
        }

        // 4. Matriz de Alianzas y Padrinos Políticos
        if ($propio) {
            AlianzaPolitica::create([
                'candidato_id' => $propio->id,
                'nombre_figura' => 'Gobernador de la Provincia',
                'cargo_o_rol' => 'Gobernador Constitucional (Líder Provincial)',
                'tipo_impacto' => 'suma',
                'notas_observacion' => 'Fuerte transferencia de imagen positiva (62% aprobación en la capital). Presencia conjunta en inauguraciones de obras provinciales-municipales.',
            ]);

            AlianzaPolitica::create([
                'candidato_id' => $propio->id,
                'nombre_figura' => 'Ministro de Finanzas Provincial',
                'cargo_o_rol' => 'Ministro de Economía y Finanzas',
                'tipo_impacto' => 'suma',
                'notas_observacion' => 'Garantiza respaldo de solvencia fiscal y líneas de financiamiento internacional para el plan de pavimentación.',
            ]);

            AlianzaPolitica::create([
                'candidato_id' => $propio->id,
                'nombre_figura' => 'Ex-Secretario de Transporte Polémico',
                'cargo_o_rol' => 'Ex-Funcionario',
                'tipo_impacto' => 'resta',
                'notas_observacion' => 'Figura con imagen negativa asociada al conflicto gremial del 2021. Mantener al margen de actos públicos y fotos de campaña.',
            ]);
        }
    }
}
