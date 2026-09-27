<script setup>
import {
  Heart,
  MessageCircle,
  Repeat,
  Send,
  Eye,
  Bookmark,
  Sparkles
} from '@lucide/vue';

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  netApproval: {
    type: Number,
    default: 100,
  },
});
</script>

<template>
  <div class="space-y-3.5">
    <!-- CASO 1: INSTAGRAM -->
    <div v-if="form.plataforma === 'instagram'" class="p-4 rounded-2xl bg-gradient-to-r from-[#E4405F]/10 via-[#F77737]/5 to-transparent border border-[#E4405F]/20 space-y-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Heart class="w-4 h-4 text-[#E4405F] fill-[#E4405F]" />
          <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de Instagram</span>
        </div>
        <span class="text-[10px] font-mono text-[#E4405F] font-bold uppercase">Interacciones</span>
      </div>

      <div class="grid grid-cols-2 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-[#E4405F]/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5 flex items-center justify-center gap-1">
            <Heart class="w-3 h-3 fill-rose-500" />
            <span>Me gusta (❤️)</span>
          </label>
          <input v-model.number="form.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
            <MessageCircle class="w-3 h-3" />
            <span>Comentarios (💬)</span>
          </label>
          <input v-model.number="form.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5 flex items-center justify-center gap-1">
            <Repeat class="w-3 h-3" />
            <span>Republicar (🔁)</span>
          </label>
          <input v-model.number="form.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
            <Send class="w-3 h-3" />
            <span>Compartidos (✈️)</span>
          </label>
          <input v-model.number="form.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
            <Eye class="w-3 h-3" />
            <span>Vistas / Plays</span>
          </label>
          <input v-model.number="form.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5 flex items-center justify-center gap-1">
            <Bookmark class="w-3 h-3 fill-amber-500" />
            <span>Guardados (🔖)</span>
          </label>
          <input v-model.number="form.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
        </div>
      </div>
    </div>

    <!-- CASO 2: FACEBOOK -->
    <div v-else-if="form.plataforma === 'facebook'" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
      <div class="flex items-center justify-between">
        <span class="font-bold text-xs text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
          <Sparkles class="w-3.5 h-3.5 text-blue-500" />
          <span>Métricas de Facebook</span>
        </span>
        <span class="font-mono text-cyan-500 font-bold text-xs">Aprobación: {{ netApproval }}%</span>
      </div>

      <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 text-center font-mono">
        <div>
          <span class="text-xs">👍</span>
          <input v-model.number="form.reacciones_detalladas.like" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
        </div>
        <div>
          <span class="text-xs">❤️</span>
          <input v-model.number="form.reacciones_detalladas.love" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
        </div>
        <div>
          <span class="text-xs">🥰</span>
          <input v-model.number="form.reacciones_detalladas.care" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
        </div>
        <div>
          <span class="text-xs">😂</span>
          <input v-model.number="form.reacciones_detalladas.haha" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
        </div>
        <div>
          <span class="text-xs">😮</span>
          <input v-model.number="form.reacciones_detalladas.wow" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
        </div>
        <div>
          <span class="text-xs">😢</span>
          <input v-model.number="form.reacciones_detalladas.sad" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
        </div>
        <div>
          <span class="text-xs">😡</span>
          <input v-model.number="form.reacciones_detalladas.angry" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
        </div>
      </div>

      <div class="grid grid-cols-3 gap-2 pt-1 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5">👁️ Vistas</label>
          <input v-model.number="form.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5">💬 Comentarios</label>
          <input v-model.number="form.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
          <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5">🔄 Shares</label>
          <input v-model.number="form.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
      </div>
    </div>

    <!-- CASO 3: TIKTOK -->
    <div v-else-if="form.plataforma === 'tiktok'" class="p-4 rounded-2xl bg-gradient-to-r from-[#00F2FE]/10 via-[#FF004F]/5 to-transparent border border-[#00F2FE]/20 space-y-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="text-xs">🎵</span>
          <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de TikTok</span>
        </div>
        <span class="text-[10px] font-mono text-[#00F2FE] font-bold uppercase">Video Corto</span>
      </div>

      <div class="grid grid-cols-2 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-[#FF004F]/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-[#FF004F] mb-0.5 flex items-center justify-center gap-1">
            <Heart class="w-3 h-3 fill-[#FF004F]" />
            <span>Me gusta (❤️)</span>
          </label>
          <input v-model.number="form.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
            <MessageCircle class="w-3 h-3" />
            <span>Comentarios (💬)</span>
          </label>
          <input v-model.number="form.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5 flex items-center justify-center gap-1">
            <Bookmark class="w-3 h-3 fill-amber-500" />
            <span>Favoritos (🔖)</span>
          </label>
          <input v-model.number="form.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
            <Send class="w-3 h-3" />
            <span>Compartidos (↗️)</span>
          </label>
          <input v-model.number="form.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>
      </div>

      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-[#00F2FE]/30 shadow-2xs text-center font-mono">
        <label class="block text-[10px] uppercase font-bold text-[#00F2FE] mb-0.5 flex items-center justify-center gap-1">
          <Eye class="w-3 h-3" />
          <span>Reproducciones / Plays</span>
        </label>
        <input v-model.number="form.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
      </div>
    </div>

    <!-- CASO 4A: THREADS -->
    <div v-else-if="form.plataforma === 'threads'" class="p-4 rounded-2xl bg-slate-900/50 border border-slate-700/60 space-y-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="font-black text-xs text-slate-100 font-mono">@</span>
          <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de Threads</span>
        </div>
        <span class="text-[10px] font-mono text-cyan-400 font-bold uppercase">Feed de Conversación</span>
      </div>

      <div class="grid grid-cols-2 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-rose-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5 flex items-center justify-center gap-1">
            <Heart class="w-3 h-3 fill-rose-500" />
            <span>Me gusta (❤️)</span>
          </label>
          <input v-model.number="form.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
            <MessageCircle class="w-3 h-3" />
            <span>Respuestas (💬)</span>
          </label>
          <input v-model.number="form.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5 flex items-center justify-center gap-1">
            <Repeat class="w-3 h-3" />
            <span>Reposts (🔁)</span>
          </label>
          <input v-model.number="form.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
            <Send class="w-3 h-3" />
            <span>Compartir (✈️)</span>
          </label>
          <input v-model.number="form.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-indigo-600 dark:text-indigo-400" />
        </div>
      </div>

      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
        <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
          <Eye class="w-3 h-3" />
          <span>Visualizaciones / Vistas</span>
        </label>
        <input v-model.number="form.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
      </div>
    </div>

    <!-- CASO 4B: X / TWITTER -->
    <div v-else-if="form.plataforma === 'x_twitter' || form.plataforma === 'twitter'" class="p-4 rounded-2xl bg-slate-900/50 border border-slate-700/60 space-y-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="font-black text-xs text-slate-100 font-mono">𝕏</span>
          <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de X (Twitter)</span>
        </div>
        <span class="text-[10px] font-mono text-cyan-400 font-bold uppercase">Timeline</span>
      </div>

      <div class="grid grid-cols-2 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-rose-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5 flex items-center justify-center gap-1">
            <Heart class="w-3 h-3 fill-rose-500" />
            <span>Me gusta (❤️)</span>
          </label>
          <input v-model.number="form.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
            <MessageCircle class="w-3 h-3" />
            <span>Respuestas (💬)</span>
          </label>
          <input v-model.number="form.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5 flex items-center justify-center gap-1">
            <Repeat class="w-3 h-3" />
            <span>Reposts (🔁)</span>
          </label>
          <input v-model.number="form.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5 flex items-center justify-center gap-1">
            <Bookmark class="w-3 h-3 fill-amber-500" />
            <span>Guardados (🔖)</span>
          </label>
          <input v-model.number="form.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
            <Eye class="w-3 h-3" />
            <span>Impresiones</span>
          </label>
          <input v-model.number="form.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
        </div>
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
            <Send class="w-3 h-3" />
            <span>Compartidos (↗️)</span>
          </label>
          <input v-model.number="form.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
        </div>
      </div>
    </div>

    <!-- CASO 5: OTRAS REDES (YOUTUBE, LINKEDIN) -->
    <div v-else class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
      <div class="flex items-center justify-between">
        <span class="font-bold text-xs text-slate-700 dark:text-slate-300">Métricas ({{ form.plataforma.toUpperCase() }}):</span>
        <span class="font-mono text-cyan-500 font-bold text-xs">Total</span>
      </div>

      <div class="grid grid-cols-2 gap-2 font-mono text-center">
        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5">👍 Me gusta</label>
          <input v-model.number="form.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5">💬 Comentarios</label>
          <input v-model.number="form.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5">🔄 Shares</label>
          <input v-model.number="form.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
        </div>

        <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
          <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5">🔖 Guardados</label>
          <input v-model.number="form.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
        </div>
      </div>

      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
        <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
          <Eye class="w-3 h-3" />
          <span>Reproducciones / Alcance</span>
        </label>
        <input v-model.number="form.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
      </div>
    </div>
  </div>
</template>
