<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';
import { useFormatters } from '../../composables/useFormatters';
import { useSocialMetrics } from '../../composables/useSocialMetrics';
import {
  Users,
  Flame,
  Eye,
  PieChart,
  BarChart3,
  Layers,
  Sparkles,
  Target,
  Maximize2,
  Calendar,
  X
} from '@lucide/vue';

// Chart.js & vue-chartjs
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
} from 'chart.js';
import { Line, Doughnut, Bar } from 'vue-chartjs';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
);

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

const { formatNumber } = useFormatters();
const { getSocialMeta } = useSocialMetrics();

// Redes con cuenta configurada o activas del candidato para los filtros de las gráficas
const redesDisponiblesTimeline = computed(() => {
  return props.redesDesglose.filter(r => r.handle_usuario || r.esta_activo || r.seguidores > 0);
});

// Redes que manejan métricas de visualizaciones activas (> 0 vistas) para la Gráfica 3
const redesConVistasDisponibles = computed(() => {
  return props.redesDesglose.filter(r => (r.vistas_acumuladas && r.vistas_acumuladas > 0));
});

// ─────────────────────────────────────────────────────────────────────────────
// 1. GRÁFICA TEMPORAL: COMUNIDAD (SEGUIDORES NETOS) CON HITOS DE BOOSTER
// ─────────────────────────────────────────────────────────────────────────────
const selectedComunidadPlatform = ref('todas');

const comunidadChartData = computed(() => {
  const hitos = props.hitosBooster || [];
  const series = props.seriesPorRed || {};
  const plataformasKeys = Object.keys(series);

  if (selectedComunidadPlatform.value === 'todas') {
    const primerKey = plataformasKeys[0];
    const labels = primerKey && series[primerKey].puntos
      ? series[primerKey].puntos.map(m => m.fecha)
      : (props.historicoMediciones || []).map(m => m.fecha);

    const datasets = [];

    plataformasKeys.forEach(platKey => {
      const s = series[platKey];
      if (!s || !s.puntos || s.puntos.length === 0) return;

      const meta = getSocialMeta(platKey);
      const colorHex = s.color || meta.color;

      const pointRadiuses = [];
      const pointHoverRadiuses = [];
      const pointBackgroundColors = [];
      const pointBorderColors = [];
      const pointBorderWidths = [];

      s.puntos.forEach(p => {
        const tieneBooster = hitos.some(h => 
          h.plataforma === platKey && (
            (h.fecha_raw && p.fecha_raw && h.fecha_raw === p.fecha_raw) || 
            (h.fecha && p.fecha && h.fecha === p.fecha)
          )
        );

        if (tieneBooster) {
          pointRadiuses.push(8);
          pointHoverRadiuses.push(10);
          pointBackgroundColors.push('#ef4444');
          pointBorderColors.push('#ffffff');
          pointBorderWidths.push(2);
        } else {
          pointRadiuses.push(3);
          pointHoverRadiuses.push(5);
          pointBackgroundColors.push(colorHex);
          pointBorderColors.push('#ffffff');
          pointBorderWidths.push(1);
        }
      });

      datasets.push({
        label: s.nombre,
        data: s.puntos.map(m => m.seguidores),
        borderColor: colorHex,
        backgroundColor: `${colorHex}15`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        pointRadius: pointRadiuses,
        pointHoverRadius: pointHoverRadiuses,
        pointBackgroundColor: pointBackgroundColors,
        pointBorderColor: pointBorderColors,
        pointBorderWidth: pointBorderWidths,
        plataforma: platKey,
      });
    });

    const totalPuntos = props.historicoMediciones || [];
    if (totalPuntos.length > 0) {
      const colorTotal = '#06b6d4';
      datasets.unshift({
        label: 'Total Multired (Suma)',
        data: totalPuntos.map(m => m.seguidores),
        borderColor: colorTotal,
        backgroundColor: `${colorTotal}10`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        borderDash: [5, 4],
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: colorTotal,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        plataforma: 'todas',
      });
    }

    return { labels, datasets };
  }

  const s = series[selectedComunidadPlatform.value];
  if (!s || !s.puntos || s.puntos.length === 0) {
    return { labels: [], datasets: [] };
  }

  const meta = getSocialMeta(selectedComunidadPlatform.value);
  const colorHex = s.color || meta.color;
  const labels = s.puntos.map(m => m.fecha);
  const data = s.puntos.map(m => m.seguidores);

  const pointRadiuses = [];
  const pointHoverRadiuses = [];
  const pointBackgroundColors = [];
  const pointBorderColors = [];
  const pointBorderWidths = [];

  s.puntos.forEach(p => {
    const tieneBooster = hitos.some(h => 
      h.plataforma === selectedComunidadPlatform.value && (
        (h.fecha_raw && p.fecha_raw && h.fecha_raw === p.fecha_raw) || 
        (h.fecha && p.fecha && h.fecha === p.fecha)
      )
    );

    if (tieneBooster) {
      pointRadiuses.push(8);
      pointHoverRadiuses.push(10);
      pointBackgroundColors.push('#ef4444');
      pointBorderColors.push('#ffffff');
      pointBorderWidths.push(2);
    } else {
      pointRadiuses.push(3);
      pointHoverRadiuses.push(5);
      pointBackgroundColors.push(colorHex);
      pointBorderColors.push('#ffffff');
      pointBorderWidths.push(1);
    }
  });

  return {
    labels,
    datasets: [{
      label: s.nombre,
      data,
      borderColor: colorHex,
      backgroundColor: `${colorHex}18`,
      fill: true,
      tension: 0.35,
      borderWidth: 2.5,
      pointRadius: pointRadiuses,
      pointHoverRadius: pointHoverRadiuses,
      pointBackgroundColor: pointBackgroundColors,
      pointBorderColor: pointBorderColors,
      pointBorderWidth: pointBorderWidths,
      plataforma: selectedComunidadPlatform.value,
    }]
  };
});

