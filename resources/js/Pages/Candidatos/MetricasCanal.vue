<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import MetricasCanalHeader from '../../Components/MetricasCanal/MetricasCanalHeader.vue';
import SemaforoPadronGrid from '../../Components/MetricasCanal/SemaforoPadronGrid.vue';
import RadiografiaDemograficaSection from '../../Components/MetricasCanal/RadiografiaDemograficaSection.vue';
import BenchmarksIndustriaSection from '../../Components/MetricasCanal/BenchmarksIndustriaSection.vue';
import OrganicoVsPautaSection from '../../Components/MetricasCanal/OrganicoVsPautaSection.vue';
import FormatosYReelsSection from '../../Components/MetricasCanal/FormatosYReelsSection.vue';
import ConsistenciaYHistoricoSection from '../../Components/MetricasCanal/ConsistenciaYHistoricoSection.vue';
import MetricasChartModal from '../../Components/MetricasCanal/MetricasChartModal.vue';
import { Film, Layers, MessageCircle, Sparkles } from '@lucide/vue';

// Chart.js & vue-chartjs
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
} from 'chart.js';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
);

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  perfilSocial: {
    type: Object,
    required: true,
  },
  territorioContexto: {
    type: Object,
    default: () => ({}),
  },
  semaforoPadron: {
    type: Object,
    default: () => ({}),
  },
  alertaRedistribucionPauta: {
    type: Object,
    default: null,
  },
  demografiaAudiencia: {
    type: Object,
    default: null,
  },
  cruceDemografico: {
    type: Array,
    default: () => [],
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
  organicoVsPauta: {
    type: Object,
    default: () => ({}),
  },
  rendimientoPorFormato: {
    type: Array,
    default: () => [],
  },
  consistenciaMensual: {
    type: Array,
    default: () => [],
  },
  promedioVistasInfo: {
    type: Object,
    default: () => ({}),
  },
  semaforoObjetivos: {
    type: Array,
    default: () => [],
  },
  historicoMediciones: {
    type: Array,
    default: () => [],
  },
  topPublicaciones: {
    type: Array,
    default: () => [],
  },
  distribucionEjes: {
    type: Array,
    default: () => [],
  },
  ejes: {
    type: Array,
    default: () => [],
  },
  canalesCandidato: {
    type: Array,
    default: () => [],
  }
});

const isChartModalOpen = ref(false);

const formatNumber = (n) => {
  return Number(n || 0).toLocaleString('es-AR');
};

const formatCurrency = (n) => {
  return '$' + Number(n || 0).toLocaleString('es-AR');
};

const tabBadgeStyle = (colorEstado) => {
  switch (colorEstado) {
    case 'azul':
      return {
        tab: 'border-blue-500 bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold ring-2 ring-blue-500/30',
        pill: 'bg-blue-500 text-white font-bold',
        label: 'Verificada'
      };
    case 'verde':
    case 'naranja':
      return {
        tab: 'border-emerald-500 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold ring-2 ring-emerald-500/30',
        pill: 'bg-emerald-500 text-white font-bold',
        label: 'Activa'
      };
    case 'rojo':
      return {
        tab: 'border-rose-500 bg-rose-500/10 text-rose-600 dark:text-rose-400 font-semibold ring-2 ring-rose-500/30',
        pill: 'bg-rose-500 text-white font-bold',
        label: 'Inactiva'
      };
    case 'gris':
    default:
      return {
        tab: 'border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-400 opacity-75 hover:opacity-100 hover:border-slate-400 hover:bg-white dark:hover:bg-slate-900 border-dashed',
        pill: 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium',
        label: 'Configurar'
      };
  }
};

