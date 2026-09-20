<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { 
  X, 
  Sparkles, 
  Globe, 
  Rss, 
  Image, 
  Loader2, 
  Check, 
  AlertCircle 
} from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  medioEditar: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close']);

const form = useForm({
  nombre: '',
  tipo_medio: 'digital',
  url_sitio: '',
  url_facebook: '',
  avatar_url: '',
  feed_rss_url: '',
  alcance_tipo: 'provincial',
  sesgo_editorial_estimado: 'independiente',
});

// Autodetección state
const detectando = ref(false);
const mensajeDeteccion = ref('');
const errorDeteccion = ref('');

const limpiarFormulario = () => {
  form.reset();
  form.nombre = '';
  form.tipo_medio = 'digital';
  form.url_sitio = '';
  form.url_facebook = '';
  form.avatar_url = '';
  form.feed_rss_url = '';
  form.alcance_tipo = 'provincial';
  form.sesgo_editorial_estimado = 'independiente';
  form.clearErrors();
  mensajeDeteccion.value = '';
  errorDeteccion.value = '';
};

const handleClose = () => {
  limpiarFormulario();
  emit('close');
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.isOpen) {
    handleClose();
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    window.addEventListener('keydown', handleKeyDown);
    mensajeDeteccion.value = '';
    errorDeteccion.value = '';
    form.clearErrors();

    if (props.medioEditar) {
      form.nombre = props.medioEditar.nombre || '';
      form.tipo_medio = props.medioEditar.tipo_medio || 'digital';
      form.url_sitio = props.medioEditar.url_sitio || '';
      form.url_facebook = props.medioEditar.url_facebook || '';
      form.avatar_url = props.medioEditar.avatar_url || '';
      form.feed_rss_url = props.medioEditar.feed_rss_url || '';
      form.alcance_tipo = props.medioEditar.alcance_tipo || 'provincial';
      form.sesgo_editorial_estimado = props.medioEditar.sesgo_editorial_estimado || 'independiente';
    } else {
      limpiarFormulario();
    }
  } else {
    window.removeEventListener('keydown', handleKeyDown);
    limpiarFormulario();
  }
});

const autodescubrirFuentes = async () => {
  if (!form.url_sitio && !form.url_facebook) {
    errorDeteccion.value = 'Ingresa al menos la URL del portal web o de Facebook para analizar.';
    return;
  }

  detectando.value = true;
  mensajeDeteccion.value = '';
  errorDeteccion.value = '';

  try {
    const res = await axios.post('/medios/detectar-fuentes', {
      url_sitio: form.url_sitio || null,
      url_facebook: form.url_facebook || null,
    });

    const data = res.data;
    if (data.success) {
      if (data.feed_rss_url && !form.feed_rss_url) {
        form.feed_rss_url = data.feed_rss_url;
      }
      if (data.avatar_url && !form.avatar_url) {
        form.avatar_url = data.avatar_url;
      }
      if (data.titulo_sitio && !form.nombre) {
        form.nombre = data.titulo_sitio;
      }
      mensajeDeteccion.value = data.mensaje || '¡Fuentes y avatar detectados exitosamente!';
    } else {
      errorDeteccion.value = data.mensaje || 'No se pudieron detectar fuentes automáticamente.';
    }
  } catch (err) {
    errorDeteccion.value = err.response?.data?.mensaje || 'Error al conectar con el servicio de autodescubrimiento.';
  } finally {
    detectando.value = false;
  }
};

const intentarAutodescubrir = () => {
  if (detectando.value) return;
  if ((form.url_sitio || form.url_facebook) && (!form.feed_rss_url || !form.avatar_url || !form.nombre)) {
    autodescubrirFuentes();
  }
};

