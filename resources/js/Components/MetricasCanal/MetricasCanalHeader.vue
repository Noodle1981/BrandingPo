<script setup>
import { Link } from '@inertiajs/vue3';
import Badge from '../Badge.vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';
import {
  ArrowLeft,
  BarChart3,
  LineChart as LineChartIcon,
  ExternalLink,
  Vote,
  Sparkles,
  Target
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  perfilSocial: {
    type: Object,
    required: true,
  },
  territorioContexto: {
    type: Object,
    default: () => ({}),
  },
  alertaRedistribucionPauta: {
    type: Object,
    default: null,
  },
  benchmarks: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
  canalesCandidato: {
    type: Array,
    default: () => [],
  },
  getSocialMeta: {
    type: Function,
    required: true,
  },
  tabBadgeStyle: {
    type: Function,
    required: true,
  },
  formatNumber: {
    type: Function,
    required: true,
  },
});

defineEmits(['open-chart-modal']);
</script>

<template>
  <div class="space-y-6">
    <!-- Top Navigation & Return Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div class="flex items-center gap-3">
        <Link
          :href="candidato.es_propio ? '/mi-candidato' : `/candidatos/${candidato.id}`"
          class="p-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-cyan-500 hover:border-cyan-500/30 transition-all shadow-xs flex items-center justify-center cursor-pointer"
          title="Volver a la ficha del candidato"
        >
          <ArrowLeft class="w-4 h-4" />
        </Link>

        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <BarChart3 class="w-6 h-6 text-cyan-500" />
              <span>Dashboard de Métricas: {{ getSocialMeta(perfilSocial.plataforma).name }}</span>
            </h1>
            <Badge variant="estado" :value="candidato.estado_politico" size="sm" />
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Auditoría en tiempo real, objetivos algorítmicos, pauta vs orgánico y evolución de <strong>{{ candidato.nombre_completo }}</strong>.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2.5">
        <button
          type="button"
          @click="$emit('open-chart-modal')"
          class="px-4 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono inline-flex items-center gap-2 transition-all shadow-xs hover:scale-102 cursor-pointer"
        >
          <LineChartIcon class="w-4 h-4" />
          <span>Ver Gráfico Evolutivo</span>
        </button>

        <a
          v-if="perfilSocial.url_perfil"
          :href="perfilSocial.url_perfil"
          target="_blank"
          rel="noopener noreferrer"
          class="px-4 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-cyan-500/40 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono inline-flex items-center gap-2 transition-all shadow-xs"
        >
          <span>{{ perfilSocial.handle_usuario }}</span>
          <ExternalLink class="w-3.5 h-3.5 text-cyan-500" />
        </a>
      </div>
    </div>

    <!-- BARRA DE LOS 7 CANALES OFICIALES EN 1 SOLA FILA -->
    <div v-if="canalesCandidato && canalesCandidato.length > 0" class="space-y-2">
      <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 lg:grid-cols-7 gap-2.5 sm:gap-3">
        <component
          :is="canal.perfil_id && canal.color_estado !== 'gris' ? Link : 'a'"
          v-for="canal in canalesCandidato"
          :key="canal.key"
          :href="canal.perfil_id && canal.color_estado !== 'gris' ? `/perfiles-sociales/${canal.perfil_id}/metricas` : '/mi-candidato'"
          class="p-3 sm:p-3.5 rounded-2xl border-2 transition-all flex flex-col items-center justify-between text-center gap-2 cursor-pointer relative shadow-xs group"
          :class="[
            perfilSocial.plataforma === canal.key
              ? 'shadow-md scale-102 ' + tabBadgeStyle(canal.color_estado).tab
              : (canal.color_estado === 'gris'
                  ? 'border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-400 opacity-75 hover:opacity-100 hover:border-slate-400 hover:bg-white dark:hover:bg-slate-900'
                  : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-sm')
          ]"
        >
          <div class="flex items-center justify-center w-8 h-8 rounded-xl shadow-2xs shrink-0" :class="getSocialMeta(canal.key).bgLight">
            <SocialPlatformIcon :platform="canal.key" size="sm" />
          </div>

          <div class="min-w-0 w-full">
            <span class="font-bold text-xs leading-tight block text-slate-900 dark:text-slate-100 truncate">
              {{ canal.nombre }}
            </span>
            <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate mt-0.5 font-mono">
              {{ canal.handle_usuario || '@sin_configurar' }}
            </span>
          </div>

          <span
            class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider font-mono truncate max-w-full"
            :class="tabBadgeStyle(canal.color_estado).pill"
          >
            {{ tabBadgeStyle(canal.color_estado).label }}
          </span>
        </component>
      </div>
    </div>

    <!-- BANNER DE CONTEXTO TERRITORIO-FIRST -->
    <div class="p-5 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-900 to-cyan-950 border border-cyan-500/30 shadow-md text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="flex items-center gap-3.5">
        <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 flex items-center justify-center shrink-0">
          <Vote class="w-6 h-6" />
        </div>
        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-cyan-400">
              🏛️ Universo Rector de Campaña
            </span>
            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
              {{ territorioContexto.nombre || candidato.territorio_nombre || 'Albardón' }}
            </span>
          </div>
          <h2 class="text-lg sm:text-xl font-black text-slate-100 mt-0.5">
            Padrón Electoral: {{ formatNumber(territorioContexto.padron_electoral || candidato.padron_electoral || 24500) }} Electores
          </h2>
          <p class="text-xs text-slate-300 mt-0.5">
            Todas las métricas evalúan la penetración y movilización real sobre los votantes de este territorio.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 sm:gap-4 font-mono text-xs self-start md:self-auto flex-wrap">
        <div class="px-3.5 py-2 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-left">
          <span class="text-[10px] text-slate-400 uppercase block font-bold">Meta Regular (30%)</span>
          <span class="text-sm font-extrabold text-amber-400">
            {{ formatNumber(territorioContexto.meta_regular_vistas || (candidato.padron_electoral * 0.3)) }} vistas
          </span>
        </div>
        <div class="px-3.5 py-2 rounded-2xl bg-slate-800/80 border border-slate-700/80 text-left">
          <span class="text-[10px] text-slate-400 uppercase block font-bold">Meta Victoria (40%)</span>
          <span class="text-sm font-extrabold text-emerald-400">
            {{ formatNumber(territorioContexto.meta_ganadora_vistas || (candidato.padron_electoral * 0.4)) }} vistas
          </span>
        </div>
      </div>
    </div>

    <!-- ALERTA DE REDISTRIBUCIÓN DE PAUTA / ÉXITO VIRAL -->
    <div
      v-if="alertaRedistribucionPauta"
      class="p-5 rounded-3xl border text-xs leading-relaxed flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
      :class="alertaRedistribucionPauta.tipo === 'exito_viral' ? 'bg-amber-500/10 border-amber-500/30 text-amber-900 dark:text-amber-200' : 'bg-cyan-500/10 border-cyan-500/30 text-cyan-900 dark:text-cyan-200'"
    >
      <div class="flex items-start gap-3">
        <div class="p-2 rounded-xl bg-amber-500/20 text-amber-500 shrink-0 mt-0.5">
          <Sparkles class="w-5 h-5" />
        </div>
        <div class="space-y-1">
          <div class="font-extrabold text-sm flex items-center gap-2">
            <span>{{ alertaRedistribucionPauta.mensaje }}</span>
          </div>
          <p class="text-slate-600 dark:text-slate-300">
            🎯 <strong>Recomendación Táctica:</strong> {{ alertaRedistribucionPauta.accion_sugerida }}
          </p>
        </div>
      </div>

      <Link
        href="/territorios/impacto-electoral"
        class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs font-mono shrink-0 shadow-xs hover:scale-102 transition-all flex items-center gap-1.5"
      >
        <Target class="w-3.5 h-3.5" />
        <span>Ver Balance Multi-Red</span>
      </Link>
    </div>

    <!-- Ficha de Cabecera del Canal Auditado -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between flex-wrap gap-5">
      <div class="flex items-center gap-4">
        <div class="relative shrink-0">
          <img
            :src="perfilSocial.foto_perfil_url || candidato.avatar_url"
            alt="Foto Canal"
            referrerpolicy="no-referrer"
            class="w-16 h-16 rounded-2xl object-cover border-2 border-cyan-500 shadow-md"
          />
          <div
            class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900"
            :class="perfilSocial.esta_activo ? 'bg-amber-500 text-slate-950' : 'bg-rose-500 text-white'"
          >
            <span class="text-[9px] font-extrabold">●</span>
          </div>
        </div>

        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-slate-100">
              {{ perfilSocial.handle_usuario || '@cuenta' }}
            </h2>
            <span
              class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
              :class="{
                'bg-cyan-500/20 text-cyan-500 border border-cyan-500/30': perfilSocial.semaforo_color === 'azul',
                'bg-amber-500/20 text-amber-500 border border-amber-500/30': perfilSocial.semaforo_color === 'naranja',
                'bg-rose-500/20 text-rose-500 border border-rose-500/30': perfilSocial.semaforo_color === 'rojo'
              }"
            >
              {{ perfilSocial.esta_verificado ? 'Verificada' : (perfilSocial.esta_activo ? 'Canal Activo' : 'Inactivo') }}
            </span>
            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-md bg-purple-500/15 text-purple-400 border border-purple-500/30 uppercase">
              Tramo: {{ benchmarks.tramo_label || 'Nano (<10k)' }}
            </span>
            <span v-if="perfilSocial.fecha_punto_cero" class="text-xs font-mono text-slate-400">
              (Punto Alfa: {{ perfilSocial.fecha_punto_cero }})
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Última medición registrada: <strong>{{ perfilSocial.fecha_ultima_medicion || 'Hoy' }}</strong> ({{ perfilSocial.fecha_ultima_medicion_relativa || 'hace instantes' }})
          </p>
        </div>
      </div>

      <div class="flex items-center gap-3 font-mono text-xs">
        <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
          <span class="text-[10px] text-slate-400 block uppercase font-bold">Seguidores</span>
          <span class="text-base font-extrabold text-cyan-600 dark:text-cyan-400">
            {{ formatNumber(stats.seguidores_actuales) }}
          </span>
        </div>
        <div class="px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
          <span class="text-[10px] text-slate-400 block uppercase font-bold">Posts en Red</span>
          <span class="text-base font-extrabold text-slate-800 dark:text-slate-200">
            {{ formatNumber(stats.posts_actuales) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
