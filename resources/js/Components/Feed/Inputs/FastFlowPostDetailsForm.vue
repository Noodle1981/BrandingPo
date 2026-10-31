<script setup>
import {
  RefreshCw,
  CheckCircle2,
  AlertCircle,
} from '@lucide/vue';
import FastFlowLastPostInfo from './FastFlowLastPostInfo.vue';
import FastFlowDuplicateAlert from './FastFlowDuplicateAlert.vue';
import FastFlowAdBanner from './FastFlowAdBanner.vue';
import FastFlowPautaSection from './FastFlowPautaSection.vue';

const props = defineProps({
  createForm: {
    type: Object,
    required: true,
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  perfilesCandidatoSeleccionado: {
    type: Array,
    default: () => [],
  },
  groupedEjes: {
    type: Object,
    default: () => ({}),
  },
  ultimaPublicacionRegistrada: {
    type: Object,
    default: null,
  },
  duplicateAlert: {
    type: Object,
    default: null,
  },
  platformMeta: {
    type: Object,
    required: true,
  },
  isScraping: {
    type: Boolean,
    default: false,
  },
  scrapeSuccessMsg: {
    type: String,
    default: '',
  },
  scrapeErrorMsg: {
    type: String,
    default: '',
  },
  adDetectionAlert: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits([
  'candidatoChange',
  'perfilChange',
  'autocompletarScrape',
  'aplicarPautaDetectada',
  'normalizarUrl',
]);
</script>

<template>
  <div class="space-y-4">
    <!-- Fila 1: Candidato y Red Social -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <div class="space-y-1">
        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
          Candidato <span class="text-rose-500">*</span>
        </label>
        <select
          v-model="createForm.candidato_id"
          @change="emit('candidatoChange')"
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
          @change="emit('perfilChange')"
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
          @paste.prevent="emit('normalizarUrl', $event)"
        />
        <button
          type="button"
          @click="emit('autocompletarScrape')"
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

      <FastFlowAdBanner
        :ad-detection-alert="adDetectionAlert"
        :tipo-pauta="createForm.tipo_pauta"
        @aplicar-pauta="emit('aplicarPautaDetectada')"
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
</template>
