<script setup>
import { ref, computed } from 'vue';
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
  ChevronRight
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
      crecimiento_neto_seguidores: 0,
      crecimiento_pct_seguidores: 0,
      score_impacto_total: '0',
      score_impacto_raw: 0,
      total_vistas: '0',
      total_vistas_raw: 0,
      total_publicaciones: 0,
      engagement_promedio: '0%',
      inversion_pauta_total: 0,
      humor_social_promedio: '4.8',
      ratio_penetracion: '0%',
      share_of_voice: '0%',
    })
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

// 1. Gráfico de Evolución Temporal (Line Chart)
const chartTimeMetric = ref('seguidores'); // 'seguidores' | 'vistas'

const timelineChartData = computed(() => {
  const labels = props.historico_mediciones.map(m => m.fecha);
  const data = chartTimeMetric.value === 'seguidores'
    ? props.historico_mediciones.map(m => m.seguidores)
    : props.historico_mediciones.map(m => m.vistas);

  const isFollowers = chartTimeMetric.value === 'seguidores';
  const colorHex = isFollowers ? '#06b6d4' : '#10b981';

  return {
    labels,
    datasets: [
      {
        label: isFollowers ? 'Comunidad Total' : 'Visualizaciones Acumuladas',
        data,
        borderColor: colorHex,
        backgroundColor: isFollowers ? 'rgba(6, 182, 212, 0.12)' : 'rgba(16, 185, 129, 0.12)',
        fill: true,
        tension: 0.35,
        pointBackgroundColor: colorHex,
        pointBorderColor: '#ffffff',
        pointHoverRadius: 6,
        borderWidth: 2.5,
      }
    ]
  };
});

const timelineChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      titleColor: '#06b6d4',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: function(context) {
          return `${context.dataset.label}: ${Number(context.raw).toLocaleString('es-AR')}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' } }
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

// 2. Gráfico Donut de Participación por Red Social
const doughnutChartData = computed(() => {
  const labels = props.distribucion_plataformas.map(p => p.nombre);
  const data = props.distribucion_plataformas.map(p => p.interacciones);
  const colors = props.distribucion_plataformas.map(p => p.color);

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

    <!-- 2. HUD CENTRAL DE KPIS MULTICANAL NORMALIZADOS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
      <!-- KPI 1: Comunidad Multired & Crecimiento Neto -->
      <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Comunidad Total</span>
          <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
            <Users class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
            {{ stats.total_seguidores }}
          </p>
          <div class="mt-1 flex items-center gap-1.5 text-xs font-semibold" :class="stats.crecimiento_neto_seguidores >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'">
            <TrendingUp v-if="stats.crecimiento_neto_seguidores >= 0" class="w-3.5 h-3.5" />
            <TrendingDown v-else class="w-3.5 h-3.5" />
            <span>{{ stats.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ Number(stats.crecimiento_neto_seguidores).toLocaleString('es-AR') }} neto</span>
            <span class="text-slate-400 font-normal">vs Punto Cero</span>
          </div>
        </div>
      </div>

      <!-- KPI 2: Score de Impacto Orgánico -->
      <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score de Impacto</span>
          <div class="w-8 h-8 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center">
            <Zap class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
            {{ stats.score_impacto_total }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Ponderado (1/3/5/10 pts)
          </p>
        </div>
      </div>

      <!-- KPI 3: Visualizaciones Acumuladas -->
      <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Alcance & Vistas</span>
          <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
            <Eye class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
            {{ stats.total_vistas }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            {{ stats.engagement_promedio }} engagement rate
          </p>
        </div>
      </div>

      <!-- KPI 4: Clima de Humor Social -->
      <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Humor Social</span>
          <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
            <Flame class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight flex items-center gap-1.5">
            <span>{{ stats.humor_social_promedio }}</span>
            <span class="text-xs text-slate-400 font-normal">/ 5.0</span>
          </p>
          <div class="mt-1 flex items-center gap-1 text-amber-400 text-xs">
            <span>★</span><span>★</span><span>★</span><span>★</span><span class="text-slate-300 dark:text-slate-600">★</span>
            <span class="text-slate-500 dark:text-slate-400 text-[11px] ml-1 font-semibold">Favorable</span>
          </div>
        </div>
      </div>

      <!-- KPI 5: Penetración sobre el Padrón -->
      <div class="p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penetración Padrón</span>
          <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
            <Target class="w-4 h-4" />
          </div>
        </div>
        <div class="mt-2.5">
          <p class="text-2xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
            {{ stats.ratio_penetracion }}
          </p>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            del electorado territorial
          </p>
        </div>
      </div>
    </div>

    <!-- 3. CENTRO DE ANALÍTICA VISUAL (4 GRÁFICOS INTERACTIVOS CHART.JS) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <!-- Gráfico 1 (7 cols): Tendencia Temporal Multired -->
      <div class="lg:col-span-7 p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Activity class="w-4 h-4 text-cyan-500" />
              <span>Evolución Temporal Multired</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Progresión acumulada desde el inicio de campaña</p>
          </div>

          <!-- Selector de Métrica Temporal -->
          <div class="flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 self-start">
            <button
              @click="chartTimeMetric = 'seguidores'"
              class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
              :class="chartTimeMetric === 'seguidores' ? 'bg-cyan-500 text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              Comunidad
            </button>
            <button
              @click="chartTimeMetric = 'vistas'"
              class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
              :class="chartTimeMetric === 'vistas' ? 'bg-emerald-500 text-slate-950 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
            >
              Visualizaciones
            </button>
          </div>
        </div>

        <div class="h-64 sm:h-72 w-full">
          <Line :data="timelineChartData" :options="timelineChartOptions" />
        </div>
      </div>

      <!-- Gráfico 2 (5 cols): Donut de Cuota de Atención por Red Social -->
      <div class="lg:col-span-5 p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between">
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <PieChart class="w-4 h-4 text-violet-500" />
              <span>Cuota de Interacción por Red</span>
            </h2>
            <span class="text-xs font-mono text-slate-400 font-semibold">Share of Social</span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400">Distribución de likes, comentarios y compartidos</p>
        </div>

        <div class="h-56 w-full relative flex items-center justify-center">
          <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
        </div>

        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-center font-mono">
          <div v-for="red in distribucion_plataformas.slice(0, 3)" :key="red.plataforma" class="p-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/40">
            <span class="text-[10px] text-slate-400 block truncate">{{ red.nombre }}</span>
            <span class="text-xs font-bold" :style="{ color: red.color }">{{ red.porcentaje }}%</span>
          </div>
        </div>
      </div>

      <!-- Gráfico 3 (6 cols): Rendimiento por Formato -->
      <div class="lg:col-span-6 p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <BarChart3 class="w-4 h-4 text-cyan-500" />
              <span>Rendimiento por Formato</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Reels vs Fotos vs Videos vs Texto</p>
          </div>
        </div>

        <div class="h-60 w-full">
          <Bar :data="formatBarChartData" :options="formatBarChartOptions" />
        </div>
      </div>

      <!-- Gráfico 4 (6 cols): Ejes Temáticos de Campaña -->
      <div class="lg:col-span-6 p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Target class="w-4 h-4 text-amber-500" />
              <span>Cobertura de Ejes Temáticos</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Interacciones logradas por propuesta de campaña</p>
          </div>
        </div>

        <div v-if="distribucion_ejes.length > 0" class="h-60 w-full">
          <Bar :data="ejesBarChartData" :options="ejesBarChartOptions" />
        </div>
        <div v-else class="h-60 flex items-center justify-center text-xs text-slate-400">
          No hay publicaciones clasificadas por ejes temáticos todavía.
        </div>
      </div>
    </div>

    <!-- 4. MALLA DE CANALES SOCIALES (CON SEMÁFORO Y ACCESO EN 1 CLIC A /perfiles-sociales/{id}/metricas) -->
    <div class="space-y-3.5">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Sparkles class="w-4 h-4 text-cyan-500" />
            <span>Auditoría de Canales Sociales Conectados</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">Haz clic en cualquier canal para abrir su panel analítico detallado</p>
        </div>
        <Link href="/mi-candidato" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
          Configurar Punto Cero &rarr;
        </Link>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
        <Link
          v-for="red in redes_desglose"
          :key="red.id"
          :href="`/perfiles-sociales/${red.id}/metricas`"
          class="p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs hover:border-cyan-500/50 hover:shadow-md transition-all group flex flex-col justify-between block cursor-pointer"
        >
          <div>
            <!-- Header de Canal con Ícono Oficial -->
            <div class="flex items-center justify-between gap-2 mb-3">
              <div class="flex items-center gap-2.5">
                <SocialPlatformIcon :platform="red.plataforma" size="md" />
                <div>
                  <h3 class="font-bold text-xs text-slate-900 dark:text-slate-100 uppercase tracking-wider font-mono">
                    {{ red.plataforma.replace('_', ' ') }}
                  </h3>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 block truncate max-w-[130px]">
                    {{ red.handle_usuario || '@pendiente' }}
                  </span>
                </div>
              </div>

              <!-- Semáforo de Estado -->
              <span
                class="w-2.5 h-2.5 rounded-full"
                :class="red.esta_verificado ? 'bg-cyan-500 shadow-xs shadow-cyan-500/50' : (red.esta_activo ? 'bg-emerald-500' : 'bg-rose-500')"
                :title="red.esta_verificado ? 'Verificada' : (red.esta_activo ? 'Activa' : 'Inactiva')"
              ></span>
            </div>

            <!-- Métricas Clave del Canal -->
            <div class="grid grid-cols-2 gap-2 font-mono py-2 border-y border-slate-100 dark:border-slate-800/80">
              <div>
                <span class="text-[10px] text-slate-400 uppercase block">Comunidad</span>
                <span class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                  {{ formatNumber(red.seguidores) }}
                </span>
                <span class="text-[10px] block" :class="red.crecimiento_neto_seguidores >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                  {{ red.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ formatNumber(red.crecimiento_neto_seguidores) }} neto
                </span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 uppercase block">Engagement</span>
                <span class="text-base font-extrabold text-cyan-600 dark:text-cyan-400">
                  {{ red.tasa_engagement }}%
                </span>
                <span class="text-[10px] text-slate-400 block">
                  {{ red.publicaciones_count }} publicaciones
                </span>
              </div>
            </div>
          </div>

          <!-- Footer con Botón Directo -->
          <div class="mt-3 pt-1 flex items-center justify-between text-xs font-semibold text-cyan-600 dark:text-cyan-400 group-hover:translate-x-0.5 transition-transform">
            <span>Ver Métricas & Cadencia</span>
            <ChevronRight class="w-4 h-4" />
          </div>
        </Link>
      </div>
    </div>

    <!-- 5. INTELIGENCIA DE PAUTA (ROI) & OBSERVATORIO DE PRENSA (2 COLUMNAS) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <!-- Módulo 1: Inteligencia de Pauta vs Orgánico -->
      <div class="p-5 sm:p-6 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 shadow-md space-y-4 flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-mono uppercase tracking-wider text-cyan-400 font-bold flex items-center gap-1.5">
              <DollarSign class="w-4 h-4" />
              Eficiencia Publicitaria (ROI)
            </span>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-mono">
              Inversión Ads
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 font-mono">
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
              <span class="text-[10px] text-slate-400 block uppercase">Inversión Total</span>
              <span class="text-xl font-extrabold text-white">{{ formatCurrency(organico_vs_pauta?.inversion_total ?? 0) }}</span>
              <span class="text-[10px] text-slate-400 block mt-0.5">{{ organico_vs_pauta?.total_posts_pautados ?? 0 }} anuncios activos</span>
            </div>
            <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
              <span class="text-[10px] text-slate-400 block uppercase">Costo / Interacción</span>
              <span class="text-xl font-extrabold text-emerald-400">{{ formatCurrency(organico_vs_pauta?.costo_por_interaccion ?? 0) }}</span>
              <span class="text-[10px] text-slate-400 block mt-0.5">por reacción/comentario</span>
            </div>
          </div>

          <!-- Barra de Distribución Orgánico vs Pautado -->
          <div class="mt-4 space-y-1.5 text-xs">
            <div class="flex justify-between text-[11px] font-mono">
              <span class="text-cyan-400">Orgánico: {{ organico_vs_pauta?.porcentaje_vistas_organicas ?? 100 }}%</span>
              <span class="text-violet-400">Pautado: {{ organico_vs_pauta?.porcentaje_vistas_pagadas ?? 0 }}%</span>
            </div>
            <div class="w-full h-2.5 rounded-full bg-slate-800 overflow-hidden flex">
              <div class="bg-cyan-500 h-full transition-all" :style="{ width: `${organico_vs_pauta?.porcentaje_vistas_organicas ?? 100}%` }"></div>
              <div class="bg-violet-500 h-full transition-all" :style="{ width: `${organico_vs_pauta?.porcentaje_vistas_pagadas ?? 0}%` }"></div>
            </div>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
          <span>CPM Promedio: <strong class="text-white font-mono">{{ formatCurrency(organico_vs_pauta?.cpm_estimado ?? 0) }}</strong></span>
          <Link href="/predictor" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
            <span>Simular Presupuesto</span>
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
  </WarRoomLayout>
</template>
