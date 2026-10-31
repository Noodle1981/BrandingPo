<script setup>
import {
  BarChart3,
  Calendar,
  X
} from '@lucide/vue';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import { useModalKeydown } from '../../../composables/useModalKeydown';

const props = defineProps({
  modalGraficoActivo: {
    type: String,
    default: null
  },
  agrupacionTemporalComunidad: {
    type: String,
    default: 'semana'
  },
  agrupacionTemporalScore: {
    type: String,
    default: 'semana'
  },
  agrupacionTemporalVistas: {
    type: String,
    default: 'semana'
  },
  comunidadChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  comunidadChartOptions: {
    type: Object,
    default: () => ({})
  },
  scoreChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  scoreChartOptions: {
    type: Object,
    default: () => ({})
  },
  scoreMensualChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  scoreMensualChartOptions: {
    type: Object,
    default: () => ({})
  },
  listaDesgloseMensual: {
    type: Array,
    default: () => []
  },
  vistasChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  vistasChartOptions: {
    type: Object,
    default: () => ({})
  },
  doughnutChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  doughnutChartOptions: {
    type: Object,
    default: () => ({})
  },
  formatBarChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  formatBarChartOptions: {
    type: Object,
    default: () => ({})
  },
  matrizFormatosBarChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  matrizFormatosBarChartOptions: {
    type: Object,
    default: () => ({})
  },
  ejesTraccionBarChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  ejesTraccionBarChartOptions: {
    type: Object,
    default: () => ({})
  },
  ejesBarChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  ejesBarChartOptions: {
    type: Object,
    default: () => ({})
  },
  ejesVolumenBarChartData: {
    type: Object,
    default: () => ({ labels: [], datasets: [] })
  },
  ejesVolumenBarChartOptions: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits([
  'close',
  'update:agrupacionTemporalComunidad',
  'update:agrupacionTemporalScore',
  'update:agrupacionTemporalVistas'
]);

const cerrar = () => {
  emit('close');
};

useModalKeydown(() => props.modalGraficoActivo !== null, cerrar);
</script>

<template>
  <Teleport to="body">
    <div
      v-if="modalGraficoActivo"
      class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-md transition-all duration-200"
      @click.self="cerrar"
    >
      <div class="w-full max-w-5xl max-h-[92vh] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Cabecera del Modal -->
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-4 bg-slate-50/50 dark:bg-slate-950/40 shrink-0">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center shrink-0">
              <BarChart3 class="w-5 h-5" />
            </div>
            <div class="min-w-0">
              <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 truncate">
                {{
                  modalGraficoActivo === 'comunidad' ? 'Evolución de Comunidad (Seguidores Netos)' :
                  modalGraficoActivo === 'score' ? 'Tracción Acumulada (Score / Puntos de Impacto)' :
                  modalGraficoActivo === 'score_mensual' ? 'Evolución y Desglose Mensual del Score de Impacto' :
                  modalGraficoActivo === 'vistas' ? 'Visualizaciones Totales (Facebook Reels & Video)' :
                  modalGraficoActivo === 'cuota' ? 'Cuota de Interacción por Red (Share of Social)' :
                  modalGraficoActivo === 'formato' ? 'Rendimiento Promedio por Formato' :
                  modalGraficoActivo === 'matriz_formatos' ? 'Matriz de Formatos por Red Social Activa' :
                  modalGraficoActivo === 'ejes_traccion' ? 'Tracción Promedio por Eje Temático (Eficiencia 0-100)' :
                  modalGraficoActivo === 'ejes_impacto' ? 'Impacto por Eje Temático (Score Ponderado Acumulado)' :
                  'Volumen de Publicaciones por Eje Temático'
                }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Visualización ampliada en alta resolución
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <!-- Selector Temporal dentro del modal para gráficos de evolución -->
            <div v-if="modalGraficoActivo === 'comunidad'" class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] font-mono">
              <button
                type="button"
                @click="emit('update:agrupacionTemporalComunidad', 'semana')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalComunidad === 'semana'
                    ? 'bg-cyan-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Semana
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalComunidad', 'mes')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalComunidad === 'mes'
                    ? 'bg-cyan-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Mes
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalComunidad', 'dia')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalComunidad === 'dia'
                    ? 'bg-cyan-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Día
              </button>
            </div>

            <div v-else-if="modalGraficoActivo === 'score'" class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] font-mono">
              <button
                type="button"
                @click="emit('update:agrupacionTemporalScore', 'semana')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalScore === 'semana'
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Semana
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalScore', 'mes')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalScore === 'mes'
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Mes
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalScore', 'dia')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalScore === 'dia'
                    ? 'bg-amber-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Día
              </button>
            </div>

            <div v-else-if="modalGraficoActivo === 'vistas'" class="inline-flex items-center p-0.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] font-mono">
              <button
                type="button"
                @click="emit('update:agrupacionTemporalVistas', 'semana')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalVistas === 'semana'
                    ? 'bg-emerald-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Semana
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalVistas', 'mes')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalVistas === 'mes'
                    ? 'bg-emerald-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Mes
              </button>
              <button
                type="button"
                @click="emit('update:agrupacionTemporalVistas', 'dia')"
                :class="[
                  'px-2.5 py-1 rounded-md transition-all cursor-pointer font-bold',
                  agrupacionTemporalVistas === 'dia'
                    ? 'bg-emerald-500 text-white shadow-xs'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200'
                ]"
              >
                Día
              </button>
            </div>

            <button
              type="button"
              @click="cerrar"
              class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition-all cursor-pointer shrink-0"
              title="Cerrar modal (ESC)"
            >
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Contenido del Gráfico Ampliado -->
        <div class="p-6 flex-1 overflow-y-auto min-h-[460px] flex flex-col justify-start">
          <!-- Caso Especial: Desglose Mensual de Score -->
          <div v-if="modalGraficoActivo === 'score_mensual'" class="space-y-6">
            <div class="h-64 sm:h-72 w-full bg-slate-50/50 dark:bg-slate-950/40 rounded-2xl p-3 border border-slate-100 dark:border-slate-800/80">
              <Bar :data="scoreMensualChartData" :options="scoreMensualChartOptions" />
            </div>

            <div>
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center gap-2">
                <Calendar class="w-4 h-4 text-violet-500" />
                <span>Matriz Comparativa por Mes de Campaña</span>
              </h4>

              <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xs">
                <table class="w-full text-left text-xs">
                  <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-mono uppercase text-[10px] border-b border-slate-200 dark:border-slate-800">
                    <tr>
                      <th class="px-4 py-3">Mes</th>
                      <th class="px-4 py-3 text-center">Publicaciones</th>
                      <th class="px-4 py-3 text-right">Score Promedio / Post</th>
                      <th class="px-4 py-3 text-right">Score Total Acumulado</th>
                      <th class="px-4 py-3 text-right">Vistas Totales</th>
                      <th class="px-4 py-3 text-right">Total Interacciones</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                    <tr
                      v-for="(mes, idx) in listaDesgloseMensual"
                      :key="mes.clave_mes || idx"
                      class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors"
                    >
                      <td class="px-4 py-3.5 font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                        <span>{{ mes.nombre_mes }}</span>
                      </td>
                      <td class="px-4 py-3.5 text-center text-slate-600 dark:text-slate-300">
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold">
                          {{ mes.total_posts }} posts
                        </span>
                      </td>
                      <td class="px-4 py-3.5 text-right font-black text-violet-600 dark:text-violet-400 text-sm">
                        {{ Number(mes.score_promedio_post).toLocaleString('es-AR') }} <span class="text-[10px] font-normal text-slate-400">pts/post</span>
                      </td>
                      <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-300 font-semibold">
                        {{ Number(mes.score_total).toLocaleString('es-AR') }} pts
                      </td>
                      <td class="px-4 py-3.5 text-right text-emerald-600 dark:text-emerald-400 font-semibold">
                        {{ Number(mes.total_vistas).toLocaleString('es-AR') }}
                      </td>
                      <td class="px-4 py-3.5 text-right text-slate-700 dark:text-slate-300 font-semibold">
                        {{ Number(mes.total_interacciones).toLocaleString('es-AR') }}
                      </td>
                    </tr>
                    <tr v-if="listaDesgloseMensual.length === 0">
                      <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                        No se registran meses con publicaciones para auditar.
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Gráficos Estándar -->
          <div v-else class="h-[62vh] w-full">
            <Line v-if="modalGraficoActivo === 'comunidad'" :data="comunidadChartData" :options="comunidadChartOptions" />
            <Line v-else-if="modalGraficoActivo === 'score'" :data="scoreChartData" :options="scoreChartOptions" />
            <Line v-else-if="modalGraficoActivo === 'vistas'" :data="vistasChartData" :options="vistasChartOptions" />
            <Doughnut v-else-if="modalGraficoActivo === 'cuota'" :data="doughnutChartData" :options="doughnutChartOptions" />
            <Bar v-else-if="modalGraficoActivo === 'formato'" :data="formatBarChartData" :options="formatBarChartOptions" />
            <Bar v-else-if="modalGraficoActivo === 'matriz_formatos'" :data="matrizFormatosBarChartData" :options="matrizFormatosBarChartOptions" />
            <Bar v-else-if="modalGraficoActivo === 'ejes_traccion'" :data="ejesTraccionBarChartData" :options="ejesTraccionBarChartOptions" />
            <Bar v-else-if="modalGraficoActivo === 'ejes_impacto'" :data="ejesBarChartData" :options="ejesBarChartOptions" />
            <Bar v-else-if="modalGraficoActivo === 'ejes_volumen'" :data="ejesVolumenBarChartData" :options="ejesVolumenBarChartOptions" />
          </div>
        </div>

        <!-- Pie del Modal -->
        <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-400 bg-slate-50/50 dark:bg-slate-950/40 shrink-0">
          <span>Presiona ESC o haz clic fuera para cerrar</span>
          <button
            type="button"
            @click="cerrar"
            class="px-4 py-1.5 rounded-xl font-bold bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition-all cursor-pointer"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
