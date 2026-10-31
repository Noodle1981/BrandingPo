<script setup>
import { X } from '@lucide/vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  form: {
    type: Object,
    required: true,
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close', 'submit']);
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
  >
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative">
      <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
          Reportar Evento de Crisis
        </h3>
        <button @click="emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="emit('submit')" class="mt-4 space-y-4 text-sm">
        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Candidato Afectado *
          </label>
          <select
            v-model="form.candidato_id"
            required
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
          >
            <option v-for="c in candidatos" :key="c.id" :value="c.id">{{ c.nombre_completo }}</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Título / Motivo del Incidente *
          </label>
          <input
            v-model="form.titulo"
            type="text"
            required
            placeholder="ej. Denuncia en redes por corte de servicio"
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Nivel de Gravedad *
            </label>
            <select
              v-model="form.nivel_gravedad"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500 font-bold"
            >
              <option value="leve">🟢 Leve</option>
              <option value="moderado">🟡 Moderado</option>
              <option value="critico">🔴 Crítico</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Tiempo Reacción (min)
            </label>
            <input
              v-model="form.minutos_tiempo_respuesta"
              type="number"
              min="0"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm font-mono"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
            Estrategia de Contención
          </label>
          <textarea
            v-model="form.estrategia_contencion"
            rows="3"
            placeholder="Acciones operativas, vocerías y réplica en redes..."
            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
          ></textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-sm shadow-md cursor-pointer disabled:opacity-50"
          >
            Guardar Incidente
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
