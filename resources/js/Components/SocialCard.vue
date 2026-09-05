<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
  Eye,
  MessageCircle,
  Share2,
  Sparkles,
  DollarSign,
  Star,
  Edit3,
  Trash2,
  X,
  Check,
  Link2,
  Activity,
  Calendar,
  Bookmark,
  Flame,
  Target,
  Heart,
  Repeat,
  Send,
  AlertCircle,
  RefreshCw
} from '@lucide/vue';
import Badge from './Badge.vue';
import MediaEmbed from './MediaEmbed.vue';
import SocialCardEditModal from './SocialCardEditModal.vue';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  canWrite: {
    type: Boolean,
    default: true,
  },
  ejes: {
    type: Array,
    default: () => [],
  }
});

const page = usePage();
const availableEjes = computed(() => {
  if (props.ejes && props.ejes.length) return props.ejes;
  return page.props.ejes || [];
});

const groupedEjes = computed(() => {
  const list = availableEjes.value || [];
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

const showComments = ref(false);
const showPautaHistorial = ref(false);
const isEditing = ref(false);
const editandoFecha = ref(false);
const fechaEditada = ref('');
const guardandoFecha = ref(false);
// Override local para el estado del semáforo (evita esperar al reload de Inertia)
const fechaConfirmadaLocalmente = ref(false);
const fechaHumanaLocal = ref(null); // Fecha humanizada actualizada localmente tras confirmar
const isTextExpanded = ref(false);
const isLongText = computed(() => {
  const txt = (props.post.contenido_resumen || '').trim();
  // Estándar de tamaño ideal: posts de hasta ~420 caracteres u 8 renglones/saltos no necesitan 'Ver más'
  const saltosDeLinea = (txt.match(/\n/g) || []).length;
  return txt.length > 420 || saltosDeLinea > 8;
});

const platform = computed(() => (props.post.plataforma || props.post.perfil_social?.plataforma || 'instagram').toLowerCase());
const isInstagram = computed(() => platform.value === 'instagram');
const isFacebook = computed(() => platform.value === 'facebook');
const isThreads = computed(() => platform.value === 'threads');
const isTikTok = computed(() => platform.value === 'tiktok');
const isTwitter = computed(() => platform.value === 'x_twitter' || platform.value === 'twitter');
const isYouTube = computed(() => platform.value === 'youtube');
const isLinkedIn = computed(() => platform.value === 'linkedin');

// Detectar si la fecha de publicación coincide con la fecha en que se cargó al sistema
// (significa que nunca se confirmó la fecha real de la red social)
const esFechaSinConfirmar = computed(() => {
  // Si ya fue confirmada (en base de datos o en esta sesión), es una fecha validada
  if (props.post.fecha_confirmada || fechaConfirmadaLocalmente.value) return false;
  if (!props.post.fecha_publicacion_raw || !props.post.fecha_carga) return false;
  const fechaPub = props.post.fecha_publicacion_raw.slice(0, 10); // YYYY-MM-DD
  return fechaPub === props.post.fecha_carga;
});

// Fecha humanizada: priorizar la actualizada localmente
const fechaHumanaVisible = computed(() => {
  return fechaHumanaLocal.value || props.post.fecha_publicacion_humana || props.post.fecha_publicacion;
});

// Formatear una fecha YYYY-MM-DD al formato legible estilo Facebook en español
const formatearFechaHumana = (fechaStr) => {
  if (!fechaStr) return '';
  try {
    const d = new Date(fechaStr + 'T12:00:00'); // mediodía para evitar problemas de timezone
    const anioActual = new Date().getFullYear();
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const dia = d.getDate();
    const mes = meses[d.getMonth()];
    const anio = d.getFullYear();
    return anio === anioActual ? `${dia} de ${mes}` : `${dia} de ${mes} de ${anio}`;
  } catch (e) {
    return fechaStr;
  }
};

const abrirEdicionFecha = () => {
  fechaEditada.value = props.post.fecha_publicacion_raw?.slice(0, 10) || '';
  editandoFecha.value = true;
};

const cancelarEdicionFecha = () => {
  editandoFecha.value = false;
  fechaEditada.value = '';
};

const confirmarFecha = () => {
  if (!fechaEditada.value) return;
  guardandoFecha.value = true;
  router.patch(
    `/publicaciones/${props.post.id}/fecha`,
    { fecha_publicacion: fechaEditada.value },
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        editandoFecha.value = false;
        guardandoFecha.value = false;
        // Actualizar estado visual inmediatamente sin esperar al reload de Inertia
        fechaConfirmadaLocalmente.value = true;
        fechaHumanaLocal.value = formatearFechaHumana(fechaEditada.value);
      },
      onError: () => {
        guardandoFecha.value = false;
      },
    }
  );
};

// Sincronización en vivo individual con detección de pauta
const sincronizando = ref(false);
const syncFeedback = ref('');
const sugerenciaPautaModal = ref(null); // { visible: boolean, motivo: string, sugerido: string }

const sincronizarPost = async () => {
  if (sincronizando.value) return;
  sincronizando.value = true;
  syncFeedback.value = '';

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const resp = await fetch(`/publicaciones/${props.post.id}/sincronizar`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
      },
    });
    const data = await resp.json();

    if (resp.ok && data.success) {
      syncFeedback.value = `+${data.delta_likes || 0} likes`;
      setTimeout(() => { syncFeedback.value = ''; }, 4000);

      // Si el backend detectó sospecha de pauta durante el salto de métricas
      if (data.sospecha_pauta && props.post.tipo_pauta === 'organico') {
        sugerenciaPautaModal.value = {
          visible: true,
          motivo: data.motivo_sospecha_pauta,
          sugerido: data.tipo_pauta_sugerido || 'organico_impulsado',
        };
      } else {
        router.reload({ preserveScroll: true });
      }
    } else {
      syncFeedback.value = data.mensaje || 'Error al sincronizar';
      setTimeout(() => { syncFeedback.value = ''; }, 3000);
    }
  } catch (e) {
    syncFeedback.value = 'Error de conexión';
    setTimeout(() => { syncFeedback.value = ''; }, 3000);
  } finally {
    sincronizando.value = false;
  }
};

