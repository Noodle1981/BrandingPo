<script setup>
import { PieChart as PieIcon, Layers, Compass } from '@lucide/vue';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  pasteles: {
    type: Object,
    default: () => ({})
  },
  formatNumber: {
    type: Function,
    required: true
  }
});

// Helper para generar coordenadas SVG de donut charts
const getDonutSlices = (items) => {
  if (!items || !items.length) return [];
  const total = items.reduce((sum, item) => sum + Number(item.valor || 0), 0);
  if (total === 0) return [];

  let accumulatedAngle = 0;
  return items.map(item => {
    const fraction = Number(item.valor || 0) / total;
    const angle = fraction * 360;
    const startAngle = accumulatedAngle;
    accumulatedAngle += angle;

    return {
      ...item,
      color: item.color,
      strokeDasharray: `${fraction * 251.2} 251.2`,
      strokeDashoffset: `${-(startAngle / 360) * 251.2}`,
    };
  });
};
</script>

<template>
  <!-- 🥧 GRÁFICOS DE PASTEL (DONUT CHARTS) INTERACTIVOS -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <!-- Pastel 1: Penetración en el Padrón -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
      <div>
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <PieIcon class="w-4 h-4 text-cyan-500" />
            <span>Comunidad vs. Padrón</span>
          </h3>
          <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-500">
            {{ stats.penetracion_padron_pct }}% Cobertura
          </span>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">
          Porción del padrón electoral alcanzada por seguidores únicos.
        </p>
      </div>

      <!-- Donut SVG -->
      <div class="flex items-center justify-center relative py-2">
        <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="38" fill="transparent" stroke="currentColor" stroke-width="14" class="text-slate-100 dark:text-slate-800" />
          <circle
            cx="50"
            cy="50"
            r="38"
            fill="transparent"
            stroke="#06b6d4"
            stroke-width="14"
            :stroke-dasharray="`${(stats.penetracion_padron_pct * 238.76) / 100} 238.76`"
            stroke-dashoffset="0"
            class="transition-all duration-1000"
          />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span class="text-xl font-black font-mono text-slate-900 dark:text-slate-100">
            {{ stats.penetracion_padron_pct }}%
          </span>
          <span class="text-[10px] font-mono text-slate-400 uppercase">del padrón</span>
        </div>
      </div>

      <!-- Leyendas -->
      <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 font-mono text-xs">
        <div class="flex items-center justify-between">
          <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
            <span>Comunidad Única:</span>
          </span>
          <span class="font-bold text-slate-900 dark:text-slate-100">
            {{ formatNumber(stats.total_seguidores_comunidad) }} electores
          </span>
        </div>
        <div class="flex items-center justify-between">
          <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
            <span>Padrón Restante:</span>
          </span>
          <span class="font-bold text-slate-500">
            {{ formatNumber(Math.max(0, stats.padron_electoral - stats.total_seguidores_comunidad)) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Pastel 2: Distribución por Red Social -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
      <div>
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Layers class="w-4 h-4 text-cyan-500" />
            <span>Distribución por Canal</span>
          </h3>
          <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-500">
            {{ pasteles?.redes?.length || 0 }} Redes
          </span>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">
          Participación de cada red en el total de la comunidad.
        </p>
      </div>

      <!-- Donut SVG Multi-Slice -->
      <div class="flex items-center justify-center relative py-2">
        <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="38" fill="transparent" stroke="currentColor" stroke-width="14" class="text-slate-100 dark:text-slate-800" />
          <circle
            v-for="(slice, i) in getDonutSlices(pasteles?.redes)"
            :key="i"
            cx="50"
            cy="50"
            r="38"
            fill="transparent"
            :stroke="slice.color"
            stroke-width="14"
            :stroke-dasharray="slice.strokeDasharray"
            :stroke-dashoffset="slice.strokeDashoffset"
            class="transition-all duration-1000"
          />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span class="text-base font-black font-mono text-slate-900 dark:text-slate-100">
            {{ formatNumber(stats.total_seguidores_comunidad) }}
          </span>
          <span class="text-[9px] font-mono text-slate-400 uppercase">seguidores</span>
        </div>
      </div>

      <!-- Leyendas de Redes -->
      <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 font-mono text-xs">
        <div
          v-for="(red, i) in pasteles?.redes"
          :key="i"
          class="flex items-center justify-between"
        >
          <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: red.color }"></span>
            <span>{{ red.label }}:</span>
          </span>
          <span class="font-bold text-slate-900 dark:text-slate-100">
            {{ formatNumber(red.valor) }} <span class="text-slate-400 font-normal">({{ red.porcentaje }}%)</span>
          </span>
        </div>
        <div v-if="!pasteles?.redes?.length" class="text-slate-400 text-center py-1">
          Configura tus canales en Mi Candidato
        </div>
      </div>
    </div>

    <!-- Pastel 3: Núcleo Duro vs Expansión por Pauta vs Silencioso -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
      <div>
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Compass class="w-4 h-4 text-emerald-500" />
            <span>Estructura del Electorado</span>
          </h3>
          <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500">
            Núcleo vs. Pauta
          </span>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">
          Militancia habitual vs electores alcanzados por pauta.
        </p>
      </div>

      <!-- Donut SVG -->
      <div class="flex items-center justify-center relative py-2">
        <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="38" fill="transparent" stroke="currentColor" stroke-width="14" class="text-slate-100 dark:text-slate-800" />
          <circle
            v-for="(slice, i) in getDonutSlices(pasteles?.electorado)"
            :key="i"
            cx="50"
            cy="50"
            r="38"
            fill="transparent"
            :stroke="slice.color"
            stroke-width="14"
            :stroke-dasharray="slice.strokeDasharray"
            :stroke-dashoffset="slice.strokeDashoffset"
            class="transition-all duration-1000"
          />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
          <span class="text-xs font-black font-mono text-emerald-500">
            NÚCLEO
          </span>
          <span class="text-[10px] font-mono text-slate-400">
            {{ formatNumber(pasteles?.electorado?.[0]?.valor) }} fieles
          </span>
        </div>
      </div>

      <!-- Leyendas -->
      <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 font-mono text-xs">
        <div
          v-for="(el, i) in pasteles?.electorado"
          :key="i"
          class="flex items-center justify-between"
        >
          <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
            <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: el.color }"></span>
            <span>{{ el.label }}:</span>
          </span>
          <span class="font-bold text-slate-900 dark:text-slate-100">
            {{ formatNumber(el.valor) }} <span class="text-slate-400 font-normal">({{ el.porcentaje }}%)</span>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