const submit = () => {
  if (props.medioEditar) {
    form.put(`/medios/${props.medioEditar.id}`, {
      onSuccess: () => handleClose(),
    });
  } else {
    form.post('/medios', {
      onSuccess: () => handleClose(),
    });
  }
};
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop interactivo que cierra al hacer clic afuera -->
    <div 
      class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity" 
      @click="handleClose"
    />

    <!-- Contenedor con centrado seguro que evita trabarse arriba (items-start sm:items-center) -->
    <div class="flex min-h-full items-start sm:items-center justify-center p-3 sm:p-4 text-center">
      <!-- Tarjeta del Modal con altura contenida y flex-col -->
      <div 
        class="relative w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl text-left overflow-hidden flex flex-col max-h-[90vh] my-4 sm:my-8 transform transition-all"
        @click.stop
      >
        <!-- Modal Header Fijo -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900 z-10">
          <div>
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100">
              {{ medioEditar ? 'Editar Medio de Prensa' : 'Registrar Nuevo Medio' }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Configura las fuentes web y de redes para auditar coberturas periodísticas.
            </p>
          </div>
          <button
            type="button"
            @click="handleClose"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form con Body scrolleable y Footer fijo -->
        <form @submit.prevent="submit" class="flex flex-col overflow-hidden flex-1">
          <!-- Body con scroll interno suave -->
          <div class="p-6 overflow-y-auto space-y-4 flex-1 overscroll-contain">
        <!-- URLs Row with Autodiscover button -->
        <div class="p-4 rounded-2xl bg-cyan-500/5 dark:bg-cyan-500/10 border border-cyan-500/20 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400 flex items-center gap-1.5 font-mono uppercase tracking-wider">
              <Sparkles class="w-3.5 h-3.5" />
              <span>Autodescubrimiento Inteligente</span>
            </span>

            <button
              type="button"
              @click="autodescubrirFuentes"
              :disabled="detectando || (!form.url_sitio && !form.url_facebook)"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs transition-all shadow-xs disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
            >
              <Loader2 v-if="detectando" class="w-3.5 h-3.5 animate-spin" />
              <Sparkles v-else class="w-3.5 h-3.5" />
              <span>{{ detectando ? 'Analizando...' : 'Detectar RSS & Avatar' }}</span>
            </button>
          </div>

          <!-- URL Sitio Web Oficial -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              URL Portal Web Oficial:
            </label>
            <div class="relative">
              <Globe class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
              <input
                v-model="form.url_sitio"
                @blur="intentarAutodescubrir"
                type="url"
                placeholder="https://www.diariodecuyo.com.ar"
                class="w-full pl-9 pr-3 py-2 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
            <p v-if="form.errors.url_sitio" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.url_sitio }}</p>
          </div>

          <!-- URL Facebook Fanpage -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              URL Fanpage de Facebook (Para Avatar y Sentimientos):
            </label>
            <div class="relative">
              <span class="absolute left-3 top-2.5">
                <SocialPlatformIcon platform="facebook" size="xs" />
              </span>
              <input
                v-model="form.url_facebook"
                @blur="intentarAutodescubrir"
                type="url"
                placeholder="https://www.facebook.com/diariodecuyo"
                class="w-full pl-9 pr-3 py-2 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
            <p v-if="form.errors.url_facebook" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.url_facebook }}</p>
          </div>

          <!-- Feedback de Autodescubrimiento -->
          <div v-if="mensajeDeteccion" class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
            <Check class="w-4 h-4 shrink-0" />
            <span>{{ mensajeDeteccion }}</span>
          </div>
          <div v-if="errorDeteccion" class="flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400 font-medium">
            <AlertCircle class="w-4 h-4 shrink-0" />
            <span>{{ errorDeteccion }}</span>
          </div>
        </div>

        <!-- Nombre del Medio -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Nombre del Medio de Prensa: *
          </label>
          <input
            v-model="form.nombre"
            type="text"
            required
            placeholder="Ej: Diario de Cuyo, Tiempo de San Juan, Radio Sarmiento"
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-bold"
          />
          <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.nombre }}</p>
        </div>

        <!-- Tipo de Medio & Alcance -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Tipo de Medio:
            </label>
            <select
              v-model="form.tipo_medio"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
            >
              <option value="digital">Digital (Portal Web / Noticias)</option>
              <option value="radio">Radio</option>
              <option value="tv">Televisión</option>
              <option value="impreso">Impreso (Diario / Semanario)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Alcance Geográfico:
            </label>
            <select
              v-model="form.alcance_tipo"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
            >
              <option value="local">Local / Departamental</option>
              <option value="provincial">Provincial</option>
              <option value="nacional">Nacional</option>
            </select>
          </div>
        </div>

        <!-- Sesgo Editorial Estimado -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
              Sesgo Editorial Estimado (Hipótesis Inicial):
            </label>
            <span class="text-[10px] text-slate-400">
              El análisis empírico lo calibra luego
            </span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button
              type="button"
              @click="form.sesgo_editorial_estimado = 'independiente'"
              class="py-2 px-2 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer"
              :class="form.sesgo_editorial_estimado === 'independiente' 
                ? 'bg-cyan-500/15 border-cyan-500 text-cyan-600 dark:text-cyan-400 ring-2 ring-cyan-500/20' 
                : 'border-slate-200 dark:border-slate-800 text-slate-500'"
            >
              Independiente
            </button>
            <button
              type="button"
              @click="form.sesgo_editorial_estimado = 'neutral'"
              class="py-2 px-2 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer"
              :class="form.sesgo_editorial_estimado === 'neutral' 
                ? 'bg-slate-200 dark:bg-slate-800 border-slate-400 text-slate-900 dark:text-slate-100 ring-2 ring-slate-400/20' 
                : 'border-slate-200 dark:border-slate-800 text-slate-500'"
            >
              Neutral
            </button>
            <button
              type="button"
              @click="form.sesgo_editorial_estimado = 'oficialista'"
              class="py-2 px-2 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer"
              :class="form.sesgo_editorial_estimado === 'oficialista' 
                ? 'bg-emerald-500/20 border-emerald-500 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500/20' 
                : 'border-slate-200 dark:border-slate-800 text-slate-500'"
            >
              Oficialista
            </button>
            <button
              type="button"
              @click="form.sesgo_editorial_estimado = 'opositor'"
              class="py-2 px-2 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer"
              :class="form.sesgo_editorial_estimado === 'opositor' 
                ? 'bg-rose-500/20 border-rose-500 text-rose-600 dark:text-rose-400 ring-2 ring-rose-500/20' 
                : 'border-slate-200 dark:border-slate-800 text-slate-500'"
            >
              Opositor
            </button>
          </div>
        </div>

        <!-- Advanced Fields: RSS & Avatar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          <!-- Feed RSS -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Canal RSS / Feed XML:
              </label>
              <span class="text-[10px] text-cyan-600 dark:text-cyan-400 font-medium">Autocompletable</span>
            </div>
            <div class="relative">
              <Rss class="w-3.5 h-3.5 text-amber-500 absolute left-3 top-2.5" />
              <input
                v-model="form.feed_rss_url"
                type="url"
                placeholder="Opcional: se autodetecta desde la web"
                class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-mono"
              />
            </div>
          </div>

          <!-- Avatar URL -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                Avatar / Logo URL:
              </label>
              <span class="text-[10px] text-cyan-600 dark:text-cyan-400 font-medium">Autocompletable</span>
            </div>
            <div class="relative">
              <Image class="w-3.5 h-3.5 text-cyan-500 absolute left-3 top-2.5" />
              <input
                v-model="form.avatar_url"
                type="text"
                placeholder="Opcional: se extrae del sitio o Facebook"
                class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-mono"
              />
            </div>
          </div>
        </div>

        <!-- Avatar Preview if present -->
        <div v-if="form.avatar_url" class="flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
          <img
            :src="form.avatar_url"
            alt="Avatar preview"
            class="w-10 h-10 rounded-xl object-cover border border-slate-300 dark:border-slate-700 shrink-0"
            referrerpolicy="no-referrer"
          />
          <div class="text-xs">
            <span class="font-bold text-slate-800 dark:text-slate-200 block">Avatar asignado al medio</span>
            <span class="text-slate-400 text-[11px] truncate block max-w-sm">{{ form.avatar_url }}</span>
          </div>
        </div>
      </div>

      <!-- Submit Button Fijo -->
      <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-800 shrink-0 bg-slate-50/90 dark:bg-slate-950/90 backdrop-blur-xs z-10">
          <button
            type="button"
            @click="handleClose"
            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs transition-all shadow-md shadow-cyan-500/20 disabled:opacity-50 cursor-pointer"
          >
            {{ form.processing ? 'Guardando...' : (medioEditar ? 'Actualizar Medio' : 'Guardar Medio') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
</template>