const convertirAPostImpulsado = () => {
  router.put(`/publicaciones/${props.post.id}`, {
    ...props.post,
    tipo_pauta: 'organico_impulsado',
  }, {
    preserveScroll: true,
    onSuccess: () => {
      sugerenciaPautaModal.value = null;
    }
  });
};

const formatDateForInput = (d, raw) => {
  if (raw && typeof raw === 'string' && raw.length >= 10) {
    return raw.slice(0, 16);
  }
  if (!d) return '';
  const str = String(d).trim();
  // Format: YYYY-MM-DD...
  if (/^\d{4}-\d{2}-\d{2}/.test(str)) {
    return str.slice(0, 16);
  }
  // Format: DD/MM/YYYY HH:mm or DD/MM/YYYY
  if (/^\d{1,2}\/\d{1,2}\/\d{4}/.test(str)) {
    const parts = str.split(' ');
    const dParts = parts[0].split('/');
    const day = dParts[0].padStart(2, '0');
    const month = dParts[1].padStart(2, '0');
    const year = dParts[2];
    const time = parts[1] ? parts[1].slice(0, 5) : '12:00';
    return `${year}-${month}-${day}T${time}`;
  }
  try {
    const date = new Date(str);
    if (!isNaN(date.getTime())) {
      const pad = (n) => String(n).padStart(2, '0');
      return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }
  } catch (e) {}
  return '';
};

const parseReacciones = (r, totalLikes = 0, plat = '') => {
  const currentPlat = (plat || platform.value || '').toLowerCase();
  
  if (r && typeof r === 'object') {
    return {
      me_gusta: Number(r.me_gusta ?? totalLikes),
      me_encanta: Number(r.me_encanta ?? 0),
      me_importa: Number(r.me_importa ?? 0),
      me_divierte: Number(r.me_divierte ?? 0),
      me_asombra: Number(r.me_asombra ?? 0),
      me_entristece: Number(r.me_entristece ?? 0),
      me_enoja: Number(r.me_enoja ?? 0),
    };
  }

  return {
    me_gusta: Number(totalLikes || 0),
    me_encanta: 0,
    me_importa: 0,
    me_divierte: 0,
    me_asombra: 0,
    me_entristece: 0,
    me_enoja: 0,
  };
};

const openEditModal = () => {
  isEditing.value = true;
};

const showDeleteModal = ref(false);
const deleting = ref(false);

