<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  ArrowLeft,
  Target,
  Users,
  TrendingUp,
  Flame,
  DollarSign,
  Sparkles,
  MapPin,
  Layers,
  Activity,
  Sliders,
  CheckCircle2,
  AlertTriangle,
  Send,
  ExternalLink,
  ChevronRight,
  Calculator,
  Compass,
  Clock,
  PieChart as PieIcon,
  Zap,
  Calendar,
  Award,
  BarChart2
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    default: null,
  },
  territorioActivo: {
    type: Object,
    required: true,
  },
  departamentos: {
    type: Array,
    default: () => [],
  },
  piramide: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  pasteles: {
    type: Object,
    default: () => ({}),
  },
  inteligenciaTiempo: {
    type: Object,
    default: () => ({}),
  },
  redesImpacto: {
    type: Array,
    default: () => [],
  },
  oportunidadesPauta: {
    type: Array,
    default: () => [],
  },
});

const formatNumber = (n) => Number(n || 0).toLocaleString('es-AR');
const formatCurrency = (n) => '$' + Number(n || 0).toLocaleString('es-AR');

const selectedTerritorioId = ref(props.territorioActivo.id);

const cambiarTerritorio = () => {
  router.get(
    '/territorios/impacto-electoral',
    { territorio_id: selectedTerritorioId.value },
    { preserveState: true, preserveScroll: true }
  );
};

// --- SIMULADOR INTERACTIVO DE PAUTA VS PADRÓN ---
const budgetSlider = ref(50000); // 50.000 ARS por defecto

const simulatedElectoresImpactados = computed(() => {
  const padron = props.stats.padron_electoral || 31000;
  const estimated = Math.round(budgetSlider.value / 1.6);
  return Math.min(padron, estimated);
});

const simulatedCoberturaPadronPct = computed(() => {
  const padron = props.stats.padron_electoral || 1;
  return Math.min(100, Number(((simulatedElectoresImpactados.value / padron) * 100).toFixed(1)));
});

const simulatedCoberturaMetaVotosPct = computed(() => {
  const meta = props.stats.meta_votos || 1;
  return Math.min(100, Number(((simulatedElectoresImpactados.value / meta) * 100).toFixed(1)));
});

const simulatedFrecuenciaImpacto = computed(() => {
  if (budgetSlider.value < 20000) return '1.2x (Baja exposición)';
  if (budgetSlider.value < 60000) return '2.4x (Frecuencia óptima de recordación)';
  if (budgetSlider.value < 150000) return '3.8x (Alta saturación de campaña)';
  return '5.2x (Dominio absoluto del feed)';
});

const getSocialColor = (key) => {
  switch (key) {
    case 'instagram': return { color: '#E4405F', name: 'Instagram' };
    case 'facebook': return { color: '#1877F2', name: 'Facebook' };
    case 'tiktok': return { color: '#00F2FE', name: 'TikTok' };
    case 'youtube': return { color: '#FF0000', name: 'YouTube' };
    default: return { color: '#06b6d4', name: 'Red Social' };
  }
};

// --- HELPER PARA GENERAR COORDENADAS SVG DE DONUT CHARTS ---
const getDonutSlices = (items) => {
  if (!items || !items.length) return [];
  const total = items.reduce((sum, item) => sum + Number(item.valor || 0), 0);
  if (total === 0) return [];

  let accumulatedAngle = 0;
  return items.map(item => {
    const fraction = Number(item.valor || 0) / total;
    const angle = fraction * 360;
    const startAngle = accumulatedAngle;
    accumulatedAngle += angle;

    // Calcular coordenadas SVG para arco
    const r = 40;
    const cx = 50;
    const cy = 50;

    const startRad = (startAngle - 90) * (Math.PI / 180);
    const endRad = (accumulatedAngle - 90) * (Math.PI / 180);

    const x1 = cx + r * Math.cos(startRad);
    const y1 = cy + r * Math.sin(startRad);
    const x2 = cx + r * Math.cos(endRad);
    const y2 = cy + r * Math.sin(endRad);

    const largeArcFlag = angle > 180 ? 1 : 0;
    const d = fraction >= 0.999
      ? `M ${cx} ${cy - r} A ${r} ${r} 0 1 1 ${cx - 0.01} ${cy - r}`
      : `M ${x1} ${y1} A ${r} ${r} 0 ${largeArcFlag} 1 ${x2} ${y2}`;

    return {
      ...item,
      d,
      angle,
      color: item.color,
      strokeDasharray: `${fraction * 251.2} 251.2`,
      strokeDashoffset: `${-(startAngle / 360) * 251.2}`,
    };
  });
};
</script>

