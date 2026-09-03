<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import MetricCard from '../../Components/MetricCard.vue';
import Badge from '../../Components/Badge.vue';
import axios from 'axios';
import {
  BarChart3,
  TrendingUp,
  DollarSign,
  Sparkles,
  Zap,
  Target,
  Percent,
  Sliders,
  HelpCircle,
  CheckCircle2,
  PieChart,
  Eye,
  Heart,
  Share2,
  MessageCircle
} from '@lucide/vue';

const props = defineProps({
  metricas_generales: {
    type: Object,
    default: () => ({}),
  },
  share_of_voice: {
    type: Array,
    default: () => [],
  },
  plataformas_stats: {
    type: Object,
    default: () => ({}),
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  simulacion_inicial: {
    type: Object,
    default: () => ({}),
  }
});

// Interactive Predictor State
const simulacion = ref(props.simulacion_inicial || {});
const montoSlider = ref(props.simulacion_inicial?.monto_invertido || 50000);
const formatoSelect = ref(props.simulacion_inicial?.formato || 'Reel');
const plataformaSelect = ref(props.simulacion_inicial?.plataforma || 'instagram');
const candidatoSelect = ref('');
const isSimulating = ref(false);

const formatos = ['Reel', 'Video', 'Foto', 'Carrusel', 'Tweet', 'Shorts'];
const plataformas = [
  { key: 'instagram', label: 'Instagram' },
  { key: 'facebook', label: 'Facebook' },
  { key: 'tiktok', label: 'TikTok' },
  { key: 'x_twitter', label: 'X (Twitter)' },
  { key: 'youtube', label: 'YouTube' },
];

const presetAmounts = [15000, 35000, 50000, 100000, 200000, 500000];

const ejecutarSimulacion = async () => {
  isSimulating.value = true;
  try {
    const res = await axios.post('/analytics/predict', {
      monto: montoSlider.value,
      formato: formatoSelect.value,
      plataforma: plataformaSelect.value,
      candidato_id: candidatoSelect.value || null,
    });
    simulacion.value = res.data;
  } catch (err) {
    console.error('Error al simular pauta:', err);
  } finally {
    isSimulating.value = false;
  }
};

let debounceTimer = null;

const debouncedSimular = () => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    ejecutarSimulacion();
  }, 300);
};

watch([montoSlider, formatoSelect, plataformaSelect, candidatoSelect], () => {
  debouncedSimular();
});

onUnmounted(() => {
  if (debounceTimer) {
    clearTimeout(debounceTimer);
  }
});

const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};

const formatCurrency = (amount) => {
  if (!amount) return '$0';
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(amount);
};
</script>