const openDeleteModal = () => {
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  deleting.value = true;
  router.delete(`/publicaciones/${props.post.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false;
      showDeleteModal.value = false;
    }
  });
};

const formatNumber = (num) => {
  if (num === null || num === undefined) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};

const formatCurrency = (amount) => {
  if (!amount) return '$0';
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(amount);
};

const currentReacciones = computed(() => {
  return parseReacciones(props.post.reacciones_detalladas, props.post.total_likes);
});

const approvalPct = computed(() => {
  if (props.post.aprobacion_neta_pct !== undefined && props.post.aprobacion_neta_pct !== null) {
    return Math.round(Number(props.post.aprobacion_neta_pct));
  }
  const r = currentReacciones.value;
  const pos = (Number(r.me_gusta) || 0) + (Number(r.me_encanta) || 0) + (Number(r.me_importa) || 0);
  const neg = (Number(r.me_entristece) || 0) + (Number(r.me_enoja) || 0);
  const tot = pos + neg + (Number(r.me_divierte) || 0) + (Number(r.me_asombra) || 0);
  if (tot === 0) return 100;
  return Math.round(((pos - neg) / tot) * 100);
});

const totalInteracciones = computed(() => {
  return Number(props.post.total_likes || 0) +
    Number(props.post.total_comentarios || 0) +
    Number(props.post.total_republicados || 0) +
    Number(props.post.total_compartidos || 0);
});

const scoreImpacto = computed(() => {
  if (props.post.score_impacto_organico !== undefined && props.post.score_impacto_organico !== null) {
    return Number(props.post.score_impacto_organico);
  }
  const lk = Number(props.post.total_likes || 0);
  const cm = Number(props.post.total_comentarios || 0) * 3;
  const sh = Number(props.post.total_compartidos || 0) * 5;
  const rp = Number(props.post.total_republicados || 0) * 10;
  return lk + cm + sh + rp;
});

const tasaViralidad = computed(() => {
  if (props.post.tasa_viralidad_pct !== undefined && props.post.tasa_viralidad_pct !== null) {
    return Number(props.post.tasa_viralidad_pct);
  }
  const views = Number(props.post.total_vistas || 0);
  if (views <= 0) return 0;
  return Number(((scoreImpacto.value / views) * 100).toFixed(2));
});

const isPostInActiveWindow = computed(() => {
  const raw = props.post.fecha_publicacion_raw || props.post.fecha_publicacion;
  if (!raw) return false;
  try {
    let d;
    if (typeof raw === 'string' && raw.includes('/')) {
      const parts = raw.split(' ')[0].split('/');
      d = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
    } else {
      d = new Date(raw);
    }
    const limit = new Date(Date.now() - 15 * 24 * 60 * 60 * 1000);
    return !isNaN(d.getTime()) && d >= limit;
  } catch (e) {
    return false;
  }
});

// Días transcurridos desde la fecha de publicación
const diasDesdePublicacion = computed(() => {
  const raw = props.post.fecha_publicacion_raw || props.post.fecha_publicacion;
  if (!raw) return 999;
  try {
    let d;
    if (typeof raw === 'string' && raw.includes('/')) {
      const parts = raw.split(' ')[0].split('/');
      d = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
    } else {
      d = new Date(raw);
    }
    if (isNaN(d.getTime())) return 999;
    const diffMs = Date.now() - d.getTime();
    return Math.floor(diffMs / (1000 * 60 * 60 * 24));
  } catch (e) {
    return 999;
  }
});

// Estilos diferenciados de tarjeta según Estrategia de Difusión / Pauta y Tracción Política Normalizada (War Room)
const cardPautaStyles = computed(() => {
  const p = (props.post.tipo_pauta || 'organico').toLowerCase();
  const dias = diasDesdePublicacion.value;
  // Score de Tracción Indexado (0 a 100) normalizado por el Tier de la cuenta
  const scoreTraccion = props.post.analisis_traccion?.score_traccion_indexado ?? 50;

  // 1. NIVEL DORADO / ORO (Score Tracción >= 75/100) -> Post Estrella Consagrado (Tracción Sobresaliente)
  if (scoreTraccion >= 75) {
    return {
      cardClass: 'border-amber-400/80 dark:border-amber-400/70 ring-2 ring-amber-400/40 border-t-4 border-t-amber-400 shadow-[0_4px_35px_-4px_rgba(251,191,36,0.45)] dark:shadow-[0_0_40px_-4px_rgba(251,191,36,0.50)] hover:shadow-[0_8px_45px_-2px_rgba(251,191,36,0.60)]',
      headerTint: 'bg-gradient-to-b from-amber-400/15 via-amber-400/4 to-transparent',
      budgetBadge: 'bg-gradient-to-r from-amber-400 to-yellow-500 text-slate-950 border-amber-300 dark:border-amber-400 shadow-md shadow-amber-400/30 font-black',
      scoreBadge: 'bg-amber-400/20 text-amber-600 dark:text-amber-300 border border-amber-400/40 font-black',
      estadoEstrategico: 'dorado',
    };
  }

  // 2. NIVEL ROJO FUEGO (Orgánico, <= 10 días, Score Tracción >= 60/100) -> Oportunidad de Boost Recomendada
  if (p === 'organico' && dias <= 10 && scoreTraccion >= 60) {
    return {
      cardClass: 'border-rose-500/70 dark:border-rose-500/60 ring-2 ring-rose-500/30 border-t-4 border-t-rose-500 shadow-[0_4px_35px_-4px_rgba(244,63,94,0.40)] dark:shadow-[0_0_40px_-4px_rgba(244,63,94,0.45)] hover:shadow-[0_8px_45px_-2px_rgba(244,63,94,0.55)]',
      headerTint: 'bg-gradient-to-b from-rose-500/12 via-rose-500/3 to-transparent',
      budgetBadge: '',
      scoreBadge: 'bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/40 font-black',
      estadoEstrategico: 'fuego',
    };
  }

  // 3. ESTRATEGIAS ESTÁNDAR
  switch (p) {
    case 'organico_impulsado':
      return {
        cardClass: 'border-cyan-500/60 dark:border-cyan-500/40 ring-1 ring-cyan-500/25 border-t-4 border-t-cyan-500 shadow-[0_4px_30px_-4px_rgba(6,182,212,0.30)] dark:shadow-[0_0_35px_-4px_rgba(6,182,212,0.35)] hover:shadow-[0_8px_40px_-2px_rgba(6,182,212,0.45)]',
        headerTint: 'bg-gradient-to-b from-cyan-500/8 via-cyan-500/2 to-transparent',
        budgetBadge: 'bg-cyan-500 text-slate-950 border-cyan-400/80 shadow-md shadow-cyan-500/30',
        scoreBadge: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold',
        estadoEstrategico: 'booster',
      };
    case 'pauta_paga':
      return {
        cardClass: 'border-violet-500/60 dark:border-violet-500/40 ring-1 ring-violet-500/25 border-t-4 border-t-violet-500 shadow-[0_4px_30px_-4px_rgba(139,92,246,0.30)] dark:shadow-[0_0_35px_-4px_rgba(139,92,246,0.35)] hover:shadow-[0_8px_40px_-2px_rgba(139,92,246,0.45)]',
        headerTint: 'bg-gradient-to-b from-violet-500/8 via-violet-500/2 to-transparent',
        budgetBadge: 'bg-violet-600 text-white border-violet-400/80 shadow-md shadow-violet-500/30',
        scoreBadge: 'bg-violet-500/10 text-violet-600 dark:text-violet-400 font-bold',
        estadoEstrategico: 'pauta_paga',
      };
    case 'colaboracion_pagada':
      return {
        cardClass: 'border-amber-500/60 dark:border-amber-500/40 ring-1 ring-amber-500/25 border-t-4 border-t-amber-500 shadow-[0_4px_30px_-4px_rgba(245,158,11,0.30)] dark:shadow-[0_0_35px_-4px_rgba(245,158,11,0.35)] hover:shadow-[0_8px_40px_-2px_rgba(245,158,11,0.45)]',
        headerTint: 'bg-gradient-to-b from-amber-500/8 via-amber-500/2 to-transparent',
        budgetBadge: 'bg-amber-500 text-slate-950 border-amber-400/80 shadow-md shadow-amber-500/30',
        scoreBadge: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold',
        estadoEstrategico: 'colaboracion',
      };
    case 'organico':
    default:
      return {
        cardClass: 'border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md dark:shadow-none',
        headerTint: '',
        budgetBadge: '',
        scoreBadge: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium',
        estadoEstrategico: 'organico',
      };
  }
});

</script>

<template>
  <div
    class="bg-white dark:bg-slate-900 border rounded-2xl overflow-hidden shadow-xs hover:shadow-md dark:shadow-none transition-all duration-200 relative flex flex-col justify-between h-full"
    :class="cardPautaStyles.cardClass"
  >
    <!-- Header -->
    <div
      class="p-3.5 sm:p-4 border-b border-slate-100 dark:border-slate-800/80 space-y-2.5 shrink-0 transition-colors"
      :class="cardPautaStyles.headerTint"
    >
      <!-- Fila 1: Autor (Avatar + Nombre + Handle) | Badges de Red & Acciones -->
      <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
          <!-- Avatar -->
          <div class="relative shrink-0">
            <img
              :src="post.candidato?.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.candidato?.nombre_completo || 'Candidato')}&background=0f172a&color=06b6d4`"
              :alt="post.candidato?.nombre_completo"
              class="w-10 h-10 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 shadow-xs"
            />
            <div
              v-if="post.candidato?.es_propio"
              class="absolute -bottom-1 -right-1 bg-cyan-500 text-slate-950 p-0.5 rounded-full ring-2 ring-white dark:ring-slate-900"
              title="Candidato Propio"
            >
              <Sparkles class="w-2.5 h-2.5 fill-current" />
            </div>
          </div>

          <!-- Nombre y Handle -->
          <div class="min-w-0">
            <h4 class="font-extrabold text-slate-900 dark:text-slate-100 text-sm leading-tight truncate" :title="post.candidato?.nombre_completo">
              {{ post.candidato?.nombre_completo || 'Candidato' }}
            </h4>
            <p class="font-mono text-xs text-slate-500 dark:text-slate-400 truncate">
              {{ post.perfil_social?.handle_usuario || '@cuenta' }}
            </p>
          </div>
        </div>

        <!-- Badges de Red Social & Acciones -->
        <div class="flex items-center gap-1.5 shrink-0">
          <Badge :variant="post.plataforma || post.perfil_social?.plataforma || 'facebook'" size="sm" />

          <!-- Botones de Acción -->
          <div v-if="canWrite" class="flex items-center gap-0.5 ml-1 pl-1 border-l border-slate-200 dark:border-slate-800">
            <!-- Sincronizar en vivo métricas si tiene URL -->
            <button
              v-if="post.url_post"
              type="button"
              @click="sincronizarPost"
              :disabled="sincronizando"
              class="p-1 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer disabled:opacity-50"
              :title="sincronizando ? 'Sincronizando métricas...' : 'Sincronizar métricas en vivo desde la red social'"
            >
              <RefreshCw class="w-3.5 h-3.5" :class="sincronizando ? 'animate-spin text-cyan-500' : ''" />
            </button>
            <span v-if="syncFeedback" class="text-[10px] font-mono font-bold text-emerald-500 px-1 animate-pulse">
              {{ syncFeedback }}
            </span>

            <button
              v-if="canWrite"
              type="button"
              @click="openEditModal"
              class="p-1 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
              title="Editar publicación y métricas"
            >
              <Edit3 class="w-3.5 h-3.5" />
            </button>
            <button
              v-if="canWrite"
              type="button"
              @click="openDeleteModal"
              class="p-1 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
              title="Eliminar publicación"
            >
              <Trash2 class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Fila 2: Fecha de Origen con Semáforo de Confirmación + Pauta & Sincronización -->
      <div class="flex items-center justify-between gap-2 pt-1.5 border-t border-slate-100 dark:border-slate-800/60 text-xs flex-wrap sm:flex-nowrap">

        <!-- MODO VISUALIZACIÓN: Fecha con semáforo -->
        <div v-if="!editandoFecha" class="flex items-center gap-1.5 min-w-0">
          <Calendar class="w-3.5 h-3.5 shrink-0" :class="esFechaSinConfirmar ? 'text-rose-500' : 'text-emerald-500'" />

          <!-- Badge de fecha: ROJO si sin confirmar, VERDE si confirmada -->
          <button
            v-if="canWrite"
            type="button"
            @click="abrirEdicionFecha"
            :title="esFechaSinConfirmar ? '⚠️ Fecha pendiente de confirmar — Hacer clic para corregirla' : '✅ Fecha confirmada — Hacer clic para cambiarla'"
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-bold text-[11px] sm:text-xs border transition-all cursor-pointer hover:opacity-80 shrink-0"
            :class="esFechaSinConfirmar
              ? 'bg-rose-500/10 border-rose-400/40 text-rose-700 dark:text-rose-300 animate-pulse'
              : 'bg-emerald-500/10 border-emerald-400/40 text-emerald-700 dark:text-emerald-300'"
          >
            <span>{{ fechaHumanaVisible }}</span>
            <span v-if="esFechaSinConfirmar" class="text-rose-400 font-bold">⚠️</span>
            <span v-else class="text-emerald-400">✓</span>
          </button>

          <!-- Solo texto para visualizadores -->
          <span v-else class="font-bold text-[11px] sm:text-xs truncate"
            :class="esFechaSinConfirmar ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-300'">
            {{ fechaHumanaVisible }}
          </span>

          <span v-if="post.fecha_relativa" class="text-[10px] text-slate-400 font-normal shrink-0 truncate">
            ({{ post.fecha_relativa }})
          </span>
        </div>

        <!-- MODO EDICIÓN INLINE: input de fecha -->
        <div v-else class="flex items-center gap-1.5 flex-1 min-w-0">
          <Calendar class="w-3.5 h-3.5 text-amber-500 shrink-0" />
          <input
            v-model="fechaEditada"
            type="date"
            class="flex-1 min-w-0 px-2 py-0.5 text-[11px] font-mono rounded-lg border border-amber-400 bg-amber-50 dark:bg-amber-950/40 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-amber-400 focus:outline-none"
            :max="new Date().toISOString().slice(0, 10)"
          />
          <button
            type="button"
            @click="confirmarFecha"
            :disabled="guardandoFecha || !fechaEditada"
            class="p-1 rounded-md bg-emerald-500 text-white hover:bg-emerald-400 disabled:opacity-50 transition-all cursor-pointer shrink-0"
            title="Confirmar fecha"
          >
            <Check class="w-3.5 h-3.5" />
          </button>
          <button
            type="button"
            @click="cancelarEdicionFecha"
            class="p-1 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600 transition-all cursor-pointer shrink-0"
            title="Cancelar"
          >
            <X class="w-3.5 h-3.5" />
          </button>
        </div>

        <!-- Pauta & Estado de Sincronización (derecha) -->
        <div class="shrink-0 flex items-center gap-1.5 font-mono">
          <Badge
            variant="pauta"
            :value="post.tipo_pauta || 'organico'"
            size="sm"
          />

          <!-- Badge Estratégico Especial: Fuego u Oro -->
          <span
            v-if="cardPautaStyles.estadoEstrategico === 'fuego'"
            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-rose-500/15 text-rose-600 dark:text-rose-300 text-[10px] font-black border border-rose-500/30 animate-pulse"
            title="Oportunidad de Boost: Publicación orgánica con tracción destacada (Score >= 60/100) dentro de los 10 días"
          >
            <span>🔥 Oportunidad Boost</span>
          </span>
          <span
            v-else-if="cardPautaStyles.estadoEstrategico === 'dorado'"
            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-400/20 text-amber-700 dark:text-amber-300 text-[10px] font-black border border-amber-400/40 shadow-2xs"
            title="Post Estrella Consagrado: Tracción electoral sobresaliente (Score >= 75/100)"
          >
            <span>✨ Estrella ({{ (post.tipo_pauta || 'organico') === 'organico' ? 'Viral' : 'Boost' }})</span>
          </span>

          <span
            v-if="esFechaSinConfirmar"
            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-rose-500/10 text-rose-600 dark:text-rose-400 text-[10px] font-bold"
          >
            <span>⚠️ Por confirmar</span>
          </span>
          <span
            v-else-if="isPostInActiveWindow"
            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold"
            title="En ventana de sincronización activa (menos de 15 días)"
          >
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>En vivo (15d)</span>
          </span>
          <span
            v-else
            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-400 text-[10px]"
            title="Métrica histórica consolidada"
          >
            <span>🔒 Histórico</span>
          </span>
        </div>
      </div>
    </div>

    <!-- Post Content Body (Flex 1 para empujar footer al fondo y unificar alturas) -->
    <div class="p-4 sm:p-5 space-y-3 flex-1 flex flex-col justify-between">
      <div class="space-y-2.5">
        <!-- Tag de Eje Temático y Pilar Estratégico (Destacado y Elegante) -->
        <div v-if="post.eje_tematico" class="flex items-center gap-2 flex-wrap">
          <span
            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold border transition-all"
            :style="{
              backgroundColor: `${post.eje_tematico.color_badge || '#06b6d4'}15`,
              color: post.eje_tematico.color_badge || '#06b6d4',
              borderColor: `${post.eje_tematico.color_badge || '#06b6d4'}35`,
            }"
          >
            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: post.eje_tematico.color_badge || '#06b6d4' }"></span>
            <span v-if="post.eje_tematico.pilar_principal" class="text-[10px] font-mono uppercase tracking-wider opacity-80 border-r pr-1.5 mr-0.5 border-current/30">
              {{ post.eje_tematico.pilar_principal.replace(/^\d+\.\s*/, '') }}
            </span>
            <span>{{ post.eje_tematico.nombre }}</span>
          </span>
        </div>

        <!-- Copy / Texto de Publicación con Truncamiento '... Ver más' -->
        <div class="space-y-1">
          <p
            class="text-slate-800 dark:text-slate-200 text-sm sm:text-base leading-relaxed whitespace-pre-line transition-all"
            :class="{ 'line-clamp-8': !isTextExpanded && isLongText }"
          >
            {{ post.contenido_resumen }}
          </p>

          <button
            v-if="isLongText"
            type="button"
            @click="isTextExpanded = !isTextExpanded"
            class="inline-flex items-center gap-1 text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 hover:underline cursor-pointer pt-0.5 transition-colors"
          >
            <span>{{ isTextExpanded ? 'Ver menos ▲' : '... Ver más ▼' }}</span>
          </button>
        </div>
      </div>

      <!-- Zona Inferior del Body: Media Link Card + Figuras Acompañantes -->
      <div class="space-y-3 pt-1">
        <!-- Media Embed / Preview con el valor del presupuesto justo sobre la línea superior del div -->
        <div class="relative mt-1">
          <!-- Valor de Inversión (ej. $5.000 / $80.000) reposando justo sobre la línea del div (Doble de grande) -->
          <div
            v-if="['pauta_paga', 'organico_impulsado', 'colaboracion_pagada'].includes(post.tipo_pauta) && post.monto_invertido_pauta"
            class="absolute -top-3.5 right-4 sm:right-5 px-3.5 py-1 rounded-full font-mono text-xs sm:text-sm font-black shadow-md z-10 tracking-tight flex items-center border-2 transition-transform hover:scale-105"
            :class="cardPautaStyles.budgetBadge"
            :title="`Inversión en pauta: ${formatCurrency(post.monto_invertido_pauta)}`"
          >
            <span>{{ formatCurrency(post.monto_invertido_pauta) }}</span>
          </div>

          <MediaEmbed
            :url="post.url_post"
            :media-url="post.media_url"
            :formato="post.tipo_formato || 'Post'"
            :plataforma="post.plataforma || post.perfil_social?.plataforma || 'facebook'"
          />
        </div>

        <!-- Accompanying Figures / Alliances -->
        <div v-if="post.figuras_acompanantes && post.figuras_acompanantes.length" class="flex items-center gap-1.5 flex-wrap">
          <span class="text-xs text-slate-400 dark:text-slate-500">Con:</span>
          <span
            v-for="(figura, idx) in post.figuras_acompanantes"
            :key="idx"
            class="text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-cyan-600 dark:text-cyan-400 border border-slate-200 dark:border-slate-700"
          >
            🤝 {{ figura }}
          </span>
        </div>
      </div>
    </div>

    <!-- Reactions & Metrics Bar (Adaptativo por Plataforma) -->
    <div class="px-4 sm:px-5 py-3 bg-slate-50/70 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-2 text-xs text-slate-600 dark:text-slate-400">
      <!-- Left: Native Reactions / Likes -->
      <div class="flex items-center gap-2 flex-wrap">
        
        <!-- INSTAGRAM: Solo Me Gusta / Corazones ❤️ -->
        <div v-if="isInstagram" class="flex items-center gap-2 bg-white dark:bg-slate-900 px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-[11px] shadow-2xs">
          <span class="inline-flex items-center gap-1.5 font-bold text-rose-500">
            <Heart class="w-3.5 h-3.5 fill-rose-500 text-rose-500" />
            <span class="text-slate-800 dark:text-slate-200">{{ formatNumber(post.total_likes || 0) }}</span>
            <span class="text-slate-400 font-normal text-[10px]">Me gusta</span>
          </span>
        </div>

        <!-- TIKTOK, X (TWITTER) o THREADS: Me Gusta ❤️ -->
        <div v-else-if="isTikTok || isTwitter || isThreads" class="flex items-center gap-2 bg-white dark:bg-slate-900 px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-[11px] shadow-2xs">
          <span class="inline-flex items-center gap-1.5 font-bold text-rose-500">
            <Heart class="w-3.5 h-3.5 fill-rose-500 text-rose-500" />
            <span class="text-slate-800 dark:text-slate-200">{{ formatNumber(post.total_likes || 0) }}</span>
            <span class="text-slate-400 font-normal text-[10px]">Me gusta</span>
          </span>
        </div>

        <!-- YOUTUBE: Me Gusta 👍 -->
        <div v-else-if="isYouTube" class="flex items-center gap-2 bg-white dark:bg-slate-900 px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-[11px] shadow-2xs">
          <span class="inline-flex items-center gap-1.5 font-bold text-red-500">
            <span class="text-xs">👍</span>
            <span class="text-slate-800 dark:text-slate-200">{{ formatNumber(post.total_likes || 0) }}</span>
            <span class="text-slate-400 font-normal text-[10px]">Me gusta</span>
          </span>
        </div>

        <!-- FACEBOOK / MULTI-EMOJI -->
        <div v-else class="flex items-center gap-2 bg-white dark:bg-slate-900 px-2.5 py-1 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-[11px] shadow-2xs">
          <span class="font-bold text-slate-800 dark:text-slate-200 mr-1">{{ formatNumber(post.total_likes || 0) }}</span>
          <span v-if="currentReacciones.me_gusta" title="Me gusta" class="inline-flex items-center gap-0.5">👍 {{ formatNumber(currentReacciones.me_gusta) }}</span>
          <span v-if="currentReacciones.me_encanta" title="Me encanta" class="inline-flex items-center gap-0.5 text-rose-500">❤️ {{ formatNumber(currentReacciones.me_encanta) }}</span>
          <span v-if="currentReacciones.me_importa" title="Me importa" class="inline-flex items-center gap-0.5 text-amber-500">🥰 {{ formatNumber(currentReacciones.me_importa) }}</span>
          <span v-if="currentReacciones.me_divierte" title="Me divierte" class="inline-flex items-center gap-0.5 text-amber-400">😂 {{ formatNumber(currentReacciones.me_divierte) }}</span>
          <span v-if="currentReacciones.me_asombra" title="Me asombra" class="inline-flex items-center gap-0.5 text-blue-400">😮 {{ formatNumber(currentReacciones.me_asombra) }}</span>
          <span v-if="currentReacciones.me_entristece" title="Me entristece" class="inline-flex items-center gap-0.5 text-blue-500">😢 {{ formatNumber(currentReacciones.me_entristece) }}</span>
          <span v-if="currentReacciones.me_enoja" title="Me enoja" class="inline-flex items-center gap-0.5 text-rose-600 font-bold">😡 {{ formatNumber(currentReacciones.me_enoja) }}</span>
        </div>

        <!-- Net Approval Badge (Solo relevante para Facebook con desglose multi-emocional) -->
        <span
          v-if="isFacebook"
          class="text-[11px] font-mono font-black px-2 py-0.5 rounded-lg border flex items-center gap-1 shadow-2xs"
          :class="{
            'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/30': approvalPct >= 80,
            'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border-cyan-500/30': approvalPct >= 50 && approvalPct < 80,
            'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30': approvalPct >= 20 && approvalPct < 50,
            'bg-rose-500/15 text-rose-600 dark:text-rose-400 border-rose-500/30': approvalPct < 20,
          }"
          :title="`Índice de Aprobación Neta: ${approvalPct}% (Reacciones favorables vs críticas)`"
        >
          <span class="w-1.5 h-1.5 rounded-full" :class="{
            'bg-emerald-500': approvalPct >= 80,
            'bg-cyan-500': approvalPct >= 50 && approvalPct < 80,
            'bg-amber-500': approvalPct >= 20 && approvalPct < 50,
            'bg-rose-500': approvalPct < 20,
          }"></span>
          <span>{{ approvalPct }}%</span>
        </span>
      </div>

      <!-- Quick Metrics: Interacciones Totales, Views, Comments, Reposts, Shares -->
      <div class="flex items-center gap-3.5 flex-wrap">
        <!-- 🎯 Tracción Política Normalizada (Indexada 0 a 100 por Tier de Seguidores) -->
        <div
          class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg font-mono font-bold text-xs transition-colors border"
          :class="cardPautaStyles.scoreBadge"
          :title="`Tracción Electoral: ${post.analisis_traccion?.score_traccion_indexado ?? 50}/100 (${post.analisis_traccion?.etiqueta_calidad ?? 'Estándar Electoral'}) — Normalizado para cuenta ${post.analisis_traccion?.tier?.toUpperCase() ?? 'CANAL'}. TAP: ${post.analisis_traccion?.tap_politica_real ?? 0}%. VTP Ponderado: ${formatNumber(post.analisis_traccion?.vtp_ponderado ?? scoreImpacto)} pts.`"
        >
          <Flame v-if="cardPautaStyles.estadoEstrategico === 'fuego'" class="w-3.5 h-3.5 fill-current text-rose-500 animate-pulse" />
          <Sparkles v-else-if="cardPautaStyles.estadoEstrategico === 'dorado'" class="w-3.5 h-3.5 fill-current text-amber-500" />
          <Target v-else class="w-3.5 h-3.5 text-cyan-500" />
          <span class="text-slate-800 dark:text-slate-100">{{ post.analisis_traccion?.score_traccion_indexado ?? 50 }}</span>
          <span class="text-[10px] font-normal opacity-70">/100 Tracción</span>
        </div>

        <!-- ⚠️ Alerta Forense de Bots o Anomalía de Interacciones -->
        <div
          v-if="post.analisis_traccion?.sospecha_de_bots"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30 text-[11px] font-bold shadow-2xs"
          :title="`Auditoría Forense: ${post.analisis_traccion.alertas_forenses.join(' | ')}`"
        >
          <AlertCircle class="w-3.5 h-3.5 text-rose-500 shrink-0" />
          <span>Anomalía / Posible Inflado</span>
        </div>

        <div class="flex items-center gap-1" title="Visualizaciones / Alcance">
          <Eye class="w-4 h-4 text-slate-400 dark:text-slate-500" />
          <span class="font-mono font-medium">{{ formatNumber(post.total_vistas || 0) }}</span>
        </div>

        <button
          type="button"
          @click="showComments = !showComments"
          class="flex items-center gap-1 hover:text-cyan-500 transition-colors cursor-pointer"
          title="Ver comentarios destacados"
        >
          <MessageCircle class="w-4 h-4 text-blue-500" />
          <span class="font-mono font-medium">{{ formatNumber(post.total_comentarios || 0) }}</span>
        </button>

        <!-- Republicaciones / Reposts (Separado) -->
        <div v-if="isInstagram || isTwitter || isThreads || post.total_republicados > 0" class="flex items-center gap-1" title="Republicaciones (Reposts / Retweets)">
          <Repeat class="w-4 h-4 text-emerald-500" />
          <span class="font-mono font-medium">{{ formatNumber(post.total_republicados || 0) }}</span>
        </div>

        <!-- Compartidos / Envíos (Separado) -->
        <div class="flex items-center gap-1" :title="isInstagram || isThreads ? 'Compartidos / Envíos' : 'Compartidos / Shares'">
          <Send v-if="isInstagram || isThreads" class="w-3.5 h-3.5 text-indigo-500" />
          <Share2 v-else class="w-4 h-4 text-indigo-500" />
          <span class="font-mono font-medium">{{ formatNumber(post.total_compartidos || 0) }}</span>
        </div>

        <!-- Item Propio de Auditoría: Guardado Interno de Campaña -->
        <div
          v-if="post.total_guardados > 0"
          class="flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs"
          title="Guardado propio en auditoría de campaña"
        >
          <Bookmark class="w-3.5 h-3.5 fill-amber-500 text-amber-500" />
          <span class="font-mono font-medium text-[11px]">{{ formatNumber(post.total_guardados || 0) }}</span>
        </div>
      </div>
    </div>

    <!-- Highlighted Comments & Social Humor Thermometer (Collapsible) -->
    <div
      v-if="showComments || (post.comentarios_destacados && post.comentarios_destacados.length)"
      class="p-4 bg-slate-100/60 dark:bg-slate-950/80 border-t border-slate-200 dark:border-slate-800 space-y-2.5 text-xs"
    >
      <!-- Thermometer Rating -->
      <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
        <span class="font-semibold text-slate-700 dark:text-slate-300">Termómetro de Humor Social:</span>
        <div class="flex items-center gap-0.5 text-amber-400">
          <Star
            v-for="star in 5"
            :key="star"
            class="w-3.5 h-3.5"
            :class="star <= (post.termometro_humor_social || 5) ? 'fill-amber-400' : 'text-slate-300 dark:text-slate-600'"
          />
          <span class="ml-1 font-mono font-bold text-slate-700 dark:text-slate-300">
            {{ post.termometro_humor_social || 5 }}/5
          </span>
        </div>
      </div>

      <!-- Top Comments List -->
      <div v-if="post.comentarios_destacados && post.comentarios_destacados.length" class="space-y-1.5">
        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Comentarios más votados:</p>
        <div
          v-for="(comentario, idx) in post.comentarios_destacados"
          :key="idx"
          class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 text-slate-700 dark:text-slate-300 leading-snug"
        >
          💬 "{{ comentario }}"
        </div>
      </div>
    </div>

    <!-- Historial de Pauta & Atribución de Impacto (Si tiene eventos registrados) -->
    <div v-if="post.pauta_eventos && post.pauta_eventos.length > 0" class="border-t border-slate-100 dark:border-slate-800/80">
      <button
        type="button"
        @click="showPautaHistorial = !showPautaHistorial"
        class="w-full px-4 sm:px-5 py-2.5 flex items-center justify-between text-xs font-mono font-bold bg-slate-50/70 dark:bg-slate-950/50 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors cursor-pointer"
      >
        <span class="flex items-center gap-1.5 text-cyan-600 dark:text-cyan-400">
          <Sparkles class="w-3.5 h-3.5" />
          <span>Historial de Pauta & Atribución de ROI ({{ post.pauta_eventos.length }} {{ post.pauta_eventos.length === 1 ? 'corte' : 'cortes' }})</span>
        </span>
        <span class="text-[10px] text-slate-400 font-normal">
          {{ showPautaHistorial ? 'Ocultar ▲' : 'Ver cortes ▼' }}
        </span>
      </button>

      <div v-if="showPautaHistorial" class="p-3.5 sm:p-4 bg-slate-100/50 dark:bg-slate-950/70 space-y-2.5 text-xs font-mono">
        <div
          v-for="ev in post.pauta_eventos"
          :key="ev.id"
          class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2 shadow-2xs"
        >
          <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-1.5 font-bold">
              <Badge variant="pauta" :value="ev.tipo_pauta_anterior" size="sm" />
              <span class="text-cyan-500 font-black">➔</span>
              <Badge variant="pauta" :value="ev.tipo_pauta_nuevo" size="sm" />
            </div>
            <span class="text-[10px] text-slate-400">
              {{ ev.fecha_evento }} ({{ ev.fecha_evento_humana }})
            </span>
          </div>

          <!-- Métricas del snapshot vs incremento actual -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1 text-[11px] text-slate-600 dark:text-slate-400">
            <div>
              <span class="text-[9px] uppercase tracking-wider text-slate-400 block">Base en Corte:</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatNumber(ev.likes_snapshot) }} likes</span>
            </div>
            <div>
              <span class="text-[9px] uppercase tracking-wider text-slate-400 block">Seguidores Canal:</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatNumber(ev.seguidores_canal_snapshot) }}</span>
            </div>
            <div>
              <span class="text-[9px] uppercase tracking-wider text-cyan-500 block">Ganados con Pauta:</span>
              <span class="font-bold text-emerald-500">
                +{{ formatNumber(Math.max(0, Number(post.total_likes || 0) - Number(ev.likes_snapshot || 0))) }} likes
              </span>
            </div>
          </div>

          <div v-if="ev.monto_nuevo > 0" class="flex items-center justify-between pt-1.5 border-t border-slate-100 dark:border-slate-800/80 text-[10px]">
            <span class="text-slate-400">Inversión: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ formatCurrency(ev.monto_nuevo) }}</strong></span>
            <span v-if="Math.max(0, Number(post.total_likes || 0) - Number(ev.likes_snapshot || 0)) > 0" class="text-cyan-600 dark:text-cyan-400 font-bold">
              CPL: ${{ (Number(ev.monto_nuevo) / Math.max(1, Number(post.total_likes || 0) - Number(ev.likes_snapshot || 0))).toFixed(2) }} / like atribuible
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Edición Delegado (SocialCardEditModal) -->
    <SocialCardEditModal
      v-if="isEditing"
      :show="isEditing"
      :post="post"
      :grouped-ejes="groupedEjes"
      @close="isEditing = false"
      @saved="isEditing = false"
    />

    <!-- Modal de Confirmación de Eliminación de Publicación -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
      @click.self="showDeleteModal = false"
    >
      <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl relative"
      >
        <!-- Icono de Advertencia y Título -->
        <div class="flex items-start gap-3.5">
          <div class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
            <Trash2 class="w-5 h-5" />
          </div>
          <div class="space-y-1">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 leading-snug">
              ¿Estás seguro que deseas eliminar la publicación?
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              Esta acción eliminará permanentemente este post y todas sus métricas asociadas de la base de datos.
            </p>
          </div>
        </div>

        <!-- Extracto del post para contexto -->
        <div v-if="post.contenido_resumen" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 text-xs text-slate-600 dark:text-slate-400 line-clamp-2 italic">
          "{{ post.contenido_resumen }}"
        </div>

        <!-- Botones de Acción: Cancelar y Eliminar -->
        <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="showDeleteModal = false"
            :disabled="deleting"
            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="button"
            @click="confirmDelete"
            :disabled="deleting"
            class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/25 transition-all cursor-pointer flex items-center gap-1.5 disabled:opacity-50"
          >
            <Trash2 class="w-3.5 h-3.5" />
            <span>{{ deleting ? 'Eliminando...' : 'Eliminar' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Interactivo de Sugerencia de Pauta Activa (Detectada tras sincronización) -->
    <div
      v-if="sugerenciaPautaModal?.visible"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
      @click.self="sugerenciaPautaModal = null"
    >
      <div class="bg-white dark:bg-slate-900 border border-violet-500/40 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative">
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-2xl bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20 flex items-center justify-center shrink-0">
            <DollarSign class="w-5 h-5" />
          </div>
          <div class="space-y-1">
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
              <span>🎯 Sospecha de Pauta Activa</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
              {{ sugerenciaPautaModal.motivo }}
            </p>
          </div>
        </div>

        <div class="p-3 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-xs text-violet-900 dark:text-violet-200 leading-relaxed">
          Esta publicación estaba registrada como <strong>Orgánica Pura</strong>. Si está en circulación paga, convertirla a <strong>Orgánica Impulsada (Boosted Post)</strong> registrará el punto de inflexión para el cálculo de ROI.
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="sugerenciaPautaModal = null; router.reload({ preserveScroll: true })"
            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            Mantener Orgánica
          </button>
          <button
            type="button"
            @click="convertirAPostImpulsado"
            class="px-4 py-2 rounded-xl text-xs font-bold bg-violet-600 hover:bg-violet-500 text-white shadow-md shadow-violet-600/25 transition-all cursor-pointer flex items-center gap-1.5"
          >
            <Sparkles class="w-3.5 h-3.5" />
            <span>Convertir a Boosted Post</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
