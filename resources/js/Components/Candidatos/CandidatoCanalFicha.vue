<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  ExternalLink,
  Settings,
  RefreshCw,
  Zap,
  BarChart3,
  CheckCircle,
  AlertCircle
} from '@lucide/vue';
import { tabBadgeStyle } from '../../Utils/socialConstants';

const props = defineProps({
  red: {
    type: Object,
    required: true,
  },
  candidato: {
    type: Object,
    required: true,
  },
  tema: {
    type: String,
    default: 'propio', // 'propio' | 'rival'
  },
});

const emit = defineEmits(['configurar']);

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? true);
const esPropio = computed(() => props.tema === 'propio' || props.candidato?.es_propio);

const isSyncing = ref(false);
const syncMessage = ref('');
const isRefreshing = ref(false);
const refreshMessage = ref('');

// Sincronización Maestra (Seguidores + Posts activos ventana <=15 días)
const sincronizarCanal = async () => {
  if (!props.red.perfil_id) return;
  isSyncing.value = true;
  syncMessage.value = 'Sincronizando canal y publicaciones activas...';

  try {
    const response = await fetch(`/perfiles-sociales/${props.red.perfil_id}/sincronizar-canal`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
    });
    const data = await response.json();
    if (data.success) {
      syncMessage.value = `¡Sincronización completada! ${data.mensaje_seguidores || ''} (${data.posts_actualizados || 0} posts actualizados).`;
      setTimeout(() => {
        window.location.reload();
      }, 1200);
    } else {
      syncMessage.value = data.mensaje || 'Hubo un inconveniente al sincronizar.';
    }
  } catch (e) {
    syncMessage.value = 'Error al comunicarse con el servidor.';
  } finally {
    isSyncing.value = false;
  }
};

// Re-auditar en vivo
const reauditarCanal = async () => {
  if (!props.red.perfil_id) return;
  isRefreshing.value = true;
  refreshMessage.value = 'Re-escaneando métricas en vivo...';

  try {
    const response = await fetch(`/perfiles-sociales/${props.red.perfil_id}/refrescar`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
    });
    const data = await response.json();
    if (data.success) {
      refreshMessage.value = data.mensaje || '¡Canal re-auditado con éxito!';
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      refreshMessage.value = data.mensaje || 'No se pudo re-auditar el canal.';
    }
  } catch (e) {
    refreshMessage.value = 'Error al consultar el scraper en vivo.';
  } finally {
    isRefreshing.value = false;
  }
};
</script>

