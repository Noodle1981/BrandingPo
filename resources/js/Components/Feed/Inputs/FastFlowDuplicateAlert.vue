<script setup>
import { AlertCircle } from '@lucide/vue';

defineProps({
  duplicateAlert: {
    type: Object,
    default: null
  }
});
</script>

<template>
  <!-- Alerta Inmediata si se detecta URL duplicada -->
  <div
    v-if="duplicateAlert"
    class="p-3 rounded-2xl bg-amber-500/15 border border-amber-500/40 text-amber-900 dark:text-amber-200 space-y-1 animate-fade-in"
  >
    <div class="flex items-center gap-1.5 font-bold text-xs">
      <AlertCircle class="w-4 h-4 text-amber-500 shrink-0" />
      <span>⚠️ Esta publicación ya está registrada en el sistema</span>
    </div>
    <p class="text-[11px] text-amber-800 dark:text-amber-300 font-mono">
      <strong>ID:</strong> #{{ duplicateAlert.id || duplicateAlert.publicacion_id }} • 
      <strong>Fecha de Origen:</strong> {{ duplicateAlert.fecha_publicacion }} • 
      <strong>Autor:</strong> {{ duplicateAlert.autor || duplicateAlert.candidato?.nombre_completo }}
    </p>
    <p v-if="duplicateAlert.contenido_resumen" class="text-[11px] text-slate-500 dark:text-slate-400 italic truncate">
      "{{ duplicateAlert.contenido_resumen }}"
    </p>
  </div>
</template>
