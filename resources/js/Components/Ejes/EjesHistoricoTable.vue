<script setup>
import { TrendingUp } from '@lucide/vue';

const props = defineProps({
  evolucionHistorica: {
    type: Array,
    default: () => [],
  },
  periodoSeleccionado: {
    type: String,
    default: 'todos',
  },
});

const emit = defineEmits(['cambiarPeriodo']);
</script>

<template>
  <div v-if="evolucionHistorica && evolucionHistorica.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
    <div>
      <h2 class="text-base font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
        <TrendingUp class="w-4 h-4 text-cyan-500" />
        <span>Evolución Histórica de los 3 Ejes Mes a Mes</span>
      </h2>
      <p class="text-xs text-slate-500 dark:text-slate-400">
        Seguimiento de la presencia y balance de cada pilar discursivo a lo largo del tiempo.
      </p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 font-mono uppercase">
            <th class="pb-2.5">Mes / Período</th>
            <th class="pb-2.5 text-center text-cyan-600 dark:text-cyan-400">🛡️ El Orden</th>
            <th class="pb-2.5 text-center text-emerald-600 dark:text-emerald-400">💼 El Futuro</th>
            <th class="pb-2.5 text-center text-amber-600 dark:text-amber-400">🤝 El Eje Humano</th>
            <th class="pb-2.5 text-center text-slate-400">🏛️ Institucional</th>
            <th class="pb-2.5 text-right">Total Posts</th>
            <th class="pb-2.5 text-right">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
          <tr
            v-for="mes in evolucionHistorica"
            :key="mes.clave_mes"
            class="hover:bg-slate-50 dark:hover:bg-slate-950/50 transition-colors"
            :class="{ 'bg-cyan-500/5 dark:bg-cyan-500/10 font-bold': periodoSeleccionado === mes.clave_mes }"
          >
            <td class="py-3 pr-3 font-sans font-bold text-slate-900 dark:text-slate-100">
              {{ mes.etiqueta }}
            </td>
            <td class="py-3 px-3 text-center text-cyan-600 dark:text-cyan-400 font-extrabold">
              {{ mes.orden }}
            </td>
            <td class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400 font-extrabold">
              {{ mes.futuro }}
            </td>
            <td class="py-3 px-3 text-center text-amber-600 dark:text-amber-400 font-extrabold">
              {{ mes.humano }}
            </td>
            <td class="py-3 px-3 text-center text-slate-400">
              {{ mes.institucional }}
            </td>
            <td class="py-3 px-3 text-right font-extrabold text-slate-900 dark:text-slate-100">
              {{ mes.total }}
            </td>
            <td class="py-3 pl-3 text-right">
              <button
                type="button"
                @click="emit('cambiarPeriodo', mes.clave_mes)"
                class="px-2.5 py-1 rounded-xl text-[10px] font-bold cursor-pointer transition-colors"
                :class="periodoSeleccionado === mes.clave_mes ? 'bg-cyan-500 text-slate-950 font-black' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700'"
              >
                {{ periodoSeleccionado === mes.clave_mes ? 'Activo' : 'Auditar Mes' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