<template>
  <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
    <!-- Header Ficha del Canal -->
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-4">
      <div class="flex items-center gap-4">
        <!-- Foto de Perfil en la Red Social -->
        <div class="relative shrink-0">
          <img
            :src="red.foto_perfil_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(red.handle_usuario || red.nombre || 'Canal')}&background=${esPropio ? '082f49' : '1e1b4b'}&color=${esPropio ? '38bdf8' : 'a855f7'}`"
            :alt="red.nombre"
            referrerpolicy="no-referrer"
            class="w-14 h-14 rounded-2xl object-cover border-2 shadow-sm"
            :class="esPropio ? 'border-cyan-500' : 'border-purple-500'"
          />
          <div
            class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900"
            :class="{
              'bg-blue-500 text-white': red.color_estado === 'azul',
              'bg-emerald-500 text-white': red.color_estado === 'verde' || red.color_estado === 'naranja',
              'bg-rose-500 text-white': red.color_estado === 'rojo',
              'bg-slate-400 text-slate-900': red.color_estado === 'gris',
            }"
          >
            <CheckCircle v-if="red.color_estado === 'azul'" class="w-3 h-3" />
            <span v-else class="text-[9px] font-extrabold">{{ red.color_estado === 'rojo' ? '×' : '●' }}</span>
          </div>
        </div>

        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span>{{ red.nombre }}</span>
              <span v-if="!esPropio" class="text-xs px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-500 font-mono font-bold">
                Rival
              </span>
            </h2>
            <span
              class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
              :class="tabBadgeStyle(red.color_estado).pill"
            >
              {{ tabBadgeStyle(red.color_estado).label }}
            </span>
          </div>

          <div class="flex items-center gap-3 mt-1 text-xs font-mono text-slate-600 dark:text-slate-400 flex-wrap">
            <span class="font-bold text-slate-800 dark:text-slate-200">
              {{ red.handle_usuario || 'Sin handle asignado' }}
            </span>
            <a
              v-if="red.url_perfil"
              :href="red.url_perfil"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1 text-[11px] underline font-semibold transition-colors"
              :class="esPropio ? 'text-cyan-500 hover:text-cyan-400' : 'text-purple-500 hover:text-purple-400'"
            >
              <span>Abrir perfil oficial</span>
              <ExternalLink class="w-3 h-3" />
            </a>
            <span v-else class="text-slate-400 text-[11px] italic">
              (Enlace no configurado)
            </span>
          </div>
        </div>
      </div>

      <!-- Barra de Botones de Acción del Canal -->
      <div class="flex items-center gap-2 flex-wrap">
        <!-- 1. Enlace a Métricas del Canal -->
        <Link
          v-if="red.perfil_id"
          :href="`/perfiles-sociales/${red.perfil_id}/metricas`"
          class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono flex items-center gap-1.5 transition-all shadow-xs cursor-pointer hover:scale-102"
          :title="`Ver panel analítico avanzado de ${red.nombre}`"
        >
          <BarChart3 class="w-4 h-4" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
          <span>Dashboard Canal</span>
        </Link>

        <!-- 2. Sincronización Maestra -->
        <button
          v-if="canWrite && red.perfil_id"
          type="button"
          @click="sincronizarCanal"
          :disabled="isSyncing"
          class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono flex items-center gap-1.5 transition-all shadow-xs cursor-pointer hover:scale-102 disabled:opacity-50"
          :title="`Sincronizar seguidores y posts de ${red.nombre} en <=15 días`"
        >
          <Zap class="w-4 h-4" :class="[isSyncing ? 'animate-bounce text-amber-500' : (esPropio ? 'text-cyan-500' : 'text-purple-500')]" />
          <span>{{ isSyncing ? 'Sincronizando...' : 'Sincronizar Canal' }}</span>
        </button>

        <!-- 3. Re-auditar en Vivo -->
        <button
          v-if="canWrite && red.perfil_id"
          type="button"
          @click="reauditarCanal"
          :disabled="isRefreshing"
          class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono flex items-center gap-1.5 transition-all shadow-xs cursor-pointer hover:scale-102 disabled:opacity-50"
          :title="`Re-escanear métricas públicas en vivo de ${red.nombre}`"
        >
          <RefreshCw class="w-4 h-4" :class="[isRefreshing ? 'animate-spin text-cyan-500' : 'text-slate-400']" />
          <span>{{ isRefreshing ? 'Leyendo...' : 'Re-auditar' }}</span>
        </button>

        <!-- 4. Configurar Punto Cero -->
        <button
          v-if="canWrite"
          type="button"
          @click="emit('configurar', red.key)"
          class="px-4 py-2 rounded-xl text-white text-xs font-bold font-mono flex items-center gap-1.5 transition-all cursor-pointer shadow-sm hover:scale-102"
          :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500 shadow-cyan-600/20' : 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/20'"
        >
          <Settings class="w-4 h-4" />
          <span>Configurar Canal & Punto Cero</span>
        </button>
      </div>
    </div>

    <!-- Mensajes de feedback de sincronización -->
    <div v-if="syncMessage" class="p-3 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-600 dark:text-cyan-400 text-xs font-mono flex items-center gap-2">
      <CheckCircle class="w-4 h-4 shrink-0" />
      <span>{{ syncMessage }}</span>
    </div>

    <div v-if="refreshMessage" class="p-3 rounded-2xl bg-purple-500/10 border border-purple-500/30 text-purple-600 dark:text-purple-400 text-xs font-mono flex items-center gap-2">
      <CheckCircle class="w-4 h-4 shrink-0" />
      <span>{{ refreshMessage }}</span>
    </div>

    <!-- Tarjetas de Métricas de Punto Cero vs Actual -->
    <div
      class="grid gap-4 font-mono"
      :class="red.key === 'facebook' ? 'grid-cols-1 sm:grid-cols-3' : (red.key === 'tiktok' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4')"
    >
      <!-- Seguidores / Suscriptores / Contactos -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
          <span>👥 {{ red.key === 'youtube' ? 'Suscriptores' : (red.key === 'linkedin' ? 'Contactos' : 'Seguidores') }}</span>
          <span
            v-if="red.crecimiento_neto_seguidores > 0"
            class="text-[10px] font-bold px-1.5 py-0.5 rounded font-mono"
            :class="esPropio ? 'text-emerald-500 bg-emerald-500/10' : 'text-purple-400 bg-purple-500/10'"
          >
            +{{ Number(red.crecimiento_neto_seguidores).toLocaleString('es-AR') }}
          </span>
        </span>
        <div
          class="text-2xl font-extrabold"
          :class="esPropio ? 'text-cyan-600 dark:text-cyan-400' : 'text-purple-600 dark:text-purple-400'"
        >
          {{ Number(red.seguidores_actuales || 0).toLocaleString('es-AR') }}
        </div>
        <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
          <span>Punto Alfa (Inicio):</span>
          <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.seguidores_punto_cero || 0).toLocaleString('es-AR') }}</span>
        </div>
      </div>

      <!-- Cuentas Seguidas (Oculto en YouTube) -->
      <div
        v-if="red.key !== 'youtube'"
        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1"
      >
        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block">
          🔄 Seguidos
        </span>
        <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-200">
          {{ Number(red.seguidos_actuales || 0).toLocaleString('es-AR') }}
        </div>
        <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
          <span>Punto Alfa (Inicio):</span>
          <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.seguidos_punto_cero || 0).toLocaleString('es-AR') }}</span>
        </div>
      </div>

      <!-- Me Gusta Acumulados (Específico TikTok) -->
      <div
        v-if="red.key === 'tiktok'"
        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-rose-500/30 space-y-1"
      >
        <span class="text-[11px] uppercase tracking-wider text-rose-500 font-bold block flex items-center justify-between">
          <span>❤️ Me Gusta</span>
          <span
            v-if="red.crecimiento_neto_me_gusta > 0"
            class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10"
          >
            +{{ Number(red.crecimiento_neto_me_gusta).toLocaleString('es-AR') }}
          </span>
        </span>
        <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">
          {{ Number(red.me_gusta_totales || 0).toLocaleString('es-AR') }}
        </div>
        <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
          <span>Punto Alfa (Inicio):</span>
          <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.me_gusta_punto_cero || 0).toLocaleString('es-AR') }}</span>
        </div>
      </div>

      <!-- Publicaciones / Videos Totales (Oculto en Facebook según GEMINI.md) -->
      <div
        v-if="red.key !== 'facebook'"
        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1"
      >
        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
          <span>{{ red.key === 'tiktok' || red.key === 'youtube' ? '🎬 Videos' : (red.key === 'linkedin' ? '📝 Posts / Artículos' : '📄 Publicaciones') }}</span>
          <span
            v-if="red.crecimiento_neto_posts > 0"
            class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10"
          >
            +{{ Number(red.crecimiento_neto_posts).toLocaleString('es-AR') }}
          </span>
        </span>
        <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-200">
          {{ Number(red.publicaciones_totales || 0).toLocaleString('es-AR') }}
        </div>
        <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
          <span>Punto Alfa (Inicio):</span>
          <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.publicaciones_punto_cero || 0).toLocaleString('es-AR') }}</span>
        </div>
      </div>

      <!-- Visualizaciones Totales (Específico YouTube) -->
      <div
        v-if="red.key === 'youtube'"
        class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-red-500/30 space-y-1"
      >
        <span class="text-[11px] uppercase tracking-wider text-red-500 font-bold block flex items-center justify-between">
          <span>👁️ Visualizaciones</span>
          <span
            v-if="red.crecimiento_neto_visualizaciones > 0"
            class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10"
          >
            +{{ Number(red.crecimiento_neto_visualizaciones).toLocaleString('es-AR') }}
          </span>
        </span>
        <div class="text-2xl font-extrabold text-red-600 dark:text-red-400">
          {{ Number(red.visualizaciones_totales || 0).toLocaleString('es-AR') }}
        </div>
        <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
          <span>Punto Alfa (Inicio):</span>
          <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(red.visualizaciones_punto_cero || 0).toLocaleString('es-AR') }}</span>
        </div>
      </div>

      <!-- Fecha de Inicio & Punto Cero -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block">
          📅 Fecha Punto Cero
        </span>
        <div class="text-sm font-bold text-slate-800 dark:text-slate-200 pt-1">
          {{ red.fecha_punto_cero || 'No registrada' }}
        </div>
        <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/50 truncate" :title="red.notas_punto_cero || 'Línea de partida de auditoría'">
          {{ red.notas_punto_cero || 'Línea de partida de auditoría' }}
        </div>
      </div>
    </div>

    <!-- Banner si el canal está inactivo / no configurado -->
    <div
      v-if="red.color_estado === 'rojo' || red.color_estado === 'gris'"
      class="p-4 rounded-2xl border flex items-center justify-between flex-wrap gap-3"
      :class="red.color_estado === 'rojo' ? 'bg-rose-500/10 border-rose-500/30' : 'bg-slate-100 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700'"
    >
      <div class="flex items-center gap-2.5">
        <AlertCircle class="w-5 h-5 shrink-0" :class="red.color_estado === 'rojo' ? 'text-rose-500' : 'text-slate-400'" />
        <p class="text-xs" :class="red.color_estado === 'rojo' ? 'text-rose-600 dark:text-rose-300' : 'text-slate-600 dark:text-slate-300'">
          <span v-if="red.color_estado === 'rojo'">Este canal digital figura como <strong>Inactivo o Sin Movimiento</strong>.</span>
          <span v-else>Este canal aún no cuenta con un perfil vinculado. Configúralo para comenzar la auditoría de campaña.</span>
        </p>
      </div>
      <button
        v-if="canWrite"
        type="button"
        @click="emit('configurar', red.key)"
        class="px-3.5 py-1.5 rounded-xl text-white font-bold text-xs font-mono transition-all cursor-pointer"
        :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-purple-600 hover:bg-purple-500'"
      >
        Configurar Ahora
      </button>
    </div>
  </div>
</template>