<template>
  <Head title="War Room Analytics & Predictor de Pauta" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <BarChart3 class="w-6 h-6 text-cyan-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            War Room Analytics & Predictor de Pauta
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Inteligencia competitiva, Share of Voice y simulación algorítmica de impacto publicitario con porcentaje de proximidad.
        </p>
      </div>
    </div>

    <!-- Metricas Clave Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <MetricCard
        label="Impacto Total Acumulado"
        :value="formatNumber(metricas_generales.total_vistas)"
        hint="Visualizaciones multired"
        trend="up"
        trend-value="+24.8% este ciclo"
      />

      <MetricCard
        label="Inversión en Pauta Total"
        :value="formatCurrency(metricas_generales.total_pauta)"
        hint="Presupuesto auditado"
        trend="neutral"
        trend-value="Multi-plataforma"
      />

      <MetricCard
        label="Vistas Pagadas vs Orgánicas"
        :value="`${Math.round((metricas_generales.vistas_pagadas / (metricas_generales.total_vistas || 1)) * 100)}% / ${Math.round((metricas_generales.vistas_organicas / (metricas_generales.total_vistas || 1)) * 100)}%`"
        hint="Ratio Distribución"
        trend="up"
        trend-value="Tracción híbrida"
      />

      <MetricCard
        label="Interacciones & Likes"
        :value="formatNumber(metricas_generales.total_likes)"
        hint="Reacciones sociales"
        trend="up"
        trend-value="Alto engagement"
      />
    </div>

    <!-- PREDICTOR DE PAUTA CON PORCENTAJE DE PROXIMIDAD (HERRAMIENTA CENTRAL) -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border-2 border-violet-500/40 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden text-slate-100">
      <!-- Glow effect -->
      <div class="absolute -right-20 -top-20 w-80 h-80 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-cyan-600/15 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Predictor Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
          <div class="flex items-center gap-2">
            <Sparkles class="w-6 h-6 text-violet-400" />
            <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
              <span>Predictor de Pauta & Simulador de Impacto</span>
              <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-violet-500/30 text-violet-300 border border-violet-500/40">
                IA Calibrada
              </span>
            </h2>
          </div>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Simula la inversión en publicaciones orgánicas anteriores y estima el impacto con afinamiento histórico continuo.
          </p>
        </div>

        <!-- Porcentaje de Proximidad / Certeza Big Badge -->
        <div class="flex items-center gap-3 bg-slate-950/80 border border-violet-500/50 px-4 py-2.5 rounded-2xl shrink-0 shadow-inner">
          <div>
            <span class="text-[10px] uppercase font-mono text-slate-400 block font-semibold">
              Proximidad / Certeza
            </span>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-extrabold font-mono text-violet-400">
                {{ simulacion.porcentaje_proximidad || 85 }}%
              </span>
              <span class="text-[10px] text-emerald-400 font-mono">({{ simulacion.muestras_analizadas || 0 }} muestras)</span>
            </div>
          </div>
          <Target class="w-7 h-7 text-violet-400 animate-pulse" />
        </div>
      </div>

      <!-- Simulator Controls Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-6">
        <!-- Controls Column (5 cols) -->
        <div class="lg:col-span-5 space-y-5">
          <!-- Budget Slider & Quick Presets -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-slate-300">
                Monto de Inversión Simulado
              </label>
              <span class="text-lg font-extrabold font-mono text-cyan-400">
                {{ formatCurrency(montoSlider) }}
              </span>
            </div>

            <input
              v-model.number="montoSlider"
              type="range"
              min="5000"
              max="500000"
              step="5000"
              class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-cyan-500"
            />

            <!-- Presets buttons -->
            <div class="flex flex-wrap gap-1.5 pt-1">
              <button
                v-for="p in presetAmounts"
                :key="p"
                type="button"
                @click="montoSlider = p"
                class="px-2 py-0.8 rounded-lg text-[11px] font-mono border transition-all cursor-pointer"
                :class="montoSlider === p ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-500' : 'bg-slate-800/80 border-slate-700 text-slate-300 hover:border-slate-500'"
              >
                ${{ (p / 1000) }}K
              </button>
            </div>
          </div>

          <!-- Format Selector -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
              Tipo de Formato
            </label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="f in formatos"
                :key="f"
                type="button"
                @click="formatoSelect = f"
                class="px-3 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer"
                :class="formatoSelect === f ? 'bg-violet-600 text-white border-violet-500 shadow-md' : 'bg-slate-800/80 border-slate-700 text-slate-400 hover:border-slate-500'"
              >
                {{ f }}
              </button>
            </div>
          </div>

          <!-- Social Network Selector -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
              Plataforma Social
            </label>
            <div class="flex flex-wrap gap-1.5">
              <button
                v-for="plat in plataformas"
                :key="plat.key"
                type="button"
                @click="plataformaSelect = plat.key"
                class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold border transition-all cursor-pointer"
                :class="plataformaSelect === plat.key ? 'bg-cyan-500 text-slate-950 border-cyan-400 shadow-sm' : 'bg-slate-800/80 border-slate-700 text-slate-400 hover:border-slate-500'"
              >
                {{ plat.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Predicted Impact Dashboard (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
          <!-- Main Output: Estimated Views Bracket -->
          <div class="p-6 rounded-3xl bg-slate-950/70 border border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-xs font-mono uppercase font-bold text-slate-400 flex items-center gap-1.5">
                <Eye class="w-4 h-4 text-cyan-400" />
                <span>Visualizaciones Proyectadas</span>
              </span>
              <span class="text-xs font-mono text-cyan-400 font-bold">
                CPV Est: ${{ simulacion.cpv_estimado_ars || 0.75 }} ARS
              </span>
            </div>

            <!-- Big Number -->
            <div class="text-center py-2">
              <span class="text-4xl sm:text-5xl font-extrabold font-mono text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-violet-400">
                {{ formatNumber(simulacion.vistas_esperadas) }}
              </span>
              <span class="text-xs text-slate-400 block mt-1 font-mono">
                Rango proyectado: {{ formatNumber(simulacion.vistas_minimas) }} — {{ formatNumber(simulacion.vistas_maximas) }} vistas
              </span>
            </div>

            <!-- Visual Dispersion Range Meter -->
            <div class="space-y-1.5 pt-2">
              <div class="h-3 w-full bg-slate-800 rounded-full overflow-hidden flex">
                <div class="h-full bg-slate-700" style="width: 20%;"></div>
                <div class="h-full bg-gradient-to-r from-cyan-500 to-violet-500" style="width: 60%;"></div>
                <div class="h-full bg-slate-700" style="width: 20%;"></div>
              </div>
              <div class="flex items-center justify-between text-[10px] font-mono text-slate-400">
                <span>Mínimo: {{ formatNumber(simulacion.vistas_minimas) }}</span>
                <span class="font-bold text-cyan-400">Esperado: {{ formatNumber(simulacion.vistas_esperadas) }}</span>
                <span>Máximo: {{ formatNumber(simulacion.vistas_maximas) }}</span>
              </div>
            </div>
          </div>

          <!-- Secondary Projected Metrics (Likes, Comments, Shares) -->
          <div class="grid grid-cols-3 gap-3">
            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center">
              <span class="text-[11px] font-mono uppercase text-slate-400 block font-semibold flex items-center justify-center gap-1">
                <Heart class="w-3.5 h-3.5 text-rose-500" />
                <span>Likes Est.</span>
              </span>
              <span class="text-xl font-extrabold font-mono text-slate-100 mt-1 block">
                {{ formatNumber(simulacion.likes_estimados) }}
              </span>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center">
              <span class="text-[11px] font-mono uppercase text-slate-400 block font-semibold flex items-center justify-center gap-1">
                <MessageCircle class="w-3.5 h-3.5 text-cyan-400" />
                <span>Comentarios</span>
              </span>
              <span class="text-xl font-extrabold font-mono text-slate-100 mt-1 block">
                {{ formatNumber(simulacion.comentarios_estimados) }}
              </span>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-slate-800 text-center">
              <span class="text-[11px] font-mono uppercase text-slate-400 block font-semibold flex items-center justify-center gap-1">
                <Share2 class="w-3.5 h-3.5 text-emerald-400" />
                <span>Compartidos</span>
              </span>
              <span class="text-xl font-extrabold font-mono text-slate-100 mt-1 block">
                {{ formatNumber(simulacion.compartidos_estimados) }}
              </span>
            </div>
          </div>

          <!-- Strategic AI Recommendation Box -->
          <div class="p-4 rounded-2xl bg-violet-500/10 border border-violet-500/30 text-xs text-violet-200 leading-relaxed flex items-start gap-2.5">
            <Sparkles class="w-4 h-4 text-violet-400 shrink-0 mt-0.5" />
            <div>
              <strong class="font-bold text-violet-300 block mb-0.5">Dictamen Estratégico de Pauta:</strong>
              {{ simulacion.recomendacion_estrategica }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SHARE OF VOICE & COMPETITIVE BENCHMARK -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left 7 cols: Share of Voice Meter -->
      <div class="lg:col-span-7 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs space-y-5">
        <div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <PieChart class="w-5 h-5 text-cyan-500" />
            <span>Share of Voice (Participación en Vistas Totales)</span>
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">Porcentaje de impacto acumulado por cada candidato</p>
        </div>

        <div class="space-y-4">
          <div
            v-for="sov in share_of_voice"
            :key="sov.id"
            class="space-y-1.5"
          >
            <div class="flex items-center justify-between text-xs">
              <div class="flex items-center gap-2">
                <span class="font-bold text-slate-900 dark:text-slate-100">{{ sov.nombre }}</span>
                <span v-if="sov.es_propio" class="text-[9px] font-mono font-extrabold px-1.5 py-0.2 rounded bg-cyan-500 text-slate-950">PROPIO</span>
              </div>
              <div class="font-mono text-slate-500">
                <strong class="text-slate-900 dark:text-slate-100">{{ sov.porcentaje }}%</strong> ({{ formatNumber(sov.vistas) }} vistas)
              </div>
            </div>

            <!-- Bar -->
            <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-500"
                :style="{ width: `${sov.porcentaje}%`, backgroundColor: sov.color || '#06b6d4' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 5 cols: Platform Distribution -->
      <div class="lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
        <div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <TrendingUp class="w-5 h-5 text-cyan-500" />
            <span>Distribución de Tracción por Red</span>
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">Volumen de visualizaciones por plataforma</p>
        </div>

        <div class="space-y-2.5">
          <div
            v-for="(vistas, plat) in plataformas_stats"
            :key="plat"
            class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs"
          >
            <Badge :variant="plat" size="md" />
            <span class="font-mono font-bold text-slate-900 dark:text-slate-100 text-sm">
              {{ formatNumber(vistas) }} vistas
            </span>
          </div>
        </div>
      </div>
    </div>
  </WarRoomLayout>
</template>
