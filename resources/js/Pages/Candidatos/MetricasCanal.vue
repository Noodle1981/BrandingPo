<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  ArrowLeft,
  Sparkles,
  ExternalLink,
  TrendingUp,
  TrendingDown,
  Users,
  Film,
  Flame,
  Heart,
  MessageCircle,
  Share2,
  Bookmark,
  DollarSign,
  Activity,
  Calendar,
  Layers,
  Target,
  BarChart3,
  CheckCircle2,
  AlertCircle
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
  stats: {
    type: Object,
    default: () => ({}),
  },
  historicoMediciones: {
    type: Array,
    default: () => [],
  },
  topPublicaciones: {
    type: Array,
    default: () => [],
  },
  distribucionEjes: {
    type: Array,
    default: () => [],
  },
  ejes: {
    type: Array,
    default: () => [],
  }
});

const formatNumber = (n) => {
  return Number(n || 0).toLocaleString('es-AR');
};

const formatCurrency = (n) => {
  return '$' + Number(n || 0).toLocaleString('es-AR');
};

const getSocialMeta = (key) => {
  switch (key) {
    case 'instagram':
      return { color: '#E4405F', bgLight: 'bg-[#E4405F]/15', name: 'Instagram' };
    case 'facebook':
      return { color: '#1877F2', bgLight: 'bg-[#1877F2]/15', name: 'Facebook' };
    case 'tiktok':
      return { color: '#00F2FE', bgLight: 'bg-cyan-500/15', name: 'TikTok' };
    case 'youtube':
      return { color: '#FF0000', bgLight: 'bg-red-500/15', name: 'YouTube' };
    case 'x_twitter':
      return { color: '#000000', bgLight: 'bg-slate-500/15', name: 'X (Twitter)' };
    default:
      return { color: '#06b6d4', bgLight: 'bg-cyan-500/15', name: 'Red Social' };
  }
};
</script>

