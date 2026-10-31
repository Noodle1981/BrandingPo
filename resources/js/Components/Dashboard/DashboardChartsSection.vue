<script setup>
import { ref, watch } from 'vue';
import DashboardEvolutionTrilogy from './Charts/DashboardEvolutionTrilogy.vue';
import DashboardSocialDistribution from './Charts/DashboardSocialDistribution.vue';
import DashboardThemeEjesGrid from './Charts/DashboardThemeEjesGrid.vue';
import DashboardChartFullscreenModal from './Charts/DashboardChartFullscreenModal.vue';
import { useDashboardCharts } from '../../composables/useDashboardCharts';
import { useModalKeydown } from '../../composables/useModalKeydown';

const props = defineProps({
  historicoMediciones: {
    type: Array,
    default: () => []
  },
  seriesPorRed: {
    type: Object,
    default: () => ({})
  },
  redesDesglose: {
    type: Array,
    default: () => []
  },
  distribucionPlataformas: {
    type: Array,
    default: () => []
  },
  rendimientoPorFormato: {
    type: Array,
    default: () => []
  },
  formatosPorRed: {
    type: Array,
    default: () => []
  },
  distribucionEjes: {
    type: Array,
    default: () => []
  },
  hitosBooster: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  modalGraficoExterno: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['update:modal-grafico-externo']);

// Composable con todas las opciones, transformaciones y reactividad de gráficos
const {
  redesDisponiblesTimeline,
  redesConVistasDisponibles,
  selectedComunidadPlatform,
  agrupacionTemporalComunidad,
  selectedScorePlatform,
  agrupacionTemporalScore,
  selectedVistasPlatform,
  agrupacionTemporalVistas,
  comunidadChartData,
  comunidadChartOptions,
  scoreChartData,
  scoreChartOptions,
  scoreMensualChartData,
  scoreMensualChartOptions,
  listaDesgloseMensual,
  vistasChartData,
  vistasChartOptions,
  doughnutChartData,
  doughnutChartOptions,
  formatBarChartData,
  formatBarChartOptions,
  matrizFormatosBarChartData,
  matrizFormatosBarChartOptions,
  ejesTraccionBarChartData,
  ejesTraccionBarChartOptions,
  ejesBarChartData,
  ejesBarChartOptions,
  ejesVolumenBarChartData,
  ejesVolumenBarChartOptions,
} = useDashboardCharts(props);

// Modal de Ampliación (Fullscreen / HD)
const modalGraficoActivo = ref(null);

watch(() => props.modalGraficoExterno, (nuevo) => {
  if (nuevo) modalGraficoActivo.value = nuevo;
});

const abrirModalGrafico = (tipo) => {
  modalGraficoActivo.value = tipo;
  emit('update:modal-grafico-externo', tipo);
};

const cerrarModalGrafico = () => {
  modalGraficoActivo.value = null;
  emit('update:modal-grafico-externo', null);
};

useModalKeydown(() => modalGraficoActivo.value !== null, cerrarModalGrafico);
</script>

<template>
  <div class="space-y-6">
    <!-- 1. TRILOGÍA DE EVOLUCIÓN TEMPORAL DELEGADA -->
    <DashboardEvolutionTrilogy
      :comunidad-chart-data="comunidadChartData"
      :comunidad-chart-options="comunidadChartOptions"
      :score-chart-data="scoreChartData"
      :score-chart-options="scoreChartOptions"
      :vistas-chart-data="vistasChartData"
      :vistas-chart-options="vistasChartOptions"
      :redes-disponibles-timeline="redesDisponiblesTimeline"
      :redes-con-vistas-disponibles="redesConVistasDisponibles"
      v-model:selected-comunidad-platform="selectedComunidadPlatform"
      v-model:agrupacion-temporal-comunidad="agrupacionTemporalComunidad"
      v-model:selected-score-platform="selectedScorePlatform"
      v-model:agrupacion-temporal-score="agrupacionTemporalScore"
      v-model:selected-vistas-platform="selectedVistasPlatform"
      v-model:agrupacion-temporal-vistas="agrupacionTemporalVistas"
      @abrir-modal="abrirModalGrafico"
    />

    <!-- 2. CUOTA DE ATENCIÓN, RENDIMIENTO Y MATRIZ POR FORMATO DELEGADA -->
    <DashboardSocialDistribution
      :doughnut-chart-data="doughnutChartData"
      :doughnut-chart-options="doughnutChartOptions"
      :distribucion-plataformas="distribucionPlataformas"
      :format-bar-chart-data="formatBarChartData"
      :format-bar-chart-options="formatBarChartOptions"
      :matriz-formatos-bar-chart-data="matrizFormatosBarChartData"
      :matriz-formatos-bar-chart-options="matrizFormatosBarChartOptions"
      @abrir-modal="abrirModalGrafico"
    />

    <!-- 3. EJES TEMÁTICOS: TRACCIÓN, IMPACTO Y VOLUMEN DELEGADA -->
    <DashboardThemeEjesGrid
      :distribucion-ejes="distribucionEjes"
      :ejes-traccion-bar-chart-data="ejesTraccionBarChartData"
      :ejes-traccion-bar-chart-options="ejesTraccionBarChartOptions"
      :ejes-bar-chart-data="ejesBarChartData"
      :ejes-bar-chart-options="ejesBarChartOptions"
      :ejes-volumen-bar-chart-data="ejesVolumenBarChartData"
      :ejes-volumen-bar-chart-options="ejesVolumenBarChartOptions"
      @abrir-modal="abrirModalGrafico"
    />

    <!-- 4. MODAL DE AMPLIACIÓN (FULLSCREEN / HD) DELEGADO -->
    <DashboardChartFullscreenModal
      :modal-grafico-activo="modalGraficoActivo"
      v-model:agrupacion-temporal-comunidad="agrupacionTemporalComunidad"
      v-model:agrupacion-temporal-score="agrupacionTemporalScore"
      v-model:agrupacion-temporal-vistas="agrupacionTemporalVistas"
      :comunidad-chart-data="comunidadChartData"
      :comunidad-chart-options="comunidadChartOptions"
      :score-chart-data="scoreChartData"
      :score-chart-options="scoreChartOptions"
      :score-mensual-chart-data="scoreMensualChartData"
      :score-mensual-chart-options="scoreMensualChartOptions"
      :lista-desglose-mensual="listaDesgloseMensual"
      :vistas-chart-data="vistasChartData"
      :vistas-chart-options="vistasChartOptions"
      :doughnut-chart-data="doughnutChartData"
      :doughnut-chart-options="doughnutChartOptions"
      :format-bar-chart-data="formatBarChartData"
      :format-bar-chart-options="formatBarChartOptions"
      :matriz-formatos-bar-chart-data="matrizFormatosBarChartData"
      :matriz-formatos-bar-chart-options="matrizFormatosBarChartOptions"
      :ejes-traccion-bar-chart-data="ejesTraccionBarChartData"
      :ejes-traccion-bar-chart-options="ejesTraccionBarChartOptions"
      :ejes-bar-chart-data="ejesBarChartData"
      :ejes-bar-chart-options="ejesBarChartOptions"
      :ejes-volumen-bar-chart-data="ejesVolumenBarChartData"
      :ejes-volumen-bar-chart-options="ejesVolumenBarChartOptions"
      @close="cerrarModalGrafico"
    />
  </div>
</template>
