<script setup>
import { ref } from 'vue';
import {
  Target,
  ChevronDown,
  ChevronUp,
  CheckCircle2,
  AlertTriangle,
  XCircle
} from '@lucide/vue';

const props = defineProps({
  benchmarks: {
    type: Object,
    required: true,
  },
  semaforoObjetivos: {
    type: Array,
    default: () => [],
  },
});

const showIndustryBenchmarks = ref(false);
</script>

<template>
  <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
    <div
      class="flex items-center justify-between cursor-pointer select-none"
      @click="showIndustryBenchmarks = !showIndustryBenchmarks"
    >
      <div class="flex items-center gap-3">
        <span class="p-2 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20">
          <Target class="w-5 h-5" />
        </span>
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <span>Benchmarks Técnicos de Industria (Tramo: {{ benchmarks.tramo_label || 'Nano' }})</span>
            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500">
              Referencia Secundaria
            </span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Estándares de algoritmos sociales ajustados al tamaño de audiencia de tu canal.
          </p>
        </div>
      </div>

      <button
        type="button"
        class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-cyan-500 transition-colors"
      >
        <component :is="showIndustryBenchmarks ? ChevronUp : ChevronDown" class="w-5 h-5" />
      </button>
    </div>

    <div v-show="showIndustryBenchmarks" class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="obj in semaforoObjetivos"
        :key="obj.id"
        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-3"
        :class="{
          'border-emerald-500/40 shadow-xs shadow-emerald-500/5': obj.estado === 'verde',
          'border-amber-500/40 shadow-xs shadow-amber-500/5': obj.estado === 'amarillo',
          'border-rose-500/40 shadow-xs shadow-rose-500/5': obj.estado === 'rojo',
        }"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
            {{ obj.titulo }}
          </span>
          <span
            class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase flex items-center gap-1"
            :class="{
              'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400': obj.estado === 'verde',
              'bg-amber-500/15 text-amber-600 dark:text-amber-400': obj.estado === 'amarillo',
              'bg-rose-500/15 text-rose-600 dark:text-rose-400': obj.estado === 'rojo',
            }"
          >
            <CheckCircle2 v-if="obj.estado === 'verde'" class="w-3 h-3" />
            <AlertTriangle v-else-if="obj.estado === 'amarillo'" class="w-3 h-3" />
            <XCircle v-else class="w-3 h-3" />
            <span>{{ obj.estado === 'verde' ? 'En Rango' : (obj.estado === 'amarillo' ? 'Atención' : 'Crítico') }}</span>
          </span>
        </div>

        <div>
          <span class="text-lg font-black font-mono block" :class="{
            'text-emerald-500': obj.estado === 'verde',
            'text-amber-500': obj.estado === 'amarillo',
            'text-rose-500': obj.estado === 'rojo',
          }">
            {{ obj.actual_formato }}
          </span>
          <span class="text-[11px] font-mono text-slate-400 block mt-0.5">
            Rango ideal: <strong>{{ obj.rango_ideal }}</strong>
          </span>
        </div>

        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight pt-1 border-t border-slate-200/60 dark:border-slate-800/60">
          💡 {{ obj.consejo }}
        </p>
      </div>
    </div>
  </div>
</template>
