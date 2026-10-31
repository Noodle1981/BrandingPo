<script setup>
import { Calendar } from '@lucide/vue';

defineProps({
  ultimaPublicacionRegistrada: {
    type: Object,
    default: null
  },
  plataforma: {
    type: String,
    default: 'red'
  }
});
</script>

<template>
  <!-- Tarjeta Auxiliar: Última publicación -->
  <div
    v-if="ultimaPublicacionRegistrada"
    class="p-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 flex items-start gap-2.5"
  >
    <div class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 shrink-0 mt-0.5">
      <Calendar class="w-3.5 h-3.5" />
    </div>
    <div class="text-[11px] leading-tight flex-1 min-w-0">
      <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 flex-wrap">
        <span>Último post en {{ (plataforma || 'red').toUpperCase() }}:</span>
        <span class="text-cyan-600 dark:text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded-md font-bold text-xs">
          🗓️ {{ ultimaPublicacionRegistrada.fecha_publicacion_humana || ultimaPublicacionRegistrada.fecha_publicacion }}
        </span>
        <span class="text-slate-400 font-normal">({{ ultimaPublicacionRegistrada.fecha_relativa }})</span>
      </div>
      <p class="text-slate-500 dark:text-slate-400 mt-1 truncate italic">
        "{{ ultimaPublicacionRegistrada.contenido_resumen }}"
      </p>
    </div>
  </div>
</template>
