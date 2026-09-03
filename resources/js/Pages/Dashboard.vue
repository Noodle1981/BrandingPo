<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import WarRoomLayout from '../Layouts/WarRoomLayout.vue';
import MetricCard from '../Components/MetricCard.vue';
import SocialCard from '../Components/SocialCard.vue';
import Badge from '../Components/Badge.vue';
import SocialPlatformIcon from '../Components/SocialPlatformIcon.vue';
import {
  Users,
  Eye,
  TrendingUp,
  TrendingDown,
  DollarSign,
  Radio,
  Sparkles,
  ArrowRight,
  Flame,
  ShieldCheck,
  Zap,
  Target,
  Newspaper,
  ExternalLink,
  MapPin,
  Vote,
  Layers,
  ChevronDown,
  BarChart3,
  PieChart,
  Activity,
  Award,
  Heart,
  MessageCircle,
  Share2,
  Repeat,
  Bookmark,
  CheckCircle2,
  AlertCircle,
  Clock,
  ChevronRight,
  Film,
  Rocket,
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
  candidato: {
    type: Object,
    default: null
  },
  candidatos_lista: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_seguidores: '0',
      total_seguidores_raw: 0,
      total_seguidores_netos: '0',
      total_seguidores_netos_raw: 0,
      crecimiento_neto_seguidores: 0,
      crecimiento_pct_seguidores: 0,
      score_impacto_total: '0',
      score_impacto_raw: 0,
      score_promedio_post: '0',
      score_promedio_post_raw: 0,
      score_promedio_post_meta: '0',
      score_promedio_post_pct: 0,
      score_promedio_mensual: '0',
      score_promedio_mensual_raw: 0,
      score_promedio_diario: '0',
      score_promedio_diario_raw: 0,
      dias_campana_activa: 1,
      meses_campana_activa: 1,
      tendencia_score_mes: null,
      score_impacto_meta: '0',
      score_impacto_meta_raw: 0,
      score_impacto_pct: 0,
      score_impacto_estado: 'frio',
      score_impacto_estado_texto: 'Sin datos',
      score_impacto_base_texto: 'Sin audiencia neta calculada',
      total_vistas: '0',
      total_vistas_raw: 0,
      total_publicaciones: 0,
      engagement_promedio: '0%',
      inversion_pauta_total: 0,
      humor_social_promedio: '4.8',
      ratio_penetracion: '0%',
      ratio_penetracion_bruta: '0%',
      tiers_desglose: [],
      share_of_voice: '0%',
    })
  },
  desglose_mensual: {
    type: Array,
    default: () => []
  },
  redes_desglose: {
    type: Array,
    default: () => []
  },
  distribucion_plataformas: {
    type: Array,
    default: () => []
  },
  rendimiento_por_formato: {
    type: Array,
    default: () => []
  },
  distribucion_ejes: {
    type: Array,
    default: () => []
  },
  historico_mediciones: {
    type: Array,
    default: () => []
  },
  series_por_red: {
    type: Object,
    default: () => ({})
  },
  organico_vs_pauta: {
    type: Object,
    default: () => ({
      total_posts_organicos: 0,
      total_posts_pautados: 0,
      vistas_organicas: 0,
      vistas_pagadas: 0,
      interacciones_organicas: 0,
      interacciones_pautadas: 0,
      porcentaje_vistas_organicas: 100,
      porcentaje_vistas_pagadas: 0,
      inversion_total: 0,
      costo_por_interaccion: 0,
      cpm_estimado: 0,
    })
  },
  top_publicaciones: {
    type: Array,
    default: () => []
  },
  ultimas_publicaciones: {
    type: Array,
    default: () => []
  },
  ultimas_notas_prensa: {
    type: Array,
    default: () => []
  },
  hitos_booster: {
    type: Array,
    default: () => []
  },
  formatos_por_red: {
    type: Array,
    default: () => []
  }
});

const cambiarCandidato = (event) => {
  const id = event.target.value;
  router.get('/dashboard', { candidato_id: id }, { preserveState: true, replace: true });
};

const formatCurrency = (amount) => {
  if (!amount) return '$0';
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(amount);
};

const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
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
      return { color: '#E4405F', bgLight: 'bg-[#E4405F]/15' };
    case 'facebook':
      return { color: '#1877F2', bgLight: 'bg-[#1877F2]/15' };
    case 'tiktok':
      return { color: '#00F2FE', bgLight: 'bg-cyan-500/15' };
    case 'threads':
      return { color: '#000000', bgLight: 'bg-slate-900/15 dark:bg-white/10' };
    case 'x_twitter':
    case 'twitter':
    case 'x':
      return { color: '#000000', bgLight: 'bg-slate-500/15 dark:bg-white/10' };
    case 'youtube':
      return { color: '#FF0000', bgLight: 'bg-red-500/15' };
    case 'linkedin':
      return { color: '#0A66C2', bgLight: 'bg-[#0A66C2]/15' };
    default:
      return { color: '#06b6d4', bgLight: 'bg-cyan-500/15' };
  }
};

// Redes con cuenta configurada o activas del candidato para los filtros de las gráficas
const redesDisponiblesTimeline = computed(() => {
  return props.redes_desglose.filter(r => r.handle_usuario || r.esta_activo || r.seguidores > 0);
});

// Redes que manejan métricas de visualizaciones activas (> 0 vistas) para la Gráfica 3
const redesConVistasDisponibles = computed(() => {
  return props.redes_desglose.filter(r => (r.vistas_acumuladas && r.vistas_acumuladas > 0));
});

// ─────────────────────────────────────────────────────────────────────────────
// 1. GRÁFICA TEMPORAL: COMUNIDAD (SEGUIDORES NETOS) CON HITOS DE BOOSTER 🚀
// ─────────────────────────────────────────────────────────────────────────────
// 1. GRÁFICA TEMPORAL: COMUNIDAD MULTILÍNEA SIMULTÁNEA CON HITOS DE BOOSTER 🚀
// ─────────────────────────────────────────────────────────────────────────────
const selectedComunidadPlatform = ref('todas');