const comunidadChartOptions = computed(() => {
  const isAll = selectedComunidadPlatform.value === 'todas';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: isAll,
        position: 'top',
        align: 'end',
        labels: {
          boxWidth: 8,
          boxHeight: 8,
          usePointStyle: true,
          color: '#94a3b8',
          padding: 8,
          font: { size: 10, family: 'monospace', weight: 'bold' }
        }
      },
      tooltip: {
        backgroundColor: '#0f172a',
        titleColor: '#06b6d4',
        bodyColor: '#f8fafc',
        padding: 10,
        cornerRadius: 8,
        borderColor: 'rgba(148, 163, 184, 0.2)',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')} seg`,
          afterLabel: (ctx) => {
            const dataset = ctx.dataset;
            const plat = dataset.plataforma || selectedComunidadPlatform.value;
            const idx = ctx.dataIndex;
            const series = props.seriesPorRed || {};
            const punto = series[plat]?.puntos ? series[plat].puntos[idx] : null;
            if (!punto) return '';

            const hitos = (props.hitosBooster || []).filter(h => 
              h.plataforma === plat && (
                (h.fecha_raw && punto.fecha_raw && h.fecha_raw === punto.fecha_raw) || 
                (h.fecha && punto.fecha && h.fecha === punto.fecha)
              )
            );

            if (hitos.length > 0) {
              const h = hitos[0];
              return `🚀 BOOSTER ${h.plataforma.toUpperCase()}: ${h.monto_formateado}\n📌 ${h.titulo}`;
            }
            return '';
          }
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' } } },
      y: { grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, callback: (v) => formatNumber(v) } }
    }
  };
});

// ─────────────────────────────────────────────────────────────────────────────
// 2. GRÁFICA TEMPORAL: TRACCIÓN / SCORE DE IMPACTO PONDERADO (PUNTOS 🔥)
// ─────────────────────────────────────────────────────────────────────────────
const selectedScorePlatform = ref('todas');

