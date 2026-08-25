<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import SocialCard from '../../Components/SocialCard.vue';
import Badge from '../../Components/Badge.vue';
import SocialPlatformIcon from '../../Components/SocialPlatformIcon.vue';
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
  ArrowRight,
  Plus,
  X,
  RefreshCw,
  ExternalLink,
  Calendar,
  Smile,
  ShieldAlert,
  Star,
  CheckCircle2,
  AlertCircle
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
const selectedEje = ref(props.filtros.eje_tematico_id || '');
const selectedMes = ref(props.filtros.mes || '');
const selectedRangoAprobacion = ref(props.filtros.rango_aprobacion || '');
const searchQuery = ref(props.filtros.search || '');

const plataformas = [
  { key: 'facebook', label: 'Facebook', color: '#1877F2' },
  { key: 'instagram', label: 'Instagram', color: '#E4405F' },
  { key: 'tiktok', label: 'TikTok', color: '#00F2FE' },
  { key: 'youtube', label: 'YouTube', color: '#FF0000' },
  { key: 'x_twitter', label: 'X (Twitter)', color: '#64748b' },
  { key: 'linkedin', label: 'LinkedIn', color: '#0A66C2' },
];

const tiposPauta = [
  { key: 'organico', label: 'Orgánica Pura', color: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' },
  { key: 'organico_impulsado', label: 'Orgánica Impulsada (Boost)', color: 'bg-amber-500/10 text-amber-500 border-amber-500/20' },
  { key: 'pauta_paga', label: 'Pauta Paga / Dark Post', color: 'bg-violet-500/10 text-violet-500 border-violet-500/20' },
];

const applyFilters = () => {
  router.get('/feed', {
    filtro: props.filtros.filtro || undefined,
    candidato_id: selectedCandidato.value || undefined,
    plataforma: selectedPlataforma.value || undefined,
    tipo_pauta: selectedTipoPauta.value || undefined,
    eje_tematico_id: selectedEje.value || undefined,
    mes: selectedMes.value || undefined,
    rango_aprobacion: selectedRangoAprobacion.value || undefined,
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
  selectedEje.value = '';
  selectedMes.value = '';
  selectedRangoAprobacion.value = '';
  searchQuery.value = '';
  applyFilters();
};

// --- MODAL DE CREACIÓN DE PUBLICACIÓN (FAST-FLOW INTEGRADO) ---
const showCreateModal = ref(false);
const isScraping = ref(false);
const scrapeSuccessMsg = ref('');
const scrapeErrorMsg = ref('');

const createForm = useForm({
  candidato_id: props.filtros.candidato_id || (props.candidatos[0]?.id ?? ''),
  perfil_social_id: '',
  plataforma: 'instagram',
  eje_tematico_id: '',
  url_post: '',
  tipo_formato: 'Reel',
  tipo_pauta: 'organico',
  monto_invertido_pauta: 0,
  fecha_publicacion: new Date().toISOString().slice(0, 16),
  vistas_organicas: 0,
  vistas_pagadas: 0,
  total_likes: 0,
  total_comentarios: 0,
  total_compartidos: 0,
  total_guardados: 0,
  contenido_resumen: '',
  comentario_destacado: '',
  figura_acompanante: '',
  reacciones_detalladas: {
    like: 0,
    love: 0,
    care: 0,
    haha: 0,
    wow: 0,
    sad: 0,
    angry: 0,
  },
});

// Perfiles del candidato seleccionado en el modal
const perfilesCandidatoSeleccionado = computed(() => {
  const c = props.candidatos.find(cand => cand.id === Number(createForm.candidato_id));
  return c?.perfiles_sociales || c?.perfilesSociales || [];
});

const openCreateModal = () => {
  // 1. Preseleccionar candidato según filtros activos
  if (selectedCandidato.value) {
    createForm.candidato_id = selectedCandidato.value;
  } else if (props.filtros.candidato_id) {
    createForm.candidato_id = props.filtros.candidato_id;
  } else if (props.filtros.filtro === 'propio') {
    const propio = props.candidatos.find(c => c.es_propio);
    createForm.candidato_id = propio ? propio.id : (props.candidatos[0]?.id ?? '');
  } else if (props.filtros.filtro === 'oposicion') {
    const opositor = props.candidatos.find(c => !c.es_propio);
    createForm.candidato_id = opositor ? opositor.id : (props.candidatos[0]?.id ?? '');
  } else if (props.candidatos.length > 0) {
    const propio = props.candidatos.find(c => c.es_propio);
    createForm.candidato_id = propio ? propio.id : props.candidatos[0].id;
  }

  // 2. Preseleccionar perfil social según red social filtrada
  const perfiles = perfilesCandidatoSeleccionado.value;
  const activePlatform = selectedPlataforma.value || props.filtros.plataforma;

  if (activePlatform) {
    const matchingPerfil = perfiles.find(p => p.plataforma.toLowerCase() === activePlatform.toLowerCase());
    if (matchingPerfil) {
      createForm.perfil_social_id = matchingPerfil.id;
      createForm.plataforma = matchingPerfil.plataforma;
    } else if (perfiles.length > 0) {
      createForm.perfil_social_id = perfiles[0].id;
      createForm.plataforma = perfiles[0].plataforma;
    }
  } else if (perfiles.length > 0) {
    createForm.perfil_social_id = perfiles[0].id;
    createForm.plataforma = perfiles[0].plataforma;
  }

  // 3. Preseleccionar eje temático si está filtrado
  if (selectedEje.value) {
    createForm.eje_tematico_id = selectedEje.value;
  }

  // 4. Preseleccionar tipo de pauta si está filtrado
  if (selectedTipoPauta.value) {
    createForm.tipo_pauta = selectedTipoPauta.value;
  }

  scrapeSuccessMsg.value = '';
  scrapeErrorMsg.value = '';
  showCreateModal.value = true;
};

const onCandidatoChange = () => {
  const perfiles = perfilesCandidatoSeleccionado.value;
  const activePlatform = selectedPlataforma.value || props.filtros.plataforma;
  
  if (activePlatform) {
    const matchingPerfil = perfiles.find(p => p.plataforma.toLowerCase() === activePlatform.toLowerCase());
    if (matchingPerfil) {
      createForm.perfil_social_id = matchingPerfil.id;
      createForm.plataforma = matchingPerfil.plataforma;
      return;
    }
  }

  if (perfiles.length > 0) {
    createForm.perfil_social_id = perfiles[0].id;
    createForm.plataforma = perfiles[0].plataforma;
  } else {
    createForm.perfil_social_id = '';
  }
};

const onPerfilChange = () => {
  const perfiles = perfilesCandidatoSeleccionado.value;
  const p = perfiles.find(item => item.id === Number(createForm.perfil_social_id));
  if (p) {
    createForm.plataforma = p.plataforma;
  }
};

// Autocompletar con 1 Clic desde URL
const autocompletarScrape = async () => {
  if (!createForm.url_post) {
    scrapeErrorMsg.value = 'Ingresa primero la URL de la publicación de Instagram, TikTok o Facebook.';
    return;
  }

  isScraping.value = true;
  scrapeSuccessMsg.value = '';
  scrapeErrorMsg.value = '';

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch('/publicaciones/scrape-post', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        url_post: createForm.url_post,
        plataforma: createForm.plataforma,
      }),
    });

    const data = await response.json();

    if (response.ok && data.success) {
      if (data.data.tipo_formato) createForm.tipo_formato = data.data.tipo_formato;
      if (data.data.total_likes) createForm.total_likes = data.data.total_likes;
      if (data.data.total_comentarios) createForm.total_comentarios = data.data.total_comentarios;
      if (data.data.vistas_organicas) createForm.vistas_organicas = data.data.vistas_organicas;
      if (data.data.contenido_resumen) createForm.contenido_resumen = data.data.contenido_resumen;
      if (data.data.fecha_publicacion) createForm.fecha_publicacion = data.data.fecha_publicacion.slice(0, 16);

      // Distribuir likes en reacciones
      const lk = data.data.total_likes || 0;
      createForm.reacciones_detalladas.like = Math.round(lk * 0.7);
      createForm.reacciones_detalladas.love = Math.round(lk * 0.25);
      createForm.reacciones_detalladas.haha = Math.round(lk * 0.05);

      scrapeSuccessMsg.value = '✅ ¡Datos y métricas extraídos exitosamente!';
    } else {
      scrapeErrorMsg.value = data.error || 'No se pudieron extraer los datos automáticamente. Puedes completarlos a mano.';
    }
  } catch (err) {
    scrapeErrorMsg.value = 'Error al consultar el lector de redes. Completa los datos a mano.';
  } finally {
    isScraping.value = false;
  }
};

