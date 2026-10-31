<script setup>
import {
  Shield,
  Briefcase,
  HeartHandshake,
  Flame,
  ChevronDown,
  ChevronUp,
  ExternalLink,
} from '@lucide/vue';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
  pilar: {
    type: Object,
    required: true,
  },
  isOpen: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['toggle']);

const { formatNumber } = useFormatters();

const getPilarIcon = (claveSlug) => {
  switch (claveSlug) {
    case 'orden':
      return Shield;
    case 'futuro':
      return Briefcase;
    case 'humano':
      return HeartHandshake;
    default:
      return Shield;
  }
};
</script>

<template>
  <div
    class="rounded-3xl bg-white dark:bg-slate-900 border transition-all duration-300 shadow-sm overflow-hidden"
    :class="[
      pilar.alerta_crisis ? 'border-red-500/50 shadow-red-500/10' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'
    ]"
  >
    <!-- Cabecera de la Tarjeta del Pilar -->
    <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <div class="flex items-start gap-4">
        <!-- Icono con fondo de color -->
        <div
          class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 border"
          :class="[pilar.color_bg, pilar.color_border, pilar.color_text]"
        >
          <component :is="getPilarIcon(pilar.clave_slug)" class="w-6 h-6" />
        </div>

        <div class="space-y-1">
          <div class="flex items-center gap-2.5 flex-wrap">
            <h3 class="text-xl font-black text-slate-900 dark:text-slate-100">
              {{ pilar.pilar_principal }}
            </h3>
            <!-- Badge de Share -->
            <span
              class="px-2.5 py-0.5 rounded-full text-xs font-mono font-extrabold border"
              :class="[pilar.color_bg, pilar.color_border, pilar.color_text]"
            >
              {{ pilar.share_porcentaje }}% de la Campaña
            </span>
            <!-- Badge de Alerta Roja si > 15% indignación -->
            <span
              v-if="pilar.alerta_crisis"
              class="px-2.5 py-0.5 rounded-full text-xs font-mono font-extrabold bg-red-500/20 text-red-600 dark:text-red-400 border border-red-500/40 animate-pulse inline-flex items-center gap-1"
            >
              <Flame class="w-3.5 h-3.5" />
              <span>ALERTA CRISIS (😡 {{ pilar.pct_indignacion }}%)</span>
            </span>
          </div>
          <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
            {{ pilar.subtitulo }}
          </p>
        </div>
      </div>

      <!-- Target Etario & Propuesta Clave -->
      <div class="flex items-center gap-3 self-start lg:self-center flex-wrap">
        <div class="px-3 py-1.5 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs">
          <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold">Afinidad Demográfica:</span>
          <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ pilar.target_etario }}</span>
        </div>
        <div class="px-3 py-1.5 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs">
          <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold">Formato que más rinde:</span>
          <span class="font-extrabold" :class="pilar.color_text">{{ pilar.formato_destacado }}</span>
        </div>
      </div>
    </div>

    <!-- Cuadrícula de Métricas Clave del Pilar -->
    <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 border-b border-slate-100 dark:border-slate-800/80">
      <!-- 1. Posts & Vistas -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/70 dark:border-slate-800/70 space-y-1">
        <span class="text-[11px] font-bold text-slate-400 font-mono uppercase">Volumen Auditado</span>
        <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 font-mono">
          {{ pilar.posts_count }} <span class="text-xs font-normal text-slate-400">posts</span>
        </div>
        <div class="text-[11px] text-slate-500 font-mono">
          {{ formatNumber(pilar.total_vistas) }} vistas totales
        </div>
      </div>

      <!-- 2. Tracción e Interacciones -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/70 dark:border-slate-800/70 space-y-1">
        <span class="text-[11px] font-bold text-slate-400 font-mono uppercase">Impacto Ponderado</span>
        <div class="text-xl sm:text-2xl font-black font-mono" :class="pilar.color_text">
          {{ formatNumber(pilar.score_impacto) }} <span class="text-xs font-normal text-slate-400">pts</span>
        </div>
        <div class="text-[11px] text-slate-500 font-mono">
          {{ formatNumber(pilar.total_interacciones) }} interacciones
        </div>
      </div>

      <!-- 3. Aprobación Neta & Humor Social -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/70 dark:border-slate-800/70 space-y-1">
        <span class="text-[11px] font-bold text-slate-400 font-mono uppercase">Aprobación Neta</span>
        <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
          {{ pilar.aprobacion_neta }}%
        </div>
        <div class="text-[11px] text-slate-500 font-mono flex items-center gap-1">
          <span>⭐ {{ pilar.humor_promedio }}/5</span>
          <span class="text-slate-400">humor ciudadano</span>
        </div>
      </div>

      <!-- 4. Semáforo de Indignación (😡) -->
      <div
        class="p-4 rounded-2xl border space-y-1 transition-colors"
        :class="[
          pilar.alerta_crisis
            ? 'bg-red-500/10 border-red-500/40 text-red-600 dark:text-red-400'
            : 'bg-slate-50 dark:bg-slate-950/70 border-slate-200/70 dark:border-slate-800/70'
        ]"
      >
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold font-mono uppercase" :class="pilar.alerta_crisis ? 'text-red-600 dark:text-red-400' : 'text-slate-400'">
            Rechazo (😡)
          </span>
          <span v-if="pilar.alerta_crisis" class="text-xs">⚠️ Crisis</span>
        </div>
        <div
          class="text-xl sm:text-2xl font-black font-mono"
          :class="pilar.alerta_crisis ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-100'"
        >
          {{ pilar.pct_indignacion }}%
        </div>
        <div class="text-[11px] text-slate-500 font-mono">
          {{ pilar.reacciones.me_enoja }} de {{ formatNumber(pilar.reacciones.total) }} reacciones
        </div>
      </div>
    </div>

    <!-- Encuadre Político: Demanda vs Propuesta -->
    <div class="px-6 py-4 bg-slate-50/40 dark:bg-slate-950/40 border-b border-slate-100 dark:border-slate-800/80 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
      <div>
        <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold mb-0.5">
          🛑 ¿Qué demanda vecinal busca resolver?
        </span>
        <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed">
          {{ pilar.demanda_central }}
        </p>
      </div>
      <div>
        <span class="text-slate-400 font-mono block text-[10px] uppercase font-bold mb-0.5">
          💡 Propuesta Concreta del Candidato:
        </span>
        <p class="text-slate-700 dark:text-slate-300 font-medium leading-relaxed">
          {{ pilar.solucion_propuesta }}
        </p>
      </div>
    </div>

    <!-- ACORDEÓN: DESGLOSE DE EJES TEMÁTICOS -->
    <div class="p-6 space-y-4">
      <div class="flex items-center justify-between">
        <button
          type="button"
          @click="emit('toggle')"
          class="flex items-center gap-2 text-xs font-black text-slate-800 dark:text-slate-200 hover:text-cyan-500 transition-colors cursor-pointer select-none"
        >
          <span>Temáticas Vecinales Acopladas ({{ pilar.subejes.length }} temas específicos)</span>
          <component :is="isOpen ? ChevronUp : ChevronDown" class="w-4 h-4 text-slate-400" />
        </button>
        <span class="text-[11px] font-mono text-slate-400">
          Al elegir estos temas en un post, se acumulan a este pilar
        </span>
      </div>

      <!-- Tabla de Sub-ejes desplegable -->
      <div v-show="isOpen" class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="border-b border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-400 font-mono uppercase">
              <th class="pb-2.5">Eje Temático Concreto</th>
              <th class="pb-2.5">Descripción de la Demanda</th>
              <th class="pb-2.5 text-right">Posts</th>
              <th class="pb-2.5 text-right">% del Pilar</th>
              <th class="pb-2.5 text-right">Vistas</th>
              <th class="pb-2.5 text-right">Score</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-medium">
            <tr
              v-for="sub in pilar.subejes"
              :key="sub.id"
              class="hover:bg-slate-50 dark:hover:bg-slate-950/50 transition-colors"
            >
              <td class="py-3 pr-3">
                <div class="flex items-center gap-2">
                  <span
                    class="w-2.5 h-2.5 rounded-full shrink-0"
                    :style="{ backgroundColor: sub.color_badge || pilar.color }"
                  />
                  <span class="font-extrabold text-slate-900 dark:text-slate-100">{{ sub.nombre }}</span>
                </div>
              </td>
              <td class="py-3 px-3 text-slate-500 dark:text-slate-400 max-w-xs truncate text-[11px]">
                {{ sub.descripcion || 'Sin descripción' }}
              </td>
              <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                {{ sub.posts_count }}
              </td>
              <td class="py-3 px-3 text-right font-mono text-slate-500">
                {{ sub.porcentaje_del_pilar }}%
              </td>
              <td class="py-3 px-3 text-right font-mono text-slate-600 dark:text-slate-400">
                {{ formatNumber(sub.total_vistas) }}
              </td>
              <td class="py-3 pl-3 text-right font-mono font-bold" :class="pilar.color_text">
                {{ formatNumber(sub.score_impacto) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Top Publicaciones del Pilar en el Período -->
      <div v-if="pilar.top_publicaciones && pilar.top_publicaciones.length > 0" class="pt-4 border-t border-slate-100 dark:border-slate-800/80">
        <span class="text-[11px] font-bold text-slate-400 font-mono uppercase block mb-3">
          🔥 Publicaciones más Influyentes de este Pilar en el Período:
        </span>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div
            v-for="top in pilar.top_publicaciones"
            :key="top.id"
            class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between space-y-2 hover:border-slate-300 dark:hover:border-slate-700 transition-colors"
          >
            <div class="space-y-1">
              <div class="flex items-center justify-between text-[10px] text-slate-400 font-mono">
                <span class="uppercase font-bold text-cyan-600 dark:text-cyan-400">{{ top.plataforma }}</span>
                <span>{{ top.fecha_publicacion }}</span>
              </div>
              <p class="text-xs text-slate-800 dark:text-slate-200 line-clamp-2 font-medium">
                "{{ top.texto_extracto }}"
              </p>
            </div>

            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 dark:border-slate-800 text-[11px] font-mono">
              <span class="text-slate-500">{{ formatNumber(top.total_vistas) }} vistas</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">❤️ {{ formatNumber(top.total_likes) }}</span>
              <a
                v-if="top.url_post"
                :href="top.url_post"
                target="_blank"
                rel="noopener noreferrer"
                class="text-cyan-500 hover:text-cyan-400"
                title="Ver publicación original"
              >
                <ExternalLink class="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