const scoreChartData = computed(() => {
  const series = props.seriesPorRed || {};
  const plataformasKeys = Object.keys(series);

  if (selectedScorePlatform.value === 'todas') {
    const primerKey = plataformasKeys[0];
    const labels = primerKey && series[primerKey].puntos
      ? series[primerKey].puntos.map(m => m.fecha)
      : (props.historicoMediciones || []).map(m => m.fecha);

    const datasets = [];

    plataformasKeys.forEach(platKey => {
      const s = series[platKey];
      if (!s || !s.puntos || s.puntos.length === 0) return;

      const meta = getSocialMeta(platKey);
      const colorHex = s.color || meta.color;

      datasets.push({
        label: s.nombre,
        data: s.puntos.map(m => m.puntos || 0),
        borderColor: colorHex,
        backgroundColor: `${colorHex}15`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: colorHex,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1,
        plataforma: platKey,
      });
    });

    const totalPuntos = props.historicoMediciones || [];
    if (totalPuntos.length > 0) {
      const colorTotal = '#f59e0b';
      datasets.unshift({
        label: 'Total Score (Suma)',
        data: totalPuntos.map(m => m.puntos || 0),
        borderColor: colorTotal,
        backgroundColor: `${colorTotal}10`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        borderDash: [5, 4],
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: colorTotal,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        plataforma: 'todas',
      });
    }

    return { labels, datasets };
  }

  const s = series[selectedScorePlatform.value];
  if (!s || !s.puntos || s.puntos.length === 0) {
    return { labels: [], datasets: [] };
  }

  const meta = getSocialMeta(selectedScorePlatform.value);
  const colorHex = s.color || meta.color;
  const labels = s.puntos.map(m => m.fecha);
  const data = s.puntos.map(m => m.puntos || 0);

  return {
    labels,
    datasets: [{
      label: s.nombre,
      data,
      borderColor: colorHex,
      backgroundColor: `${colorHex}18`,
      fill: true,
      tension: 0.35,
      borderWidth: 2.5,
      pointRadius: 3,
      pointHoverRadius: 6,
      pointBackgroundColor: colorHex,
      pointBorderColor: '#ffffff',
      pointBorderWidth: 1,
      plataforma: selectedScorePlatform.value,
    }]
  };
});

const scoreChartOptions = computed(() => {
  const isAll = selectedScorePlatform.value === 'todas';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: isAll,
        position: 'top',
        align: 'end',
        labels: {
          boxWidth: 8,
          boxHeight: 8,
          usePointStyle: true,
          color: '#94a3b8',
          padding: 8,
          font: { size: 10, family: 'monospace', weight: 'bold' }
        }
      },
      tooltip: {
        backgroundColor: '#0f172a',
        titleColor: '#f59e0b',
        bodyColor: '#f8fafc',
        padding: 10,
        cornerRadius: 8,
        borderColor: 'rgba(245, 158, 11, 0.3)',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')} pts`
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' } } },
      y: { grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, callback: (v) => formatNumber(v) } }
    }
  };
});

// ─────────────────────────────────────────────────────────────────────────────
// 2.B. GRÁFICA Y COMPARATIVA MENSUAL DE SCORE
// ─────────────────────────────────────────────────────────────────────────────
const listaDesgloseMensual = computed(() => {
  if (props.stats?.desglose_mensual && props.stats.desglose_mensual.length > 0) {
    return props.stats.desglose_mensual;
  }
  return [];
});