const getSocialMeta = (key) => {
  switch (key) {
    case 'instagram':
      return { color: '#E4405F', bgLight: 'bg-[#E4405F]/15', name: 'Instagram', badge: 'bg-[#E4405F]/10 text-[#E4405F]' };
    case 'facebook':
      return { color: '#1877F2', bgLight: 'bg-[#1877F2]/15', name: 'Facebook', badge: 'bg-[#1877F2]/10 text-[#1877F2]' };
    case 'threads':
      return { color: '#000000', bgLight: 'bg-slate-900/15', name: 'Threads', badge: 'bg-slate-500/10 text-slate-700 dark:text-slate-300' };
    case 'tiktok':
      return { color: '#00F2FE', bgLight: 'bg-cyan-500/15', name: 'TikTok', badge: 'bg-cyan-500/10 text-cyan-500' };
    case 'youtube':
      return { color: '#FF0000', bgLight: 'bg-red-500/15', name: 'YouTube', badge: 'bg-red-500/10 text-red-500' };
    case 'x_twitter':
      return { color: '#000000', bgLight: 'bg-slate-500/15', name: 'X (Twitter)', badge: 'bg-slate-500/10 text-slate-700 dark:text-slate-300' };
    case 'linkedin':
      return { color: '#0A66C2', bgLight: 'bg-blue-600/15', name: 'LinkedIn', badge: 'bg-blue-600/10 text-blue-600' };
    default:
      return { color: '#06b6d4', bgLight: 'bg-cyan-500/15', name: 'Red Social', badge: 'bg-cyan-500/10 text-cyan-500' };
  }
};

const getFormatoIcon = (formato) => {
  switch (formato?.toLowerCase()) {
    case 'reel':
    case 'video':
    case 'shorts':
      return Film;
    case 'foto':
    case 'carrusel':
      return Layers;
    case 'tweet':
    case 'nota':
    case 'articulo':
      return MessageCircle;
    default:
      return Sparkles;
  }
};

// Keyboard listener for Escape key to close modal
const handleKeyDown = (e) => {
  if (e.key === 'Escape' && isChartModalOpen.value) {
    isChartModalOpen.value = false;
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});

// ══════════════════════════════════════════════════════════════════════════
// CONFIGURACIÓN DE GRÁFICOS PARA EL MODAL (CHART.JS)
// ══════════════════════════════════════════════════════════════════════════

// 1. AUDIENCIA & SEGUIDORES (Time-Series)
const audienciaChartData = computed(() => {
  const data = props.historicoMediciones || [];
  const labels = data.map(m => m.fecha_corta || m.fecha);
  const seguidoresData = data.map(m => m.seguidores);
  const crecimientoData = data.map(m => m.crecimiento_neto_seguidores);

  return {
    labels,
    datasets: [
      {
        label: 'Seguidores Totales',
        data: seguidoresData,
        borderColor: '#06b6d4',
        backgroundColor: 'rgba(6, 182, 212, 0.12)',
        fill: true,
        tension: 0.35,
        borderWidth: 3,
        pointBackgroundColor: '#06b6d4',
        pointBorderColor: '#ffffff',
        pointRadius: 4,
        pointHoverRadius: 6,
        yAxisID: 'y',
      },
      {
        label: 'Crecimiento Neto (+/-)',
        data: crecimientoData,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.08)',
        fill: false,
        borderDash: [5, 5],
        tension: 0.3,
        borderWidth: 2,
        pointBackgroundColor: '#10b981',
        pointBorderColor: '#ffffff',
        pointRadius: 3,
        pointHoverRadius: 5,
        yAxisID: 'y1',
      }
    ]
  };
});

const audienciaChartOptions = computed(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#94a3b8' : '#64748b';
  const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: {
        position: 'top',
        labels: { color: textColor, font: { family: 'ui-monospace, monospace', size: 12, weight: 'bold' }, usePointStyle: true, padding: 15 }
      },
      tooltip: {
        backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: isDark ? '#f8fafc' : '#0f172a',
        bodyColor: isDark ? '#cbd5e1' : '#334155',
        borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
        callbacks: {
          label: (context) => `${context.dataset.label}: ${Number(context.parsed.y).toLocaleString('es-AR')}`
        }
      }
    },
    scales: {
      x: { grid: { color: gridColor, drawBorder: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
      y: {
        type: 'linear',
        display: true,
        position: 'left',
        grid: { color: gridColor, drawBorder: false },
        ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => Number(v).toLocaleString('es-AR') }
      },
      y1: {
        type: 'linear',
        display: true,
        position: 'right',
        grid: { drawOnChartArea: false },
        ticks: { color: '#10b981', font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => (v >= 0 ? '+' : '') + Number(v).toLocaleString('es-AR') }
      }
    }
  };
});

