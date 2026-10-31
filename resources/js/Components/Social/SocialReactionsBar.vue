<script setup>
import {
  Heart,
  Flame,
  Sparkles,
  Target,
  AlertCircle,
  Eye,
  MessageCircle,
  Repeat,
  Send,
  Share2,
  Bookmark
} from '@lucide/vue';
import { useFormatters } from '../../composables/useFormatters';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  isInstagram: {
    type: Boolean,
    default: false,
  },
  isTikTok: {
    type: Boolean,
    default: false,
  },
  isTwitter: {
    type: Boolean,
    default: false,
  },
  isThreads: {
    type: Boolean,
    default: false,
  },
  isYouTube: {
    type: Boolean,
    default: false,
  },
  hasEmotionalBreakdown: {
    type: Boolean,
    default: false,
  },
  currentReacciones: {
    type: Object,
    default: () => ({}),
  },
  cardPautaStyles: {
    type: Object,
    default: () => ({}),
  },
  isRival: {
    type: Boolean,
    default: false,
  },
  scoreImpacto: {
    type: Number,
    default: 0,
  },
  showComments: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['toggle-comments']);
const { formatNumber } = useFormatters();
</script>

<template>
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
      <div v-else class="flex items-center gap-2 bg-white dark:bg-slate-900 px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-[11px] shadow-2xs">
        <span class="inline-flex items-center gap-1.5 font-bold text-blue-600 dark:text-blue-400">
          <span class="text-xs">👍/❤️</span>
          <span class="text-slate-800 dark:text-slate-200">{{ formatNumber(post.total_likes || 0) }}</span>
          <span class="text-slate-400 font-normal text-[10px]">Reacciones</span>
        </span>
        <template v-if="hasEmotionalBreakdown">
          <span v-if="currentReacciones.me_encanta" title="Me encanta" class="inline-flex items-center gap-0.5 text-rose-500">❤️ {{ formatNumber(currentReacciones.me_encanta) }}</span>
          <span v-if="currentReacciones.me_importa" title="Me importa" class="inline-flex items-center gap-0.5 text-amber-500">🥰 {{ formatNumber(currentReacciones.me_importa) }}</span>
          <span v-if="currentReacciones.me_divierte" title="Me divierte" class="inline-flex items-center gap-0.5 text-amber-400">😂 {{ formatNumber(currentReacciones.me_divierte) }}</span>
          <span v-if="currentReacciones.me_asombra" title="Me asombra" class="inline-flex items-center gap-0.5 text-blue-400">😮 {{ formatNumber(currentReacciones.me_asombra) }}</span>
          <span v-if="currentReacciones.me_entristece" title="Me entristece" class="inline-flex items-center gap-0.5 text-blue-500">😢 {{ formatNumber(currentReacciones.me_entristece) }}</span>
          <span v-if="currentReacciones.me_enoja" title="Me enoja" class="inline-flex items-center gap-0.5 text-rose-600 font-bold">😡 {{ formatNumber(currentReacciones.me_enoja) }}</span>
        </template>
      </div>
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

      <!-- ⚠️ Alerta Forense de Bots o Anomalía de Interacciones (Exclusivo para Contrincantes / Oposición) -->
      <div
        v-if="isRival && post.analisis_traccion?.sospecha_de_bots"
        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-violet-500/15 text-violet-600 dark:text-violet-300 border border-violet-500/30 text-[11px] font-bold shadow-2xs"
        :title="`Auditoría Oposición: ${post.analisis_traccion.alertas_forenses.join(' | ')}`"
      >
        <AlertCircle class="w-3.5 h-3.5 text-violet-500 shrink-0" />
        <span>Anomalía / Posible Inflado Rival</span>
      </div>

      <div class="flex items-center gap-1" title="Visualizaciones / Alcance">
        <Eye class="w-4 h-4 text-slate-400 dark:text-slate-500" />
        <span class="font-mono font-medium">{{ formatNumber(post.total_vistas || 0) }}</span>
      </div>

      <button
        type="button"
        @click="emit('toggle-comments')"
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
</template>
