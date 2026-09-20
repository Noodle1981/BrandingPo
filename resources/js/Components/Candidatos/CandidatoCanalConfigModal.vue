<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
  Settings,
  Sparkles,
  Link2,
  CheckCircle,
  AlertCircle,
  Flag,
  Image as ImageIcon,
  Save,
  X
} from '@lucide/vue';
import {
  getSocialMeta,
  getSocialPlaceholder,
  getHandlePlaceholder
} from '../../Utils/socialConstants';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  candidato: {
    type: Object,
    required: true,
  },
  currentRed: {
    type: Object,
    required: true,
  },
  tema: {
    type: String,
    default: 'propio', // 'propio' | 'rival'
  },
});

const emit = defineEmits(['close', 'saved']);

const esPropio = computed(() => props.tema === 'propio' || props.candidato?.es_propio);

const formRed = useForm({
  candidato_id: props.candidato.id,
  plataforma: props.currentRed.key || 'instagram',
  handle_usuario: props.currentRed.handle_usuario || '',
  url_perfil: props.currentRed.url_perfil || '',
  foto_perfil_url: props.currentRed.foto_perfil_url || '',
  esta_activo: props.currentRed.esta_activo ?? true,
  esta_verificado: props.currentRed.esta_verificado ?? false,
  seguidores_actuales: props.currentRed.seguidores_actuales || 0,
  seguidos_actuales: props.currentRed.seguidos_actuales || 0,
  publicaciones_totales: props.currentRed.publicaciones_totales || 0,
  me_gusta_totales: props.currentRed.me_gusta_totales || 0,
  visualizaciones_totales: props.currentRed.visualizaciones_totales || 0,
  fecha_punto_cero: props.currentRed.fecha_punto_cero || new Date().toISOString().slice(0, 10),
  seguidores_punto_cero: props.currentRed.seguidores_punto_cero || props.currentRed.seguidores_actuales || 0,
  seguidos_punto_cero: props.currentRed.seguidos_punto_cero || props.currentRed.seguidos_actuales || 0,
  publicaciones_punto_cero: props.currentRed.publicaciones_punto_cero || props.currentRed.publicaciones_totales || 0,
  me_gusta_punto_cero: props.currentRed.me_gusta_punto_cero || props.currentRed.me_gusta_totales || 0,
  visualizaciones_punto_cero: props.currentRed.visualizaciones_punto_cero || props.currentRed.visualizaciones_totales || 0,
  notas_punto_cero: props.currentRed.notas_punto_cero || '',
});

watch(() => props.currentRed, (newRed) => {
  if (!newRed) return;
  formRed.candidato_id = props.candidato.id;
  formRed.plataforma = newRed.key || 'instagram';
  formRed.handle_usuario = newRed.handle_usuario || '';
  formRed.url_perfil = newRed.url_perfil || '';
  formRed.foto_perfil_url = newRed.foto_perfil_url || '';
  formRed.esta_activo = newRed.esta_activo ?? true;
  formRed.esta_verificado = newRed.esta_verificado ?? false;
  formRed.seguidores_actuales = newRed.seguidores_actuales || 0;
  formRed.seguidos_actuales = newRed.seguidos_actuales || 0;
  formRed.publicaciones_totales = newRed.publicaciones_totales || 0;
  formRed.me_gusta_totales = newRed.me_gusta_totales || 0;
  formRed.visualizaciones_totales = newRed.visualizaciones_totales || 0;
  formRed.fecha_punto_cero = newRed.fecha_punto_cero || new Date().toISOString().slice(0, 10);
  formRed.seguidores_punto_cero = newRed.seguidores_punto_cero || newRed.seguidores_actuales || 0;
  formRed.seguidos_punto_cero = newRed.seguidos_punto_cero || newRed.seguidos_actuales || 0;
  formRed.publicaciones_punto_cero = newRed.publicaciones_punto_cero || newRed.publicaciones_totales || 0;
  formRed.me_gusta_punto_cero = newRed.me_gusta_punto_cero || newRed.me_gusta_totales || 0;
  formRed.visualizaciones_punto_cero = newRed.visualizaciones_punto_cero || newRed.visualizaciones_totales || 0;
  formRed.notas_punto_cero = newRed.notas_punto_cero || '';
  scrapeMessage.value = '';
}, { deep: true });

