<script setup>
import { Link } from '@inertiajs/vue3';
import Badge from '../Badge.vue';
import { Newspaper, ChevronRight } from '@lucide/vue';

const props = defineProps({
  ultimasNotasPrensa: {
    type: Array,
    default: () => []
  }
});
</script>

<template>
  <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5 flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between mb-2">
        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Newspaper class="w-4 h-4 text-cyan-500" />
          <span>Observatorio de Prensa & Medios</span>
        </h2>
        <Link href="/medios" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
          Ver Todos &rarr;
        </Link>
      </div>

      <div v-if="ultimasNotasPrensa.length > 0" class="space-y-2.5">
        <a
          v-for="nota in ultimasNotasPrensa"
          :key="nota.id"
          :href="nota.url_nota || '#'"
          target="_blank"
          rel="noopener noreferrer"
          class="block p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 transition-all group"
        >
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 uppercase font-mono">
              {{ nota.medio_nombre }}
            </span>
            <Badge variant="tono" :value="nota.tono_mencion" size="xs" />
          </div>
          <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors line-clamp-2 leading-relaxed">
            {{ nota.titulo }}
          </p>
          <span class="text-[10px] text-slate-400 mt-1 block font-mono">{{ nota.fecha }}</span>
        </a>
      </div>

      <div v-else class="text-xs text-slate-400 text-center py-8">
        No se registran notas de prensa vinculadas al candidato.
      </div>
    </div>

    <div class="pt-2 text-right">
      <Link href="/medios" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline inline-flex items-center gap-1">
        <span>Ir al Clipping Completo</span>
        <ChevronRight class="w-4 h-4" />
      </Link>
    </div>
  </div>
</template>
