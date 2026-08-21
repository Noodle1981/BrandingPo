<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import { TrendingUp, ArrowUp, ArrowDown, Minus, Users } from '@lucide/vue';

const props = defineProps({
  candidatos: {
    type: Array,
    default: () => [],
  },
  workspace: {
    type: Object,
    default: () => ({}),
  },
});

// Plataformas únicas presentes entre todos los candidatos
const plataformas = computed(() => {
  const set = new Set();
  props.candidatos.forEach(c => c.redes.forEach(r => set.add(r.plataforma)));
  return [...set];
});

// Nombre de display de plataforma
const platformLabel = (p) => ({
  instagram: 'Instagram',
  facebook: 'Facebook',
  tiktok: 'TikTok',
  x_twitter: 'X / Twitter',
  youtube: 'YouTube',
  linkedin: 'LinkedIn',
}[p] || p);

// Color por plataforma
const platformColor = (p) => ({
  instagram: '#E4405F',
  facebook: '#1877F2',
  tiktok: '#00F2FE',
  x_twitter: '#000000',
  youtube: '#FF0000',
  linkedin: '#0A66C2',
}[p] || '#8b5cf6');

// Obtener la red de un candidato para una plataforma dada
const getRedByPlataforma = (candidato, plataforma) => {
  return candidato.redes.find(r => r.plataforma === plataforma) || null;
};

// Formatear número con separador de miles
const formatNum = (n) => {
  if (!n && n !== 0) return '—';
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
  if (n >= 1_000) return (n / 1_000).toFixed(1) + 'K';
  return n.toString();
};
</script>