const scoreMensualChartData = computed(() => {
  const meses = listaDesgloseMensual.value;
  const labels = meses.map(m => m.mes_corto || m.nombre_mes);
  const dataPromedios = meses.map(m => m.score_promedio_post || 0);
  const dataPosts = meses.map(m => m.total_posts || 0);

  return {
    labels,
    datasets: [
      {
        type: 'bar',
        label: 'Score Promedio por Post (pts/post)',
        data: dataPromedios,
        backgroundColor: '#8b5cf6',
        borderRadius: 8,
        barThickness: 32,
        yAxisID: 'y',
      },
      {
        type: 'line',
        label: 'Publicaciones Realizadas',
        data: dataPosts,
        borderColor: '#06b6d4',
        backgroundColor: 'rgba(6, 182, 212, 0.15)',
        borderWidth: 2.5,
        pointRadius: 4,
        pointBackgroundColor: '#06b6d4',
        tension: 0.3,
        yAxisID: 'y1',
      }
    ]
  };
});

const scoreMensualChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: '#94a3b8',
        font: { size: 11, weight: 'bold' },
        usePointStyle: true,
      }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      titleColor: '#8b5cf6',
      bodyColor: '#f8fafc',
      padding: 12,
      cornerRadius: 10,
      borderColor: 'rgba(139, 92, 246, 0.3)',
      borderWidth: 1,
      callbacks: {
        label: (ctx) => {
          if (ctx.dataset.type === 'line') {
            return `Publicaciones: ${ctx.raw} posts`;
          }
          return `Score Promedio: ${Number(ctx.raw).toLocaleString('es-AR')} pts/post`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, family: 'monospace', weight: 'bold' } }
    },
    y: {
      position: 'left',
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: { color: '#8b5cf6', font: { size: 10, family: 'monospace' }, callback: (v) => `${formatNumber(v)} pts` },
      title: { display: true, text: 'Score Promedio (pts/post)', color: '#8b5cf6', font: { size: 10 } }
    },
    y1: {
      position: 'right',
      grid: { display: false },
      ticks: { color: '#06b6d4', font: { size: 10, family: 'monospace' }, stepSize: 1 },
      title: { display: true, text: 'Cant. Posts', color: '#06b6d4', font: { size: 10 } }
    }
  }
}));

// ─────────────────────────────────────────────────────────────────────────────
// 3. GRÁFICA TEMPORAL: REPRODUCCIONES (VISUALIZACIONES / REELS 👁️)
// ─────────────────────────────────────────────────────────────────────────────
const selectedVistasPlatform = ref('todas');

const vistasChartData = computed(() => {
  const series = props.seriesPorRed || {};
  const plataformasConVistas = redesConVistasDisponibles.value.map(r => r.plataforma);

  if (selectedVistasPlatform.value === 'todas') {
    const primerKey = plataformasConVistas[0] || Object.keys(series)[0];
    const labels = primerKey && series[primerKey]?.puntos
      ? series[primerKey].puntos.map(m => m.fecha)
      : (props.historicoMediciones || []).map(m => m.fecha);

    const datasets = [];

    plataformasConVistas.forEach(platKey => {
      const s = series[platKey];
      if (!s || !s.puntos || s.puntos.length === 0) return;

      const meta = getSocialMeta(platKey);
      const colorHex = s.color || meta.color;

      datasets.push({
        label: s.nombre,
        data: s.puntos.map(m => m.vistas || 0),
        borderColor: colorHex,
        backgroundColor: `${colorHex}15`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        pointRadius: 3,
        pointHoverRadius: 6,
        pointBackgroundColor: colorHex,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1,
        plataforma: platKey,
      });
    });

    const totalPuntos = props.historicoMediciones || [];
    if (totalPuntos.length > 0) {
      const colorTotal = '#10b981';
      datasets.unshift({
        label: 'Total Vistas (Suma)',
        data: totalPuntos.map(m => m.vistas || 0),
        borderColor: colorTotal,
        backgroundColor: `${colorTotal}10`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        borderDash: [5, 4],
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: colorTotal,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        plataforma: 'todas',
      });
    }

    return { labels, datasets };
  }

  const s = series[selectedVistasPlatform.value];
  if (!s || !s.puntos || s.puntos.length === 0) {
    return { labels: [], datasets: [] };
  }

  const meta = getSocialMeta(selectedVistasPlatform.value);
  const colorHex = s.color || meta.color;
  const labels = s.puntos.map(m => m.fecha);
  const data = s.puntos.map(m => m.vistas || 0);

  return {
    labels,
    datasets: [{
      label: s.nombre,
      data,
      borderColor: colorHex,
      backgroundColor: `${colorHex}18`,
      fill: true,
      tension: 0.35,
      borderWidth: 2.5,
      pointRadius: 3,
      pointHoverRadius: 6,
      pointBackgroundColor: colorHex,
      pointBorderColor: '#ffffff',
      pointBorderWidth: 1,
      plataforma: selectedVistasPlatform.value,
    }]
  };
});

