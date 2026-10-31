<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
  Sparkles,
  X,
  AlertCircle,
  RefreshCw,
  CheckCircle2,
  DollarSign
} from '@lucide/vue';
import FeedPlatformMetricsForm from './Inputs/FeedPlatformMetricsForm.vue';
import FastFlowAdBanner from './Inputs/FastFlowAdBanner.vue';
import FastFlowDuplicateAlert from './Inputs/FastFlowDuplicateAlert.vue';
import FastFlowLastPostInfo from './Inputs/FastFlowLastPostInfo.vue';
import FastFlowMediaPreview from './Inputs/FastFlowMediaPreview.vue';
import FastFlowPautaSection from './Inputs/FastFlowPautaSection.vue';
import { useFastFlowScraper } from '../../composables/useFastFlowScraper';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  candidatos: {
    type: Array,
    default: () => []
  },
  groupedEjes: {
    type: Object,
    default: () => ({})
  },
  publicaciones: {
    type: Array,
    default: () => []
  },
  initialCandidatoId: {
    type: [Number, String],
    default: ''
  },
  initialPlataforma: {
    type: String,
    default: ''
  },
  initialEjeId: {
    type: [Number, String],
    default: ''
  },
  initialTipoPauta: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue', 'created']);

const createForm = useForm({
  candidato_id: '',
  perfil_social_id: '',
  plataforma: 'instagram',
  eje_tematico_id: '',
  url_post: '',
  media_url: '',
  tipo_formato: 'Reel',
  tipo_pauta: 'organico',
  monto_invertido_pauta: 0,
  fecha_publicacion: '',
  vistas_organicas: 0,
  vistas_pagadas: 0,
  total_likes: 0,
  total_comentarios: 0,
  total_compartidos: 0,
  total_republicados: 0,
  total_guardados: 0,
  contenido_resumen: '',
  comentario_destacado: '',
  figura_acompanante: '',
  reacciones_detalladas: {
    like: 0,
    love: 0,
    care: 0,
    haha: 0,
    wow: 0,
    sad: 0,
    angry: 0,
  },
});

const perfilesCandidatoSeleccionado = computed(() => {
  const c = props.candidatos.find(cand => cand.id === Number(createForm.candidato_id));
  return c?.perfiles_sociales || c?.perfilesSociales || [];
});

const {
  isScraping,
  scrapeSuccessMsg,
  scrapeErrorMsg,
  adDetectionAlert,
  normalizarUrlPegada,
  platformMeta,
  duplicateAlert,
  ultimaPublicacionRegistrada,
  autocompletarScrape,
  aplicarPautaDetectada,
  limpiarAlertas,
} = useFastFlowScraper({
  createForm,
  props,
  perfilesCandidatoSeleccionado
});

const initForm = () => {
  limpiarAlertas();

  // 1. Candidato
  if (props.initialCandidatoId) {
    createForm.candidato_id = props.initialCandidatoId;
  } else {
    const propio = props.candidatos.find(c => c.es_propio);
    createForm.candidato_id = propio ? propio.id : (props.candidatos[0]?.id ?? '');
  }

  // 2. Perfil social según plataforma inicial
  const cand = props.candidatos.find(c => c.id === Number(createForm.candidato_id));
  const perfiles = cand?.perfiles_sociales || cand?.perfilesSociales || [];
  const activePlatform = props.initialPlataforma;

  if (activePlatform) {
    const matchingPerfil = perfiles.find(p => p.plataforma.toLowerCase() === activePlatform.toLowerCase());
    if (matchingPerfil) {
      createForm.perfil_social_id = matchingPerfil.id;
      createForm.plataforma = matchingPerfil.plataforma;
    } else if (perfiles.length > 0) {
      createForm.perfil_social_id = perfiles[0].id;
      createForm.plataforma = perfiles[0].plataforma;
    } else {
      createForm.plataforma = activePlatform.toLowerCase();
    }
  } else if (perfiles.length > 0) {
    createForm.perfil_social_id = perfiles[0].id;
    createForm.plataforma = perfiles[0].plataforma;
  }

  // 3. Eje inicial
  if (props.initialEjeId) {
    createForm.eje_tematico_id = props.initialEjeId;
  }

  // 4. Tipo pauta inicial
  if (props.initialTipoPauta) {
    createForm.tipo_pauta = props.initialTipoPauta;
  }
};

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    initForm();
  }
});

