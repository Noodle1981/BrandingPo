<script setup>
import { ref, computed } from 'vue';
import {
  Users,
  Target,
  Layers,
  Vote,
  Sparkles,
  Info,
  X,
  Share2
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  tema: {
    type: String,
    default: 'propio', // 'propio' | 'rival'
  },
});

const esPropio = computed(() => props.tema === 'propio' || props.candidato?.es_propio);
const isTiersModalOpen = ref(false);

const totalSeguidoresBruto = computed(() => {
  return Number(props.candidato.total_seguidores_bruto ?? props.candidato.total_seguidores ?? 0);
});

const totalSeguidoresNetos = computed(() => {
  return Number(props.candidato.total_seguidores_netos ?? props.candidato.total_seguidores ?? 0);
});

const penetracionNetaPct = computed(() => {
  return Number(props.candidato.penetracion_neta_pct ?? 0);
});

const padronElectoral = computed(() => {
  return Number(props.candidato.territorio?.padron_electoral ?? props.candidato.padron_electoral ?? 0);
});

const totalPublicaciones = computed(() => {
  return Number(props.candidato.total_publicaciones ?? 0);
});

const tiersDesglose = computed(() => {
  return props.candidato.tiers_desglose || [];
});
</script>

<template>
  <div class="space-y-4">
    <!-- Grid de 4 Cards Ejecutivas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
      <!-- 1. Audiencia Bruta Acumulada -->
      <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1">
        <div class="flex items-center justify-between text-slate-500 text-xs font-semibold">
          <span class="flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
            <Users class="w-4 h-4 text-slate-400" />
            Comunidad Bruta
          </span>
          <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold">
            Suma Redes
          </span>
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100">
          {{ totalSeguidoresBruto.toLocaleString('es-AR') }}
        </div>
        <div class="text-[11px] text-slate-400 font-sans pt-1 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
          <span>Contactos totales en canales</span>
          <span class="font-bold text-slate-500 font-mono">{{ candidato.perfiles_count || candidato.redes?.length || 7 }} redes</span>
        </div>
      </div>

      <!-- 2. Seguidores Únicos Reales (Tiers Deduplicados) -->
      <div
        class="p-4 rounded-3xl bg-white dark:bg-slate-900 border-2 shadow-sm space-y-1 relative group cursor-pointer transition-all hover:scale-101"
        :class="esPropio ? 'border-cyan-500/40 hover:border-cyan-500' : 'border-purple-500/40 hover:border-purple-500'"
        @click="isTiersModalOpen = true"
      >
        <div class="flex items-center justify-between text-xs font-semibold">
          <span
            class="flex items-center gap-1.5 uppercase tracking-wider text-[11px] font-bold"
            :class="esPropio ? 'text-cyan-600 dark:text-cyan-400' : 'text-purple-600 dark:text-purple-400'"
          >
            <Layers class="w-4 h-4" />
            Audiencia Real Única
          </span>
          <span
            class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider"
            :class="esPropio ? 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400' : 'bg-purple-500/15 text-purple-600 dark:text-purple-400'"
          >
            Desduplicado
          </span>
        </div>
        <div
          class="text-2xl sm:text-3xl font-extrabold"
          :class="esPropio ? 'text-cyan-600 dark:text-cyan-400' : 'text-purple-600 dark:text-purple-400'"
        >
          {{ totalSeguidoresNetos.toLocaleString('es-AR') }}
        </div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-sans pt-1 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
          <span>Personas individuales netas</span>
          <span class="font-bold underline text-[10px] font-mono flex items-center gap-0.5" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'">
            Ver Tiers &rarr;
          </span>
        </div>
      </div>

      <!-- 3. Penetración en Padrón Electoral -->
      <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1">
        <div class="flex items-center justify-between text-slate-500 text-xs font-semibold">
          <span class="flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
            <Target class="w-4 h-4 text-emerald-500" />
            Penetración Padrón
          </span>
          <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">
            Electoral
          </span>
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">
          {{ penetracionNetaPct }}%
        </div>
        <div class="text-[11px] text-slate-400 font-sans pt-1 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
          <span>Padrón: {{ padronElectoral.toLocaleString('es-AR') }} electores</span>
          <span class="font-bold text-slate-500 font-mono">{{ candidato.territorio?.nombre || 'Distrito' }}</span>
        </div>
      </div>

      <!-- 4. Publicaciones Registradas -->
      <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-1">
        <div class="flex items-center justify-between text-slate-500 text-xs font-semibold">
          <span class="flex items-center gap-1.5 uppercase tracking-wider text-[11px]">
            <Sparkles class="w-4 h-4 text-amber-500" />
            Contenidos Auditados
          </span>
          <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold">
            Feed
          </span>
        </div>
        <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 dark:text-amber-400">
          {{ totalPublicaciones.toLocaleString('es-AR') }}
        </div>
        <div class="text-[11px] text-slate-400 font-sans pt-1 border-t border-slate-100 dark:border-slate-800/60 flex items-center justify-between">
          <span>Posts y videos monitoreados</span>
          <span class="font-bold text-slate-500 font-mono">Multired</span>
        </div>
      </div>
    </div>

    <!-- MODAL DE DESGLOSE DE TIERS & AUDIENCIA REAL -->
    <div
      v-if="isTiersModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-2xl flex items-center justify-center"
              :class="esPropio ? 'bg-cyan-500/15 text-cyan-500' : 'bg-purple-500/15 text-purple-500'"
            >
              <Layers class="w-5 h-5" />
            </div>
            <div>
              <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span>Desduplicación por Tiers de Audiencia Real</span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Modelo matemático cross-platform de personas únicas reales sin duplicar seguidores compartidos.
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="isTiersModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Explicación del Algoritmo -->
        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs space-y-1.5 text-slate-600 dark:text-slate-400">
          <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
            <Info class="w-4 h-4" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
            <span>Fórmula de Atribución Incremental por Plataforma:</span>
          </div>
          <ul class="list-disc pl-5 space-y-1">
            <li><strong>Tier 1 (Red Principal):</strong> 100% de la base es única (punto de partida rector).</li>
            <li><strong>Ecosistema Meta (Facebook, Instagram, Threads):</strong> Factor incremental del 35% (estima ~65% de usuarios compartidos en la misma app matrix).</li>
            <li><strong>Otras Redes (TikTok, X, YouTube, LinkedIn):</strong> Factor incremental del 55% (mayor aporte de nueva audiencia no solapada).</li>
          </ul>
        </div>

        <!-- Tabla de Tiers Desglosados -->
        <div class="space-y-2 font-mono">
          <div
            v-for="t in tiersDesglose"
            :key="t.tier"
            class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-2 text-xs"
          >
            <div class="flex items-center gap-2.5">
              <span
                class="w-6 h-6 rounded-lg text-white font-extrabold flex items-center justify-center text-[10px]"
                :class="esPropio ? 'bg-cyan-600' : 'bg-purple-600'"
              >
                T{{ t.tier }}
              </span>
              <div>
                <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ t.nombre }}</span>
                <span class="text-[10px] text-slate-400 font-sans">@{{ t.handle }} ({{ t.plataforma }})</span>
              </div>
            </div>

            <div class="flex items-center gap-4 text-right">
              <div>
                <span class="text-[10px] text-slate-400 block font-sans">Seguidores Brutos</span>
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(t.seguidores_brutos).toLocaleString('es-AR') }}</span>
              </div>
              <div class="w-16 text-center">
                <span class="text-[10px] text-slate-400 block font-sans">Factor</span>
                <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-500 font-bold text-[10px]">
                  +{{ t.factor_incremental_pct }}%
                </span>
              </div>
              <div class="min-w-[80px]">
                <span class="text-[10px] text-slate-400 block font-sans">Únicos Aportados</span>
                <span
                  class="font-extrabold text-sm"
                  :class="esPropio ? 'text-cyan-600 dark:text-cyan-400' : 'text-purple-600 dark:text-purple-400'"
                >
                  +{{ Number(t.seguidores_unicos).toLocaleString('es-AR') }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Totalización -->
        <div class="p-4 rounded-2xl bg-slate-100 dark:bg-slate-800/80 flex items-center justify-between font-mono text-xs">
          <div>
            <span class="text-slate-500 block font-sans text-[11px]">Total Deduplicado Final</span>
            <span class="text-xl font-extrabold text-slate-900 dark:text-slate-100">
              {{ totalSeguidoresNetos.toLocaleString('es-AR') }} seguidores únicos
            </span>
          </div>
          <div class="text-right">
            <span class="text-slate-500 block font-sans text-[11px]">Penetración en Padrón</span>
            <span class="text-xl font-extrabold text-emerald-500">
              {{ penetracionNetaPct }}%
            </span>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            type="button"
            @click="isTiersModalOpen = false"
            class="px-5 py-2 rounded-xl text-white font-bold text-xs cursor-pointer shadow-sm"
            :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-purple-600 hover:bg-purple-500'"
          >
            Entendido
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
