<script setup>
import { DollarSign, Sparkles } from '@lucide/vue';

const props = defineProps({
  modal: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'convertir']);
</script>

<template>
  <div
    v-if="modal?.visible"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
    @click.self="emit('close')"
  >
    <div class="bg-white dark:bg-slate-900 border border-violet-500/40 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl relative">
      <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-2xl bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20 flex items-center justify-center shrink-0">
          <DollarSign class="w-5 h-5" />
        </div>
        <div class="space-y-1">
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
            <span>🎯 Sospecha de Pauta Activa</span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            {{ modal.motivo }}
          </p>
        </div>
      </div>

      <div class="p-3 rounded-2xl bg-violet-500/10 border border-violet-500/20 text-xs text-violet-900 dark:text-violet-200 leading-relaxed">
        Esta publicación estaba registrada como <strong>Orgánica Pura</strong>. Si está en circulación paga, convertirla a <strong>Orgánica Impulsada (Boosted Post)</strong> registrará el punto de inflexión para el cálculo de ROI.
      </div>

      <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
        <button
          type="button"
          @click="emit('close')"
          class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          Mantener Orgánica
        </button>
        <button
          type="button"
          @click="emit('convertir')"
          class="px-4 py-2 rounded-xl text-xs font-bold bg-violet-600 hover:bg-violet-500 text-white shadow-md shadow-violet-600/25 transition-all cursor-pointer flex items-center gap-1.5"
        >
          <Sparkles class="w-3.5 h-3.5" />
          <span>Convertir a Boosted Post</span>
        </button>
      </div>
    </div>
  </div>
</template>
