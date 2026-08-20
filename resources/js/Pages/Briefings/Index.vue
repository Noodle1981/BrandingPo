<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  FileText,
  Plus,
  Calendar,
  Sparkles,
  ExternalLink,
  Printer,
  X,
  Clock,
  ArrowRight
} from '@lucide/vue';

const props = defineProps({
  informes: {
    type: Array,
    default: () => [],
  },
  ciclos: {
    type: Array,
    default: () => [],
  },
  filtros: {
    type: Object,
    default: () => ({}),
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const isModalOpen = ref(false);
const form = useForm({
  ciclo_campana_id: props.ciclos[0]?.id || '',
  titulo: '',
  periodo_cubierto: 'Semana Actual - ' + new Date().toLocaleDateString('es-AR', { month: 'long', year: 'numeric' }),
  resumen_ejecutivo: '',
  conclusiones_estrategicas: '',
});

const openModal = () => {
  form.reset();
  form.ciclo_campana_id = props.ciclos[0]?.id || '';
  form.periodo_cubierto = 'Semana Actual - ' + new Date().toLocaleDateString('es-AR', { month: 'long', year: 'numeric' });
  form.clearErrors();
  isModalOpen.value = true;
};

const submitInforme = () => {
  form.post('/briefings', {
    onSuccess: () => isModalOpen.value = false,
  });
};
</script>

<template>
  <Head title="Briefings & Informes Ejecutivos" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <FileText class="w-6 h-6 text-cyan-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Briefings & Informes Ejecutivos
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Dossiers consolidados de situación para toma de decisiones, lectura ejecutiva y exportación impresa.
        </p>
      </div>

      <div v-if="canWrite" class="flex items-center gap-2">
        <button
          type="button"
          @click="openModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/20 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Generar Nuevo Briefing</span>
        </button>
      </div>
    </div>

    <!-- Briefings Grid -->
    <div v-if="!informes.length" class="text-center py-12 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
      <FileText class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-50" />
      <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No hay informes generados aún</h3>
      <p class="text-xs text-slate-500 mt-1">Genera el primer briefing ejecutivo con métricas consolidadas.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div
        v-for="inf in informes"
        :key="inf.id"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xs hover:shadow-md transition-all flex flex-col justify-between"
      >
        <div>
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[10px] font-mono uppercase font-extrabold px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
              {{ inf.periodo_cubierto }}
            </span>
            <span class="text-xs font-mono text-slate-400">{{ inf.fecha_generacion }}</span>
          </div>

          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 leading-snug">
            {{ inf.titulo }}
          </h3>

          <p class="mt-3 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-3">
            {{ inf.resumen_ejecutivo }}
          </p>

          <!-- Snapshot Preview Metrics -->
          <div v-if="inf.metricas_snapshot" class="mt-4 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 grid grid-cols-3 gap-2 text-center text-xs font-mono">
            <div>
              <span class="text-[10px] text-slate-400 block uppercase">Vistas</span>
              <span class="font-extrabold text-cyan-600 dark:text-cyan-400 text-sm">
                {{ (inf.metricas_snapshot.total_vistas_semana || inf.metricas_snapshot.total_vistas || 0).toLocaleString() }}
              </span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block uppercase">Pauta</span>
              <span class="font-extrabold text-violet-600 dark:text-violet-400 text-sm">
                ${{ (inf.metricas_snapshot.inversion_pauta_semana || inf.metricas_snapshot.total_pauta_invertida || 0).toLocaleString() }}
              </span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 block uppercase">Proximidad</span>
              <span class="font-extrabold text-emerald-500 text-sm">
                {{ inf.metricas_snapshot.proximidad_algoritmica || 92 }}%
              </span>
            </div>
          </div>
        </div>

        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
          <span class="text-xs text-slate-400 font-mono">{{ inf.ciclo }}</span>
          <Link
            :href="`/briefings/${inf.id}`"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs hover:opacity-90 transition-all shadow-xs"
          >
            <span>Ver Briefing Completo</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>
    </div>

    <!-- Modal: New Briefing -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
            Generar Briefing Ejecutivo
          </h3>
          <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitInforme" class="mt-4 space-y-4 text-sm">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Ciclo de Campaña *
            </label>
            <select
              v-model="form.ciclo_campana_id"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            >
              <option v-for="c in ciclos" :key="c.id" :value="c.id">{{ c.anio }} - {{ c.nombre }}</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Título del Informe *
            </label>
            <input
              v-model="form.titulo"
              type="text"
              required
              placeholder="ej. Briefing Estratégico Semanal - Agosto 2026"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Período Cubierto *
            </label>
            <input
              v-model="form.periodo_cubierto"
              type="text"
              required
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Resumen Ejecutivo *
            </label>
            <textarea
              v-model="form.resumen_ejecutivo"
              rows="4"
              required
              placeholder="Síntesis estratégica de impacto, opinión pública y pauta..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Conclusiones & Líneas de Acción Recomendadas
            </label>
            <textarea
              v-model="form.conclusiones_estrategicas"
              rows="3"
              placeholder="1. Reforzar pauta... 2. Preparar réplica..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
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
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-md"
            >
              Generar Briefing
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
