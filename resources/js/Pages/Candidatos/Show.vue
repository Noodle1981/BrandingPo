<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import MetricCard from '../../Components/MetricCard.vue';
import {
  Users,
  ArrowLeft,
  Calendar,
  MapPin,
  Sparkles,
  Radio,
  ExternalLink,
  Shield,
  BarChart2
} from '@lucide/vue';

defineProps({
  candidato: {
    type: Object,
    required: true,
  }
});

const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};
</script>

<template>
  <Head :title="`Ficha: ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <!-- Back Link -->
    <div>
      <Link
        href="/candidatos"
        class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors"
      >
        <ArrowLeft class="w-4 h-4" />
        <span>Volver al Catálogo de Candidatos</span>
      </Link>
    </div>

    <!-- Candidate Main Dossier Header Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs relative overflow-hidden">
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
        <!-- Avatar with Color Ring -->
        <div class="relative shrink-0">
          <img
            :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&size=150`"
            :alt="candidato.nombre_completo"
            class="w-24 h-24 rounded-3xl object-cover border-4 shadow-lg"
            :style="{ borderColor: candidato.color_hex || '#06b6d4' }"
          />
          <div
            v-if="candidato.es_propio"
            class="absolute -top-2 -right-2 bg-cyan-500 text-slate-950 px-2 py-0.5 rounded-md font-mono text-[10px] font-extrabold uppercase shadow-sm"
          >
            PROPIO
          </div>
        </div>

        <!-- Details -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              {{ candidato.nombre_completo }}
            </h1>
            <Badge variant="estado" :value="candidato.estado_politico" size="md" />
          </div>

          <p class="text-sm font-semibold text-slate-600 dark:text-slate-400 mt-1">
            {{ candidato.partido_coalicion }} <span v-if="candidato.cargo_aspirado">• {{ candidato.cargo_aspirado }}</span>
          </p>

          <div class="mt-3 flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
            <span v-if="candidato.ciclo_campana" class="flex items-center gap-1">
              <Calendar class="w-3.5 h-3.5 text-cyan-500" />
              <span>{{ candidato.ciclo_campana.nombre }}</span>
            </span>
            <span v-if="candidato.territorio" class="flex items-center gap-1">
              <MapPin class="w-3.5 h-3.5 text-emerald-500" />
              <span>{{ candidato.territorio.nombre }} ({{ candidato.territorio.tipo }})</span>
            </span>
          </div>
        </div>

        <!-- Total Audiencia Big Pill -->
        <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center shrink-0">
          <span class="text-[10px] uppercase font-mono tracking-wider text-slate-400 block font-semibold">
            Audiencia Multired
          </span>
          <span class="text-2xl sm:text-3xl font-extrabold font-mono text-cyan-600 dark:text-cyan-400">
            {{ formatNumber(candidato.total_seguidores) }}
          </span>
          <span class="text-[11px] text-slate-500 block mt-0.5">Seguidores Acumulados</span>
        </div>
      </div>

      <!-- Strategic Bio -->
      <div v-if="candidato.bio_resumen" class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800/80">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5 font-mono">
          📋 Enfoque & Resumen Estratégico:
        </h4>
        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
          {{ candidato.bio_resumen }}
        </p>
      </div>
    </div>

    <!-- Social Profiles Matrix -->
    <div>
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Radio class="w-5 h-5 text-cyan-500" />
            <span>Presencia en Redes Sociales ({{ candidato.perfiles_sociales?.length || 0 }})</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400">Canales oficiales monitoreados y auditados</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="perfil in candidato.perfiles_sociales"
          :key="perfil.id"
          class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between"
        >
          <div class="flex items-center justify-between">
            <Badge :variant="perfil.plataforma" size="md" />
            <span class="text-xs font-mono text-slate-400 font-bold">
              {{ perfil.publicaciones_totales || 0 }} posts
            </span>
          </div>

          <div class="mt-4">
            <span class="text-sm font-bold text-slate-900 dark:text-slate-100 block font-mono">
              {{ perfil.handle_usuario }}
            </span>
            <div class="mt-2 flex items-baseline justify-between">
              <span class="text-xs text-slate-500 dark:text-slate-400">Seguidores:</span>
              <span class="text-lg font-extrabold font-mono text-slate-900 dark:text-slate-100">
                {{ formatNumber(perfil.seguidores_actuales) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </WarRoomLayout>
</template>
