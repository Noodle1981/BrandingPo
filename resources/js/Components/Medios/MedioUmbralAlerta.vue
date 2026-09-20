<script setup>
import { computed } from 'vue';
import { AlertCircle, CheckCircle2, ShieldAlert } from '@lucide/vue';

const props = defineProps({
  umbral: {
    type: Object,
    required: true,
    default: () => ({
      total_medios: 0,
      minimo_requerido: 5,
      cumple_umbral: false,
      porcentaje_progreso: 0,
      faltantes: 5,
    }),
  },
});

const cumple = computed(() => props.umbral.cumple_umbral);
const total = computed(() => props.umbral.total_medios);
const faltantes = computed(() => props.umbral.faltantes);
const progreso = computed(() => props.umbral.porcentaje_progreso);
</script>

<template>
  <div
    class="p-4 sm:p-5 rounded-2xl border transition-all shadow-xs"
    :class="cumple 
      ? 'bg-emerald-500/5 dark:bg-emerald-500/10 border-emerald-500/20' 
      : 'bg-amber-500/5 dark:bg-amber-500/10 border-amber-500/30'"
  >
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-start gap-3">
        <div
          class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
          :class="cumple ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-amber-500/20 text-amber-600 dark:text-amber-400'"
        >
          <CheckCircle2 v-if="cumple" class="w-5 h-5" />
          <AlertCircle v-else class="w-5 h-5" />
        </div>

        <div>
          <div class="flex items-center gap-2">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
              {{ cumple ? 'Muestra Representativa de Medios Calibrada' : 'Muestra de Medios en Calibración' }}
            </h3>
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider"
              :class="cumple ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-amber-500/20 text-amber-600 dark:text-amber-300'"
            >
              {{ total }} / {{ umbral.minimo_requerido }} medios
            </span>
          </div>

          <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
            <span v-if="!cumple">
              El observatorio requiere registrar un <strong>mínimo de 5 medios de prensa</strong> para activar el índice de sesgo editorial y no distorsionar las métricas con muestras reducidas. Faltan <strong>{{ faltantes }} medio{{ faltantes !== 1 ? 's' : '' }}</strong> para completar el umbral.
            </span>
            <span v-else>
              Umbral metodológico superado con éxito. Las métricas de cobertura, tono editorial y humor social cuentan con base representativa multi-fuente.
            </span>
          </p>
        </div>
      </div>

      <!-- Progress Meter -->
      <div class="sm:w-48 shrink-0 flex flex-col justify-end">
        <div class="flex items-center justify-between text-xs font-mono mb-1.5 font-bold">
          <span class="text-slate-500 dark:text-slate-400 text-[11px]">Madurez del Panel</span>
          <span :class="cumple ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">
            {{ progreso }}%
          </span>
        </div>
        <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
          <div
            class="h-full rounded-full transition-all duration-500"
            :class="cumple ? 'bg-emerald-500' : 'bg-amber-500'"
            :style="{ width: `${progreso}%` }"
          />
        </div>
      </div>
    </div>
  </div>
</template>
