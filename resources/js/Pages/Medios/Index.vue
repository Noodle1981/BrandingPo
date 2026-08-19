<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  Newspaper,
  Plus,
  Filter,
  ExternalLink,
  Trash2,
  X,
  Radio,
  Tv,
  FileText,
  AlertTriangle,
  CheckCircle2,
  MessageSquare
} from 'lucide-vue-next';

const props = defineProps({
  notas: {
    type: Array,
    default: () => [],
  },
  medios: {
    type: Array,
    default: () => [],
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  filtros: {
    type: Object,
    default: () => ({}),
  },
  resumen_tonos: {
    type: Object,
    default: () => ({}),
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const selectedCandidato = ref(props.filtros.candidato_id || '');
const selectedTono = ref(props.filtros.tono || '');
const selectedMedio = ref(props.filtros.medio_id || '');

const applyFilters = () => {
  router.get('/medios', {
    candidato_id: selectedCandidato.value || undefined,
    tono: selectedTono.value || undefined,
    medio_id: selectedMedio.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const filterByTono = (tonoKey) => {
  selectedTono.value = selectedTono.value === tonoKey ? '' : tonoKey;
  applyFilters();
};

const clearFilters = () => {
  selectedCandidato.value = '';
  selectedTono.value = '';
  selectedMedio.value = '';
  applyFilters();
};

// Modal Logic for Clipping
const isModalOpen = ref(false);
const form = useForm({
  medio_prensa_id: props.medios[0]?.id || '',
  candidato_id: props.candidatos[0]?.id || '',
  fecha_publicacion: new Date().toISOString().slice(0, 10),
  titulo: '',
  resumen: '',
  url_nota: '',
  tono_mencion: 'favorable',
  es_tapa_o_principal: false,
  interacciones_en_redes_del_medio: 0,
  respuesta_replica_candidato: '',
});

const openCreateModal = () => {
  form.reset();
  form.medio_prensa_id = props.medios[0]?.id || '';
  form.candidato_id = props.candidatos[0]?.id || '';
  form.fecha_publicacion = new Date().toISOString().slice(0, 10);
  form.clearErrors();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const submitNota = () => {
  form.post('/medios/clipping', {
    onSuccess: () => closeModal(),
  });
};

const deleteNota = (nota) => {
  if (confirm(`¿Eliminar la nota "${nota.titulo}"?`)) {
    router.delete(`/medios/clipping/${nota.id}`);
  }
};
</script>

<template>
  <Head title="Observatorio de Medios & Clipping" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <Newspaper class="w-6 h-6 text-cyan-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Observatorio de Medios & Clipping
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Monitoreo de prensa escrita, portales digitales, radio y TV con análisis de tono editorial y réplica.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="canWrite"
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/20 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Agregar Nota al Clipping</span>
        </button>
      </div>
    </div>

    <!-- Tone Summary HUD Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3.5">
      <button
        type="button"
        @click="filterByTono('')"
        class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all cursor-pointer"
        :class="!selectedTono ? 'border-cyan-500 ring-2 ring-cyan-500/20' : 'border-slate-200 dark:border-slate-800'"
      >
        <span class="text-[11px] font-mono uppercase text-slate-400 block font-semibold">Total Notas Monitoreadas</span>
        <span class="text-2xl font-extrabold font-mono text-slate-900 dark:text-slate-100">
          {{ resumen_tonos.total || 0 }}
        </span>
      </button>

      <button
        type="button"
        @click="filterByTono('favorable')"
        class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all cursor-pointer"
        :class="selectedTono === 'favorable' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-500/5' : 'border-slate-200 dark:border-slate-800'"
      >
        <span class="text-[11px] font-mono uppercase text-emerald-600 dark:text-emerald-400 block font-semibold">Tono Favorable</span>
        <span class="text-2xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
          {{ resumen_tonos.favorables || 0 }}
        </span>
      </button>

      <button
        type="button"
        @click="filterByTono('neutro')"
        class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all cursor-pointer"
        :class="selectedTono === 'neutro' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-500/5' : 'border-slate-200 dark:border-slate-800'"
      >
        <span class="text-[11px] font-mono uppercase text-amber-600 dark:text-amber-400 block font-semibold">Tono Neutro / Informativo</span>
        <span class="text-2xl font-extrabold font-mono text-amber-600 dark:text-amber-400">
          {{ resumen_tonos.neutras || 0 }}
        </span>
      </button>

      <button
        type="button"
        @click="filterByTono('critico')"
        class="p-4 rounded-2xl bg-white dark:bg-slate-900 border text-left transition-all cursor-pointer"
        :class="selectedTono === 'critico' ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-500/5' : 'border-slate-200 dark:border-slate-800'"
      >
        <span class="text-[11px] font-mono uppercase text-rose-600 dark:text-rose-400 block font-semibold">Tono Crítico / Alerta</span>
        <span class="text-2xl font-extrabold font-mono text-rose-600 dark:text-rose-400">
          {{ resumen_tonos.criticas || 0 }}
        </span>
      </button>
    </div>

    <!-- Filters Row -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-3">
        <!-- Candidate Select -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Candidato:</label>
          <select
            v-model="selectedCandidato"
            @change="applyFilters"
            class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
          >
            <option value="">Todos los Candidatos</option>
            <option v-for="c in candidatos" :key="c.id" :value="c.id">
              {{ c.nombre_completo }}
            </option>
          </select>
        </div>

        <!-- Media Select -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Medio:</label>
          <select
            v-model="selectedMedio"
            @change="applyFilters"
            class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
          >
            <option value="">Todos los Medios</option>
            <option v-for="m in medios" :key="m.id" :value="m.id">
              {{ m.nombre }} ({{ m.tipo_medio }})
            </option>
          </select>
        </div>

        <button
          v-if="selectedCandidato || selectedTono || selectedMedio"
          type="button"
          @click="clearFilters"
          class="text-xs text-cyan-600 dark:text-cyan-400 font-bold hover:underline"
        >
          Limpiar filtros
        </button>
      </div>
    </div>

    <!-- Media Directory Cards Carousel/Grid -->
    <div>
      <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 mb-3 flex items-center gap-2">
        <Radio class="w-4 h-4 text-cyan-500" />
        <span>Medios de Prensa Auditados ({{ medios.length }})</span>
      </h2>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div
          v-for="m in medios"
          :key="m.id"
          class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between">
              <span class="text-[10px] font-mono uppercase font-bold px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                {{ m.tipo_medio }}
              </span>
              <span
                class="text-[9px] font-mono uppercase font-extrabold px-1.5 py-0.2 rounded"
                :class="m.sesgo_editorial_estimado === 'oficialista' ? 'bg-emerald-500/20 text-emerald-600' : (m.sesgo_editorial_estimado === 'opositor' ? 'bg-rose-500/20 text-rose-600' : 'bg-slate-500/20 text-slate-400')"
              >
                {{ m.sesgo_editorial_estimado }}
              </span>
            </div>
            <h4 class="font-bold text-slate-900 dark:text-slate-100 text-xs sm:text-sm mt-2">
              {{ m.nombre }}
            </h4>
          </div>

          <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] font-mono text-slate-400">
            <span>{{ m.alcance_tipo }}</span>
            <span class="font-bold text-cyan-600 dark:text-cyan-400">{{ m.notas_prensa_count || 0 }} notas</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Clipping Notes Feed -->
    <div class="space-y-4">
      <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
        <FileText class="w-4 h-4 text-cyan-500" />
        <span>Clipping de Notas Periodísticas ({{ notas.length }})</span>
      </h2>

      <div v-if="!notas.length" class="text-center py-12 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
        <Newspaper class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-50" />
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No hay notas registradas con estos filtros</h3>
        <p class="text-xs text-slate-500 mt-1">Prueba cambiando el tono o medio seleccionado.</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="nota in notas"
          :key="nota.id"
          class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border shadow-xs transition-all"
          :class="nota.tono_mencion === 'critico' ? 'border-rose-500/40 dark:border-rose-900/50 bg-rose-50/20 dark:bg-rose-950/10' : (nota.tono_mencion === 'favorable' ? 'border-emerald-500/30 dark:border-emerald-900/40' : 'border-slate-200 dark:border-slate-800')"
        >
          <!-- Top Row: Media, Date & Tone -->
          <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
            <div class="flex items-center gap-2">
              <span class="font-bold text-xs sm:text-sm text-cyan-600 dark:text-cyan-400">
                {{ nota.medio?.nombre }}
              </span>
              <span class="text-xs text-slate-400">• {{ nota.fecha }}</span>
              <span v-if="nota.es_tapa_o_principal" class="px-2 py-0.2 rounded-md bg-amber-500 text-slate-950 font-mono text-[10px] font-extrabold uppercase shadow-xs">
                NOTA DE TAPA
              </span>
            </div>

            <div class="flex items-center gap-2">
              <Badge variant="tono" :value="nota.tono_mencion" size="sm" />
              <button
                v-if="canWrite"
                type="button"
                @click="deleteNota(nota)"
                class="text-slate-400 hover:text-rose-500 p-1"
                title="Eliminar"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Title -->
          <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-slate-100 leading-snug">
            {{ nota.titulo }}
          </h3>

          <!-- Summary -->
          <p v-if="nota.resumen" class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            {{ nota.resumen }}
          </p>

          <!-- Candidate Tag & Official Rebuttal -->
          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
            <div v-if="nota.candidato" class="flex items-center gap-2">
              <span class="text-slate-400 font-semibold">Menciona a:</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ nota.candidato.nombre_completo }}</span>
              <span v-if="nota.candidato.es_propio" class="text-[9px] font-mono font-bold px-1.5 py-0.2 rounded bg-cyan-500 text-slate-950">PROPIO</span>
            </div>

            <div v-if="nota.url_nota" class="flex items-center gap-1">
              <a
                :href="nota.url_nota"
                target="_blank"
                rel="noopener noreferrer"
                class="text-cyan-600 dark:text-cyan-400 font-semibold hover:underline inline-flex items-center gap-1"
              >
                <span>Ver Nota Original</span>
                <ExternalLink class="w-3.5 h-3.5" />
              </a>
            </div>
          </div>

          <!-- Official Rebuttal / Replica Box -->
          <div
            v-if="nota.respuesta_replica"
            class="mt-3.5 p-3.5 rounded-2xl bg-cyan-500/10 dark:bg-cyan-500/15 border border-cyan-500/30 text-xs text-slate-700 dark:text-slate-300"
          >
            <div class="flex items-center gap-1.5 font-bold text-cyan-700 dark:text-cyan-300 mb-1">
              <MessageSquare class="w-3.5 h-3.5" />
              <span>Réplica / Posicionamiento Oficial del Comando:</span>
            </div>
            <p>{{ nota.respuesta_replica }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: New Press Clipping Note -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
            Agregar Nota al Clipping
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitNota" class="mt-4 space-y-4 text-sm">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Medio de Prensa *
              </label>
              <select
                v-model="form.medio_prensa_id"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              >
                <option v-for="m in medios" :key="m.id" :value="m.id">{{ m.nombre }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Candidato Mencionado
              </label>
              <select
                v-model="form.candidato_id"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              >
                <option value="">Sin candidato específico</option>
                <option v-for="c in candidatos" :key="c.id" :value="c.id">{{ c.nombre_completo }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Fecha de Publicación *
              </label>
              <input
                v-model="form.fecha_publicacion"
                type="date"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Tono de la Mención *
              </label>
              <select
                v-model="form.tono_mencion"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500 font-bold"
              >
                <option value="favorable">🟢 Favorable</option>
                <option value="neutro">🟡 Neutro / Informativo</option>
                <option value="critico">🔴 Crítico / Alerta</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Titular de la Nota *
            </label>
            <input
              v-model="form.titulo"
              type="text"
              required
              placeholder="Titular exacto o destacado..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Resumen del Contenido
            </label>
            <textarea
              v-model="form.resumen"
              rows="2"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              URL de la Noticia
            </label>
            <input
              v-model="form.url_nota"
              type="url"
              placeholder="https://..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Respuesta / Posicionamiento Oficial
            </label>
            <textarea
              v-model="form.respuesta_replica_candidato"
              rows="2"
              placeholder="Acción tomada por el comando de campaña o vocero..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <div class="flex items-center gap-2">
            <input
              id="es_tapa"
              v-model="form.es_tapa_o_principal"
              type="checkbox"
              class="w-4 h-4 rounded text-cyan-500"
            />
            <label for="es_tapa" class="text-xs font-bold text-slate-700 dark:text-slate-300">
              ¿Es Nota de Tapa o Portada Principal?
            </label>
          </div>

          <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-md"
            >
              Guardar Nota
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
