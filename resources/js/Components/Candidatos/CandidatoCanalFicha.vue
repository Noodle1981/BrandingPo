<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
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
import CandidatoCanalPuntoCeroGrid from './CandidatoCanalPuntoCeroGrid.vue';
import CandidatoCanalSyncModal from './CandidatoCanalSyncModal.vue';

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

const isSyncModalOpen = ref(false);
const isSyncingCanal = ref(false);
const syncProgress = ref({
  current: 0,
  total: 0,
  percent: 10,
  currentUrl: '',
  isFinished: false,
  totalNewLikes: 0,
  totalNewComments: 0,
  seguidoresInfo: '',
});
const syncLogs = ref([]);
let progressInterval = null;

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval);
  }
});

const isRefreshing = ref(false);
const refreshMessage = ref('');
const refreshError = ref(false);

// Sincronización Maestra (Seguidores + Posts activos ventana <=15 días con Modal de Progreso)
const sincronizarCanal = async () => {
  if (!props.red.perfil_id || isSyncingCanal.value) return;

  isSyncModalOpen.value = true;
  isSyncingCanal.value = true;
  syncProgress.value = {
    current: 0,
    total: 0,
    percent: 15,
    currentUrl: 'Conectando con la red social y auditando seguidores...',
    isFinished: false,
    totalNewLikes: 0,
    totalNewComments: 0,
    seguidoresInfo: 'Auditando seguidores del canal en vivo...',
  };
  syncLogs.value = [];

  // Simulación de avance visual fluido mientras el servidor procesa las publicaciones
  if (progressInterval) clearInterval(progressInterval);
  progressInterval = setInterval(() => {
    if (syncProgress.value.percent < 85) {
      syncProgress.value.percent += 5;
      if (syncProgress.value.percent > 30 && syncProgress.value.currentUrl.includes('seguidores')) {
        syncProgress.value.currentUrl = 'Escaneando publicaciones en ventana activa (≤ 15 días)...';
      }
    }
  }, 400);

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
    if (progressInterval) clearInterval(progressInterval);

    if (response.ok && data.success) {
      syncProgress.value.percent = 100;
      syncProgress.value.current = data.posts_actualizados || 0;
      syncProgress.value.total = data.posts_total || 0;
      syncProgress.value.seguidoresInfo = data.mensaje_seguidores || 'Seguidores actualizados correctamente';
      syncProgress.value.totalNewLikes = data.nuevos_likes || 0;
      syncProgress.value.totalNewComments = data.nuevos_comentarios || 0;
      syncLogs.value = data.logs || [];
      syncProgress.value.currentUrl = '';
      syncProgress.value.isFinished = true;
    } else {
      syncProgress.value.seguidoresInfo = data.mensaje || data.message || 'Hubo un inconveniente al sincronizar.';
      syncProgress.value.currentUrl = '';
      syncProgress.value.isFinished = true;
    }
  } catch (e) {
    if (progressInterval) clearInterval(progressInterval);
    syncProgress.value.seguidoresInfo = 'Error al comunicarse con el servidor.';
    syncProgress.value.currentUrl = '';
    syncProgress.value.isFinished = true;
  } finally {
    isSyncingCanal.value = false;
  }
};

const cerrarSyncModal = () => {
  isSyncModalOpen.value = false;
  if (syncProgress.value.isFinished) {
    router.reload({ preserveScroll: true });
  }
};

// Re-auditar en vivo (únicamente seguidores y datos de cabecera del canal)
const reauditarCanal = async () => {
  if (!props.red.perfil_id || isRefreshing.value) return;
  isRefreshing.value = true;
  refreshError.value = false;
  refreshMessage.value = 'Re-escaneando seguidores en vivo...';

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
    if (response.ok && data.success) {
      refreshError.value = false;
      refreshMessage.value = data.mensaje || '¡Canal re-auditado con éxito!';
      // Refresco reactivo mediante Inertia sin descarga de DOM ni pantalla azul
      router.reload({
        preserveScroll: true,
        onSuccess: () => {
          setTimeout(() => {
            refreshMessage.value = '';
          }, 4000);
        }
      });
    } else {
      refreshError.value = true;
      refreshMessage.value = data.mensaje || data.message || 'No se pudo re-auditar el canal.';
      setTimeout(() => {
        refreshMessage.value = '';
        refreshError.value = false;
      }, 5000);
    }
  } catch (e) {
    refreshError.value = true;
    refreshMessage.value = 'Error al consultar el scraper en vivo.';
    setTimeout(() => {
      refreshMessage.value = '';
      refreshError.value = false;
    }, 5000);
  } finally {
    isRefreshing.value = false;
  }
};
</script>

