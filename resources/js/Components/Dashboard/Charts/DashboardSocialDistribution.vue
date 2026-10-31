<script setup>
import { PieChart, BarChart3, Layers, Maximize2 } from '@lucide/vue';
import { Doughnut, Bar } from 'vue-chartjs';
import SocialPlatformIcon from '../../SocialPlatformIcon.vue';
import { useFormatters } from '../../../composables/useFormatters';

const props = defineProps({
  doughnutChartData: {
    type: Object,
    required: true
  },
  doughnutChartOptions: {
    type: Object,
    required: true
  },
  distribucionPlataformas: {
    type: Array,
    default: () => []
  },
  formatBarChartData: {
    type: Object,
    required: true
  },
  formatBarChartOptions: {
    type: Object,
    required: true
  },
  matrizFormatosBarChartData: {
    type: Object,
    required: true
  },
  matrizFormatosBarChartOptions: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['abrir-modal']);
const { formatNumber } = useFormatters();
</script>

<template>
  <div class="space-y-6">
    <!-- 2. CUOTA DE ATENCIÓN POR RED + RENDIMIENTO POR FORMATO -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <!-- Donut: Cuota de Interacción por Red -->
      <div class="lg:col-span-5 p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4 flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2">
          <div>
            <div class="flex items-center gap-2">
              <PieChart class="w-4 h-4 text-violet-500" />
              <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100">
                <span>Cuota de Interacción por Red</span>
              </h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Distribución de likes, comentarios y compartidos</p>
          </div>

          <button
            type="button"
            @click="emit('abrir-modal', 'cuota')"
            class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer shrink-0"
            title="Ampliar gráfico"
          >
            <Maximize2 class="w-3.5 h-3.5" />
            <span class="text-[11px]">Ampliar</span>
          </button>
        </div>

        <div class="h-56 w-full relative flex items-center justify-center">
          <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
        </div>

        <div v-if="distribucionPlataformas.length > 0" class="flex flex-wrap items-stretch justify-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-800/80 font-mono">
          <div
            v-for="red in distribucionPlataformas"
            :key="red.plataforma"
            class="flex-1 min-w-[95px] max-w-[150px] p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800 flex flex-col justify-between"
          >
            <div class="flex items-center justify-between gap-1 mb-1">
              <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold truncate flex items-center gap-1">
                <SocialPlatformIcon :platform="red.plataforma" size="xs" />
                <span :class="{ 'text-slate-900 dark:text-white font-black': red.plataforma === 'threads' || red.plataforma === 'x_twitter' }">
                  {{ red.nombre }}
                </span>
              </span>
              <span
                class="w-1.5 h-1.5 rounded-full shrink-0"
                :class="{ 'bg-slate-900 dark:bg-white ring-1 ring-slate-400/50': red.plataforma === 'threads' || red.plataforma === 'x_twitter' }"
                :style="{ backgroundColor: (red.plataforma === 'threads' || red.plataforma === 'x_twitter') ? '' : red.color }"
              ></span>
            </div>
            <div class="flex items-baseline justify-between gap-1">
              <span
                class="text-xs sm:text-sm font-extrabold tracking-tight"
                :class="{ 'text-slate-900 dark:text-white': red.plataforma === 'threads' || red.plataforma === 'x_twitter' }"
                :style="{ color: (red.plataforma === 'threads' || red.plataforma === 'x_twitter') ? '' : red.color }"
              >
                {{ red.porcentaje }}%
              </span>
              <span class="text-[9px] text-slate-400 truncate" :title="`${red.interacciones} interacciones totales`">
                {{ formatNumber(red.interacciones) }} int.
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Rendimiento por Formato -->
      <div class="lg:col-span-7 p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <BarChart3 class="w-4 h-4 text-cyan-500" />
              <span>Rendimiento por Formato</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Efectividad promedio de Reels vs Fotos vs Videos vs Texto</p>
          </div>

          <button
            type="button"
            @click="emit('abrir-modal', 'formato')"
            class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer shrink-0"
            title="Ampliar gráfico"
          >
            <Maximize2 class="w-3.5 h-3.5" />
            <span class="text-[11px]">Ampliar</span>
          </button>
        </div>

        <div class="h-64 w-full">
          <Bar :data="formatBarChartData" :options="formatBarChartOptions" />
        </div>
      </div>
    </div>

    <!-- 3. MATRIZ DE FORMATOS EN BARRAS -->
    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
      <div class="flex items-center justify-between gap-2">
        <div>
          <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Layers class="w-4 h-4 text-cyan-500" />
            <span>Matriz de Formatos por Red Social Activa</span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">Distribución de posts publicados por plataforma y formato</p>
        </div>

        <button
          type="button"
          @click="emit('abrir-modal', 'matriz_formatos')"
          class="px-2.5 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1.5 cursor-pointer shrink-0"
          title="Ampliar gráfico"
        >
          <Maximize2 class="w-3.5 h-3.5" />
          <span>Ampliar</span>
        </button>
      </div>

      <div class="h-64 w-full">
        <Bar :data="matrizFormatosBarChartData" :options="matrizFormatosBarChartOptions" />
      </div>
    </div>
  </div>
</template>
