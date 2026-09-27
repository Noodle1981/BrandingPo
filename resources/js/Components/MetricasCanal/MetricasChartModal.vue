<script setup>
import { ref } from 'vue';
import {
  LineChart as LineChartIcon,
  X,
  Users,
  Flame,
  Calendar,
  Layers,
  DollarSign
} from '@lucide/vue';
import { Line, Bar, Doughnut } from 'vue-chartjs';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  perfilSocial: {
    type: Object,
    required: true,
  },
  getSocialMeta: {
    type: Function,
    required: true,
  },
  formatNumber: {
    type: Function,
    required: true,
  },
  historicoMediciones: {
    type: Array,
    default: () => [],
  },
  consistenciaMensual: {
    type: Array,
    default: () => [],
  },
  rendimientoPorFormato: {
    type: Array,
    default: () => [],
  },
  organicoVsPauta: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  benchmarks: {
    type: Object,
    default: () => ({}),
  },
  frecuenciaPublicacion: {
    type: Object,
    default: () => ({}),
  },
  audienciaChartData: {
    type: Object,
    required: true,
  },
  audienciaChartOptions: {
    type: Object,
    required: true,
  },
  interaccionesChartData: {
    type: Object,
    required: true,
  },
  interaccionesChartOptions: {
    type: Object,
    required: true,
  },
  cadenciaChartData: {
    type: Object,
    required: true,
  },
  cadenciaChartOptions: {
    type: Object,
    required: true,
  },
  formatosChartData: {
    type: Object,
    required: true,
  },
  formatosChartOptions: {
    type: Object,
    required: true,
  },
  pautaChartData: {
    type: Object,
    required: true,
  },
  pautaChartOptions: {
    type: Object,
    required: true,
  },
});

defineEmits(['close']);

