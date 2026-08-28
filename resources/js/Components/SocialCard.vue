<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
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
  AlertCircle
} from '@lucide/vue';
import Badge from './Badge.vue';
import MediaEmbed from './MediaEmbed.vue';

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
const isEditing = ref(false);

const platform = computed(() => (props.post.plataforma || props.post.perfil_social?.plataforma || 'instagram').toLowerCase());
const isInstagram = computed(() => platform.value === 'instagram');
const isFacebook = computed(() => platform.value === 'facebook');
const isThreads = computed(() => platform.value === 'threads');
const isTikTok = computed(() => platform.value === 'tiktok');
const isTwitter = computed(() => platform.value === 'x_twitter' || platform.value === 'twitter');
const isYouTube = computed(() => platform.value === 'youtube');
const isLinkedIn = computed(() => platform.value === 'linkedin');

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

const initialReacciones = parseReacciones(props.post.reacciones_detalladas, props.post.total_likes, props.post.plataforma || props.post.perfil_social?.plataforma);

const editForm = useForm({
  contenido_resumen: props.post.contenido_resumen || '',
  fecha_publicacion: formatDateForInput(props.post.fecha_publicacion, props.post.fecha_publicacion_raw),
  url_post: props.post.url_post || '',
  media_url: props.post.media_url || '',
  tipo_formato: props.post.tipo_formato || (isInstagram.value ? 'Reel' : 'Post'),
  tipo_pauta: props.post.tipo_pauta || 'organico',
  monto_invertido_pauta: props.post.monto_invertido_pauta || 0,
  eje_tematico_id: props.post.eje_tematico_id || props.post.eje_tematico?.id || null,
  total_vistas: props.post.total_vistas || 0,
  me_gusta: initialReacciones.me_gusta,
  me_encanta: initialReacciones.me_encanta,
  me_importa: initialReacciones.me_importa,
  me_divierte: initialReacciones.me_divierte,
  me_asombra: initialReacciones.me_asombra,
  me_entristece: initialReacciones.me_entristece,
  me_enoja: initialReacciones.me_enoja,
  total_likes: props.post.total_likes || 0,
  total_comentarios: props.post.total_comentarios || 0,
  total_compartidos: props.post.total_compartidos || 0,
  total_republicados: props.post.total_republicados || 0,
  total_guardados: props.post.total_guardados || 0,
  termometro_humor_social: props.post.termometro_humor_social || 5,
});

const editTotalReacciones = computed(() => {
  if (isFacebook.value) {
    return Number(editForm.me_gusta || 0) +
      Number(editForm.me_encanta || 0) +
      Number(editForm.me_importa || 0) +
      Number(editForm.me_divierte || 0) +
      Number(editForm.me_asombra || 0) +
      Number(editForm.me_entristece || 0) +
      Number(editForm.me_enoja || 0);
  }
  return Number(editForm.total_likes || 0);
});

watch(editTotalReacciones, (val) => {
  if (isFacebook.value) {
    editForm.total_likes = val;
  }
});

const editAiSentiment = computed(() => {
  const tot = editTotalReacciones.value;
  if (tot === 0) return { aprobacion: 100, label: 'Sin datos', isCrisis: false };
  if (!isFacebook.value) {
    return { aprobacion: 100, isCrisis: false };
  }
  const pos = Number(editForm.me_gusta || 0) + Number(editForm.me_encanta || 0) + Number(editForm.me_importa || 0);
  const neg = Number(editForm.me_enoja || 0) + Number(editForm.me_entristece || 0);
  const ratio = Math.round(((pos - neg) / tot) * 100);
  const isCrisis = (Number(editForm.me_enoja || 0) / tot) >= 0.15;
  return { aprobacion: ratio, isCrisis };
});

