<script setup>
import { Compass } from '@lucide/vue';

const props = defineProps({
  semaforoPadron: {
    type: Object,
    required: true,
  },
  formatNumber: {
    type: Function,
    required: true,
  },
});
</script>

<template>
  <div v-if="semaforoPadron" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
      <div>
        <div class="flex items-center gap-2">
          <span class="p-1.5 rounded-xl bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
            <Compass class="w-5 h-5" />
          </span>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
            Semáforo del Padrón Electoral (KPIs Determinantes de Campaña)
          </h3>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Rendimiento contrastado contra los {{ formatNumber(semaforoPadron.cobertura?.padron_total || 24500) }} electores habilitados.
        </p>
      </div>
      <span class="text-xs font-mono px-3 py-1.5 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold self-start sm:self-auto border border-cyan-500/20">
        🎯 Meta Ganadora: 40% del Padrón
      </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
      <!-- Card 1: Cobertura del Padrón (Alcance Visual) -->
      <div
        class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-3 flex flex-col justify-between"
        :class="{
          'border-emerald-500/50 shadow-xs shadow-emerald-500/10': semaforoPadron.cobertura?.estado === 'ganadora',
          'border-amber-500/50 shadow-xs shadow-amber-500/10': semaforoPadron.cobertura?.estado === 'regular' || semaforoPadron.cobertura?.estado === 'medio',
          'border-rose-500/50 shadow-xs shadow-rose-500/10': semaforoPadron.cobertura?.estado === 'critico',
        }"
      >
        <div class="space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
              👀 Cobertura Padrón
            </span>
            <span
              class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
              :class="{
                'bg-emerald-500/15 text-emerald-500': semaforoPadron.cobertura?.estado === 'ganadora',
                'bg-amber-500/15 text-amber-500': semaforoPadron.cobertura?.estado === 'regular' || semaforoPadron.cobertura?.estado === 'medio',
                'bg-rose-500/15 text-rose-500': semaforoPadron.cobertura?.estado === 'critico',
              }"
            >
              {{ semaforoPadron.cobertura?.pct_actual }}% Padrón
            </span>
          </div>
          <span class="text-2xl font-black block" :class="{
            'text-emerald-500': semaforoPadron.cobertura?.estado === 'ganadora',
            'text-amber-500': semaforoPadron.cobertura?.estado === 'regular' || semaforoPadron.cobertura?.estado === 'medio',
            'text-rose-500': semaforoPadron.cobertura?.estado === 'critico',
          }">
            {{ formatNumber(semaforoPadron.cobertura?.actual_vistas) }}
          </span>
          <span class="text-[11px] text-slate-400 block">visualizaciones este ciclo</span>
        </div>

        <div class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800/80">
          <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-500"
              :class="semaforoPadron.cobertura?.pct_actual >= 40 ? 'bg-emerald-500' : (semaforoPadron.cobertura?.pct_actual >= 30 ? 'bg-amber-500' : 'bg-rose-500')"
              :style="{ width: `${Math.min(100, (semaforoPadron.cobertura?.pct_actual / 40) * 100)}%` }"
            ></div>
          </div>
          <div class="flex justify-between text-[10px] text-slate-400">
            <span>Meta Regular: 30%</span>
            <span>Meta Victoria: 40%</span>
          </div>
        </div>

        <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
          {{ semaforoPadron.cobertura?.diagnostico }}
        </p>
      </div>

      <!-- Card 2: Movilización del Padrón (Interacciones Cívicas) -->
      <div
        class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border transition-all space-y-3 flex flex-col justify-between"
        :class="{
          'border-emerald-500/50 shadow-xs shadow-emerald-500/10': semaforoPadron.movilizacion?.estado === 'ganadora',
          'border-amber-500/50 shadow-xs shadow-amber-500/10': semaforoPadron.movilizacion?.estado === 'regular' || semaforoPadron.movilizacion?.estado === 'medio',
          'border-rose-500/50 shadow-xs shadow-rose-500/10': semaforoPadron.movilizacion?.estado === 'critico',
        }"
      >
        <div class="space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
              💬 Movilización
            </span>
            <span
              class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
              :class="{
                'bg-emerald-500/15 text-emerald-500': semaforoPadron.movilizacion?.estado === 'ganadora',
                'bg-amber-500/15 text-amber-500': semaforoPadron.movilizacion?.estado === 'regular' || semaforoPadron.movilizacion?.estado === 'medio',
                'bg-rose-500/15 text-rose-500': semaforoPadron.movilizacion?.estado === 'critico',
              }"
            >
              {{ semaforoPadron.movilizacion?.pct_actual }}% Padrón
            </span>
          </div>
          <span class="text-2xl font-black block" :class="{
            'text-emerald-500': semaforoPadron.movilizacion?.estado === 'ganadora',
            'text-amber-500': semaforoPadron.movilizacion?.estado === 'regular' || semaforoPadron.movilizacion?.estado === 'medio',
            'text-rose-500': semaforoPadron.movilizacion?.estado === 'critico',
          }">
            {{ formatNumber(semaforoPadron.movilizacion?.actual_interacciones) }}
          </span>
          <span class="text-[11px] text-slate-400 block">likes + coment + reposts + shares</span>
        </div>

        <div class="space-y-1.5 pt-2 border-t border-slate-200 dark:border-slate-800/80">
          <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
            <div
              class="h-full rounded-full transition-all duration-500"
              :class="semaforoPadron.movilizacion?.pct_actual >= 15 ? 'bg-emerald-500' : (semaforoPadron.movilizacion?.pct_actual >= 8 ? 'bg-amber-500' : 'bg-rose-500')"
              :style="{ width: `${Math.min(100, (semaforoPadron.movilizacion?.pct_actual / 15) * 100)}%` }"
            ></div>
          </div>
          <div class="flex justify-between text-[10px] text-slate-400">
            <span>Meta Regular: 8%</span>
            <span>Meta Ganadora: 15%</span>
          </div>
        </div>

        <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
          {{ semaforoPadron.movilizacion?.diagnostico }}
        </p>
      </div>

      <!-- Card 3: Calculadora de Ritmo de Publicación -->
      <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between">
        <div class="space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
              📅 Ritmo Publicitario
            </span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-cyan-500/15 text-cyan-500">
              {{ semaforoPadron.ritmo?.posts_semana_actual }} posts/sem
            </span>
          </div>
          <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-black text-cyan-500">
              {{ semaforoPadron.ritmo?.posts_semana_necesarios }}
            </span>
            <span class="text-xs text-slate-400">posts/semana necesarios</span>
          </div>
          <span class="text-[11px] text-slate-400 block">
            Promedio actual: {{ formatNumber(semaforoPadron.ritmo?.promedio_vistas_post) }} vistas/post
          </span>
        </div>

        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-[11px] space-y-1">
          <div class="flex justify-between">
            <span class="text-slate-400">Meta Mensual:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ semaforoPadron.ritmo?.posts_mes_necesarios }} posts/mes</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">¿Viable 100% Orgánico?</span>
            <span class="font-bold" :class="semaforoPadron.ritmo?.es_alcanzable_organico ? 'text-emerald-500' : 'text-amber-500'">
              {{ semaforoPadron.ritmo?.es_alcanzable_organico ? 'Sí 🏆' : 'Requiere Pauta' }}
            </span>
          </div>
        </div>

        <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
          {{ semaforoPadron.ritmo?.consejo }}
        </p>
      </div>

      <!-- Card 4: Amplificación Viral & Costo por Elector -->
      <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between">
        <div class="space-y-1">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">
              🚀 Viralidad & CEA
            </span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-500/15 text-purple-400">
              {{ (semaforoPadron.amplificacion?.total_compartidos || 0) + (semaforoPadron.amplificacion?.total_republicados || 0) }} difusiones
            </span>
          </div>
          <div>
            <span class="text-2xl font-black text-purple-400 block">
              +{{ formatNumber(semaforoPadron.amplificacion?.amplificacion_estimada) }}
            </span>
            <span class="text-[11px] text-slate-400 block">alcance viral expandido</span>
          </div>
        </div>

        <div class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 text-[11px] space-y-1">
          <div class="flex justify-between">
            <span class="text-slate-400">Costo / Elector (CEA):</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">
              {{ semaforoPadron.amplificacion?.costo_por_elector_ars > 0 ? '$' + semaforoPadron.amplificacion?.costo_por_elector_ars : 'Orgánico' }}
            </span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Electores Únicos Estim.:</span>
            <span class="font-bold text-cyan-500">{{ formatNumber(semaforoPadron.amplificacion?.electores_alcanzados_estimados) }}</span>
          </div>
        </div>

        <p class="text-[11px] font-sans text-slate-500 dark:text-slate-400 leading-tight">
          {{ semaforoPadron.amplificacion?.diagnostico }}
        </p>
      </div>
    </div>
  </div>
</template>