const comunidadChartData = computed(() => {
  const hitos = props.hitos_booster || [];
  const series = props.series_por_red || {};
  const plataformasKeys = Object.keys(series);

  // MODO 1: MULTILÍNEA SIMULTÁNEA (TODAS LAS REDES JUNTAS CON SUS COLORES OFICIALES)
  if (selectedComunidadPlatform.value === 'todas') {
    // Tomar las etiquetas cronológicas de la primera serie disponible
    const primerKey = plataformasKeys[0];
    const labels = primerKey && series[primerKey].puntos
      ? series[primerKey].puntos.map(m => m.fecha)
      : (props.historico_mediciones || []).map(m => m.fecha);

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
        // El booster DEBE coincidir con la plataforma exacta de esta línea
        const tieneBooster = hitos.some(h => 
          h.plataforma === platKey && (
            (h.fecha_raw && p.fecha_raw && h.fecha_raw === p.fecha_raw) || 
            (h.fecha && p.fecha && h.fecha === p.fecha)
          )
        );

        if (tieneBooster) {
          pointRadiuses.push(8);
          pointHoverRadiuses.push(10);
          pointBackgroundColors.push('#ef4444'); // Rojo Booster solo en su red real
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

    // Línea destacada: Suma Total de Todas las Redes (Consolidado Multired)
    const totalPuntos = props.historico_mediciones || [];
    if (totalPuntos.length > 0) {
      const colorTotal = '#06b6d4'; // Cian institucional War Room
      datasets.unshift({
        label: 'Total Multired (Suma)',
        data: totalPuntos.map(m => m.seguidores),
        borderColor: colorTotal,
        backgroundColor: `${colorTotal}10`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        borderDash: [5, 4], // Estilo punteado elegante para identificar la suma consolidada
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

  // MODO 2: RED INDIVIDUAL AISLADA
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
            const series = props.series_por_red || {};
            const punto = series[plat]?.puntos ? series[plat].puntos[idx] : null;
            if (!punto) return '';

            const hitos = (props.hitos_booster || []).filter(h => 
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
  const series = props.series_por_red || {};
  const plataformasKeys = Object.keys(series);

  // MODO 1: MULTILÍNEA SIMULTÁNEA DE SCORE (TODAS LAS REDES + TOTAL)
  if (selectedScorePlatform.value === 'todas') {
    const primerKey = plataformasKeys[0];
    const labels = primerKey && series[primerKey].puntos
      ? series[primerKey].puntos.map(m => m.fecha)
      : (props.historico_mediciones || []).map(m => m.fecha);

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

    // Línea de Suma Total (Tracción Consolidada)
    const totalPuntos = props.historico_mediciones || [];
    if (totalPuntos.length > 0) {
      const colorTotal = '#f59e0b'; // Ámbar fuego institucional
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

  // MODO 2: RED INDIVIDUAL AISLADA
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
// 2.B. GRÁFICA Y COMPARATIVA MENSUAL DE SCORE (JULIO, AGOSTO, SEPTIEMBRE...)
// ─────────────────────────────────────────────────────────────────────────────

const listaDesgloseMensual = computed(() => {
  if (props.stats?.desglose_mensual && props.stats.desglose_mensual.length > 0) {
    return props.stats.desglose_mensual;
  }
  if (props.desglose_mensual && props.desglose_mensual.length > 0) {
    return props.desglose_mensual;
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
// 3. GRÁFICA TEMPORAL: REPRODUCCIONES (VISUALIZACIONES / FACEBOOK REELS 👁️)
// ─────────────────────────────────────────────────────────────────────────────
const selectedVistasPlatform = ref('todas');

const vistasChartData = computed(() => {
  const series = props.series_por_red || {};
  const plataformasConVistas = redesConVistasDisponibles.value.map(r => r.plataforma);

  // MODO 1: MULTILÍNEA SIMULTÁNEA DE REPRODUCCIONES
  if (selectedVistasPlatform.value === 'todas') {
    const primerKey = plataformasConVistas[0] || Object.keys(series)[0];
    const labels = primerKey && series[primerKey]?.puntos
      ? series[primerKey].puntos.map(m => m.fecha)
      : (props.historico_mediciones || []).map(m => m.fecha);

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

    // Línea de Suma Total (Vistas Totales)
    const totalPuntos = props.historico_mediciones || [];
    if (totalPuntos.length > 0) {
      const colorTotal = '#10b981'; // Verde Esmeralda institucional
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

  // MODO 2: RED INDIVIDUAL AISLADA
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

// 2. Gráfico Donut de Participación por Red Social
const doughnutChartData = computed(() => {
  const labels = props.distribucion_plataformas.map(p => p.nombre);
  const data = props.distribucion_plataformas.map(p => p.interacciones);
  const colors = props.distribucion_plataformas.map(p => {
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

// 3. Gráfico de Barras: Rendimiento por Formato
const formatBarChartData = computed(() => {
  const labels = props.rendimiento_por_formato.map(f => f.formato);
  const dataVistas = props.rendimiento_por_formato.map(f => f.promedio_vistas);
  const dataInt = props.rendimiento_por_formato.map(f => f.promedio_interacciones);

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

// 4. Gráfico Horizontal de Ejes Temáticos
const ejesBarChartData = computed(() => {
  const labels = props.distribucion_ejes.map(e => e.nombre);
  const data = props.distribucion_ejes.map(e => e.total_interacciones);
  const colors = props.distribucion_ejes.map(e => e.color_badge || '#06b6d4');

  return {
    labels,
    datasets: [
      {
        label: 'Interacciones Totales',
        data,
        backgroundColor: colors,
        borderRadius: 6,
      }
    ]
  };
});

const ejesBarChartOptions = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ` Interacciones: ${Number(ctx.raw).toLocaleString('es-AR')}`
      }
    }
  },
  scales: {
    x: {
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        callback: (value) => formatNumber(value)
      }
    },
    y: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
    }
  }
};

// 4.B. Gráfico Horizontal de Volumen de Publicaciones por Eje Temático
const ejesVolumenBarChartData = computed(() => {
  const labels = props.distribucion_ejes.map(e => e.nombre);
  const data = props.distribucion_ejes.map(e => e.posts_count);
  const colors = props.distribucion_ejes.map(e => e.color_badge || '#3b82f6');

  return {
    labels,
    datasets: [
      {
        label: 'Publicaciones Emitidas',
        data,
        backgroundColor: colors,
        borderRadius: 6,
      }
    ]
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

// 5. Gráfico de Barras Apiladas: Matriz de Formatos por Red Social Activa
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
  const redes = (props.formatos_por_red || []).filter(r => r.total_posts > 0);
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

  return {
    labels,
    datasets,
  };
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

// 6. Modal para Ampliación de Gráficos a Pantalla Completa / HD
const modalGraficoActivo = ref(null);

const abrirModalGrafico = (tipo) => {
  modalGraficoActivo.value = tipo;
};

const cerrarModalGrafico = () => {
  modalGraficoActivo.value = null;
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
  <Head :title="candidato ? `Sala de Situación: ${candidato.nombre_completo}` : 'Dashboard Central'" />

  <WarRoomLayout>
    <!-- 1. CABECERA ESTRATÉGICA (SALA DE SITUACIÓN / WAR ROOM) -->
    <div v-if="candidato" class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
      <!-- Glow decorativo de fondo -->
      <div class="absolute -right-20 -top-20 w-80 h-80 bg-cyan-500/10 dark:bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
        <!-- Candidato Identity -->
        <div class="flex items-start sm:items-center gap-4">
          <div class="relative shrink-0">
            <img
              :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="candidato.nombre_completo"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 shadow-md"
              :style="{ borderColor: candidato.color_hex || '#06b6d4' }"
            />
            <span
              v-if="candidato.es_propio"
              class="absolute -bottom-1.5 -right-1.5 px-2 py-0.5 rounded-md bg-cyan-500 text-slate-950 font-extrabold text-[10px] uppercase shadow-xs tracking-wider"
            >
              Propio
            </span>
          </div>

          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {{ candidato.nombre_completo }}
              </h1>
              <Badge variant="estado" :value="candidato.estado_politico" size="sm" />
            </div>

            <p class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300 mt-0.5">
              {{ candidato.cargo_aspirado }} &bull; <span class="text-slate-500 dark:text-slate-400 font-normal">{{ candidato.partido_coalicion }}</span>
            </p>

            <div class="mt-2 flex items-center gap-3.5 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
              <span class="inline-flex items-center gap-1">
                <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                {{ candidato.territorio_nombre }}
              </span>
              <span v-if="candidato.padron_electoral" class="inline-flex items-center gap-1 font-mono font-medium">
                <Vote class="w-3.5 h-3.5 text-emerald-500" />
                Padrón: {{ Number(candidato.padron_electoral).toLocaleString('es-AR') }} votantes
              </span>
              <span class="inline-flex items-center gap-1">
                <Layers class="w-3.5 h-3.5 text-violet-500" />
                {{ candidato.ciclo_nombre }}
              </span>
            </div>
          </div>
        </div>

        <!-- Selector & Accesos Rápidos -->
        <div class="flex items-center gap-2.5 flex-wrap self-start md:self-center">
          <!-- Selector de Candidato -->
          <div v-if="candidatos_lista.length > 1" class="relative">
            <select
              :value="candidato.id"
              @change="cambiarCandidato"
              class="appearance-none bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl pl-3 pr-8 py-2.5 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all cursor-pointer shadow-2xs"
            >
              <option v-for="cand in candidatos_lista" :key="cand.id" :value="cand.id">
                {{ cand.es_propio ? '⭐ ' : '' }}{{ cand.nombre_completo }} ({{ cand.cargo_aspirado }})
              </option>
            </select>
            <ChevronDown class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>

          <Link
            href="/mi-candidato"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs transition-all shadow-xs hover:scale-102"
          >
            <Sparkles class="w-3.5 h-3.5" />
            <span>Mi Candidato</span>
          </Link>
          <Link
            href="/feed"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold text-xs transition-all border border-slate-200 dark:border-slate-700"
          >
            <Radio class="w-3.5 h-3.5 text-cyan-500" />
            <span>Muro Social</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- 2. HUD CENTRAL DE KPIS ESTRATÉGICOS (DIVIDIDO EN 2 BLOQUES CONTEXTUALES) -->
    <div class="space-y-4">
      
      <!-- BLOQUE 1: AUDIENCIA, TERRITORIO & CLIMA ELECTORAL (3 TARJETAS GRANDES) -->
      <div class="space-y-2">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-1">
          <Users class="w-3.5 h-3.5 text-cyan-500" />
          <span>Audiencia, Territorio & Clima Social</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- KPI 1: Comunidad Multired & Crecimiento Neto (con Tiers) -->
          <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Comunidad Total</span>
              <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
                <Users class="w-4 h-4" />
              </div>
            </div>
            <div class="mt-3">
              <div class="flex items-baseline justify-between gap-1">
                <p class="text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                  {{ stats.total_seguidores }}
                </p>
                <span
                  v-if="stats.total_seguidores_netos && stats.total_seguidores_netos !== stats.total_seguidores"
                  class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 font-mono px-2 py-0.5 rounded-md bg-cyan-500/10"
                  :title="`Alcance Único Neto por Tiers: ~${stats.total_seguidores_netos} personas reales desduplicadas`"
                >
                  ~{{ stats.total_seguidores_netos }} netos
                </span>
              </div>
              <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold" :class="stats.crecimiento_neto_seguidores >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'">
                <TrendingUp v-if="stats.crecimiento_neto_seguidores >= 0" class="w-3.5 h-3.5" />
                <TrendingDown v-else class="w-3.5 h-3.5" />
                <span>{{ stats.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ Number(stats.crecimiento_neto_seguidores).toLocaleString('es-AR') }} neto</span>
                <span class="text-slate-400 font-normal">vs Punto Cero</span>
              </div>
            </div>
          </div>

          <!-- KPI 2: Penetración sobre el Padrón (Neto Real por Tiers) -->
          <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penetración Padrón</span>
              <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                <Target class="w-4 h-4" />
              </div>
            </div>
            <div class="mt-3">
              <div class="flex items-baseline justify-between gap-1">
                <p class="text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                  {{ stats.ratio_penetracion }}
                </p>
                <span class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shrink-0">
                  Neto Real
                </span>
              </div>

              <!-- Barra de Progreso de Penetración sobre el Padrón -->
              <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`~${stats.total_seguidores_netos} de ${Number(candidato.padron_electoral).toLocaleString('es-AR')} electores únicos alcanzados`">
                <div
                  class="h-full rounded-full bg-blue-500 transition-all duration-500"
                  :style="{ width: `${Math.min(stats.ratio_penetracion_raw || 0, 100)}%` }"
                ></div>
              </div>

              <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                <span class="text-[11px] text-slate-700 dark:text-slate-300">~{{ stats.total_seguidores_netos }} electores únicos</span>
                <span class="text-[11px] text-slate-400">de {{ Number(candidato.padron_electoral).toLocaleString('es-AR') }}</span>
              </div>
            </div>
          </div>

          <!-- KPI 3: Clima de Humor Social Dinámico -->
          <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Humor Social</span>
              <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <Flame class="w-4 h-4" />
              </div>
            </div>
            <div class="mt-3">
              <div class="flex items-baseline justify-between gap-1">
                <p class="text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight flex items-center gap-1.5">
                  <span>{{ stats.humor_social_promedio }}</span>
                  <span class="text-xs text-slate-400 font-normal">/ 5.0</span>
                </p>
                <span
                  class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md border shrink-0"
                  :class="{
                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20': (stats.humor_social_promedio_raw || 5) >= 4.5,
                    'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20': (stats.humor_social_promedio_raw || 5) >= 3.5 && (stats.humor_social_promedio_raw || 5) < 4.5,
                    'bg-rose-500/10 text-rose-500 border-rose-500/20': (stats.humor_social_promedio_raw || 5) < 3.5
                  }"
                >
                  {{ stats.humor_clima_texto || 'Muy Favorable' }}
                </span>
              </div>
              <div class="mt-2.5 flex items-center justify-between">
                <div class="flex items-center gap-1 text-sm">
                  <span
                    v-for="star in 5"
                    :key="star"
                    :class="star <= Math.round(stats.humor_social_promedio_raw || 5) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'"
                  >★</span>
                </div>
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">0% Rechazo</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BLOQUE 2: ALCANCE, TRACCIÓN & RENDIMIENTO DE CAMPAÑA (4 TARJETAS GRANDES) -->
      <div class="space-y-2 pt-1">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-1">
          <Flame class="w-3.5 h-3.5 text-amber-500" />
          <span>Alcance, Tracción & Metas de Contenido</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- KPI 4: Visualizaciones Acumuladas & Eficiencia de Alcance -->
          <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Alcance & Vistas</span>
              <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <Eye class="w-4 h-4" />
              </div>
            </div>
            <div class="mt-3">
              <div class="flex items-baseline justify-between gap-1">
                <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                  {{ stats.total_vistas }}
                </p>
                <span
                  class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0"
                  :title="`Tasa de interacción activa: ${stats.engagement_promedio}`"
                >
                  {{ stats.engagement_promedio }} ER
                </span>
              </div>
              <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span class="font-mono">{{ stats.vistas_promedio_post || '0' }} vistas / post</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-[11px]">{{ stats.engagement_calidad_texto || 'Alto Involucramiento' }}</span>
              </div>
            </div>
          </div>

          <!-- KPI 5: Score Promedio por Publicación (pts/post) -->
          <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score / Post</span>
              <div class="w-8 h-8 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center">
                <Sparkles class="w-4 h-4" />
              </div>
            </div>
            <div class="mt-3">
              <div class="flex items-baseline justify-between gap-1">
                <div class="flex items-baseline gap-1.5">
                  <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                    {{ stats.score_promedio_post || '0' }}
                  </p>
                  <span class="text-xs font-semibold text-violet-600 dark:text-violet-400 font-mono">pts / post</span>
                </div>

                <span
                  class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md border shrink-0 flex items-center gap-1"
                  :class="{
                    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20': stats.score_promedio_post_pct >= 100,
                    'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20': stats.score_promedio_post_pct >= 60 && stats.score_promedio_post_pct < 100,
                    'bg-rose-500/10 text-rose-500 border-rose-500/20': stats.score_promedio_post_pct < 60
                  }"
                  :title="`Meta Territorial: ${stats.score_promedio_post_meta} pts (${stats.meta_score_base_texto})`"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="{
                    'bg-emerald-500': stats.score_promedio_post_pct >= 100,
                    'bg-amber-500': stats.score_promedio_post_pct >= 60 && stats.score_promedio_post_pct < 100,
                    'bg-rose-500': stats.score_promedio_post_pct < 60
                  }"></span>
                  <span>{{ stats.score_promedio_post_pct || 0 }}%</span>
                </span>
              </div>

              <!-- Barra de Progreso hacia la Meta Territorial -->
              <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`${stats.score_promedio_post} de ${stats.score_promedio_post_meta} pts objetivo`">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="{
                    'bg-emerald-500': stats.score_promedio_post_pct >= 100,
                    'bg-amber-500': stats.score_promedio_post_pct >= 60 && stats.score_promedio_post_pct < 100,
                    'bg-rose-500': stats.score_promedio_post_pct < 60
                  }"
                  :style="{ width: `${Math.min(stats.score_promedio_post_pct || 0, 100)}%` }"
                ></div>
              </div>

              <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <span class="font-mono">{{ stats.total_publicaciones }} posts</span>
                <span class="font-mono text-[10px] text-slate-400 dark:text-slate-500">
                  Meta: <strong class="text-slate-700 dark:text-slate-300 font-bold">{{ stats.score_promedio_post_meta }}</strong> pts
                </span>
              </div>
            </div>
          </div>

          <!-- KPI 6: Score Promedio Mensual (pts/mes) -->
          <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score / Mes</span>
              <button
                type="button"
                @click="abrirModalGrafico('score_mensual')"
                class="w-8 h-8 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-500 flex items-center justify-center transition-colors cursor-pointer"
                title="Ver comparativa detallada por meses (Julio, Agosto, Septiembre)"
              >
                <Calendar class="w-4 h-4" />
              </button>
            </div>
            <div class="mt-3">
              <div class="flex items-baseline justify-between gap-1">
                <div class="flex items-baseline gap-1.5">
                  <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                    {{ stats.score_promedio_mensual || '0' }}
                  </p>
                  <span class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 font-mono">pts / mes</span>
                </div>

                <div v-if="stats.tendencia_score_mes" class="shrink-0">
                  <span
                    class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md flex items-center gap-0.5"
                    :class="stats.tendencia_score_mes.variacion_pct >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-500 border border-rose-500/20'"
                    :title="`Tendencia de Volumen: ${stats.tendencia_score_mes.variacion_pct >= 0 ? '+' : ''}${stats.tendencia_score_mes.variacion_pct}% vs mes anterior`"
                  >
                    <TrendingUp v-if="stats.tendencia_score_mes.variacion_pct >= 0" class="w-3 h-3" />
                    <TrendingDown v-else class="w-3 h-3" />
                    <span>{{ stats.tendencia_score_mes.variacion_pct >= 0 ? '+' : '' }}{{ stats.tendencia_score_mes.variacion_pct }}%</span>
                  </span>
                </div>
              </div>

              <!-- Barra de Progreso hacia la Meta Mensual Territorial -->
              <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`${stats.score_promedio_mensual} de ${stats.score_promedio_mensual_meta} pts objetivo mensual`">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="{
                    'bg-emerald-500': stats.score_promedio_mensual_pct >= 100,
                    'bg-amber-500': stats.score_promedio_mensual_pct >= 60 && stats.score_promedio_mensual_pct < 100,
                    'bg-rose-500': stats.score_promedio_mensual_pct < 60
                  }"
                  :style="{ width: `${Math.min(stats.score_promedio_mensual_pct || 0, 100)}%` }"
                ></div>
              </div>

              <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <span class="font-mono text-[10px] text-slate-400 dark:text-slate-500">
                  Meta: <strong class="text-slate-700 dark:text-slate-300 font-bold">{{ stats.score_promedio_mensual_meta }}</strong> pts
                </span>
                <button
                  type="button"
                  @click="abrirModalGrafico('score_mensual')"
                  class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-0.5 cursor-pointer"
                >
                  <span>Ver meses</span>
                  <span>→</span>
                </button>
              </div>
            </div>
          </div>

          <!-- KPI 7: Score Campaña (Presión Electoral sobre Padrón) -->
          <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score Campaña</span>
              <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <Flame class="w-4 h-4" />
              </div>
            </div>
            <div class="mt-3">
              <div class="flex items-baseline justify-between gap-1">
                <div class="flex items-baseline gap-1.5">
                  <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                    {{ stats.score_impacto_total }}
                  </p>
                  <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 font-mono">pts tot.</span>
                </div>

                <span
                  class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0"
                  :title="`Presión Electoral: ${stats.avance_campana_padron_pct}% del padrón total`"
                >
                  {{ stats.avance_campana_padron_pct || 0 }}% padrón
                </span>
              </div>

              <!-- Barra de Progreso hacia el 100% del Padrón Electoral -->
              <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`${stats.score_impacto_total} de ${stats.meta_score_campana} pts del padrón`">
                <div
                  class="h-full rounded-full bg-amber-500 transition-all duration-500"
                  :style="{ width: `${Math.min(stats.avance_campana_padron_pct || 0, 100)}%` }"
                ></div>
              </div>

              <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                <span class="text-[10px] text-slate-700 dark:text-slate-300 font-bold flex items-center gap-1" :title="`Mes récord: ${stats.record_mensual_score} pts en ${stats.record_mensual_nombre}`">
                  <span>🏆 Récord:</span>
                  <strong class="text-amber-600 dark:text-amber-400">{{ stats.record_mensual_score }}</strong>
                  <span class="text-slate-400 text-[9px]">({{ stats.record_mensual_corto }})</span>
                </span>
                <span class="text-[10px] text-slate-400">
                  {{ stats.score_promedio_diario || '0' }} pts/d
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- 3. CENTRO DE ANALÍTICA VISUAL (WAR ROOM) -->
    <div class="space-y-6">
      
      <!-- 1. TRILOGÍA DE EVOLUCIÓN TEMPORAL (3 GRÁFICAS PARALELAS INDEPENDIENTES) -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        
        <!-- GRÁFICA 1: COMUNIDAD (SEGUIDORES NETOS) CON HITOS DE BOOSTER 🚀 -->
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
                <!-- Selector Red -->
                <select
                  v-model="selectedComunidadPlatform"
                  class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-cyan-500"
                >
                  <option value="todas">🌐 Todas (Multilínea)</option>
                  <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                    {{ red.plataforma.toUpperCase() }}
                  </option>
                </select>

                <!-- Botón Ampliar -->
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

        <!-- GRÁFICA 2: TRACCIÓN / SCORE DE IMPACTO PONDERADO (PUNTOS 🔥) -->
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
                <!-- Selector Red -->
                <select
                  v-model="selectedScorePlatform"
                  class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500"
                >
                  <option value="todas">🌐 Todas (Total)</option>
                  <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                    {{ red.plataforma.toUpperCase() }}
                  </option>
                </select>

                <!-- Botón Ampliar -->
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

        <!-- GRÁFICA 3: REPRODUCCIONES / VIDEO & FACEBOOK REELS 👁️ -->
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
                <!-- Selector Red -->
                <select
                  v-model="selectedVistasPlatform"
                  class="px-2 py-1 text-xs font-mono font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                >
                  <option value="todas">🌐 Todas (Multilínea)</option>
                  <option v-for="red in redesConVistasDisponibles" :key="red.plataforma" :value="red.plataforma">
                    {{ red.plataforma.toUpperCase() }}
                  </option>
                </select>

                <!-- Botón Ampliar -->
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

      <!-- 2. CUOTA DE ATENCIÓN POR RED (5 COLS) + RENDIMIENTO POR FORMATO (7 COLS) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        <!-- Gráfico Donut (5 cols): Cuota de Atención por Red Social -->
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

          <!-- Tarjetas de desglose de todas las redes activas/configuradas -->
          <div v-if="distribucion_plataformas.length > 0" class="flex flex-wrap items-stretch justify-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-800/80 font-mono">
            <div
              v-for="red in distribucion_plataformas"
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

        <!-- Gráfico Bar (7 cols): Rendimiento por Formato -->
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

      <!-- 3. FILA DUAL: MATRIZ DE FORMATOS EN BARRAS (6 COLS) + IMPACTO POR EJE (6 COLS) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
        
        <!-- Matriz de Formatos por Red Social Activa (Gráfico de Barras Apiladas - 6 cols) -->
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

          <div v-if="formatos_por_red && formatos_por_red.length > 0" class="h-64 w-full">
            <Bar :data="matrizFormatosBarChartData" :options="matrizFormatosBarChartOptions" />
          </div>
          <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
            No hay datos de formatos por red social registrados todavía.
          </div>
        </div>

        <!-- Impacto por Eje Temático (Gráfico de Barras - 6 cols) -->
        <div class="lg:col-span-6 p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
          <div class="flex items-center justify-between gap-2">
            <div>
              <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Target class="w-4 h-4 text-amber-500" />
                <span>Impacto por Eje Temático</span>
              </h2>
              <p class="text-xs text-slate-500 dark:text-slate-400">Interacciones logradas por cada propuesta</p>
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

          <div v-if="distribucion_ejes.length > 0" class="h-64 w-full">
            <Bar :data="ejesBarChartData" :options="ejesBarChartOptions" />
          </div>
          <div v-else class="h-64 flex items-center justify-center text-xs text-slate-400">
            No hay publicaciones clasificadas por ejes temáticos todavía.
          </div>
        </div>
      </div>

      <!-- 4. FILA INFERIOR: VOLUMEN DE PUBLICACIONES POR EJE (OFICIAL) -->
      <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Layers class="w-4 h-4 text-blue-500" />
              <span>Volumen de Publicaciones por Eje</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
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

        <div v-if="distribucion_ejes.length > 0" class="h-60 w-full">
          <Bar :data="ejesVolumenBarChartData" :options="ejesVolumenBarChartOptions" />
        </div>
        <div v-else class="h-60 flex items-center justify-center text-xs text-slate-400">
          No hay publicaciones clasificadas por ejes temáticos todavía.
        </div>
      </div>

    </div>

    <!-- 4. MALLA DE CANALES SOCIALES (7 CANALES EN 1 SOLA FILA ESTILO MI-PERFIL) -->
    <div class="space-y-3.5">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Sparkles class="w-4 h-4 text-cyan-500" />
            <span>Auditoría de Canales Sociales Conectados</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">Haz clic en cualquier canal para auditar sus métricas o configurar su Punto Cero</p>
        </div>
        <Link href="/mi-candidato" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
          Configurar Punto Cero &rarr;
        </Link>
      </div>

      <!-- Grid de 7 Canales en 1 Sola Fila Continua -->
      <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 lg:grid-cols-7 gap-2.5 sm:gap-3">
        <Link
          v-for="red in redes_desglose"
          :key="red.plataforma"
          :href="red.id && red.color_estado !== 'gris' ? `/perfiles-sociales/${red.id}/metricas` : '/mi-candidato'"
          class="p-3 sm:p-3.5 rounded-2xl border-2 transition-all flex flex-col items-center justify-between text-center gap-2 cursor-pointer relative shadow-xs group"
          :class="[
            red.color_estado === 'gris'
              ? 'border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-400 opacity-75 hover:opacity-100 hover:border-slate-400 hover:bg-white dark:hover:bg-slate-900'
              : (red.color_estado === 'azul'
                  ? 'border-blue-500/60 bg-blue-500/5 hover:border-blue-500 hover:shadow-md'
                  : (red.color_estado === 'verde'
                      ? 'border-emerald-500/60 bg-emerald-500/5 hover:border-emerald-500 hover:shadow-md'
                      : 'border-rose-500/40 bg-rose-500/5 hover:border-rose-500 hover:shadow-md'))
          ]"
        >
          <!-- Logo Oficial de la Red -->
          <div class="flex items-center justify-center w-9 h-9 rounded-xl shadow-2xs shrink-0" :class="getSocialMeta(red.plataforma).bgLight">
            <SocialPlatformIcon :platform="red.plataforma" size="md" />
          </div>

          <div class="min-w-0 w-full">
            <span class="font-bold text-xs leading-tight block text-slate-900 dark:text-slate-100 truncate">
              {{ red.plataforma.replace('_', ' ') }}
            </span>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate mt-0.5 font-mono">
              {{ red.handle_usuario || '@sin_configurar' }}
            </span>
          </div>

          <!-- Pill de Estado Oficial -->
          <span
            class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider font-mono truncate max-w-full"
            :class="tabBadgeStyle(red.color_estado).pill"
          >
            {{ tabBadgeStyle(red.color_estado).label }}
          </span>

          <!-- Métricas o Estado -->
          <div v-if="red.color_estado === 'verde' || red.color_estado === 'azul'" class="w-full pt-1.5 border-t border-slate-200/60 dark:border-slate-800/80 font-mono text-[10px] flex items-center justify-between text-slate-500 dark:text-slate-400">
            <span>{{ formatNumber(red.seguidores) }} seg</span>
            <span class="text-cyan-600 dark:text-cyan-400 font-bold">{{ red.publicaciones_count }} posts</span>
          </div>
          <div v-else-if="red.color_estado === 'rojo'" class="w-full pt-1.5 border-t border-rose-500/20 font-mono text-[9px] text-rose-500 truncate">
            0 publicaciones
          </div>
          <div v-else class="w-full pt-1.5 border-t border-dashed border-slate-300 dark:border-slate-800 font-mono text-[9px] text-slate-400 truncate">
            Sin vincular
          </div>
        </Link>
      </div>
    </div>

    <!-- 5. INTELIGENCIA DE PAUTA (ROI) & OBSERVATORIO DE PRENSA (2 COLUMNAS) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <!-- Módulo 1: Inteligencia de Pauta vs Orgánico & Booster en Vivo -->
      <div class="p-5 sm:p-6 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 shadow-md space-y-4 flex flex-col justify-between">
        <div>
          <!-- Cabecera del Módulo con Toggle / Badge de Estado -->
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-mono uppercase tracking-wider text-cyan-400 font-bold flex items-center gap-1.5">
              <DollarSign class="w-4 h-4" />
              Eficiencia Publicitaria (ROI)
            </span>
            <span v-if="organico_vs_pauta?.primer_post_impulsado" class="text-[10px] px-2.5 py-0.5 rounded-full bg-violet-500/25 text-violet-300 font-mono font-bold flex items-center gap-1 border border-violet-500/30">
              <Rocket class="w-3 h-3 text-violet-400" />
              <span>Booster Activo</span>
            </span>
            <span v-else class="text-[10px] px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-mono">
              Inversión Ads
            </span>
          </div>

          <!-- Métricas Generales de Inversión y Eficiencia -->
          <div class="grid grid-cols-2 gap-3 font-mono">
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
              <span class="text-[10px] text-slate-400 block uppercase">Inversión Total</span>
              <span class="text-xl font-extrabold text-white">{{ formatCurrency(organico_vs_pauta?.inversion_total ?? 0) }}</span>
              <span class="text-[10px] text-slate-400 block mt-0.5">{{ organico_vs_pauta?.total_posts_pautados ?? 0 }} anuncio(s) activo(s)</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
              <span class="text-[10px] text-slate-400 block uppercase">
                {{ organico_vs_pauta?.primer_post_impulsado?.costo_por_like ? 'Costo / Like Ganado' : 'Costo / Interacción' }}
              </span>
              <span class="text-xl font-extrabold text-emerald-400">
                {{ formatCurrency(organico_vs_pauta?.primer_post_impulsado?.costo_por_like ?? organico_vs_pauta?.costo_por_interaccion ?? 0) }}
              </span>
              <span class="text-[10px] text-slate-400 block mt-0.5">
                {{ organico_vs_pauta?.primer_post_impulsado ? 'CPL atribuible a pauta' : 'por reacción/comentario' }}
              </span>
            </div>
          </div>

          <!-- SI HAY UN POST IMPULSADO CON BOOSTER: DETALLE DE AUDITORÍA (Corte Orgánico vs Ganado con Pauta) -->
          <div v-if="organico_vs_pauta?.primer_post_impulsado" class="mt-4 p-3.5 rounded-2xl bg-slate-900/90 border border-violet-500/30 space-y-2.5">
            <!-- Header del Post Impulsado -->
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex items-center gap-2">
                <Badge :variant="organico_vs_pauta.primer_post_impulsado.plataforma" size="sm" />
                <span class="text-xs font-bold text-slate-200 truncate" :title="organico_vs_pauta.primer_post_impulsado.titulo">
                  {{ organico_vs_pauta.primer_post_impulsado.titulo }}
                </span>
              </div>
              <a
                v-if="organico_vs_pauta.primer_post_impulsado.url_post"
                :href="organico_vs_pauta.primer_post_impulsado.url_post"
                target="_blank"
                rel="noopener noreferrer"
                class="text-slate-400 hover:text-cyan-400 p-1 shrink-0"
                title="Ver publicación oficial"
              >
                <ExternalLink class="w-3.5 h-3.5" />
              </a>
            </div>

            <!-- Desglose Granular: Base Orgánica vs Ganado con Booster -->
            <div class="grid grid-cols-2 gap-2 pt-1 font-mono text-[11px]">
              <!-- Columna 1: Tracción Orgánica Previa al Corte -->
              <div class="p-2 rounded-xl bg-cyan-500/10 border border-cyan-500/20">
                <span class="text-[9px] uppercase tracking-wider text-cyan-400 block font-bold">Base Orgánica (Previa)</span>
                <span class="text-sm font-extrabold text-cyan-300">
                  {{ formatNumber(organico_vs_pauta.primer_post_impulsado.base_organica_likes) }} likes
                </span>
                <span class="text-[9px] text-cyan-400/80 block mt-0.5">
                  Tracción natural sin gasto ({{ organico_vs_pauta.primer_post_impulsado.pct_organico }}%)
                </span>
              </div>

              <!-- Columna 2: Ganado desde que se puso el Booster -->
              <div class="p-2 rounded-xl bg-violet-500/15 border border-violet-500/30">
                <span class="text-[9px] uppercase tracking-wider text-violet-300 block font-bold flex items-center gap-1">
                  <Rocket class="w-2.5 h-2.5 text-violet-400" />
                  <span>Ganados con Pauta</span>
                </span>
                <span class="text-sm font-extrabold text-emerald-400">
                  +{{ formatNumber(organico_vs_pauta.primer_post_impulsado.ganados_pauta_likes) }} likes
                </span>
                <span class="text-[9px] text-violet-300/80 block mt-0.5">
                  Generados por Booster ({{ organico_vs_pauta.primer_post_impulsado.pct_pautado }}%)
                </span>
              </div>
            </div>

            <!-- Barra de Distribución Orgánico vs Pautado del Post -->
            <div class="space-y-1 text-xs">
              <div class="flex justify-between text-[10px] font-mono">
                <span class="text-cyan-400 font-bold">Orgánico: {{ organico_vs_pauta.primer_post_impulsado.pct_organico }}%</span>
                <span class="text-violet-400 font-bold">Booster: {{ organico_vs_pauta.primer_post_impulsado.pct_pautado }}%</span>
              </div>
              <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden flex">
                <div class="bg-cyan-500 h-full transition-all" :style="{ width: `${organico_vs_pauta.primer_post_impulsado.pct_organico}%` }"></div>
                <div class="bg-violet-500 h-full transition-all" :style="{ width: `${organico_vs_pauta.primer_post_impulsado.pct_pautado}%` }"></div>
              </div>
            </div>

            <!-- Estado del Booster y Fecha de Corte -->
            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-800/80 font-mono">
              <span class="flex items-center gap-1 text-slate-300">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Estado: <strong>{{ organico_vs_pauta.primer_post_impulsado.estado }}</strong></span>
              </span>
              <span v-if="organico_vs_pauta.primer_post_impulsado.fecha_booster">
                Corte: {{ organico_vs_pauta.primer_post_impulsado.fecha_booster }}
              </span>
            </div>
          </div>

          <!-- SI NO HAY POST IMPULSADO: BARRA GLOBAL GENÉRICA -->
          <div v-else class="mt-4 space-y-1.5 text-xs">
            <div class="flex justify-between text-[11px] font-mono">
              <span class="text-cyan-400">Orgánico: {{ organico_vs_pauta?.porcentaje_interacciones_organicas ?? organico_vs_pauta?.porcentaje_vistas_organicas ?? 100 }}%</span>
              <span class="text-violet-400">Pautado: {{ organico_vs_pauta?.porcentaje_interacciones_pautadas ?? organico_vs_pauta?.porcentaje_vistas_pagadas ?? 0 }}%</span>
            </div>
            <div class="w-full h-2.5 rounded-full bg-slate-800 overflow-hidden flex">
              <div class="bg-cyan-500 h-full transition-all" :style="{ width: `${organico_vs_pauta?.porcentaje_interacciones_organicas ?? 100}%` }"></div>
              <div class="bg-violet-500 h-full transition-all" :style="{ width: `${organico_vs_pauta?.porcentaje_interacciones_pautadas ?? 0}%` }"></div>
            </div>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
          <span>
            Total Post: <strong class="text-white font-mono">{{ organico_vs_pauta?.primer_post_impulsado ? formatNumber(organico_vs_pauta.primer_post_impulsado.total_likes) + ' likes' : formatCurrency(organico_vs_pauta?.cpm_estimado ?? 0) }}</strong>
          </span>
          <Link href="/predictor" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
            <span>Simulador de Presupuesto</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>

      <!-- Módulo 2: Menciones en Prensa Digital & Medios -->
      <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Newspaper class="w-4 h-4 text-cyan-500" />
              <span>Observatorio de Prensa & Medios</span>
            </h2>
            <Link href="/medios" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
              Ver Todos &rarr;
            </Link>
          </div>

          <div v-if="ultimas_notas_prensa.length > 0" class="space-y-2.5">
            <a
              v-for="nota in ultimas_notas_prensa"
              :key="nota.id"
              :href="nota.url_nota || '#'"
              target="_blank"
              class="block p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 transition-all group"
            >
              <div class="flex items-center justify-between gap-2 mb-1">
                <span class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 uppercase font-mono">
                  {{ nota.medio_nombre }}
                </span>
                <Badge variant="tono" :value="nota.tono_mencion" size="xs" />
              </div>
              <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors line-clamp-2 leading-relaxed">
                {{ nota.titulo }}
              </p>
              <span class="text-[10px] text-slate-400 mt-1 block font-mono">{{ nota.fecha }}</span>
            </a>
          </div>

          <div v-else class="text-xs text-slate-400 text-center py-8">
            No se registran notas de prensa vinculadas al candidato.
          </div>
        </div>

        <div class="pt-2 text-right">
          <Link href="/medios" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline inline-flex items-center gap-1">
            <span>Ir al Clipping Completo</span>
            <ChevronRight class="w-4 h-4" />
          </Link>
        </div>
      </div>
    </div>

    <!-- 6. MODAL DE AMPLIACIÓN DE GRÁFICO (WAR ROOM FULLSCREEN / HD) -->
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
                    modalGraficoActivo === 'ejes_impacto' ? 'Impacto por Eje Temático (Interacciones)' :
                    'Volumen de Publicaciones por Eje Temático'
                  }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                  {{
                    modalGraficoActivo === 'score_mensual'
                      ? 'Comparativa del Score Promedio por Post mes a mes (Julio, Agosto, Septiembre...)'
                      : 'Visualización detallada de alta resolución'
                  }}
                </p>
              </div>
            </div>

            <!-- Controles y Filtros del Modal (Filtro interactivo de red + botón cerrar) -->
            <div class="flex items-center gap-2.5 shrink-0">
              <!-- Filtro de Red para Score al ampliar -->
              <div v-if="modalGraficoActivo === 'score'" class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hidden sm:inline">Canal:</span>
                <select
                  v-model="selectedScorePlatform"
                  class="px-2.5 py-1.5 text-xs font-mono font-bold rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300 focus:outline-none focus:ring-1 focus:ring-amber-500 cursor-pointer shadow-xs"
                >
                  <option value="todas">🌐 Todas (Multilínea)</option>
                  <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                    {{ red.plataforma.toUpperCase() }}
                  </option>
                </select>
              </div>

              <!-- Filtro de Red para Comunidad al ampliar -->
              <div v-if="modalGraficoActivo === 'comunidad'" class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hidden sm:inline">Canal:</span>
                <select
                  v-model="selectedComunidadPlatform"
                  class="px-2.5 py-1.5 text-xs font-mono font-bold rounded-xl border border-cyan-500/30 bg-cyan-500/10 text-cyan-700 dark:text-cyan-300 focus:outline-none focus:ring-1 focus:ring-cyan-500 cursor-pointer shadow-xs"
                >
                  <option value="todas">🌐 Todas (Multilínea)</option>
                  <option v-for="red in redesDisponiblesTimeline" :key="red.plataforma" :value="red.plataforma">
                    {{ red.plataforma.toUpperCase() }}
                  </option>
                </select>
              </div>

              <!-- Filtro de Red para Vistas al ampliar -->
              <div v-if="modalGraficoActivo === 'vistas'" class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hidden sm:inline">Canal:</span>
                <select
                  v-model="selectedVistasPlatform"
                  class="px-2.5 py-1.5 text-xs font-mono font-bold rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer shadow-xs"
                >
                  <option value="todas">🌐 Todas (Multilínea)</option>
                  <option v-for="red in redesConVistasDisponibles" :key="red.plataforma" :value="red.plataforma">
                    {{ red.plataforma.toUpperCase() }}
                  </option>
                </select>
              </div>

              <button
                type="button"
                @click="cerrarModalGrafico"
                class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer shrink-0"
                title="Cerrar modal (Esc)"
              >
                <X class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Contenido del Gráfico Ampliado -->
          <div class="p-6 flex-1 overflow-y-auto min-h-[460px] flex flex-col justify-start">
            <!-- Caso Especial: Desglose Mensual de Score con Gráfica Dual y Tabla Comparativa -->
            <div v-if="modalGraficoActivo === 'score_mensual'" class="space-y-6">
              <!-- Gráfica de Barras por Mes -->
              <div class="h-64 sm:h-72 w-full bg-slate-50/50 dark:bg-slate-950/40 rounded-2xl p-3 border border-slate-100 dark:border-slate-800/80">
                <Bar :data="scoreMensualChartData" :options="scoreMensualChartOptions" />
              </div>

              <!-- Tabla Comparativa Mes a Mes -->
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
  </WarRoomLayout>
</template>