const openEditModal = () => {
  const r = parseReacciones(props.post.reacciones_detalladas, props.post.total_likes, platform.value);
  editForm.contenido_resumen = props.post.contenido_resumen || '';
  editForm.fecha_publicacion = formatDateForInput(props.post.fecha_publicacion, props.post.fecha_publicacion_raw);
  editForm.url_post = props.post.url_post || '';
  editForm.media_url = props.post.media_url || '';
  editForm.tipo_formato = props.post.tipo_formato || (isInstagram.value ? 'Reel' : 'Post');
  editForm.tipo_pauta = props.post.tipo_pauta || 'organico';
  editForm.monto_invertido_pauta = props.post.monto_invertido_pauta || 0;
  editForm.eje_tematico_id = props.post.eje_tematico_id || props.post.eje_tematico?.id || null;
  editForm.total_vistas = props.post.total_vistas || 0;
  editForm.me_gusta = r.me_gusta;
  editForm.me_encanta = r.me_encanta;
  editForm.me_importa = r.me_importa;
  editForm.me_divierte = r.me_divierte;
  editForm.me_asombra = r.me_asombra;
  editForm.me_entristece = r.me_entristece;
  editForm.me_enoja = r.me_enoja;
  editForm.total_likes = props.post.total_likes || 0;
  editForm.total_comentarios = props.post.total_comentarios || 0;
  editForm.total_compartidos = props.post.total_compartidos || 0;
  editForm.total_republicados = props.post.total_republicados || 0;
  editForm.total_guardados = props.post.total_guardados || 0;
  editForm.termometro_humor_social = props.post.termometro_humor_social || 5;
  isEditing.value = true;
};

const closeEditModal = () => {
  isEditing.value = false;
};

