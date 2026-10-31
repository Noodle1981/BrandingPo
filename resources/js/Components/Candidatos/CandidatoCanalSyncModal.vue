<script setup>
import { RefreshCw, CheckCircle, X } from '@lucide/vue';

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  canalNombre: {
    type: String,
    default: '',
  },
  isSyncing: {
    type: Boolean,
    default: false,
  },
  syncProgress: {
    type: Object,
    required: true,
  },
  syncLogs: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close']);
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
  >
    <div class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-2xl bg-cyan-500/15 text-cyan-500 flex items-center justify-center shadow-xs">
            <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': isSyncing }" />
          </div>
          <div>
            <h3 class="font-extrabold text-base sm:text-lg text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span>⚡ Sincronización Maestra</span>
              <span class="px-2 py-0.5 rounded-md bg-cyan-500/10 text-cyan-500 text-xs font-mono font-bold">
                {{ canalNombre }}
              </span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Auditando seguidores de {{ canalNombre }} y publicaciones en ventana activa (≤ 15 días).
            </p>
          </div>
        </div>

        <button
          v-if="!isSyncing"
          type="button"
          @click="emit('close')"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Paso 1: Estado de Seguidores del Canal -->
      <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs font-mono">
        <div class="flex items-center gap-2">
          <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-[10px]">
            PASO 1
          </span>
          <span class="font-bold text-slate-800 dark:text-slate-200">
            👥 Seguidores del Canal:
          </span>
        </div>
        <span class="font-bold text-emerald-600 dark:text-emerald-400">
          {{ syncProgress.seguidoresInfo || (isSyncing ? 'Auditando seguidores en vivo...' : 'Seguidores al día') }}
        </span>
      </div>

      <!-- Paso 2: Barra de Progreso y Estado de Publicaciones -->
      <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5">
        <div class="flex items-center justify-between text-xs font-mono">
          <span class="text-slate-600 dark:text-slate-400 font-semibold flex items-center gap-1.5">
            <RefreshCw v-if="isSyncing" class="w-3.5 h-3.5 animate-spin text-cyan-500" />
            <CheckCircle v-else class="w-3.5 h-3.5 text-emerald-500" />
            <span>{{ isSyncing ? 'PASO 2: Sincronizando publicaciones activas...' : 'PASO 2: Publicaciones Sincronizadas' }}</span>
          </span>
          <span class="font-bold text-slate-900 dark:text-slate-100">
            {{ syncProgress.current }} / {{ syncProgress.total }} publicaciones
          </span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full h-2.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
          <div
            class="h-full bg-gradient-to-r from-cyan-500 to-emerald-500 transition-all duration-300 rounded-full"
            :style="{ width: `${syncProgress.percent}%` }"
          ></div>
        </div>

        <!-- Enlace actual en lectura -->
        <div v-if="isSyncing && syncProgress.currentUrl" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/20 text-xs font-mono">
          <span class="text-[10px] text-cyan-500 font-bold block">🔗 ESTADO:</span>
          <p class="text-slate-700 dark:text-slate-300 truncate text-[11px] mt-0.5">
            {{ syncProgress.currentUrl }}
          </p>
        </div>
      </div>

      <!-- Resumen de Resultados si finalizó -->
      <div v-if="syncProgress.isFinished" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-2.5">
          <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0" />
          <div>
            <span class="text-xs font-extrabold text-emerald-800 dark:text-emerald-300 block">
              ¡Sincronización Maestra finalizada con éxito!
            </span>
            <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-mono">
              {{ syncProgress.seguidoresInfo }} &bull; +{{ syncProgress.totalNewLikes }} likes y +{{ syncProgress.totalNewComments }} comentarios actualizados.
            </span>
          </div>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-xs cursor-pointer"
        >
          Aceptar
        </button>
      </div>

      <!-- Log / Lista en Vivo de Publicaciones Procesadas -->
      <div class="space-y-2">
        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono block">
          📋 Registro de Publicaciones Procesadas:
        </span>

        <div class="max-h-60 overflow-y-auto space-y-2 pr-1 font-mono text-xs">
          <div
            v-for="(log, idx) in syncLogs"
            :key="idx"
            class="p-3 rounded-xl border flex items-start justify-between gap-3 transition-all"
            :class="log.status === 'success' ? 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' : 'bg-amber-500/10 border-amber-500/20'"
          >
            <div class="space-y-0.5 flex-1 min-w-0">
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold">
                  {{ log.fecha || 'Reciente' }}
                </span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate block">
                  {{ log.resumen }}
                </span>
              </div>
              <p class="text-[10px] text-slate-400 truncate">{{ log.url }}</p>
            </div>

            <div v-if="log.status === 'success'" class="text-right shrink-0">
              <span class="text-xs font-extrabold text-cyan-600 dark:text-cyan-400 block">
                ❤️ {{ log.likes }} <span v-if="log.deltaLikes > 0" class="text-emerald-500 font-bold">(+{{ log.deltaLikes }})</span>
              </span>
              <span class="text-[10px] text-slate-500 dark:text-slate-400 block">
                💬 {{ log.comments }} <span v-if="log.deltaComments > 0" class="text-blue-500 font-bold">(+{{ log.deltaComments }})</span>
              </span>
            </div>
            <div v-else class="text-right shrink-0 text-amber-500 text-[11px] font-bold">
              ⚠️ {{ log.error }}
            </div>
          </div>

          <div v-if="!syncLogs.length && isSyncing" class="p-6 text-center text-slate-400 text-xs">
            Iniciando lectura de publicaciones...
          </div>
          <div v-else-if="!syncLogs.length && !isSyncing" class="p-6 text-center text-slate-400 text-xs">
            No se encontraron publicaciones en la ventana activa de 15 días.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
