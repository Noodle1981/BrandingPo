<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import ElectoralDonutsSection from './Components/ElectoralDonutsSection.vue';
import ElectoralTiersMatrix from './Components/ElectoralTiersMatrix.vue';
import ElectoralDemographicPyramid from './Components/ElectoralDemographicPyramid.vue';
import ElectoralCampaignTiming from './Components/ElectoralCampaignTiming.vue';
import ElectoralBudgetSimulator from './Components/ElectoralBudgetSimulator.vue';
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
  cruceFranjas: {
    type: Array,
    default: () => [],
  },
  alertaBalanceRedes: {
    type: Object,
    default: null,
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

const getSocialColor = (key) => {
  switch (key) {
    case 'instagram': return { color: '#E4405F', name: 'Instagram' };
    case 'facebook': return { color: '#1877F2', name: 'Facebook' };
    case 'tiktok': return { color: '#00F2FE', name: 'TikTok' };
    case 'youtube': return { color: '#FF0000', name: 'YouTube' };
    default: return { color: '#06b6d4', name: 'Red Social' };
  }
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
      
      <!-- ALERTA DE BALANCE ESTRATÉGICO MULTI-RED & REDISTRIBUCIÓN DE PAUTA -->
      <div
        v-if="alertaBalanceRedes"
        class="p-5 rounded-3xl bg-amber-500/10 border border-amber-500/30 text-xs leading-relaxed flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm text-amber-900 dark:text-amber-200"
      >
        <div class="flex items-start gap-3">
          <div class="p-2 rounded-xl bg-amber-500/20 text-amber-500 shrink-0 mt-0.5">
            <Sparkles class="w-5 h-5" />
          </div>
          <div class="space-y-1">
            <div class="font-extrabold text-sm">
              ⚖️ Balance Multi-Red: {{ alertaBalanceRedes.mensaje }}
            </div>
            <p class="text-slate-600 dark:text-slate-300">
              🎯 <strong>Recomendación Táctica:</strong> {{ alertaBalanceRedes.accion }}
            </p>
          </div>
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
            <div class="text-2xl sm:text-3xl font-black font-mono text-slate-900 dark:text-slate-100">
              {{ formatNumber(stats.padron_electoral) }}
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-mono">
              <Target class="w-3.5 h-3.5 text-emerald-500" />
              <span>Meta Ganadora: <strong>{{ formatNumber(stats.meta_votos) }}</strong> votos ({{ stats.meta_votos_pct }}%)</span>
            </div>
          </div>
        </div>

        <!-- 2. Penetración Digital Directa en el Padrón (Desduplicada por TIERS) -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Comunidad Directa</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <Activity class="w-4 h-4" />
            </div>
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black font-mono text-emerald-500">
              {{ stats.penetracion_padron_pct }}%
            </div>
            <div class="flex items-center gap-1 text-xs text-slate-500 mt-1 font-mono">
              <span>{{ formatNumber(stats.total_seguidores_comunidad) }} electores netos</span>
              <span class="text-[10px] text-slate-400">({{ formatNumber(stats.total_seguidores_bruto) }} brutos)</span>
            </div>
          </div>
        </div>

        <!-- 3. Score de Impacto Orgánico Ponderado (vs Meta /500 pts) -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Tracción Ponderada</span>
            <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
              <Flame class="w-4 h-4" />
            </div>
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black font-mono text-purple-600 dark:text-purple-400">
              {{ formatNumber(stats.score_impacto_territorial) }} pts
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1 font-mono">
              <span>Cumplimiento Meta: <strong>{{ stats.cumplimiento_meta_score_pct }}%</strong></span>
            </div>
          </div>
        </div>

        <!-- 4. Rendimiento Promedio por Post & Pico Viral -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Impacto Promedio / Post</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <BarChart2 class="w-4 h-4" />
            </div>
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black font-mono text-amber-500">
              {{ formatNumber(stats.promedio_interacciones_post) }} int
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 mt-1 font-mono">
              <span>Pico Viral: <strong>{{ formatNumber(stats.post_pico_viral_interacciones) }} int</strong></span>
            </div>
          </div>
        </div>
      </div>

      <!-- SECCIÓN 2: 🥧 GRÁFICOS DE PASTEL (DONUT CHARTS) INTERACTIVOS DELEGADA -->
      <ElectoralDonutsSection
        :stats="stats"
        :pasteles="pasteles"
        :format-number="formatNumber"
      />

      <!-- SECCIÓN: 🏛️ MATRIZ DE TIERS & DESDUPLICACIÓN DE AUDIENCIA MULTICANAL DELEGADA -->
      <ElectoralTiersMatrix
        :stats="stats"
        :format-number="formatNumber"
      />

      <!-- SECCIÓN 3: 👥 CRUCE DEMOGRÁFICO DE VOTO POTENCIAL POR FRANJA ETARIA DELEGADA -->
      <ElectoralDemographicPyramid
        :cruce-franjas="cruceFranjas"
        :territorio-nombre="territorioActivo.nombre"
        :format-number="formatNumber"
      />

      <!-- SECCIÓN 4: ⏳ INTELIGENCIA DE TIEMPO & RITMO DE CAMPAÑA HACIA LA ELECCIÓN DELEGADA -->
      <ElectoralCampaignTiming
        :inteligencia-tiempo="inteligenciaTiempo"
        :format-number="formatNumber"
        :format-currency="formatCurrency"
      />

      <!-- SECCIÓN 5: Cruce Demográfico por Red Social y Balance Estratégico -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
          <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Layers class="w-5 h-5 text-cyan-500" />
              <span>Balance Estratégico de Redes vs. Segmentos del Padrón</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Evaluación de cobertura en el territorio y rol táctico sugerido para cada canal.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="red in redesImpacto"
            :key="red.id"
            class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3.5 flex flex-col justify-between"
            :class="{
              'border-emerald-500/40 shadow-xs shadow-emerald-500/5': red.estado_nivel === 'ganadora',
              'border-amber-500/40 shadow-xs shadow-amber-500/5': red.estado_nivel === 'regular',
              'border-rose-500/40 shadow-xs shadow-rose-500/5': red.estado_nivel === 'critico',
            }"
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

              <!-- Rol táctico y audiencia target -->
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 font-mono block">🎯 Rol Estratégico:</span>
                <p class="text-slate-800 dark:text-slate-200 font-semibold text-[11px]">
                  {{ red.estrategia_rol || red.rango_objetivo }}
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

      <!-- SECCIÓN 6: 🎛️ Simulador Interactivo de Inversión en Pauta vs. Padrón DELEGADA -->
      <ElectoralBudgetSimulator
        :stats="stats"
        :territorio-nombre="territorioActivo.nombre"
        :format-number="formatNumber"
        :format-currency="formatCurrency"
      />

    </div>
  </WarRoomLayout>
</template>
