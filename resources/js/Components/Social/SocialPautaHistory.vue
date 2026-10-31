<script setup>
import { ref } from 'vue';
import { Sparkles } from '@lucide/vue';
import Badge from '../Badge.vue';
import { useFormatters } from '../../composables/useFormatters';

const props = defineProps({
  pautaEventos: {
    type: Array,
    default: () => [],
  },
  totalLikes: {
    type: [Number, String],
    default: 0,
  },
});

const showPautaHistorial = ref(false);
const { formatNumber, formatCurrency } = useFormatters();
</script>

<template>
  <div v-if="pautaEventos && pautaEventos.length > 0" class="border-t border-slate-100 dark:border-slate-800/80">
    <button
      type="button"
      @click="showPautaHistorial = !showPautaHistorial"
      class="w-full px-4 sm:px-5 py-2.5 flex items-center justify-between text-xs font-mono font-bold bg-slate-50/70 dark:bg-slate-950/50 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors cursor-pointer"
    >
      <span class="flex items-center gap-1.5 text-cyan-600 dark:text-cyan-400">
        <Sparkles class="w-3.5 h-3.5" />
        <span>Historial de Pauta & Atribución de ROI ({{ pautaEventos.length }} {{ pautaEventos.length === 1 ? 'corte' : 'cortes' }})</span>
      </span>
      <span class="text-[10px] text-slate-400 font-normal">
        {{ showPautaHistorial ? 'Ocultar ▲' : 'Ver cortes ▼' }}
      </span>
    </button>

    <div v-if="showPautaHistorial" class="p-3.5 sm:p-4 bg-slate-100/50 dark:bg-slate-950/70 space-y-2.5 text-xs font-mono">
      <div
        v-for="ev in pautaEventos"
        :key="ev.id"
        class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2 shadow-2xs"
      >
        <div class="flex items-center justify-between flex-wrap gap-2">
          <div class="flex items-center gap-1.5 font-bold">
            <Badge variant="pauta" :value="ev.tipo_pauta_anterior" size="sm" />
            <span class="text-cyan-500 font-black">➔</span>
            <Badge variant="pauta" :value="ev.tipo_pauta_nuevo" size="sm" />
          </div>
          <span class="text-[10px] text-slate-400">
            {{ ev.fecha_evento }} ({{ ev.fecha_evento_humana }})
          </span>
        </div>

        <!-- Métricas del snapshot vs incremento actual -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1 text-[11px] text-slate-600 dark:text-slate-400">
          <div>
            <span class="text-[9px] uppercase tracking-wider text-slate-400 block">Base en Corte:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatNumber(ev.likes_snapshot) }} likes</span>
          </div>
          <div>
            <span class="text-[9px] uppercase tracking-wider text-slate-400 block">Seguidores Canal:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatNumber(ev.seguidores_canal_snapshot) }}</span>
          </div>
          <div>
            <span class="text-[9px] uppercase tracking-wider text-cyan-500 block">Ganados con Pauta:</span>
            <span class="font-bold text-emerald-500">
              +{{ formatNumber(Math.max(0, Number(totalLikes || 0) - Number(ev.likes_snapshot || 0))) }} likes
            </span>
          </div>
        </div>

        <div v-if="ev.monto_nuevo > 0" class="flex items-center justify-between pt-1.5 border-t border-slate-100 dark:border-slate-800/80 text-[10px]">
          <span class="text-slate-400">Inversión: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ formatCurrency(ev.monto_nuevo) }}</strong></span>
          <span v-if="Math.max(0, Number(totalLikes || 0) - Number(ev.likes_snapshot || 0)) > 0" class="text-cyan-600 dark:text-cyan-400 font-bold">
            CPL: ${{ (Number(ev.monto_nuevo) / Math.max(1, Number(totalLikes || 0) - Number(ev.likes_snapshot || 0))).toFixed(2) }} / like atribuible
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