// 2. INTERACCIONES & ENGAGEMENT MENSUAL
const interaccionesChartData = computed(() => {
  const meses = props.consistenciaMensual || [];
  const labels = meses.map(m => m.mes_nombre);
  const interacciones = meses.map(m => m.total_interacciones);

  return {
    labels,
    datasets: [
      {
        label: 'Interacciones Totales (Likes + Coment + Comp)',
        data: interacciones,
        backgroundColor: '#06b6d4',
        borderRadius: 10,
        hoverBackgroundColor: '#22d3ee',
      }
    ]
  };
});

const interaccionesChartOptions = computed(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#94a3b8' : '#64748b';
  const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: isDark ? '#f8fafc' : '#0f172a',
        bodyColor: isDark ? '#cbd5e1' : '#334155',
        borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
        callbacks: {
          label: (context) => `🔥 Interacciones: ${Number(context.parsed.y).toLocaleString('es-AR')}`
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
      y: {
        grid: { color: gridColor, drawBorder: false },
        ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => Number(v).toLocaleString('es-AR') }
      }
    }
  };
});

// 3. CADENCIA & VOLUMEN DE PUBLICACIONES VS META
const cadenciaChartData = computed(() => {
  const meses = props.consistenciaMensual || [];
  const labels = meses.map(m => m.mes_nombre);
  const postsCount = meses.map(m => m.posts_count);
  const metas = meses.map(m => m.meta_mensual || 16);

  return {
    labels,
    datasets: [
      {
        type: 'bar',
        label: 'Posts Publicados',
        data: postsCount,
        backgroundColor: postsCount.map(c => (c >= 16 ? '#10b981' : (c >= 12 ? '#f59e0b' : (c === 0 ? '#ef4444' : '#64748b')))),
        borderRadius: 10,
        order: 2,
      },
      {
        type: 'line',
        label: 'Meta de Campaña (≥ 16 posts)',
        data: metas,
        borderColor: '#f59e0b',
        borderWidth: 2,
        borderDash: [6, 6],
        pointRadius: 0,
        fill: false,
        order: 1,
      }
    ]
  };
});

const cadenciaChartOptions = computed(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#94a3b8' : '#64748b';
  const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top',
        labels: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11, weight: 'bold' }, usePointStyle: true }
      },
      tooltip: {
        backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: isDark ? '#f8fafc' : '#0f172a',
        bodyColor: isDark ? '#cbd5e1' : '#334155',
        borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
      y: {
        grid: { color: gridColor, drawBorder: false },
        ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, stepSize: 4 }
      }
    }
  };
});

// 4. FORMATOS & MIX DE CONTENIDO (Doughnut)
const formatosChartData = computed(() => {
  const formatos = props.rendimientoPorFormato || [];
  const labels = formatos.map(f => f.tipo_formato || f.formato || 'Otro');
  const data = formatos.map(f => Number(f.cantidad_posts ?? f.cantidad ?? 0));
  const colors = ['#06b6d4', '#ec4899', '#8b5cf6', '#f59e0b', '#10b981', '#64748b', '#3b82f6'];

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: colors.slice(0, labels.length),
        borderWidth: 2,
        borderColor: typeof document !== 'undefined' && document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
        hoverOffset: 8,
      }
    ]
  };
});

const formatosChartOptions = computed(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#94a3b8' : '#64748b';

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'right',
        labels: { color: textColor, font: { family: 'ui-monospace, monospace', size: 12, weight: 'bold' }, padding: 15, usePointStyle: true }
      },
      tooltip: {
        backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: isDark ? '#f8fafc' : '#0f172a',
        bodyColor: isDark ? '#cbd5e1' : '#334155',
        borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
        callbacks: {
          label: (context) => ` ${context.label}: ${context.parsed} posts`
        }
      }
    }
  };
});

// 5. PAUTA VS ORGÁNICO & INVERSIÓN MENSUAL
const pautaChartData = computed(() => {
  const meses = props.consistenciaMensual || [];
  const labels = meses.map(m => m.mes_nombre);
  const pautaInvertida = meses.map(m => m.total_pauta || 0);

  return {
    labels,
    datasets: [
      {
        label: 'Inversión en Pauta ($)',
        data: pautaInvertida,
        backgroundColor: '#8b5cf6',
        borderRadius: 10,
        hoverBackgroundColor: '#a78bfa',
      }
    ]
  };
});

