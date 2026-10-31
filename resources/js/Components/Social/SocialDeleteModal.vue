<script setup>
import { Trash2 } from '@lucide/vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  resumen: {
    type: String,
    default: '',
  },
  deleting: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity"
    @click.self="emit('close')"
  >
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full p-6 space-y-5 shadow-2xl relative">
      <!-- Icono de Advertencia y Título -->
      <div class="flex items-start gap-3.5">
        <div class="w-11 h-11 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
          <Trash2 class="w-5 h-5" />
        </div>
        <div class="space-y-1">
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 leading-snug">
            ¿Estás seguro que deseas eliminar la publicación?
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Esta acción eliminará permanentemente este post y todas sus métricas asociadas de la base de datos.
          </p>
        </div>
      </div>

      <!-- Extracto del post para contexto -->
      <div v-if="resumen" class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 text-xs text-slate-600 dark:text-slate-400 line-clamp-2 italic">
        "{{ resumen }}"
      </div>

      <!-- Botones de Acción: Cancelar y Eliminar -->
      <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
        <button
          type="button"
          @click="emit('close')"
          :disabled="deleting"
          class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          Cancelar
        </button>
        <button
          type="button"
          @click="emit('confirm')"
          :disabled="deleting"
          class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/25 transition-all cursor-pointer flex items-center gap-1.5 disabled:opacity-50"
        >
          <Trash2 class="w-3.5 h-3.5" />
          <span>{{ deleting ? 'Eliminando...' : 'Eliminar' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
