<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Eye, MessageCircle, Share2, Sparkles, DollarSign, Star, Edit3, Trash2, X, Check, Link2, Activity } from '@lucide/vue';
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
  }
});

const showComments = ref(false);
const isEditing = ref(false);

const parseReacciones = (r, totalLikes = 0) => {
  if (r && typeof r === 'object') {
    return {
      me_gusta: Number(r.me_gusta ?? Math.round(totalLikes * 0.7)),
      me_encanta: Number(r.me_encanta ?? Math.round(totalLikes * 0.2)),
      me_importa: Number(r.me_importa ?? 0),
      me_divierte: Number(r.me_divierte ?? 0),
      me_asombra: Number(r.me_asombra ?? 0),
      me_entristece: Number(r.me_entristece ?? 0),
      me_enoja: Number(r.me_enoja ?? Math.round(totalLikes * 0.1)),
    };
  }
  return {
    me_gusta: Math.round(totalLikes * 0.7),
    me_encanta: Math.round(totalLikes * 0.2),
    me_importa: 0,
    me_divierte: 0,
    me_asombra: 0,
    me_entristece: 0,
    me_enoja: Math.round(totalLikes * 0.1),
  };
};

const initialReacciones = parseReacciones(props.post.reacciones_detalladas, props.post.total_likes);

const editForm = useForm({
  contenido_resumen: props.post.contenido_resumen || '',
  url_post: props.post.url_post || '',
  media_url: props.post.media_url || '',
  tipo_formato: props.post.tipo_formato || 'Reel',
  tipo_pauta: props.post.tipo_pauta || 'organico',
  monto_invertido_pauta: props.post.monto_invertido_pauta || 0,
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
  termometro_humor_social: props.post.termometro_humor_social || 4,
});

const editTotalReacciones = computed(() => {
  return Number(editForm.me_gusta || 0) +
    Number(editForm.me_encanta || 0) +
    Number(editForm.me_importa || 0) +
    Number(editForm.me_divierte || 0) +
    Number(editForm.me_asombra || 0) +
    Number(editForm.me_entristece || 0) +
    Number(editForm.me_enoja || 0);
});

watch(editTotalReacciones, (val) => {
  editForm.total_likes = val;
});

const editAiSentiment = computed(() => {
  const tot = editTotalReacciones.value;
  if (tot === 0) return { aprobacion: 0, label: 'Sin datos', isCrisis: false };
  const pos = Number(editForm.me_gusta || 0) + Number(editForm.me_encanta || 0) + Number(editForm.me_importa || 0);
  const neg = Number(editForm.me_enoja || 0) + Number(editForm.me_entristece || 0);
  const ratio = Math.round(((pos - neg) / tot) * 100);
  const isCrisis = (Number(editForm.me_enoja || 0) / tot) >= 0.15;
  return { aprobacion: ratio, isCrisis };
});

