<script setup>
import {
  Users,
  Target,
  Heart,
  Film,
  PieChart,
  MapPin
} from '@lucide/vue';

const props = defineProps({
  demografiaAudiencia: {
    type: Object,
    required: true,
  },
  cruceDemografico: {
    type: Array,
    default: () => [],
  },
  territorioContexto: {
    type: Object,
    default: () => ({}),
  },
  perfilSocial: {
    type: Object,
    required: true,
  },
  getSocialMeta: {
    type: Function,
    required: true,
  },
});
</script>

<template>
  <div v-if="demografiaAudiencia" class="space-y-6">
    <!-- A. Cruce por Franjas Etarias vs Padrón (Full Width) -->
    <div class="w-full p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-2">
        <div>
          <div class="flex items-center gap-2">
            <span class="p-1.5 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
              <Users class="w-4 h-4" />
            </span>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
              Audiencia Digital vs Padrón Electoral (Análisis de Brechas)
            </h3>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Compara la composición etaria de tu comunidad en {{ getSocialMeta(perfilSocial.plataforma).name }} frente a los electores del territorio.
          </p>
        </div>
        <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
          Fuente: {{ demografiaAudiencia.fuente_datos === 'estimacion_territorial' ? 'Estimación Territorial' : 'Meta Graph API' }}
        </span>
      </div>

      <!-- Tabla / Barras de Franjas Etarias y Brechas -->
      <div class="space-y-3 font-mono text-xs">
        <div
          v-for="item in cruceDemografico"
          :key="item.rango"
          class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5"
        >
          <!-- Encabezado de Franja: Rango y Electores Nominales -->
          <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-100 dark:border-slate-800/80 pb-2.5">
            <div>
              <div class="flex items-center gap-2">
                <span class="font-black text-slate-900 dark:text-slate-100 text-sm">{{ item.rango }} años</span>
                <span class="text-[10px] text-slate-400">({{ item.categoria }})</span>
              </div>
              <div class="text-[11px] text-slate-500 font-sans">
                Universo en {{ territorioContexto.nombre || 'el Municipio' }}: <strong>{{ Number(item.electores_totales_franja || 0).toLocaleString('es-AR') }} electores</strong>
              </div>
            </div>

            <div class="text-right">
              <span
                class="px-2.5 py-1 rounded-xl font-bold text-[10px] font-mono inline-flex items-center gap-1 border"
                :class="{
                  'bg-rose-500/10 border-rose-500/30 text-rose-400': item.pauta_tipo === 'urgente',
                  'bg-amber-500/10 border-amber-500/30 text-amber-400': item.pauta_tipo === 'moderada',
                  'bg-emerald-500/10 border-emerald-500/30 text-emerald-400': item.pauta_tipo === 'buena' || item.pauta_tipo === 'victoria',
                }"
              >
                {{ item.pauta_badge }}
              </span>
            </div>
          </div>

          <!-- 1. Penetración Nominal en el Padrón Electoral -->
          <div class="space-y-1.5 pt-1">
            <div class="flex items-center justify-between text-[11px]">
              <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1">
                <span>👥 Cobertura Real en el Padrón:</span>
                <strong class="font-bold font-mono" :class="item.cobertura_padron_franja_pct < 15 ? 'text-rose-500' : (item.cobertura_padron_franja_pct < 30 ? 'text-amber-500' : 'text-emerald-500')">
                  {{ item.cobertura_padron_franja_pct }}%
                </strong>
                <span class="text-[10px] text-slate-400">({{ item.seguidores_en_franja }} de {{ Number(item.electores_totales_franja).toLocaleString('es-AR') }} electores)</span>
              </span>
              <span class="text-[10px] font-mono text-slate-400">
                Faltan: <strong>{{ Number(item.electores_faltantes || 0).toLocaleString('es-AR') }}</strong>
              </span>
            </div>

            <!-- Barra de Cobertura Electoral -->
            <div class="w-full h-2.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
              <div
                class="h-full rounded-full transition-all"
                :class="item.cobertura_padron_franja_pct >= 30 ? 'bg-emerald-500' : (item.cobertura_padron_franja_pct >= 15 ? 'bg-amber-500' : 'bg-rose-500')"
                :style="{ width: `${Math.max(item.cobertura_padron_franja_pct, 4)}%` }"
              ></div>
            </div>
          </div>

          <!-- 2. Banner de Diagnóstico Táctico de Pauta Publicitaria -->
          <div
            class="p-3 rounded-2xl border text-xs font-sans leading-relaxed space-y-1"
            :class="{
              'bg-rose-500/10 border-rose-500/25 text-rose-900 dark:text-rose-300': item.pauta_tipo === 'urgente',
              'bg-amber-500/10 border-amber-500/25 text-amber-900 dark:text-amber-300': item.pauta_tipo === 'moderada',
              'bg-emerald-500/10 border-emerald-500/25 text-emerald-900 dark:text-emerald-300': item.pauta_tipo === 'buena' || item.pauta_tipo === 'victoria',
            }"
          >
            <div class="font-bold flex items-center gap-1.5 text-[11px]">
              <Target class="w-3.5 h-3.5 shrink-0" />
              <span>Diagnóstico para el Jefe de Campaña:</span>
            </div>
            <p class="text-[11px] leading-snug">
              {{ item.diagnostico_pauta }}
            </p>
          </div>

          <!-- 3. Rendimiento Interno de la Cuenta (Últimos 30 días) -->
          <div class="flex items-center justify-between flex-wrap gap-2 pt-1 border-t border-slate-100 dark:border-slate-800/80 text-[10px] text-slate-500">
            <div class="flex items-center gap-3">
              <span class="flex items-center gap-1">
                <Heart class="w-3 h-3 text-rose-500" />
                <span>~<strong>{{ item.reacciones_actuales_30d || 0 }}</strong> reacc./post (30d)</span>
              </span>
              <span class="flex items-center gap-1 text-slate-400">
                <Film class="w-3 h-3 text-cyan-500" />
                <span>~<strong>{{ Number(item.vistas_actuales_30d || 0).toLocaleString('es-AR') }}</strong> vistas</span>
              </span>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-[10px] text-slate-400">
                Récord histórico: <strong class="text-amber-500 font-mono">~{{ item.reacciones_max_historico || 0 }}</strong> ({{ item.mes_record_nombre }})
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- B. Género, Top Ciudades y Horarios Pico -->
    <div class="w-full p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
      <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
        <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <PieChart class="w-4 h-4 text-emerald-500" />
          <span>Demografía & Anclaje Geográfico</span>
        </h4>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
          Origen territorial, desglose por género y momentos de mayor resonancia de la comunidad.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Columna 1: Distribución de Género -->
        <div v-if="demografiaAudiencia.genero" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/70 dark:border-slate-800 space-y-3 flex flex-col justify-between">
          <div>
            <span class="text-[11px] font-mono uppercase font-bold text-slate-400 block mb-2">Distribución de Género</span>
            <div class="w-full h-3.5 rounded-full overflow-hidden flex bg-slate-200 dark:bg-slate-800 shadow-inner">
              <div
                class="h-full bg-[#E4405F] transition-all"
                :style="{ width: `${demografiaAudiencia.genero.femenino_pct}%` }"
                :title="`Mujeres: ${demografiaAudiencia.genero.femenino_pct}%`"
              ></div>
              <div
                class="h-full bg-[#1877F2] transition-all"
                :style="{ width: `${demografiaAudiencia.genero.masculino_pct}%` }"
                :title="`Varones: ${demografiaAudiencia.genero.masculino_pct}%`"
              ></div>
            </div>
          </div>
          <div class="flex justify-between text-xs font-mono pt-1">
            <span class="text-[#E4405F] font-bold">👩 {{ demografiaAudiencia.genero.femenino_pct }}% Mujeres</span>
            <span class="text-[#1877F2] font-bold">👨 {{ demografiaAudiencia.genero.masculino_pct }}% Varones</span>
          </div>
        </div>

        <!-- Columna 2: Top Ciudades de la Audiencia -->
        <div v-if="demografiaAudiencia.ciudades_principales" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/70 dark:border-slate-800 space-y-2.5">
          <span class="text-[11px] font-mono uppercase font-bold text-slate-400 block">Top Ciudades de la Audiencia</span>
          <div class="space-y-1.5 font-mono text-xs">
            <div
              v-for="c in demografiaAudiencia.ciudades_principales"
              :key="c.ciudad"
              class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 flex items-center justify-between"
            >
              <span class="flex items-center gap-1.5 text-slate-800 dark:text-slate-200 font-medium">
                <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                {{ c.ciudad }}
              </span>
              <span class="font-bold text-cyan-500">{{ c.pct }}%</span>
            </div>
          </div>
        </div>

        <!-- Columna 3: Horarios & Días Pico -->
        <div v-if="demografiaAudiencia.horarios_actividad" class="p-4 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 text-xs space-y-2 text-cyan-800 dark:text-cyan-200 flex flex-col justify-between">
          <div>
            <span class="font-bold font-mono text-[11px] uppercase block mb-1">⏰ Momentos de Mayor Actividad:</span>
            <div class="text-xs leading-relaxed space-y-1">
              <div><strong>Días:</strong> {{ demografiaAudiencia.horarios_actividad.dias_pico.join(', ') }}</div>
              <div><strong>Horarios Prime:</strong> {{ demografiaAudiencia.horarios_actividad.horas_pico.join(' & ') }}</div>
            </div>
          </div>
          <div class="text-[10px] opacity-75 font-mono">
            💡 Programar publicaciones en estas ventanas para maximizar alcance.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
