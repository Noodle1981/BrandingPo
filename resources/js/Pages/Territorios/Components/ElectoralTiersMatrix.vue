<script setup>
import { Layers } from '@lucide/vue';

defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  formatNumber: {
    type: Function,
    required: true
  }
});
</script>

<template>
  <!-- 🏛️ MATRIZ DE TIERS & DESDUPLICACIÓN DE AUDIENCIA MULTICANAL -->
  <div v-if="stats.tiers_desglose && stats.tiers_desglose.length" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
      <div>
        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Layers class="w-4 h-4 text-cyan-500" />
          <span>Matriz de TIERS: Desduplicación de Audiencia Única</span>
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Desglose de electores reales no repetidos por canal. Total Bruto: <strong class="text-slate-700 dark:text-slate-200 font-mono">{{ formatNumber(stats.total_seguidores_bruto) }}</strong> cuentas vs. <strong class="text-cyan-500 font-mono">{{ formatNumber(stats.total_seguidores_comunidad) }}</strong> electores netos únicos.
        </p>
      </div>
      <span class="text-xs font-bold font-mono px-2.5 py-1 rounded-xl bg-cyan-500/10 text-cyan-500 border border-cyan-500/20 shrink-0">
        Penetración Neta: {{ stats.penetracion_padron_pct }}% del Padrón
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
      <div
        v-for="(t, idx) in stats.tiers_desglose"
        :key="idx"
        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 space-y-3"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-extrabold font-mono text-cyan-500 uppercase">
            {{ t.nombre }}
          </span>
          <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
            {{ t.factor_incremental_pct }}% incremental
          </span>
        </div>

        <div class="flex items-center justify-between text-sm">
          <div class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-slate-100 capitalize">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <span>{{ t.plataforma }}</span>
          </div>
          <span class="font-mono text-xs text-slate-400">
            {{ t.handle || '@' + t.plataforma }}
          </span>
        </div>

        <div class="space-y-1.5 pt-2 border-t border-slate-200/60 dark:border-slate-800/60 font-mono text-xs">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span>Cuentas brutas:</span>
            <span class="font-bold text-slate-700 dark:text-slate-300">{{ formatNumber(t.seguidores_brutos) }}</span>
          </div>
          <div class="flex items-center justify-between text-cyan-600 dark:text-cyan-400 font-semibold">
            <span>Electores únicos aportados:</span>
            <span class="font-black text-slate-900 dark:text-slate-100">+{{ formatNumber(t.seguidores_unicos) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