const saveEdit = () => {
  editForm.put(`/publicaciones/${props.post.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      isEditing.value = false;
    }
  });
};

const deletePost = () => {
  if (confirm('¿Estás seguro de que deseas eliminar esta publicación?')) {
    router.delete(`/publicaciones/${props.post.id}`, {
      preserveScroll: true,
    });
  }
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

const formatos = [
  { value: 'Reel', label: '🎬 Reel / Video Vertical' },
  { value: 'Foto', label: '🖼️ Foto / Imagen' },
  { value: 'Carrusel', label: '📚 Carrusel / Galería' },
  { value: 'Story', label: '⚡ Story / Historia' },
  { value: 'Collab', label: '🤝 Collab / Co-autoría' },
  { value: 'Video', label: '📹 Video en Feed' },
  { value: 'Shorts', label: '⚡ Shorts' },
  { value: 'Tweet', label: '🐦 Tweet / Post' },
  { value: 'Post', label: '📄 Post Estándar' },
  { value: 'Live', label: '🎙️ Transmisión En Vivo' },
];

const tiposPauta = [
  { value: 'organico', label: '🌱 Orgánica Pura' },
  { value: 'organico_impulsado', label: '🚀 Post Impulsado (Boosted)' },
  { value: 'pauta_paga', label: '🎯 Dark Post / Anuncio Directo' },
  { value: 'colaboracion_pagada', label: '🌟 Colaboración Pagada / Influencer' },
];
</script>

<template>
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs hover:shadow-md dark:shadow-none hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200 relative">
    <!-- Header -->
    <div class="p-4 sm:p-5 flex items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80">
      <!-- Left: Avatar + Author Info -->
      <div class="flex items-center gap-3 min-w-0">
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

        <!-- Account Handle + Subtitle Details -->
        <div class="min-w-0">
          <h4 class="font-extrabold font-mono text-slate-900 dark:text-slate-100 text-sm sm:text-base leading-tight truncate">
            {{ post.perfil_social?.handle_usuario || '@cuenta' }}
          </h4>

          <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-2 mt-0.5 flex-wrap">
            <span class="font-sans">{{ post.fecha_relativa || post.fecha_publicacion }}</span>
            <span
              v-if="isPostInActiveWindow"
              class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-mono font-bold"
              title="En ventana de sincronización activa (menos de 15 días)"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
              <span>Activo (15d)</span>
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-500 text-[10px] font-mono"
              title="Métrica histórica consolidada."
            >
              <span>🔒 Consolidado</span>
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Social Network, Pauta Badges & Actions -->
      <div class="flex items-center gap-2 shrink-0">
        <div class="flex items-center gap-1.5">
          <Badge :variant="post.plataforma || post.perfil_social?.plataforma || 'facebook'" size="sm" />
          <Badge
            variant="pauta"
            :value="post.tipo_pauta || 'organico'"
            size="sm"
          />
        </div>

        <!-- Edit & Delete Buttons -->
        <div v-if="canWrite" class="flex items-center gap-0.5 ml-1 pl-1 border-l border-slate-200 dark:border-slate-800">
          <button
            type="button"
            @click="openEditModal"
            class="p-1.5 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            title="Editar publicación y métricas"
          >
            <Edit3 class="w-4 h-4" />
          </button>
          <button
            type="button"
            @click="deletePost"
            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            title="Eliminar publicación"
          >
            <Trash2 class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Post Content Body -->
    <div class="p-4 sm:p-5 space-y-3">
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

      <p class="text-slate-800 dark:text-slate-200 text-sm sm:text-base leading-relaxed whitespace-pre-line">
        {{ post.contenido_resumen }}
      </p>

      <!-- Media Embed / Preview -->
      <div class="mt-3.5 relative">
        <MediaEmbed
          :url="post.url_post"
          :media-url="post.media_url"
          :formato="post.tipo_formato || 'Post'"
          :plataforma="post.plataforma || post.perfil_social?.plataforma || 'facebook'"
        />

        <!-- Paid Ads Overlay Tag if with budget -->
        <div
          v-if="post.tipo_pauta === 'pauta_paga' && post.monto_invertido_pauta"
          class="absolute top-2.5 right-2.5 bg-violet-600/90 backdrop-blur-xs text-white text-xs font-semibold px-2.5 py-1 rounded-lg flex items-center gap-1 shadow-md z-10"
        >
          <DollarSign class="w-3.5 h-3.5" />
          <span>Invertido: {{ formatCurrency(post.monto_invertido_pauta) }}</span>
        </div>
      </div>

      <!-- Accompanying Figures / Alliances -->
      <div v-if="post.figuras_acompanantes && post.figuras_acompanantes.length" class="mt-3 flex items-center gap-1.5 flex-wrap">
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
        <!-- 🔥 Score de Impacto Orgánico Ponderado (War Room) -->
        <div
          class="flex items-center gap-1 px-2 py-0.5 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-mono font-bold text-xs"
          :title="`Score de Impacto Orgánico: ${scoreImpacto} pts (${post.total_likes || 0} Likes [x1] + ${post.total_comentarios || 0} Comentarios [x3] + ${post.total_compartidos || 0} Compartidos [x5] + ${post.total_republicados || 0} Republicaciones [x10])`"
        >
          <Flame class="w-3.5 h-3.5 fill-current text-cyan-500" />
          <span>{{ formatNumber(scoreImpacto) }} <span class="text-[10px] font-normal opacity-80">pts</span></span>
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

    <!-- Edit Modal Dialog (Adaptativo por Red Social) -->
    <div
      v-if="isEditing"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <div class="flex items-center gap-2">
            <Edit3 class="w-5 h-5 text-cyan-500" />
            <h3 class="font-bold text-base text-slate-900 dark:text-slate-100">
              Editar Publicación & Métricas ({{ platform.toUpperCase() }})
            </h3>
          </div>
          <button
            type="button"
            @click="closeEditModal"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="saveEdit" class="space-y-4">
          <!-- 1. Fecha & Formato -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-cyan-500" />
                <span>Fecha y Hora de Publicación *</span>
              </label>
              <input
                v-model="editForm.fecha_publicacion"
                type="datetime-local"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 font-mono focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Tipo de Formato *
              </label>
              <select
                v-model="editForm.tipo_formato"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500 font-semibold"
              >
                <option v-for="f in formatos" :key="f.value" :value="f.value">{{ f.label }}</option>
              </select>
            </div>
          </div>

          <!-- 2. URL / Enlace Oficial -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
              <Link2 class="w-3.5 h-3.5 text-cyan-500" />
              <span>Enlace Oficial del Post / Reel (URL)</span>
            </label>
            <div class="relative">
              <input
                v-model="editForm.url_post"
                type="url"
                placeholder="https://www.instagram.com/p/..."
                class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 font-mono focus:ring-2 focus:ring-cyan-500"
              />
              <Link2 class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
            </div>
          </div>

          <!-- 3. Tipo de Difusión & Pauta -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center gap-1.5">
                <DollarSign class="w-3.5 h-3.5 text-cyan-500" />
                <span>Estrategia de Difusión / Pauta</span>
              </label>
              <select
                v-model="editForm.tipo_pauta"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500 font-semibold"
              >
                <option v-for="tp in tiposPauta" :key="tp.value" :value="tp.value">
                  {{ tp.label }}
                </option>
              </select>
            </div>

            <!-- Inversión si no es orgánico -->
            <div v-if="editForm.tipo_pauta !== 'organico'">
              <label class="block text-xs font-bold text-violet-600 dark:text-violet-400 mb-1">
                Monto Invertido en Pauta ($ ARS/USD)
              </label>
              <input
                v-model.number="editForm.monto_invertido_pauta"
                type="number"
                min="0"
                placeholder="ej. 25000"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-violet-500/40 text-xs font-mono font-bold text-violet-600 dark:text-violet-400 focus:ring-2 focus:ring-violet-500"
              />
            </div>
          </div>

          <!-- 4. Eje Temático de Campaña -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <Target class="w-3.5 h-3.5 text-cyan-500" />
                <span>Eje Temático de Campaña</span>
              </span>
              <span class="text-[10px] text-slate-400 font-mono">5 Pilares & 16 Sub-ejes</span>
            </label>
            <select
              v-model="editForm.eje_tematico_id"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-semibold focus:ring-2 focus:ring-cyan-500"
            >
              <option :value="null">-- Sin Eje Temático Asignado --</option>
              <optgroup v-for="(ejesInGroup, pilar) in groupedEjes" :key="pilar" :label="pilar">
                <option v-for="eje in ejesInGroup" :key="eje.id" :value="eje.id">
                  • {{ eje.nombre }}
                </option>
              </optgroup>
            </select>
          </div>

          <!-- 5. Text Content -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Texto o Resumen del Post *
            </label>
            <textarea
              v-model="editForm.contenido_resumen"
              required
              rows="3"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <!-- 6. PANEL DE INTERACCIONES NATIVAS DE INSTAGRAM -->
          <div v-if="isInstagram" class="p-4 rounded-2xl bg-gradient-to-r from-[#E4405F]/10 via-[#F77737]/5 to-transparent border border-[#E4405F]/20 space-y-3">
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
                <input v-model.number="editForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center justify-center gap-1">
                  <MessageCircle class="w-3 h-3" />
                  <span>Comentarios (💬)</span>
                </label>
                <input v-model.number="editForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1 flex items-center justify-center gap-1">
                  <Repeat class="w-3 h-3" />
                  <span>Republicar (🔁)</span>
                </label>
                <input v-model.number="editForm.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-1 flex items-center justify-center gap-1">
                  <Send class="w-3 h-3" />
                  <span>Compartidos (✈️)</span>
                </label>
                <input v-model.number="editForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 font-mono">
              <!-- Vistas / Reproducciones -->
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center">
                <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1 flex items-center justify-center gap-1">
                  <Eye class="w-3 h-3" />
                  <span>Reproducciones / Vistas</span>
                </label>
                <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
              </div>

              <!-- Item Propio de Auditoría: Guardados -->
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs text-center">
                <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1" title="Métrica privada / seguimiento interno de la campaña">
                  <Bookmark class="w-3 h-3 fill-amber-500" />
                  <span>Guardado Propio (Auditoría)</span>
                </label>
                <input v-model.number="editForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
              </div>
            </div>
          </div>

          <!-- 6b. PANEL PARA FACEBOOK (DESGLOSE MULTI-EMOJI) -->
          <div v-else-if="isFacebook" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between text-xs font-bold">
              <span class="text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <Sparkles class="w-3.5 h-3.5 text-blue-500" />
                <span>Reacciones de Facebook</span>
              </span>
              <div class="flex items-center gap-2">
                <span class="text-cyan-500 font-mono text-[11px]">Total: {{ formatNumber(editTotalReacciones) }}</span>
                <span
                  class="text-[10px] px-2 py-0.5 rounded font-mono font-bold"
                  :class="editAiSentiment.isCrisis ? 'bg-rose-500/15 text-rose-500' : 'bg-emerald-500/15 text-emerald-500'"
                >
                  {{ editAiSentiment.aprobacion }}% Aprobación
                </span>
              </div>
            </div>

            <!-- Aviso Táctico de Carga -->
            <div class="p-2.5 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-800 dark:text-blue-200 text-xs flex items-start gap-2">
              <AlertCircle class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" />
              <div class="leading-snug">
                <span class="font-bold">⚠️ Importante para Facebook:</span>
                Ingresa o actualiza las cantidades de cada emoji (👍 ❤️ 🥰 😂 😮 😢 😡) observadas en la publicación <strong>antes de guardar</strong> para calcular el Índice de Aprobación Neta.
              </div>
            </div>

            <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 font-mono text-center">
              <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <span class="text-xs block">👍</span>
                <input v-model.number="editForm.me_gusta" type="number" min="0" class="w-full text-center text-xs font-bold" />
              </div>
              <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <span class="text-xs block">❤️</span>
                <input v-model.number="editForm.me_encanta" type="number" min="0" class="w-full text-center text-xs font-bold text-rose-500" />
              </div>
              <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <span class="text-xs block">🥰</span>
                <input v-model.number="editForm.me_importa" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-500" />
              </div>
              <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <span class="text-xs block">😂</span>
                <input v-model.number="editForm.me_divierte" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-400" />
              </div>
              <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <span class="text-xs block">😮</span>
                <input v-model.number="editForm.me_asombra" type="number" min="0" class="w-full text-center text-xs font-bold text-blue-400" />
              </div>
              <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <span class="text-xs block">😢</span>
                <input v-model.number="editForm.me_entristece" type="number" min="0" class="w-full text-center text-xs font-bold text-blue-500" />
              </div>
              <div class="p-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <span class="text-xs block">😡</span>
                <input v-model.number="editForm.me_enoja" type="number" min="0" class="w-full text-center text-xs font-bold text-rose-600" />
              </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pt-2">
              <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1">👁️ Plays / Vistas</label>
                <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold" />
              </div>
              <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1">💬 Comentarios</label>
                <input v-model.number="editForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
              </div>
              <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1">🔄 Shares</label>
                <input v-model.number="editForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
              </div>
            </div>
          </div>

          <!-- 6c. PANEL PARA TIKTOK (❤️ Likes, 💬 Comentarios, 🔖 Favoritos, ↗️ Compartidos, 👁️ Plays) -->
          <div v-else-if="isTikTok" class="p-4 rounded-2xl bg-gradient-to-r from-[#00F2FE]/10 via-[#FF004F]/5 to-transparent border border-[#00F2FE]/20 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-sm">🎵</span>
                <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas Nativas de TikTok</span>
              </div>
              <span class="text-[11px] font-mono text-[#00F2FE] font-bold">Video Corto</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-center">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-[#FF004F]/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-[#FF004F] mb-1 flex items-center justify-center gap-1">
                  <Heart class="w-3 h-3 fill-[#FF004F]" />
                  <span>Me gusta (❤️)</span>
                </label>
                <input v-model.number="editForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center justify-center gap-1">
                  <MessageCircle class="w-3 h-3" />
                  <span>Comentarios (💬)</span>
                </label>
                <input v-model.number="editForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1">
                  <Bookmark class="w-3 h-3 fill-amber-500" />
                  <span>Favoritos (🔖)</span>
                </label>
                <input v-model.number="editForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-1 flex items-center justify-center gap-1">
                  <Send class="w-3 h-3" />
                  <span>Compartidos (↗️)</span>
                </label>
                <input v-model.number="editForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>
            </div>

            <!-- Vistas / Plays TikTok -->
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-[#00F2FE]/30 shadow-2xs text-center font-mono">
              <label class="block text-[10px] uppercase font-bold text-[#00F2FE] mb-1 flex items-center justify-center gap-1">
                <Eye class="w-3 h-3" />
                <span>Visualizaciones (Reproducciones / Plays)</span>
              </label>
              <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
            </div>
          </div>

          <!-- 6d. PANEL PARA X / TWITTER Y THREADS (❤️ Likes, 💬 Respuestas, 🔁 Reposts, ↗️ Compartidos, 🔖 Guardados, 👁️ Impresiones) -->
          <div v-else-if="isTwitter || isThreads" class="p-4 rounded-2xl bg-slate-900/50 border border-slate-700/60 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="font-black text-sm text-slate-100 font-mono">{{ isThreads ? '@' : '𝕏' }}</span>
                <span class="font-bold text-xs text-slate-800 dark:text-slate-200">{{ isThreads ? 'Métricas Nativas de Threads' : 'Métricas Nativas de X (Twitter)' }}</span>
              </div>
              <span class="text-[11px] font-mono text-cyan-400 font-bold">{{ isThreads ? 'Feed de Conversación' : 'Timeline Político' }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono text-center">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-rose-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-rose-500 mb-1 flex items-center justify-center gap-1">
                  <Heart class="w-3 h-3 fill-rose-500" />
                  <span>Me gusta (❤️)</span>
                </label>
                <input v-model.number="editForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center justify-center gap-1">
                  <MessageCircle class="w-3 h-3" />
                  <span>Respuestas (💬)</span>
                </label>
                <input v-model.number="editForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1 flex items-center justify-center gap-1">
                  <Repeat class="w-3 h-3" />
                  <span>Reposts (🔁)</span>
                </label>
                <input v-model.number="editForm.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
                <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1">
                  <Bookmark class="w-3 h-3 fill-amber-500" />
                  <span>Guardados (🔖)</span>
                </label>
                <input v-model.number="editForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
              </div>
            </div>

            <!-- Vistas / Impresiones X -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 font-mono">
              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
                <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1 flex items-center justify-center gap-1">
                  <Eye class="w-3 h-3" />
                  <span>Visualizaciones / Impresiones</span>
                </label>
                <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
              </div>

              <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs text-center font-mono">
                <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-1 flex items-center justify-center gap-1">
                  <Send class="w-3 h-3" />
                  <span>Compartidos / DM (↗️)</span>
                </label>
                <input v-model.number="editForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
              </div>
            </div>
          </div>

          <!-- 6e. PANEL PARA OTRAS REDES (YOUTUBE, LINKEDIN) -->
          <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 font-mono">
            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
              <label class="block text-[10px] uppercase font-bold text-rose-500 mb-1">
                {{ isYouTube || isLinkedIn ? '👍 Likes' : '❤️ Likes' }}
              </label>
              <input
                v-model.number="editForm.total_likes"
                type="number"
                min="0"
                class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-center font-bold text-slate-800 dark:text-slate-100"
              />
            </div>

            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
              <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1">
                💬 Comentarios
              </label>
              <input
                v-model.number="editForm.total_comentarios"
                type="number"
                min="0"
                class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-center font-bold text-blue-500"
              />
            </div>

            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
              <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1">
                🔄 Compartidos
              </label>
              <input
                v-model.number="editForm.total_compartidos"
                type="number"
                min="0"
                class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-center font-bold text-slate-700 dark:text-slate-300"
              />
            </div>

            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-amber-500/30">
              <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1">
                🔖 Guardados
              </label>
              <input
                v-model.number="editForm.total_guardados"
                type="number"
                min="0"
                class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-amber-500/30 text-xs text-center font-bold text-amber-500"
              />
            </div>
          </div>

          <!-- 7. Termómetro de Humor Social -->
          <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1">
              <Star class="w-3.5 h-3.5 text-amber-400 fill-amber-400" />
              <span>Termómetro de Humor Social:</span>
            </span>
            <div class="flex items-center gap-1.5">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                @click="editForm.termometro_humor_social = star"
                class="p-1 text-amber-400 hover:scale-125 transition-transform cursor-pointer"
              >
                <Star
                  class="w-4 h-4"
                  :class="star <= editForm.termometro_humor_social ? 'fill-amber-400' : 'text-slate-300 dark:text-slate-600'"
                />
              </button>
              <span class="ml-1 text-xs font-mono font-bold text-amber-500">
                {{ editForm.termometro_humor_social }}/5
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeEditModal"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="editForm.processing"
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-sm flex items-center gap-1.5 cursor-pointer"
            >
              <Check class="w-3.5 h-3.5" />
              <span>{{ editForm.processing ? 'Guardando...' : 'Guardar Cambios' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
