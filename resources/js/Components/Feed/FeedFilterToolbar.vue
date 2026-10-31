<script setup>
import {
  Filter,
  Fingerprint,
  X,
} from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';

const props = defineProps({
  candidatos: {
    type: Array,
    default: () => [],
  },
  aniosDisponibles: {
    type: Array,
    default: () => [],
  },
  mesesDisponibles: {
    type: Array,
    default: () => [],
  },
  groupedEjes: {
    type: Object,
    default: () => ({}),
  },
  selectedCandidato: {
    type: [String, Number],
    default: '',
  },
  selectedPlataforma: {
    type: String,
    default: '',
  },
  selectedTipoPauta: {
    type: String,
    default: '',
  },
  selectedEje: {
    type: [String, Number],
    default: '',
  },
  selectedAnio: {
    type: [String, Number],
    default: '',
  },
  selectedMes: {
    type: [String, Number],
    default: '',
  },
  selectedOrden: {
    type: String,
    default: 'recientes',
  },
  canWrite: {
    type: Boolean,
    default: false,
  },
  escaneandoHuellas: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  'update:selectedCandidato',
  'update:selectedPlataforma',
  'update:selectedTipoPauta',
  'update:selectedEje',
  'update:selectedAnio',
  'update:selectedMes',
  'update:selectedOrden',
  'applyFilters',
  'clearFilters',
  'filterByPlatform',
  'filterByPauta',
  'onAnioChange',
  'detectarHuellasPauta',
]);

const plataformas = [
  { key: 'facebook', label: 'Facebook', color: '#1877F2' },
  { key: 'instagram', label: 'Instagram', color: '#E4405F' },
  { key: 'threads', label: 'Threads', color: '#000000' },
  { key: 'tiktok', label: 'TikTok', color: '#00F2FE' },
  { key: 'youtube', label: 'YouTube', color: '#FF0000' },
  { key: 'x_twitter', label: 'X (Twitter)', color: '#64748b' },
  { key: 'linkedin', label: 'LinkedIn', color: '#0A66C2' },
];