const vistasChartOptions = computed(() => {
  const isAll = selectedVistasPlatform.value === 'todas';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: isAll,
        position: 'top',
        align: 'end',
        labels: {
          boxWidth: 8,
          boxHeight: 8,
          usePointStyle: true,
          color: '#94a3b8',
          padding: 8,
          font: { size: 10, family: 'monospace', weight: 'bold' }
        }
      },
      tooltip: {
        backgroundColor: '#0f172a',
        titleColor: '#10b981',
        bodyColor: '#f8fafc',
        padding: 10,
        cornerRadius: 8,
        borderColor: 'rgba(16, 185, 129, 0.3)',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')} vistas`
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' } } },
      y: { grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, callback: (v) => formatNumber(v) } }
    }
  };
});

// ─────────────────────────────────────────────────────────────────────────────
// 4. DONUT DE PARTICIPACIÓN POR RED SOCIAL
// ─────────────────────────────────────────────────────────────────────────────
const doughnutChartData = computed(() => {
  const labels = props.distribucionPlataformas.map(p => p.nombre);
  const data = props.distribucionPlataformas.map(p => p.interacciones);
  const colors = props.distribucionPlataformas.map(p => {
    if (p.plataforma === 'threads' || p.plataforma === 'x_twitter' || p.plataforma === 'twitter') {
      return '#94a3b8';
    }
    return p.color;
  });

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: colors.length ? colors : ['#06b6d4', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444'],
        borderWidth: 2,
        borderColor: '#0f172a',
      }
    ]
  };
});

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        boxWidth: 10,
        padding: 12,
        color: '#94a3b8',
        font: { size: 11 }
      }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: function(context) {
          const total = context.dataset.data.reduce((a, b) => a + b, 0);
          const val = context.raw || 0;
          const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
          return ` ${context.label}: ${Number(val).toLocaleString('es-AR')} interacciones (${pct}%)`;
        }
      }
    }
  },
  cutout: '68%'
};

// ─────────────────────────────────────────────────────────────────────────────
// 5. BARRAS: RENDIMIENTO POR FORMATO
// ─────────────────────────────────────────────────────────────────────────────
const formatBarChartData = computed(() => {
  const labels = props.rendimientoPorFormato.map(f => f.formato);
  const dataVistas = props.rendimientoPorFormato.map(f => f.promedio_vistas);
  const dataInt = props.rendimientoPorFormato.map(f => f.promedio_interacciones);

  return {
    labels,
    datasets: [
      {
        label: 'Promedio Interacciones / Post',
        data: dataInt,
        backgroundColor: '#8b5cf6',
        borderRadius: 8,
      },
      {
        label: 'Promedio Vistas / Post',
        data: dataVistas,
        backgroundColor: '#06b6d4',
        borderRadius: 8,
      }
    ]
  };
});

const formatBarChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { boxWidth: 10, padding: 10, color: '#94a3b8', font: { size: 10 } }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ` ${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')}`
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 10 } }
    },
    y: {
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        callback: (value) => formatNumber(value)
      }
    }
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// 6. MATRIZ DE FORMATOS POR RED SOCIAL ACTIVA
// ─────────────────────────────────────────────────────────────────────────────
const formatColorsMap = {
  'Reel': '#f43f5e',
  'Foto': '#06b6d4',
  'Video': '#8b5cf6',
  'Carrusel': '#f59e0b',
  'Texto': '#10b981',
  'Historia': '#ec4899',
  'Enlace': '#3b82f6',
};