const isScraping = ref(false);
const scrapeMessage = ref('');
const scrapeSuccess = ref(false);

const fetchScrapedData = async () => {
  if (!formRed.url_perfil) {
    scrapeMessage.value = 'Por favor ingresa primero la URL del perfil.';
    scrapeSuccess.value = false;
    return;
  }
  isScraping.value = true;
  scrapeMessage.value = 'Extrayendo métricas públicas del canal con lector especializado...';

  try {
    const response = await fetch('/perfiles-sociales/scrape', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        url: formRed.url_perfil,
        plataforma: formRed.plataforma,
      }),
    });

    const data = await response.json();
    if (data) {
      if (data.handle_usuario) formRed.handle_usuario = data.handle_usuario;
      if (data.foto_perfil_url) formRed.foto_perfil_url = data.foto_perfil_url;

      if (data.seguidores !== null && data.seguidores !== undefined) {
        formRed.seguidores_actuales = Number(data.seguidores);
        formRed.seguidores_punto_cero = Number(data.seguidores);
      }
      if (data.seguidos !== null && data.seguidos !== undefined) {
        formRed.seguidos_actuales = Number(data.seguidos);
        formRed.seguidos_punto_cero = Number(data.seguidos);
      }
      if (data.publicaciones !== null && data.publicaciones !== undefined) {
        formRed.publicaciones_totales = Number(data.publicaciones);
        formRed.publicaciones_punto_cero = Number(data.publicaciones);
      }
      if (data.me_gusta_totales !== null && data.me_gusta_totales !== undefined) {
        formRed.me_gusta_totales = Number(data.me_gusta_totales);
        formRed.me_gusta_punto_cero = Number(data.me_gusta_totales);
      }
      if (data.visualizaciones_totales !== null && data.visualizaciones_totales !== undefined) {
        formRed.visualizaciones_totales = Number(data.visualizaciones_totales);
        formRed.visualizaciones_punto_cero = Number(data.visualizaciones_totales);
      }

      formRed.esta_activo = true;
      scrapeSuccess.value = true;
      scrapeMessage.value = data.mensaje || '¡Datos extraídos con éxito!';
    }
  } catch (err) {
    scrapeSuccess.value = false;
    scrapeMessage.value = 'No se pudo conectar con el lector. Puedes ingresar los números manualmente.';
  } finally {
    isScraping.value = false;
  }
};

