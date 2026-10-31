<script setup>
import { Calendar, Building2, ExternalLink } from '@lucide/vue';

defineProps({
  notaEditar: {
    type: Object,
    required: true,
  },
  urlNota: {
    type: String,
    default: '',
  },
  titulo: {
    type: String,
    default: '',
  },
  fechaPublicacion: {
    type: String,
    default: '',
  },
  origenTipo: {
    type: String,
    default: 'web',
  },
  tipoMencion: {
    type: String,
    default: 'titular',
  },
});
</script>

<template>
  <!-- Tarjeta de Contexto Informativa Fija (Modo Edición) -->
  <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 space-y-3">
    <!-- Medio & Link -->
    <div class="flex items-center justify-between gap-3">
      <div class="flex items-center gap-2.5 min-w-0">
        <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800 overflow-hidden shrink-0 border border-slate-300 dark:border-slate-700 flex items-center justify-center">
          <img
            v-if="notaEditar.medio?.avatar_url"
            :src="notaEditar.medio.avatar_url"
            :alt="notaEditar.medio.nombre"
            class="w-full h-full object-cover"
            referrerpolicy="no-referrer"
          />
          <Building2 v-else class="w-4 h-4 text-slate-400" />
        </div>
        <span class="text-xs font-black text-slate-900 dark:text-slate-100 truncate">
          {{ notaEditar.medio?.nombre || 'Medio de Prensa' }}
        </span>
      </div>

      <!-- Botón abrir enlace original si existe -->
      <a
        v-if="urlNota"
        :href="urlNota"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 font-bold text-[11px] transition-all shrink-0"
      >
        <span>Leer Nota</span>
        <ExternalLink class="w-3.5 h-3.5" />
      </a>
    </div>

    <!-- Titular Original -->
    <div>
      <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug">
        {{ titulo }}
      </h4>
    </div>

    <!-- Badges de contexto: Fecha, Origen, Mención -->
    <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-200/60 dark:border-slate-800/60 text-[11px] font-mono text-slate-500">
      <span class="flex items-center gap-1">
        <Calendar class="w-3 h-3 text-slate-400" />
        <span>{{ notaEditar.fecha || fechaPublicacion }}</span>
      </span>
      <span>•</span>
      <span class="capitalize">
        {{ origenTipo === 'facebook' ? 'Facebook' : 'Portal Web' }}
      </span>
      <span>•</span>
      <span>
        Mención en {{ tipoMencion === 'titular' ? 'Titular' : (tipoMencion === 'cuerpo' ? 'Cuerpo' : 'Etiqueta @') }}
      </span>
    </div>
  </div>
</template>
