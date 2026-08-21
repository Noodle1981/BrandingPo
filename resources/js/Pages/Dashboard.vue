<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import WarRoomLayout from '../Layouts/WarRoomLayout.vue';
import MetricCard from '../Components/MetricCard.vue';
import SocialCard from '../Components/SocialCard.vue';
import Badge from '../Components/Badge.vue';
import {
  Users,
  Eye,
  TrendingUp,
  DollarSign,
  Radio,
  Sparkles,
  ArrowRight,
  Flame,
  ShieldCheck,
  Zap,
  Target,
  Newspaper,
  ExternalLink,
  MapPin,
  Vote,
  Layers,
  ChevronDown
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    default: null
  },
  candidatos_lista: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_seguidores: '0',
      total_vistas: '0',
      total_publicaciones: 0,
      engagement_promedio: '0%',
      inversion_pauta_total: 0,
      humor_social_promedio: '4.5',
      ratio_penetracion: '0%',
      share_of_voice: '0%',
    })
  },
  redes_desglose: {
    type: Array,
    default: () => []
  },
  ultimas_publicaciones: {
    type: Array,
    default: () => []
  },
  ultimas_notas_prensa: {
    type: Array,
    default: () => []
  }
});

const cambiarCandidato = (event) => {
  const id = event.target.value;
  router.get('/dashboard', { candidato_id: id }, { preserveState: true, replace: true });
};

const formatCurrency = (amount) => {
  if (!amount) return '$0';
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(amount);
};

const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};