const tiposPauta = [
  { key: 'organico', label: 'Orgánica Pura', color: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' },
  { key: 'con_huella', label: '🎯 Con Huella de Pauta', color: 'bg-pink-500/10 text-pink-500 border-pink-500/20' },
  { key: 'organico_impulsado', label: 'Orgánica Impulsada (Boost)', color: 'bg-amber-500/10 text-amber-500 border-amber-500/20' },
  { key: 'pauta_paga', label: 'Pauta Paga / Dark Post', color: 'bg-violet-500/10 text-violet-500 border-violet-500/20' },
];
</script>

<template>
  <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-bold font-mono uppercase text-slate-500 flex items-center gap-1.5">
          <Filter class="w-3.5 h-3.5 text-cyan-500" />
          <span>Filtros Operativos</span>
        </span>

        <!-- Botón Detectar Huellas de Pauta -->
        <button
          v-if="canWrite"
          type="button"
          @click="emit('detectarHuellasPauta')"
          :disabled="escaneandoHuellas"
          class="p-1.5 rounded-xl text-xs font-mono font-bold bg-pink-500/10 hover:bg-pink-500/20 text-pink-600 dark:text-pink-400 border border-pink-500/30 hover:border-pink-500/60 transition-all flex items-center justify-center cursor-pointer disabled:opacity-50 shadow-2xs"
          :title="escaneandoHuellas ? 'Escaneando...' : 'Escanear publicaciones orgánicas en busca de huellas publicitarias'"
        >
          <Fingerprint class="w-4 h-4" :class="escaneandoHuellas ? 'animate-spin' : ''" />
        </button>
      </div>

      <!-- Botón Limpiar Filtros -->
      <button
        v-if="selectedCandidato || selectedPlataforma || selectedTipoPauta || selectedEje || selectedAnio || selectedMes"
        type="button"
        @click="emit('clearFilters')"
        class="text-xs text-rose-500 hover:text-rose-400 font-semibold flex items-center gap-1 transition-colors cursor-pointer"
      >
        <X class="w-3.5 h-3.5" />
        <span>Limpiar Filtros</span>
      </button>
    </div>

    <!-- Filtros Rápidos por Red Social -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
      <button
        type="button"
        @click="emit('filterByPlatform', '')"
        class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer border shrink-0"
        :class="!selectedPlataforma
          ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 border-transparent shadow-xs'
          : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
      >
        🌐 Todas las Redes
      </button>
      <button
        v-for="plat in plataformas"
        :key="plat.key"
        type="button"
        @click="emit('filterByPlatform', plat.key)"
        class="px-3 py-1.5 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer border shrink-0 flex items-center gap-1.5"
        :class="selectedPlataforma === plat.key
          ? 'bg-cyan-500 text-slate-950 border-cyan-400 shadow-xs'
          : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-slate-300'"
      >
        <SocialPlatformIcon :platform="plat.key" size="xs" />
        <span>{{ plat.label }}</span>
      </button>
    </div>

    <!-- Filtros Rápidos por Tipo de Pauta -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
      <button
        type="button"
        @click="emit('filterByPauta', '')"
        class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold transition-all cursor-pointer border shrink-0"
        :class="!selectedTipoPauta
          ? 'bg-slate-800 text-slate-100 border-slate-600'
          : 'bg-slate-100 dark:bg-slate-800 text-slate-500 border-slate-200 dark:border-slate-700'"
      >
        Todo Tipo
      </button>
      <button
        v-for="pauta in tiposPauta"
        :key="pauta.key"
        type="button"
        @click="emit('filterByPauta', pauta.key)"
        class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold transition-all cursor-pointer border shrink-0"
        :class="selectedTipoPauta === pauta.key
          ? 'bg-cyan-500 text-slate-950 border-cyan-400'
          : pauta.color"
      >
        {{ pauta.label }}
      </button>
    </div>

    <!-- Selectores Secundarios -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" :class="candidatos.length > 1 ? 'lg:grid-cols-6' : 'lg:grid-cols-5'">
      <select
        v-if="candidatos.length > 1"
        :value="selectedCandidato"
        @change="$emit('update:selectedCandidato', $event.target.value); $emit('applyFilters')"
        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
      >
        <option value="">👤 Candidato (Todos)</option>
        <option v-for="c in candidatos" :key="c.id" :value="c.id">
          {{ c.nombre_completo }}
        </option>
      </select>

      <select
        :value="selectedEje"
        @change="$emit('update:selectedEje', $event.target.value); $emit('applyFilters')"
        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
      >
        <option value="">Eje Temático (Todos)</option>
        <optgroup v-for="(ejesInGroup, pilar) in groupedEjes" :key="pilar" :label="pilar">
          <option v-for="eje in ejesInGroup" :key="eje.id" :value="eje.id">
            • {{ eje.nombre }}
          </option>
        </optgroup>
      </select>

      <select
        :value="selectedTipoPauta"
        @change="$emit('update:selectedTipoPauta', $event.target.value); $emit('applyFilters')"
        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
      >
        <option value="">Orgánico & Pauta</option>
        <option value="con_huella">🎯 Sospechosos con Huella</option>
        <option value="organico">Orgánico Puro</option>
        <option value="organico_impulsado">Orgánico Impulsado</option>
        <option value="pauta_paga">Anuncio / Pauta</option>
      </select>

      <select
        :value="selectedAnio"
        @change="$emit('update:selectedAnio', $event.target.value); $emit('onAnioChange')"
        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
      >
        <option value="">Año (Todos)</option>
        <option
          v-for="a in aniosDisponibles"
          :key="a"
          :value="a"
        >
          {{ a }}
        </option>
      </select>

      <select
        :value="selectedMes"
        @change="$emit('update:selectedMes', $event.target.value); $emit('applyFilters')"
        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
      >
        <option value="">Mes (Todos)</option>
        <option
          v-for="m in mesesDisponibles"
          :key="m.numero"
          :value="m.numero"
        >
          {{ m.nombre }}
        </option>
      </select>

      <select
        :value="selectedOrden"
        @change="$emit('update:selectedOrden', $event.target.value); $emit('applyFilters')"
        class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
        title="Orden de las publicaciones"
      >
        <option value="recientes">🕒 Más Recientes</option>
        <option value="antiguos">⏳ Más Antiguos</option>
        <option value="interacciones">🔥 Más Interacciones</option>
      </select>
    </div>
  </div>
</template>