const pautaChartOptions = computed(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#94a3b8' : '#64748b';
  const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
        titleColor: isDark ? '#f8fafc' : '#0f172a',
        bodyColor: isDark ? '#cbd5e1' : '#334155',
        borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
        callbacks: {
          label: (context) => ` Inversión: $${Number(context.parsed.y).toLocaleString('es-AR')}`
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
      y: {
        grid: { color: gridColor, drawBorder: false },
        ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => '$' + Number(v).toLocaleString('es-AR') }
      }
    }
  };
});
</script>

<template>
  <Head :title="`Métricas ${getSocialMeta(perfilSocial.plataforma).name} - ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
      <!-- 1. Cabecera, Navegación, Barra de Canales y Contexto Territorial -->
      <MetricasCanalHeader
        :candidato="candidato"
        :perfil-social="perfilSocial"
        :territorio-contexto="territorioContexto"
        :alerta-redistribucion-pauta="alertaRedistribucionPauta"
        :benchmarks="benchmarks"
        :stats="stats"
        :canales-candidato="canalesCandidato"
        :get-social-meta="getSocialMeta"
        :tab-badge-style="tabBadgeStyle"
        :format-number="formatNumber"
        @open-chart-modal="isChartModalOpen = true"
      />

      <!-- 2. Semáforo Territorio-First del Padrón Electoral -->
      <SemaforoPadronGrid
        v-if="semaforoPadron"
        :semaforo-padron="semaforoPadron"
        :format-number="formatNumber"
      />

      <!-- 3. Radiografía Demográfica y Cruce con el Padrón -->
      <RadiografiaDemograficaSection
        v-if="demografiaAudiencia"
        :demografia-audiencia="demografiaAudiencia"
        :cruce-demografico="cruceDemografico"
        :territorio-contexto="territorioContexto"
        :perfil-social="perfilSocial"
        :get-social-meta="getSocialMeta"
      />

      <!-- 4. Benchmarks Técnicos de Industria (Panel Colapsable) -->
      <BenchmarksIndustriaSection
        :benchmarks="benchmarks"
        :semaforo-objetivos="semaforoObjetivos"
      />

      <!-- 5. Tracción Orgánica vs Pauta Publicitaria -->
      <OrganicoVsPautaSection
        v-if="organicoVsPauta"
        :organico-vs-pauta="organicoVsPauta"
        :format-number="formatNumber"
        :format-currency="formatCurrency"
      />

      <!-- 6. Rendimiento por Formato y Benchmark de Reels -->
      <FormatosYReelsSection
        :rendimiento-por-formato="rendimientoPorFormato"
        :promedio-vistas-info="promedioVistasInfo"
        :perfil-social="perfilSocial"
        :format-number="formatNumber"
        :get-social-meta="getSocialMeta"
        :get-formato-icon="getFormatoIcon"
      />

      <!-- 7. Consistencia Mensual, Time-Series y Top Publicaciones -->
      <ConsistenciaYHistoricoSection
        :consistencia-mensual="consistenciaMensual"
        :benchmarks="benchmarks"
        :historico-mediciones="historicoMediciones"
        :distribucion-ejes="distribucionEjes"
        :top-publicaciones="topPublicaciones"
        :perfil-social="perfilSocial"
        :format-number="formatNumber"
        @open-chart-modal="isChartModalOpen = true"
      />
    </div>

    <!-- 8. Modal Gráfico de Evolución Temporal Fullscreen -->
    <MetricasChartModal
      :is-open="isChartModalOpen"
      :perfil-social="perfilSocial"
      :get-social-meta="getSocialMeta"
      :format-number="formatNumber"
      :historico-mediciones="historicoMediciones"
      :consistencia-mensual="consistenciaMensual"
      :rendimiento-por-formato="rendimientoPorFormato"
      :organico-vs-pauta="organicoVsPauta"
      :stats="stats"
      :benchmarks="benchmarks"
      :frecuencia-publicacion="frecuenciaPublicacion"
      :audiencia-chart-data="audienciaChartData"
      :audiencia-chart-options="audienciaChartOptions"
      :interacciones-chart-data="interaccionesChartData"
      :interacciones-chart-options="interaccionesChartOptions"
      :cadencia-chart-data="cadenciaChartData"
      :cadencia-chart-options="cadenciaChartOptions"
      :formatos-chart-data="formatosChartData"
      :formatos-chart-options="formatosChartOptions"
      :pauta-chart-data="pautaChartData"
      :pauta-chart-options="pautaChartOptions"
      @close="isChartModalOpen = false"
    />
  </WarRoomLayout>
</template>
