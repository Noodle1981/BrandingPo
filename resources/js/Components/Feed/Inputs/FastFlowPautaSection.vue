<script setup>
import { DollarSign } from '@lucide/vue';

defineProps({
  form: {
    type: Object,
    required: true
  }
});
</script>

<template>
  <!-- Estrategia de Difusión & Pauta -->
  <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
    <div class="flex items-center justify-between">
      <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
        <DollarSign class="w-3.5 h-3.5 text-emerald-500" />
        <span>Estrategia de Difusión & Pauta</span>
      </span>
      <div class="flex items-center gap-1.5 bg-white dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
        <button
          type="button"
          @click="form.tipo_pauta = 'organico'"
          class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
          :class="form.tipo_pauta === 'organico' ? 'bg-cyan-500 text-slate-950 shadow-xs' : 'text-slate-400 hover:text-slate-200'"
        >
          Orgánica
        </button>
        <button
          type="button"
          @click="form.tipo_pauta = 'organico_impulsado'"
          class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
          :class="form.tipo_pauta === 'organico_impulsado' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'text-slate-400 hover:text-slate-200'"
        >
          🚀 Impulsada (Booster)
        </button>
        <button
          type="button"
          @click="form.tipo_pauta = 'anuncio'"
          class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
          :class="form.tipo_pauta === 'anuncio' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-400 hover:text-slate-200'"
        >
          📢 Anuncio Directo
        </button>
      </div>
    </div>

    <div v-if="form.tipo_pauta !== 'organico'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200/50 dark:border-slate-800/50">
      <div class="space-y-1">
        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 font-mono">
          Presupuesto / Inversión (ARS $):
        </label>
        <input
          v-model.number="form.monto_invertido_pauta"
          type="number"
          min="0"
          step="100"
          placeholder="0"
          class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold font-mono text-emerald-500 focus:ring-2 focus:ring-cyan-500"
        />
      </div>

      <!-- Vistas separadas cuando hay inversión publicitaria -->
      <div class="space-y-1">
        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 font-mono">
          Vistas Pagadas (Pauta):
        </label>
        <input
          v-model.number="form.vistas_pagadas"
          type="number"
          min="0"
          placeholder="0"
          class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold font-mono text-purple-400 focus:ring-2 focus:ring-cyan-500"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
      <div class="space-y-1">
        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400">
          Figura Acompañante / Co-protagonista:
        </label>
        <input
          v-model="form.figura_acompanante"
          type="text"
          placeholder="Ej: Gobernador, Intendente, Vecino..."
          class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs focus:ring-2 focus:ring-cyan-500"
        />
      </div>

      <div class="space-y-1">
        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400">
          Comentario Destacado / Relevante:
        </label>
        <input
          v-model="form.comentario_destacado"
          type="text"
          placeholder="Ej: 'Excelente gestión en el barrio!'"
          class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs focus:ring-2 focus:ring-cyan-500"
        />
      </div>
    </div>
  </div>
</template>