const matrizFormatosBarChartData = computed(() => {
  const redes = (props.formatosPorRed || []).filter(r => r.total_posts > 0);
  const labels = redes.map(r => r.plataforma.toUpperCase());

  const formatosSet = new Set();
  redes.forEach(r => {
    (r.formatos || []).forEach(f => formatosSet.add(f.formato));
  });
  const formatosUnicos = Array.from(formatosSet);

  const datasets = formatosUnicos.map(fmt => {
    const color = formatColorsMap[fmt] || '#64748b';
    return {
      label: fmt,
      data: redes.map(r => {
        const item = (r.formatos || []).find(f => f.formato === fmt);
        return item ? item.cantidad : 0;
      }),
      backgroundColor: color,
      borderRadius: 6,
    };
  });

  return { labels, datasets };
});

const matrizFormatosBarChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { boxWidth: 10, padding: 8, color: '#94a3b8', font: { size: 10 } }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} publicaciones`
      }
    }
  },
  scales: {
    x: {
      stacked: true,
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 10, weight: 'bold' } }
    },
    y: {
      stacked: true,
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, precision: 0 }
    }
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// 7. EJES TEMÁTICOS (TRACCIÓN, IMPACTO Y VOLUMEN)
// ─────────────────────────────────────────────────────────────────────────────
const ejesTraccionBarChartData = computed(() => {
  const sorted = [...props.distribucionEjes].sort((a, b) => (b.score_traccion_promedio || 0) - (a.score_traccion_promedio || 0));
  const labels = sorted.map(e => e.nombre);
  const data = sorted.map(e => e.score_traccion_promedio || 50);
  const colors = sorted.map(e => {
    const tr = e.score_traccion_promedio || 50;
    if (tr >= 75) return '#f59e0b';
    if (tr >= 60) return '#10b981';
    if (tr >= 40) return '#06b6d4';
    return '#64748b';
  });

  return {
    labels,
    datasets: [{
      label: 'Tracción Electoral Promedio (/100)',
      data,
      backgroundColor: colors,
      borderRadius: 6,
    }]
  };
});

const ejesTraccionBarChartOptions = computed(() => ({
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 12,
      cornerRadius: 10,
      borderColor: 'rgba(245, 158, 11, 0.3)',
      borderWidth: 1,
      callbacks: {
        label: (ctx) => {
          const sorted = [...props.distribucionEjes].sort((a, b) => (b.score_traccion_promedio || 0) - (a.score_traccion_promedio || 0));
          const eje = sorted[ctx.dataIndex];
          const val = ctx.raw;
          const cualidad = val >= 75 ? 'Sobresaliente / Viral' : (val >= 60 ? 'Sólida / Favorable' : (val >= 40 ? 'Estándar' : 'Bajo Rendimiento'));
          return ` Tracción Promedio: ${val}/100 (${cualidad})`;
        },
        afterLabel: (ctx) => {
          const sorted = [...props.distribucionEjes].sort((a, b) => (b.score_traccion_promedio || 0) - (a.score_traccion_promedio || 0));
          const eje = sorted[ctx.dataIndex];
          if (!eje) return '';
          return ` Publicaciones: ${eje.posts_count || 0} | Impacto Total: ${Number(eje.score_impacto_ponderado || 0).toLocaleString('es-AR')} pts`;
        }
      }
    }
  },
  scales: {
    x: {
      min: 0,
      max: 100,
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        callback: (value) => `${value}/100`
      }
    },
    y: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
    }
  }
}));

const ejesBarChartData = computed(() => {
  const labels = props.distribucionEjes.map(e => e.nombre);
  const data = props.distribucionEjes.map(e => e.score_impacto_ponderado ?? e.total_interacciones ?? 0);
  const colors = props.distribucionEjes.map(e => e.color_badge || '#06b6d4');

  return {
    labels,
    datasets: [{
      label: 'Score de Impacto (Puntos Ponderados)',
      data,
      backgroundColor: colors,
      borderRadius: 6,
    }]
  };
});

const ejesBarChartOptions = computed(() => ({
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 12,
      cornerRadius: 10,
      borderColor: 'rgba(251, 191, 36, 0.3)',
      borderWidth: 1,
      callbacks: {
        label: (ctx) => {
          const eje = props.distribucionEjes[ctx.dataIndex];
          const val = Number(ctx.raw).toLocaleString('es-AR');
          return ` Impacto: ${val} pts (${eje?.posts_count || 0} publicaciones)`;
        },
        afterLabel: (ctx) => {
          const eje = props.distribucionEjes[ctx.dataIndex];
          if (!eje) return '';
          return ` Tracción: ${eje.score_traccion_promedio || 50}/100 | Interacciones brutas: ${Number(eje.total_interacciones || 0).toLocaleString('es-AR')}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        callback: (value) => `${formatNumber(value)} pts`
      }
    },
    y: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
    }
  }
}));

