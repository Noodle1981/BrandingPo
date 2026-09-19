<script setup>
import { ref, computed, watch } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import SocialCard from '../../Components/SocialCard.vue';
import SocialPlatformIcon from '../../Components/SocialPlatformIcon.vue';
import FeedFastFlowModal from '../../Components/Feed/FeedFastFlowModal.vue';
import { useFormatters } from '../../composables/useFormatters';
import {
  Radio,
  Search,
  Filter,
  Fingerprint,
  Plus,
  X,
  Calendar
} from '@lucide/vue';

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
  anios_disponibles: {
    type: Array,
    default: () => [],
  },
  meses_disponibles: {
    type: Array,
    default: () => [],
  },
  stats_resumen: {
    type: Object,
    default: () => ({}),
  }
});

const { formatNumber, formatCurrency } = useFormatters();

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const selectedPlataforma = ref(props.filtros.plataforma || '');
const selectedTipoPauta = ref(props.filtros.tipo_pauta || '');
const selectedEje = ref(props.filtros.eje_tematico_id || '');
const selectedAnio = ref(props.filtros.anio || '');
const selectedMes = ref(props.filtros.mes || '');
const selectedOrden = ref(props.filtros.orden || 'recientes');

const plataformas = [
  { key: 'facebook', label: 'Facebook', color: '#1877F2' },
  { key: 'instagram', label: 'Instagram', color: '#E4405F' },
  { key: 'threads', label: 'Threads', color: '#000000' },
  { key: 'tiktok', label: 'TikTok', color: '#00F2FE' },
  { key: 'youtube', label: 'YouTube', color: '#FF0000' },
  { key: 'x_twitter', label: 'X (Twitter)', color: '#64748b' },
  { key: 'linkedin', label: 'LinkedIn', color: '#0A66C2' },
];

const groupedEjes = computed(() => {
  const list = props.ejes || [];
  const groups = {};
  list.forEach((eje) => {
    if (!eje.pilar_principal) return;
    const pilar = eje.pilar_principal;
    if (!groups[pilar]) {
      groups[pilar] = [];
    }
    groups[pilar].push(eje);
  });
  return groups;
});

const tiposPauta = [
  { key: 'organico', label: 'Orgánica Pura', color: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' },
  { key: 'con_huella', label: '🎯 Con Huella de Pauta', color: 'bg-pink-500/10 text-pink-500 border-pink-500/20' },
  { key: 'organico_impulsado', label: 'Orgánica Impulsada (Boost)', color: 'bg-amber-500/10 text-amber-500 border-amber-500/20' },
  { key: 'pauta_paga', label: 'Pauta Paga / Dark Post', color: 'bg-violet-500/10 text-violet-500 border-violet-500/20' },
];

const escaneandoHuellas = ref(false);
const huellaScanResultado = ref(null);

const detectarHuellasPauta = async () => {
  if (escaneandoHuellas.value) return;
  escaneandoHuellas.value = true;
  huellaScanResultado.value = null;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const resp = await fetch('/publicaciones/detectar-huellas-pauta', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        filtro: props.filtros.filtro || undefined,
        candidato_id: selectedCandidato.value || undefined,
        plataforma: selectedPlataforma.value || undefined,
        anio: selectedAnio.value || undefined,
        mes: selectedMes.value || undefined,
      }),
    });

    const data = await resp.json();
    if (resp.ok && data.success) {
      huellaScanResultado.value = data;
      if (data.total_detectadas > 0) {
        selectedTipoPauta.value = 'con_huella';
        applyFilters();
      }
    }
  } catch (e) {
    console.error('Error al detectar huellas de pauta:', e);
  } finally {
    escaneandoHuellas.value = false;
  }
};