const onCandidatoChange = () => {
  const cand = props.candidatos.find(c => c.id === Number(createForm.candidato_id));
  const perfiles = cand?.perfiles_sociales || cand?.perfilesSociales || [];
  const activePlatform = props.initialPlataforma;
  
  if (activePlatform) {
    const matchingPerfil = perfiles.find(p => p.plataforma.toLowerCase() === activePlatform.toLowerCase());
    if (matchingPerfil) {
      createForm.perfil_social_id = matchingPerfil.id;
      createForm.plataforma = matchingPerfil.plataforma;
      return;
    }
  }

  if (perfiles.length > 0) {
    createForm.perfil_social_id = perfiles[0].id;
    createForm.plataforma = perfiles[0].plataforma;
  } else {
    createForm.perfil_social_id = '';
  }
};

const onPerfilChange = () => {
  const perfiles = perfilesCandidatoSeleccionado.value;
  const p = perfiles.find(item => item.id === Number(createForm.perfil_social_id));
  if (p) {
    createForm.plataforma = p.plataforma;
  }
};

watch(() => createForm.reacciones_detalladas, (reacs) => {
  if (createForm.plataforma === 'facebook') {
    const sum = Object.values(reacs).reduce((acc, val) => acc + (Number(val) || 0), 0);
    if (sum > 0) {
      createForm.total_likes = sum;
    }
  }
}, { deep: true });

const netApproval = computed(() => {
  if (createForm.plataforma !== 'facebook') {
    return 100;
  }
  const r = createForm.reacciones_detalladas;
  const pos = (Number(r.like) || 0) + (Number(r.love) || 0) + (Number(r.care) || 0) + (Number(r.haha) || 0) + (Number(r.wow) || 0);
  const neg = (Number(r.sad) || 0) + (Number(r.angry) || 0);
  const tot = pos + neg;
  if (tot === 0) return 100;
  return Math.round(((pos - neg) / tot) * 100);
});

const submitCreatePost = () => {
  createForm.clearErrors();

  const payload = {
    ...createForm.data(),
    candidato_id: createForm.candidato_id ? Number(createForm.candidato_id) : null,
    perfil_social_id: createForm.perfil_social_id ? Number(createForm.perfil_social_id) : null,
    eje_tematico_id: createForm.eje_tematico_id ? Number(createForm.eje_tematico_id) : null,
    total_likes: Number(createForm.total_likes || 0),
    total_comentarios: Number(createForm.total_comentarios || 0),
    total_compartidos: Number(createForm.total_compartidos || 0),
    total_republicados: Number(createForm.total_republicados || 0),
    total_guardados: Number(createForm.total_guardados || 0),
    vistas_organicas: Number(createForm.vistas_organicas || 0),
    vistas_pagadas: Number(createForm.vistas_pagadas || 0),
    monto_invertido_pauta: Number(createForm.monto_invertido_pauta || 0),
    me_gusta: createForm.plataforma === 'facebook' 
      ? Number(createForm.reacciones_detalladas.like || createForm.total_likes || 0)
      : Number(createForm.total_likes || 0),
    me_encanta: Number(createForm.reacciones_detalladas.love || 0),
    me_importa: Number(createForm.reacciones_detalladas.care || 0),
    me_divierte: Number(createForm.reacciones_detalladas.haha || 0),
    me_asombra: Number(createForm.reacciones_detalladas.wow || 0),
    me_entristece: Number(createForm.reacciones_detalladas.sad || 0),
    me_enoja: Number(createForm.reacciones_detalladas.angry || 0),
  };

  createForm.transform(() => payload).post('/publicaciones', {
    preserveScroll: true,
    onSuccess: () => {
      emit('update:modelValue', false);
      emit('created');
      createForm.reset();
    },
    onError: (errors) => {
      console.warn('Errores de validación al guardar publicación:', errors);
    }
  });
};
</script>