// Sincronizar suma de emojis con total_likes
watch(() => createForm.reacciones_detalladas, (reacs) => {
  const sum = Object.values(reacs).reduce((acc, val) => acc + (Number(val) || 0), 0);
  if (sum > 0) {
    createForm.total_likes = sum;
  }
}, { deep: true });

// Cálculo del Índice de Aprobación Neta
const netApproval = computed(() => {
  const pos = (Number(createForm.reacciones_detalladas.like) || 0) +
              (Number(createForm.reacciones_detalladas.love) || 0) +
              (Number(createForm.reacciones_detalladas.care) || 0);
  const neg = (Number(createForm.reacciones_detalladas.sad) || 0) +
              (Number(createForm.reacciones_detalladas.angry) || 0);
  const tot = pos + neg + (Number(createForm.reacciones_detalladas.haha) || 0) + (Number(createForm.reacciones_detalladas.wow) || 0);
  if (tot === 0) return 100;
  return Math.round(((pos - neg) / tot) * 100);
});

// Termómetro de Humor Social (1 a 5 estrellas)
const calculatedSocialThermometer = computed(() => {
  const r = createForm.reacciones_detalladas;
  const pos = (Number(r.like) || 0) + (Number(r.love) || 0) + (Number(r.care) || 0);
  const neg = (Number(r.sad) || 0) + (Number(r.angry) || 0) * 2;
  const tot = pos + neg + (Number(r.haha) || 0) + (Number(r.wow) || 0);
  if (tot === 0) return 4;
  const ratio = (pos - neg) / tot;
  if (ratio > 0.6) return 5;
  if (ratio > 0.2) return 4;
  if (ratio > -0.2) return 3;
  if (ratio > -0.6) return 2;
  return 1;
});

