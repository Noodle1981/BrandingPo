<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
  Edit3,
  MapPin,
  X,
  Save,
  Users
} from '@lucide/vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  candidato: {
    type: Object,
    required: true,
  },
  ciclos: {
    type: Array,
    default: () => [],
  },
  territorios: {
    type: Array,
    default: () => [],
  },
  tema: {
    type: String,
    default: 'propio', // 'propio' | 'rival'
  },
});

const emit = defineEmits(['close']);

const esPropio = computed(() => props.tema === 'propio' || props.candidato?.es_propio);

const formCandidato = useForm({
  workspace_nombre: '',
  nombre_completo: props.candidato.nombre_completo || '',
  partido_coalicion: props.candidato.partido_coalicion || '',
  cargo_aspirado: props.candidato.cargo_aspirado || '',
  estado_politico: props.candidato.estado_politico || (esPropio.value ? 'candidato' : 'opositor'),
  ciclo_campana_id: props.candidato.ciclo_campana_id || props.ciclos[0]?.id || '',
  territorio_id: props.candidato.territorio_id || props.candidato.territorio?.id || '',
  territorio_nombre: props.candidato.territorio?.nombre || '',
  padron_electoral: props.candidato.territorio?.padron_electoral || props.candidato.padron_electoral || 0,
  poblacion_total: props.candidato.territorio?.poblacion_total || props.candidato.poblacion_total || 0,
  tipo_territorio: props.candidato.territorio?.tipo || 'municipio',
  color_hex: props.candidato.color_hex || (esPropio.value ? '#06b6d4' : '#8b5cf6'),
  avatar_url: props.candidato.avatar_url || '',
  bio_resumen: props.candidato.bio_resumen || '',
});

const saveCandidato = () => {
  formCandidato.put(`/candidatos/${props.candidato.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    },
  });
};
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
  >
    <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
        <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <Edit3 class="w-5 h-5" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
          <span>{{ esPropio ? 'Editar Datos del Candidato Oficial' : 'Editar Datos del Rival' }}</span>
        </h3>
        <button
          type="button"
          @click="emit('close')"
          class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <form @submit.prevent="saveCandidato" class="space-y-4">
        <!-- Nombre Completo -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre Completo *</label>
          <input
            v-model="formCandidato.nombre_completo"
            type="text"
            required
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2"
            :class="esPropio ? 'focus:ring-cyan-500' : 'focus:ring-purple-500'"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Partido / Coalición *</label>
            <input
              v-model="formCandidato.partido_coalicion"
              type="text"
              required
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2"
              :class="esPropio ? 'focus:ring-cyan-500' : 'focus:ring-purple-500'"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cargo al que aspira</label>
            <input
              v-model="formCandidato.cargo_aspirado"
              type="text"
              placeholder="ej. Intendente / Concejal"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2"
              :class="esPropio ? 'focus:ring-cyan-500' : 'focus:ring-purple-500'"
            />
          </div>
        </div>

        <!-- Ciclo y Estado Político -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ciclo Electoral</label>
            <select
              v-model="formCandidato.ciclo_campana_id"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100"
            >
              <option v-for="ciclo in ciclos" :key="ciclo.id" :value="ciclo.id">
                {{ ciclo.nombre }} ({{ ciclo.anio }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Estado Político</label>
            <select
              v-model="formCandidato.estado_politico"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100"
            >
              <option value="candidato">Candidato Oficial</option>
              <option value="precandidato">Precandidato (Interna)</option>
              <option value="opositor">Opositor / Rival</option>
              <option value="intendente_electo">Intendente Electo</option>
              <option value="gobernador_electo">Gobernador Electo</option>
              <option value="en_funciones">En Funciones</option>
              <option value="inactivo">Inactivo</option>
            </select>
          </div>
        </div>

        <!-- DATOS GEOGRÁFICOS Y ELECTORALES -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-1.5">
              <MapPin class="w-4 h-4" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
              <span>Territorio Geográfico & Padrón Electoral</span>
            </span>
            <span class="text-[10px] font-mono uppercase font-bold" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'">
              Base Territorial
            </span>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Nombre del Territorio / Municipio / Departamento *
            </label>
            <input
              v-model="formCandidato.territorio_nombre"
              type="text"
              required
              placeholder="ej. Municipio Rawson / Albardón"
              class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2"
              :class="esPropio ? 'focus:ring-cyan-500' : 'focus:ring-purple-500'"
            />
          </div>

          <div class="grid grid-cols-2 gap-3 font-mono">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Padrón Electoral (Votantes) *
              </label>
              <input
                v-model.number="formCandidato.padron_electoral"
                type="number"
                min="0"
                placeholder="ej. 24500"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-extrabold"
                :class="esPropio ? 'text-cyan-500' : 'text-purple-500'"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Población Total Estimada
              </label>
              <input
                v-model.number="formCandidato.poblacion_total"
                type="number"
                min="0"
                placeholder="ej. 31000"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100"
              />
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Foto / Avatar URL</label>
          <input
            v-model="formCandidato.avatar_url"
            type="url"
            placeholder="https://..."
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2"
            :class="esPropio ? 'focus:ring-cyan-500' : 'focus:ring-purple-500'"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Biografía / Perfil de Campaña</label>
          <textarea
            v-model="formCandidato.bio_resumen"
            rows="3"
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2"
            :class="esPropio ? 'focus:ring-cyan-500' : 'focus:ring-purple-500'"
          ></textarea>
        </div>

        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="formCandidato.processing"
            class="px-5 py-2 rounded-xl text-white text-xs font-bold shadow-sm transition-all hover:scale-102 cursor-pointer flex items-center gap-1.5"
            :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-purple-600 hover:bg-purple-500'"
          >
            <Save class="w-4 h-4" />
            <span>{{ formCandidato.processing ? 'Guardando...' : 'Guardar Cambios' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
