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
  AlertCircle,
  Heart,
  Repeat,
  Send,
  Bookmark,
  MessageCircle,
  Share2,
  Eye
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

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const selectedCandidato = ref(props.filtros.candidato_id || '');
const selectedPlataforma = ref(props.filtros.plataforma || '');
const selectedTipoPauta = ref(props.filtros.tipo_pauta || '');
const selectedEje = ref(props.filtros.eje_tematico_id || '');
const selectedAnio = ref(props.filtros.anio || '');
const selectedMes = ref(props.filtros.mes || '');
const selectedRangoAprobacion = ref(props.filtros.rango_aprobacion || '');
const selectedOrden = ref(props.filtros.orden || 'recientes');
const searchQuery = ref(props.filtros.search || '');

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
    anio: selectedAnio.value || undefined,
    mes: selectedMes.value || undefined,
    rango_aprobacion: selectedRangoAprobacion.value || undefined,
    orden: selectedOrden.value !== 'recientes' ? selectedOrden.value : undefined,
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

const onAnioChange = () => {
  // Al cambiar de año, si el mes seleccionado no existe en el nuevo año, se limpiará al recibir los nuevos meses
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
  selectedCandidato.value = newFiltros.candidato_id || '';
  selectedPlataforma.value = newFiltros.plataforma || '';
  selectedTipoPauta.value = newFiltros.tipo_pauta || '';
  selectedEje.value = newFiltros.eje_tematico_id || '';
  selectedRangoAprobacion.value = newFiltros.rango_aprobacion || '';
  selectedOrden.value = newFiltros.orden || 'recientes';
  searchQuery.value = newFiltros.search || '';
}, { deep: true });

const clearFilters = () => {
  selectedCandidato.value = '';
  selectedPlataforma.value = '';
  selectedTipoPauta.value = '';
  selectedEje.value = '';
  selectedAnio.value = '';
  selectedMes.value = '';
  selectedRangoAprobacion.value = '';
  selectedOrden.value = 'recientes';
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
  media_url: '',
  tipo_formato: 'Reel',
  tipo_pauta: 'organico',
  monto_invertido_pauta: 0,
  fecha_publicacion: new Date().toISOString().slice(0, 16),
  vistas_organicas: 0,
  vistas_pagadas: 0,
  total_likes: 0,
  total_comentarios: 0,
  total_compartidos: 0,
  total_republicados: 0,
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
      if (data.data.total_likes !== undefined) createForm.total_likes = data.data.total_likes;
      if (data.data.total_comentarios !== undefined) createForm.total_comentarios = data.data.total_comentarios;
      if (data.data.total_vistas || data.data.vistas_organicas) {
        createForm.vistas_organicas = data.data.total_vistas || data.data.vistas_organicas;
      }
      if (data.data.contenido_resumen) createForm.contenido_resumen = data.data.contenido_resumen;
      if (data.data.fecha_publicacion) createForm.fecha_publicacion = data.data.fecha_publicacion.slice(0, 16);
      if (data.data.url_post) createForm.url_post = data.data.url_post;
      if (data.data.media_url) createForm.media_url = data.data.media_url;
      if (data.data.plataforma) {
        createForm.plataforma = data.data.plataforma;
        const matchingPerfil = perfilesCandidatoSeleccionado.value.find(p => p.plataforma.toLowerCase() === data.data.plataforma.toLowerCase());
        if (matchingPerfil) {
          createForm.perfil_social_id = matchingPerfil.id;
        }
      }

      const lk = data.data.total_likes || 0;
      if (createForm.plataforma === 'facebook') {
        createForm.reacciones_detalladas.like = lk;
        createForm.reacciones_detalladas.love = 0;
        createForm.reacciones_detalladas.care = 0;
        createForm.reacciones_detalladas.haha = 0;
        createForm.reacciones_detalladas.wow = 0;
        createForm.reacciones_detalladas.sad = 0;
        createForm.reacciones_detalladas.angry = 0;
      } else {
        createForm.total_likes = lk;
        createForm.reacciones_detalladas.like = 0;
        createForm.reacciones_detalladas.love = 0;
        createForm.reacciones_detalladas.care = 0;
        createForm.reacciones_detalladas.haha = 0;
        createForm.reacciones_detalladas.wow = 0;
        createForm.reacciones_detalladas.sad = 0;
        createForm.reacciones_detalladas.angry = 0;
      }

      scrapeSuccessMsg.value = '✅ ¡Datos, texto e imagen extraídos exitosamente!';
    } else {
      scrapeErrorMsg.value = data.error || data.mensaje || 'No se pudieron extraer los datos automáticamente. Puedes completarlos a mano.';
    }
  } catch (err) {
    scrapeErrorMsg.value = 'Error al consultar el lector de redes. Completa los datos a mano.';
  } finally {
    isScraping.value = false;
  }
};

