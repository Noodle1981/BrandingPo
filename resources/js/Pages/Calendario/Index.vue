<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  Calendar,
  Plus,
  Clock,
  MapPin,
  X,
  Trash2,
  CheckCircle2,
  Users,
  Tv,
  Megaphone,
  DollarSign
} from '@lucide/vue';

const props = defineProps({
  eventos: {
    type: Array,
    default: () => [],
  },
  ciclos: {
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
  tipos_disponibles: {
    type: Array,
    default: () => [],
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const selectedTipo = ref(props.filtros.tipo || '');
const selectedCandidato = ref(props.filtros.candidato_id || '');

const applyFilters = () => {
  router.get('/calendario', {
    tipo: selectedTipo.value || undefined,
    candidato_id: selectedCandidato.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const filterByTipo = (tipoKey) => {
  selectedTipo.value = selectedTipo.value === tipoKey ? '' : tipoKey;
  applyFilters();
};

const isModalOpen = ref(false);
const form = useForm({
  ciclo_campana_id: props.ciclos[0]?.id || '',
  candidato_id: props.candidatos[0]?.id || '',
  titulo: '',
  fecha_inicio: new Date().toISOString().slice(0, 16),
  fecha_fin: '',
  tipo_evento: 'acto',
  lugar: '',
  estado: 'programado',
  notas: '',
});

const openModal = () => {
  form.reset();
  form.ciclo_campana_id = props.ciclos[0]?.id || '';
  form.candidato_id = props.candidatos[0]?.id || '';
  form.fecha_inicio = new Date().toISOString().slice(0, 16);
  form.clearErrors();
  isModalOpen.value = true;
};

const submitEvento = () => {
  form.post('/calendario', {
    onSuccess: () => isModalOpen.value = false,
  });
};

const deleteEvento = (ev) => {
  if (confirm(`¿Eliminar el evento "${ev.titulo}"?`)) {
    router.delete(`/calendario/${ev.id}`);
  }
};
</script>

<template>
  <Head title="Agenda de Campaña & Calendario" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <Calendar class="w-6 h-6 text-cyan-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Agenda de Campaña & Cronograma
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Hitos electorales, actos territoriales, debates y fechas críticas de rotación publicitaria.
        </p>
      </div>

      <div v-if="canWrite" class="flex items-center gap-2">
        <button
          type="button"
          @click="openModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/20 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Programar Evento</span>
        </button>
      </div>
    </div>

    <!-- Filter Pills by Event Type -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="text-xs font-semibold text-slate-400 mr-1">Tipo de Evento:</span>
          <button
            type="button"
            @click="filterByTipo('')"
            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer"
            :class="!selectedTipo ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-500 shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
          >
            Todos ({{ eventos.length }})
          </button>
          <button
            v-for="t in tipos_disponibles"
            :key="t.key"
            type="button"
            @click="filterByTipo(t.key)"
            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer"
            :class="selectedTipo === t.key ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-500 shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
          >
            {{ t.label }}
          </button>
        </div>

        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500">Candidato:</label>
          <select
            v-model="selectedCandidato"
            @change="applyFilters"
            class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
          >
            <option value="">Todos</option>
            <option v-for="c in candidatos" :key="c.id" :value="c.id">{{ c.nombre_completo }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Timeline of Events -->
    <div class="space-y-4">
      <div v-if="!eventos.length" class="text-center py-12 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
        <Calendar class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-50" />
        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No hay eventos programados</h3>
        <p class="text-xs text-slate-500 mt-1">Prueba seleccionando otro tipo de evento.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div
          v-for="ev in eventos"
          :key="ev.id"
          class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:border-cyan-500/40 transition-all flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between gap-2 mb-2">
              <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-mono font-extrabold uppercase bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30">
                {{ ev.tipo_evento.replace('_', ' ') }}
              </span>

              <div class="flex items-center gap-2">
                <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                  {{ ev.estado }}
                </span>
                <button
                  v-if="canWrite"
                  type="button"
                  @click="deleteEvento(ev)"
                  class="text-slate-400 hover:text-rose-500 p-1"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>

            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
              {{ ev.titulo }}
            </h3>

            <div class="mt-3 space-y-1 text-xs text-slate-600 dark:text-slate-400">
              <div class="flex items-center gap-2 font-mono">
                <Clock class="w-3.5 h-3.5 text-cyan-500 shrink-0" />
                <span>{{ ev.fecha_inicio }} <span v-if="ev.fecha_fin">hasta {{ ev.fecha_fin }}</span></span>
              </div>
              <div v-if="ev.lugar" class="flex items-center gap-2">
                <MapPin class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                <span>{{ ev.lugar }}</span>
              </div>
            </div>

            <p v-if="ev.notas" class="mt-3 text-xs text-slate-500 dark:text-slate-400 leading-relaxed pt-2 border-t border-slate-100 dark:border-slate-800/80">
              {{ ev.notas }}
            </p>
          </div>

          <div v-if="ev.candidato?.nombre" class="mt-4 pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Candidato asignado:</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ ev.candidato.nombre }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: New Event -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
            Programar Evento en Agenda
          </h3>
          <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitEvento" class="mt-4 space-y-4 text-sm">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Título del Evento *
            </label>
            <input
              v-model="form.titulo"
              type="text"
              required
              placeholder="ej. Acto de Cierre en Plaza Central"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Tipo de Evento *
              </label>
              <select
                v-model="form.tipo_evento"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500 font-bold"
              >
                <option v-for="t in tipos_disponibles" :key="t.key" :value="t.key">{{ t.label }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Candidato
              </label>
              <select
                v-model="form.candidato_id"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              >
                <option value="">Sin asignar</option>
                <option v-for="c in candidatos" :key="c.id" :value="c.id">{{ c.nombre_completo }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Fecha & Hora Inicio *
              </label>
              <input
                v-model="form.fecha_inicio"
                type="datetime-local"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Fecha & Hora Fin
              </label>
              <input
                v-model="form.fecha_fin"
                type="datetime-local"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Lugar / Locación
            </label>
            <input
              v-model="form.lugar"
              type="text"
              placeholder="ej. Plaza San Martín / Canal 12"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Notas & Logística
            </label>
            <textarea
              v-model="form.notas"
              rows="2"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="isModalOpen = false"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-md"
            >
              Programar
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
