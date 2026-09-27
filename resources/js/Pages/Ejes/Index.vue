<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import {
  Shield,
  Briefcase,
  HeartHandshake,
  Building2,
  TrendingUp,
  AlertTriangle,
  CheckCircle2,
  Calendar,
  Layers,
  Sparkles,
  ChevronDown,
  ChevronUp,
  ExternalLink,
  Users,
  Eye,
  ThumbsUp,
  Heart,
  Smile,
  Frown,
  Flame,
  Info,
  Radio,
  BarChart3
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    default: null,
  },
  periodo_seleccionado: {
    type: String,
    default: 'todos',
  },
  periodos_disponibles: {
    type: Array,
    default: () => [],
  },
  pilares: {
    type: Array,
    default: () => [],
  },
  bloque_institucional: {
    type: Object,
    default: null,
  },
  balance_discursivo: {
    type: Object,
    default: () => ({}),
  },
  evolucion_historica: {
    type: Array,
    default: () => [],
  },
  territorio: {
    type: Object,
    default: null,
  },
  notas_prensa: {
    type: Array,
    default: () => [],
  },
});

// Control de acordeón de sub-ejes por pilar
const acordeonAbierto = ref({
  1: true,
  2: true,
  3: true,
  institucional: false,
});

const toggleAcordeon = (id) => {
  acordeonAbierto.value[id] = !acordeonAbierto.value[id];
};