const plataformaIconColor = (plataforma) => {
  switch (plataforma) {
    case 'facebook': return { bg: 'bg-[#1877F2]/10 text-[#1877F2] border-[#1877F2]/30', name: 'Facebook' };
    case 'instagram': return { bg: 'bg-[#E4405F]/10 text-[#E4405F] border-[#E4405F]/30', name: 'Instagram' };
    case 'x_twitter': return { bg: 'bg-slate-900/10 dark:bg-white/10 text-slate-900 dark:text-white border-slate-400/30', name: 'X / Twitter' };
    case 'tiktok': return { bg: 'bg-[#00F2FE]/10 text-[#00F2FE] border-[#00F2FE]/30', name: 'TikTok' };
    case 'youtube': return { bg: 'bg-[#FF0000]/10 text-[#FF0000] border-[#FF0000]/30', name: 'YouTube' };
    case 'linkedin': return { bg: 'bg-[#0A66C2]/10 text-[#0A66C2] border-[#0A66C2]/30', name: 'LinkedIn' };
    default: return { bg: 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30', name: plataforma };
  }
};
</script>

<template>
  <Head :title="candidato ? `Panel: ${candidato.nombre_completo}` : 'Dashboard del Perfil'" />

  <WarRoomLayout>
    <!-- 1. Perfil del Cliente / Candidato Principal Header -->
    <div v-if="candidato" class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
        <!-- Candidate Identity & Details -->
        <div class="flex items-start sm:items-center gap-4">
          <div class="relative shrink-0">
            <img
              :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="candidato.nombre_completo"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 shadow-md"
              :style="{ borderColor: candidato.color_hex || '#06b6d4' }"
            />
          </div>

          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {{ candidato.nombre_completo }}
              </h1>
              <Badge variant="estado" :value="candidato.estado_politico" size="sm" />
            </div>

            <p class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300 mt-0.5">
              {{ candidato.cargo_aspirado }} &bull; <span class="text-slate-500 dark:text-slate-400 font-normal">{{ candidato.partido_coalicion }}</span>
            </p>

            <div class="mt-2 flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
              <span class="inline-flex items-center gap-1">
                <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                {{ candidato.territorio_nombre }}
              </span>
              <span v-if="candidato.padron_electoral" class="inline-flex items-center gap-1 font-mono">
                <Vote class="w-3.5 h-3.5 text-emerald-500" />
                Padrón: {{ Number(candidato.padron_electoral).toLocaleString('es-AR') }} electores
              </span>
              <span class="inline-flex items-center gap-1">
                <Layers class="w-3.5 h-3.5 text-violet-500" />
                {{ candidato.ciclo_nombre }}
              </span>
            </div>
          </div>
        </div>

        <!-- Selector & Quick Actions -->
        <div class="flex items-center gap-2.5 flex-wrap self-start md:self-center">
          <!-- Selector de Candidato -->
          <div v-if="candidatos_lista.length > 1" class="relative">
            <select
              :value="candidato.id"
              @change="cambiarCandidato"
              class="appearance-none bg-slate-100 dark:bg-slate-800/90 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl pl-3 pr-8 py-2.5 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all cursor-pointer"
            >
              <option v-for="cand in candidatos_lista" :key="cand.id" :value="cand.id">
                {{ cand.es_propio ? '⭐ ' : '' }}{{ cand.nombre_completo }} ({{ cand.cargo_aspirado }})
              </option>
            </select>
            <ChevronDown class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>

          <Link
            href="/fast-flow"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs transition-all shadow-sm hover:scale-102"
          >
            <Zap class="w-3.5 h-3.5" />
            <span>Cargar Post</span>
          </Link>
          <Link
            href="/feed"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold text-xs transition-all"
          >
            <Radio class="w-3.5 h-3.5 text-cyan-500" />
            <span>Ver Feed</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- 2. HUD Key Metrics Grid (Enfocado en el Cliente) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <MetricCard
        title="Comunidad Multired"
        :value="stats.total_seguidores"
        subtitle="Seguidores acumulados en todas las redes"
        :icon="Users"
        color="cyan"
      />
      <MetricCard
        title="Visualizaciones Totales"
        :value="stats.total_vistas"
        subtitle="Impacto de video y publicaciones"
        :icon="Eye"
        color="emerald"
      />
      <MetricCard
        title="Tasa de Engagement"
        :value="stats.engagement_promedio"
        subtitle="Interacción activa (likes, comments, shares)"
        :icon="TrendingUp"
        color="violet"
      />
      <MetricCard
        title="Clima de Aceptación"
        :value="`${stats.humor_social_promedio} / 5`"
        subtitle="Termómetro de opinión pública"
        :icon="Flame"
        color="amber"
      />
    </div>

    <!-- 3. Desglose de Canales Sociales del Cliente -->
    <div v-if="redes_desglose.length > 0" class="space-y-3">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 uppercase tracking-wider font-mono flex items-center gap-2">
          <span>Canales Sociales Conectados</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-normal">
            {{ redes_desglose.length }} redes activas
          </span>
        </h2>
        <Link href="/candidatos" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
          Gestionar perfiles &rarr;
        </Link>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div
          v-for="red in redes_desglose"
          :key="red.id"
          class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between"
        >
          <div class="flex items-center justify-between gap-1 mb-2">
            <span
              class="text-[11px] font-bold px-2 py-0.5 rounded-md border uppercase font-mono tracking-wider"
              :class="plataformaIconColor(red.plataforma).bg"
            >
              {{ plataformaIconColor(red.plataforma).name }}
            </span>
            <span class="text-[10px] text-slate-400 font-mono">{{ red.publicaciones_count }} posts</span>
          </div>

          <div class="space-y-0.5">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 truncate" :title="red.handle_usuario">
              {{ red.handle_usuario || '@cuenta' }}
            </p>
            <p class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-slate-100 font-mono">
              {{ formatNumber(red.seguidores) }}
            </p>
            <span class="text-[10px] text-slate-400 block">seguidores</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Contenidos del Cliente & Observatorio de Prensa (Dos Columnas) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Columna Izquierda (7 cols): Últimas Publicaciones del Cliente -->
      <div class="lg:col-span-7 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Radio class="w-4 h-4 text-cyan-500" />
              <span>Publicaciones Recientes del Perfil</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Rendimiento, formatos y engagement de sus contenidos</p>
          </div>
          <Link href="/feed" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
            <span>Ver todo el feed</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </Link>
        </div>

        <div v-if="ultimas_publicaciones.length > 0" class="space-y-4">
          <SocialCard
            v-for="post in ultimas_publicaciones"
            :key="post.id"
            :post="post"
          />
        </div>

        <div v-else class="p-8 text-center rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500">
          <p class="text-sm">No hay publicaciones registradas recientemente para este perfil.</p>
          <Link
            href="/fast-flow"
            class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs"
          >
            <Zap class="w-3.5 h-3.5" />
            <span>Cargar primera publicación</span>
          </Link>
        </div>
      </div>

      <!-- Columna Derecha (5 cols): Menciones en Prensa & Benchmark Estratégico -->
      <div class="lg:col-span-5 space-y-5">
        <!-- Menciones en Prensa Digital -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Newspaper class="w-4 h-4 text-cyan-500" />
              <span>Menciones en Diarios & Prensa</span>
            </h2>
            <Link href="/medios" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
              Observatorio &rarr;
            </Link>
          </div>

          <div v-if="ultimas_notas_prensa.length > 0" class="space-y-2.5">
            <a
              v-for="nota in ultimas_notas_prensa"
              :key="nota.id"
              :href="nota.url_nota || '#'"
              target="_blank"
              class="block p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200/60 dark:border-slate-700/60 transition-all group"
            >
              <div class="flex items-center justify-between gap-2 mb-1">
                <span class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 uppercase font-mono">
                  {{ nota.medio_nombre }}
                </span>
                <Badge variant="tono" :value="nota.tono_mencion" size="xs" />
              </div>
              <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors line-clamp-2 leading-relaxed">
                {{ nota.titulo }}
              </p>
              <span class="text-[10px] text-slate-400 mt-1 block font-mono">{{ nota.fecha }}</span>
            </a>
          </div>

          <div v-else class="text-xs text-slate-400 text-center py-4">
            No se registran notas de prensa vinculadas al candidato.
          </div>
        </div>

        <!-- Tarjeta de Diagnóstico & Posicionamiento Contextual -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 shadow-md space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-mono uppercase tracking-wider text-cyan-400 font-bold flex items-center gap-1.5">
              <Target class="w-3.5 h-3.5" />
              Diagnóstico de Campaña
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-mono">
              Estratégico
            </span>
          </div>

          <div class="grid grid-cols-2 gap-3 font-mono">
            <div class="p-3 rounded-xl bg-white/5 border border-white/10">
              <span class="text-[10px] text-slate-400 block uppercase">Share of Voice</span>
              <span class="text-lg font-bold text-cyan-400">{{ stats.share_of_voice }}</span>
              <span class="text-[9px] text-slate-400 block mt-0.5">de la atención digital</span>
            </div>
            <div class="p-3 rounded-xl bg-white/5 border border-white/10">
              <span class="text-[10px] text-slate-400 block uppercase">Penetración Padrón</span>
              <span class="text-lg font-bold text-emerald-400">{{ stats.ratio_penetracion }}</span>
              <span class="text-[9px] text-slate-400 block mt-0.5">sobre votantes locales</span>
            </div>
          </div>

          <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <span>Inversión en pauta: <strong class="text-white font-mono">{{ formatCurrency(stats.inversion_pauta_total) }}</strong></span>
            <Link href="/predictor" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
              <span>Simulador ROI</span>
              <ArrowRight class="w-3 h-3" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  </WarRoomLayout>
</template>
