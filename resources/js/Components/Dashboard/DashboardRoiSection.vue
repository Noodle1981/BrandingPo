<script setup>
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import Badge from '../Badge.vue';
import { DollarSign, Rocket, ExternalLink, ArrowRight } from '@lucide/vue';
import { useFormatters } from '../../composables/useFormatters';

const props = defineProps({
  organicoVsPauta: {
    type: Object,
    default: () => ({
      total_posts_organicos: 0,
      total_posts_pautados: 0,
      vistas_organicas: 0,
      vistas_pagadas: 0,
      interacciones_organicas: 0,
      interacciones_pautadas: 0,
      porcentaje_vistas_organicas: 100,
      porcentaje_vistas_pagadas: 0,
      inversion_total: 0,
      costo_por_interaccion: 0,
      cpm_estimado: 0,
      posts_impulsados: []
    })
  }
});

const { formatCurrency, formatNumber } = useFormatters();

const selectedBoostIndex = ref(0);

watch(() => props.organicoVsPauta?.posts_impulsados, (newPosts) => {
  if (!newPosts || selectedBoostIndex.value >= newPosts.length) {
    selectedBoostIndex.value = 0;
  }
});

const activeBoostedPost = computed(() => {
  const posts = props.organicoVsPauta?.posts_impulsados;
  if (Array.isArray(posts) && posts.length > 0) {
    return posts[selectedBoostIndex.value] || posts[0];
  }
  return props.organicoVsPauta?.primer_post_impulsado || null;
});
</script>