<template>
  <Head :title="`Matriz de Impacto Territorial - ${territorioActivo.nombre}`" />

  <WarRoomLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-16">

      <!-- Top Header & Selector de Territorio -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
          <Link
            href="/territorios"
            class="p-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-cyan-500 hover:border-cyan-500/30 transition-all shadow-xs flex items-center justify-center cursor-pointer"
            title="Volver a Mapa de Situación Territorial"
          >
            <ArrowLeft class="w-4 h-4" />
          </Link>

          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Target class="w-6 h-6 text-cyan-500" />
                <span>Matriz de Impacto Territorial & Penetración Electoral</span>
              </h1>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
                Padrón & Redes
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Cruce estratégico entre la demografía del <strong>{{ territorioActivo.nombre }}</strong>, penetración por red social y decisiones de pauta publicitaria.
            </p>
          </div>
        </div>

        <!-- Selector de Territorio / Departamento -->
        <div v-if="departamentos.length > 1" class="flex items-center gap-2">
          <label class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1">
            <MapPin class="w-3.5 h-3.5 text-cyan-500" />
            <span>Territorio:</span>
          </label>
          <select
            v-model="selectedTerritorioId"
            @change="cambiarTerritorio"
            class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
          >
            <option v-for="d in departamentos" :key="d.id" :value="d.id">
              {{ d.nombre }} ({{ formatNumber(d.padron_electoral) }} electores)
            </option>
          </select>
        </div>
      </div>

      <!-- SECCIÓN 1: Tarjetas KPI de Impacto & Penetración Electoral -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Padrón Electoral & Meta de Votos -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Padrón Electoral</span>
            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
              <Users class="w-4 h-4" />
            </div>
          </div>
          <div>
            <span class="text-2xl font-black font-mono text-slate-900 dark:text-slate-100">
              {{ formatNumber(stats.padron_electoral) }}
            </span>
          </div>
          <div class="text-[11px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800 font-mono">
            <span>Meta de Victoria (40%):</span>
            <span class="font-bold text-emerald-500">{{ formatNumber(stats.meta_votos) }} votos</span>
          </div>
        </div>

        <!-- 2. Penetración Digital Directa en el Padrón -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Penetración Digital</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <TrendingUp class="w-4 h-4" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl font-black font-mono text-emerald-500">
              {{ stats.penetracion_padron_pct }}%
            </span>
            <span class="text-xs font-mono text-slate-400">
              del padrón
            </span>
          </div>
          <div class="text-[11px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800 font-mono">
            <span>Seguidores Totales:</span>
            <span class="font-bold text-slate-700 dark:text-slate-300">{{ formatNumber(stats.total_seguidores_comunidad) }}</span>
          </div>
        </div>

        <!-- 3. Rendimiento Promedio por Post (Sin Inflación Acumulada) -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">⚡ Promedio / Post</span>
            <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center">
              <Flame class="w-4 h-4 fill-current" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl font-black font-mono text-rose-500">
              {{ stats.promedio_interacciones_por_post }}
            </span>
            <span class="text-xs font-mono text-slate-400">
              interacc. / post
            </span>
          </div>
          <div class="text-[11px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800 font-mono">
            <span>Moviliza por posteo:</span>
            <span class="font-bold text-cyan-500">{{ stats.tasa_movilizacion_promedio_pct }}% del padrón</span>
          </div>
        </div>

        <!-- 4. Pico Máximo Viral (Techo Histórico) -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">🏆 Pico Máximo Viral</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <Award class="w-4 h-4" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl font-black font-mono text-amber-500">
              {{ formatNumber(stats.pico_maximo_interacciones) }}
            </span>
            <span class="text-xs font-mono text-slate-400">
              reacciones
            </span>
          </div>
          <div class="text-[11px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800 font-mono">
            <span>Techo de impacto:</span>
            <span class="font-bold text-amber-500">{{ stats.tasa_movilizacion_pico_pct }}% del padrón</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: 🥧 GRÁFICOS DE PASTEL (DONUT CHARTS) INTERACTIVOS -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Pastel 1: Penetración en el Padrón -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <PieIcon class="w-4 h-4 text-cyan-500" />
                <span>Comunidad vs. Padrón</span>
              </h3>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-500">
                {{ stats.penetracion_padron_pct }}% Cobertura
              </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
              Porción del padrón electoral alcanzada por seguidores.
            </p>
          </div>

          <!-- Donut SVG -->
          <div class="flex items-center justify-center relative py-2">
            <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
              <!-- Fondo del anillo -->
              <circle cx="50" cy="50" r="38" fill="transparent" stroke="currentColor" stroke-width="14" class="text-slate-100 dark:text-slate-800" />
              <!-- Sector Comunidad -->
              <circle
                cx="50"
                cy="50"
                r="38"
                fill="transparent"
                stroke="#06b6d4"
                stroke-width="14"
                :stroke-dasharray="`${(stats.penetracion_padron_pct * 238.76) / 100} 238.76`"
                stroke-dashoffset="0"
                class="transition-all duration-1000"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-xl font-black font-mono text-slate-900 dark:text-slate-100">
                {{ stats.penetracion_padron_pct }}%
              </span>
              <span class="text-[9px] font-mono text-slate-400 uppercase">del padrón</span>
            </div>
          </div>

          <!-- Leyendas -->
          <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 font-mono text-xs">
            <div class="flex items-center justify-between">
              <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                <span>Comunidad en Redes:</span>
              </span>
              <span class="font-bold text-slate-900 dark:text-slate-100">{{ formatNumber(stats.total_seguidores_comunidad) }} ({{ stats.penetracion_padron_pct }}%)</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                <span>Padrón No Alcanzado:</span>
              </span>
              <span class="font-bold text-slate-500">{{ formatNumber(Math.max(0, stats.padron_electoral - stats.total_seguidores_comunidad)) }}</span>
            </div>
          </div>
        </div>

        <!-- Pastel 2: Distribución de la Comunidad por Red Social -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Layers class="w-4 h-4 text-purple-500" />
                <span>Cuota por Red Social</span>
              </h3>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-purple-500/10 text-purple-500">
                Mix de Canales
              </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
              Participación de cada red en el total de la comunidad.
            </p>
          </div>

          <!-- Donut SVG Multi-Slice -->
          <div class="flex items-center justify-center relative py-2">
            <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="38" fill="transparent" stroke="currentColor" stroke-width="14" class="text-slate-100 dark:text-slate-800" />
              <circle
                v-for="(slice, i) in getDonutSlices(pasteles?.redes)"
                :key="i"
                cx="50"
                cy="50"
                r="38"
                fill="transparent"
                :stroke="slice.color"
                stroke-width="14"
                :stroke-dasharray="slice.strokeDasharray"
                :stroke-dashoffset="slice.strokeDashoffset"
                class="transition-all duration-1000"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-base font-black font-mono text-slate-900 dark:text-slate-100">
                {{ formatNumber(stats.total_seguidores_comunidad) }}
              </span>
              <span class="text-[9px] font-mono text-slate-400 uppercase">seguidores</span>
            </div>
          </div>

          <!-- Leyendas de Redes -->
          <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 font-mono text-xs">
            <div
              v-for="(red, i) in pasteles?.redes"
              :key="i"
              class="flex items-center justify-between"
            >
              <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: red.color }"></span>
                <span>{{ red.label }}:</span>
              </span>
              <span class="font-bold text-slate-900 dark:text-slate-100">
                {{ formatNumber(red.valor) }} <span class="text-slate-400 font-normal">({{ red.porcentaje }}%)</span>
              </span>
            </div>
            <div v-if="!pasteles?.redes?.length" class="text-slate-400 text-center py-1">
              Configura tus canales en Mi Candidato
            </div>
          </div>
        </div>

        <!-- Pastel 3: Núcleo Duro (Militancia) vs Expansión por Pauta vs Silencioso -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Compass class="w-4 h-4 text-emerald-500" />
                <span>Estructura del Electorado</span>
              </h3>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-500">
                Núcleo vs. Pauta
              </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
              Militancia habitual vs electores alcanzados por pauta.
            </p>
          </div>

          <!-- Donut SVG -->
          <div class="flex items-center justify-center relative py-2">
            <svg class="w-36 h-36 transform -rotate-90" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="38" fill="transparent" stroke="currentColor" stroke-width="14" class="text-slate-100 dark:text-slate-800" />
              <circle
                v-for="(slice, i) in getDonutSlices(pasteles?.electorado)"
                :key="i"
                cx="50"
                cy="50"
                r="38"
                fill="transparent"
                :stroke="slice.color"
                stroke-width="14"
                :stroke-dasharray="slice.strokeDasharray"
                :stroke-dashoffset="slice.strokeDashoffset"
                class="transition-all duration-1000"
              />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-xs font-black font-mono text-emerald-500">
                NÚCLEO
              </span>
              <span class="text-[10px] font-mono text-slate-400">
                {{ formatNumber(pasteles?.electorado?.[0]?.valor) }} fieles
              </span>
            </div>
          </div>

          <!-- Leyendas -->
          <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800 font-mono text-xs">
            <div
              v-for="(el, i) in pasteles?.electorado"
              :key="i"
              class="flex items-center justify-between"
            >
              <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                <span class="w-2.5 h-2.5 rounded-full" :style="{ backgroundColor: el.color }"></span>
                <span>{{ el.label }}:</span>
              </span>
              <span class="font-bold text-slate-900 dark:text-slate-100">
                {{ formatNumber(el.valor) }} <span class="text-slate-400 font-normal">({{ el.porcentaje }}%)</span>
              </span>
            </div>
          </div>
        </div>

      </div>

      <!-- SECCIÓN 3: ⏳ INTELIGENCIA DE TIEMPO & RITMO DE CAMPAÑA HACIA LA ELECCIÓN -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
              <Clock class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span>Inteligencia de Tiempo & Proyección al Día de la Elección</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500 text-slate-950">
                  ⏳ {{ inteligenciaTiempo.dias_para_eleccion }} Días Restantes
                </span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Velocidad de penetración orgánica actual vs. Aceleración necesaria con pauta para alcanzar la meta de votos.
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
          <!-- 1. Ritmo Semanal Actual -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Velocidad Orgánica</span>
            <span class="text-xl font-black text-slate-800 dark:text-slate-200 block">
              +{{ inteligenciaTiempo.ritmo_semanal_crecimiento }} electores
            </span>
            <span class="text-[10px] text-slate-400 block">por semana en promedio</span>
          </div>

          <!-- 2. Proyección al Día D -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Proyección Orgánica Día D</span>
            <span class="text-xl font-black text-cyan-600 dark:text-cyan-400 block">
              {{ formatNumber(inteligenciaTiempo.proyeccion_organica_total) }} electores
            </span>
            <span class="text-[10px] text-cyan-500 block">({{ inteligenciaTiempo.proyeccion_organica_padron_pct }}% del padrón)</span>
          </div>

          <!-- 3. Brecha de Victoria -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Brecha vs Meta de Votos</span>
            <span class="text-xl font-black text-amber-500 block">
              -{{ formatNumber(inteligenciaTiempo.brecha_meta_votos) }} votos
            </span>
            <span class="text-[10px] text-amber-500/80 block">para asegurar victoria (40%)</span>
          </div>

          <!-- 4. Pauta Mensual de Aceleración -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Pauta Mensual Sugerida</span>
            <span class="text-xl font-black text-emerald-500 block">
              {{ formatCurrency(inteligenciaTiempo.pauta_mensual_sugerida_ars) }}
            </span>
            <span class="text-[10px] text-emerald-600 block">para cerrar la brecha a tiempo</span>
          </div>
        </div>

        <!-- Franjas Horarias & Días Pico -->
        <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-between flex-wrap gap-4 text-xs font-mono text-cyan-800 dark:text-cyan-300">
          <div class="flex items-center gap-2">
            <Calendar class="w-4 h-4 text-cyan-500 shrink-0" />
            <span><strong>Días de Máxima Receptividad Barrial:</strong> {{ inteligenciaTiempo.dias_pico }}</span>
          </div>
          <div class="flex items-center gap-2">
            <Zap class="w-4 h-4 text-amber-500 shrink-0" />
            <span><strong>Horarios Prime:</strong> {{ inteligenciaTiempo.horarios_prime }}</span>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 4: Cruce Demográfico por Red Social y Cobertura Etaria -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Layers class="w-5 h-5 text-cyan-500" />
              <span>Cruce de Redes Sociales vs. Segmentos Etarios del Padrón</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Evaluación de cobertura en el territorio según los grupos demográficos donde tiene mayor peso cada plataforma.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="red in redesImpacto"
            :key="red.id"
            class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3.5 flex flex-col justify-between"
          >
            <div class="space-y-2.5">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs" :style="{ backgroundColor: getSocialColor(red.plataforma).color + '20', color: getSocialColor(red.plataforma).color }">
                    ●
                  </div>
                  <div>
                    <h4 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                      {{ getSocialColor(red.plataforma).name }}
                    </h4>
                    <span class="text-[11px] font-mono text-slate-400">{{ red.handle_usuario }}</span>
                  </div>
                </div>

                <div class="text-right font-mono">
                  <span class="text-base font-extrabold text-cyan-600 dark:text-cyan-400 block">
                    {{ red.cobertura_padron_pct }}%
                  </span>
                  <span class="text-[9px] text-slate-400 uppercase">del padrón</span>
                </div>
              </div>

              <!-- Rango etario target -->
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 font-mono block">🎯 Audiencia Objetivo en Territorio:</span>
                <p class="text-slate-800 dark:text-slate-200 font-semibold text-[11px]">
                  {{ red.rango_objetivo }}
                </p>
              </div>

              <!-- Diagnóstico Táctico -->
              <div class="text-xs font-mono font-medium">
                {{ red.diagnostico }}
              </div>
            </div>

            <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between font-mono text-xs text-slate-500">
              <span>👥 {{ formatNumber(red.seguidores) }} seg</span>
              <span>🔥 {{ formatNumber(red.total_interacciones) }} int</span>
              <span>📢 {{ formatCurrency(red.pauta_invertida) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 5: 🚀 Motor de Oportunidades de Pauta (Boost AI Engine) -->
      <div v-if="oportunidadesPauta.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Sparkles class="w-5 h-5 text-amber-500" />
              <span>Oportunidades de Pauta & Impulso Algorítmico (Boost Engine)</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Ventana de Oro (48h a 7 días): Publicaciones orgánicas maduras con alto engagement detectadas para convertirlas en anuncios de alto impacto territorial.
            </p>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">
            Ventana 48h - 7 Días
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="op in oportunidadesPauta"
            :key="op.id"
            class="p-5 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/30 space-y-3 flex flex-col justify-between"
          >
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs font-mono">
                <span class="px-2 py-0.5 rounded-md bg-amber-500 text-slate-950 font-black text-[10px]">
                  🚀 OPORTUNIDAD BOOST
                </span>
                <span class="text-amber-600 dark:text-amber-400 font-bold text-[11px]">
                  {{ op.eje_tematico }}
                </span>
              </div>

              <!-- Estado de la ventana de 48h a 7 días -->
              <div class="flex items-center justify-between text-[10px] font-mono">
                <span class="px-2 py-0.5 rounded-md bg-white dark:bg-slate-900 border border-amber-500/30 font-bold text-slate-700 dark:text-slate-300">
                  {{ op.estado_ventana }}
                </span>
                <span class="text-slate-400">
                  {{ op.tipo_formato }} &bull; {{ getSocialColor(op.plataforma).name }}
                </span>
              </div>

              <p class="text-xs text-slate-800 dark:text-slate-200 font-medium line-clamp-3">
                "{{ op.contenido_resumen }}"
              </p>

              <div class="flex items-center gap-2 font-mono text-xs text-slate-500">
                <span>❤️ {{ formatNumber(op.total_likes) }}</span>
                <span>💬 {{ formatNumber(op.total_comentarios) }}</span>
                <span class="text-cyan-500 font-bold">🔥 {{ formatNumber(op.total_interacciones) }} int</span>
              </div>
            </div>

            <!-- Caja de Sugerencia Táctica de Pauta -->
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/20 space-y-1.5 text-xs font-mono">
              <div class="flex items-center justify-between">
                <span class="text-slate-400 text-[10px] uppercase">Inversión Sugerida:</span>
                <span class="font-extrabold text-amber-500">{{ formatCurrency(op.sugerencia_inversion_ars) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-slate-400 text-[10px] uppercase">Alcance Proyectado:</span>
                <span class="font-extrabold text-emerald-500">{{ formatNumber(op.alcance_estimado_electores) }} electores</span>
              </div>
              <p class="text-[11px] text-slate-500 font-sans pt-1 border-t border-slate-100 dark:border-slate-800">
                {{ op.justificacion }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 6: 🎛️ Simulador Interactivo de Inversión en Pauta vs. Padrón -->
      <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 shadow-xl space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
              <Calculator class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base sm:text-lg font-extrabold text-slate-100 flex items-center gap-2">
                <span>Simulador de Presupuesto & Cobertura del Padrón</span>
              </h3>
              <p class="text-xs text-slate-400">
                Mueve el presupuesto de pauta geolocalizada para simular el impacto en electores de <strong>{{ territorioActivo.nombre }}</strong>.
              </p>
            </div>
          </div>

          <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-cyan-500 text-slate-950">
            Algoritmo Predictivo War Room
          </span>
        </div>

        <!-- Slider de Presupuesto -->
        <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
          <div class="flex items-center justify-between flex-wrap gap-2">
            <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider">
              Presupuesto Mensual de Pauta Asignado:
            </span>
            <span class="text-2xl sm:text-3xl font-black font-mono text-cyan-400">
              {{ formatCurrency(budgetSlider) }} ARS
            </span>
          </div>

          <input
            v-model.number="budgetSlider"
            type="range"
            min="10000"
            max="500000"
            step="5000"
            class="w-full h-3 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-cyan-400"
          />

          <div class="flex items-center justify-between text-[10px] font-mono text-slate-500">
            <span>$10.000 (Mínimo Táctico)</span>
            <span>$100.000 (Campaña Media)</span>
            <span>$250.000 (Alta Intensidad)</span>
            <span>$500.000 (Saturación)</span>
          </div>
        </div>

        <!-- Resultados del Simulador -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
          <!-- 1. Electores Únicos Impactados -->
          <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Electores Impactados</span>
            <span class="text-2xl font-extrabold text-cyan-400 block">
              {{ formatNumber(simulatedElectoresImpactados) }}
            </span>
            <span class="text-[10px] text-slate-400 block">
              de {{ formatNumber(stats.padron_electoral) }} del padrón
            </span>
          </div>

          <!-- 2. Cobertura del Padrón -->
          <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">% Cobertura Padrón</span>
            <span class="text-2xl font-extrabold text-emerald-400 block">
              {{ simulatedCoberturaPadronPct }}%
            </span>
            <span class="text-[10px] text-emerald-500/80 block">
              del electorado total
            </span>
          </div>

          <!-- 3. Cobertura de Meta de Votos -->
          <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Meta de Victoria (40%)</span>
            <span class="text-2xl font-extrabold text-blue-400 block">
              {{ simulatedCoberturaMetaVotosPct }}%
            </span>
            <span class="text-[10px] text-blue-400/80 block">
              de los {{ formatNumber(stats.meta_votos) }} votos objetivo
            </span>
          </div>

          <!-- 4. Frecuencia Estimada -->
          <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
            <span class="text-[10px] text-slate-400 uppercase font-bold block">Frecuencia por Elector</span>
            <span class="text-lg font-extrabold text-amber-400 block">
              {{ simulatedFrecuenciaImpacto }}
            </span>
            <span class="text-[10px] text-slate-400 block">
              repetición del mensaje
            </span>
          </div>
        </div>

        <!-- Sugerencia de Distribución de Canales para esta Inversión -->
        <div class="p-4 rounded-2xl bg-slate-900/40 border border-slate-800/60 flex items-center justify-between flex-wrap gap-4 text-xs font-mono">
          <div class="flex items-center gap-2">
            <Compass class="w-4 h-4 text-cyan-400" />
            <span class="font-bold text-slate-300">Mix de Medios Recomendado:</span>
          </div>
          <div class="flex items-center gap-4 flex-wrap text-[11px]">
            <span class="text-cyan-400">Meta Ads (IG/FB): 60% ({{ formatCurrency(budgetSlider * 0.6) }})</span>
            <span class="text-blue-400">TikTok Ads: 25% ({{ formatCurrency(budgetSlider * 0.25) }})</span>
            <span class="text-red-400">YouTube / Prensa: 15% ({{ formatCurrency(budgetSlider * 0.15) }})</span>
          </div>
        </div>
      </div>

    </div>
  </WarRoomLayout>
</template>
