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
  DollarSign,
  Flag,
  Target,
  Sparkles,
  Flame,
  AlertTriangle,
  Layers,
  Filter,
  ArrowRight,
  TrendingUp,
  Share2
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
  ejes: {
    type: Array,
    default: () => [],
  },
  stats_ejes: {
    type: Object,
    default: () => ({
      total_ejes: 0,
      ejes_maximizar: 0,
      ejes_reforzar: 0,
      ejes_equilibrados: 0,
    }),
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
const selectedEje = ref(props.filtros.eje_tematico_id || '');
const filterTabDiagnostico = ref('todos'); // 'todos', 'maximizar', 'reforzar'

const applyFilters = () => {
  router.get('/calendario', {
    tipo: selectedTipo.value || undefined,
    candidato_id: selectedCandidato.value || undefined,
    eje_tematico_id: selectedEje.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const filterByTipo = (tipoKey) => {
  selectedTipo.value = selectedTipo.value === tipoKey ? '' : tipoKey;
  applyFilters();
};

const filterByEje = (ejeId) => {
  selectedEje.value = selectedEje.value === String(ejeId) ? '' : String(ejeId);
  applyFilters();
};

const resetAllFilters = () => {
  selectedTipo.value = '';
  selectedCandidato.value = '';
  selectedEje.value = '';
  applyFilters();
};

const ejesFiltradosDiagnostico = computed(() => {
  if (filterTabDiagnostico.value === 'maximizar') {
    return props.ejes.filter(e => e.diagnostico === 'maximizar');
  }
  if (filterTabDiagnostico.value === 'reforzar') {
    return props.ejes.filter(e => e.diagnostico === 'reforzar');
  }
  return props.ejes;
});

const candidatoPrincipal = computed(() => props.candidatos.find(c => c.es_propio) || props.candidatos[0] || null);

const isModalOpen = ref(false);
const form = useForm({
  ciclo_campana_id: props.ciclos[0]?.id || '',
  candidato_id: props.candidatos.find(c => c.es_propio)?.id || props.candidatos[0]?.id || '',
  eje_tematico_id: '',
  titulo: '',
  fecha_inicio: new Date().toISOString().slice(0, 16),
  fecha_fin: '',
  tipo_evento: 'publicacion_eje',
  lugar: '',
  estado: 'programado',
  notas: '',
});

const openModal = (preselectedEjeId = null) => {
  form.reset();
  form.ciclo_campana_id = props.ciclos[0]?.id || '';
  form.candidato_id = props.candidatos.find(c => c.es_propio)?.id || props.candidatos[0]?.id || '';
  form.eje_tematico_id = preselectedEjeId || '';
  form.tipo_evento = 'publicacion_eje';
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
  if (confirm(`¿Eliminar la actividad "${ev.titulo}" de la agenda?`)) {
    router.delete(`/calendario/${ev.id}`);
  }
};
</script>

<template>
  <Head title="Agenda de Campaña & Estrategia por Ejes" />

  <WarRoomLayout>
    <div class="space-y-6">

      <!-- Cabecera Principal -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2.5">
            <div class="p-2.5 rounded-2xl bg-cyan-500/10 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30">
              <Calendar class="w-6 h-6" />
            </div>
            <div>
              <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                <span>Agenda Estratégica & Contenidos por Eje</span>
              </h1>
              <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Planificación táctica del feed: maximizar lo que funciona bien, reforzar lo que falta y calendarizar hitos territoriales.
              </p>
            </div>
          </div>
        </div>

        <div v-if="canWrite" class="flex items-center gap-3">
          <button
            type="button"
            @click="openModal()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/20 cursor-pointer hover:scale-102"
          >
            <Plus class="w-4 h-4 stroke-[3]" />
            <span>Programar Publicación / Evento</span>
          </button>
        </div>
      </div>

      <!-- 🎯 PANEL DE INTELIGENCIA ESTRATÉGICA POR EJES TEMÁTICOS -->
      <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <!-- Header del Panel Estratégico -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div class="space-y-1">
            <h2 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Target class="w-4 h-4 text-cyan-500" />
              <span>Matriz de Diagnóstico por Ejes (¿A qué apuntar ahora?)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              Balance en tiempo real entre publicaciones realizadas en el feed e hitos agendados en campaña.
            </p>
          </div>

          <!-- Filtros de Estado de Diagnóstico -->
          <div class="flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono">
            <button
              type="button"
              @click="filterTabDiagnostico = 'todos'"
              class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
              :class="filterTabDiagnostico === 'todos' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700'"
            >
              Todos ({{ ejes.length }})
            </button>
            <button
              type="button"
              @click="filterTabDiagnostico = 'maximizar'"
              class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="filterTabDiagnostico === 'maximizar' ? 'bg-emerald-500 text-slate-950 shadow-xs' : 'text-emerald-600 dark:text-emerald-400 hover:text-emerald-500'"
            >
              <TrendingUp class="w-3.5 h-3.5" />
              <span>Maximizar ({{ stats_ejes.ejes_maximizar }})</span>
            </button>
            <button
              type="button"
              @click="filterTabDiagnostico = 'reforzar'"
              class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer flex items-center gap-1.5"
              :class="filterTabDiagnostico === 'reforzar' ? 'bg-amber-500 text-slate-950 shadow-xs' : 'text-amber-600 dark:text-amber-400 hover:text-amber-500'"
            >
              <AlertTriangle class="w-3.5 h-3.5" />
              <span>Reforzar ({{ stats_ejes.ejes_reforzar }})</span>
            </button>
          </div>
        </div>

        <!-- Tarjetas Interactivas de Ejes Temáticos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5">
          <div
            v-for="eje in ejesFiltradosDiagnostico"
            :key="eje.id"
            @click="filterByEje(eje.id)"
            class="p-4 rounded-2xl border transition-all cursor-pointer flex flex-col justify-between space-y-3 relative group"
            :class="selectedEje === String(eje.id)
              ? 'bg-cyan-500/10 dark:bg-cyan-500/15 border-cyan-500 ring-2 ring-cyan-500/40 shadow-sm'
              : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800/80 hover:border-slate-300 dark:hover:border-slate-700'"
          >
            <!-- Cabecera de la Tarjeta del Eje -->
            <div class="flex items-start justify-between gap-2">
              <div class="flex items-center gap-2 min-w-0">
                <span
                  class="w-2.5 h-2.5 rounded-full shrink-0"
                  :style="{ backgroundColor: eje.color_badge || '#06b6d4' }"
                ></span>
                <span class="text-xs font-black text-slate-900 dark:text-slate-100 truncate" :title="eje.nombre">
                  {{ eje.nombre }}
                </span>
              </div>

              <!-- Badge de Diagnóstico -->
              <span
                class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-black shrink-0"
                :class="eje.diagnostico === 'maximizar'
                  ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30'
                  : (eje.diagnostico === 'reforzar'
                    ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30'
                    : 'bg-slate-200/60 dark:bg-slate-800 text-slate-600 dark:text-slate-400')"
              >
                {{ eje.diagnostico_badge }}
              </span>
            </div>

            <!-- Métricas Clave del Eje -->
            <div class="grid grid-cols-3 gap-2 text-center font-mono py-1.5 px-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
              <div>
                <span class="text-[9px] text-slate-400 block uppercase">Posts</span>
                <strong class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ eje.total_publicaciones }}</strong>
              </div>
              <div class="border-x border-slate-100 dark:border-slate-800">
                <span class="text-[9px] text-slate-400 block uppercase">Agenda</span>
                <strong class="text-xs font-bold text-cyan-600 dark:text-cyan-400">{{ eje.total_eventos }}</strong>
              </div>
              <div>
                <span class="text-[9px] text-slate-400 block uppercase">Score</span>
                <strong class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ eje.score_promedio }}</strong>
              </div>
            </div>

            <!-- Diagnóstico Estratégico y Acción Rápida -->
            <div class="space-y-2 pt-1 border-t border-slate-200/50 dark:border-slate-800/60">
              <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-tight line-clamp-2">
                {{ eje.diagnostico_texto }}
              </p>

              <div class="flex items-center justify-between text-[10px] font-mono">
                <span
                  class="font-bold flex items-center gap-1"
                  :class="selectedEje === String(eje.id) ? 'text-cyan-600 dark:text-cyan-400' : 'text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200'"
                >
                  <span>{{ selectedEje === String(eje.id) ? '✓ Filtrando agenda' : 'Clic para filtrar' }}</span>
                </span>

                <button
                  v-if="canWrite"
                  type="button"
                  @click.stop="openModal(eje.id)"
                  class="px-2 py-0.5 rounded-md bg-cyan-500/10 hover:bg-cyan-500 hover:text-slate-950 text-cyan-600 dark:text-cyan-400 font-bold transition-all"
                  title="Agendar nueva publicación o evento para este eje"
                >
                  + Agendar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BARRA DE FILTROS COMBINADOS -->
      <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Filtro por Tipo de Evento -->
        <div class="flex items-center gap-1.5 flex-wrap">
          <span class="text-xs font-semibold text-slate-400 mr-1 flex items-center gap-1">
            <Filter class="w-3.5 h-3.5" />
            <span>Tipo:</span>
          </span>
          <button
            type="button"
            @click="filterByTipo('')"
            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer font-mono"
            :class="!selectedTipo ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-500 shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
          >
            Todos ({{ eventos.length }})
          </button>
          <button
            v-for="t in tipos_disponibles"
            :key="t.key"
            type="button"
            @click="filterByTipo(t.key)"
            class="px-2.5 py-1 rounded-lg text-xs font-medium border transition-all cursor-pointer font-mono"
            :class="selectedTipo === t.key ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-500 shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300'"
          >
            {{ t.label }}
          </button>
        </div>

        <!-- Filtros Desplegables: Eje & Candidato -->
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
          <!-- Filtro Eje Temático Dropdown -->
          <div class="flex items-center gap-1.5">
            <label class="text-xs font-semibold text-slate-400">Eje:</label>
            <select
              v-model="selectedEje"
              @change="applyFilters"
              class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-mono"
            >
              <option value="">Todos los Ejes</option>
              <option v-for="ej in ejes" :key="ej.id" :value="String(ej.id)">
                {{ ej.nombre }} ({{ ej.diagnostico_badge }})
              </option>
            </select>
          </div>

          <!-- Filtro Candidato Dropdown -->
          <div class="flex items-center gap-1.5">
            <label class="text-xs font-semibold text-slate-400">Candidato:</label>
            <select
              v-model="selectedCandidato"
              @change="applyFilters"
              class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
            >
              <option value="">Todos</option>
              <option v-for="c in candidatos" :key="c.id" :value="String(c.id)">{{ c.nombre_completo }}</option>
            </select>
          </div>

          <!-- Botón Limpiar Filtros -->
          <button
            v-if="selectedTipo || selectedCandidato || selectedEje"
            type="button"
            @click="resetAllFilters"
            class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 text-xs font-bold font-mono transition-all cursor-pointer"
            title="Limpiar todos los filtros"
          >
            ✕ Limpiar
          </button>
        </div>
      </div>

      <!-- 📅 CRONOGRAMA & TIMELINE DE EVENTOS Y CONTENIDOS -->
      <div class="space-y-4">
        <!-- Estado Vacío si no hay eventos -->
        <div v-if="!eventos.length" class="text-center py-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 space-y-3">
          <Calendar class="w-12 h-12 text-slate-400 mx-auto opacity-40" />
          <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">
            No hay actividades ni contenidos agendados con este filtro
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
            Prueba seleccionando otro eje temático, o programa una nueva publicación para reforzar la presencia de campaña.
          </p>
          <div v-if="canWrite" class="pt-2">
            <button
              type="button"
              @click="openModal()"
              class="px-4 py-2 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs shadow-xs cursor-pointer"
            >
              + Programar Actividad Ahora
            </button>
          </div>
        </div>

        <!-- Grilla de Tarjetas de la Agenda -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
          <div
            v-for="ev in eventos"
            :key="ev.id"
            class="p-5 rounded-3xl bg-white dark:bg-slate-900 border shadow-xs hover:border-cyan-500/40 transition-all flex flex-col justify-between space-y-4"
            :class="ev.tipo_evento === 'publicacion_eje'
              ? 'border-cyan-500/30 dark:border-cyan-500/20 shadow-cyan-500/5'
              : 'border-slate-200 dark:border-slate-800'"
          >
            <!-- Parte Superior: Badges & Estado -->
            <div class="space-y-3">
              <div class="flex items-center justify-between gap-2 flex-wrap">
                <!-- Badge del Eje Temático -->
                <span
                  v-if="ev.eje_tematico"
                  class="px-2.5 py-1 rounded-lg text-xs font-mono font-extrabold flex items-center gap-1.5 border"
                  :style="{
                    backgroundColor: `${ev.eje_tematico.color_badge}15`,
                    borderColor: `${ev.eje_tematico.color_badge}40`,
                    color: ev.eje_tematico.color_badge || '#06b6d4'
                  }"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: ev.eje_tematico.color_badge || '#06b6d4' }"></span>
                  <span>{{ ev.eje_tematico.nombre }}</span>
                </span>
                <span
                  v-else
                  class="px-2 py-0.5 rounded-md text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800"
                >
                  Sin Eje Asignado
                </span>

                <!-- Tipo de Evento & Estado -->
                <div class="flex items-center gap-1.5">
                  <span
                    class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase"
                    :class="ev.tipo_evento === 'publicacion_eje'
                      ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                  >
                    {{ ev.tipo_evento.replace('_', ' ') }}
                  </span>

                  <button
                    v-if="canWrite"
                    type="button"
                    @click="deleteEvento(ev)"
                    class="text-slate-400 hover:text-rose-500 p-1 rounded-lg hover:bg-rose-500/10 transition-colors"
                    title="Eliminar de la agenda"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <!-- Título del Evento -->
              <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 leading-snug">
                {{ ev.titulo }}
              </h3>

              <!-- Datos de Fecha, Hora y Canal/Lugar -->
              <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 font-mono">
                <div class="flex items-center gap-2">
                  <Clock class="w-3.5 h-3.5 text-cyan-500 shrink-0" />
                  <span>{{ ev.fecha_inicio }} <span v-if="ev.fecha_fin">hasta {{ ev.fecha_fin }}</span></span>
                </div>
                <div v-if="ev.lugar" class="flex items-center gap-2">
                  <MapPin class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
                  <span>{{ ev.lugar }}</span>
                </div>
              </div>

              <!-- Notas & Estrategia -->
              <p v-if="ev.notas" class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed pt-2.5 border-t border-slate-100 dark:border-slate-800">
                {{ ev.notas }}
              </p>
            </div>

            <!-- Footer: Candidato Asignado & Estado de Realización -->
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-mono">
              <div class="flex items-center gap-1.5">
                <span class="text-slate-400">Responsable:</span>
                <strong class="text-slate-800 dark:text-slate-200">{{ ev.candidato?.nombre || 'General' }}</strong>
              </div>

              <span
                class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                :class="ev.estado === 'realizado'
                  ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                  : (ev.estado === 'cancelado' ? 'bg-rose-500/10 text-rose-500' : 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400')"
              >
                {{ ev.estado }}
              </span>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- MODAL: PROGRAMAR PUBLICACIÓN O EVENTO EN AGENDA -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
          <div>
            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Calendar class="w-5 h-5 text-cyan-500" />
              <span>Programar en Agenda de Campaña</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Planifica contenidos digitales o hitos territoriales vinculados a un eje temático.
            </p>
          </div>
          <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitEvento" class="mt-4 space-y-4 text-sm">
          <!-- Título -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Título del Contenido / Evento *
            </label>
            <input
              v-model="form.titulo"
              type="text"
              required
              placeholder="ej. Lanzamiento Reel: Propuesta Primer Empleo Joven"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <!-- Selección de Eje Temático Estratégico -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1 flex items-center justify-between">
              <span>Eje Temático Estratégico *</span>
              <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400">Guía el mensaje y público</span>
            </label>
            <select
              v-model="form.eje_tematico_id"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500 font-mono font-bold"
            >
              <option value="">-- Sin eje específico (General) --</option>
              <option v-for="ej in ejes" :key="ej.id" :value="ej.id">
                {{ ej.nombre }} [{{ ej.diagnostico_badge }}]
              </option>
            </select>
          </div>

          <!-- Tipo de Actividad -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                Tipo de Actividad *
              </label>
              <span class="text-[11px] font-mono text-cyan-600 dark:text-cyan-400 flex items-center gap-1 font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Perfil: {{ candidatoPrincipal?.nombre_completo || 'Federico Sisterna' }}</span>
              </span>
            </div>
            <select
              v-model="form.tipo_evento"
              required
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-bold"
            >
              <option v-for="t in tipos_disponibles" :key="t.key" :value="t.key">{{ t.label }}</option>
            </select>
          </div>

          <!-- Fechas y Horarios -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Fecha & Hora Publicación / Inicio *
              </label>
              <input
                v-model="form.fecha_inicio"
                type="datetime-local"
                required
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-mono"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Fecha & Hora Fin (Opcional)
              </label>
              <input
                v-model="form.fecha_fin"
                type="datetime-local"
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-mono"
              />
            </div>
          </div>

          <!-- Canal o Lugar -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Canal Digital / Locación Física
            </label>
            <input
              v-model="form.lugar"
              type="text"
              placeholder="ej. Instagram & TikTok Reels / Club Juventud / Canal 12"
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <!-- Notas & Estrategia -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Objetivo Estratégico & Logística
            </label>
            <textarea
              v-model="form.notas"
              rows="3"
              placeholder="Detalla el mensaje clave, formato audiovisual o consigna territorial a destacar..."
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <!-- Botones de Acción -->
          <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="isModalOpen = false"
              class="px-4 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-semibold transition-all cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm shadow-md transition-all cursor-pointer"
            >
              Programar en Agenda
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