const openEditModal = () => {
  const r = parseReacciones(props.post.reacciones_detalladas, props.post.total_likes);
  editForm.contenido_resumen = props.post.contenido_resumen || '';
  editForm.url_post = props.post.url_post || '';
  editForm.media_url = props.post.media_url || '';
  editForm.tipo_formato = props.post.tipo_formato || 'Reel';
  editForm.tipo_pauta = props.post.tipo_pauta || 'organico';
  editForm.monto_invertido_pauta = props.post.monto_invertido_pauta || 0;
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
  editForm.termometro_humor_social = props.post.termometro_humor_social || 4;
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

const formatos = ['Reel', 'Video', 'Foto', 'Carrusel', 'Tweet', 'Hilo/Thread', 'Shorts', 'Articulo'];
</script>

<template>
  <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs hover:shadow-md dark:shadow-none hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200 relative">
    <!-- Header -->
    <div class="p-4 sm:p-5 flex items-start justify-between gap-3 border-b border-slate-100 dark:border-slate-800/80">
      <div class="flex items-center gap-3">
        <!-- Avatar -->
        <div class="relative">
          <img
            :src="post.candidato?.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.candidato?.nombre_completo || 'Candidato')}&background=0f172a&color=06b6d4`"
            :alt="post.candidato?.nombre_completo"
            class="w-11 h-11 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 shadow-xs"
          />
          <div
            v-if="post.candidato?.es_propio"
            class="absolute -bottom-1 -right-1 bg-cyan-500 text-slate-950 p-0.5 rounded-full ring-2 ring-white dark:ring-slate-900"
            title="Perfil Propio"
          >
            <Sparkles class="w-3 h-3 fill-current" />
          </div>
        </div>

        <!-- Candidate Info -->
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm sm:text-base leading-tight">
              {{ post.candidato?.nombre_completo || 'Candidato' }}
            </h4>
            <Badge
              v-if="post.candidato?.estado_politico"
              variant="estado"
              :value="post.candidato?.estado_politico"
              size="sm"
            />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            {{ post.perfil_social?.handle_usuario || '@cuenta' }} &bull; {{ post.fecha_relativa || post.fecha_publicacion }}
          </p>
        </div>
      </div>

      <!-- Social Network, Pauta Badges & Edit Button -->
      <div class="flex items-center gap-2">
        <div class="flex flex-col items-end gap-1.5">
          <Badge :variant="post.plataforma || post.perfil_social?.plataforma || 'facebook'" size="sm" />
          <Badge
            variant="pauta"
            :value="post.tipo_pauta || 'organico'"
            size="sm"
          />
        </div>

        <!-- Edit & Delete Buttons -->
        <div v-if="canWrite" class="flex items-center gap-1 ml-1">
          <button
            type="button"
            @click="openEditModal"
            class="p-1.5 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            title="Editar publicación y conteo de emociones"
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
    <div class="p-4 sm:p-5">
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

    <!-- Reactions & Metrics Bar (Emoji por Emoji Cuantificado) -->
    <div class="px-4 sm:px-5 py-3 bg-slate-50/70 dark:bg-slate-950/50 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-2 text-xs text-slate-600 dark:text-slate-400">
      <!-- Detailed Emoji Breakdown -->
      <div class="flex items-center gap-2 flex-wrap">
        <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-2.5 py-1 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-[11px] shadow-2xs">
          <span class="font-bold text-slate-800 dark:text-slate-200 mr-1">{{ formatNumber(post.total_likes || 0) }}</span>
          <span v-if="currentReacciones.me_gusta" title="Me gusta" class="inline-flex items-center gap-0.5">👍 {{ formatNumber(currentReacciones.me_gusta) }}</span>
          <span v-if="currentReacciones.me_encanta" title="Me encanta" class="inline-flex items-center gap-0.5 text-rose-500">❤️ {{ formatNumber(currentReacciones.me_encanta) }}</span>
          <span v-if="currentReacciones.me_importa" title="Me importa" class="inline-flex items-center gap-0.5 text-amber-500">🥰 {{ formatNumber(currentReacciones.me_importa) }}</span>
          <span v-if="currentReacciones.me_divierte" title="Me divierte" class="inline-flex items-center gap-0.5 text-amber-400">😂 {{ formatNumber(currentReacciones.me_divierte) }}</span>
          <span v-if="currentReacciones.me_asombra" title="Me asombra" class="inline-flex items-center gap-0.5 text-blue-400">😮 {{ formatNumber(currentReacciones.me_asombra) }}</span>
          <span v-if="currentReacciones.me_entristece" title="Me entristece" class="inline-flex items-center gap-0.5 text-blue-500">😢 {{ formatNumber(currentReacciones.me_entristece) }}</span>
          <span v-if="currentReacciones.me_enoja" title="Me enoja" class="inline-flex items-center gap-0.5 text-rose-600 font-bold">😡 {{ formatNumber(currentReacciones.me_enoja) }}</span>
        </div>

        <span
          v-if="post.sentimiento_predominante"
          class="text-[10px] font-bold px-2 py-0.5 rounded-md font-mono uppercase"
          :class="{
            'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': post.sentimiento_predominante === 'positivo',
            'bg-slate-500/15 text-slate-600 dark:text-slate-400': post.sentimiento_predominante === 'neutro',
            'bg-rose-500/15 text-rose-600 dark:text-rose-400': post.sentimiento_predominante === 'negativo',
          }"
        >
          IA: {{ post.sentimiento_predominante }}
        </span>
      </div>

      <!-- Quick Metrics: Views, Comments, Shares -->
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-1" title="Visualizaciones">
          <Eye class="w-4 h-4 text-cyan-500" />
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

        <div class="flex items-center gap-1" title="Compartidos">
          <Share2 class="w-4 h-4 text-emerald-500" />
          <span class="font-mono font-medium">{{ formatNumber(post.total_compartidos || 0) }}</span>
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
            :class="star <= (post.termometro_humor_social || 3) ? 'fill-amber-400' : 'text-slate-300 dark:text-slate-600'"
          />
          <span class="ml-1 font-mono font-bold text-slate-700 dark:text-slate-300">
            {{ post.termometro_humor_social || 3 }}/5
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

    <!-- Edit Modal Dialog (Emoji por Emoji) -->
    <div
      v-if="isEditing"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <div class="flex items-center gap-2">
            <Edit3 class="w-5 h-5 text-cyan-500" />
            <h3 class="font-bold text-base text-slate-900 dark:text-slate-100">
              Editar Publicación & Emociones
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
          <!-- URL / Link -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Enlace del Post / Video (URL)
            </label>
            <div class="relative">
              <input
                v-model="editForm.url_post"
                type="url"
                placeholder="https://..."
                class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 font-mono focus:ring-2 focus:ring-cyan-500"
              />
              <Link2 class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
            </div>
          </div>

          <!-- Text Content -->
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

          <!-- Formato & Pauta -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Formato
              </label>
              <select
                v-model="editForm.tipo_formato"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              >
                <option v-for="f in formatos" :key="f" :value="f">{{ f }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Distribución
              </label>
              <select
                v-model="editForm.tipo_pauta"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              >
                <option value="organico">Orgánico</option>
                <option value="pauta_paga">Pauta Paga</option>
              </select>
            </div>
          </div>

          <!-- Inversión si es pauta paga -->
          <div v-if="editForm.tipo_pauta === 'pauta_paga'">
            <label class="block text-xs font-bold text-violet-600 dark:text-violet-400 mb-1">
              Monto Invertido en Pauta ($ ARS)
            </label>
            <input
              v-model.number="editForm.monto_invertido_pauta"
              type="number"
              min="0"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-violet-600 dark:text-violet-400"
            />
          </div>

          <!-- Reacciones Emoji por Emoji -->
          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
            <div class="flex items-center justify-between text-xs font-bold">
              <span class="text-slate-700 dark:text-slate-300">Conteo de Emociones (👍 ❤️ 🥰 😂 😮 😢 😡)</span>
              <span class="text-cyan-500 font-mono">Total: {{ formatNumber(editTotalReacciones) }}</span>
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
          </div>

          <!-- Views, Comments, Shares -->
          <div class="grid grid-cols-3 gap-2.5">
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Vistas</label>
              <input
                v-model.number="editForm.total_vistas"
                type="number"
                min="0"
                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Comentarios</label>
              <input
                v-model.number="editForm.total_comentarios"
                type="number"
                min="0"
                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Shares</label>
              <input
                v-model.number="editForm.total_compartidos"
                type="number"
                min="0"
                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono"
              />
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