// Sincronizar suma de emojis con total_likes solo para Facebook
watch(() => createForm.reacciones_detalladas, (reacs) => {
  if (createForm.plataforma === 'facebook') {
    const sum = Object.values(reacs).reduce((acc, val) => acc + (Number(val) || 0), 0);
    if (sum > 0) {
      createForm.total_likes = sum;
    }
  }
}, { deep: true });

// Cálculo del Índice de Aprobación Neta
const netApproval = computed(() => {
  if (createForm.plataforma !== 'facebook') {
    return 100;
  }
  const r = createForm.reacciones_detalladas;
  const pos = (Number(r.like) || 0) + (Number(r.love) || 0) + (Number(r.care) || 0) + (Number(r.haha) || 0) + (Number(r.wow) || 0);
  const neg = (Number(r.sad) || 0) + (Number(r.angry) || 0);
  const tot = pos + neg;
  if (tot === 0) return 100;
  return Math.round(((pos - neg) / tot) * 100);
});

// Termómetro de Humor Social (1 a 5 estrellas)
const calculatedSocialThermometer = computed(() => {
  if (createForm.plataforma !== 'facebook') {
    return 5;
  }
  const r = createForm.reacciones_detalladas;
  const pos = (Number(r.like) || 0) + (Number(r.love) || 0) + (Number(r.care) || 0);
  const neg = (Number(r.sad) || 0) + (Number(r.angry) || 0) * 2;
  const tot = pos + neg + (Number(r.haha) || 0) + (Number(r.wow) || 0);
  if (tot === 0) return 5;
  const ratio = (pos - neg) / tot;
  if (ratio > 0.6) return 5;
  if (ratio > 0.2) return 4;
  if (ratio > -0.2) return 3;
  if (ratio > -0.6) return 2;
  return 1;
});

