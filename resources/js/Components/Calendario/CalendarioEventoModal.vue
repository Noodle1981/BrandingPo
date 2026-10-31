<script setup>
import { Calendar, X } from '@lucide/vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  form: {
    type: Object,
    required: true,
  },
  ejes: {
    type: Array,
    default: () => [],
  },
  tiposDisponibles: {
    type: Array,
    default: () => [],
  },
  candidatoPrincipal: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close', 'submit']);
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
  >
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
        <div>
          <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Calendar class="w-5 h-5 text-cyan-500" />
            <span>Programar en Agenda de Campaña</span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Planifica contenidos digitales o hitos territoriales vinculados a un eje temático.
          </p>
        </div>
        <button @click="emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="emit('submit')" class="mt-4 space-y-4 text-sm">
        <!-- Título -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Título del Contenido / Evento *
          </label>
          <input
            v-model="form.titulo"
            type="text"
            required
            placeholder="ej. Lanzamiento Reel: Propuesta Primer Empleo Joven"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
          />
        </div>

        <!-- Selección de Eje Temático Estratégico -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1 flex items-center justify-between">
            <span>Eje Temático Estratégico *</span>
            <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400">Guía el mensaje y público</span>
          </label>
          <select
            v-model="form.eje_tematico_id"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500 font-mono font-bold"
          >
            <option value="">-- Sin eje específico (General) --</option>
            <option v-for="ej in ejes" :key="ej.id" :value="ej.id">
              {{ ej.nombre }} [{{ ej.diagnostico_badge }}]
            </option>
          </select>
        </div>

        <!-- Tipo de Actividad -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
              Tipo de Actividad *
            </label>
            <span class="text-[11px] font-mono text-cyan-600 dark:text-cyan-400 flex items-center gap-1 font-bold">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              <span>Perfil: {{ candidatoPrincipal?.nombre_completo || 'Federico Sisterna' }}</span>
            </span>
          </div>
          <select
            v-model="form.tipo_evento"
            required
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-bold"
          >
            <option v-for="t in tiposDisponibles" :key="t.key" :value="t.key">{{ t.label }}</option>
          </select>
        </div>

        <!-- Fechas y Horarios -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Fecha & Hora Publicación / Inicio *
            </label>
            <input
              v-model="form.fecha_inicio"
              type="datetime-local"
              required
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-mono"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Fecha & Hora Fin (Opcional)
            </label>
            <input
              v-model="form.fecha_fin"
              type="datetime-local"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-mono"
            />
          </div>
        </div>

        <!-- Canal o Lugar -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Canal Digital / Locación Física
          </label>
          <input
            v-model="form.lugar"
            type="text"
            placeholder="ej. Instagram & TikTok Reels / Club Juventud / Canal 12"
            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
          />
        </div>

        <!-- Notas & Estrategia -->
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Objetivo Estratégico & Logística
          </label>
          <textarea
            v-model="form.notas"
            rows="3"
            placeholder="Detalla el mensaje clave, formato audiovisual o consigna territorial a destacar..."
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
          ></textarea>
        </div>

        <!-- Botones de Acción -->
        <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold transition-all cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-md transition-all cursor-pointer disabled:opacity-50"
          >
            Programar en Agenda
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
