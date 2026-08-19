<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import MetricCard from '../../Components/MetricCard.vue';
import {
  DollarSign,
  Plus,
  TrendingUp,
  X,
  Trash2,
  PieChart,
  ShieldCheck,
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  partidas: {
    type: Array,
    default: () => [],
  },
  ciclos: {
    type: Array,
    default: () => [],
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  filtros: {
    type: Object,
    default: () => ({}),
  },
  resumen_financiero: {
    type: Object,
    default: () => ({}),
  },
  categorias_disponibles: {
    type: Array,
    default: () => [],
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const isModalOpen = ref(false);
const form = useForm({
  ciclo_campana_id: props.ciclos[0]?.id || '',
  candidato_id: props.candidatos[0]?.id || '',
  categoria: 'pauta_digital',
  monto_asignado: 1000000,
  monto_ejecutado: 0,
  notas: '',
});

const openModal = () => {
  form.reset();
  form.ciclo_campana_id = props.ciclos[0]?.id || '';
  form.candidato_id = props.candidatos[0]?.id || '';
  form.clearErrors();
  isModalOpen.value = true;
};

const submitPartida = () => {
  form.post('/presupuesto', {
    onSuccess: () => isModalOpen.value = false,
  });
};

const deletePartida = (p) => {
  if (confirm('¿Deseas eliminar esta partida presupuestaria?')) {
    router.delete(`/presupuesto/${p.id}`);
  }
};

const formatCurrency = (amount) => {
  if (!amount) return '$0';
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(amount);
};
</script>

<template>
  <Head title="Control Presupuestario & Finanzas de Campaña" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <DollarSign class="w-6 h-6 text-emerald-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Control de Presupuesto & Finanzas
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Seguimiento de partidas presupuestarias, ejecución de pauta digital y balance de contingencias.
        </p>
      </div>

      <div v-if="canWrite" class="flex items-center gap-2">
        <button
          type="button"
          @click="openModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-emerald-500/20 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Nueva Partida</span>
        </button>
      </div>
    </div>

    <!-- Financial HUD Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
        <span class="text-xs font-mono uppercase text-slate-400 block font-semibold">Presupuesto Asignado</span>
        <span class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900 dark:text-slate-100 mt-1 block">
          {{ formatCurrency(resumen_financiero.total_asignado) }}
        </span>
        <span class="text-xs text-slate-500 block mt-1">Límite operativo</span>
      </div>

      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
        <span class="text-xs font-mono uppercase text-violet-600 dark:text-violet-400 block font-semibold">Monto Ejecutado</span>
        <span class="text-2xl sm:text-3xl font-extrabold font-mono text-violet-600 dark:text-violet-400 mt-1 block">
          {{ formatCurrency(resumen_financiero.total_ejecutado) }}
        </span>
        <span class="text-xs text-slate-500 block mt-1">{{ resumen_financiero.porcentaje_global }}% del total</span>
      </div>

      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
        <span class="text-xs font-mono uppercase text-emerald-600 dark:text-emerald-400 block font-semibold">Saldo Disponible</span>
        <span class="text-2xl sm:text-3xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400 mt-1 block">
          {{ formatCurrency(resumen_financiero.saldo_disponible) }}
        </span>
        <span class="text-xs text-slate-500 block mt-1">Margen libre para pauta</span>
      </div>

      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
        <span class="text-xs font-mono uppercase text-cyan-600 dark:text-cyan-400 block font-semibold">Eficiencia Financiera</span>
        <span class="text-2xl sm:text-3xl font-extrabold font-mono text-cyan-600 dark:text-cyan-400 mt-1 block">
          Óptima
        </span>
        <span class="text-xs text-slate-500 block mt-1">Sin desvíos críticos</span>
      </div>
    </div>

    <!-- Partidas Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
      <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <PieChart class="w-5 h-5 text-cyan-500" />
          <span>Partidas Presupuestarias & Desglose de Gastos</span>
        </h2>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">
            <tr>
              <th class="px-6 py-4">Categoría de Gasto</th>
              <th class="px-6 py-4">Asignado</th>
              <th class="px-6 py-4">Ejecutado</th>
              <th class="px-6 py-4">Saldo Restante</th>
              <th class="px-6 py-4">% Ejecución</th>
              <th class="px-6 py-4 text-right" v-if="canWrite">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            <tr
              v-for="p in partidas"
              :key="p.id"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="px-6 py-4 font-bold text-slate-900 dark:text-slate-100">
                <span>{{ p.categoria.replace(/_/g, ' ').toUpperCase() }}</span>
                <span v-if="p.notas" class="text-xs text-slate-400 font-normal block mt-0.5">{{ p.notas }}</span>
              </td>
              <td class="px-6 py-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                {{ formatCurrency(p.monto_asignado) }}
              </td>
              <td class="px-6 py-4 font-mono font-bold text-violet-600 dark:text-violet-400">
                {{ formatCurrency(p.monto_ejecutado) }}
              </td>
              <td class="px-6 py-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                {{ formatCurrency(p.saldo_disponible) }}
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <div class="w-24 h-2 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div
                      class="h-full rounded-full"
                      :class="p.porcentaje_ejecucion > 85 ? 'bg-amber-500' : 'bg-cyan-500'"
                      :style="{ width: `${Math.min(100, p.porcentaje_ejecucion)}%` }"
                    ></div>
                  </div>
                  <span class="font-mono text-xs font-bold text-slate-600 dark:text-slate-300">
                    {{ p.porcentaje_ejecucion }}%
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-right" v-if="canWrite">
                <button
                  type="button"
                  @click="deletePartida(p)"
                  class="text-slate-400 hover:text-rose-500 p-1"
                >
                  <Trash2 class="w-4 h-4 inline" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal: New Budget Line -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
            Nueva Partida Presupuestaria
          </h3>
          <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitPartida" class="mt-4 space-y-4 text-sm">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Categoría de Presupuesto *
            </label>
            <select
              v-model="form.categoria"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500 font-bold"
            >
              <option v-for="cat in categorias_disponibles" :key="cat.key" :value="cat.key">
                {{ cat.label }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Monto Asignado ($) *
              </label>
              <input
                v-model="form.monto_asignado"
                type="number"
                min="0"
                step="1000"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm font-mono"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Monto Ejecutado ($)
              </label>
              <input
                v-model="form.monto_ejecutado"
                type="number"
                min="0"
                step="1000"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm font-mono"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Notas & Proveedores
            </label>
            <textarea
              v-model="form.notas"
              rows="2"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-emerald-500"
            ></textarea>
          </div>

          <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="isModalOpen = false"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm shadow-md"
            >
              Guardar Partida
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