<template>
  <div class="p-5 sm:p-6 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 shadow-md space-y-4 flex flex-col justify-between">
    <div>
      <!-- Cabecera del Módulo con Toggle / Badge de Estado -->
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-mono uppercase tracking-wider text-cyan-400 font-bold flex items-center gap-1.5">
          <DollarSign class="w-4 h-4" />
          Eficiencia Publicitaria (ROI)
        </span>
        <span v-if="organicoVsPauta?.posts_impulsados && organicoVsPauta.posts_impulsados.length > 0" class="text-[10px] px-2.5 py-0.5 rounded-full bg-violet-500/25 text-violet-300 font-mono font-bold flex items-center gap-1 border border-violet-500/30">
          <Rocket class="w-3 h-3 text-violet-400" />
          <span>{{ organicoVsPauta.posts_impulsados.length }} Booster(s) Activo(s)</span>
        </span>
        <span v-else class="text-[10px] px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-400 font-mono border border-slate-700">
          Sin Pauta Activa
        </span>
      </div>

      <!-- Métricas Generales de Inversión y Eficiencia -->
      <div class="grid grid-cols-2 gap-3 font-mono">
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
          <span class="text-[10px] text-slate-400 block uppercase">Inversión Total</span>
          <span class="text-xl font-extrabold text-white">{{ formatCurrency(organicoVsPauta?.inversion_total ?? 0) }}</span>
          <span class="text-[10px] text-slate-400 block mt-0.5">{{ organicoVsPauta?.total_posts_pautados ?? 0 }} anuncio(s) activo(s)</span>
        </div>
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10">
          <span class="text-[10px] text-slate-400 block uppercase">
            {{ activeBoostedPost?.costo_por_like ? 'Costo / Like Ganado' : 'Costo / Interacción' }}
          </span>
          <span class="text-xl font-extrabold text-emerald-400">
            {{ formatCurrency(activeBoostedPost?.costo_por_like ?? organicoVsPauta?.costo_por_interaccion ?? 0) }}
          </span>
          <span class="text-[10px] text-slate-400 block mt-0.5">
            {{ activeBoostedPost ? `CPL ${activeBoostedPost.plataforma}` : 'por reacción/comentario' }}
          </span>
        </div>
      </div>

      <!-- Pestañas para Alternar entre Todas las Publicaciones con Booster -->
      <div v-if="organicoVsPauta?.posts_impulsados && organicoVsPauta.posts_impulsados.length > 1" class="mt-3 flex items-center gap-1.5 overflow-x-auto pb-1">
        <button
          v-for="(postPauta, idx) in organicoVsPauta.posts_impulsados"
          :key="postPauta.id"
          type="button"
          @click="selectedBoostIndex = idx"
          class="px-2.5 py-1.5 rounded-xl text-xs font-mono font-bold flex items-center gap-1.5 transition-all cursor-pointer border shrink-0"
          :class="selectedBoostIndex === idx
            ? 'bg-violet-500/25 text-violet-200 border-violet-400 ring-1 ring-violet-400/40 shadow-xs'
            : 'bg-white/5 text-slate-400 border-white/10 hover:bg-white/10 hover:text-slate-200'"
        >
          <Badge :variant="postPauta.plataforma" size="sm" />
          <span class="capitalize">{{ postPauta.plataforma }}</span>
          <span class="text-[10px] opacity-75 font-normal">({{ formatCurrency(postPauta.monto_invertido) }})</span>
        </button>
      </div>

      <!-- DETALLE DE AUDITORÍA DEL BOOSTER ACTIVO -->
      <div v-if="activeBoostedPost" class="mt-3.5 p-3.5 rounded-2xl bg-slate-900/90 border border-violet-500/30 space-y-2.5">
        <!-- Header del Post Impulsado -->
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0 flex items-center gap-2">
            <Badge :variant="activeBoostedPost.plataforma" size="sm" />
            <span class="text-xs font-bold text-slate-200 truncate" :title="activeBoostedPost.titulo">
              {{ activeBoostedPost.titulo }}
            </span>
          </div>
          <a
            v-if="activeBoostedPost.url_post"
            :href="activeBoostedPost.url_post"
            target="_blank"
            rel="noopener noreferrer"
            class="text-slate-400 hover:text-cyan-400 p-1 shrink-0"
            title="Ver publicación oficial"
          >
            <ExternalLink class="w-3.5 h-3.5" />
          </a>
        </div>

        <!-- Desglose Granular: Base Orgánica vs Ganado con Booster -->
        <div class="grid grid-cols-2 gap-2 pt-1 font-mono text-[11px]">
          <!-- Columna 1: Tracción Orgánica Previa al Corte -->
          <div class="p-2 rounded-xl bg-cyan-500/10 border border-cyan-500/20">
            <span class="text-[9px] uppercase tracking-wider text-cyan-400 block font-bold">Base Orgánica (Previa)</span>
            <span class="text-sm font-extrabold text-cyan-300">
              {{ formatNumber(activeBoostedPost.base_organica_likes) }} likes
            </span>
            <span class="text-[9px] text-cyan-400/80 block mt-0.5">
              Tracción natural sin gasto ({{ activeBoostedPost.pct_organico }}%)
            </span>
          </div>

          <!-- Columna 2: Ganado desde que se puso el Booster -->
          <div class="p-2 rounded-xl bg-violet-500/15 border border-violet-500/30">
            <span class="text-[9px] uppercase tracking-wider text-violet-300 block font-bold flex items-center gap-1">
              <Rocket class="w-2.5 h-2.5 text-violet-400" />
              <span>Ganados con Pauta</span>
            </span>
            <span class="text-sm font-extrabold text-emerald-400">
              +{{ formatNumber(activeBoostedPost.ganados_pauta_likes) }} likes
            </span>
            <span class="text-[9px] text-violet-300/80 block mt-0.5">
              Generados por Booster ({{ activeBoostedPost.pct_pautado }}%)
            </span>
          </div>
        </div>

        <!-- Barra de Distribución Orgánico vs Pautado del Post -->
        <div class="space-y-1 text-xs">
          <div class="flex justify-between text-[10px] font-mono">
            <span class="text-cyan-400 font-bold">Orgánico: {{ activeBoostedPost.pct_organico }}%</span>
            <span class="text-violet-400 font-bold">Booster: {{ activeBoostedPost.pct_pautado }}%</span>
          </div>
          <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden flex">
            <div class="bg-cyan-500 h-full transition-all" :style="{ width: `${activeBoostedPost.pct_organico}%` }"></div>
            <div class="bg-violet-500 h-full transition-all" :style="{ width: `${activeBoostedPost.pct_pautado}%` }"></div>
          </div>
        </div>

        <!-- Estado del Booster y Fecha de Corte -->
        <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-800/80 font-mono">
          <span class="flex items-center gap-1 text-slate-300">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Estado: <strong>{{ activeBoostedPost.estado }}</strong></span>
          </span>
          <span v-if="activeBoostedPost.fecha_booster">
            Corte: {{ activeBoostedPost.fecha_booster }}
          </span>
        </div>
      </div>

      <!-- SI NO HAY POST IMPULSADO EN EL PERÍODO -->
      <div v-else class="mt-4 p-4 rounded-2xl bg-white/5 border border-white/10 text-center space-y-1 text-xs text-slate-400">
        <DollarSign class="w-5 h-5 mx-auto text-slate-500" />
        <p class="font-bold text-slate-300">Sin anuncios con pauta en este período</p>
        <p class="text-[11px] opacity-80">El 100% de la tracción y alcance del período proviene de fuentes orgánicas puras.</p>
      </div>
    </div>

    <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
      <span>
        Total Post: <strong class="text-white font-mono">{{ activeBoostedPost ? formatNumber(activeBoostedPost.total_likes) + ' likes' : formatCurrency(organicoVsPauta?.cpm_estimado ?? 0) }}</strong>
      </span>
      <Link href="/analytics" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
        <span>Simulador de Presupuesto</span>
        <ArrowRight class="w-3.5 h-3.5" />
      </Link>
    </div>
  </div>
</template>
