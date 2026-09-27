<script setup>
import { Zap } from '@lucide/vue';

const props = defineProps({
  organicoVsPauta: {
    type: Object,
    required: true,
  },
  formatNumber: {
    type: Function,
    required: true,
  },
  formatCurrency: {
    type: Function,
    required: true,
  },
});
</script>

<template>
  <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
      <div>
        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Zap class="w-5 h-5 text-cyan-500" />
          <span>Desglose Estratégico: Tracción Orgánica vs Pauta Publicitaria</span>
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Evalúa la dependencia de presupuesto y el retorno de interacciones de la pauta.
        </p>
      </div>
      <div class="flex items-center gap-3 text-xs font-mono">
        <span class="flex items-center gap-1.5 text-cyan-600 dark:text-cyan-400 font-bold">
          <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
          Orgánico: {{ organicoVsPauta.pct_interacciones_organicas }}%
        </span>
        <span class="flex items-center gap-1.5 text-violet-500 font-bold">
          <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
          Pauta: {{ organicoVsPauta.pct_interacciones_pautadas }}%
        </span>
      </div>
    </div>

    <!-- Barra Visual Comparativa de Interacciones -->
    <div class="space-y-1.5">
      <div class="w-full h-3.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex shadow-inner">
        <div
          class="h-full bg-cyan-500 transition-all duration-500"
          :style="{ width: `${organicoVsPauta.pct_interacciones_organicas}%` }"
          :title="`Orgánico: ${organicoVsPauta.pct_interacciones_organicas}%`"
        ></div>
        <div
          class="h-full bg-violet-500 transition-all duration-500"
          :style="{ width: `${organicoVsPauta.pct_interacciones_pautadas}%` }"
          :title="`Pauta: ${organicoVsPauta.pct_interacciones_pautadas}%`"
        ></div>
      </div>
      <div class="flex justify-between text-[11px] font-mono text-slate-400">
        <span>{{ formatNumber(organicoVsPauta.interacciones_organicas) }} interacciones naturales</span>
        <span>{{ formatNumber(organicoVsPauta.interacciones_pautadas) }} interacciones con pauta</span>
      </div>
    </div>

    <!-- 2 Columnas Side by Side -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Columna Orgánica -->
      <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-cyan-500/15 text-cyan-500 flex items-center justify-center font-bold text-xs">
              🌱
            </div>
            <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
              Rendimiento Orgánico Puro
            </h4>
          </div>
          <span class="text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400">
            {{ organicoVsPauta.total_posts_organicos }} publicaciones
          </span>
        </div>

        <div class="grid grid-cols-2 gap-3 font-mono text-xs">
          <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
            <span class="text-[10px] text-slate-400 uppercase block font-bold">Interacciones</span>
            <span class="text-base font-extrabold text-cyan-500 mt-0.5 block">
              {{ formatNumber(organicoVsPauta.interacciones_organicas) }}
            </span>
            <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
              Promedio: {{ organicoVsPauta.promedio_int_organico }} / post
            </span>
          </div>
          <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
            <span class="text-[10px] text-slate-400 uppercase block font-bold">Vistas Naturales</span>
            <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block">
              {{ formatNumber(organicoVsPauta.vistas_organicas) }}
            </span>
            <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
              Alcance espontáneo
            </span>
          </div>
        </div>
      </div>

      <!-- Columna Pauta Pagada -->
      <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-violet-500/15 text-violet-500 flex items-center justify-center font-bold text-xs">
              📢
            </div>
            <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
              Pauta & Ads Impulsados
            </h4>
          </div>
          <span class="text-xs font-mono font-bold text-violet-500">
            {{ organicoVsPauta.total_posts_pautados }} publicaciones con pauta
          </span>
        </div>

        <div class="grid grid-cols-2 gap-3 font-mono text-xs">
          <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
            <span class="text-[10px] text-slate-400 uppercase block font-bold">Inversión Canal</span>
            <span class="text-base font-extrabold text-violet-500 mt-0.5 block">
              {{ formatCurrency(organicoVsPauta.inversion_total) }}
            </span>
            <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
              Costo/Int: ${{ organicoVsPauta.costo_por_interaccion }}
            </span>
          </div>
          <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
            <span class="text-[10px] text-slate-400 uppercase block font-bold">Interacciones Ads</span>
            <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block">
              {{ formatNumber(organicoVsPauta.interacciones_pautadas) }}
            </span>
            <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
              ROI: {{ organicoVsPauta.roi_interacciones_por_peso }} int / $
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