<template>
  <Head title="Benchmarking & Comparativa de Candidatos" />

  <WarRoomLayout>
    <!-- Cabecera -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <TrendingUp class="w-6 h-6 text-cyan-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Benchmarking & Comparativa
          </h1>
          <span class="text-[10px] uppercase font-mono font-bold px-2 py-0.5 rounded-full bg-violet-500/20 text-violet-600 dark:text-violet-400 border border-violet-500/40">
            Crecimiento Neto
          </span>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
          Comparativa de crecimiento de seguidores desde el Punto Cero entre tu campaña y los rivales monitoreados.
        </p>
      </div>

      <Link
        href="/candidatos"
        class="text-xs font-semibold text-slate-500 hover:text-cyan-500 transition-colors flex items-center gap-1"
      >
        ← Volver a Fichas de Rivales
      </Link>
    </div>

    <!-- Tabla por plataforma -->
    <div
      v-for="plataforma in plataformas"
      :key="plataforma"
      class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4"
    >
      <!-- Header plataforma -->
      <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-3">
        <div
          class="w-3 h-3 rounded-full"
          :style="{ background: platformColor(plataforma) }"
        />
        <h2 class="font-bold text-base text-slate-900 dark:text-slate-100">
          {{ platformLabel(plataforma) }}
        </h2>
        <span class="text-xs text-slate-400 font-mono">Comparativa de crecimiento desde Punto Cero</span>
      </div>

      <!-- Tabla comparativa -->
      <div class="overflow-x-auto">
        <table class="w-full text-xs font-mono">
          <thead>
            <tr class="text-slate-400 dark:text-slate-500 text-left border-b border-slate-100 dark:border-slate-800">
              <th class="pb-2 pr-4 font-bold uppercase tracking-wider">Candidato</th>
              <th class="pb-2 pr-4 font-bold uppercase tracking-wider text-right">Inicio (Punto Cero)</th>
              <th class="pb-2 pr-4 font-bold uppercase tracking-wider text-right">Actual</th>
              <th class="pb-2 pr-4 font-bold uppercase tracking-wider text-right">Δ Neto</th>
              <th class="pb-2 font-bold uppercase tracking-wider text-right">% Crec.</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="cand in candidatos"
              :key="cand.id"
              class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/40"
              :class="cand.es_propio ? 'bg-cyan-500/5 dark:bg-cyan-500/10' : ''"
            >
              <template v-if="getRedByPlataforma(cand, plataforma)">
                <!-- Candidato -->
                <td class="py-3 pr-4">
                  <div class="flex items-center gap-2.5">
                    <img
                      v-if="cand.avatar"
                      :src="cand.avatar"
                      :alt="cand.nombre"
                      referrerpolicy="no-referrer"
                      class="w-7 h-7 rounded-lg object-cover border shrink-0"
                      :style="{ borderColor: cand.color }"
                    />
                    <div
                      v-else
                      class="w-7 h-7 rounded-lg shrink-0 flex items-center justify-center text-white text-[10px] font-bold"
                      :style="{ background: cand.color }"
                    >
                      {{ cand.nombre.charAt(0) }}
                    </div>
                    <div>
                      <p class="font-bold text-slate-900 dark:text-slate-100 leading-none">
                        {{ cand.nombre }}
                        <span
                          v-if="cand.es_propio"
                          class="ml-1 text-[9px] px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 font-bold"
                        >PROPIO</span>
                      </p>
                      <p class="text-[10px] text-slate-400 mt-0.5">
                        {{ getRedByPlataforma(cand, plataforma)?.handle || '—' }}
                      </p>
                    </div>
                  </div>
                </td>

                <!-- Inicio -->
                <td class="py-3 pr-4 text-right text-slate-600 dark:text-slate-400">
                  {{ formatNum(getRedByPlataforma(cand, plataforma)?.seguidores_punto_cero) }}
                </td>

                <!-- Actual -->
                <td class="py-3 pr-4 text-right font-bold text-slate-900 dark:text-slate-100">
                  {{ formatNum(getRedByPlataforma(cand, plataforma)?.seguidores_actuales) }}
                </td>

                <!-- Delta neto -->
                <td class="py-3 pr-4 text-right">
                  <span
                    class="inline-flex items-center gap-0.5 font-bold px-2 py-0.5 rounded-full text-[11px]"
                    :class="(getRedByPlataforma(cand, plataforma)?.crecimiento_neto ?? 0) > 0
                      ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                      : (getRedByPlataforma(cand, plataforma)?.crecimiento_neto ?? 0) < 0
                        ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400'
                        : 'bg-slate-500/15 text-slate-500'"
                  >
                    <ArrowUp v-if="(getRedByPlataforma(cand, plataforma)?.crecimiento_neto ?? 0) > 0" class="w-3 h-3" />
                    <ArrowDown v-else-if="(getRedByPlataforma(cand, plataforma)?.crecimiento_neto ?? 0) < 0" class="w-3 h-3" />
                    <Minus v-else class="w-3 h-3" />
                    {{ formatNum(Math.abs(getRedByPlataforma(cand, plataforma)?.crecimiento_neto ?? 0)) }}
                  </span>
                </td>

                <!-- % Crecimiento -->
                <td class="py-3 text-right">
                  <span
                    class="font-bold"
                    :class="(getRedByPlataforma(cand, plataforma)?.crecimiento_pct ?? 0) > 0
                      ? 'text-emerald-500'
                      : (getRedByPlataforma(cand, plataforma)?.crecimiento_pct ?? 0) < 0
                        ? 'text-rose-500'
                        : 'text-slate-400'"
                  >
                    {{ (getRedByPlataforma(cand, plataforma)?.crecimiento_pct ?? 0) > 0 ? '+' : '' }}{{ getRedByPlataforma(cand, plataforma)?.crecimiento_pct ?? 0 }}%
                  </span>
                </td>
              </template>

              <!-- Candidato sin esta plataforma -->
              <template v-else>
                <td class="py-3 pr-4">
                  <div class="flex items-center gap-2.5 opacity-40">
                    <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800 shrink-0" />
                    <span class="font-semibold text-slate-500">{{ cand.nombre }}</span>
                  </div>
                </td>
                <td colspan="4" class="py-3 text-slate-400 text-center italic">
                  Sin cuenta registrada en {{ platformLabel(plataforma) }}
                </td>
              </template>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Estado vacío -->
    <div
      v-if="candidatos.length === 0"
      class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center"
    >
      <Users class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
      <p class="font-bold text-slate-500 dark:text-slate-400">
        No hay candidatos registrados en este workspace para comparar.
      </p>
      <Link
        href="/candidatos"
        class="inline-block mt-4 px-4 py-2 rounded-xl bg-cyan-500 text-white text-xs font-bold hover:bg-cyan-400 transition-colors"
      >
        Agregar Rivales
      </Link>
    </div>
  </WarRoomLayout>
</template>