const activeChartTab = ref('audiencia');
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm"
    @click.self="$emit('close')"
  >
    <div class="w-full max-w-5xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 max-h-[92vh] overflow-y-auto">
      <!-- Header Modal -->
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-cyan-500/15 text-cyan-500 flex items-center justify-center">
            <LineChartIcon class="w-5 h-5" />
          </div>
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">
              Centro de Inteligencia Visual: {{ getSocialMeta(perfilSocial.plataforma).name }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Gráficos evolutivos, cadencia, interacción y mix de contenidos de {{ perfilSocial.handle_usuario }}
            </p>
          </div>
        </div>

        <button
          type="button"
          @click="$emit('close')"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Pestañas de Gráficos (5 Ejes Estratégicos) -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-100 dark:border-slate-800 scrollbar-thin">
        <button
          type="button"
          @click="activeChartTab = 'audiencia'"
          class="px-3.5 py-2 rounded-xl font-mono text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer"
          :class="activeChartTab === 'audiencia'
            ? 'bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/20'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          <Users class="w-3.5 h-3.5" />
          <span>Audiencia & Seguidores</span>
        </button>

        <button
          type="button"
          @click="activeChartTab = 'interacciones'"
          class="px-3.5 py-2 rounded-xl font-mono text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer"
          :class="activeChartTab === 'interacciones'
            ? 'bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/20'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          <Flame class="w-3.5 h-3.5" />
          <span>Interacciones & Engagement</span>
        </button>

        <button
          type="button"
          @click="activeChartTab = 'cadencia'"
          class="px-3.5 py-2 rounded-xl font-mono text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer"
          :class="activeChartTab === 'cadencia'
            ? 'bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/20'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          <Calendar class="w-3.5 h-3.5" />
          <span>Cadencia de Posts</span>
        </button>

        <button
          type="button"
          @click="activeChartTab = 'formatos'"
          class="px-3.5 py-2 rounded-xl font-mono text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer"
          :class="activeChartTab === 'formatos'
            ? 'bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/20'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          <Layers class="w-3.5 h-3.5" />
          <span>Mix de Formatos</span>
        </button>

        <button
          type="button"
          @click="activeChartTab = 'pauta'"
          class="px-3.5 py-2 rounded-xl font-mono text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap cursor-pointer"
          :class="activeChartTab === 'pauta'
            ? 'bg-cyan-500 text-slate-950 shadow-sm shadow-cyan-500/20'
            : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
        >
          <DollarSign class="w-3.5 h-3.5" />
          <span>Pauta vs Orgánico</span>
        </button>
      </div>

      <!-- Canvas del Gráfico Dinámico -->
      <div class="w-full h-80 sm:h-96 relative bg-slate-50 dark:bg-slate-950 rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
        <!-- Tab 1: Audiencia -->
        <Line
          v-if="activeChartTab === 'audiencia' && historicoMediciones.length > 0"
          :data="audienciaChartData"
          :options="audienciaChartOptions"
        />

        <!-- Tab 2: Interacciones -->
        <Bar
          v-else-if="activeChartTab === 'interacciones' && consistenciaMensual.length > 0"
          :data="interaccionesChartData"
          :options="interaccionesChartOptions"
        />

        <!-- Tab 3: Cadencia -->
        <Bar
          v-else-if="activeChartTab === 'cadencia' && consistenciaMensual.length > 0"
          :data="cadenciaChartData"
          :options="cadenciaChartOptions"
        />

        <!-- Tab 4: Formatos -->
        <Doughnut
          v-else-if="activeChartTab === 'formatos' && rendimientoPorFormato.length > 0"
          :data="formatosChartData"
          :options="formatosChartOptions"
        />

        <!-- Tab 5: Pauta -->
        <Bar
          v-else-if="activeChartTab === 'pauta' && consistenciaMensual.length > 0"
          :data="pautaChartData"
          :options="pautaChartOptions"
        />

        <!-- Fallback Sin Datos -->
        <div v-else class="h-full flex items-center justify-center text-slate-400 font-mono text-xs">
          No hay suficientes datos registrados para graficar esta pestaña.
        </div>
      </div>

      <!-- Resumen Rápido Contextual -->
      <div v-if="activeChartTab === 'audiencia'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Punto Alfa Inicial</span>
          <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 block mt-0.5">
            {{ formatNumber(stats.seguidores_punto_cero) }} seg.
          </span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Seguidores Actuales</span>
          <span class="text-base font-extrabold text-cyan-500 block mt-0.5">
            {{ formatNumber(stats.seguidores_actuales) }} seg.
          </span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Crecimiento Neto</span>
          <span class="text-base font-extrabold block mt-0.5" :class="stats.crecimiento_neto_seguidores >= 0 ? 'text-emerald-500' : 'text-rose-500'">
            {{ stats.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ formatNumber(stats.crecimiento_neto_seguidores) }} ({{ stats.crecimiento_pct_seguidores }}%)
          </span>
        </div>
      </div>

      <div v-else-if="activeChartTab === 'interacciones'" class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-xs">
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">❤️ Total Likes</span>
          <span class="text-base font-extrabold text-rose-500 block mt-0.5">{{ formatNumber(stats.total_likes) }}</span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">💬 Comentarios</span>
          <span class="text-base font-extrabold text-cyan-500 block mt-0.5">{{ formatNumber(stats.total_comentarios) }}</span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">✈️ Compartidos</span>
          <span class="text-base font-extrabold text-amber-500 block mt-0.5">{{ formatNumber(stats.total_compartidos + (stats.total_republicados || 0)) }}</span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">⚡ Impact Score</span>
          <span class="text-base font-extrabold text-emerald-500 block mt-0.5">{{ formatNumber(stats.score_impacto_total) }} pts</span>
        </div>
      </div>

      <div v-else-if="activeChartTab === 'cadencia'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Publicaciones</span>
          <span class="text-base font-extrabold text-cyan-500 block mt-0.5">{{ formatNumber(stats.posts_actuales) }} posts</span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Meta Mensual</span>
          <span class="text-base font-extrabold text-amber-500 block mt-0.5">≥ {{ benchmarks.posts_semana_ideal * 4 }} posts/mes</span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Frecuencia Promedio</span>
          <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 block mt-0.5">~{{ frecuenciaPublicacion.promedio_semanal }} posts/sem</span>
        </div>
      </div>

      <div v-else-if="activeChartTab === 'formatos'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Formatos Utilizados</span>
          <span class="text-base font-extrabold text-cyan-500 block mt-0.5">{{ rendimientoPorFormato.length }} tipos</span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Formato Más Usado</span>
          <span class="text-base font-extrabold text-emerald-500 block mt-0.5">
            {{ rendimientoPorFormato[0]?.tipo_formato || 'N/A' }} ({{ rendimientoPorFormato[0]?.cantidad_posts || 0 }} posts)
          </span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Mejor Rendimiento</span>
          <span class="text-base font-extrabold text-amber-500 block mt-0.5">
            ~{{ formatNumber(rendimientoPorFormato[0]?.promedio_interacciones || 0) }} reacc/post
          </span>
        </div>
      </div>

      <div v-else-if="activeChartTab === 'pauta'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Invertido en Pauta</span>
          <span class="text-base font-extrabold text-violet-400 block mt-0.5">${{ formatNumber(organicoVsPauta.monto_total_pauta || 0) }}</span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Mix Orgánico vs Pauta</span>
          <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 block mt-0.5">
            {{ organicoVsPauta.organicos_count }} org. / {{ organicoVsPauta.pauta_count }} impulsados
          </span>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <span class="text-[10px] text-slate-400 uppercase font-bold block">Multiplicador de Tracción</span>
          <span class="text-base font-extrabold text-emerald-500 block mt-0.5">
            x{{ organicoVsPauta.multiplicador_pauta }} tracción
          </span>
        </div>
      </div>

      <!-- Footer Modal -->
      <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
        <button
          type="button"
          @click="$emit('close')"
          class="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-800 text-white font-bold text-xs font-mono transition-all cursor-pointer hover:bg-slate-800"
        >
          Cerrar Gráfico
        </button>
      </div>
    </div>
  </div>
</template>
