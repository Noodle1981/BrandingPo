<script setup>
import { Link } from '@inertiajs/vue3';
import Badge from '../Badge.vue';
import {
  Calendar,
  Activity,
  Maximize2,
  Target,
  Flame,
  ExternalLink
} from '@lucide/vue';

const props = defineProps({
  consistenciaMensual: {
    type: Array,
    default: () => [],
  },
  benchmarks: {
    type: Object,
    default: () => ({}),
  },
  historicoMediciones: {
    type: Array,
    default: () => [],
  },
  distribucionEjes: {
    type: Array,
    default: () => [],
  },
  topPublicaciones: {
    type: Array,
    default: () => [],
  },
  perfilSocial: {
    type: Object,
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
    <!-- 6. CONSISTENCIA MENSUAL & CADENCIA HISTÓRICA (Últimos 6 Meses) -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
      <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Calendar class="w-5 h-5 text-cyan-500" />
            <span>Consistencia Mensual de Publicación (Últimos 6 Meses)</span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Auditoría de meses activos para evitar caídas en el algoritmo.
          </p>
        </div>
        <span class="text-xs font-mono text-slate-400">
          Meta Mensual: <strong>≥ {{ benchmarks.posts_semana_ideal * 4 }} posts</strong>
        </span>
      </div>

      <div class="grid grid-flow-col auto-cols-[minmax(165px,1fr)] lg:auto-cols-fr gap-3 overflow-x-auto pb-2 scrollbar-thin">
        <div
          v-for="mes in consistenciaMensual"
          :key="mes.mes_key"
          class="p-4 rounded-2xl border transition-all space-y-2.5 flex flex-col justify-between"
          :class="{
            'bg-emerald-500/10 border-emerald-500/40 shadow-xs': mes.estado === 'excelente',
            'bg-amber-500/10 border-amber-500/40': mes.estado === 'adecuado',
            'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800': mes.estado === 'bajo',
            'bg-rose-500/10 border-rose-500/50 dark:bg-rose-950/20': mes.estado === 'inactivo_perdido',
            'border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 opacity-75': mes.estado === 'sin_configurar' || mes.estado === 'pendiente',
          }"
        >
          <div>
            <div class="flex items-center justify-between gap-1.5 mb-1.5">
              <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100 font-mono truncate">
                {{ mes.mes_nombre }}
              </span>
              <span
                class="w-2.5 h-2.5 rounded-full shrink-0"
                :class="{
                  'bg-emerald-500': mes.estado === 'excelente',
                  'bg-amber-500': mes.estado === 'adecuado',
                  'bg-slate-400': mes.estado === 'bajo',
                  'bg-rose-500 animate-pulse shadow-sm shadow-rose-500/50': mes.estado === 'inactivo_perdido',
                  'bg-slate-300 dark:bg-slate-600': mes.estado === 'sin_configurar' || mes.estado === 'pendiente',
                }"
                :title="mes.estado"
              ></span>
            </div>

            <div class="mb-2">
              <Badge :variant="mes.estado" :value="mes.estado" size="sm" />
            </div>

            <div v-if="mes.estado === 'inactivo_perdido'">
              <span class="text-base font-black font-mono block text-rose-500">
                0 posts
              </span>
              <span class="text-[10px] font-mono text-rose-500/90 font-bold block mt-0.5">
                ⚠️ Mes Inactivo / Perdido
              </span>
            </div>
            <div v-else-if="mes.estado === 'sin_configurar' || mes.estado === 'pendiente'">
              <span class="text-base font-black font-mono block text-slate-400">
                0 posts
              </span>
              <span class="text-[10px] font-mono text-slate-400 font-medium block mt-0.5">
                ⏳ Sin registrar
              </span>
            </div>
            <div v-else>
              <span class="text-lg font-black font-mono block text-cyan-600 dark:text-cyan-400">
                {{ mes.posts_count }} posts
              </span>
              <span class="text-[10px] font-mono text-slate-400 block mt-0.5">
                {{ mes.pct_cumplimiento }}% de la meta
              </span>
            </div>
          </div>

          <div class="space-y-2 pt-2">
            <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
              <div
                class="h-full transition-all"
                :class="{
                  'bg-emerald-500': mes.estado === 'excelente',
                  'bg-amber-500': mes.estado === 'adecuado',
                  'bg-slate-400': mes.estado === 'bajo',
                  'bg-rose-500': mes.estado === 'inactivo_perdido',
                  'bg-slate-300 dark:bg-slate-700': mes.estado === 'sin_configurar' || mes.estado === 'pendiente',
                }"
                :style="{ width: `${mes.estado === 'inactivo_perdido' || mes.estado === 'sin_configurar' || mes.estado === 'pendiente' ? 0 : mes.pct_cumplimiento}%` }"
              ></div>
            </div>

            <div
              class="text-[10px] font-mono pt-1 border-t flex justify-between items-center"
              :class="mes.estado === 'inactivo_perdido' ? 'border-rose-500/20 text-rose-400' : 'border-slate-200/60 dark:border-slate-800/60 text-slate-400'"
            >
              <span v-if="mes.estado === 'inactivo_perdido'">⛔ Sin posts</span>
              <span v-else-if="mes.estado === 'sin_configurar' || mes.estado === 'pendiente'">⏳ Sin datos</span>
              <span v-else>🔥 {{ formatNumber(mes.total_interacciones) }}</span>
              <span v-if="mes.total_pauta > 0" class="text-violet-400 font-semibold">📢 ${{ formatNumber(mes.total_pauta) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 7. TIME-SERIES HISTÓRICO & BOTÓN MODAL GRÁFICO -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Activity class="w-5 h-5 text-cyan-500" />
            <span>Evolución Temporal de Auditorías (Time-Series)</span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Registro histórico diario de seguidores y deltas netos.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <span class="text-xs font-mono text-slate-400">
            {{ historicoMediciones.length }} mediciones registradas
          </span>
          <button
            type="button"
            @click="$emit('open-chart-modal')"
            class="px-3.5 py-2 rounded-xl bg-cyan-500/15 hover:bg-cyan-500/25 text-cyan-600 dark:text-cyan-400 text-xs font-mono font-bold inline-flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <Maximize2 class="w-3.5 h-3.5" />
            <span>Expandir Gráfico</span>
          </button>
        </div>
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

    <!-- 8. DISTRIBUCIÓN POR EJE TEMÁTICO DE CAMPAÑA -->
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

    <!-- 9. TOP PUBLICACIONES CON IMPACTO EN EL PADRÓN -->
    <div v-if="topPublicaciones.length > 0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Flame class="w-5 h-5 text-cyan-500 fill-current" />
          <span>Top Publicaciones con Mayor Impacto Territorial en este Canal</span>
        </h3>
        <Link
          :href="`/feed?filtro=propio&plataforma=${perfilSocial.plataforma}`"
          class="text-xs font-mono font-bold text-cyan-500 hover:text-cyan-400 flex items-center gap-1 cursor-pointer"
        >
          <span>Ver todas en Muro Social</span>
          <ExternalLink class="w-3 h-3" />
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="(top, i) in topPublicaciones"
          :key="top.id"
          class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 flex flex-col justify-between"
          :class="{ 'border-amber-500/50 shadow-md shadow-amber-500/10': top.es_viral_territorial }"
        >
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-mono">
              <span class="px-2 py-0.5 rounded-md bg-cyan-500 text-slate-950 font-black text-[10px]">
                #{{ i + 1 }} TOP
              </span>
              <span v-if="top.cobertura_padron_pct > 0" class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="top.es_viral_territorial ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-cyan-500/15 text-cyan-400'">
                {{ top.cobertura_padron_pct }}% Padrón
              </span>
              <span class="text-slate-400 text-[11px]">{{ top.fecha_relativa || top.fecha_publicacion }}</span>
            </div>
            <p class="text-xs text-slate-800 dark:text-slate-200 font-medium line-clamp-3">
              {{ top.contenido_resumen }}
            </p>
          </div>

          <div class="pt-2 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between font-mono text-xs">
            <span class="font-extrabold text-cyan-600 dark:text-cyan-400">
              🔥 {{ formatNumber(top.total_interacciones) }} int.
            </span>
            <span class="text-slate-500 text-[11px]">
              👀 {{ formatNumber(top.total_vistas) }} vistas
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
