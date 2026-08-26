<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  ArrowLeft,
  Sparkles,
  ExternalLink,
  TrendingUp,
  TrendingDown,
  Users,
  Film,
  Flame,
  Heart,
  MessageCircle,
  Share2,
  Bookmark,
  DollarSign,
  Activity,
  Calendar,
  Layers,
  Target,
  BarChart3,
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
  XCircle,
  Play,
  Zap,
  Award,
  Clock,
  ShieldCheck,
  X,
  Maximize2,
  LineChart as LineChartIcon,
  Vote,
  MapPin,
  Compass,
  ChevronDown,
  ChevronUp,
  PieChart
} from '@lucide/vue';

// Chart.js & vue-chartjs
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
} from 'chart.js';
import { Line } from 'vue-chartjs';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
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
  }
});

const isChartModalOpen = ref(false);
const showIndustryBenchmarks = ref(false);

const formatNumber = (n) => {
  return Number(n || 0).toLocaleString('es-AR');
};

const formatCurrency = (n) => {
  return '$' + Number(n || 0).toLocaleString('es-AR');
};

const getSocialMeta = (key) => {
  switch (key) {
    case 'instagram':
      return { color: '#E4405F', bgLight: 'bg-[#E4405F]/15', name: 'Instagram', badge: 'bg-[#E4405F]/10 text-[#E4405F]' };
    case 'facebook':
      return { color: '#1877F2', bgLight: 'bg-[#1877F2]/15', name: 'Facebook', badge: 'bg-[#1877F2]/10 text-[#1877F2]' };
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

// Chart.js Data & Options for Time-Series Modal
const chartData = computed(() => {
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

const chartOptions = computed(() => {
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
  const textColor = isDark ? '#94a3b8' : '#64748b';
  const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        position: 'top',
        labels: {
          color: textColor,
          font: { family: 'ui-monospace, monospace', size: 12, weight: 'bold' },
          usePointStyle: true,
          padding: 20,
        }
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
          label: (context) => {
            let label = context.dataset.label || '';
            if (label) label += ': ';
            if (context.parsed.y !== null) {
              label += Number(context.parsed.y).toLocaleString('es-AR');
            }
            return label;
          }
        }
      }
    },
    scales: {
      x: {
        grid: { color: gridColor, drawBorder: false },
        ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } }
      },
      y: {
        type: 'linear',
        display: true,
        position: 'left',
        grid: { color: gridColor, drawBorder: false },
        ticks: {
          color: textColor,
          font: { family: 'ui-monospace, monospace', size: 11 },
          callback: (value) => Number(value).toLocaleString('es-AR')
        }
      },
      y1: {
        type: 'linear',
        display: true,
        position: 'right',
        grid: { drawOnChartArea: false },
        ticks: {
          color: '#10b981',
          font: { family: 'ui-monospace, monospace', size: 11 },
          callback: (value) => (value >= 0 ? '+' : '') + Number(value).toLocaleString('es-AR')
        }
      }
    }
  };
});
</script>

