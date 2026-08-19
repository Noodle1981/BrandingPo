<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import SocialCard from '../../Components/SocialCard.vue';
import Badge from '../../Components/Badge.vue';
import {
  Radio,
  Search,
  Filter,
  Zap,
  DollarSign,
  TrendingUp,
  Sparkles,
  Layers,
  Flame,
  ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
  publicaciones: {
    type: Array,
    default: () => [],
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  ejes: {
    type: Array,
    default: () => [],
  },
  filtros: {
    type: Object,
    default: () => ({}),
  },
  stats_resumen: {
    type: Object,
    default: () => ({}),
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const selectedCandidato = ref(props.filtros.candidato_id || '');
const selectedPlataforma = ref(props.filtros.plataforma || '');
const selectedTipoPauta = ref(props.filtros.tipo_pauta || '');
const searchQuery = ref(props.filtros.search || '');

const plataformas = [
  { key: 'facebook', label: 'Facebook' },
  { key: 'instagram', label: 'Instagram' },
  { key: 'x_twitter', label: 'X (Twitter)' },
  { key: 'tiktok', label: 'TikTok' },
  { key: 'youtube', label: 'YouTube' },
  { key: 'linkedin', label: 'LinkedIn' },
];

const applyFilters = () => {
  router.get('/feed', {
    candidato_id: selectedCandidato.value || undefined,
    plataforma: selectedPlataforma.value || undefined,
    tipo_pauta: selectedTipoPauta.value || undefined,
    search: searchQuery.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const filterByPlatform = (platKey) => {
  selectedPlataforma.value = selectedPlataforma.value === platKey ? '' : platKey;
  applyFilters();
};

const filterByPauta = (pautaKey) => {
  selectedTipoPauta.value = selectedTipoPauta.value === pautaKey ? '' : pautaKey;
  applyFilters();
};

const clearFilters = () => {
  selectedCandidato.value = '';
  selectedPlataforma.value = '';
  selectedTipoPauta.value = '';
  searchQuery.value = '';
  applyFilters();
};

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
  <Head title="Feed Social Multired | Social Wall" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <Radio class="w-6 h-6 text-cyan-500 animate-pulse" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Feed Social Multired (Social Wall)
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Línea de tiempo unificada con publicaciones de candidatos, reacciones nativas y radar de pauta.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link
          v-if="canWrite"
          href="/fast-flow"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/20"
        >
          <Zap class="w-4 h-4" />
          <span>Carga Rápida Fast-Flow</span>
        </Link>
      </div>
    </div>

    <!-- Stats Mini Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Publicaciones en Feed:</span>
        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-base">
          {{ stats_resumen.total_posts || 0 }} posts
        </span>
      </div>

      <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Impacto / Vistas Totales:</span>
        <span class="font-bold font-mono text-cyan-600 dark:text-cyan-400 text-base">
          {{ formatNumber(stats_resumen.total_vistas) }}
        </span>
      </div>

      <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Pauta Publicitaria Total:</span>
        <span class="font-bold font-mono text-violet-600 dark:text-violet-400 text-base">
          {{ formatCurrency(stats_resumen.total_pauta_invertida) }}
        </span>
      </div>
    </div>

    <!-- Filter Bar & Search -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-xs space-y-3.5">
      <!-- Top Row: Search & Candidate Select -->
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Search Input -->
        <div class="relative flex-1 w-full">
          <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            @keyup.enter="applyFilters"
            type="text"
            placeholder="Buscar por palabras clave en publicaciones..."
            class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-2 focus:ring-cyan-500"
          />
        </div>

        <!-- Candidate Selector -->
        <div class="w-full sm:w-64">
          <select
            v-model="selectedCandidato"
            @change="applyFilters"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
          >
            <option value="">Todos los Candidatos</option>
            <option v-for="cand in candidatos" :key="cand.id" :value="cand.id">
              {{ cand.nombre_completo }} {{ cand.es_propio ? '(PROPIO)' : '' }}
            </option>
          </select>
        </div>
      </div>

      <!-- Bottom Row: Platform Pills & Pauta Switchers -->
      <div class="flex items-center justify-between flex-wrap gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
        <!-- Social Networks Pills -->
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="text-xs font-semibold text-slate-400 mr-1">Red:</span>
          <button
            v-for="plat in plataformas"
            :key="plat.key"
            type="button"
            @click="filterByPlatform(plat.key)"
            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer"
            :class="selectedPlataforma === plat.key ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-500 shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-slate-400'"
          >
            {{ plat.label }}
          </button>
        </div>

        <!-- Pauta Buttons -->
        <div class="flex items-center gap-1.5">
          <span class="text-xs font-semibold text-slate-400 mr-1">Tipo:</span>
          <button
            type="button"
            @click="filterByPauta('organico')"
            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all"
            :class="selectedTipoPauta === 'organico' ? 'bg-emerald-500 text-slate-950 font-bold border-emerald-500' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
          >
            🌱 Orgánico
          </button>
          <button
            type="button"
            @click="filterByPauta('pauta_paga')"
            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all"
            :class="selectedTipoPauta === 'pauta_paga' ? 'bg-violet-600 text-white font-bold border-violet-600' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
          >
            🎯 Pauta Paga
          </button>

          <button
            v-if="selectedCandidato || selectedPlataforma || selectedTipoPauta || searchQuery"
            type="button"
            @click="clearFilters"
            class="ml-2 text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:underline"
          >
            Limpiar
          </button>
        </div>
      </div>
    </div>

    <!-- Posts Feed Stream -->
    <div v-if="!publicaciones.length" class="text-center py-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
      <Radio class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-50" />
      <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No hay publicaciones con estos filtros</h3>
      <p class="text-xs text-slate-500 mt-1">Prueba cambiando la red social o el candidato seleccionado.</p>
    </div>

    <!-- Feed Grid / Social Cards Stream -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <SocialCard
        v-for="post in publicaciones"
        :key="post.id"
        :post="post"
        :can-write="canWrite"
      />
    </div>
  </WarRoomLayout>
</template>
