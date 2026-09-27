<script setup>
import { Layers, Award, Play } from '@lucide/vue';

const props = defineProps({
  rendimientoPorFormato: {
    type: Array,
    default: () => [],
  },
  promedioVistasInfo: {
    type: Object,
    default: () => ({}),
  },
  perfilSocial: {
    type: Object,
    required: true,
  },
  formatNumber: {
    type: Function,
    required: true,
  },
  getSocialMeta: {
    type: Function,
    required: true,
  },
  getFormatoIcon: {
    type: Function,
    required: true,
  },
});
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Rendimiento por Formato (2 Cols) -->
    <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
      <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Layers class="w-5 h-5 text-cyan-500" />
            <span>Rendimiento por Formato de Contenido</span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Compara la efectividad de Reels, Fotos, Carruseles y Videos.
          </p>
        </div>
      </div>

      <div v-if="rendimientoPorFormato.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div
          v-for="(f, idx) in rendimientoPorFormato"
          :key="f.formato"
          class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2 relative overflow-hidden"
        >
          <div v-if="idx === 0 && f.cantidad > 0" class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-500 text-[10px] font-mono font-black flex items-center gap-1 border border-amber-500/30">
            <Award class="w-3 h-3" />
            <span>Top Formato</span>
          </div>

          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
              <component :is="getFormatoIcon(f.formato)" class="w-4 h-4" />
            </div>
            <div>
              <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase font-mono">
                {{ f.formato }}
              </h4>
              <span class="text-[11px] font-mono text-slate-400">
                {{ f.cantidad }} publicaciones
              </span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/80 font-mono text-xs">
            <div>
              <span class="text-[10px] text-slate-400 block uppercase">Prom. Interacciones</span>
              <span class="font-extrabold text-cyan-600 dark:text-cyan-400">
                🔥 {{ formatNumber(f.promedio_interacciones) }}
              </span>
            </div>
            <div v-if="f.promedio_vistas > 0">
              <span class="text-[10px] text-slate-400 block uppercase">Prom. Vistas</span>
              <span class="font-extrabold text-slate-800 dark:text-slate-200">
                👀 {{ formatNumber(f.promedio_vistas) }}
              </span>
            </div>
            <div v-else>
              <span class="text-[10px] text-slate-400 block uppercase">Likes Totales</span>
              <span class="font-extrabold text-slate-800 dark:text-slate-200">
                ❤️ {{ formatNumber(f.total_likes) }}
              </span>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="p-6 text-center text-xs font-mono text-slate-400">
        No hay publicaciones clasificadas por formato aún.
      </div>
    </div>

    <!-- KPI Promedio de Vistas en Reels/Videos (1 Col) -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 flex flex-col justify-between">
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Benchmark en Reels</span>
          <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
            <Play class="w-4 h-4 fill-current" />
          </div>
        </div>

        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
          Vistas Promedio por Reel / Video
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
          En {{ getSocialMeta(perfilSocial.plataforma).name }}, cada video debe alcanzar al menos el <strong>{{ promedioVistasInfo.ratio_esperado_pct }}%</strong> de tus seguidores actuales.
        </p>
      </div>

      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
        <div class="flex items-baseline justify-between font-mono">
          <span class="text-2xl font-black" :class="promedioVistasInfo.cumple_benchmark ? 'text-emerald-500' : 'text-amber-500'">
            {{ formatNumber(promedioVistasInfo.promedio_vistas_real) }}
          </span>
          <span class="text-xs font-bold" :class="promedioVistasInfo.cumple_benchmark ? 'text-emerald-500' : 'text-amber-500'">
            {{ promedioVistasInfo.ratio_cumplimiento }}% de la meta
          </span>
        </div>

        <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
          <div
            class="h-full transition-all duration-500"
            :class="promedioVistasInfo.cumple_benchmark ? 'bg-emerald-500' : 'bg-amber-500'"
            :style="{ width: `${Math.min(100, promedioVistasInfo.ratio_cumplimiento)}%` }"
          ></div>
        </div>

        <div class="flex justify-between text-[11px] font-mono text-slate-400">
          <span>Benchmark: ≥ {{ formatNumber(promedioVistasInfo.vistas_esperadas_benchmark) }} vistas</span>
          <span>{{ promedioVistasInfo.total_reels }} videos</span>
        </div>
      </div>

      <div class="text-[11px] text-slate-400 font-mono">
        <span>{{ promedioVistasInfo.cumple_benchmark ? '✅ Excelente tracción algorítmica.' : '⚠️ Recomendación: optimizar hook en los primeros 3 segundos.' }}</span>
      </div>
    </div>
  </div>
</template>
