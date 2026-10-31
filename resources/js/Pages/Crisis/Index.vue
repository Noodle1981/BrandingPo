<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import CrisisModal from '../../Components/Crisis/CrisisModal.vue';
import CrisisAlianzaModal from '../../Components/Crisis/CrisisAlianzaModal.vue';
import {
  ShieldAlert,
  Flame,
  Clock,
  CheckCircle2,
  AlertTriangle,
  Users,
  Plus,
  Trash2,
  ThumbsUp,
  ThumbsDown,
  Minus
} from '@lucide/vue';

const props = defineProps({
  eventos: {
    type: Array,
    default: () => [],
  },
  alianzas: {
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
  semaforo: {
    type: Object,
    default: () => ({}),
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const selectedCandidato = ref(props.filtros.candidato_id || '');

const applyFilters = () => {
  router.get('/crisis', {
    candidato_id: selectedCandidato.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

// Modal Crisis
const isCrisisModalOpen = ref(false);
const crisisForm = useForm({
  candidato_id: props.candidatos[0]?.id || '',
  titulo: '',
  fecha_evento: new Date().toISOString().slice(0, 16),
  nivel_gravedad: 'moderado',
  minutos_tiempo_respuesta: 30,
  estrategia_contencion: '',
  estado: 'abierto',
  impacto_estimado: 'Medio',
});

const openCrisisModal = () => {
  crisisForm.reset();
  crisisForm.candidato_id = props.candidatos[0]?.id || '';
  crisisForm.fecha_evento = new Date().toISOString().slice(0, 16);
  crisisForm.clearErrors();
  isCrisisModalOpen.value = true;
};

const submitCrisis = () => {
  crisisForm.post('/crisis', {
    onSuccess: () => isCrisisModalOpen.value = false,
  });
};

const resolveCrisis = (crisis) => {
  if (!canWrite.value) return;
  router.put(`/crisis/${crisis.id}`, {
    estado: 'resuelto',
    estrategia_contencion: crisis.estrategia_contencion,
    minutos_tiempo_respuesta: crisis.minutos_tiempo_respuesta,
  });
};

// Modal Alianza
const isAlianzaModalOpen = ref(false);
const alianzaForm = useForm({
  candidato_id: props.candidatos[0]?.id || '',
  nombre_figura: '',
  cargo_o_rol: '',
  tipo_impacto: 'suma',
  notas_observacion: '',
});

const openAlianzaModal = () => {
  alianzaForm.reset();
  alianzaForm.candidato_id = props.candidatos[0]?.id || '';
  alianzaForm.clearErrors();
  isAlianzaModalOpen.value = true;
};

const submitAlianza = () => {
  alianzaForm.post('/crisis/alianza', {
    onSuccess: () => isAlianzaModalOpen.value = false,
  });
};

const deleteAlianza = (a) => {
  if (confirm(`¿Eliminar la alianza con ${a.nombre_figura}?`)) {
    router.delete(`/crisis/alianza/${a.id}`);
  }
};
</script>

<template>
  <Head title="Centro de Crisis & Matriz de Alianzas" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <ShieldAlert class="w-6 h-6 text-rose-500 animate-pulse" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Centro de Crisis & Matriz de Alianzas
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Semáforo de incidentes en tiempo real, protocolo de contención y evaluación de padrinos políticos.
        </p>
      </div>

      <div v-if="canWrite" class="flex items-center gap-2">
        <button
          type="button"
          @click="openCrisisModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-sm transition-all shadow-md shadow-rose-500/20 cursor-pointer"
        >
          <Flame class="w-4 h-4" />
          <span>Reportar Incidente</span>
        </button>

        <button
          type="button"
          @click="openAlianzaModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-100 font-bold text-sm transition-all border border-slate-700 cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Nueva Alianza</span>
        </button>
      </div>
    </div>

    <!-- Semaphore HUD Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
      <!-- Critical Active -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-rose-500/40 dark:border-rose-900/50 shadow-xs relative overflow-hidden">
        <div class="flex items-center justify-between">
          <span class="text-xs font-mono uppercase font-bold text-rose-600 dark:text-rose-400">Crisis Críticas Activas</span>
          <Flame class="w-5 h-5 text-rose-500" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold font-mono text-rose-600 dark:text-rose-400">
            {{ semaforo.criticos_activos || 0 }}
          </span>
          <span class="text-xs text-slate-500">urgente contención</span>
        </div>
      </div>

      <!-- Moderate Active -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-amber-500/40 dark:border-amber-900/50 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-mono uppercase font-bold text-amber-600 dark:text-amber-400">Alertas Moderadas</span>
          <AlertTriangle class="w-5 h-5 text-amber-500" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold font-mono text-amber-600 dark:text-amber-400">
            {{ semaforo.moderados_activos || 0 }}
          </span>
          <span class="text-xs text-slate-500">en seguimiento</span>
        </div>
      </div>

      <!-- Resolved -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-emerald-500/40 dark:border-emerald-900/50 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-mono uppercase font-bold text-emerald-600 dark:text-emerald-400">Casos Resueltos</span>
          <CheckCircle2 class="w-5 h-5 text-emerald-500" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
            {{ semaforo.resueltos || 0 }}
          </span>
          <span class="text-xs text-slate-500">neutralizados</span>
        </div>
      </div>

      <!-- Response Time Avg -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-cyan-500/40 dark:border-cyan-900/50 shadow-xs">
        <div class="flex items-center justify-between">
          <span class="text-xs font-mono uppercase font-bold text-cyan-600 dark:text-cyan-400">Tiempo de Respuesta</span>
          <Clock class="w-5 h-5 text-cyan-500" />
        </div>
        <div class="mt-2 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold font-mono text-cyan-600 dark:text-cyan-400">
            {{ semaforo.promedio_tiempo_min || 0 }} min
          </span>
          <span class="text-xs text-slate-500">promedio</span>
        </div>
      </div>
    </div>

    <!-- Two Columns: Incidents Stream + Alliances Matrix -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left 7 Cols: Crisis Incidents -->
      <div class="lg:col-span-7 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Flame class="w-4 h-4 text-rose-500" />
            <span>Registro de Incidentes de Crisis ({{ eventos.length }})</span>
          </h2>
        </div>

        <div v-if="!eventos.length" class="text-center py-12 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8">
          <ShieldAlert class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-50" />
          <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Sin incidentes reportados</h3>
          <p class="text-xs text-slate-500 mt-1">El semáforo se encuentra en estado verde óptimo.</p>
        </div>

        <div v-else class="space-y-3.5">
          <div
            v-for="ev in eventos"
            :key="ev.id"
            class="p-5 rounded-3xl bg-white dark:bg-slate-900 border shadow-xs transition-all"
            :class="ev.nivel_gravedad === 'critico' && ev.estado !== 'resuelto' ? 'border-rose-500 ring-2 ring-rose-500/20 bg-rose-500/5' : 'border-slate-200 dark:border-slate-800'"
          >
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div class="flex items-center gap-2">
                <span
                  class="px-2 py-0.5 rounded-md font-mono text-[10px] font-extrabold uppercase"
                  :class="ev.nivel_gravedad === 'critico' ? 'bg-rose-500 text-white' : (ev.nivel_gravedad === 'moderado' ? 'bg-amber-500 text-slate-950' : 'bg-emerald-500 text-slate-950')"
                >
                  {{ ev.nivel_gravedad }}
                </span>
                <span class="text-xs font-mono text-slate-400">{{ ev.fecha }}</span>
              </div>

              <div class="flex items-center gap-2">
                <span
                  class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded-full border"
                  :class="ev.estado === 'resuelto' ? 'bg-emerald-500/20 text-emerald-600 border-emerald-500/30' : 'bg-rose-500/20 text-rose-600 border-rose-500/30 animate-pulse'"
                >
                  {{ ev.estado.replace('_', ' ') }}
                </span>

                <button
                  v-if="canWrite && ev.estado !== 'resuelto'"
                  type="button"
                  @click="resolveCrisis(ev)"
                  class="px-2.5 py-1 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition-all shadow-xs cursor-pointer"
                >
                  Marcar Resuelto
                </button>
              </div>
            </div>

            <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 mt-2">
              {{ ev.titulo }}
            </h3>

            <div class="mt-2 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-3">
              <span>Candidato: <strong>{{ ev.candidato?.nombre_completo }}</strong></span>
              <span>Tiempo de reacción: <strong>{{ ev.minutos_tiempo_respuesta }} min</strong></span>
            </div>

            <!-- Containment Strategy -->
            <div v-if="ev.estrategia_contencion" class="mt-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300">
              <strong class="text-cyan-600 dark:text-cyan-400 block mb-1">🛡️ Estrategia de Contención & Despliegue:</strong>
              <p>{{ ev.estrategia_contencion }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right 5 Cols: Alliances & Endorsements Matrix -->
      <div class="lg:col-span-5 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Users class="w-4 h-4 text-cyan-500" />
            <span>Matriz de Padrinos & Alianzas</span>
          </h2>
        </div>

        <div class="space-y-3">
          <div
            v-for="a in alianzas"
            :key="a.id"
            class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-2"
          >
            <div class="flex items-center justify-between">
              <div>
                <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm">
                  {{ a.nombre_figura }}
                </h4>
                <p class="text-xs text-slate-500">{{ a.cargo_o_rol }}</p>
              </div>

              <div class="flex items-center gap-2">
                <span
                  class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-mono font-extrabold uppercase"
                  :class="a.tipo_impacto === 'suma' ? 'bg-emerald-500/20 text-emerald-600 border border-emerald-500/30' : (a.tipo_impacto === 'resta' ? 'bg-rose-500/20 text-rose-600 border border-rose-500/30' : 'bg-slate-500/20 text-slate-400')"
                >
                  <ThumbsUp v-if="a.tipo_impacto === 'suma'" class="w-3.5 h-3.5" />
                  <ThumbsDown v-else-if="a.tipo_impacto === 'resta'" class="w-3.5 h-3.5" />
                  <Minus v-else class="w-3.5 h-3.5" />
                  <span>{{ a.tipo_impacto === 'suma' ? '+ Suma' : (a.tipo_impacto === 'resta' ? '- Resta' : '= Neutro') }}</span>
                </span>

                <button
                  v-if="canWrite"
                  type="button"
                  @click="deleteAlianza(a)"
                  class="text-slate-400 hover:text-rose-500 p-1"
                  title="Eliminar"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>

            <p v-if="a.notas_observacion" class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed pt-2 border-t border-slate-100 dark:border-slate-800/80">
              {{ a.notas_observacion }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: New Crisis Incident Delegado -->
    <CrisisModal
      :show="isCrisisModalOpen"
      :form="crisisForm"
      :candidatos="candidatos"
      @close="isCrisisModalOpen = false"
      @submit="submitCrisis"
    />

    <!-- Modal: New Alliance Delegado -->
    <CrisisAlianzaModal
      :show="isAlianzaModalOpen"
      :form="alianzaForm"
      :candidatos="candidatos"
      @close="isAlianzaModalOpen = false"
      @submit="submitAlianza"
    />
  </WarRoomLayout>
</template>