<template>
  <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3.5">
    <!-- Header Ficha del Canal -->
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 flex-wrap gap-3">
      <div class="flex items-center gap-3">
        <!-- Foto de Perfil en la Red Social -->
        <div class="relative shrink-0">
          <img
            :src="red.foto_perfil_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(red.handle_usuario || red.nombre || 'Canal')}&background=${esPropio ? '082f49' : '1e1b4b'}&color=${esPropio ? '38bdf8' : 'a855f7'}`"
            :alt="red.nombre"
            referrerpolicy="no-referrer"
            class="w-10 h-10 rounded-xl object-cover border-2 shadow-xs"
            :class="esPropio ? 'border-cyan-500' : 'border-purple-500'"
          />
          <div
            class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full flex items-center justify-center border border-white dark:border-slate-900"
            :class="{
              'bg-blue-500 text-white': red.color_estado === 'azul',
              'bg-emerald-500 text-white': red.color_estado === 'verde' || red.color_estado === 'naranja',
              'bg-rose-500 text-white': red.color_estado === 'rojo',
              'bg-slate-400 text-slate-900': red.color_estado === 'gris',
            }"
          >
            <CheckCircle v-if="red.color_estado === 'azul'" class="w-2.5 h-2.5" />
            <span v-else class="text-[8px] font-extrabold">{{ red.color_estado === 'rojo' ? '×' : '●' }}</span>
          </div>
        </div>

        <div>
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
              <span>{{ red.nombre }}</span>
              <span v-if="!esPropio" class="text-[10px] px-1.5 py-0.2 rounded-full bg-purple-500/10 text-purple-500 font-mono font-bold">
                Rival
              </span>
            </h2>
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
              :class="tabBadgeStyle(red.color_estado).pill"
            >
              {{ tabBadgeStyle(red.color_estado).label }}
            </span>
          </div>

          <div class="flex items-center gap-2.5 text-xs font-mono text-slate-600 dark:text-slate-400 flex-wrap">
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
              <span>Abrir perfil</span>
              <ExternalLink class="w-3 h-3" />
            </a>
            <span v-else class="text-slate-400 text-[11px] italic">
              (Sin enlace)
            </span>
          </div>
        </div>
      </div>

      <!-- Barra de Botones de Acción del Canal -->
      <div class="flex items-center gap-1.5 flex-wrap">
        <!-- 1. Enlace a Métricas del Canal -->
        <Link
          v-if="red.perfil_id"
          :href="`/perfiles-sociales/${red.perfil_id}/metricas`"
          class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono flex items-center gap-1.5 transition-all shadow-xs cursor-pointer hover:scale-102"
          :title="`Ver panel analítico avanzado de ${red.nombre}`"
        >
          <BarChart3 class="w-3.5 h-3.5" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
          <span>Dashboard</span>
        </Link>

        <!-- 2. Sincronización Maestra con Modal de Progreso -->
        <button
          v-if="canWrite && red.perfil_id"
          type="button"
          @click="sincronizarCanal"
          :disabled="isSyncingCanal"
          class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono flex items-center gap-1.5 transition-all shadow-xs cursor-pointer hover:scale-102 disabled:opacity-50"
          :title="`Sincronización Maestra: audita seguidores y todas las publicaciones de ${red.nombre} en ventana activa (≤ 15 días) con progreso en vivo`"
        >
          <Zap class="w-3.5 h-3.5" :class="[isSyncingCanal ? 'animate-bounce text-amber-500' : (esPropio ? 'text-cyan-500' : 'text-purple-500')]" />
          <span>{{ isSyncingCanal ? 'Sincronizando...' : 'Sincronizar' }}</span>
        </button>

        <!-- 3. Re-auditar Seguidores en Vivo -->
        <button
          v-if="canWrite && red.perfil_id"
          type="button"
          @click="reauditarCanal"
          :disabled="isRefreshing"
          class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono flex items-center gap-1.5 transition-all shadow-xs cursor-pointer hover:scale-102 disabled:opacity-50"
          :title="`Auditoría rápida: actualiza únicamente el número de seguidores y datos de cabecera de ${red.nombre}`"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="[isRefreshing ? 'animate-spin text-cyan-500' : 'text-slate-400']" />
          <span>{{ isRefreshing ? 'Leyendo...' : 'Re-auditar' }}</span>
        </button>

        <!-- 4. Configurar Punto Cero -->
        <button
          v-if="canWrite"
          type="button"
          @click="emit('configurar', red.key)"
          class="px-3 py-1.5 rounded-lg text-white text-xs font-bold font-mono flex items-center gap-1.5 transition-all cursor-pointer shadow-xs hover:scale-102"
          :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500 shadow-cyan-600/20' : 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/20'"
        >
          <Settings class="w-3.5 h-3.5" />
          <span>Configurar Punto Cero</span>
        </button>
      </div>
    </div>

    <!-- Mensaje de feedback de re-auditar seguidores -->
    <div
      v-if="refreshMessage"
      class="p-2.5 rounded-xl border text-xs font-mono flex items-center gap-2 transition-all"
      :class="refreshError
        ? 'bg-rose-500/10 border-rose-500/30 text-rose-600 dark:text-rose-400'
        : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400'"
    >
      <AlertCircle v-if="refreshError" class="w-4 h-4 shrink-0 text-rose-500" />
      <CheckCircle v-else class="w-4 h-4 shrink-0 text-emerald-500" />
      <span>{{ refreshMessage }}</span>
    </div>

    <!-- Tarjetas de Métricas de Punto Cero vs Actual (Modularizado) -->
    <CandidatoCanalPuntoCeroGrid :red="red" :es-propio="esPropio" />

    <!-- Banner si el canal está inactivo / no configurado -->
    <div
      v-if="red.color_estado === 'rojo' || red.color_estado === 'gris'"
      class="p-3 rounded-xl border flex items-center justify-between flex-wrap gap-2.5"
      :class="red.color_estado === 'rojo' ? 'bg-rose-500/10 border-rose-500/30' : 'bg-slate-100 dark:bg-slate-800/60 border-slate-200 dark:border-slate-700'"
    >
      <div class="flex items-center gap-2">
        <AlertCircle class="w-4 h-4 shrink-0" :class="red.color_estado === 'rojo' ? 'text-rose-500' : 'text-slate-400'" />
        <p class="text-xs" :class="red.color_estado === 'rojo' ? 'text-rose-600 dark:text-rose-300' : 'text-slate-600 dark:text-slate-300'">
          <span v-if="red.color_estado === 'rojo'">Canal digital <strong>Inactivo o Sin Movimiento</strong>.</span>
          <span v-else>Canal sin perfil vinculado. Configúralo para iniciar la auditoría.</span>
        </p>
      </div>
      <button
        v-if="canWrite"
        type="button"
        @click="emit('configurar', red.key)"
        class="px-3 py-1 rounded-lg text-white font-bold text-xs font-mono transition-all cursor-pointer"
        :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-purple-600 hover:bg-purple-500'"
      >
        Configurar Ahora
      </button>
    </div>

    <!-- MODAL DE SINCRONIZACIÓN EN VIVO (VENTANA 15 DÍAS - MODULARIZADO) -->
    <CandidatoCanalSyncModal
      :is-open="isSyncModalOpen"
      :canal-nombre="red.nombre"
      :is-syncing="isSyncingCanal"
      :sync-progress="syncProgress"
      :sync-logs="syncLogs"
      @close="cerrarSyncModal"
    />
  </div>
</template>