const submitCreatePost = () => {
  createForm.post('/publicaciones', {
    preserveScroll: true,
    onSuccess: () => {
      showCreateModal.value = false;
      createForm.reset();
    }
  });
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
          <span class="text-xs font-bold font-mono uppercase text-slate-500 flex items-center gap-1.5">
            <Filter class="w-3.5 h-3.5 text-cyan-500" />
            <span>Filtros Operativos</span>
          </span>

          <button
            v-if="selectedPlataforma || selectedTipoPauta || selectedCandidato || selectedEje || selectedMes || searchQuery"
            @click="clearFilters"
            class="text-xs font-mono font-bold text-rose-500 hover:text-rose-400 cursor-pointer"
          >
            Limpiar Filtros (✕)
          </button>
        </div>

        <!-- Fila 1: Píldoras de Red Social con Logos Oficiales -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1">
          <button
            type="button"
            @click="filterByPlatform('')"
            class="px-3 py-2 rounded-2xl text-xs font-mono font-bold border transition-all cursor-pointer whitespace-nowrap"
            :class="!selectedPlataforma ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 border-transparent shadow-xs' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:border-cyan-500/40'"
          >
            Todas las Redes
          </button>
          <button
            v-for="plat in plataformas"
            :key="plat.key"
            type="button"
            @click="filterByPlatform(plat.key)"
            class="p-2 rounded-2xl border transition-all cursor-pointer flex items-center justify-center hover:scale-105 shadow-2xs"
            :class="selectedPlataforma === plat.key ? 'bg-cyan-500/20 border-cyan-500 ring-2 ring-cyan-500/30' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700'"
            :title="`Filtrar por ${plat.label}`"
          >
            <SocialPlatformIcon :platform="plat.key" size="sm" />
          </button>
        </div>

        <!-- Fila 2: Selectores de Eje, Tipo de Pauta, Aprobación %, Mes y Buscador -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
          <!-- Buscador -->
          <div class="relative">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              @input="applyFilters"
              type="text"
              placeholder="Buscar en texto del post..."
              class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <!-- Selector de Eje Temático -->
          <select
            v-model="selectedEje"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          >
            <option value="">Todos los Ejes</option>
            <option v-for="eje in ejes" :key="eje.id" :value="eje.id">
              {{ eje.nombre }}
            </option>
          </select>

          <!-- Selector de Tipo de Pauta -->
          <select
            v-model="selectedTipoPauta"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          >
            <option value="">Orgánico & Pauta</option>
            <option value="organico">Orgánico Puro</option>
            <option value="organico_impulsado">Orgánico Impulsado (Boost)</option>
            <option value="pauta_paga">Anuncio Directo / Pauta</option>
          </select>

          <!-- Selector de Aprobación Neta (%) -->
          <select
            v-model="selectedRangoAprobacion"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          >
            <option value="">Aprobación (%) — Todas</option>
            <option value="alta">🟢 Alta (≥ 80%)</option>
            <option value="media">🔵 Media (50% - 79%)</option>
            <option value="baja">🔴 Baja / Alerta (&lt; 50%)</option>
          </select>

          <!-- Selector de Mes -->
          <input
            v-model="selectedMes"
            @change="applyFilters"
            type="month"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          />
        </div>
      </div>

      <!-- Muro de Publicaciones (Social Cards) -->
      <div v-if="publicaciones.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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

    <!-- MODAL DE CARGA INTELIGENTE DE PUBLICACIÓN (+ AUTOCOMPLETAR 1 CLIC) -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-fade-in overflow-y-auto"
    >
      <div class="relative w-full max-w-2xl my-8 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <div class="flex items-center gap-2">
            <Sparkles class="w-5 h-5 text-cyan-500" />
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
              Cargar Nueva Publicación en el Feed
            </h3>
          </div>
          <button
            @click="showCreateModal = false"
            class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitCreatePost" class="space-y-4 text-xs font-sans">

          <!-- Fila 1: Candidato y Perfil Social -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Candidato:</label>
              <select
                v-model="createForm.candidato_id"
                @change="onCandidatoChange"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-bold"
              >
                <option v-for="cand in candidatos" :key="cand.id" :value="cand.id">
                  {{ cand.es_propio ? '⭐ ' : '' }}{{ cand.nombre_completo }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Canal Social / Red:</label>
              <select
                v-model="createForm.perfil_social_id"
                @change="onPerfilChange"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-bold"
              >
                <option v-for="p in perfilesCandidatoSeleccionado" :key="p.id" :value="p.id">
                  {{ p.plataforma.toUpperCase() }} ({{ p.handle_usuario }})
                </option>
              </select>
            </div>
          </div>

          <!-- Fila 2: URL del Post + Botón Autocompletar (1 Clic) -->
          <div class="p-4 rounded-2xl bg-cyan-500/5 dark:bg-cyan-500/10 border border-cyan-500/20 space-y-2">
            <label class="block font-bold text-slate-800 dark:text-slate-200">
              🔗 Enlace del Post (Instagram Reel, TikTok Video o Post de Facebook):
            </label>
            <div class="flex items-center gap-2">
              <input
                v-model="createForm.url_post"
                type="url"
                placeholder="https://www.instagram.com/reel/C.../"
                class="flex-1 px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs focus:ring-2 focus:ring-cyan-500"
              />
              <button
                type="button"
                @click="autocompletarScrape"
                :disabled="isScraping"
                class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-xs hover:scale-102 disabled:opacity-50 cursor-pointer shrink-0"
              >
                <RefreshCw class="w-3.5 h-3.5" :class="isScraping ? 'animate-spin' : ''" />
                <span>{{ isScraping ? 'Leyendo...' : '⚡ Autocompletar (1 Clic)' }}</span>
              </button>
            </div>

            <div v-if="scrapeSuccessMsg" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
              <CheckCircle2 class="w-3.5 h-3.5" />
              <span>{{ scrapeSuccessMsg }}</span>
            </div>
            <div v-if="scrapeErrorMsg" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
              <AlertCircle class="w-3.5 h-3.5" />
              <span>{{ scrapeErrorMsg }}</span>
            </div>
          </div>

          <!-- Fila 3: Eje Temático, Formato y Fecha -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Eje Temático:</label>
              <select
                v-model="createForm.eje_tematico_id"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
              >
                <option value="">Seleccionar Eje...</option>
                <option v-for="e in ejes" :key="e.id" :value="e.id">
                  {{ e.nombre }}
                </option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Formato:</label>
              <select
                v-model="createForm.tipo_formato"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
              >
                <option value="Reel">Reel / Short / TikTok</option>
                <option value="Video">Video Largo</option>
                <option value="Foto">Foto Única</option>
                <option value="Carrusel">Carrusel / Álbum</option>
                <option value="Historia">Historia / Story</option>
                <option value="Tweet">Post Texto / Tweet</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Fecha & Hora:</label>
              <input
                v-model="createForm.fecha_publicacion"
                type="datetime-local"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
              />
            </div>
          </div>

          <!-- Fila 4: Tipo de Pauta y Monto Invertido -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tipo de Pauta:</label>
              <select
                v-model="createForm.tipo_pauta"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800"
              >
                <option value="organico">Orgánica Pura ($0)</option>
                <option value="organico_impulsado">Orgánica Impulsada (Boost)</option>
                <option value="pauta_paga">Anuncio Directo / Dark Post</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Monto Invertido (ARS):</label>
              <input
                v-model.number="createForm.monto_invertido_pauta"
                type="number"
                min="0"
                step="500"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-mono"
              />
            </div>
          </div>

          <!-- Fila 5: Resumen del Contenido -->
          <div>
            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Texto / Copy del Post:</label>
            <textarea
              v-model="createForm.contenido_resumen"
              rows="2"
              placeholder="Escribe o pega aquí el resumen o texto del post..."
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
            ></textarea>
          </div>

          <!-- Fila 6: Cuantificación Emoji por Emoji -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-slate-700 dark:text-slate-300">Desglose de Emojis / Reacciones:</span>
              <span class="font-mono text-cyan-500 font-bold">Aprobación Neta: {{ netApproval }}%</span>
            </div>

            <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 text-center font-mono">
              <div>
                <span class="text-sm">👍</span>
                <input v-model.number="createForm.reacciones_detalladas.like" type="number" min="0" class="w-full mt-1 p-1 text-center text-xs rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
              </div>
              <div>
                <span class="text-sm">❤️</span>
                <input v-model.number="createForm.reacciones_detalladas.love" type="number" min="0" class="w-full mt-1 p-1 text-center text-xs rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
              </div>
              <div>
                <span class="text-sm">🥰</span>
                <input v-model.number="createForm.reacciones_detalladas.care" type="number" min="0" class="w-full mt-1 p-1 text-center text-xs rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
              </div>
              <div>
                <span class="text-sm">😂</span>
                <input v-model.number="createForm.reacciones_detalladas.haha" type="number" min="0" class="w-full mt-1 p-1 text-center text-xs rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
              </div>
              <div>
                <span class="text-sm">😮</span>
                <input v-model.number="createForm.reacciones_detalladas.wow" type="number" min="0" class="w-full mt-1 p-1 text-center text-xs rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
              </div>
              <div>
                <span class="text-sm">😢</span>
                <input v-model.number="createForm.reacciones_detalladas.sad" type="number" min="0" class="w-full mt-1 p-1 text-center text-xs rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
              </div>
              <div>
                <span class="text-sm">😡</span>
                <input v-model.number="createForm.reacciones_detalladas.angry" type="number" min="0" class="w-full mt-1 p-1 text-center text-xs rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
              </div>
            </div>
          </div>

          <!-- Botones de Acción Modal -->
          <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="createForm.processing"
              class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold shadow-md shadow-cyan-500/20 disabled:opacity-50 cursor-pointer"
            >
              {{ createForm.processing ? 'Guardando...' : 'Publicar en Feed' }}
            </button>
          </div>

        </form>

      </div>
    </div>

  </WarRoomLayout>
</template>