const applyFilters = () => {
  router.get('/feed', {
    filtro: props.filtros.filtro || undefined,
    candidato_id: props.filtros.candidato_id || undefined,
    plataforma: selectedPlataforma.value || undefined,
    tipo_pauta: selectedTipoPauta.value || undefined,
    eje_tematico_id: selectedEje.value || undefined,
    anio: selectedAnio.value || undefined,
    mes: selectedMes.value || undefined,
    orden: selectedOrden.value !== 'recientes' ? selectedOrden.value : undefined,
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

const onAnioChange = () => {
  selectedMes.value = '';
  applyFilters();
};

watch(() => props.meses_disponibles, (newMeses) => {
  if (selectedMes.value && Array.isArray(newMeses) && !newMeses.some(m => m.numero === selectedMes.value)) {
    selectedMes.value = '';
  }
});

watch(() => props.filtros, (newFiltros) => {
  selectedAnio.value = newFiltros.anio || '';
  selectedMes.value = newFiltros.mes || '';
  selectedPlataforma.value = newFiltros.plataforma || '';
  selectedTipoPauta.value = newFiltros.tipo_pauta || '';
  selectedEje.value = newFiltros.eje_tematico_id || '';
  selectedOrden.value = newFiltros.orden || 'recientes';
}, { deep: true });

const clearFilters = () => {
  selectedPlataforma.value = '';
  selectedTipoPauta.value = '';
  selectedEje.value = '';
  selectedAnio.value = '';
  selectedMes.value = '';
  selectedOrden.value = 'recientes';
  applyFilters();
};

// --- MODAL DE CREACIÓN DE PUBLICACIÓN (FAST-FLOW INTEGRADO) ---
const showCreateModal = ref(false);

const openCreateModal = () => {
  showCreateModal.value = true;
};
</script>

<template>
  <Head :title="filtros.filtro === 'propio' ? 'Muro Social — Mi Campaña' : 'Feed Social Multired | Social Wall'" />

  <WarRoomLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-16">

      <!-- Header Principal -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2.5 flex-wrap">
            <Radio class="w-6 h-6 text-cyan-500 animate-pulse" />
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              {{ filtros.filtro === 'propio' ? 'Muro de Publicaciones — Mi Campaña' : (filtros.filtro === 'oposicion' ? 'Muro de Publicaciones — Rivales' : 'Feed Social Multired (Social Wall)') }}
            </h1>
            <span
              v-if="filtros.filtro"
              class="text-[10px] uppercase font-mono font-bold px-2.5 py-0.5 rounded-full border"
              :class="filtros.filtro === 'propio' ? 'bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border-cyan-500/40' : 'bg-violet-500/20 text-violet-600 dark:text-violet-400 border-violet-500/40'"
            >
              {{ filtros.filtro === 'propio' ? '🎖️ CANDIDATO OFICIAL' : '⚔️ RIVALES' }}
            </span>
          </div>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
            {{ filtros.filtro === 'propio' ? 'Línea de tiempo cronológica con todas las publicaciones, reels, reacciones nativas y pauta publicitaria.' : 'Muro unificado de auditoría de publicaciones de todos los candidatos.' }}
          </p>
        </div>

        <!-- Botón de Carga de Publicación -->
        <div class="flex items-center gap-2">
          <button
            v-if="canWrite"
            type="button"
            @click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/20 transition-all hover:scale-102 cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>+ Cargar Publicación</span>
          </button>
        </div>
      </div>

      <!-- Stats Mini Bar -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
          <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-mono">Publicaciones en Feed:</span>
          <span class="font-mono font-extrabold text-base text-slate-900 dark:text-slate-100">{{ stats_resumen.total_posts || 0 }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
          <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-mono">Visualizaciones Estimadas:</span>
          <span class="font-mono font-extrabold text-base text-cyan-600 dark:text-cyan-400">{{ formatNumber(stats_resumen.total_vistas) }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-xs">
          <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 font-mono">Pauta Invertida Total:</span>
          <span class="font-mono font-extrabold text-base text-violet-600 dark:text-violet-400">{{ formatCurrency(stats_resumen.total_pauta_invertida) }}</span>
        </div>
      </div>

      <!-- Barra de Filtros Avanzados -->
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
              @click="detectarHuellasPauta"
              :disabled="escaneandoHuellas"
              class="p-1.5 rounded-xl text-xs font-mono font-bold bg-pink-500/10 hover:bg-pink-500/20 text-pink-600 dark:text-pink-400 border border-pink-500/30 hover:border-pink-500/60 transition-all flex items-center justify-center cursor-pointer disabled:opacity-50 shadow-2xs"
              :title="escaneandoHuellas ? 'Escaneando...' : 'Escanear publicaciones orgánicas en busca de huellas publicitarias'"
            >
              <Fingerprint class="w-4 h-4" :class="escaneandoHuellas ? 'animate-spin' : ''" />
            </button>
          </div>

          <!-- Botón Limpiar Filtros -->
          <button
            v-if="selectedPlataforma || selectedTipoPauta || selectedEje || selectedAnio || selectedMes"
            type="button"
            @click="clearFilters"
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
            @click="filterByPlatform('')"
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
            @click="filterByPlatform(plat.key)"
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
            @click="filterByPauta('')"
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
            @click="filterByPauta(pauta.key)"
            class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold transition-all cursor-pointer border shrink-0"
            :class="selectedTipoPauta === pauta.key
              ? 'bg-cyan-500 text-slate-950 border-cyan-400'
              : pauta.color"
          >
            {{ pauta.label }}
          </button>
        </div>

        <!-- Selectores Secundarios en una sola línea (Eje, Pauta, Año, Mes, Orden) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
          <!-- Selector Eje Temático Agrupado por Pilar Estratégico -->
          <select
            v-model="selectedEje"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
          >
            <option value="">Eje Temático (Todos)</option>
            <optgroup v-for="(ejesInGroup, pilar) in groupedEjes" :key="pilar" :label="pilar">
              <option v-for="eje in ejesInGroup" :key="eje.id" :value="eje.id">
                • {{ eje.nombre }}
              </option>
            </optgroup>
          </select>

          <!-- Selector de Tipo de Pauta -->
          <select
            v-model="selectedTipoPauta"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
          >
            <option value="">Orgánico & Pauta</option>
            <option value="con_huella">🎯 Sospechosos con Huella</option>
            <option value="organico">Orgánico Puro</option>
            <option value="organico_impulsado">Orgánico Impulsado</option>
            <option value="pauta_paga">Anuncio / Pauta</option>
          </select>

          <!-- Selector de Año -->
          <select
            v-model="selectedAnio"
            @change="onAnioChange"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
          >
            <option value="">Año (Todos)</option>
            <option
              v-for="a in anios_disponibles"
              :key="a"
              :value="a"
            >
              {{ a }}
            </option>
          </select>

          <!-- Selector de Mes -->
          <select
            v-model="selectedMes"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
          >
            <option value="">Mes (Todos)</option>
            <option
              v-for="m in meses_disponibles"
              :key="m.numero"
              :value="m.numero"
            >
              {{ m.nombre }}
            </option>
          </select>

          <!-- Selector de Orden Cronológico -->
          <select
            v-model="selectedOrden"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 w-full"
            title="Orden de las publicaciones"
          >
            <option value="recientes">🕒 Más Recientes</option>
            <option value="antiguos">⏳ Más Antiguos</option>
            <option value="interacciones">🔥 Más Interacciones</option>
          </select>
        </div>
      </div>

      <!-- Banner de Resultado de Detección de Huellas de Pauta -->
      <div
        v-if="huellaScanResultado"
        class="p-4 rounded-3xl border flex items-center justify-between gap-4 text-xs shadow-md transition-all animate-fadeIn"
        :class="huellaScanResultado.total_detectadas > 0
          ? 'bg-pink-500/10 border-pink-500/30 text-pink-900 dark:text-pink-200'
          : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300'"
      >
        <div class="flex items-center gap-2.5">
          <div
            class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
            :class="huellaScanResultado.total_detectadas > 0 ? 'bg-pink-500/20 text-pink-600 dark:text-pink-400' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'"
          >
            <Fingerprint class="w-4 h-4" />
          </div>
          <div>
            <p class="font-bold text-xs">{{ huellaScanResultado.mensaje }}</p>
            <p v-if="huellaScanResultado.total_detectadas > 0" class="text-[11px] opacity-80 mt-0.5">
              El feed se ha filtrado automáticamente para mostrar únicamente las publicaciones sospechosas. Puedes convertirlas a Booster con 1 clic en cada tarjeta.
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="huellaScanResultado = null"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer font-bold"
          title="Cerrar aviso"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Indicador de Orden Cronológico y Total de Posts -->
      <div v-if="publicaciones.length > 0" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1 text-xs font-mono text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300">
          <Calendar class="w-3.5 h-3.5 text-cyan-500" />
          <span>Línea de tiempo cronológica por Fecha de Origen (Más recientes arriba)</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-2.5 py-0.5 rounded-lg text-[11px] font-bold border border-slate-200 dark:border-slate-700">
            {{ publicaciones.length }} {{ publicaciones.length === 1 ? 'publicación' : 'publicaciones' }}
          </span>
        </div>
      </div>

      <!-- Muro de Publicaciones (Social Cards) -->
      <div v-if="publicaciones.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
        <SocialCard
          v-for="post in publicaciones"
          :key="post.id"
          :post="post"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
        <Radio class="w-12 h-12 text-slate-300 dark:text-slate-700 mx-auto" />
        <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-200">
          No se encontraron publicaciones con estos filtros
        </h3>
        <p class="text-xs text-slate-400 max-w-md mx-auto">
          Prueba cambiando los criterios de búsqueda o presiona el botón para cargar una nueva publicación.
        </p>
        <button
          v-if="canWrite"
          type="button"
          @click="openCreateModal"
          class="px-4 py-2 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs shadow-sm hover:scale-102 transition-all cursor-pointer inline-flex items-center gap-1.5"
        >
          <Plus class="w-4 h-4" />
          <span>Cargar primera publicación</span>
        </button>
      </div>

    </div>

    <!-- Modal Fast-Flow Modularizado -->
    <FeedFastFlowModal
      v-model="showCreateModal"
      :candidatos="candidatos"
      :grouped-ejes="groupedEjes"
      :publicaciones="publicaciones"
      :initial-candidato-id="selectedCandidato || filtros.candidato_id"
      :initial-plataforma="selectedPlataforma || filtros.plataforma"
      :initial-eje-id="selectedEje"
      :initial-tipo-pauta="selectedTipoPauta"
    />

  </WarRoomLayout>
</template>
