<script setup>
import { Newspaper, Plus, RefreshCw } from '@lucide/vue';

defineProps({
  canWrite: {
    type: Boolean,
    default: false,
  },
  sincronizando: {
    type: Boolean,
    default: false,
  },
  totalMedios: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['open-create-modal', 'sincronizar-todos']);
</script>

<template>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2.5">
        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center ring-1 ring-cyan-500/30">
          <Newspaper class="w-5 h-5" />
        </div>
        <div>
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 tracking-tight">
            Observatorio de Medios & Prensa
          </h1>
          <p class="text-xs text-slate-500 dark:text-slate-400">
            Monitoreo en tiempo real de portales web (RSS) y redes sociales (Facebook) con análisis de sentimiento y menciones.
          </p>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div v-if="canWrite" class="flex flex-wrap items-center gap-2.5">
      <button
        type="button"
        @click="emit('sincronizar-todos')"
        :disabled="sincronizando || totalMedios === 0"
        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-all shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
        title="Rastrear RSS y Facebook en busca de nuevas menciones de candidatos"
      >
        <RefreshCw class="w-3.5 h-3.5 text-cyan-500" :class="{ 'animate-spin': sincronizando }" />
        <span>{{ sincronizando ? 'Sincronizando medios...' : 'Sincronizar Menciones' }}</span>
      </button>

      <button
        type="button"
        @click="emit('open-create-modal')"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs transition-all shadow-md shadow-cyan-500/20 cursor-pointer"
      >
        <Plus class="w-4 h-4 stroke-[3]" />
        <span>Registrar Nuevo Medio</span>
      </button>
    </div>
  </div>
</template>
