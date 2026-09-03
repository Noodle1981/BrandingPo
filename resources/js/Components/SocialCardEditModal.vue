<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
  Edit3,
  X,
  Calendar,
  Link2,
  DollarSign,
  Sparkles,
  Target,
  Heart,
  MessageCircle,
  Repeat,
  Send,
  Eye,
  Bookmark,
  AlertCircle,
  Star,
  Check
} from '@lucide/vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  post: {
    type: Object,
    required: true,
  },
  groupedEjes: {
    type: Object,
    default: () => ({}),
  }
});

const emit = defineEmits(['close', 'saved']);

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
  if (/^\d{4}-\d{2}-\d{2}/.test(str)) {
    return str.slice(0, 16);
  }
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

const initialReacciones = parseReacciones(props.post.reacciones_detalladas, props.post.total_likes, platform.value);

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

const populateFormFromPost = () => {
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
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    populateFormFromPost();
  }
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

const close = () => {
  emit('close');
};

const saveEdit = () => {
  editForm.put(`/publicaciones/${props.post.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved');
      emit('close');
    }
  });
};

const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};

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
  <div
    v-if="show"
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
          @click="close"
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
              Monto Invertido en Pauta ($ ARS — Pesos)
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

        <!-- Alerta explicativa de snapshot de corte al cambiar de pauta -->
        <div
          v-if="editForm.tipo_pauta !== (post.tipo_pauta || 'organico')"
          class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-900 dark:text-cyan-200 text-xs space-y-1.5"
        >
          <div class="flex items-center gap-1.5 font-bold">
            <Sparkles class="w-4 h-4 text-cyan-500 shrink-0" />
            <span>Snapshot automático de corte al guardar:</span>
          </div>
          <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-300 font-sans">
            Al pasar a <strong>{{ tiposPauta.find(t => t.value === editForm.tipo_pauta)?.label || editForm.tipo_pauta }}</strong>, el sistema registrará un snapshot con la base actual (<strong>{{ formatNumber(post.total_likes || 0) }} likes</strong>) y seguidores del canal. Así, todas las nuevas reacciones tras este momento quedarán atribuidas con precisión al impacto de la pauta.
          </p>
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

        <!-- 6c. PANEL PARA TIKTOK -->
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

          <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-[#00F2FE]/30 shadow-2xs text-center font-mono">
            <label class="block text-[10px] uppercase font-bold text-[#00F2FE] mb-1 flex items-center justify-center gap-1">
              <Eye class="w-3 h-3" />
              <span>Visualizaciones (Reproducciones / Plays)</span>
            </label>
            <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
          </div>
        </div>

        <!-- 6d. PANEL PARA X / TWITTER Y THREADS -->
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

            <div v-if="!isThreads" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
              <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1">
                <Bookmark class="w-3 h-3 fill-amber-500" />
                <span>Guardados (🔖)</span>
              </label>
              <input v-model.number="editForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
            </div>
            <div v-else class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
              <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-1 flex items-center justify-center gap-1">
                <Send class="w-3 h-3" />
                <span>Compartir (✈️)</span>
              </label>
              <input v-model.number="editForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-indigo-600 dark:text-indigo-400" />
            </div>
          </div>

          <div :class="isThreads ? 'grid grid-cols-1 gap-2.5 font-mono' : 'grid grid-cols-1 sm:grid-cols-2 gap-2.5 font-mono'">
            <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
              <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1 flex items-center justify-center gap-1">
                <Eye class="w-3 h-3" />
                <span>Visualizaciones / Impresiones</span>
              </label>
              <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
            </div>

            <div v-if="!isThreads" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs text-center font-mono">
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
            @click="close"
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
</template>
