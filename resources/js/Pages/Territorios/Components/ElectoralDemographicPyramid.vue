<script setup>
import { Users } from '@lucide/vue';

defineProps({
  cruceFranjas: {
    type: Array,
    default: () => []
  },
  territorioNombre: {
    type: String,
    default: ''
  },
  formatNumber: {
    type: Function,
    required: true
  }
});
</script>

<template>
  <!-- 👥 CRUCE DEMOGRÁFICO DE VOTO POTENCIAL POR FRANJA ETARIA -->
  <div v-if="cruceFranjas.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
    <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
      <div>
        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Users class="w-5 h-5 text-purple-400" />
          <span>Voto Potencial Digital por Franja Etaria en el Padrón</span>
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Cruce entre la pirámide de electores de {{ territorioNombre }} y la comunidad digital alcanzada.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
      <div
        v-for="franja in cruceFranjas"
        :key="franja.id"
        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-3"
        :class="{
          'border-emerald-500/40 shadow-xs shadow-emerald-500/5': franja.estado === 'verde',
          'border-amber-500/40 shadow-xs shadow-amber-500/5': franja.estado === 'amarillo',
          'border-rose-500/40 shadow-xs shadow-rose-500/5': franja.estado === 'rojo',
        }"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-black text-slate-900 dark:text-slate-100">
            {{ franja.rango }} años
          </span>
          <span
            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
            :class="{
              'bg-emerald-500/15 text-emerald-500': franja.estado === 'verde',
              'bg-amber-500/15 text-amber-500': franja.estado === 'amarillo',
              'bg-rose-500/15 text-rose-500': franja.estado === 'rojo',
            }"
          >
            {{ franja.cobertura_franja_pct }}% Cobertura
          </span>
        </div>

        <div>
          <span class="text-lg font-black block text-cyan-500">
            {{ formatNumber(franja.seguidores_estimados) }} alcanzados
          </span>
          <span class="text-[11px] text-slate-400 block">
            de {{ formatNumber(franja.electores_padron) }} electores en padrón
          </span>
        </div>

        <div class="space-y-1 pt-1 border-t border-slate-200 dark:border-slate-800/80 text-[11px] font-sans">
          <div class="flex justify-between font-mono text-[10px] text-slate-400">
            <span>Canal Clave:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ franja.red_principal }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