<template>
  <Head :title="`Métricas ${getSocialMeta(perfilSocial.plataforma).name} - ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
      
      <!-- Top Navigation & Return Header -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
          <Link
            :href="candidato.es_propio ? '/mi-candidato' : `/candidatos/${candidato.id}`"
            class="p-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-cyan-500 hover:border-cyan-500/30 transition-all shadow-xs flex items-center justify-center cursor-pointer"
            title="Volver a la ficha del candidato"
          >
            <ArrowLeft class="w-4 h-4" />
          </Link>

          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <BarChart3 class="w-6 h-6 text-cyan-500" />
                <span>Dashboard de Métricas: {{ getSocialMeta(perfilSocial.plataforma).name }}</span>
              </h1>
              <Badge variant="estado" :value="candidato.estado_politico" size="sm" />
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Auditoría en tiempo real, objetivos algorítmicos, pauta vs orgánico y evolución de <strong>{{ candidato.nombre_completo }}</strong>.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2.5">
          <button
            type="button"
            @click="isChartModalOpen = true"
            class="px-4 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono inline-flex items-center gap-2 transition-all shadow-xs hover:scale-102 cursor-pointer"
          >
            <LineChartIcon class="w-4 h-4" />
            <span>Ver Gráfico Evolutivo</span>
          </button>

          <a
            v-if="perfilSocial.url_perfil"
            :href="perfilSocial.url_perfil"
            target="_blank"
            rel="noopener noreferrer"
            class="px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-cyan-500/40 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono inline-flex items-center gap-2 transition-all shadow-xs"
          >
            <span>{{ perfilSocial.handle_usuario }}</span>
            <ExternalLink class="w-3.5 h-3.5 text-cyan-500" />
          </a>
        </div>
      </div>

      <!-- BANNER DE CONTEXTO TERRITORIO-FIRST (EL PADRÓN ES EL UNIVERSO) -->
      <div class="p-5 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-900 to-cyan-950 border border-cyan-500/30 shadow-md text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 flex items-center justify-center shrink-0">
            <Vote class="w-6 h-6" />
          </div>
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-xs font-mono font-bold uppercase tracking-wider text-cyan-400">
                🏛️ Universo Rector de Campaña
              </span>
              <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                {{ territorioContexto.nombre || candidato.territorio_nombre || 'Albardón' }}
              </span>
            </div>
            <h2 class="text-lg sm:text-xl font-black text-slate-100 mt-0.5">
              Padrón Electoral: {{ formatNumber(territorioContexto.padron_electoral || candidato.padron_electoral || 24500) }} Electores
            </h2>
            <p class="text-xs text-slate-300 mt-0.5">
              Todas las métricas evalúan la penetración y movilización real sobre los votantes de este territorio.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 font-mono text-xs self-start md:self-auto flex-wrap">
          <div class="px-3.5 py-2 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-left">
            <span class="text-[10px] text-slate-400 uppercase block font-bold">Meta Regular (30%)</span>
            <span class="text-sm font-extrabold text-amber-400">
              {{ formatNumber(territorioContexto.meta_regular_vistas || (candidato.padron_electoral * 0.3)) }} vistas
            </span>
          </div>
          <div class="px-3.5 py-2 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-left">
            <span class="text-[10px] text-slate-400 uppercase block font-bold">Meta Victoria (40%)</span>
            <span class="text-sm font-extrabold text-emerald-400">
              {{ formatNumber(territorioContexto.meta_ganadora_vistas || (candidato.padron_electoral * 0.4)) }} vistas
            </span>
          </div>
        </div>
      </div>

      <!-- ALERTA DE REDISTRIBUCIÓN DE PAUTA / ÉXITO VIRAL -->
      <div
        v-if="alertaRedistribucionPauta"
        class="p-5 rounded-3xl border text-xs leading-relaxed flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
        :class="alertaRedistribucionPauta.tipo === 'exito_viral' ? 'bg-amber-500/10 border-amber-500/30 text-amber-900 dark:text-amber-200' : 'bg-cyan-500/10 border-cyan-500/30 text-cyan-900 dark:text-cyan-200'"
      >
        <div class="flex items-start gap-3">
          <div class="p-2 rounded-xl bg-amber-500/20 text-amber-500 shrink-0 mt-0.5">
            <Sparkles class="w-5 h-5" />
          </div>
          <div class="space-y-1">
            <div class="font-extrabold text-sm flex items-center gap-2">
              <span>{{ alertaRedistribucionPauta.mensaje }}</span>
            </div>
            <p class="text-slate-600 dark:text-slate-300">
              🎯 <strong>Recomendación Táctica:</strong> {{ alertaRedistribucionPauta.accion_sugerida }}
            </p>
          </div>
        </div>

        <Link
          href="/territorios/impacto-electoral"
          class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs font-mono shrink-0 shadow-xs hover:scale-102 transition-all flex items-center gap-1.5"
        >
          <Target class="w-3.5 h-3.5" />
          <span>Ver Balance Multi-Red</span>
        </Link>
      </div>

      <!-- Ficha de Cabecera del Canal Auditado -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between flex-wrap gap-5">
        <div class="flex items-center gap-4">
          <div class="relative shrink-0">
            <img
              :src="perfilSocial.foto_perfil_url || candidato.avatar_url"
              alt="Foto Canal"
              referrerpolicy="no-referrer"
              class="w-16 h-16 rounded-2xl object-cover border-2 border-cyan-500 shadow-md"
            />
            <div
              class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900"
              :class="perfilSocial.esta_activo ? 'bg-amber-500 text-slate-950' : 'bg-rose-500 text-white'"
            >
              <span class="text-[9px] font-extrabold">●</span>
            </div>
          </div>

          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h2 class="text-lg font-extrabold text-slate-900 dark:text-slate-100">
                {{ perfilSocial.handle_usuario || '@cuenta' }}
              </h2>
              <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
                :class="{
                  'bg-cyan-500/20 text-cyan-500 border border-cyan-500/30': perfilSocial.semaforo_color === 'azul',
                  'bg-amber-500/20 text-amber-500 border border-amber-500/30': perfilSocial.semaforo_color === 'naranja',
                  'bg-rose-500/20 text-rose-500 border border-rose-500/30': perfilSocial.semaforo_color === 'rojo'
                }"
              >
                {{ perfilSocial.esta_verificado ? 'Verificada' : (perfilSocial.esta_activo ? 'Canal Activo' : 'Inactivo') }}
              </span>
              <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-purple-500/15 text-purple-400 border border-purple-500/30 uppercase">
                Tramo: {{ benchmarks.tramo_label || 'Nano (<10k)' }}
              </span>
              <span v-if="perfilSocial.fecha_punto_cero" class="text-xs font-mono text-slate-400">
                (Punto Alfa: {{ perfilSocial.fecha_punto_cero }})
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              Última medición registrada: <strong>{{ perfilSocial.fecha_ultima_medicion || 'Hoy' }}</strong> ({{ perfilSocial.fecha_ultima_medicion_relativa || 'hace instantes' }})
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3 font-mono text-xs">
          <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">Seguidores</span>
            <span class="text-base font-extrabold text-cyan-600 dark:text-cyan-400">
              {{ formatNumber(stats.seguidores_actuales) }}
            </span>
          </div>
          <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
            <span class="text-[10px] text-slate-400 block uppercase font-bold">Posts en Red</span>
            <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">
              {{ formatNumber(stats.posts_actuales) }}
            </span>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 1. SEMÁFORO TERRITORIO-FIRST: MÉTRICAS DETERMINANTES CONTRA EL PADRÓN -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div v-if="semaforoPadron" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="p-1.5 rounded-xl bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
                <Compass class="w-5 h-5" />
              </span>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                Semáforo del Padrón Electoral (KPIs Determinantes de Campaña)
              </h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Rendimiento contrastado contra los {{ formatNumber(semaforoPadron.cobertura?.padron_total || 24500) }} electores habilitados.
            </p>
          </div>
          <span class="text-xs font-mono px-3 py-1.5 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold self-start sm:self-auto border border-cyan-500/20">
            🎯 Meta Ganadora: 40% del Padrón
          </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
          <!-- Card 1: Cobertura del Padrón (Alcance Visual) -->
          <div
            class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-3 flex flex-col justify-between"
            :class="{
              'border-emerald-500/50 shadow-xs shadow-emerald-500/10': semaforoPadron.cobertura?.estado === 'ganadora',
              'border-amber-500/50 shadow-xs shadow-amber-500/10': semaforoPadron.cobertura?.estado === 'regular' || semaforoPadron.cobertura?.estado === 'medio',
              'border-rose-500/50 shadow-xs shadow-rose-500/10': semaforoPadron.cobertura?.estado === 'critico',
            }"
          >
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
                  👀 Cobertura Padrón
                </span>
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="{
                    'bg-emerald-500/15 text-emerald-500': semaforoPadron.cobertura?.estado === 'ganadora',
                    'bg-amber-500/15 text-amber-500': semaforoPadron.cobertura?.estado === 'regular' || semaforoPadron.cobertura?.estado === 'medio',
                    'bg-rose-500/15 text-rose-500': semaforoPadron.cobertura?.estado === 'critico',
                  }"
                >
                  {{ semaforoPadron.cobertura?.pct_actual }}% Padrón
                </span>
              </div>
              <span class="text-2xl font-black block" :class="{
                'text-emerald-500': semaforoPadron.cobertura?.estado === 'ganadora',
                'text-amber-500': semaforoPadron.cobertura?.estado === 'regular' || semaforoPadron.cobertura?.estado === 'medio',
                'text-rose-500': semaforoPadron.cobertura?.estado === 'critico',
              }">
                {{ formatNumber(semaforoPadron.cobertura?.actual_vistas) }}
              </span>
              <span class="text-[11px] text-slate-400 block">visualizaciones este ciclo</span>
            </div>

            <div class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800/80">
              <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="semaforoPadron.cobertura?.pct_actual >= 40 ? 'bg-emerald-500' : (semaforoPadron.cobertura?.pct_actual >= 30 ? 'bg-amber-500' : 'bg-rose-500')"
                  :style="{ width: `${Math.min(100, (semaforoPadron.cobertura?.pct_actual / 40) * 100)}%` }"
                ></div>
              </div>
              <div class="flex justify-between text-[10px] text-slate-400">
                <span>Meta Regular: 30%</span>
                <span>Meta Victoria: 40%</span>
              </div>
            </div>

            <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
              {{ semaforoPadron.cobertura?.diagnostico }}
            </p>
          </div>

          <!-- Card 2: Movilización del Padrón (Interacciones Cívicas) -->
          <div
            class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-3 flex flex-col justify-between"
            :class="{
              'border-emerald-500/50 shadow-xs shadow-emerald-500/10': semaforoPadron.movilizacion?.estado === 'ganadora',
              'border-amber-500/50 shadow-xs shadow-amber-500/10': semaforoPadron.movilizacion?.estado === 'regular' || semaforoPadron.movilizacion?.estado === 'medio',
              'border-rose-500/50 shadow-xs shadow-rose-500/10': semaforoPadron.movilizacion?.estado === 'critico',
            }"
          >
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
                  💬 Movilización
                </span>
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="{
                    'bg-emerald-500/15 text-emerald-500': semaforoPadron.movilizacion?.estado === 'ganadora',
                    'bg-amber-500/15 text-amber-500': semaforoPadron.movilizacion?.estado === 'regular' || semaforoPadron.movilizacion?.estado === 'medio',
                    'bg-rose-500/15 text-rose-500': semaforoPadron.movilizacion?.estado === 'critico',
                  }"
                >
                  {{ semaforoPadron.movilizacion?.pct_actual }}% Padrón
                </span>
              </div>
              <span class="text-2xl font-black block" :class="{
                'text-emerald-500': semaforoPadron.movilizacion?.estado === 'ganadora',
                'text-amber-500': semaforoPadron.movilizacion?.estado === 'regular' || semaforoPadron.movilizacion?.estado === 'medio',
                'text-rose-500': semaforoPadron.movilizacion?.estado === 'critico',
              }">
                {{ formatNumber(semaforoPadron.movilizacion?.actual_interacciones) }}
              </span>
              <span class="text-[11px] text-slate-400 block">reacciones + coment + compartidos</span>
            </div>

            <div class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800/80">
              <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :class="semaforoPadron.movilizacion?.pct_actual >= 15 ? 'bg-emerald-500' : (semaforoPadron.movilizacion?.pct_actual >= 8 ? 'bg-amber-500' : 'bg-rose-500')"
                  :style="{ width: `${Math.min(100, (semaforoPadron.movilizacion?.pct_actual / 15) * 100)}%` }"
                ></div>
              </div>
              <div class="flex justify-between text-[10px] text-slate-400">
                <span>Meta Regular: 8%</span>
                <span>Meta Ganadora: 15%</span>
              </div>
            </div>

            <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
              {{ semaforoPadron.movilizacion?.diagnostico }}
            </p>
          </div>

          <!-- Card 3: Calculadora de Ritmo de Publicación -->
          <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between">
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
                  📅 Ritmo Publicitario
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-cyan-500/15 text-cyan-500">
                  {{ semaforoPadron.ritmo?.posts_semana_actual }} posts/sem
                </span>
              </div>
              <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-cyan-500">
                  {{ semaforoPadron.ritmo?.posts_semana_necesarios }}
                </span>
                <span class="text-xs text-slate-400">posts/semana necesarios</span>
              </div>
              <span class="text-[11px] text-slate-400 block">
                Promedio actual: {{ formatNumber(semaforoPadron.ritmo?.promedio_vistas_post) }} vistas/post
              </span>
            </div>

            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-[11px] space-y-1">
              <div class="flex justify-between">
                <span class="text-slate-400">Meta Mensual:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ semaforoPadron.ritmo?.posts_mes_necesarios }} posts/mes</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">¿Viable 100% Orgánico?</span>
                <span class="font-bold" :class="semaforoPadron.ritmo?.es_alcanzable_organico ? 'text-emerald-500' : 'text-amber-500'">
                  {{ semaforoPadron.ritmo?.es_alcanzable_organico ? 'Sí 🏆' : 'Requiere Pauta' }}
                </span>
              </div>
            </div>

            <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
              {{ semaforoPadron.ritmo?.consejo }}
            </p>
          </div>

          <!-- Card 4: Amplificación Viral & Costo por Elector -->
          <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between">
            <div class="space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
                  🚀 Viralidad & CEA
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-500/15 text-purple-400">
                  {{ semaforoPadron.amplificacion?.total_compartidos }} shares
                </span>
              </div>
              <div>
                <span class="text-2xl font-black text-purple-400 block">
                  +{{ formatNumber(semaforoPadron.amplificacion?.amplificacion_estimada) }}
                </span>
                <span class="text-[11px] text-slate-400 block">alcance viral expandido</span>
              </div>
            </div>

            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-[11px] space-y-1">
              <div class="flex justify-between">
                <span class="text-slate-400">Costo / Elector (CEA):</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">
                  {{ semaforoPadron.amplificacion?.costo_por_elector_ars > 0 ? '$' + semaforoPadron.amplificacion?.costo_por_elector_ars : 'Orgánico' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-400">Electores Únicos Estim.:</span>
                <span class="font-bold text-cyan-500">{{ formatNumber(semaforoPadron.amplificacion?.electores_alcanzados_estimados) }}</span>
              </div>
            </div>

            <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
              {{ semaforoPadron.amplificacion?.diagnostico }}
            </p>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 2. RADIOGRAFÍA DEMOGRÁFICA DE AUDIENCIA & CRUCE CON EL PADRÓN ELECTORAL -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div v-if="demografiaAudiencia" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- A. Cruce por Franjas Etarias vs Padrón (2 Cols) -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-2">
            <div>
              <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
                  <Users class="w-4 h-4" />
                </span>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
                  Audiencia Digital vs Padrón Electoral (Análisis de Brechas)
                </h3>
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Compara la composición etaria de tu comunidad en {{ getSocialMeta(perfilSocial.plataforma).name }} frente a los electores del territorio.
              </p>
            </div>
            <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
              Fuente: {{ demografiaAudiencia.fuente_datos === 'estimacion_territorial' ? 'Estimación Territorial' : 'Meta Graph API' }}
            </span>
          </div>

          <!-- Tabla / Barras de Franjas Etarias y Brechas -->
          <div class="space-y-3 font-mono text-xs">
            <div
              v-for="item in cruceDemografico"
              :key="item.rango"
              class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5"
            >
              <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2">
                  <span class="font-black text-slate-900 dark:text-slate-100 text-sm">{{ item.rango }} años</span>
                  <span class="text-[10px] text-slate-400">({{ item.categoria }})</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-slate-500">Padrón: <strong>{{ item.pct_padron }}%</strong></span>
                  <span class="text-cyan-500 font-bold">Tu Red: <strong>{{ item.pct_audiencia }}%</strong></span>
                  <span
                    class="px-2 py-0.5 rounded font-bold text-[10px]"
                    :class="item.brecha >= 0 ? 'bg-cyan-500/15 text-cyan-400' : 'bg-rose-500/15 text-rose-400'"
                  >
                    {{ item.brecha >= 0 ? '+' : '' }}{{ item.brecha }}%
                  </span>
                </div>
              </div>

              <!-- 1. Bloque de Barras: Demografía (Padrón vs Tu Red) -->
              <div class="space-y-1.5 pt-1">
                <div class="flex items-center justify-between text-[10px] text-slate-500">
                  <span class="flex items-center gap-1.5 font-bold">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span>Padrón Electoral: <strong>{{ item.pct_padron }}%</strong></span>
                  </span>
                  <span class="flex items-center gap-1.5 font-bold text-cyan-500">
                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                    <span>Tu Audiencia: <strong>{{ item.pct_audiencia }}%</strong></span>
                  </span>
                </div>
                <!-- Doble Barra Comparativa Demografía -->
                <div class="space-y-1">
                  <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden" title="Distribución en el Padrón">
                    <div class="h-full bg-slate-400 rounded-full" :style="{ width: `${item.pct_padron * 2.2}%` }"></div>
                  </div>
                  <div class="w-full h-2.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden" title="Distribución en tu Audiencia Digital">
                    <div class="h-full bg-cyan-500 rounded-full transition-all" :style="{ width: `${item.pct_audiencia * 2.2}%` }"></div>
                  </div>
                </div>
              </div>

              <!-- 2. Bloque de Barras: Volumen de Reacciones (Últimos 30 días vs Máximo Histórico Logrado) -->
              <div class="p-3 rounded-2xl bg-slate-100/70 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-sans">
                  <div class="flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200">
                    <Heart class="w-3.5 h-3.5 text-rose-500 fill-rose-500/20" />
                    <span>Impacto de Reacciones por Post:</span>
                  </div>
                  <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-[10px] font-bold text-slate-700 dark:text-slate-300">
                    {{ item.resonancia_nivel || 'Resonancia Moderada ⚡' }}
                  </span>
                </div>

                <!-- Barra 1: Actual 30 días -->
                <div class="space-y-1">
                  <div class="flex items-center justify-between text-[10px]">
                    <span class="flex items-center gap-1 text-rose-500 font-bold">
                      <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                      <span>Promedio Actual (Últimos 30d):</span>
                    </span>
                    <span class="font-bold text-rose-500 font-mono">
                      ~{{ item.reacciones_actuales_30d || 0 }} reacc. <span class="text-slate-400 font-normal font-sans">(~{{ Number(item.vistas_actuales_30d || 0).toLocaleString('es-AR') }} vistas)</span>
                    </span>
                  </div>
                  <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                    <div
                      class="h-full bg-rose-500 rounded-full transition-all"
                      :style="{ width: `${Math.min((item.reacciones_actuales_30d / Math.max(item.reacciones_max_historico, 1)) * 100, 100)}%` }"
                    ></div>
                  </div>
                </div>

                <!-- Barra 2: Récord Histórico -->
                <div class="space-y-1">
                  <div class="flex items-center justify-between text-[10px]">
                    <span class="flex items-center gap-1 text-amber-500 font-bold">
                      <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                      <span>🏆 Récord Histórico Mensual:</span>
                    </span>
                    <span class="font-bold text-amber-500 font-mono">
                      ~{{ item.reacciones_max_historico || 0 }} reacc./post <span class="text-slate-400 font-normal font-sans">({{ item.mes_record_nombre }})</span>
                    </span>
                  </div>
                  <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full w-full"></div>
                  </div>
                </div>

                <div class="flex items-center justify-between text-[10px] text-slate-400 pt-0.5">
                  <span>Rendimiento vs. Mes Récord:</span>
                  <strong class="font-mono" :class="item.pct_vs_record >= 80 ? 'text-emerald-500' : 'text-amber-500'">
                    {{ item.pct_vs_record }}% del pico histórico
                  </strong>
                </div>
              </div>

              <div class="text-[11px] font-sans text-slate-500 dark:text-slate-400 pt-0.5">
                <span>🎯 {{ item.accion_sugerida }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- B. Género, Top Ciudades y Horarios Pico (1 Col) -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
          <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <PieChart class="w-4 h-4 text-emerald-500" />
              <span>Demografía & Anclaje Geográfico</span>
            </h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Origen territorial y momentos de mayor resonancia.
            </p>
          </div>

          <!-- Género -->
          <div v-if="demografiaAudiencia.genero" class="space-y-2">
            <span class="text-[11px] font-mono uppercase font-bold text-slate-400 block">Distribución de Género</span>
            <div class="w-full h-3 rounded-full overflow-hidden flex bg-slate-200 dark:bg-slate-800 shadow-inner">
              <div
                class="h-full bg-[#E4405F] transition-all"
                :style="{ width: `${demografiaAudiencia.genero.femenino_pct}%` }"
                :title="`Mujeres: ${demografiaAudiencia.genero.femenino_pct}%`"
              ></div>
              <div
                class="h-full bg-[#1877F2] transition-all"
                :style="{ width: `${demografiaAudiencia.genero.masculino_pct}%` }"
                :title="`Varones: ${demografiaAudiencia.genero.masculino_pct}%`"
              ></div>
            </div>
            <div class="flex justify-between text-[11px] font-mono">
              <span class="text-[#E4405F] font-bold">👩 {{ demografiaAudiencia.genero.femenino_pct }}% Mujeres</span>
              <span class="text-[#1877F2] font-bold">👨 {{ demografiaAudiencia.genero.masculino_pct }}% Varones</span>
            </div>
          </div>

          <!-- Top Ciudades -->
          <div v-if="demografiaAudiencia.ciudades_principales" class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <span class="text-[11px] font-mono uppercase font-bold text-slate-400 block">Top Ciudades de la Audiencia</span>
            <div class="space-y-1.5 font-mono text-xs">
              <div
                v-for="c in demografiaAudiencia.ciudades_principales"
                :key="c.ciudad"
                class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200/70 dark:border-slate-800 flex items-center justify-between"
              >
                <span class="flex items-center gap-1.5 text-slate-800 dark:text-slate-200 font-medium">
                  <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                  {{ c.ciudad }}
                </span>
                <span class="font-bold text-cyan-500">{{ c.pct }}%</span>
              </div>
            </div>
          </div>

          <!-- Horarios & Días Pico -->
          <div v-if="demografiaAudiencia.horarios_actividad" class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-xs space-y-1 text-cyan-800 dark:text-cyan-200">
            <span class="font-bold font-mono text-[11px] uppercase block">⏰ Momentos de Mayor Actividad:</span>
            <p class="text-[11px] leading-relaxed">
              <strong>Días:</strong> {{ demografiaAudiencia.horarios_actividad.dias_pico.join(', ') }}<br />
              <strong>Horarios Prime:</strong> {{ demografiaAudiencia.horarios_actividad.horas_pico.join(' & ') }}
            </p>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 3. PANEL COLAPSABLE: BENCHMARKS DE INDUSTRIA (REFERENCIA SECUNDARIA) -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between cursor-pointer select-none" @click="showIndustryBenchmarks = !showIndustryBenchmarks">
          <div class="flex items-center gap-3">
            <span class="p-2 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20">
              <Target class="w-5 h-5" />
            </span>
            <div>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span>Benchmarks Técnicos de Industria (Tramo: {{ benchmarks.tramo_label || 'Nano' }})</span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
                  Referencia Secundaria
                </span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Estándares de algoritmos sociales ajustados al tamaño de audiencia de tu canal.
              </p>
            </div>
          </div>

          <button
            type="button"
            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-cyan-500 transition-colors"
          >
            <component :is="showIndustryBenchmarks ? ChevronUp : ChevronDown" class="w-5 h-5" />
          </button>
        </div>

        <div v-show="showIndustryBenchmarks" class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="obj in semaforoObjetivos"
            :key="obj.id"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-3"
            :class="{
              'border-emerald-500/40 shadow-xs shadow-emerald-500/5': obj.estado === 'verde',
              'border-amber-500/40 shadow-xs shadow-amber-500/5': obj.estado === 'amarillo',
              'border-rose-500/40 shadow-xs shadow-rose-500/5': obj.estado === 'rojo',
            }"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                {{ obj.titulo }}
              </span>
              <span
                class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase flex items-center gap-1"
                :class="{
                  'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': obj.estado === 'verde',
                  'bg-amber-500/15 text-amber-600 dark:text-amber-400': obj.estado === 'amarillo',
                  'bg-rose-500/15 text-rose-600 dark:text-rose-400': obj.estado === 'rojo',
                }"
              >
                <CheckCircle2 v-if="obj.estado === 'verde'" class="w-3 h-3" />
                <AlertTriangle v-else-if="obj.estado === 'amarillo'" class="w-3 h-3" />
                <XCircle v-else class="w-3 h-3" />
                <span>{{ obj.estado === 'verde' ? 'En Rango' : (obj.estado === 'amarillo' ? 'Atención' : 'Crítico') }}</span>
              </span>
            </div>

            <div>
              <span class="text-lg font-black font-mono block" :class="{
                'text-emerald-500': obj.estado === 'verde',
                'text-amber-500': obj.estado === 'amarillo',
                'text-rose-500': obj.estado === 'rojo',
              }">
                {{ obj.actual_formato }}
              </span>
              <span class="text-[11px] font-mono text-slate-400 block mt-0.5">
                Rango ideal: <strong>{{ obj.rango_ideal }}</strong>
              </span>
            </div>

            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight pt-1 border-t border-slate-200/60 dark:border-slate-800/60">
              💡 {{ obj.consejo }}
            </p>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 4. DESGLOSE ESTRATÉGICO: ORGÁNICO VS PAUTA PAGADA -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Zap class="w-5 h-5 text-cyan-500" />
              <span>Desglose Estratégico: Tracción Orgánica vs Pauta Publicitaria</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Evalúa la dependencia de presupuesto y el retorno de interacciones de la pauta.
            </p>
          </div>
          <div class="flex items-center gap-3 text-xs font-mono">
            <span class="flex items-center gap-1.5 text-cyan-600 dark:text-cyan-400 font-bold">
              <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
              Orgánico: {{ organicoVsPauta.pct_interacciones_organicas }}%
            </span>
            <span class="flex items-center gap-1.5 text-violet-500 font-bold">
              <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
              Pauta: {{ organicoVsPauta.pct_interacciones_pautadas }}%
            </span>
          </div>
        </div>

        <!-- Barra Visual Comparativa de Interacciones -->
        <div class="space-y-1.5">
          <div class="w-full h-3.5 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden flex shadow-inner">
            <div
              class="h-full bg-cyan-500 transition-all duration-500"
              :style="{ width: `${organicoVsPauta.pct_interacciones_organicas}%` }"
              :title="`Orgánico: ${organicoVsPauta.pct_interacciones_organicas}%`"
            ></div>
            <div
              class="h-full bg-violet-500 transition-all duration-500"
              :style="{ width: `${organicoVsPauta.pct_interacciones_pautadas}%` }"
              :title="`Pauta: ${organicoVsPauta.pct_interacciones_pautadas}%`"
            ></div>
          </div>
          <div class="flex justify-between text-[11px] font-mono text-slate-400">
            <span>{{ formatNumber(organicoVsPauta.interacciones_organicas) }} interacciones naturales</span>
            <span>{{ formatNumber(organicoVsPauta.interacciones_pautadas) }} interacciones con pauta</span>
          </div>
        </div>

        <!-- 2 Columnas Side by Side -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Columna Orgánica -->
          <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-cyan-500/15 text-cyan-500 flex items-center justify-center font-bold text-xs">
                  🌱
                </div>
                <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                  Rendimiento Orgánico Puro
                </h4>
              </div>
              <span class="text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400">
                {{ organicoVsPauta.total_posts_organicos }} publicaciones
              </span>
            </div>

            <div class="grid grid-cols-2 gap-3 font-mono text-xs">
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
                <span class="text-[10px] text-slate-400 uppercase block font-bold">Interacciones</span>
                <span class="text-base font-extrabold text-cyan-500 mt-0.5 block">
                  {{ formatNumber(organicoVsPauta.interacciones_organicas) }}
                </span>
                <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
                  Promedio: {{ organicoVsPauta.promedio_int_organico }} / post
                </span>
              </div>
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
                <span class="text-[10px] text-slate-400 uppercase block font-bold">Vistas Naturales</span>
                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block">
                  {{ formatNumber(organicoVsPauta.vistas_organicas) }}
                </span>
                <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
                  Alcance espontáneo
                </span>
              </div>
            </div>
          </div>

          <!-- Columna Pauta Pagada -->
          <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-violet-500/15 text-violet-500 flex items-center justify-center font-bold text-xs">
                  📢
                </div>
                <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                  Pauta & Ads Impulsados
                </h4>
              </div>
              <span class="text-xs font-mono font-bold text-violet-500">
                {{ organicoVsPauta.total_posts_pautados }} publicaciones con pauta
              </span>
            </div>

            <div class="grid grid-cols-2 gap-3 font-mono text-xs">
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
                <span class="text-[10px] text-slate-400 uppercase block font-bold">Inversión Canal</span>
                <span class="text-base font-extrabold text-violet-500 mt-0.5 block">
                  {{ formatCurrency(organicoVsPauta.inversion_total) }}
                </span>
                <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
                  Costo/Int: ${{ organicoVsPauta.costo_por_interaccion }}
                </span>
              </div>
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800/70">
                <span class="text-[10px] text-slate-400 uppercase block font-bold">Interacciones Ads</span>
                <span class="text-base font-extrabold text-slate-800 dark:text-slate-200 mt-0.5 block">
                  {{ formatNumber(organicoVsPauta.interacciones_pautadas) }}
                </span>
                <span class="text-[10px] text-slate-400 font-sans block mt-0.5">
                  ROI: {{ organicoVsPauta.roi_interacciones_por_peso }} int / $
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 5. RENDIMIENTO POR FORMATO DE CONTENIDO & BENCHMARK DE REELS -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Rendimiento por Formato (2 Cols) -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
          <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Layers class="w-5 h-5 text-cyan-500" />
                <span>Rendimiento por Formato de Contenido</span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Compara la efectividad de Reels, Fotos, Carruseles y Videos.
              </p>
            </div>
          </div>

          <div v-if="rendimientoPorFormato.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
              v-for="(f, idx) in rendimientoPorFormato"
              :key="f.formato"
              class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2 relative overflow-hidden"
            >
              <div v-if="idx === 0 && f.cantidad > 0" class="absolute top-2 right-2 px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-500 text-[10px] font-mono font-black flex items-center gap-1 border border-amber-500/30">
                <Award class="w-3 h-3" />
                <span>Top Formato</span>
              </div>

              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
                  <component :is="getFormatoIcon(f.formato)" class="w-4 h-4" />
                </div>
                <div>
                  <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100 uppercase font-mono">
                    {{ f.formato }}
                  </h4>
                  <span class="text-[11px] font-mono text-slate-400">
                    {{ f.cantidad }} publicaciones
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200 dark:border-slate-800/80 font-mono text-xs">
                <div>
                  <span class="text-[10px] text-slate-400 block uppercase">Prom. Interacciones</span>
                  <span class="font-extrabold text-cyan-600 dark:text-cyan-400">
                    🔥 {{ formatNumber(f.promedio_interacciones) }}
                  </span>
                </div>
                <div v-if="f.promedio_vistas > 0">
                  <span class="text-[10px] text-slate-400 block uppercase">Prom. Vistas</span>
                  <span class="font-extrabold text-slate-800 dark:text-slate-200">
                    👀 {{ formatNumber(f.promedio_vistas) }}
                  </span>
                </div>
                <div v-else>
                  <span class="text-[10px] text-slate-400 block uppercase">Likes Totales</span>
                  <span class="font-extrabold text-slate-800 dark:text-slate-200">
                    ❤️ {{ formatNumber(f.total_likes) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="p-6 text-center text-xs font-mono text-slate-400">
            No hay publicaciones clasificadas por formato aún.
          </div>
        </div>

        <!-- KPI Promedio de Vistas en Reels/Videos (1 Col) -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 flex flex-col justify-between">
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Benchmark en Reels</span>
              <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                <Play class="w-4 h-4 fill-current" />
              </div>
            </div>

            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
              Vistas Promedio por Reel / Video
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              En {{ getSocialMeta(perfilSocial.plataforma).name }}, cada video debe alcanzar al menos el <strong>{{ promedioVistasInfo.ratio_esperado_pct }}%</strong> de tus seguidores actuales.
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-baseline justify-between font-mono">
              <span class="text-2xl font-black" :class="promedioVistasInfo.cumple_benchmark ? 'text-emerald-500' : 'text-amber-500'">
                {{ formatNumber(promedioVistasInfo.promedio_vistas_real) }}
              </span>
              <span class="text-xs font-bold" :class="promedioVistasInfo.cumple_benchmark ? 'text-emerald-500' : 'text-amber-500'">
                {{ promedioVistasInfo.ratio_cumplimiento }}% de la meta
              </span>
            </div>

            <!-- Barra de Progreso de Vistas -->
            <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
              <div
                class="h-full transition-all duration-500"
                :class="promedioVistasInfo.cumple_benchmark ? 'bg-emerald-500' : 'bg-amber-500'"
                :style="{ width: `${Math.min(100, promedioVistasInfo.ratio_cumplimiento)}%` }"
              ></div>
            </div>

            <div class="flex justify-between text-[11px] font-mono text-slate-400">
              <span>Benchmark: ≥ {{ formatNumber(promedioVistasInfo.vistas_esperadas_benchmark) }} vistas</span>
              <span>{{ promedioVistasInfo.total_reels }} videos</span>
            </div>
          </div>

          <div class="text-[11px] text-slate-400 font-mono">
            <span>{{ promedioVistasInfo.cumple_benchmark ? '✅ Excelente tracción algorítmica.' : '⚠️ Recomendación: optimizar hook en los primeros 3 segundos.' }}</span>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 6. CONSISTENCIA MENSUAL & CADENCIA HISTÓRICA (Últimos 6 Meses) -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Calendar class="w-5 h-5 text-cyan-500" />
              <span>Consistencia Mensual de Publicación (Últimos 6 Meses)</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Auditoría de meses activos para evitar caídas en el algoritmo.
            </p>
          </div>
          <span class="text-xs font-mono text-slate-400">
            Meta Mensual: <strong>≥ {{ benchmarks.posts_semana_ideal * 4 }} posts</strong>
          </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
          <div
            v-for="mes in consistenciaMensual"
            :key="mes.mes_key"
            class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-2.5"
            :class="{
              'border-emerald-500/40 shadow-xs': mes.estado === 'excelente',
              'border-amber-500/40': mes.estado === 'adecuado',
              'border-slate-200 dark:border-slate-800': mes.estado === 'bajo',
            }"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100 font-mono">
                {{ mes.mes_nombre }}
              </span>
              <span
                class="w-2 h-2 rounded-full"
                :class="{
                  'bg-emerald-500': mes.estado === 'excelente',
                  'bg-amber-500': mes.estado === 'adecuado',
                  'bg-rose-500': mes.estado === 'bajo' && mes.posts_count === 0,
                  'bg-slate-400': mes.estado === 'bajo' && mes.posts_count > 0,
                }"
              ></span>
            </div>

            <div>
              <span class="text-lg font-black font-mono block text-cyan-600 dark:text-cyan-400">
                {{ mes.posts_count }} posts
              </span>
              <span class="text-[10px] font-mono text-slate-400 block mt-0.5">
                {{ mes.pct_cumplimiento }}% de la meta
              </span>
            </div>

            <!-- Mini Barra de Cumplimiento -->
            <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
              <div
                class="h-full transition-all"
                :class="{
                  'bg-emerald-500': mes.estado === 'excelente',
                  'bg-amber-500': mes.estado === 'adecuado',
                  'bg-slate-400': mes.estado === 'bajo',
                }"
                :style="{ width: `${mes.pct_cumplimiento}%` }"
              ></div>
            </div>

            <div class="text-[10px] font-mono text-slate-400 pt-1 border-t border-slate-200/60 dark:border-slate-800/60 flex justify-between">
              <span>🔥 {{ formatNumber(mes.total_interacciones) }}</span>
              <span v-if="mes.total_pauta > 0" class="text-violet-400">📢 ${{ formatNumber(mes.total_pauta) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 7. TIME-SERIES HISTÓRICO & BOTÓN MODAL GRÁFICO -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Activity class="w-5 h-5 text-cyan-500" />
              <span>Evolución Temporal de Auditorías (Time-Series)</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Registro histórico diario de seguidores y deltas netos.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <span class="text-xs font-mono text-slate-400">
              {{ historicoMediciones.length }} mediciones registradas
            </span>
            <button
              type="button"
              @click="isChartModalOpen = true"
              class="px-3.5 py-2 rounded-xl bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-600 dark:text-cyan-400 text-xs font-mono font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <Maximize2 class="w-3.5 h-3.5" />
              <span>Expandir Gráfico</span>
            </button>
          </div>
        </div>

        <div v-if="historicoMediciones.length > 0" class="overflow-x-auto">
          <table class="w-full text-xs font-mono text-left">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400">
                <th class="py-2.5 px-3">Fecha de Medición</th>
                <th class="py-2.5 px-3 text-right">Seguidores</th>
                <th class="py-2.5 px-3 text-right">Crecimiento Neto</th>
                <th class="py-2.5 px-3 text-right">Seguidos</th>
                <th class="py-2.5 px-3 text-right">Posts Totales</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              <tr
                v-for="med in historicoMediciones.slice().reverse()"
                :key="med.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
              >
                <td class="py-3 px-3 font-semibold text-slate-700 dark:text-slate-300">
                  📅 {{ med.fecha }}
                </td>
                <td class="py-3 px-3 text-right font-extrabold text-cyan-600 dark:text-cyan-400">
                  {{ formatNumber(med.seguidores) }}
                </td>
                <td class="py-3 px-3 text-right font-bold" :class="med.crecimiento_neto_seguidores >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                  {{ med.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ formatNumber(med.crecimiento_neto_seguidores) }}
                </td>
                <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-400">
                  {{ formatNumber(med.seguidos) }}
                </td>
                <td class="py-3 px-3 text-right text-slate-800 dark:text-slate-200">
                  {{ formatNumber(med.publicaciones) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="p-8 text-center text-slate-400 text-xs font-mono">
          Aún no hay registros en la serie temporal. Presiona "Auditar Ahora (1 Clic)" en el perfil para registrar mediciones.
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 8. DISTRIBUCIÓN POR EJE TEMÁTICO DE CAMPAÑA -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div v-if="distribucionEjes.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Target class="w-5 h-5 text-cyan-500" />
          <span>Interacciones por Eje Temático de Campaña</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="eje in distribucionEjes"
            :key="eje.id"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate">
                🎯 {{ eje.nombre }}
              </span>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-cyan-500/15 text-cyan-600 dark:text-cyan-400">
                {{ eje.total_posts }} posts
              </span>
            </div>
            <div class="flex items-center justify-between font-mono text-xs pt-1">
              <span class="text-slate-400">Interacciones:</span>
              <span class="font-extrabold text-cyan-500">🔥 {{ formatNumber(eje.total_interacciones) }}</span>
            </div>
            <div class="flex items-center justify-between font-mono text-[11px] text-slate-400">
              <span>❤️ {{ formatNumber(eje.total_likes) }} likes</span>
              <span>💬 {{ formatNumber(eje.total_comentarios) }} coment</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <!-- 9. TOP PUBLICACIONES CON IMPACTO EN EL PADRÓN -->
      <!-- ══════════════════════════════════════════════════════════════════════════ -->
      <div v-if="topPublicaciones.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Flame class="w-5 h-5 text-cyan-500 fill-current" />
            <span>Top Publicaciones con Mayor Impacto Territorial en este Canal</span>
          </h3>
          <Link
            :href="`/feed?filtro=propio&plataforma=${perfilSocial.plataforma}`"
            class="text-xs font-mono font-bold text-cyan-500 hover:text-cyan-400 flex items-center gap-1 cursor-pointer"
          >
            <span>Ver todas en Muro Social</span>
            <ExternalLink class="w-3 h-3" />
          </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="(top, i) in topPublicaciones"
            :key="top.id"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between"
            :class="{ 'border-amber-500/50 shadow-md shadow-amber-500/10': top.es_viral_territorial }"
          >
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs font-mono">
                <span class="px-2 py-0.5 rounded-md bg-cyan-500 text-slate-950 font-black text-[10px]">
                  #{{ i + 1 }} TOP
                </span>
                <span v-if="top.cobertura_padron_pct > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="top.es_viral_territorial ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-cyan-500/15 text-cyan-400'">
                  {{ top.cobertura_padron_pct }}% Padrón
                </span>
                <span class="text-slate-400 text-[11px]">{{ top.fecha_relativa || top.fecha_publicacion }}</span>
              </div>
              <p class="text-xs text-slate-800 dark:text-slate-200 font-medium line-clamp-3">
                {{ top.contenido_resumen }}
              </p>
            </div>

            <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between font-mono text-xs">
              <span class="font-extrabold text-cyan-600 dark:text-cyan-400">
                🔥 {{ formatNumber(top.total_interacciones) }} int.
              </span>
              <span class="text-slate-500 text-[11px]">
                👀 {{ formatNumber(top.total_vistas) }} vistas
              </span>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- MODAL GRÁFICO DE EVOLUCIÓN TEMPORAL (CHART.JS) -->
    <div
      v-if="isChartModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm"
      @click.self="isChartModalOpen = false"
    >
      <div class="w-full max-w-5xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 max-h-[92vh] overflow-y-auto">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/15 text-cyan-500 flex items-center justify-center">
              <LineChartIcon class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">
                Gráfico Evolutivo: {{ getSocialMeta(perfilSocial.plataforma).name }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Curva temporal de seguidores y crecimiento neto acumulado de {{ perfilSocial.handle_usuario }}
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="isChartModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Canvas del Gráfico -->
        <div class="w-full h-80 sm:h-96 relative bg-slate-50 dark:bg-slate-950 rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
          <Line
            v-if="historicoMediciones.length > 0"
            :data="chartData"
            :options="chartOptions"
          />
          <div v-else class="h-full flex items-center justify-center text-slate-400 font-mono text-xs">
            No hay suficientes mediciones temporales registradas para graficar.
          </div>
        </div>

        <!-- Resumen Rápido debajo del Gráfico -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
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

        <!-- Footer Modal -->
        <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="isChartModalOpen = false"
            class="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-800 text-white font-bold text-xs font-mono transition-all cursor-pointer hover:bg-slate-800"
          >
            Cerrar Gráfico
          </button>
        </div>
      </div>
    </div>
  </WarRoomLayout>
</template>