<template>
  <!-- MODAL DE CARGA INTELIGENTE DE PUBLICACIÓN (+ AUTOCOMPLETAR 1 CLIC) -->
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-md overflow-y-auto animate-fade-in"
    @click.self="emit('update:modelValue', false)"
  >
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-4xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden my-auto"
    >
      <!-- Modal Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0 bg-slate-50/50 dark:bg-slate-950/50">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center">
            <Sparkles class="w-5 h-5" />
          </div>
          <div>
            <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span>Carga Rápida de Publicación (Fast-Flow)</span>
              <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold border border-cyan-500/20">
                Lector de Redes 1 Clic
              </span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Pega el enlace de Instagram, Facebook o TikTok y el sistema extraerá automáticamente texto, imagen y métricas.
            </p>
          </div>
        </div>
        <button
          @click="emit('update:modelValue', false)"
          class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Modal Form -->
      <form @submit.prevent="submitCreatePost" class="flex flex-col flex-1 min-h-0 overflow-hidden">
        
        <!-- Scrollable Body Container -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
          
          <!-- Banner de Errores de Validación -->
          <div
            v-if="Object.keys(createForm.errors).length > 0"
            class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs flex items-start gap-2.5"
          >
            <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
            <div>
              <div class="font-bold">Por favor revisa los campos requeridos:</div>
              <ul class="list-disc list-inside mt-1 space-y-0.5 text-[11px]">
                <li v-for="(err, field) in createForm.errors" :key="field">
                  {{ err }}
                </li>
              </ul>
            </div>
          </div>

          <!-- Grilla 2 Columnas -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- COLUMNA IZQUIERDA (7 cols) -->
            <div class="lg:col-span-7 space-y-4">
              
              <!-- Fila 1: Candidato y Red Social -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Candidato <span class="text-rose-500">*</span>
                  </label>
                  <select
                    v-model="createForm.candidato_id"
                    @change="onCandidatoChange"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold focus:ring-2 focus:ring-cyan-500"
                    :class="createForm.errors.candidato_id ? 'border-rose-500 ring-1 ring-rose-500' : ''"
                  >
                    <option value="" disabled>Seleccionar candidato...</option>
                    <option v-for="cand in candidatos" :key="cand.id" :value="cand.id">
                      {{ cand.nombre_completo }} ({{ cand.es_propio ? 'Propio' : 'Rival' }})
                    </option>
                  </select>
                  <span v-if="createForm.errors.candidato_id" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.candidato_id }}</span>
                </div>

                <div class="space-y-1">
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Canal / Red Social <span class="text-rose-500">*</span>
                  </label>
                  <select
                    v-model="createForm.perfil_social_id"
                    @change="onPerfilChange"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold focus:ring-2 focus:ring-cyan-500 capitalize"
                    :class="createForm.errors.perfil_social_id ? 'border-rose-500 ring-1 ring-rose-500' : ''"
                  >
                    <option value="" disabled>Seleccionar canal...</option>
                    <option v-for="perfil in perfilesCandidatoSeleccionado" :key="perfil.id" :value="perfil.id">
                      {{ perfil.plataforma }} ({{ perfil.handle_usuario || 'Sin handle' }})
                    </option>
                  </select>
                  <span v-if="createForm.errors.perfil_social_id" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.perfil_social_id }}</span>
                </div>
              </div>

              <!-- Tarjeta Auxiliar: Última publicación -->
              <FastFlowLastPostInfo
                :ultima-publicacion-registrada="ultimaPublicacionRegistrada"
                :plataforma="createForm.plataforma"
              />

              <!-- Alerta Inmediata si se detecta URL duplicada -->
              <FastFlowDuplicateAlert
                :duplicate-alert="duplicateAlert"
              />

              <!-- Fila 2: URL del Post + Botón Scraper 1 Clic -->
              <div class="p-3.5 rounded-2xl bg-cyan-500/5 dark:bg-cyan-500/10 border border-cyan-500/20 space-y-2">
                <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center justify-between">
                  <span>{{ platformMeta.label }}</span>
                  <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400 font-bold uppercase tracking-wider">{{ platformMeta.nombre }}</span>
                </label>
                <div class="flex items-center gap-2">
                  <input
                    v-model="createForm.url_post"
                    type="text"
                    :placeholder="platformMeta.placeholder"
                    class="flex-1 px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs focus:ring-2 focus:ring-cyan-500 font-mono"
                    :class="(createForm.errors.url_post || duplicateAlert) ? 'border-amber-500 ring-1 ring-amber-500' : ''"
                    @paste.prevent="(e) => {
                      const raw = (e.clipboardData || window.clipboardData).getData('text');
                      createForm.url_post = normalizarUrlPegada(raw);
                    }"
                  />
                  <button
                    type="button"
                    @click="autocompletarScrape"
                    :disabled="isScraping"
                    class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-xs hover:scale-102 disabled:opacity-50 cursor-pointer shrink-0"
                  >
                    <RefreshCw class="w-3.5 h-3.5" :class="isScraping ? 'animate-spin' : ''" />
                    <span>{{ isScraping ? 'Leyendo...' : '⚡ Autocompletar (1 Clic)' }}</span>
                  </button>
                </div>

                <div v-if="scrapeSuccessMsg" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                  <CheckCircle2 class="w-3.5 h-3.5 shrink-0" />
                  <span>{{ scrapeSuccessMsg }}</span>
                </div>

                <!-- Banner Interactivo: Detección Temprana de Pauta -->
                <FastFlowAdBanner
                  :ad-detection-alert="adDetectionAlert"
                  :tipo-pauta="createForm.tipo_pauta"
                  @aplicar-pauta="aplicarPautaDetectada"
                />

                <div v-if="scrapeErrorMsg" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                  <AlertCircle class="w-3.5 h-3.5 shrink-0" />
                  <span>{{ scrapeErrorMsg }}</span>
                </div>
                <div v-if="createForm.errors.url_post" class="text-[11px] font-bold text-rose-500 flex items-center gap-1">
                  <AlertCircle class="w-3.5 h-3.5 shrink-0" />
                  <span>{{ createForm.errors.url_post }}</span>
                </div>
              </div>

              <!-- Fila 3: Eje Temático, Formato y Fecha -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="space-y-1">
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Eje Temático
                  </label>
                  <select
                    v-model="createForm.eje_tematico_id"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold focus:ring-2 focus:ring-cyan-500"
                  >
                    <option value="">Sin eje asignado</option>
                    <optgroup v-for="(ejes, cat) in groupedEjes" :key="cat" :label="cat">
                      <option v-for="eje in ejes" :key="eje.id" :value="eje.id">
                        {{ eje.nombre }}
                      </option>
                    </optgroup>
                  </select>
                </div>

                <div class="space-y-1">
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Formato <span class="text-rose-500">*</span>
                  </label>
                  <select
                    v-model="createForm.tipo_formato"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold focus:ring-2 focus:ring-cyan-500"
                  >
                    <option value="Reel">Reel / Video Vertical</option>
                    <option value="Video">Video Horizontal</option>
                    <option value="Foto">Foto Única</option>
                    <option value="Carrusel">Carrusel / Álbum</option>
                    <option value="Historia">Historia / Temporal</option>
                    <option value="Texto">Texto / Hilo</option>
                    <option value="Short">Short</option>
                  </select>
                </div>

                <div class="space-y-1">
                  <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Fecha y Hora <span class="text-rose-500">*</span>
                  </label>
                  <input
                    v-model="createForm.fecha_publicacion"
                    type="datetime-local"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold font-mono focus:ring-2 focus:ring-cyan-500"
                    :class="createForm.errors.fecha_publicacion ? 'border-rose-500 ring-1 ring-rose-500' : ''"
                  />
                  <span v-if="createForm.errors.fecha_publicacion" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.fecha_publicacion }}</span>
                </div>
              </div>

              <!-- Fila 4: Tipo de Pauta y Monto Invertido DELEGADA -->
              <FastFlowPautaSection
                :form="createForm"
              />

              <!-- Fila 5: Resumen del Contenido / Copy -->
              <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                  <span>Texto / Copy de la Publicación <span class="text-rose-500">*</span></span>
                  <span class="text-[10px] font-mono text-slate-400">{{ createForm.contenido_resumen.length }} caracteres</span>
                </label>
                <textarea
                  v-model="createForm.contenido_resumen"
                  rows="3"
                  placeholder="Pega o escribe el contenido de la publicación..."
                  class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs focus:ring-2 focus:ring-cyan-500"
                  :class="createForm.errors.contenido_resumen ? 'border-rose-500 ring-1 ring-rose-500' : ''"
                ></textarea>
                <span v-if="createForm.errors.contenido_resumen" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.contenido_resumen }}</span>
              </div>

            </div>

            <!-- COLUMNA DERECHA (5 cols) -->
            <div class="lg:col-span-5 space-y-3.5">
              
              <!-- Preview de Imagen / Portada -->
              <FastFlowMediaPreview
                v-model="createForm.media_url"
              />

              <!-- Formularios de Métricas por Plataforma (Instagram, Facebook, TikTok, Threads, Twitter, etc.) -->
              <FeedPlatformMetricsForm
                :form="createForm"
                :net-approval="netApproval"
              />
            </div>

          </div>

        </div>

        <!-- Sticky Footer -->
        <div class="flex items-center justify-between px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-950/95 backdrop-blur-md shrink-0 sticky bottom-0 z-20">
          <button
            type="button"
            @click="emit('update:modelValue', false)"
            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
          >
            Cancelar
          </button>

          <div class="flex items-center gap-3">
            <span v-if="createForm.processing" class="text-xs text-slate-400 font-mono animate-pulse">
              Guardando publicación...
            </span>

            <button
              type="submit"
              :disabled="createForm.processing"
              class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs flex items-center gap-2 transition-all shadow-md hover:scale-102 disabled:opacity-50 cursor-pointer"
            >
              <Sparkles class="w-4 h-4" />
              <span>Guardar en el Feed</span>
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</template>
