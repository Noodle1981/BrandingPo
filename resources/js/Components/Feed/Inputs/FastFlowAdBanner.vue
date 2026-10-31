<script setup>
import { DollarSign, CheckCircle2 } from '@lucide/vue';

defineProps({
  adDetectionAlert: {
    type: Object,
    default: null
  },
  tipoPauta: {
    type: String,
    default: 'organico'
  }
});

defineEmits(['aplicar-pauta']);
</script>

<template>
  <!-- Banner Interactivo: Detección Temprana de Pauta -->
  <div
    v-if="adDetectionAlert"
    class="p-3 rounded-xl bg-violet-500/15 border border-violet-500/40 text-violet-900 dark:text-violet-200 space-y-2 animate-fade-in"
  >
    <div class="flex items-start justify-between gap-2">
      <div class="flex items-start gap-2">
        <DollarSign class="w-4 h-4 text-violet-500 shrink-0 mt-0.5" />
        <div>
          <p class="font-bold text-xs flex items-center gap-1">
            <span>🎯 Sospecha de Pauta Activa / Boosted Post</span>
            <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-violet-500/20 text-violet-700 dark:text-violet-300 font-black uppercase">Detectada</span>
          </p>
          <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 leading-snug">
            {{ adDetectionAlert.motivo }}
          </p>
        </div>
      </div>
      <button
        v-if="tipoPauta === 'organico'"
        type="button"
        @click="$emit('aplicar-pauta')"
        class="px-2.5 py-1 rounded-lg bg-violet-600 hover:bg-violet-500 text-white font-bold text-[11px] shrink-0 transition-all shadow-xs cursor-pointer"
      >
        ⚡ Clasificar como Boosted
      </button>
      <span
        v-else
        class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 shrink-0"
      >
        <CheckCircle2 class="w-3 h-3" /> Clasificada
      </span>
    </div>
  </div>
</template>
