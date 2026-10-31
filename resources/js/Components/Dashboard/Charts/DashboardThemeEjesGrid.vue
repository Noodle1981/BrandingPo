<script setup>
import { Target, Sparkles, Layers, Maximize2 } from '@lucide/vue';
import { Bar } from 'vue-chartjs';

const props = defineProps({
  distribucionEjes: {
    type: Array,
    default: () => []
  },
  ejesTraccionBarChartData: {
    type: Object,
    required: true
  },
  ejesTraccionBarChartOptions: {
    type: Object,
    required: true
  },
  ejesBarChartData: {
    type: Object,
    required: true
  },
  ejesBarChartOptions: {
    type: Object,
    required: true
  },
  ejesVolumenBarChartData: {
    type: Object,
    required: true
  },
  ejesVolumenBarChartOptions: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['abrir-modal']);
</script>

<template>
  <div class="space-y-6">
    <!-- Tracción Promedio por Eje Temático (Eficiencia 0-100) -->
    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
      <div class="flex items-center justify-between gap-2">
        <div>
          <div class="flex items-center gap-2">
            <Target class="w-4 h-4 text-amber-500" />
            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100">
              <span>Tracción Promedio por Eje Temático (Eficiencia 0-100)</span>
            </h3>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Mide qué temática genera más enganche electoral relativo por publicación (Normalizado por Tier de seguidores)
          </p>
        </div>

        <button
          type="button"
          @click="emit('abrir-modal', 'ejes_traccion')"
          class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1.5 cursor-pointer shrink-0"
          title="Ampliar gráfico"
        >
          <Maximize2 class="w-3.5 h-3.5" />
          <span>Ampliar</span>
        </button>
      </div>

      <div v-if="distribucionEjes.length > 0" class="h-64 w-full">
        <Bar :data="ejesTraccionBarChartData" :options="ejesTraccionBarChartOptions" />
      </div>
      <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
        No hay publicaciones clasificadas por ejes temáticos todavía.
      </div>
    </div>

    <!-- Impacto y Volumen por Eje Temático en Grilla Dual -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <!-- Impacto por Eje Temático (Score Ponderado) -->
      <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <div class="flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-amber-500" />
              <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100">
                <span>Impacto por Eje Temático (Puntos Ponderados)</span>
              </h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Score de impacto acumulado por temática (Likes 1pt, Comments 3pts, Shares 5pts, Reposts 10pts)
            </p>
          </div>

          <button
            type="button"
            @click="emit('abrir-modal', 'ejes_impacto')"
            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1.5 cursor-pointer shrink-0"
            title="Ampliar gráfico"
          >
            <Maximize2 class="w-3.5 h-3.5" />
            <span>Ampliar</span>
          </button>
        </div>

        <div v-if="distribucionEjes.length > 0" class="h-64 w-full">
          <Bar :data="ejesBarChartData" :options="ejesBarChartOptions" />
        </div>
        <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
          No hay publicaciones clasificadas por ejes temáticos todavía.
        </div>
      </div>

      <!-- Volumen de Publicaciones por Eje Temático -->
      <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <div class="flex items-center gap-2">
              <Layers class="w-4 h-4 text-cyan-500" />
              <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100">
                <span>Volumen de Publicaciones por Eje</span>
              </h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Cantidad absoluta de posts publicados en cada eje temático
            </p>
          </div>

          <button
            type="button"
            @click="emit('abrir-modal', 'ejes_volumen')"
            class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1.5 cursor-pointer shrink-0"
            title="Ampliar gráfico"
          >
            <Maximize2 class="w-3.5 h-3.5" />
            <span>Ampliar</span>
          </button>
        </div>

        <div v-if="distribucionEjes.length > 0" class="h-64 w-full">
          <Bar :data="ejesVolumenBarChartData" :options="ejesVolumenBarChartOptions" />
        </div>
        <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
          No hay publicaciones clasificadas por ejes temáticos todavía.
        </div>
      </div>
    </div>
  </div>
</template>
