<script setup>
defineProps({
  red: {
    type: Object,
    required: true,
  },
  esPropio: {
    type: Boolean,
    default: true,
  },
});
</script>

<template>
  <!-- Tarjetas de Métricas de Punto Cero vs Actual -->
  <div
    class="grid gap-2.5 sm:gap-3 font-mono"
    :class="red.key === 'facebook' ? 'grid-cols-1 sm:grid-cols-3' : (red.key === 'tiktok' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4')"
  >
    <!-- Seguidores / Suscriptores / Contactos -->
    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-0.5">
      <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
        <span>👥 {{ red.key === 'youtube' ? 'Suscriptores' : (red.key === 'linkedin' ? 'Contactos' : 'Seguidores') }}</span>
        <span
          v-if="red.crecimiento_neto_seguidores > 0"
          class="text-[9px] font-bold px-1.5 py-0.2 rounded font-mono"
          :class="esPropio ? 'text-emerald-500 bg-emerald-500/10' : 'text-purple-400 bg-purple-500/10'"
        >
          +{{ Number(red.crecimiento_neto_seguidores).toLocaleString('es-AR') }}
        </span>
      </span>
      <div
        class="text-lg sm:text-xl font-extrabold leading-tight"
        :class="esPropio ? 'text-cyan-600 dark:text-cyan-400' : 'text-purple-600 dark:text-purple-400'"
      >
        {{ Number(red.seguidores_actuales || 0).toLocaleString('es-AR') }}
      </div>
      <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
        <span>Punto Alfa:</span>
        <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.seguidores_punto_cero || 0).toLocaleString('es-AR') }}</span>
      </div>
    </div>

    <!-- Cuentas Seguidas (Oculto en YouTube) -->
    <div
      v-if="red.key !== 'youtube'"
      class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-0.5"
    >
      <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
        🔄 Seguidos
      </span>
      <div class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-slate-200 leading-tight">
        {{ Number(red.seguidos_actuales || 0).toLocaleString('es-AR') }}
      </div>
      <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
        <span>Punto Alfa:</span>
        <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.seguidos_punto_cero || 0).toLocaleString('es-AR') }}</span>
      </div>
    </div>

    <!-- Me Gusta Acumulados (Específico TikTok) -->
    <div
      v-if="red.key === 'tiktok'"
      class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border-2 border-rose-500/30 space-y-0.5"
    >
      <span class="text-[10px] uppercase tracking-wider text-rose-500 font-bold block flex items-center justify-between">
        <span>❤️ Me Gusta</span>
        <span
          v-if="red.crecimiento_neto_me_gusta > 0"
          class="text-emerald-500 text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-500/10"
        >
          +{{ Number(red.crecimiento_neto_me_gusta).toLocaleString('es-AR') }}
        </span>
      </span>
      <div class="text-lg sm:text-xl font-extrabold text-rose-600 dark:text-rose-400 leading-tight">
        {{ Number(red.me_gusta_totales || 0).toLocaleString('es-AR') }}
      </div>
      <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
        <span>Punto Alfa:</span>
        <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.me_gusta_punto_cero || 0).toLocaleString('es-AR') }}</span>
      </div>
    </div>

    <!-- Publicaciones / Videos Totales (Oculto en Facebook según GEMINI.md) -->
    <div
      v-if="red.key !== 'facebook'"
      class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-0.5"
    >
      <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
        <span>{{ red.key === 'tiktok' || red.key === 'youtube' ? '🎬 Videos' : (red.key === 'linkedin' ? '📝 Posts' : '📄 Posts') }}</span>
        <span
          v-if="red.crecimiento_neto_posts > 0"
          class="text-emerald-500 text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-500/10"
        >
          +{{ Number(red.crecimiento_neto_posts).toLocaleString('es-AR') }}
        </span>
      </span>
      <div class="text-lg sm:text-xl font-extrabold text-slate-800 dark:text-slate-200 leading-tight">
        {{ Number(red.publicaciones_totales || 0).toLocaleString('es-AR') }}
      </div>
      <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
        <span>Punto Alfa:</span>
        <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.publicaciones_punto_cero || 0).toLocaleString('es-AR') }}</span>
      </div>
    </div>

    <!-- Visualizaciones Totales (Específico YouTube) -->
    <div
      v-if="red.key === 'youtube'"
      class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border-2 border-red-500/30 space-y-0.5"
    >
      <span class="text-[10px] uppercase tracking-wider text-red-500 font-bold block flex items-center justify-between">
        <span>👁️ Vistas</span>
        <span
          v-if="red.crecimiento_neto_visualizaciones > 0"
          class="text-emerald-500 text-[9px] font-bold px-1.5 py-0.2 rounded bg-emerald-500/10"
        >
          +{{ Number(red.crecimiento_neto_visualizaciones).toLocaleString('es-AR') }}
        </span>
      </span>
      <div class="text-lg sm:text-xl font-extrabold text-red-600 dark:text-red-400 leading-tight">
        {{ Number(red.visualizaciones_totales || 0).toLocaleString('es-AR') }}
      </div>
      <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
        <span>Punto Alfa:</span>
        <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.visualizaciones_punto_cero || 0).toLocaleString('es-AR') }}</span>
      </div>
    </div>

    <!-- Fecha de Inicio & Punto Cero -->
    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-0.5">
      <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
        📅 Punto Cero
      </span>
      <div class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 pt-0.5 truncate">
        {{ red.fecha_punto_cero || 'No registrada' }}
      </div>
      <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/50 truncate" :title="red.notas_punto_cero || 'Línea de partida de auditoría'">
        {{ red.notas_punto_cero || 'Línea base auditoría' }}
      </div>
    </div>
  </div>
</template>
