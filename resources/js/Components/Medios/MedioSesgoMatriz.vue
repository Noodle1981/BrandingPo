<script setup>
import { computed } from 'vue';
import { BarChart3, TrendingUp, TrendingDown, Minus, Building2 } from '@lucide/vue';

const props = defineProps({
  medios: {
    type: Array,
    default: () => [],
  },
  notas: {
    type: Array,
    default: () => [],
  },
  resumenTonos: {
    type: Object,
    default: () => ({}),
  },
  umbral: {
    type: Object,
    default: () => ({}),
  },
});

// Calculate metrics per media outlet
const matrizMedios = computed(() => {
  return props.medios.map(m => {
    const notasDelMedio = props.notas.filter(n => n.medio?.id === m.id);
    const total = notasDelMedio.length;
    const fav = notasDelMedio.filter(n => n.tono_mencion === 'favorable').length;
    const neu = notasDelMedio.filter(n => n.tono_mencion === 'neutro').length;
    const cri = notasDelMedio.filter(n => n.tono_mencion === 'critico').length;

    // Afinidad Neta (-100 a +100)
    const afinidadNeta = total > 0 ? Math.round(((fav - cri) / total) * 100) : 0;

    return {
      id: m.id,
      nombre: m.nombre,
      tipo_medio: m.tipo_medio,
      sesgo_declarado: m.sesgo_editorial_estimado,
      avatar_url: m.avatar_url,
      total,
      fav,
      neu,
      cri,
      afinidadNeta,
    };
  }).sort((a, b) => b.afinidadNeta - a.afinidadNeta);
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-2">
      <BarChart3 class="w-4 h-4 text-violet-500" />
      <h2 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
        Matriz de Sesgo Editorial & Percepción Mediática
      </h2>
    </div>

    <!-- Tone Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
        <span class="text-[10px] font-mono uppercase text-slate-400 font-bold block">Total Notas</span>
        <span class="text-2xl font-black font-mono text-slate-900 dark:text-slate-100">
          {{ resumenTonos.total || 0 }}
        </span>
      </div>

      <div class="p-4 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 shadow-xs">
        <span class="text-[10px] font-mono uppercase text-emerald-600 dark:text-emerald-400 font-bold block">Cobertura Favorable</span>
        <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
          {{ resumenTonos.favorables || 0 }}
        </span>
      </div>

      <div class="p-4 rounded-2xl bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/20 shadow-xs">
        <span class="text-[10px] font-mono uppercase text-amber-600 dark:text-amber-400 font-bold block">Informativas / Neutras</span>
        <span class="text-2xl font-black font-mono text-amber-600 dark:text-amber-400">
          {{ resumenTonos.neutras || 0 }}
        </span>
      </div>

      <div class="p-4 rounded-2xl bg-rose-500/5 dark:bg-rose-500/10 border border-rose-500/20 shadow-xs">
        <span class="text-[10px] font-mono uppercase text-rose-600 dark:text-rose-400 font-bold block">Cobertura Crítica</span>
        <span class="text-2xl font-black font-mono text-rose-600 dark:text-rose-400">
          {{ resumenTonos.criticas || 0 }}
        </span>
      </div>
    </div>

    <!-- Media Ranking Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-xs">
      <div class="p-5 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
          Termómetro de Afinidad Neta por Medio de Prensa
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Compara el balance entre menciones favorables y críticas publicadas por cada medio.
        </p>
      </div>

      <div v-if="!matrizMedios.length" class="text-center py-12 px-4">
        <Building2 class="w-10 h-10 text-slate-400 mx-auto mb-2 opacity-40" />
        <p class="text-xs text-slate-500">No hay medios registrados para calcular la matriz.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs font-mono">
          <thead class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 font-bold uppercase">
            <tr>
              <th class="py-3 px-5">Medio</th>
              <th class="py-3 px-3">Sesgo Declarado</th>
              <th class="py-3 px-3 text-center">Favorable</th>
              <th class="py-3 px-3 text-center">Neutro</th>
              <th class="py-3 px-3 text-center">Crítico</th>
              <th class="py-3 px-3 text-center">Total</th>
              <th class="py-3 px-5 text-right">Afinidad Neta</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="m in matrizMedios"
              :key="m.id"
              class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="py-3.5 px-5 font-sans font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                  <img
                    v-if="m.avatar_url"
                    :src="m.avatar_url"
                    :alt="m.nombre"
                    class="w-full h-full object-cover"
                    referrerpolicy="no-referrer"
                  />
                  <Building2 v-else class="w-3.5 h-3.5 text-slate-400 m-1.5" />
                </div>
                <span>{{ m.nombre }}</span>
              </td>

              <td class="py-3 px-3 font-sans capitalize text-slate-500">
                {{ m.sesgo_declarado }}
              </td>

              <td class="py-3 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                {{ m.fav }}
              </td>

              <td class="py-3 px-3 text-center font-bold text-amber-500">
                {{ m.neu }}
              </td>

              <td class="py-3 px-3 text-center font-bold text-rose-600 dark:text-rose-400">
                {{ m.cri }}
              </td>

              <td class="py-3 px-3 text-center font-bold text-slate-700 dark:text-slate-300">
                {{ m.total }}
              </td>

              <td class="py-3 px-5 text-right font-extrabold">
                <span
                  class="px-2.5 py-1 rounded-xl inline-flex items-center gap-1"
                  :class="m.afinidadNeta > 0 
                    ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' 
                    : (m.afinidadNeta < 0 
                      ? 'bg-rose-500/10 text-rose-600 dark:text-rose-400' 
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-500')"
                >
                  <TrendingUp v-if="m.afinidadNeta > 0" class="w-3.5 h-3.5" />
                  <TrendingDown v-else-if="m.afinidadNeta < 0" class="w-3.5 h-3.5" />
                  <Minus v-else class="w-3.5 h-3.5" />
                  <span>{{ m.afinidadNeta > 0 ? `+${m.afinidadNeta}%` : `${m.afinidadNeta}%` }}</span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