const ejesVolumenBarChartData = computed(() => {
  const labels = props.distribucionEjes.map(e => e.nombre);
  const data = props.distribucionEjes.map(e => e.posts_count);
  const colors = props.distribucionEjes.map(e => e.color_badge || '#3b82f6');

  return {
    labels,
    datasets: [{
      label: 'Publicaciones Emitidas',
      data,
      backgroundColor: colors,
      borderRadius: 6,
    }]
  };
});

const ejesVolumenBarChartOptions = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      titleColor: '#38bdf8',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ` Publicaciones: ${ctx.raw} posts`
      }
    }
  },
  scales: {
    x: {
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        precision: 0,
      }
    },
    y: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
    }
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// 8. MODAL DE AMPLIACIÓN (FULLSCREEN / HD)
// ─────────────────────────────────────────────────────────────────────────────
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

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && modalGraficoActivo.value) {
    cerrarModalGrafico();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <div class="space-y-6">
    <!-- 1. TRILOGÍA DE EVOLUCIÓN TEMPORAL (3 GRÁFICAS PARALELAS INDEPENDIENTES) -->
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

            <div class="flex items-center gap-1.5">
              <select
                v-model="selectedComunidadPlatform"
                class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-500"
              >
                <option value="todas">🌐 Todas (Multilínea)</option>
                <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                  {{ red.plataforma.toUpperCase() }}
                </option>
              </select>

              <button
                type="button"
                @click="abrirModalGrafico('comunidad')"
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

            <div class="flex items-center gap-1.5">
              <select
                v-model="selectedScorePlatform"
                class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500"
              >
                <option value="todas">🌐 Todas (Total)</option>
                <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                  {{ red.plataforma.toUpperCase() }}
                </option>
              </select>

              <button
                type="button"
                @click="abrirModalGrafico('score')"
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

            <div class="flex items-center gap-1.5">
              <select
                v-model="selectedVistasPlatform"
                class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              >
                <option value="todas">🌐 Todas (Multilínea)</option>
                <option v-for="red in redesConVistasDisponibles" :key="red.plataforma" :value="red.plataforma">
                  {{ red.plataforma.toUpperCase() }}
                </option>
              </select>

              <button
                type="button"
                @click="abrirModalGrafico('vistas')"
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
            @click="abrirModalGrafico('cuota')"
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
            @click="abrirModalGrafico('formato')"
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

    <!-- 3. MATRIZ DE FORMATOS EN BARRAS + TRACCIÓN PROMEDIO POR EJE -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <!-- Matriz de Formatos por Red Social Activa -->
      <div class="lg:col-span-6 p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Layers class="w-4 h-4 text-cyan-500" />
              <span>Matriz de Formatos por Red Social Activa</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Distribución de tipos de contenido desplegados en cada canal
            </p>
          </div>

          <div class="flex items-center gap-2">
            <span class="text-xs font-mono text-slate-400 font-semibold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 shrink-0">
              Barras Apiladas
            </span>

            <button
              type="button"
              @click="abrirModalGrafico('matriz_formatos')"
              class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer shrink-0"
              title="Ampliar gráfico"
            >
              <Maximize2 class="w-3.5 h-3.5" />
              <span class="text-[11px]">Ampliar</span>
            </button>
          </div>
        </div>

        <div v-if="formatosPorRed && formatosPorRed.length > 0" class="h-64 w-full">
          <Bar :data="matrizFormatosBarChartData" :options="matrizFormatosBarChartOptions" />
        </div>
        <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
          No hay datos de formatos por red social registrados todavía.
        </div>
      </div>

      <!-- Tracción Promedio por Eje Temático -->
      <div class="lg:col-span-6 p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Sparkles class="w-4 h-4 text-emerald-500" />
              <span>Tracción Promedio por Eje</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Eficiencia relativa (Score 0-100) sin sesgo de cantidad de posts</p>
          </div>

          <button
            type="button"
            @click="abrirModalGrafico('ejes_traccion')"
            class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer shrink-0"
            title="Ampliar gráfico"
          >
            <Maximize2 class="w-3.5 h-3.5" />
            <span class="text-[11px]">Ampliar</span>
          </button>
        </div>

        <div v-if="distribucionEjes.length > 0" class="h-64 w-full">
          <Bar :data="ejesTraccionBarChartData" :options="ejesTraccionBarChartOptions" />
        </div>
        <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
          No hay publicaciones clasificadas por ejes temáticos todavía.
        </div>
      </div>
    </div>

    <!-- 4. IMPACTO POR EJE TEMÁTICO + VOLUMEN DE PUBLICACIONES POR EJE -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <!-- Impacto por Eje Temático -->
      <div class="lg:col-span-6 p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-2">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Target class="w-4 h-4 text-amber-500" />
              <span>Impacto por Eje Temático</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Score de impacto cívico ponderado total por propuesta</p>
          </div>

          <button
            type="button"
            @click="abrirModalGrafico('ejes_impacto')"
            class="px-2 py-1 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-all flex items-center gap-1 cursor-pointer shrink-0"
            title="Ampliar gráfico"
          >
            <Maximize2 class="w-3.5 h-3.5" />
            <span class="text-[11px]">Ampliar</span>
          </button>
        </div>

        <div v-if="distribucionEjes.length > 0" class="h-64 w-full">
          <Bar :data="ejesBarChartData" :options="ejesBarChartOptions" />
        </div>
        <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
          No hay publicaciones clasificadas por ejes temáticos todavía.
        </div>
      </div>

      <!-- Volumen de Publicaciones por Eje -->
      <div class="lg:col-span-6 p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Layers class="w-4 h-4 text-blue-500" />
              <span>Volumen de Publicaciones por Eje</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Cantidad de publicaciones emitidas para cada propuesta de campaña
            </p>
          </div>

          <button
            type="button"
            @click="abrirModalGrafico('ejes_volumen')"
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

    <!-- MODAL DE AMPLIACIÓN DE GRÁFICO (FULLSCREEN / HD) -->
    <Teleport to="body">
      <div
        v-if="modalGraficoActivo"
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-md transition-all duration-200"
        @click.self="cerrarModalGrafico"
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

            <button
              type="button"
              @click="cerrarModalGrafico"
              class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition-all cursor-pointer shrink-0"
              title="Cerrar modal (ESC)"
            >
              <X class="w-4 h-4" />
            </button>
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
              @click="cerrarModalGrafico"
              class="px-4 py-1.5 rounded-xl font-bold bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 transition-all cursor-pointer"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