const submitCreatePost = () => {
  // Asegurar que valores numéricos y relacionales se envíen limpios
  const payload = {
    ...createForm.data(),
    perfil_social_id: createForm.perfil_social_id ? Number(createForm.perfil_social_id) : null,
    eje_tematico_id: createForm.eje_tematico_id ? Number(createForm.eje_tematico_id) : null,
    total_likes: Number(createForm.total_likes || 0),
    total_comentarios: Number(createForm.total_comentarios || 0),
    total_compartidos: Number(createForm.total_compartidos || 0),
    total_republicados: Number(createForm.total_republicados || 0),
    total_guardados: Number(createForm.total_guardados || 0),
    vistas_organicas: Number(createForm.vistas_organicas || 0),
    vistas_pagadas: Number(createForm.vistas_pagadas || 0),
    monto_invertido_pauta: Number(createForm.monto_invertido_pauta || 0),
    me_gusta: createForm.plataforma === 'facebook' 
      ? Number(createForm.reacciones_detalladas.like || createForm.total_likes || 0)
      : Number(createForm.total_likes || 0),
    me_encanta: Number(createForm.reacciones_detalladas.love || 0),
    me_importa: Number(createForm.reacciones_detalladas.care || 0),
    me_divierte: Number(createForm.reacciones_detalladas.haha || 0),
    me_asombra: Number(createForm.reacciones_detalladas.wow || 0),
    me_entristece: Number(createForm.reacciones_detalladas.sad || 0),
    me_enoja: Number(createForm.reacciones_detalladas.angry || 0),
  };

  createForm.transform(() => payload).post('/publicaciones', {
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
            v-if="selectedPlataforma || selectedTipoPauta || selectedCandidato || selectedEje || selectedAnio || selectedMes || selectedRangoAprobacion || searchQuery || (selectedOrden && selectedOrden !== 'recientes')"
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

        <!-- Fila 2: Selectores de Eje, Tipo de Pauta, Aprobación %, Año, Mes y Buscador -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
          <!-- Buscador -->
          <div class="relative">
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              @input="applyFilters"
              type="text"
              placeholder="Buscar en texto..."
              class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <!-- Selector de Eje Temático -->
          <select
            v-model="selectedEje"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          >
            <option value="">🎯 Todos los Ejes (5 Pilares)</option>
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
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          >
            <option value="">Orgánico & Pauta</option>
            <option value="organico">Orgánico Puro</option>
            <option value="organico_impulsado">Orgánico Impulsado</option>
            <option value="pauta_paga">Anuncio / Pauta</option>
          </select>

          <!-- Selector de Aprobación Neta (%) -->
          <select
            v-model="selectedRangoAprobacion"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          >
            <option value="">Aprobación (%)</option>
            <option value="alta">🟢 Alta (≥ 80%)</option>
            <option value="media">🔵 Media (50% - 79%)</option>
            <option value="baja">🔴 Baja (&lt; 50%)</option>
          </select>

          <!-- Selector de Año (Fechas de origen reales de las publicaciones) -->
          <select
            v-model="selectedAnio"
            @change="onAnioChange"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
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

          <!-- Selector de Mes (Fechas de origen reales de las publicaciones) -->
          <select
            v-model="selectedMes"
            @change="applyFilters"
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
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
            class="px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
            title="Orden de las publicaciones"
          >
            <option value="recientes">🕒 Más Recientes (Cronológico)</option>
            <option value="antiguos">⏳ Más Antiguos</option>
            <option value="interacciones">🔥 Más Interacciones</option>
          </select>
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

            <!-- Preview miniatura de la imagen extraída o URL manual -->
            <div v-if="createForm.media_url" class="pt-2 border-t border-cyan-500/20 flex items-center gap-3">
              <img
                :src="createForm.media_url"
                alt="Vista previa de imagen"
                referrerpolicy="no-referrer"
                class="w-14 h-14 object-cover rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs"
              />
              <div class="flex-1 min-w-0">
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 block">🖼️ Imagen / Portada detectada:</span>
                <input
                  v-model="createForm.media_url"
                  type="url"
                  placeholder="https://..."
                  class="w-full mt-1 px-2.5 py-1 text-[11px] font-mono rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 truncate"
                />
              </div>
            </div>
          </div>

          <!-- Fila 3: Eje Temático, Formato y Fecha -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Eje Temático (5 Pilares):</label>
              <select
                v-model="createForm.eje_tematico_id"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-semibold"
              >
                <option value="">-- Seleccionar Eje Temático --</option>
                <optgroup v-for="(ejesInGroup, pilar) in groupedEjes" :key="pilar" :label="pilar">
                  <option v-for="e in ejesInGroup" :key="e.id" :value="e.id">
                    • {{ e.nombre }}
                  </option>
                </optgroup>
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

          <!-- Fila 6: Métricas e Interacciones Adaptativas por Red Social -->
          
          <!-- CASO 1: INSTAGRAM (❤️ Corazones, 💬 Comentarios, 🔁 Republicar, ✈️ Compartidos, 🔖 Guardado Propio) -->
          <div v-if="createForm.plataforma === 'instagram'" class="p-4 rounded-2xl bg-gradient-to-r from-[#E4405F]/10 via-[#F77737]/5 to-transparent border border-[#E4405F]/20 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <Heart class="w-4 h-4 text-[#E4405F] fill-[#E4405F]" />
                <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas Públicas de Instagram</span>
              </div>
              <span class="text-[11px] font-mono text-[#E4405F] font-bold">Interacciones Nativas</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight">
              Instagram audita Corazones Me gusta (❤️), Comentarios (💬), Republicaciones (🔁) y Compartidos (✈️).
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-center">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-[#E4405F]/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-rose-500 mb-1 flex items-center justify-center gap-1">
                  <Heart class="w-3 h-3 fill-rose-500" />
                  <span>Me gusta (❤️)</span>
                </label>
                <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center justify-center gap-1">
                  <MessageCircle class="w-3 h-3" />
                  <span>Comentarios (💬)</span>
                </label>
                <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1 flex items-center justify-center gap-1">
                  <Repeat class="w-3 h-3" />
                  <span>Republicar (🔁)</span>
                </label>
                <input v-model.number="createForm.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-1 flex items-center justify-center gap-1">
                  <Send class="w-3 h-3" />
                  <span>Compartidos (✈️)</span>
                </label>
                <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 font-mono">
              <!-- Reproducciones / Vistas Orgánicas -->
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center">
                <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1 flex items-center justify-center gap-1">
                  <Eye class="w-3 h-3" />
                  <span>Reproducciones / Vistas</span>
                </label>
                <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
              </div>

              <!-- Item Propio de Auditoría: Guardados -->
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs text-center">
                <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1" title="Métrica privada / seguimiento interno de la campaña">
                  <Bookmark class="w-3 h-3 fill-amber-500" />
                  <span>Guardado Propio (Auditoría)</span>
                </label>
                <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
              </div>
            </div>
          </div>

          <!-- CASO 2: FACEBOOK (MULTI-EMOJI 👍 ❤️ 🥰 😂 😮 😢 😡) -->
          <div v-else-if="createForm.plataforma === 'facebook'" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <Sparkles class="w-4 h-4 text-blue-500" />
                <span>Desglose de Emojis / Reacciones Facebook</span>
              </span>
              <span class="font-mono text-cyan-500 font-bold">Aprobación Neta: {{ netApproval }}%</span>
            </div>

            <!-- Aviso Táctico de Carga -->
            <div class="p-2.5 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-800 dark:text-blue-200 text-xs flex items-start gap-2">
              <AlertCircle class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
              <div class="leading-snug">
                <span class="font-bold">⚠️ Importante para Facebook:</span>
                Como Facebook no expone su conteo individual de emojis públicamente, <strong>completa o verifica las cantidades observadas en la publicación antes de guardar</strong> para calcular con exactitud la Aprobación Neta y el Sentimiento.
              </div>
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

            <div class="grid grid-cols-3 gap-2 pt-2">
              <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center font-mono">
                <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1">👁️ Plays / Vistas</label>
                <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold" />
              </div>
              <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center font-mono">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1">💬 Comentarios</label>
                <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
              </div>
              <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center font-mono">
                <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1">🔄 Shares</label>
                <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
              </div>
            </div>
          </div>

          <!-- CASO 3: TIKTOK (❤️ Likes, 💬 Comentarios, 🔖 Favoritos/Guardados, 🔄 Compartidos, 👁️ Plays) -->
          <div v-else-if="createForm.plataforma === 'tiktok'" class="p-4 rounded-2xl bg-gradient-to-r from-[#00F2FE]/10 via-[#FF004F]/5 to-transparent border border-[#00F2FE]/20 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-sm">🎵</span>
                <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas Nativas de TikTok</span>
              </div>
              <span class="text-[11px] font-mono text-[#00F2FE] dark:text-[#00F2FE] font-bold">Video Corto</span>
            </div>

            <!-- Aviso Táctico TikTok -->
            <div class="p-2.5 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-800 dark:text-cyan-200 text-xs flex items-start gap-2">
              <AlertCircle class="w-4 h-4 text-cyan-500 shrink-0 mt-0.5" />
              <div class="leading-snug">
                <span class="font-bold">⚠️ Métricas de TikTok:</span>
                Verifica o ingresa las cantidades de <strong>Me gusta (❤️), Comentarios (💬), Favoritos (🔖) y Compartidos (↗️)</strong> observadas en la app antes de guardar.
              </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-center">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-[#FF004F]/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-[#FF004F] mb-1 flex items-center justify-center gap-1">
                  <Heart class="w-3 h-3 fill-[#FF004F]" />
                  <span>Me gusta (❤️)</span>
                </label>
                <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center justify-center gap-1">
                  <MessageCircle class="w-3 h-3" />
                  <span>Comentarios (💬)</span>
                </label>
                <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1">
                  <Bookmark class="w-3 h-3 fill-amber-500" />
                  <span>Favoritos (🔖)</span>
                </label>
                <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-1 flex items-center justify-center gap-1">
                  <Send class="w-3 h-3" />
                  <span>Compartidos (↗️)</span>
                </label>
                <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>
            </div>

            <!-- Vistas / Plays TikTok -->
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-[#00F2FE]/30 shadow-2xs text-center font-mono">
              <label class="block text-[10px] uppercase font-bold text-[#00F2FE] mb-1 flex items-center justify-center gap-1">
                <Eye class="w-3 h-3" />
                <span>Visualizaciones (Reproducciones / Plays)</span>
              </label>
              <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
            </div>
          </div>

          <!-- CASO 4: X / TWITTER Y THREADS (❤️ Likes, 💬 Respuestas, 🔁 Reposts/Retweets, ↗️ Compartidos, 🔖 Guardados, 👁️ Impresiones) -->
          <div v-else-if="createForm.plataforma === 'x_twitter' || createForm.plataforma === 'twitter' || createForm.plataforma === 'threads'" class="p-4 rounded-2xl bg-slate-900/50 border border-slate-700/60 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="font-black text-sm text-slate-100 font-mono">{{ createForm.plataforma === 'threads' ? '@' : '𝕏' }}</span>
                <span class="font-bold text-xs text-slate-800 dark:text-slate-200">{{ createForm.plataforma === 'threads' ? 'Métricas Nativas de Threads' : 'Métricas Nativas de X (Twitter)' }}</span>
              </div>
              <span class="text-[11px] font-mono text-cyan-400 font-bold">{{ createForm.plataforma === 'threads' ? 'Feed de Conversación' : 'Timeline Político' }}</span>
            </div>

            <!-- Aviso Táctico X / Threads -->
            <div class="p-2.5 rounded-xl bg-slate-800/80 border border-slate-700 text-slate-300 text-xs flex items-start gap-2">
              <AlertCircle class="w-4 h-4 text-cyan-400 shrink-0 mt-0.5" />
              <div class="leading-snug">
                <span class="font-bold text-cyan-400">⚠️ Métricas de {{ createForm.plataforma === 'threads' ? 'Threads' : 'X' }}:</span>
                Ingresa o verifica las cantidades de <strong>Me gusta (❤️), Respuestas (💬), Reposts (🔁) y Guardados (🔖)</strong> observadas en la publicación antes de guardar.
              </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-center">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-rose-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-rose-500 mb-1 flex items-center justify-center gap-1">
                  <Heart class="w-3 h-3 fill-rose-500" />
                  <span>Me gusta (❤️)</span>
                </label>
                <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center justify-center gap-1">
                  <MessageCircle class="w-3 h-3" />
                  <span>Respuestas (💬)</span>
                </label>
                <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1 flex items-center justify-center gap-1">
                  <Repeat class="w-3 h-3" />
                  <span>Reposts (🔁)</span>
                </label>
                <input v-model.number="createForm.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1">
                  <Bookmark class="w-3 h-3 fill-amber-500" />
                  <span>Guardados (🔖)</span>
                </label>
                <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
              </div>
            </div>

            <!-- Vistas / Impresiones X -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 font-mono">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
                <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1 flex items-center justify-center gap-1">
                  <Eye class="w-3 h-3" />
                  <span>Visualizaciones / Impresiones</span>
                </label>
                <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs text-center font-mono">
                <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-1 flex items-center justify-center gap-1">
                  <Send class="w-3 h-3" />
                  <span>Compartidos / DM (↗️)</span>
                </label>
                <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>
            </div>
          </div>

          <!-- CASO 5: OTRAS REDES (YOUTUBE, LINKEDIN) -->
          <div v-else class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-700 dark:text-slate-300">Métricas Principales ({{ createForm.plataforma.toUpperCase() }}):</span>
              <span class="font-mono text-cyan-500 font-bold">Interacciones Totales</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-center">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-rose-500 mb-1">
                  {{ createForm.plataforma === 'youtube' || createForm.plataforma === 'linkedin' ? '👍 Likes' : '❤️ Likes' }}
                </label>
                <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1">
                  💬 Comentarios
                </label>
                <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1">
                  🔄 Compartidos
                </label>
                <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1">
                  🔖 Guardados
                </label>
                <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>
            </div>

            <!-- Vistas -->
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
              <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1 flex items-center justify-center gap-1">
                <Eye class="w-3 h-3" />
                <span>Reproducciones / Alcance Estimado</span>
              </label>
              <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
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
