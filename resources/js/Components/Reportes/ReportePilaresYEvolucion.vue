<script setup>
import {
  Target,
  Layers,
} from '@lucide/vue';

const props = defineProps({
  reporte: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div class="space-y-4">
    <!-- ACTO 2: MATRIZ DE LOS 3 PILARES ESTRATÉGICOS & FRAMING DISCURSIVO -->
    <section class="evitar-corte space-y-4 pt-1">
      <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
        <h3 class="text-base font-black text-slate-950 tracking-tight flex items-center gap-2">
          <Target class="w-4 h-4 text-cyan-600" />
          <span>2. Balance de Framing & Penetración por Pilares Estratégicos</span>
        </h3>
        <span class="text-xs font-mono text-slate-500">Distribución de la Voz de Campaña</span>
      </div>

      <!-- Tarjetas de los 3 Pilares -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div
          v-for="pilar in reporte.matriz_pilares.pilares"
          :key="pilar.clave"
          class="p-4 rounded-xl border space-y-2 bg-slate-50 border-slate-200 flex flex-col justify-between"
        >
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase" :class="pilar.color_badge">
                {{ pilar.nombre }}
              </span>
              <span class="text-xs font-mono font-black text-slate-950">
                {{ pilar.porcentaje_share }}% del mensaje
              </span>
            </div>
            <div class="text-xs font-bold text-slate-900 font-sans">
              {{ pilar.subtitulo }}
            </div>
            <p class="text-[11px] text-slate-500 leading-snug">
              <strong>Demanda:</strong> {{ pilar.demanda_central }}
            </p>
          </div>

          <!-- Métrica y sub-ejes -->
          <div class="border-t border-slate-200 pt-2 font-mono text-xs space-y-1">
            <div class="flex justify-between items-center text-slate-700">
              <span>Publicaciones:</span>
              <strong class="text-slate-900">{{ pilar.total_publicaciones }} posts</strong>
            </div>
            <div class="flex justify-between items-center text-purple-700 font-bold">
              <span>Score Ponderado:</span>
              <span>{{ Number(pilar.score_impacto).toLocaleString('es-AR') }} pts</span>
            </div>
            <div class="text-[10px] text-slate-500 pt-1 border-t border-slate-100 flex flex-wrap gap-1">
              <span v-for="sub in pilar.subejes.slice(0, 2)" :key="sub.nombre" class="bg-white px-1.5 py-0.5 rounded border border-slate-200">
                {{ sub.nombre }} ({{ sub.posts }})
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Narrativa de Pilares -->
      <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
        <strong>Análisis de Framing & Equilibrio:</strong> {{ reporte.narrativa.narrativa_pilares }}
      </p>
    </section>

    <!-- ACTO 3: EVOLUCIÓN MENSUAL CERRADA & RITMO DE CAMPAÑA -->
    <section class="evitar-corte space-y-4 pt-1">
      <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
        <h3 class="text-base font-black text-slate-950 tracking-tight flex items-center gap-2">
          <Layers class="w-4 h-4 text-emerald-600" />
          <span>3. Balance de Impacto y Evolución Mensual Cerrada</span>
        </h3>
        <span class="text-xs font-mono text-slate-500">Julio &bull; Agosto &bull; Septiembre</span>
      </div>

      <!-- Tarjetas Macro del Trimestre -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
          <span class="text-[10px] text-slate-500 uppercase font-bold block">Publicaciones</span>
          <div class="text-xl sm:text-2xl font-black text-slate-900">
            {{ reporte.kpis_trimestre.total_publicaciones }}
          </div>
          <span class="text-[10px] text-slate-500 block">Contenidos auditados</span>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
          <span class="text-[10px] text-slate-500 uppercase font-bold block">Visualizaciones</span>
          <div class="text-xl sm:text-2xl font-black text-cyan-700">
            {{ Number(reporte.kpis_trimestre.total_visualizaciones).toLocaleString('es-AR') }}
          </div>
          <span class="text-[10px] text-slate-500 block">Alcance acumulado</span>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
          <span class="text-[10px] text-slate-500 uppercase font-bold block">Score de Impacto</span>
          <div class="text-xl sm:text-2xl font-black text-purple-700">
            {{ Number(reporte.kpis_trimestre.score_impacto_total).toLocaleString('es-AR') }}
          </div>
          <span class="text-[10px] text-slate-500 block">Ponderación cívica</span>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
          <span class="text-[10px] text-slate-500 uppercase font-bold block">Aprobación Neta</span>
          <div class="text-xl sm:text-2xl font-black text-emerald-700">
            {{ reporte.kpis_trimestre.aprobacion_neta_pct }}%
          </div>
          <span class="text-[10px] text-emerald-600 font-semibold block">Clima altamente favorable</span>
        </div>
      </div>

      <!-- Tabla Mes a Mes -->
      <div class="border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-left text-xs border-collapse">
          <thead class="bg-slate-100 font-mono text-[11px] text-slate-700 uppercase">
            <tr class="border-b border-slate-200">
              <th class="py-2.5 px-3 font-bold">Mes Evaluado</th>
              <th class="py-2.5 px-3 font-bold text-center">Publicaciones</th>
              <th class="py-2.5 px-3 font-bold text-center">Me Gusta</th>
              <th class="py-2.5 px-3 font-bold text-center">Comentarios</th>
              <th class="py-2.5 px-3 font-bold text-center">Compartidos</th>
              <th class="py-2.5 px-3 font-bold text-right">Score Impacto</th>
              <th class="py-2.5 px-3 font-bold">Eje Principal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-mono">
            <tr v-for="mes in reporte.desglose_mensual" :key="mes.clave" class="hover:bg-slate-50">
              <td class="py-2.5 px-3 font-bold text-slate-900 font-sans">
                {{ mes.nombre }}
              </td>
              <td class="py-2.5 px-3 text-center font-bold text-slate-800">
                {{ mes.publicaciones }}
              </td>
              <td class="py-2.5 px-3 text-center text-slate-600">
                {{ Number(mes.likes).toLocaleString('es-AR') }}
              </td>
              <td class="py-2.5 px-3 text-center text-slate-600">
                {{ Number(mes.comentarios).toLocaleString('es-AR') }}
              </td>
              <td class="py-2.5 px-3 text-center text-slate-600">
                {{ Number(mes.compartidos).toLocaleString('es-AR') }}
              </td>
              <td class="py-2.5 px-3 text-right font-black text-purple-700">
                {{ Number(mes.score_impacto).toLocaleString('es-AR') }} pts
              </td>
              <td class="py-2.5 px-3 font-sans text-slate-600 text-[11px]">
                {{ mes.eje_lider }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Narrativa de Evolución Temporal -->
      <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
        <strong>Análisis de Ritmo Comunicacional:</strong> {{ reporte.narrativa.narrativa_evolucion }}
      </p>
    </section>
  </div>
</template>
