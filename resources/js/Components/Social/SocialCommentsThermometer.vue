<script setup>
import { Star } from '@lucide/vue';

const props = defineProps({
  termometroHumorSocial: {
    type: Number,
    default: 5,
  },
  comentariosDestacados: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div class="p-4 bg-slate-100/60 dark:bg-slate-950/80 border-t border-slate-200 dark:border-slate-800 space-y-2.5 text-xs">
    <!-- Thermometer Rating -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
      <span class="font-semibold text-slate-700 dark:text-slate-300">Termómetro de Humor Social:</span>
      <div class="flex items-center gap-0.5 text-amber-400">
        <Star
          v-for="star in 5"
          :key="star"
          class="w-3.5 h-3.5"
          :class="star <= (termometroHumorSocial || 5) ? 'fill-amber-400' : 'text-slate-300 dark:text-slate-600'"
        />
        <span class="ml-1 font-mono font-bold text-slate-700 dark:text-slate-300">
          {{ termometroHumorSocial || 5 }}/5
        </span>
      </div>
    </div>

    <!-- Top Comments List -->
    <div v-if="comentariosDestacados && comentariosDestacados.length" class="space-y-1.5">
      <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Comentarios más votados:</p>
      <div
        v-for="(comentario, idx) in comentariosDestacados"
        :key="idx"
        class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 text-slate-700 dark:text-slate-300 leading-snug"
      >
        💬 "{{ comentario }}"
      </div>
    </div>
  </div>
</template>
