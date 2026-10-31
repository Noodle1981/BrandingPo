<script setup>
import { Clock, Calendar, Zap } from '@lucide/vue';

defineProps({
  inteligenciaTiempo: {
    type: Object,
    default: () => ({})
  },
  formatNumber: {
    type: Function,
    required: true
  },
  formatCurrency: {
    type: Function,
    required: true
  }
});
</script>

<template>
  <!-- ⏳ INTELIGENCIA DE TIEMPO & RITMO DE CAMPAÑA HACIA LA ELECCIÓN -->
  <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
          <Clock class="w-5 h-5" />
        </div>
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <span>Inteligencia de Tiempo & Proyección al Día de la Elección</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-cyan-500 text-slate-950">
              ⏳ {{ inteligenciaTiempo.dias_para_eleccion }} Días Restantes
            </span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Velocidad de penetración orgánica actual vs. Aceleración necesaria con pauta para alcanzar la meta de votos.
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
      <!-- 1. Ritmo Semanal Actual -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">Velocidad Orgánica</span>
        <span class="text-xl font-black text-slate-800 dark:text-slate-200 block">
          +{{ inteligenciaTiempo.ritmo_semanal_crecimiento }} electores
        </span>
        <span class="text-[10px] text-slate-400 block">por semana en promedio</span>
      </div>

      <!-- 2. Proyección al Día D -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">Proyección Orgánica Día D</span>
        <span class="text-xl font-black text-cyan-600 dark:text-cyan-400 block">
          {{ formatNumber(inteligenciaTiempo.proyeccion_organica_total) }} electores
        </span>
        <span class="text-[10px] text-cyan-500 block">({{ inteligenciaTiempo.proyeccion_organica_padron_pct }}% del padrón)</span>
      </div>

      <!-- 3. Brecha de Victoria -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">Brecha vs Meta de Votos</span>
        <span class="text-xl font-black text-amber-500 block">
          -{{ formatNumber(inteligenciaTiempo.brecha_meta_votos) }} votos
        </span>
        <span class="text-[10px] text-amber-500/80 block">para asegurar victoria (40%)</span>
      </div>

      <!-- 4. Pauta Mensual de Aceleración -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">Pauta Mensual Sugerida</span>
        <span class="text-xl font-black text-emerald-500 block">
          {{ formatCurrency(inteligenciaTiempo.pauta_mensual_sugerida_ars) }}
        </span>
        <span class="text-[10px] text-emerald-600 block">para cerrar la brecha a tiempo</span>
      </div>
    </div>

    <!-- Franjas Horarias & Días Pico -->
    <div class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-between flex-wrap gap-4 text-xs font-mono text-cyan-800 dark:text-cyan-300">
      <div class="flex items-center gap-2">
        <Calendar class="w-4 h-4 text-cyan-500 shrink-0" />
        <span><strong>Días de Máxima Receptividad Barrial:</strong> {{ inteligenciaTiempo.dias_pico }}</span>
      </div>
      <div class="flex items-center gap-2">
        <Zap class="w-4 h-4 text-amber-500 shrink-0" />
        <span><strong>Horarios Prime:</strong> {{ inteligenciaTiempo.horarios_prime }}</span>
      </div>
    </div>
  </div>
</template>