// Cambio de período (Filtrar por Mes o Histórico Completo)
const cambiarPeriodo = (clavePeriodo) => {
  router.get('/estrategia-ejes', { mes: clavePeriodo }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  return new Intl.NumberFormat('es-AR').format(num);
};

const getPilarIcon = (claveSlug) => {
  switch (claveSlug) {
    case 'orden':
      return Shield;
    case 'futuro':
      return Briefcase;
    case 'humano':
      return HeartHandshake;
    default:
      return Layers;
  }
};
</script>

<template>
  <Head title="Ejes de Campaña & Estrategia Discursiva | War Room" />

  <WarRoomLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-20">

      <!-- ─────────────────────────────────────────────────────────────
           1. CABECERA ESTRATÉGICA & SELECTOR TEMPORAL (MES / HISTÓRICO)
           ───────────────────────────────────────────────────────────── -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
        <!-- Resplandor sutil de fondo -->
        <div class="absolute -top-12 -right-12 w-64 h-64 bg-cyan-500/10 dark:bg-cyan-500/15 rounded-full blur-3xl pointer-events-none" />
        <div class="absolute -bottom-12 -left-12 w-64 h-64 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-full blur-3xl pointer-events-none" />

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="flex items-center gap-2.5 flex-wrap">
              <div class="w-9 h-9 rounded-2xl bg-cyan-500/15 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold">
                <Layers class="w-5 h-5" />
              </div>
              <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
                Los 3 Ejes Principales de Campaña
              </h1>
              <span class="px-3 py-1 rounded-full text-[11px] font-mono font-extrabold uppercase bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 border border-cyan-500/30">
                Matriz de Encuadre Electoral
              </span>
            </div>

            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-3xl leading-relaxed">
              En ciencia política, las campañas exitosas se estructuran sobre <strong>3 grandes ejes de batalla</strong> adaptados a la idiosincrasia ciudadana:
              <span class="text-cyan-600 dark:text-cyan-400 font-bold">El Orden</span>,
              <span class="text-emerald-600 dark:text-emerald-400 font-bold">El Futuro</span> y
              <span class="text-amber-600 dark:text-amber-400 font-bold">El Eje Humano</span>.
              Audita cómo rinde cada pilar y evita la dispersión en temas protocolares.
            </p>

            <!-- Metadata del candidato y territorio -->
            <div v-if="candidato" class="flex items-center gap-4 pt-1 flex-wrap text-xs text-slate-500 dark:text-slate-400">
              <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700 dark:text-slate-300">
                <span class="w-2 h-2 rounded-full bg-cyan-500" />
                Candidato: <strong>{{ candidato.nombre_completo }}</strong> ({{ candidato.cargo_postula || 'Campaña' }})
              </span>
              <span v-if="territorio" class="inline-flex items-center gap-1.5">
                🏛️ Territorio: <strong>{{ territorio.nombre }}</strong>
                <span v-if="territorio.padron_electoral" class="font-mono text-slate-400">
                  ({{ formatNumber(territorio.padron_electoral) }} electores habilitados)
                </span>
              </span>
            </div>
          </div>

          <!-- Selector de Período (Pills / Dropdown interactivo) -->
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 self-start lg:self-center shrink-0">
            <div class="flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
              <Calendar class="w-4 h-4 text-cyan-500 shrink-0" />
              <label for="selector-periodo" class="text-xs font-bold text-slate-600 dark:text-slate-400 shrink-0">Período:</label>
              <select
                id="selector-periodo"
                :value="periodo_seleccionado"
                @change="cambiarPeriodo($event.target.value)"
                class="bg-transparent text-xs font-black text-slate-900 dark:text-slate-100 border-none focus:ring-0 cursor-pointer pr-8 py-0.5"
              >
                <option
                  v-for="per in periodos_disponibles"
                  :key="per.clave"
                  :value="per.clave"
                  class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
                >
                  {{ per.nombre }} ({{ per.total_posts }} posts)
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Mini Stats de la Cabecera -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-5 border-t border-slate-200 dark:border-slate-800/80">
          <div class="space-y-0.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Publicaciones en Período</span>
            <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 font-mono">
              {{ balance_discursivo.total_posts || 0 }}
            </div>
          </div>
          <div class="space-y-0.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Foco en los 3 Pilares</span>
            <div class="text-xl sm:text-2xl font-black text-cyan-600 dark:text-cyan-400 font-mono">
              {{ balance_discursivo.pct_en_pilares || 0 }}%
            </div>
          </div>
          <div class="space-y-0.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Gestión / Protocolo</span>
            <div class="text-xl sm:text-2xl font-black text-slate-500 dark:text-slate-400 font-mono">
              {{ balance_discursivo.pct_institucional || 0 }}%
            </div>
          </div>
          <div class="space-y-0.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider font-mono">Pilar Líder</span>
            <div class="text-sm sm:text-base font-extrabold text-emerald-600 dark:text-emerald-400 truncate">
              {{ balance_discursivo.pilar_lider || 'Equilibrado' }}
            </div>
          </div>
        </div>
      </div>

      <!-- ─────────────────────────────────────────────────────────────
           2. BARRA DE BALANCE DISCURSIVO & DIAGNÓSTICO POLÍTICO
           ───────────────────────────────────────────────────────────── -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
          <div>
            <h2 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <BarChart3 class="w-4 h-4 text-cyan-500" />
              <span>Distribución Discursiva de Campaña (Share de Voz)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Proporción de publicaciones y mensajes dirigidos a cada pilar en el período seleccionado.
            </p>
          </div>
          <span
            class="px-3 py-1 rounded-full text-xs font-bold font-mono border"
            :class="{
              'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30': balance_discursivo.tipo_alerta === 'success',
              'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30': balance_discursivo.tipo_alerta === 'warning',
              'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500/30': balance_discursivo.tipo_alerta === 'danger',
              'bg-slate-500/15 text-slate-600 dark:text-slate-400 border-slate-500/30': balance_discursivo.tipo_alerta === 'info'
            }"
          >
            {{ balance_discursivo.tipo_alerta === 'success' ? '🎯 ESTRATEGIA BALANCEADA' : (balance_discursivo.tipo_alerta === 'danger' ? '🚨 ALERTA ROJA' : '⚡ ATENCIÓN REQUERIDA') }}
          </span>
        </div>

        <!-- Barra Proporcional Multicolor de los 3 Pilares + Institucional -->
        <div class="space-y-2">
          <div class="h-6 w-full rounded-2xl bg-slate-100 dark:bg-slate-950 p-1 flex items-center overflow-hidden gap-1 border border-slate-200 dark:border-slate-800">
            <!-- Pilar 1: El Orden -->
            <div
              v-if="pilares[0]?.share_porcentaje > 0"
              :style="{ width: `${pilares[0].share_porcentaje}%` }"
              class="h-full bg-cyan-500 rounded-xl transition-all duration-500 relative group cursor-pointer flex items-center justify-center text-[10px] font-mono font-black text-slate-950"
              title="1. El Orden"
            >
              <span v-if="pilares[0].share_porcentaje >= 12">{{ pilares[0].share_porcentaje }}%</span>
            </div>
            <!-- Pilar 2: El Futuro -->
            <div
              v-if="pilares[1]?.share_porcentaje > 0"
              :style="{ width: `${pilares[1].share_porcentaje}%` }"
              class="h-full bg-emerald-500 rounded-xl transition-all duration-500 relative group cursor-pointer flex items-center justify-center text-[10px] font-mono font-black text-slate-950"
              title="2. El Futuro"
            >
              <span v-if="pilares[1].share_porcentaje >= 12">{{ pilares[1].share_porcentaje }}%</span>
            </div>
            <!-- Pilar 3: El Eje Humano -->
            <div
              v-if="pilares[2]?.share_porcentaje > 0"
              :style="{ width: `${pilares[2].share_porcentaje}%` }"
              class="h-full bg-amber-500 rounded-xl transition-all duration-500 relative group cursor-pointer flex items-center justify-center text-[10px] font-mono font-black text-slate-950"
              title="3. El Eje Humano"
            >
              <span v-if="pilares[2].share_porcentaje >= 12">{{ pilares[2].share_porcentaje }}%</span>
            </div>
            <!-- Bloque Institucional -->
            <div
              v-if="bloque_institucional?.share_porcentaje > 0"
              :style="{ width: `${bloque_institucional.share_porcentaje}%` }"
              class="h-full bg-slate-400 dark:bg-slate-700 rounded-xl transition-all duration-500 relative group cursor-pointer flex items-center justify-center text-[10px] font-mono font-black text-white"
              title="Gestión Institucional"
            >
              <span v-if="bloque_institucional.share_porcentaje >= 12">{{ bloque_institucional.share_porcentaje }}%</span>
            </div>
          </div>

          <!-- Leyendas de la barra -->
          <div class="flex items-center justify-between flex-wrap gap-3 pt-1 text-xs font-semibold">
            <div class="flex items-center gap-1.5 text-cyan-600 dark:text-cyan-400 font-mono">
              <span class="w-3 h-3 rounded-full bg-cyan-500" />
              <span>🛡️ El Orden: {{ pilares[0]?.share_porcentaje || 0 }}% ({{ pilares[0]?.posts_count || 0 }} posts)</span>
            </div>
            <div class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-mono">
              <span class="w-3 h-3 rounded-full bg-emerald-500" />
              <span>💼 El Futuro: {{ pilares[1]?.share_porcentaje || 0 }}% ({{ pilares[1]?.posts_count || 0 }} posts)</span>
            </div>
            <div class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-mono">
              <span class="w-3 h-3 rounded-full bg-amber-500" />
              <span>🤝 El Eje Humano: {{ pilares[2]?.share_porcentaje || 0 }}% ({{ pilares[2]?.posts_count || 0 }} posts)</span>
            </div>
            <div class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400 font-mono">
              <span class="w-3 h-3 rounded-full bg-slate-400 dark:bg-slate-700" />
              <span>🏛️ Institucional: {{ bloque_institucional?.share_porcentaje || 0 }}% ({{ bloque_institucional?.posts_count || 0 }} posts)</span>
            </div>
          </div>
        </div>

        <!-- Cuadro de Diagnóstico Político -->
        <div
          class="p-4 rounded-2xl border flex items-start gap-3.5 transition-all text-xs sm:text-sm"
          :class="{
            'bg-emerald-500/10 border-emerald-500/30 text-emerald-900 dark:text-emerald-200': balance_discursivo.tipo_alerta === 'success',
            'bg-amber-500/10 border-amber-500/30 text-amber-900 dark:text-amber-200': balance_discursivo.tipo_alerta === 'warning',
            'bg-red-500/10 border-red-500/30 text-red-900 dark:text-red-200': balance_discursivo.tipo_alerta === 'danger',
            'bg-slate-500/10 border-slate-500/30 text-slate-900 dark:text-slate-200': balance_discursivo.tipo_alerta === 'info'
          }"
        >
          <div class="shrink-0 mt-0.5">
            <CheckCircle2 v-if="balance_discursivo.tipo_alerta === 'success'" class="w-5 h-5 text-emerald-500" />
            <AlertTriangle v-else-if="balance_discursivo.tipo_alerta === 'warning' || balance_discursivo.tipo_alerta === 'danger'" class="w-5 h-5 text-amber-500" />
            <Info v-else class="w-5 h-5 text-slate-400" />
          </div>
          <div>
            <span class="font-extrabold block text-xs uppercase tracking-wider mb-0.5">
              Diagnóstico Estratégico de Campaña:
            </span>
            <p class="leading-relaxed">
              {{ balance_discursivo.diagnostico }}
            </p>
          </div>
        </div>
      </div>

      <!-- ─────────────────────────────────────────────────────────────
           3. LAS 3 SALAS DE BATALLA: LOS 3 PILARES PRINCIPALES
           ───────────────────────────────────────────────────────────── -->
      <div class="space-y-6">
        <div
          v-for="pilar in pilares"
          :key="pilar.id"
          class="rounded-3xl bg-white dark:bg-slate-900 border transition-all duration-300 shadow-sm overflow-hidden"
          :class="[
            pilar.alerta_crisis ? 'border-red-500/50 shadow-red-500/10' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
          ]"
        >
          <!-- Cabecera de la Tarjeta del Pilar -->
          <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
              <!-- Icono con fondo de color -->
              <div
                class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border"
                :class="[pilar.color_bg, pilar.color_border, pilar.color_text]"
              >
                <component :is="getPilarIcon(pilar.clave_slug)" class="w-6 h-6" />
              </div>

              <div class="space-y-1">
                <div class="flex items-center gap-2.5 flex-wrap">
                  <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">
                    {{ pilar.pilar_principal }}
                  </h3>
                  <!-- Badge de Share -->
                  <span
                    class="px-2.5 py-0.5 rounded-full text-xs font-mono font-extrabold border"
                    :class="[pilar.color_bg, pilar.color_border, pilar.color_text]"
                  >
                    {{ pilar.share_porcentaje }}% de la Campaña
                  </span>
                  <!-- Badge de Alerta Roja si > 15% indignación -->
                  <span
                    v-if="pilar.alerta_crisis"
                    class="px-2.5 py-0.5 rounded-full text-xs font-mono font-extrabold bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/40 animate-pulse inline-flex items-center gap-1"
                  >
                    <Flame class="w-3.5 h-3.5" />
                    <span>ALERTA CRISIS (😡 {{ pilar.pct_indignacion }}%)</span>
                  </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                  {{ pilar.subtitulo }}
                </p>
              </div>
            </div>

            <!-- Target Etario & Propuesta Clave -->
            <div class="flex items-center gap-3 self-start lg:self-center flex-wrap">
              <div class="px-3 py-1.5 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs">
                <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold">Afinidad Demográfica:</span>
                <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ pilar.target_etario }}</span>
              </div>
              <div class="px-3 py-1.5 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs">
                <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold">Formato que más rinde:</span>
                <span class="font-extrabold" :class="pilar.color_text">{{ pilar.formato_destacado }}</span>
              </div>
            </div>
          </div>

          <!-- Cuadrícula de Métricas Clave del Pilar -->
          <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 border-b border-slate-100 dark:border-slate-800/80">
            <!-- 1. Posts & Vistas -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/70 dark:border-slate-800/70 space-y-1">
              <span class="text-[11px] font-bold text-slate-400 font-mono uppercase">Volumen Auditado</span>
              <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 font-mono">
                {{ pilar.posts_count }} <span class="text-xs font-normal text-slate-400">posts</span>
              </div>
              <div class="text-[11px] text-slate-500 font-mono">
                {{ formatNumber(pilar.total_vistas) }} vistas totales
              </div>
            </div>

            <!-- 2. Tracción e Interacciones -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/70 dark:border-slate-800/70 space-y-1">
              <span class="text-[11px] font-bold text-slate-400 font-mono uppercase">Impacto Ponderado</span>
              <div class="text-xl sm:text-2xl font-black font-mono" :class="pilar.color_text">
                {{ formatNumber(pilar.score_impacto) }} <span class="text-xs font-normal text-slate-400">pts</span>
              </div>
              <div class="text-[11px] text-slate-500 font-mono">
                {{ formatNumber(pilar.total_interacciones) }} interacciones
              </div>
            </div>

            <!-- 3. Aprobación Neta & Humor Social -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/70 dark:border-slate-800/70 space-y-1">
              <span class="text-[11px] font-bold text-slate-400 font-mono uppercase">Aprobación Neta</span>
              <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                {{ pilar.aprobacion_neta }}%
              </div>
              <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1">
                <span>⭐ {{ pilar.humor_promedio }}/5</span>
                <span class="text-slate-400">humor ciudadano</span>
              </div>
            </div>

            <!-- 4. Semáforo de Indignación (😡) -->
            <div
              class="p-4 rounded-2xl border space-y-1 transition-colors"
              :class="[
                pilar.alerta_crisis
                  ? 'bg-red-500/10 border-red-500/40 text-red-600 dark:text-red-400'
                  : 'bg-slate-50 dark:bg-slate-950/70 border-slate-200/70 dark:border-slate-800/70'
              ]"
            >
              <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold font-mono uppercase" :class="pilar.alerta_crisis ? 'text-red-600 dark:text-red-400' : 'text-slate-400'">
                  Rechazo (😡)
                </span>
                <span v-if="pilar.alerta_crisis" class="text-xs">⚠️ Crisis</span>
              </div>
              <div
                class="text-xl sm:text-2xl font-black font-mono"
                :class="pilar.alerta_crisis ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-100'"
              >
                {{ pilar.pct_indignacion }}%
              </div>
              <div class="text-[11px] text-slate-500 font-mono">
                {{ pilar.reacciones.me_enoja }} de {{ formatNumber(pilar.reacciones.total) }} reacciones
              </div>
            </div>
          </div>

          <!-- Encuadre Político: Demanda vs Propuesta -->
          <div class="px-6 py-4 bg-slate-50/40 dark:bg-slate-950/40 border-b border-slate-100 dark:border-slate-800/80 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div>
              <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold mb-0.5">
                🛑 ¿Qué demanda vecinal busca resolver?
              </span>
              <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed">
                {{ pilar.demanda_central }}
              </p>
            </div>
            <div>
              <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold mb-0.5">
                💡 Propuesta Concreta del Candidato:
              </span>
              <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed">
                {{ pilar.solucion_propuesta }}
              </p>
            </div>
          </div>

          <!-- ─────────────────────────────────────────────────────────────
               ACORDEÓN: DESGLOSE DE EJES TEMÁTICOS QUE COMPONEN ESTE PILAR
               ───────────────────────────────────────────────────────────── -->
          <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
              <button
                type="button"
                @click="toggleAcordeon(pilar.id)"
                class="flex items-center gap-2 text-xs font-black text-slate-800 dark:text-slate-200 hover:text-cyan-500 transition-colors cursor-pointer select-none"
              >
                <span>Temáticas Vecinales Acopladas ({{ pilar.subejes.length }} temas específicos)</span>
                <component :is="acordeonAbierto[pilar.id] ? ChevronUp : ChevronDown" class="w-4 h-4 text-slate-400" />
              </button>
              <span class="text-[11px] font-mono text-slate-400">
                Al elegir estos temas en un post, se acumulan a este pilar
              </span>
            </div>

            <!-- Tabla de Sub-ejes desplegable -->
            <div v-show="acordeonAbierto[pilar.id]" class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 font-mono uppercase">
                    <th class="pb-2.5">Eje Temático Concreto</th>
                    <th class="pb-2.5">Descripción de la Demanda</th>
                    <th class="pb-2.5 text-right">Posts</th>
                    <th class="pb-2.5 text-right">% del Pilar</th>
                    <th class="pb-2.5 text-right">Vistas</th>
                    <th class="pb-2.5 text-right">Score</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
                  <tr
                    v-for="sub in pilar.subejes"
                    :key="sub.id"
                    class="hover:bg-slate-50 dark:hover:bg-slate-950/50 transition-colors"
                  >
                    <td class="py-3 pr-3">
                      <div class="flex items-center gap-2">
                        <span
                          class="w-2.5 h-2.5 rounded-full shrink-0"
                          :style="{ backgroundColor: sub.color_badge || pilar.color }"
                        />
                        <span class="font-extrabold text-slate-900 dark:text-slate-100">{{ sub.nombre }}</span>
                      </div>
                    </td>
                    <td class="py-3 px-3 text-slate-500 dark:text-slate-400 max-w-xs truncate text-[11px]">
                      {{ sub.descripcion || 'Sin descripción' }}
                    </td>
                    <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                      {{ sub.posts_count }}
                    </td>
                    <td class="py-3 px-3 text-right font-mono text-slate-500">
                      {{ sub.porcentaje_del_pilar }}%
                    </td>
                    <td class="py-3 px-3 text-right font-mono text-slate-600 dark:text-slate-400">
                      {{ formatNumber(sub.total_vistas) }}
                    </td>
                    <td class="py-3 pl-3 text-right font-mono font-bold" :class="pilar.color_text">
                      {{ formatNumber(sub.score_impacto) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Top Publicaciones del Pilar en el Período -->
            <div v-if="pilar.top_publicaciones && pilar.top_publicaciones.length > 0" class="pt-4 border-t border-slate-100 dark:border-slate-800/80">
              <span class="text-[11px] font-bold text-slate-400 font-mono uppercase block mb-3">
                🔥 Publicaciones más Influyentes de este Pilar en el Período:
              </span>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div
                  v-for="top in pilar.top_publicaciones"
                  :key="top.id"
                  class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between space-y-2 hover:border-slate-300 dark:hover:border-slate-700 transition-colors"
                >
                  <div class="space-y-1">
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                      <span class="uppercase font-bold text-cyan-600 dark:text-cyan-400">{{ top.plataforma }}</span>
                      <span>{{ top.fecha_publicacion }}</span>
                    </div>
                    <p class="text-xs text-slate-800 dark:text-slate-200 line-clamp-2 font-medium">
                      "{{ top.texto_extracto }}"
                    </p>
                  </div>

                  <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono">
                    <span class="text-slate-500">{{ formatNumber(top.total_vistas) }} vistas</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">❤️ {{ formatNumber(top.total_likes) }}</span>
                    <a
                      v-if="top.url_post"
                      :href="top.url_post"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="text-cyan-500 hover:text-cyan-400"
                      title="Ver publicación original"
                    >
                      <ExternalLink class="w-3.5 h-3.5" />
                    </a>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- ─────────────────────────────────────────────────────────────
           4. BLOQUE SECUNDARIO: GESTIÓN INSTITUCIONAL & PROTOCOLO
           ───────────────────────────────────────────────────────────── -->
      <div v-if="bloque_institucional" class="p-6 rounded-3xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-500">
              <Building2 class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-black text-slate-800 dark:text-slate-200">
                {{ bloque_institucional.titulo }}
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ bloque_institucional.subtitulo }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
              {{ bloque_institucional.share_porcentaje }}% de publicaciones
            </span>
            <button
              type="button"
              @click="toggleAcordeon('institucional')"
              class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 flex items-center gap-1 cursor-pointer"
            >
              <span>{{ acordeonAbierto['institucional'] ? 'Ocultar' : 'Ver detalle' }}</span>
              <component :is="acordeonAbierto['institucional'] ? ChevronUp : ChevronDown" class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 leading-relaxed flex items-start gap-2.5">
          <Info class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
          <p>
            <strong>Regla de Consultoría Política:</strong> Los actos administrativos, decretos y saludos protocolares no definen la elección.
            Se recomienda que este bloque no supere el <strong>20% del total de la comunicación</strong> de campaña para no diluir el mensaje central.
          </p>
        </div>

        <!-- Detalle desplegable de subejes institucionales -->
        <div v-show="acordeonAbierto['institucional']" class="pt-2">
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div
              v-for="sub in bloque_institucional.subejes"
              :key="sub.id"
              class="p-3.5 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between"
            >
              <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400" />
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ sub.nombre }}</span>
              </div>
              <span class="font-mono text-xs font-bold text-slate-500">{{ sub.posts_count }} posts</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ─────────────────────────────────────────────────────────────
           5. EVOLUCIÓN CRONOLÓGICA HISTÓRICA POR PILAR (TIME-SERIES)
           ───────────────────────────────────────────────────────────── -->
      <div v-if="evolucion_historica && evolucion_historica.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div>
          <h2 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <TrendingUp class="w-4 h-4 text-cyan-500" />
            <span>Evolución Histórica de los 3 Ejes Mes a Mes</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Seguimiento de la presencia y balance de cada pilar discursivo a lo largo del tiempo.
          </p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 font-mono uppercase">
                <th class="pb-2.5">Mes / Período</th>
                <th class="pb-2.5 text-center text-cyan-600 dark:text-cyan-400">🛡️ El Orden</th>
                <th class="pb-2.5 text-center text-emerald-600 dark:text-emerald-400">💼 El Futuro</th>
                <th class="pb-2.5 text-center text-amber-600 dark:text-amber-400">🤝 El Eje Humano</th>
                <th class="pb-2.5 text-center text-slate-400">🏛️ Institucional</th>
                <th class="pb-2.5 text-right">Total Posts</th>
                <th class="pb-2.5 text-right">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
              <tr
                v-for="mes in evolucion_historica"
                :key="mes.clave_mes"
                class="hover:bg-slate-50 dark:hover:bg-slate-950/50 transition-colors"
                :class="{ 'bg-cyan-500/5 dark:bg-cyan-500/10 font-bold': periodo_seleccionado === mes.clave_mes }"
              >
                <td class="py-3 pr-3 font-sans font-bold text-slate-900 dark:text-slate-100">
                  {{ mes.etiqueta }}
                </td>
                <td class="py-3 px-3 text-center text-cyan-600 dark:text-cyan-400 font-extrabold">
                  {{ mes.orden }}
                </td>
                <td class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400 font-extrabold">
                  {{ mes.futuro }}
                </td>
                <td class="py-3 px-3 text-center text-amber-600 dark:text-amber-400 font-extrabold">
                  {{ mes.humano }}
                </td>
                <td class="py-3 px-3 text-center text-slate-400">
                  {{ mes.institucional }}
                </td>
                <td class="py-3 px-3 text-right font-extrabold text-slate-900 dark:text-slate-100">
                  {{ mes.total }}
                </td>
                <td class="py-3 pl-3 text-right">
                  <button
                    type="button"
                    @click="cambiarPeriodo(mes.clave_mes)"
                    class="px-2.5 py-1 rounded-xl text-[10px] font-bold cursor-pointer transition-colors"
                    :class="periodo_seleccionado === mes.clave_mes ? 'bg-cyan-500 text-slate-950 font-black' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
                  >
                    {{ periodo_seleccionado === mes.clave_mes ? 'Activo' : 'Auditar Mes' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </WarRoomLayout>
</template>
