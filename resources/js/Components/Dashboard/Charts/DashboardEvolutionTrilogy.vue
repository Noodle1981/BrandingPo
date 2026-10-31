<script setup>
import { Users, Flame, Eye, Maximize2 } from '@lucide/vue';
import { Line } from 'vue-chartjs';

const props = defineProps({
  comunidadChartData: {
    type: Object,
    required: true
  },
  comunidadChartOptions: {
    type: Object,
    required: true
  },
  scoreChartData: {
    type: Object,
    required: true
  },
  scoreChartOptions: {
    type: Object,
    required: true
  },
  vistasChartData: {
    type: Object,
    required: true
  },
  vistasChartOptions: {
    type: Object,
    required: true
  },
  redesDisponiblesTimeline: {
    type: Array,
    default: () => []
  },
  redesConVistasDisponibles: {
    type: Array,
    default: () => []
  },
  selectedComunidadPlatform: {
    type: String,
    default: 'todas'
  },
  agrupacionTemporalComunidad: {
    type: String,
    default: 'semana'
  },
  selectedScorePlatform: {
    type: String,
    default: 'todas'
  },
  agrupacionTemporalScore: {
    type: String,
    default: 'semana'
  },
  selectedVistasPlatform: {
    type: String,
    default: 'todas'
  },
  agrupacionTemporalVistas: {
    type: String,
    default: 'semana'
  }
});

const emit = defineEmits([
  'abrir-modal',
  'update:selectedComunidadPlatform',
  'update:agrupacionTemporalComunidad',
  'update:selectedScorePlatform',
  'update:agrupacionTemporalScore',
  'update:selectedVistasPlatform',
  'update:agrupacionTemporalVistas'
]);
</script>

<template>
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <!-- GRÁFICA 1: COMUNIDAD (SEGUIDORES NETOS) -->
    <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5 flex flex-col justify-between">
      <div class="space-y-2">
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
              <Users class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Evolución de Comunidad</h3>
              <p class="text-[11px] text-slate-400">Seguidores netos acumulados</p>
            </div>
          </div>

          <div class="flex items-center gap-1.5 flex-wrap">
            <!-- Selector de Agrupación Temporal -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] font-mono">
              <button
                type="button"
                @click="emit('update:agrupacionTemporalComunidad', 'semana')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalComunidad === 'semana'
                    ? 'bg-cyan-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Agrupar por semana (Cierre dominical)"
              >
                Semana
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalComunidad', 'mes')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalComunidad === 'mes'
                    ? 'bg-cyan-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Agrupar por mes calendario"
              >
                Mes
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalComunidad', 'dia')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalComunidad === 'dia'
                    ? 'bg-cyan-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Mediciones diarias detalladas"
              >
                Día
              </button>
            </div>

            <select
              :value="selectedComunidadPlatform"
              @change="emit('update:selectedComunidadPlatform', $event.target.value)"
              class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-500"
            >
              <option value="todas">🌐 Todas (Multilínea)</option>
              <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                {{ red.plataforma.toUpperCase() }}
              </option>
            </select>

            <button
              type="button"
              @click="emit('abrir-modal', 'comunidad')"
              class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer"
              title="Ampliar gráfico"
            >
              <Maximize2 class="w-3.5 h-3.5" />
              <span class="text-[11px]">Ampliar</span>
            </button>
          </div>
        </div>
      </div>

      <div class="h-60 w-full">
        <Line :data="comunidadChartData" :options="comunidadChartOptions" />
      </div>
    </div>

    <!-- GRÁFICA 2: TRACCIÓN / SCORE DE IMPACTO PONDERADO -->
    <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5 flex flex-col justify-between">
      <div class="space-y-2">
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <Flame class="w-4 h-4 fill-current" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tracción (Score / Puntos)</h3>
              <p class="text-[11px] text-slate-400">Likes (1), Comments (3), Shares (5), Reposts (10)</p>
            </div>
          </div>

          <div class="flex items-center gap-1.5 flex-wrap">
            <!-- Selector de Agrupación Temporal -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] font-mono">
              <button
                type="button"
                @click="emit('update:agrupacionTemporalScore', 'semana')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalScore === 'semana'
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Agrupar por semana (Cierre dominical)"
              >
                Semana
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalScore', 'mes')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalScore === 'mes'
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Agrupar por mes calendario"
              >
                Mes
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalScore', 'dia')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalScore === 'dia'
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Mediciones diarias detalladas"
              >
                Día
              </button>
            </div>

            <select
              :value="selectedScorePlatform"
              @change="emit('update:selectedScorePlatform', $event.target.value)"
              class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500"
            >
              <option value="todas">🌐 Todas (Total)</option>
              <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                {{ red.plataforma.toUpperCase() }}
              </option>
            </select>

            <button
              type="button"
              @click="emit('abrir-modal', 'score')"
              class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer"
              title="Ampliar gráfico"
            >
              <Maximize2 class="w-3.5 h-3.5" />
              <span class="text-[11px]">Ampliar</span>
            </button>
          </div>
        </div>
      </div>

      <div class="h-60 w-full">
        <Line :data="scoreChartData" :options="scoreChartOptions" />
      </div>
    </div>

    <!-- GRÁFICA 3: REPRODUCCIONES / VIDEO & REELS 👁️ -->
    <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5 flex flex-col justify-between">
      <div class="space-y-2">
        <div class="flex items-center justify-between gap-2 flex-wrap">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <Eye class="w-4 h-4" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Visualizaciones / Reels</h3>
              <p class="text-[11px] text-slate-400">Reproducciones de video multired</p>
            </div>
          </div>

          <div class="flex items-center gap-1.5 flex-wrap">
            <!-- Selector de Agrupación Temporal -->
            <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] font-mono">
              <button
                type="button"
                @click="emit('update:agrupacionTemporalVistas', 'semana')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalVistas === 'semana'
                    ? 'bg-emerald-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Agrupar por semana (Cierre dominical)"
              >
                Semana
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalVistas', 'mes')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalVistas === 'mes'
                    ? 'bg-emerald-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Agrupar por mes calendario"
              >
                Mes
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalVistas', 'dia')"
                :class="[
                  'px-2 py-0.5 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalVistas === 'dia'
                    ? 'bg-emerald-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
                title="Mediciones diarias detalladas"
              >
                Día
              </button>
            </div>

            <select
              :value="selectedVistasPlatform"
              @change="emit('update:selectedVistasPlatform', $event.target.value)"
              class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            >
              <option value="todas">🌐 Todas (Multilínea)</option>
              <option v-for="red in redesConVistasDisponibles" :key="red.plataforma" :value="red.plataforma">
                {{ red.plataforma.toUpperCase() }}
              </option>
            </select>

            <button
              type="button"
              @click="emit('abrir-modal', 'vistas')"
              class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer"
              title="Ampliar gráfico"
            >
              <Maximize2 class="w-3.5 h-3.5" />
              <span class="text-[11px]">Ampliar</span>
            </button>
          </div>
        </div>
      </div>

      <div class="h-60 w-full">
        <Line :data="vistasChartData" :options="vistasChartOptions" />
      </div>
    </div>
  </div>
</template>