<template>
  <Head :title="`Métricas ${getSocialMeta(perfilSocial.plataforma).name} - ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
      
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
              Auditoría en tiempo real, evolución desde Punto Cero y engagement analítico de <strong>{{ candidato.nombre_completo }}</strong>.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2.5">
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
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-amber-500 text-slate-950">
                {{ perfilSocial.semaforo_color ? `Canal ${perfilSocial.semaforo_color}` : 'Activo' }}
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

      <!-- Grid de KPIs Clave de Rendimiento del Canal -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Crecimiento Neto de Seguidores -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Crecimiento Neto</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
              <TrendingUp class="w-4 h-4" />
            </div>
          </div>
          <div class="flex items-baseline gap-2">
            <span class="text-2xl font-black font-mono" :class="stats.crecimiento_neto_seguidores >= 0 ? 'text-emerald-500' : 'text-rose-500'">
              {{ stats.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ formatNumber(stats.crecimiento_neto_seguidores) }}
            </span>
            <span class="text-xs font-mono font-bold text-emerald-500">
              ({{ stats.crecimiento_pct_seguidores }}%)
            </span>
          </div>
          <p class="text-[11px] text-slate-400">
            Desde Punto Alfa: {{ formatNumber(stats.seguidores_punto_cero) }} iniciales
          </p>
        </div>

        <!-- 2. Interacciones Totales -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">🔥 Interacciones</span>
            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
              <Flame class="w-4 h-4 fill-current" />
            </div>
          </div>
          <div>
            <span class="text-2xl font-black font-mono text-cyan-500">
              {{ formatNumber(stats.total_interacciones) }}
            </span>
          </div>
          <p class="text-[11px] text-slate-400">
            {{ formatNumber(stats.total_likes) }} likes &bull; {{ formatNumber(stats.total_comentarios) }} comentarios
          </p>
        </div>

        <!-- 3. Tasa de Engagement Promedio -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Engagement Rate</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Activity class="w-4 h-4" />
            </div>
          </div>
          <div>
            <span class="text-2xl font-black font-mono text-blue-500">
              {{ stats.tasa_engagement }}%
            </span>
          </div>
          <p class="text-[11px] text-slate-400">
            Promedio: {{ stats.promedio_likes_por_post }} likes / post
          </p>
        </div>

        <!-- 4. Pauta Publicitaria Acumulada -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">Pauta Invertida</span>
            <div class="w-8 h-8 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center">
              <DollarSign class="w-4 h-4" />
            </div>
          </div>
          <div>
            <span class="text-2xl font-black font-mono text-violet-500">
              {{ formatCurrency(stats.total_pauta_invertida) }}
            </span>
          </div>
          <p class="text-[11px] text-slate-400">
            {{ formatNumber(stats.total_publicaciones_registradas) }} publicaciones auditadas
          </p>
        </div>
      </div>

      <!-- Sección 1: Time-Series Histórico de Auditorías Punto Cero -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-2">
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Activity class="w-5 h-5 text-cyan-500" />
            <span>Evolución Temporal de Auditorías (Time-Series)</span>
          </h3>
          <span class="text-xs font-mono text-slate-400">
            {{ historicoMediciones.length }} mediciones registradas
          </span>
        </div>

        <div v-if="historicoMediciones.length > 0" class="overflow-x-auto">
          <table class="w-full text-xs font-mono text-left">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400">
                <th class="py-2.5 px-3">Fecha de Medición</th>
                <th class="py-2.5 px-3 text-right">Seguidores</th>
                <th class="py-2.5 px-3 text-right">Crecimiento Neto</th>
                <th class="py-2.5 px-3 text-right">Seguidos</th>
                <th class="py-2.5 px-3 text-right">Posts Totales</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              <tr
                v-for="med in historicoMediciones.slice().reverse()"
                :key="med.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
              >
                <td class="py-3 px-3 font-semibold text-slate-700 dark:text-slate-300">
                  📅 {{ med.fecha }}
                </td>
                <td class="py-3 px-3 text-right font-extrabold text-cyan-600 dark:text-cyan-400">
                  {{ formatNumber(med.seguidores) }}
                </td>
                <td class="py-3 px-3 text-right font-bold" :class="med.crecimiento_neto_seguidores >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                  {{ med.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ formatNumber(med.crecimiento_neto_seguidores) }}
                </td>
                <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-400">
                  {{ formatNumber(med.seguidos) }}
                </td>
                <td class="py-3 px-3 text-right text-slate-800 dark:text-slate-200">
                  {{ formatNumber(med.publicaciones) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="p-8 text-center text-slate-400 text-xs font-mono">
          Aún no hay registros en la serie temporal. Presiona "Auditar Ahora (1 Clic)" en el perfil para registrar mediciones.
        </div>
      </div>

      <!-- Sección 2: Distribución de Rendimiento por Eje Temático -->
      <div v-if="distribucionEjes.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Target class="w-5 h-5 text-cyan-500" />
          <span>Interacciones por Eje Temático de Campaña</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="eje in distribucionEjes"
            :key="eje.id"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate">
                🎯 {{ eje.nombre }}
              </span>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-cyan-500/15 text-cyan-600 dark:text-cyan-400">
                {{ eje.total_posts }} posts
              </span>
            </div>
            <div class="flex items-center justify-between font-mono text-xs pt-1">
              <span class="text-slate-400">Interacciones:</span>
              <span class="font-extrabold text-cyan-500">🔥 {{ formatNumber(eje.total_interacciones) }}</span>
            </div>
            <div class="flex items-center justify-between font-mono text-[11px] text-slate-400">
              <span>❤️ {{ formatNumber(eje.total_likes) }} likes</span>
              <span>💬 {{ formatNumber(eje.total_comentarios) }} coment</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección 3: Top Publicaciones Más Virales del Canal -->
      <div v-if="topPublicaciones.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Flame class="w-5 h-5 text-cyan-500 fill-current" />
          <span>Top Publicaciones con Mayor Tracción en este Canal</span>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="(top, i) in topPublicaciones"
            :key="top.id"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between"
          >
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs font-mono">
                <span class="px-2 py-0.5 rounded-md bg-cyan-500 text-slate-950 font-black text-[10px]">
                  #{{ i + 1 }} TOP
                </span>
                <span class="text-slate-400 text-[11px]">{{ top.fecha_relativa || top.fecha_publicacion }}</span>
              </div>
              <p class="text-xs text-slate-800 dark:text-slate-200 font-medium line-clamp-3">
                {{ top.contenido_resumen }}
              </p>
            </div>

            <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between font-mono text-xs">
              <span class="font-extrabold text-cyan-600 dark:text-cyan-400">
                🔥 {{ formatNumber(top.total_interacciones) }}
              </span>
              <span class="text-slate-500">
                ❤️ {{ formatNumber(top.total_likes) }} &bull; 💬 {{ formatNumber(top.total_comentarios) }}
              </span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </WarRoomLayout>
</template>