const savePerfilSocial = () => {
  if (!formRed.seguidores_punto_cero) formRed.seguidores_punto_cero = formRed.seguidores_actuales;
  if (!formRed.seguidos_punto_cero) formRed.seguidos_punto_cero = formRed.seguidos_actuales;
  if (!formRed.publicaciones_punto_cero) formRed.publicaciones_punto_cero = formRed.publicaciones_totales;
  if (!formRed.me_gusta_punto_cero) formRed.me_gusta_punto_cero = formRed.me_gusta_totales;
  if (!formRed.visualizaciones_punto_cero) formRed.visualizaciones_punto_cero = formRed.visualizaciones_totales;

  formRed.post('/perfiles-sociales', {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved');
      emit('close');
    }
  });
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
  >
    <div class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
      <!-- Header Modal -->
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
        <div class="flex items-center gap-3">
          <div class="flex items-center justify-center w-10 h-10 rounded-xl" :class="getSocialMeta(currentRed.key).bgLight">
            <Settings class="w-5 h-5" :style="{ color: getSocialMeta(currentRed.key).color }" />
          </div>
          <div>
            <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span>Configurar Canal: {{ currentRed.nombre }}</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Punto Cero, enlace oficial, estado de canal y lector con 1 clic.
            </p>
          </div>
        </div>

        <button
          type="button"
          @click="emit('close')"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="savePerfilSocial" class="space-y-5">
        <!-- A. Enlace, Lector Automático y Estados -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
          <div>
            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                1. Enlace Directo al Perfil en {{ currentRed.nombre }} (URL)
              </label>
              <button
                type="button"
                @click="fetchScrapedData"
                :disabled="isScraping || !formRed.url_perfil"
                class="px-3 py-1.5 rounded-xl text-white font-bold text-xs font-mono flex items-center gap-1.5 transition-all shadow-sm cursor-pointer disabled:opacity-50"
                :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-purple-600 hover:bg-purple-500'"
                title="Leer automáticamente foto, seguidores, seguidos y publicaciones"
              >
                <Sparkles class="w-3.5 h-3.5" />
                <span>{{ isScraping ? 'Leyendo datos...' : '⚡ Leer Datos & Foto con 1 Clic' }}</span>
              </button>
            </div>

            <div class="relative">
              <input
                v-model="formRed.url_perfil"
                type="url"
                :placeholder="getSocialPlaceholder(currentRed.key)"
                class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2"
                :class="esPropio ? 'focus:ring-cyan-500' : 'focus:ring-purple-500'"
              />
              <Link2 class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            </div>

            <div v-if="scrapeMessage" class="mt-2 flex items-center gap-2 text-xs font-mono" :class="scrapeSuccess ? 'text-emerald-500' : 'text-amber-500'">
              <CheckCircle v-if="scrapeSuccess" class="w-4 h-4" />
              <AlertCircle v-else class="w-4 h-4" />
              <span>{{ scrapeMessage }}</span>
            </div>
          </div>

          <!-- Handle & Switches -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-200 dark:border-slate-800">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Usuario / Handle *
              </label>
              <input
                v-model="formRed.handle_usuario"
                type="text"
                required
                :placeholder="getHandlePlaceholder(currentRed.key)"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100"
              />
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
              <div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Canal Activo</span>
                <span class="text-[10px] text-emerald-500 font-semibold">🟢 Pestaña Activa</span>
              </div>
              <input
                v-model="formRed.esta_activo"
                type="checkbox"
                class="w-5 h-5 rounded text-emerald-600 focus:ring-emerald-500"
              />
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
              <div>
                <span class="text-xs font-bold text-blue-500 dark:text-blue-400 block">Cuenta Verificada</span>
                <span class="text-[10px] text-blue-400 font-semibold">🔵 Pestaña Azul</span>
              </div>
              <input
                v-model="formRed.esta_verificado"
                type="checkbox"
                class="w-5 h-5 rounded text-blue-500 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- B. FOTO DE PERFIL & TABLA ÚNICA DE NÚMEROS (PUNTO CERO) -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-2">
            <Flag class="w-4 h-4" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
            <span>2. Foto de Perfil & Punto Cero (Línea de Base)</span>
          </h3>

          <!-- Preview Foto & Input URL -->
          <div class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex-wrap">
            <div class="relative shrink-0">
              <img
                :src="formRed.foto_perfil_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(formRed.handle_usuario || currentRed.nombre || 'Canal')}&background=082f49&color=38bdf8`"
                alt="Foto Perfil"
                referrerpolicy="no-referrer"
                class="w-14 h-14 rounded-2xl object-cover border-2 shadow-sm"
                :class="esPropio ? 'border-cyan-500' : 'border-purple-500'"
              />
            </div>
            <div class="flex-1 min-w-[240px]">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Foto de Perfil (URL Extraída)
              </label>
              <div class="relative">
                <input
                  v-model="formRed.foto_perfil_url"
                  type="url"
                  placeholder="https://..."
                  class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100"
                />
                <ImageIcon class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
              </div>
            </div>
          </div>

          <!-- Tabla Única de Métricas del Punto Cero -->
          <div
            class="grid gap-3 font-mono pt-1"
            :class="currentRed.key === 'facebook' ? 'grid-cols-1 sm:grid-cols-3' : (currentRed.key === 'tiktok' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-2 sm:grid-cols-4')"
          >
            <!-- Seguidores / Suscriptores / Contactos -->
            <div
              class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 text-center space-y-1"
              :class="esPropio ? 'border-cyan-500/40' : 'border-purple-500/40'"
            >
              <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                👥 {{ currentRed.key === 'youtube' ? 'Suscriptores Iniciales' : (currentRed.key === 'linkedin' ? 'Contactos Iniciales' : 'Seguidores Iniciales') }}
              </span>
              <input
                v-model.number="formRed.seguidores_actuales"
                type="number"
                min="0"
                placeholder="ej. 50000"
                class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold"
                :class="esPropio ? 'text-cyan-600 dark:text-cyan-400' : 'text-purple-600 dark:text-purple-400'"
              />
              <span class="text-[9px] text-slate-400 block font-mono">Punto Alfa de Inicio</span>
            </div>

            <!-- Seguidos (Oculto en YouTube) -->
            <div
              v-if="currentRed.key !== 'youtube'"
              class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1"
            >
              <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                🔄 Seguidos
              </span>
              <input
                v-model.number="formRed.seguidos_actuales"
                type="number"
                min="0"
                placeholder="ej. 300"
                class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-slate-800 dark:text-slate-200"
              />
              <span class="text-[9px] text-slate-400 block font-mono">Cuentas seguidas</span>
            </div>

            <!-- Me Gusta Iniciales (Específico TikTok) -->
            <div
              v-if="currentRed.key === 'tiktok'"
              class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-rose-500/40 text-center space-y-1"
            >
              <span class="text-[10px] uppercase tracking-wider text-rose-500 font-bold block">
                ❤️ Me Gusta Iniciales
              </span>
              <input
                v-model.number="formRed.me_gusta_totales"
                type="number"
                min="0"
                placeholder="ej. 7063"
                class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-rose-600 dark:text-rose-400"
              />
              <span class="text-[9px] text-slate-400 block font-mono">Likes acumulados</span>
            </div>

            <!-- Publicaciones / Videos Totales (Oculto en Facebook porque no aplica) -->
            <div
              v-if="currentRed.key !== 'facebook'"
              class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1"
            >
              <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                {{ currentRed.key === 'tiktok' || currentRed.key === 'youtube' ? '🎬 Videos' : (currentRed.key === 'linkedin' ? '📝 Posts / Artículos' : '📄 Publicaciones Totales') }}
              </span>
              <input
                v-model.number="formRed.publicaciones_totales"
                type="number"
                min="0"
                placeholder="ej. 250"
                class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-slate-800 dark:text-slate-200"
              />
              <span class="text-[9px] text-slate-400 block font-mono">Posts al comenzar</span>
            </div>

            <!-- Visualizaciones Iniciales (Específico YouTube) -->
            <div
              v-if="currentRed.key === 'youtube'"
              class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-red-500/40 text-center space-y-1"
            >
              <span class="text-[10px] uppercase tracking-wider text-red-500 font-bold block">
                👁️ Visualizaciones Iniciales
              </span>
              <input
                v-model.number="formRed.visualizaciones_totales"
                type="number"
                min="0"
                placeholder="ej. 6210"
                class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-red-600 dark:text-red-400"
              />
              <span class="text-[9px] text-slate-400 block font-mono">Vistas totales canal</span>
            </div>

            <!-- Fecha Punto Cero -->
            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1">
              <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                📅 Fecha de Comienzo
              </span>
              <input
                v-model="formRed.fecha_punto_cero"
                type="date"
                class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200"
              />
              <span class="text-[9px] text-slate-400 block font-mono">Línea de partida</span>
            </div>
          </div>
        </div>

        <!-- Submit Button & Cancel -->
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="formRed.processing"
            class="px-6 py-2.5 rounded-2xl text-white font-extrabold text-xs shadow-md flex items-center gap-2 cursor-pointer transition-all hover:scale-102"
            :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500 shadow-cyan-600/25' : 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/25'"
          >
            <Save class="w-4 h-4" />
            <span>{{ formRed.processing ? 'Guardando...' : `Guardar y Establecer Punto Cero de ${currentRed.nombre}` }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
