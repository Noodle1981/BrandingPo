<script setup>
import { ref } from 'vue';
import {
  Heart,
  MessageCircle,
  Repeat,
  Send,
  Eye,
  Bookmark,
  Sparkles,
} from '@lucide/vue';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
  editForm: {
    type: Object,
    required: true,
  },
  isInstagram: {
    type: Boolean,
    default: false,
  },
  isFacebook: {
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
  isLinkedIn: {
    type: Boolean,
    default: false,
  },
  editTotalReacciones: {
    type: Number,
    default: 0,
  },
});

const { formatNumber } = useFormatters();
const mostrarDesgloseEmojis = ref(false);
</script>

<template>
  <div>
    <!-- 6a. PANEL DE INTERACCIONES NATIVAS DE INSTAGRAM -->
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
        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center">
          <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1 flex items-center justify-center gap-1">
            <Eye class="w-3 h-3" />
            <span>Reproducciones / Vistas</span>
          </label>
          <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
        </div>

        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs text-center">
          <label class="block text-[10px] uppercase font-bold text-amber-500 mb-1 flex items-center justify-center gap-1" title="Métrica privada / seguimiento interno de la campaña">
            <Bookmark class="w-3 h-3 fill-amber-500" />
            <span>Guardado Propio (Auditoría)</span>
          </label>
          <input v-model.number="editForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
        </div>
      </div>
    </div>

    <!-- 6b. PANEL PARA FACEBOOK (MÉTRICAS UNIFICADAS) -->
    <div v-else-if="isFacebook" class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
      <div class="flex items-center justify-between text-xs font-bold">
        <span class="text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
          <Sparkles class="w-3.5 h-3.5 text-blue-500" />
          <span>Métricas de Facebook</span>
        </span>
        <div class="flex items-center gap-2">
          <span class="text-cyan-500 font-mono text-xs font-bold">Total: {{ formatNumber(editTotalReacciones) }} Reacciones</span>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-blue-600 dark:text-blue-400 mb-1">👍/❤️ Reacciones</label>
          <input v-model.number="editForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-blue-500 mb-1">💬 Comentarios</label>
          <input v-model.number="editForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-1">🔄 Shares</label>
          <input v-model.number="editForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-1">👁️ Plays / Vistas</label>
          <input v-model.number="editForm.total_vistas" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
      </div>

      <!-- Desglose Opcional de Emojis -->
      <div class="pt-1 border-t border-slate-200/60 dark:border-slate-800/60">
        <button
          type="button"
          @click="mostrarDesgloseEmojis = !mostrarDesgloseEmojis"
          class="text-[11px] font-semibold text-slate-500 hover:text-cyan-500 dark:text-slate-400 flex items-center gap-1 transition-colors cursor-pointer"
        >
          <span>{{ mostrarDesgloseEmojis ? 'Ocultar desglose detallado de emojis ▲' : 'Desglose detallado por emoji (opcional) ▼' }}</span>
        </button>

        <div v-if="mostrarDesgloseEmojis" class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 font-mono text-center mt-2 animate-fade-in">
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
  </div>
</template>
