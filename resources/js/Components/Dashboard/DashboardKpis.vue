<script setup>
import {
  Users,
  Target,
  Flame,
  Eye,
  Sparkles,
  TrendingUp,
  TrendingDown,
  Calendar,
  Heart
} from '@lucide/vue';

const props = defineProps({
  stats: {
    type: Object,
    required: true
  },
  candidato: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['abrir-modal-grafico']);
</script>

<template>
  <div class="space-y-4">
    <!-- BLOQUE 1: AUDIENCIA, TERRITORIO & TRACCIÓN ELECTORAL (4 TARJETAS GRANDES) -->
    <div class="space-y-2">
      <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-1">
        <Users class="w-3.5 h-3.5 text-cyan-500" />
        <span>Audiencia, Territorio & Tracción Electoral</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 1: Comunidad Multired & Crecimiento Neto (con Tiers) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Comunidad Total</span>
            <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
              <Users class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                {{ stats.total_seguidores }}
              </p>
              <span
                v-if="stats.total_seguidores_netos && stats.total_seguidores_netos !== stats.total_seguidores"
                class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 font-mono px-2 py-0.5 rounded-md bg-cyan-500/10"
                :title="`Alcance Único Neto por Tiers: ~${stats.total_seguidores_netos} personas reales desduplicadas`"
              >
                ~{{ stats.total_seguidores_netos }} netos
              </span>
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold" :class="stats.crecimiento_neto_seguidores >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500'">
              <TrendingUp v-if="stats.crecimiento_neto_seguidores >= 0" class="w-3.5 h-3.5" />
              <TrendingDown v-else class="w-3.5 h-3.5" />
              <span>{{ stats.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ Number(stats.crecimiento_neto_seguidores).toLocaleString('es-AR') }} neto</span>
              <span class="text-slate-400 font-normal">vs Punto Cero</span>
            </div>
          </div>
        </div>

        <!-- KPI 2: Penetración sobre el Padrón (Neto Real por Tiers) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Penetración Padrón</span>
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
              <Target class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                {{ stats.ratio_penetracion }}
              </p>
              <span class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 shrink-0">
                Neto Real
              </span>
            </div>

            <!-- Barra de Progreso de Penetración sobre el Padrón -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`~${stats.total_seguidores_netos} de ${candidato ? Number(candidato.padron_electoral).toLocaleString('es-AR') : ''} electores únicos alcanzados`">
              <div
                class="h-full rounded-full bg-blue-500 transition-all duration-500"
                :style="{ width: `${Math.min(stats.ratio_penetracion_raw || 0, 100)}%` }"
              ></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
              <span class="text-[11px] text-slate-700 dark:text-slate-300">~{{ stats.total_seguidores_netos }} electores únicos</span>
              <span v-if="candidato" class="text-[11px] text-slate-400">de {{ Number(candidato.padron_electoral).toLocaleString('es-AR') }}</span>
            </div>
          </div>
        </div>

        <!-- KPI 3: Total de Interacciones del Período -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Interacciones Totales</span>
            <div class="w-8 h-8 rounded-xl bg-pink-500/10 text-pink-500 flex items-center justify-center">
              <Heart class="w-4 h-4 fill-pink-500/20" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                {{ stats.interacciones_totales || '0' }}
              </p>
              <span
                class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20 shrink-0"
                title="Tasa de Aceptación Política Real sobre Seguidores"
              >
                {{ stats.engagement_promedio }} TAP
              </span>
            </div>
            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
              <span>👍 {{ stats.total_likes || '0' }}</span>
              <span>💬 {{ stats.total_comentarios || '0' }}</span>
              <span>🔄 {{ stats.total_compartidos || '0' }}</span>
            </div>
          </div>
        </div>

        <!-- KPI 4: Tracción Política Promedio (Indexada 0-100) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Tracción Política</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <Flame class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight flex items-center gap-1.5">
                <span>{{ stats.score_traccion_promedio || 50 }}</span>
                <span class="text-xs text-slate-400 font-normal">/ 100</span>
              </p>
              <span
                class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md border shrink-0"
                :class="{
                  'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20': (stats.score_traccion_promedio || 50) >= 60,
                  'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20': (stats.score_traccion_promedio || 50) >= 40 && (stats.score_traccion_promedio || 50) < 60,
                  'bg-rose-500/10 text-rose-500 border-rose-500/20': (stats.score_traccion_promedio || 50) < 40
                }"
              >
                {{ stats.engagement_calidad_texto || 'Estándar Electoral' }}
              </span>
            </div>

            <!-- Barra de Progreso de Tracción Indexada -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="{
                  'bg-emerald-500': (stats.score_traccion_promedio || 50) >= 60,
                  'bg-amber-500': (stats.score_traccion_promedio || 50) >= 40 && (stats.score_traccion_promedio || 50) < 60,
                  'bg-rose-500': (stats.score_traccion_promedio || 50) < 40
                }"
                :style="{ width: `${Math.min(stats.score_traccion_promedio || 50, 100)}%` }"
              ></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
              <span class="text-slate-700 dark:text-slate-300">VTP Ponderado x Tier</span>
              <span class="text-slate-400">Benchmark Equitativo</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- BLOQUE 2: ALCANCE, TRACCIÓN & RENDIMIENTO DE CAMPAÑA (4 TARJETAS GRANDES) -->
    <div class="space-y-2 pt-1">
      <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 px-1">
        <Flame class="w-3.5 h-3.5 text-amber-500" />
        <span>Alcance, Tracción & Metas de Contenido</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- KPI 4: Visualizaciones Acumuladas & Eficiencia de Alcance -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Alcance & Vistas</span>
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
              <Eye class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                {{ stats.total_vistas }}
              </p>
              <span
                class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0"
                :title="`Tasa de interacción activa: ${stats.engagement_promedio}`"
              >
                {{ stats.engagement_promedio }} ER
              </span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
              <span class="font-mono">{{ stats.vistas_promedio_post || '0' }} vistas / post</span>
              <span class="font-semibold text-emerald-600 dark:text-emerald-400 text-[11px]">{{ stats.engagement_calidad_texto || 'Alto Involucramiento' }}</span>
            </div>
          </div>
        </div>

        <!-- KPI 5: Score Promedio por Publicación (pts/post) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score / Post</span>
            <div class="w-8 h-8 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center">
              <Sparkles class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <div class="flex items-baseline gap-1.5">
                <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                  {{ stats.score_promedio_post || '0' }}
                </p>
                <span class="text-xs font-semibold text-violet-600 dark:text-violet-400 font-mono">pts / post</span>
              </div>

              <span
                class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md border shrink-0 flex items-center gap-1"
                :class="{
                  'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20': stats.score_promedio_post_pct >= 100,
                  'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20': stats.score_promedio_post_pct >= 60 && stats.score_promedio_post_pct < 100,
                  'bg-rose-500/10 text-rose-500 border-rose-500/20': stats.score_promedio_post_pct < 60
                }"
                :title="`Meta Territorial: ${stats.score_promedio_post_meta} pts (${stats.meta_score_base_texto})`"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="{
                  'bg-emerald-500': stats.score_promedio_post_pct >= 100,
                  'bg-amber-500': stats.score_promedio_post_pct >= 60 && stats.score_promedio_post_pct < 100,
                  'bg-rose-500': stats.score_promedio_post_pct < 60
                }"></span>
                <span>{{ stats.score_promedio_post_pct || 0 }}%</span>
              </span>
            </div>

            <!-- Barra de Progreso hacia la Meta Territorial -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`${stats.score_promedio_post} de ${stats.score_promedio_post_meta} pts objetivo`">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="{
                  'bg-emerald-500': stats.score_promedio_post_pct >= 100,
                  'bg-amber-500': stats.score_promedio_post_pct >= 60 && stats.score_promedio_post_pct < 100,
                  'bg-rose-500': stats.score_promedio_post_pct < 60
                }"
                :style="{ width: `${Math.min(stats.score_promedio_post_pct || 0, 100)}%` }"
              ></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
              <span class="font-mono">{{ stats.total_publicaciones }} posts</span>
              <span class="font-mono text-[10px] text-slate-400 dark:text-slate-500">
                Meta: <strong class="text-slate-700 dark:text-slate-300 font-bold">{{ stats.score_promedio_post_meta }}</strong> pts
              </span>
            </div>
          </div>
        </div>

        <!-- KPI 6: Score Promedio Mensual (pts/mes) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score / Mes</span>
            <button
              type="button"
              @click="emit('abrir-modal-grafico', 'score_mensual')"
              class="w-8 h-8 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-500 flex items-center justify-center transition-colors cursor-pointer"
              title="Ver comparativa detallada por meses (Julio, Agosto, Septiembre)"
            >
              <Calendar class="w-4 h-4" />
            </button>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <div class="flex items-baseline gap-1.5">
                <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                  {{ stats.score_promedio_mensual || '0' }}
                </p>
                <span class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 font-mono">pts / mes</span>
              </div>

              <div v-if="stats.tendencia_score_mes" class="shrink-0">
                <span
                  class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md flex items-center gap-0.5"
                  :class="stats.tendencia_score_mes.variacion_pct >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-500 border border-rose-500/20'"
                  :title="`Tendencia de Volumen: ${stats.tendencia_score_mes.variacion_pct >= 0 ? '+' : ''}${stats.tendencia_score_mes.variacion_pct}% vs mes anterior`"
                >
                  <TrendingUp v-if="stats.tendencia_score_mes.variacion_pct >= 0" class="w-3 h-3" />
                  <TrendingDown v-else class="w-3 h-3" />
                  <span>{{ stats.tendencia_score_mes.variacion_pct >= 0 ? '+' : '' }}{{ stats.tendencia_score_mes.variacion_pct }}%</span>
                </span>
              </div>
            </div>

            <!-- Barra de Progreso hacia la Meta Mensual Territorial -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`${stats.score_promedio_mensual} de ${stats.score_promedio_mensual_meta} pts objetivo mensual`">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="{
                  'bg-emerald-500': stats.score_promedio_mensual_pct >= 100,
                  'bg-amber-500': stats.score_promedio_mensual_pct >= 60 && stats.score_promedio_mensual_pct < 100,
                  'bg-rose-500': stats.score_promedio_mensual_pct < 60
                }"
                :style="{ width: `${Math.min(stats.score_promedio_mensual_pct || 0, 100)}%` }"
              ></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
              <span class="font-mono text-[10px] text-slate-400 dark:text-slate-500">
                Meta: <strong class="text-slate-700 dark:text-slate-300 font-bold">{{ stats.score_promedio_mensual_meta }}</strong> pts
              </span>
              <button
                type="button"
                @click="emit('abrir-modal-grafico', 'score_mensual')"
                class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-0.5 cursor-pointer"
              >
                <span>Ver meses</span>
                <span>→</span>
              </button>
            </div>
          </div>
        </div>

        <!-- KPI 7: Score Campaña (Presión Electoral sobre Padrón) -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Score Campaña</span>
            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
              <Flame class="w-4 h-4" />
            </div>
          </div>
          <div class="mt-3">
            <div class="flex items-baseline justify-between gap-1">
              <div class="flex items-baseline gap-1.5">
                <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 font-mono tracking-tight">
                  {{ stats.score_impacto_total }}
                </p>
                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 font-mono">pts tot.</span>
              </div>

              <span
                class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 shrink-0"
                :title="`Presión Electoral: ${stats.avance_campana_padron_pct}% del padrón total`"
              >
                {{ stats.avance_campana_padron_pct || 0 }}% padrón
              </span>
            </div>

            <!-- Barra de Progreso hacia el 100% del Padrón Electoral -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full mt-2.5 overflow-hidden" :title="`${stats.score_impacto_total} de ${stats.meta_score_campana} pts del padrón`">
              <div
                class="h-full rounded-full bg-amber-500 transition-all duration-500"
                :style="{ width: `${Math.min(stats.avance_campana_padron_pct || 0, 100)}%` }"
              ></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
              <span class="text-[10px] text-slate-700 dark:text-slate-300 font-bold flex items-center gap-1" :title="`Mes récord: ${stats.record_mensual_score} pts en ${stats.record_mensual_nombre}`">
                <span>🏆 Récord:</span>
                <strong class="text-amber-600 dark:text-amber-400">{{ stats.record_mensual_score }}</strong>
                <span class="text-slate-400 text-[9px]">({{ stats.record_mensual_corto }})</span>
              </span>
              <span class="text-[10px] text-slate-400">
                {{ stats.score_promedio_diario || '0' }} pts/d
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
