<script setup>
import { Link } from '@inertiajs/vue3';
import { Sparkles } from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';
import { useSocialMetrics } from '../../composables/useSocialMetrics';
import { useFormatters } from '../../composables/useFormatters';

const props = defineProps({
  redesDesglose: {
    type: Array,
    default: () => []
  }
});

const { getSocialMeta, tabBadgeStyle } = useSocialMetrics();
const { formatNumber } = useFormatters();
</script>

<template>
  <!-- MALLA DE CANALES SOCIALES (7 CANALES EN 1 SOLA FILA ESTILO MI-PERFIL) -->
  <div class="space-y-3.5">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Sparkles class="w-4 h-4 text-cyan-500" />
          <span>Auditoría de Canales Sociales Conectados</span>
        </h2>
        <p class="text-xs text-slate-500 dark:text-slate-400">Haz clic en cualquier canal para auditar sus métricas o configurar su Punto Cero</p>
      </div>
      <Link href="/mi-candidato" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline">
        Configurar Punto Cero &rarr;
      </Link>
    </div>

    <!-- Grid de 7 Canales en 1 Sola Fila Continua -->
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 lg:grid-cols-7 gap-2.5 sm:gap-3">
      <Link
        v-for="red in redesDesglose"
        :key="red.plataforma"
        :href="red.id && red.color_estado !== 'gris' ? `/perfiles-sociales/${red.id}/metricas` : '/mi-candidato'"
        class="p-3 sm:p-3.5 rounded-2xl border-2 transition-all flex flex-col items-center justify-between text-center gap-2 cursor-pointer relative shadow-xs group"
        :class="[
          red.color_estado === 'gris'
            ? 'border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-400 opacity-75 hover:opacity-100 hover:border-slate-400 hover:bg-white dark:hover:bg-slate-900'
            : (red.color_estado === 'azul'
                ? 'border-blue-500/60 bg-blue-500/5 hover:border-blue-500 hover:shadow-md'
                : (red.color_estado === 'verde'
                    ? 'border-emerald-500/60 bg-emerald-500/5 hover:border-emerald-500 hover:shadow-md'
                    : 'border-rose-500/40 bg-rose-500/5 hover:border-rose-500 hover:shadow-md'))
        ]"
      >
        <!-- Logo Oficial de la Red -->
        <div class="flex items-center justify-center w-9 h-9 rounded-xl shadow-2xs shrink-0" :class="getSocialMeta(red.plataforma).bgLight">
          <SocialPlatformIcon :platform="red.plataforma" size="md" />
        </div>

        <div class="min-w-0 w-full">
          <span class="font-bold text-xs leading-tight block text-slate-900 dark:text-slate-100 truncate">
            {{ red.plataforma.replace('_', ' ') }}
          </span>
          <span class="text-[10px] text-slate-500 dark:text-slate-400 block truncate mt-0.5 font-mono">
            {{ red.handle_usuario || '@sin_configurar' }}
          </span>
        </div>

        <!-- Pill de Estado Oficial -->
        <span
          class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider font-mono truncate max-w-full"
          :class="tabBadgeStyle(red.color_estado).pill"
        >
          {{ tabBadgeStyle(red.color_estado).label }}
        </span>

        <!-- Métricas o Estado -->
        <div v-if="red.color_estado === 'verde' || red.color_estado === 'azul'" class="w-full pt-1.5 border-t border-slate-200/60 dark:border-slate-800/80 font-mono text-[10px] flex items-center justify-between text-slate-500 dark:text-slate-400">
          <span>{{ formatNumber(red.seguidores) }} seg</span>
          <span class="text-cyan-600 dark:text-cyan-400 font-bold">{{ red.publicaciones_count }} posts</span>
        </div>
        <div v-else-if="red.color_estado === 'rojo'" class="w-full pt-1.5 border-t border-rose-500/20 font-mono text-[9px] text-rose-500 truncate">
          0 publicaciones
        </div>
        <div v-else class="w-full pt-1.5 border-t border-dashed border-slate-300 dark:border-slate-800 font-mono text-[9px] text-slate-400 truncate">
          Sin vincular
        </div>
      </Link>
    </div>
  </div>
</template>
