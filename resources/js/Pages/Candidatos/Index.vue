<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  Users,
  UserPlus,
  Filter,
  Sparkles,
  ExternalLink,
  Edit,
  Trash2,
  X,
  Share2,
  Building,
  Calendar,
  CheckCircle2
} from '@lucide/vue';

const props = defineProps({
  candidatos: {
    type: Array,
    default: () => [],
  },
  ciclos: {
    type: Array,
    default: () => [],
  },
  territorios: {
    type: Array,
    default: () => [],
  },
  filtros: {
    type: Object,
    default: () => ({}),
  },
  estados_disponibles: {
    type: Array,
    default: () => [],
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const selectedCiclo = ref(props.filtros.ciclo_id || '');
const selectedEstado = ref(props.filtros.estado || '');
const viewMode = ref('cards'); // 'cards' | 'table'

const applyFilters = () => {
  router.get('/candidatos', {
    ciclo_id: selectedCiclo.value || undefined,
    estado: selectedEstado.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const clearFilters = () => {
  selectedCiclo.value = '';
  selectedEstado.value = '';
  applyFilters();
};

// Modal Logic
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingCandidatoId = ref(null);

const form = useForm({
  nombre_completo: '',
  partido_coalicion: '',
  cargo_aspirado: '',
  estado_politico: 'candidato',
  ciclo_campana_id: props.ciclos[0]?.id || '',
  territorio_id: props.territorios[0]?.id || '',
  color_hex: '#06b6d4',
  es_propio: false,
  avatar_url: '',
  bio_resumen: '',
});

const openCreateModal = () => {
  isEditing.value = false;
  editingCandidatoId.value = null;
  form.reset();
  form.ciclo_campana_id = props.ciclos[0]?.id || '';
  form.territorio_id = props.territorios[0]?.id || '';
  form.clearErrors();
  isModalOpen.value = true;
};

const openEditModal = (c) => {
  isEditing.value = true;
  editingCandidatoId.value = c.id;
  form.nombre_completo = c.nombre_completo;
  form.partido_coalicion = c.partido_coalicion;
  form.cargo_aspirado = c.cargo_aspirado || '';
  form.estado_politico = c.estado_politico;
  form.ciclo_campana_id = c.ciclo_campana_id;
  form.territorio_id = c.territorio_id || '';
  form.color_hex = c.color_hex || '#06b6d4';
  form.es_propio = c.es_propio;
  form.avatar_url = c.avatar_url || '';
  form.bio_resumen = c.bio_resumen || '';
  form.clearErrors();
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const submitForm = () => {
  if (isEditing.value) {
    form.put(`/candidatos/${editingCandidatoId.value}`, {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post('/candidatos', {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteCandidato = (c) => {
  if (confirm(`¿Estás seguro de eliminar a ${c.nombre_completo}?`)) {
    router.delete(`/candidatos/${c.id}`);
  }
};

const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};
</script>

<template>
  <Head title="Oposición & Candidatos Rivales" />

  <WarRoomLayout>
    <!-- Banner de Acceso a Mi Candidato -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 via-slate-800 to-cyan-950 text-white border border-cyan-500/30 flex items-center justify-between flex-wrap gap-3 shadow-sm">
      <div class="flex items-center gap-3">
        <div class="p-2 rounded-xl bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">
          <Sparkles class="w-5 h-5" />
        </div>
        <div>
          <span class="text-xs font-bold text-cyan-300 block">PERFIL DE CAMPAÑA OFICIAL</span>
          <p class="text-xs text-slate-300">¿Quieres gestionar tu propio candidato y sus redes sociales (Punto Cero)?</p>
        </div>
      </div>
      <Link
        href="/mi-candidato"
        class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-xs transition-all hover:scale-102"
      >
        Ir a Mi Candidato &rarr;
      </Link>
    </div>

    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <Users class="w-6 h-6 text-purple-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Oposición & Rivales Políticos (Competencia)
          </h1>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Directorio de actores opositores para benchmarking digital y auditoría externa.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          v-if="canWrite"
          type="button"
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm transition-all shadow-md shadow-purple-600/20 cursor-pointer"
        >
          <UserPlus class="w-4 h-4" />
          <span>Nuevo Candidato Rival</span>
        </button>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-3 flex-1">
        <!-- Ciclo de Campaña Filter -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            Campaña / Año:
          </label>
          <select
            v-model="selectedCiclo"
            @change="applyFilters"
            class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
          >
            <option value="">Todos los Ciclos</option>
            <option v-for="c in ciclos" :key="c.id" :value="c.id">
              {{ c.anio }} - {{ c.nombre }}
            </option>
          </select>
        </div>

        <!-- Estado Político Filter -->
        <div class="flex items-center gap-2">
          <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap">
            Estado:
          </label>
          <select
            v-model="selectedEstado"
            @change="applyFilters"
            class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
          >
            <option value="">Todos los Estados</option>
            <option v-for="est in estados_disponibles" :key="est.key" :value="est.key">
              {{ est.label }}
            </option>
          </select>
        </div>

        <!-- Reset Button -->
        <button
          v-if="selectedCiclo || selectedEstado"
          type="button"
          @click="clearFilters"
          class="text-xs text-cyan-600 dark:text-cyan-400 hover:underline font-semibold"
        >
          Limpiar filtros
        </button>
      </div>

      <!-- View Toggle (Cards / Table) -->
      <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs">
        <button
          type="button"
          @click="viewMode = 'cards'"
          class="px-3 py-1 rounded-lg font-medium transition-all"
          :class="viewMode === 'cards' ? 'bg-white dark:bg-slate-800 text-cyan-600 dark:text-cyan-400 shadow-xs font-bold' : 'text-slate-500'"
        >
          Tarjetas
        </button>
        <button
          type="button"
          @click="viewMode = 'table'"
          class="px-3 py-1 rounded-lg font-medium transition-all"
          :class="viewMode === 'table' ? 'bg-white dark:bg-slate-800 text-cyan-600 dark:text-cyan-400 shadow-xs font-bold' : 'text-slate-500'"
        >
          Tabla
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="!candidatos.length"
      class="text-center py-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 shadow-xs"
    >
      <div class="w-16 h-16 rounded-2xl bg-cyan-500/10 text-cyan-500 flex items-center justify-center mx-auto mb-4">
        <Users class="w-8 h-8 opacity-80" />
      </div>
      <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">No hay candidatos rivales u oposición registrados</h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 max-w-md mx-auto">
        Actualmente no hay candidatos opositores en este workspace. Puedes registrar rivales para monitorear sus redes y contrastar métricas.
      </p>
      <div v-if="canWrite" class="mt-5">
        <button
          type="button"
          @click="openCreateModal"
          class="px-4 py-2 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs inline-flex items-center gap-2 transition-all shadow-xs cursor-pointer"
        >
          <UserPlus class="w-4 h-4" />
          <span>Registrar Candidato Rival</span>
        </button>
      </div>
    </div>

    <!-- Cards Grid View -->
    <div v-else-if="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5">
      <div
        v-for="c in candidatos"
        :key="c.id"
        class="bg-white dark:bg-slate-900 border rounded-3xl p-6 shadow-xs hover:shadow-md transition-all duration-200 relative group overflow-hidden"
        :class="c.es_propio ? 'border-cyan-500/50 shadow-cyan-500/5 dark:shadow-none ring-1 ring-cyan-500/30' : 'border-slate-200 dark:border-slate-800'"
      >
        <!-- Top Profile Info -->
        <div class="flex items-start gap-4">
          <!-- Avatar -->
          <div class="relative shrink-0">
            <img
              :src="c.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="c.nombre_completo"
              class="w-16 h-16 rounded-2xl object-cover border-2 shadow-sm"
              :style="{ borderColor: c.color_hex || '#06b6d4' }"
            />
            <span
              v-if="c.es_propio"
              class="absolute -top-2 -left-2 text-[9px] font-mono font-extrabold uppercase px-1.5 py-0.2 rounded-md bg-cyan-500 text-slate-950 shadow-xs"
            >
              PROPIO
            </span>
          </div>

          <!-- Titles -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
              <h3 class="font-bold text-base sm:text-lg text-slate-900 dark:text-slate-100 truncate">
                {{ c.nombre_completo }}
              </h3>
              <Badge variant="estado" :value="c.estado_politico" size="sm" />
            </div>
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mt-0.5">
              {{ c.partido_coalicion }}
            </p>
            <p v-if="c.cargo_aspirado" class="text-xs text-slate-500 dark:text-slate-500 mt-0.5">
              {{ c.cargo_aspirado }}
            </p>
          </div>
        </div>

        <!-- Bio Resumen -->
        <p v-if="c.bio_resumen" class="mt-3.5 text-xs text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2">
          {{ c.bio_resumen }}
        </p>

        <!-- Social Networks Pills -->
        <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between flex-wrap gap-2">
          <div class="flex items-center gap-1.5 flex-wrap">
            <span
              v-for="p in c.perfiles"
              :key="p.id"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-mono border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950"
            >
              <Badge :variant="p.plataforma" size="sm" />
              <span class="text-slate-600 dark:text-slate-400 font-bold">{{ formatNumber(p.seguidores_actuales) }}</span>
            </span>
          </div>

          <div class="text-right">
            <span class="text-[10px] uppercase text-slate-400 block font-mono">Seguidores Totales</span>
            <span class="text-sm font-extrabold font-mono text-cyan-600 dark:text-cyan-400">
              {{ formatNumber(c.total_seguidores) }}
            </span>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
          <div class="text-slate-500 dark:text-slate-400 flex items-center gap-1">
            <Calendar class="w-3.5 h-3.5" />
            <span>{{ c.ciclo_campana }}</span>
          </div>

          <div class="flex items-center gap-2">
            <Link
              :href="`/candidatos/${c.id}`"
              class="inline-flex items-center gap-1 font-semibold text-cyan-600 dark:text-cyan-400 hover:underline"
            >
              <span>Ver Ficha</span>
              <ExternalLink class="w-3.5 h-3.5" />
            </Link>

            <template v-if="canWrite">
              <button
                type="button"
                @click="openEditModal(c)"
                class="p-1 rounded-lg text-slate-400 hover:text-cyan-500 transition-colors"
                title="Editar"
              >
                <Edit class="w-4 h-4" />
              </button>
              <button
                type="button"
                @click="deleteCandidato(c)"
                class="p-1 rounded-lg text-slate-400 hover:text-rose-500 transition-colors"
                title="Eliminar"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Table View -->
    <div v-else class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-mono">
            <tr>
              <th class="px-6 py-4">Candidato</th>
              <th class="px-6 py-4">Partido / Coalición</th>
              <th class="px-6 py-4">Estado Político</th>
              <th class="px-6 py-4">Seguidores Redes</th>
              <th class="px-6 py-4">Campaña</th>
              <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
            <tr
              v-for="c in candidatos"
              :key="c.id"
              class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors"
            >
              <td class="px-6 py-4 flex items-center gap-3 font-bold text-slate-900 dark:text-slate-100">
                <img
                  :src="c.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.nombre_completo)}`"
                  class="w-9 h-9 rounded-full object-cover border"
                  :style="{ borderColor: c.color_hex || '#06b6d4' }"
                />
                <div>
                  <div class="flex items-center gap-1.5">
                    <span>{{ c.nombre_completo }}</span>
                    <span v-if="c.es_propio" class="text-[9px] font-mono font-bold px-1.5 py-0.2 rounded-sm bg-cyan-500 text-slate-950">
                      PROPIO
                    </span>
                  </div>
                  <span class="text-xs text-slate-400 font-normal block">{{ c.cargo_aspirado }}</span>
                </div>
              </td>
              <td class="px-6 py-4 text-slate-600 dark:text-slate-400 text-xs">
                {{ c.partido_coalicion }}
              </td>
              <td class="px-6 py-4">
                <Badge variant="estado" :value="c.estado_politico" size="sm" />
              </td>
              <td class="px-6 py-4 font-mono font-bold text-cyan-600 dark:text-cyan-400">
                {{ formatNumber(c.total_seguidores) }}
              </td>
              <td class="px-6 py-4 text-xs text-slate-500">
                {{ c.ciclo_campana }}
              </td>
              <td class="px-6 py-4 text-right space-x-2">
                <Link
                  :href="`/candidatos/${c.id}`"
                  class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline"
                >
                  Ficha
                </Link>
                <template v-if="canWrite">
                  <button @click="openEditModal(c)" class="text-slate-400 hover:text-cyan-500">
                    <Edit class="w-4 h-4 inline" />
                  </button>
                  <button @click="deleteCandidato(c)" class="text-slate-400 hover:text-rose-500">
                    <Trash2 class="w-4 h-4 inline" />
                  </button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create / Edit Candidate Modal -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
    >
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 max-w-2xl w-full shadow-2xl relative max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
            {{ isEditing ? 'Editar Candidato Político' : 'Registrar Nuevo Candidato' }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="mt-4 space-y-4 text-sm">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Nombre Completo *
              </label>
              <input
                v-model="form.nombre_completo"
                type="text"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Partido / Coalición *
              </label>
              <input
                v-model="form.partido_coalicion"
                type="text"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Cargo Aspirado / Actual
              </label>
              <input
                v-model="form.cargo_aspirado"
                type="text"
                placeholder="ej. Intendente, Concejal"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Estado Político *
              </label>
              <select
                v-model="form.estado_politico"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              >
                <option v-for="est in estados_disponibles" :key="est.key" :value="est.key">
                  {{ est.label }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Ciclo de Campaña *
              </label>
              <select
                v-model="form.ciclo_campana_id"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              >
                <option v-for="c in ciclos" :key="c.id" :value="c.id">
                  {{ c.anio }} - {{ c.nombre }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Territorio Asignado
              </label>
              <select
                v-model="form.territorio_id"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              >
                <option value="">Sin territorio específico</option>
                <option v-for="t in territorios" :key="t.id" :value="t.id">
                  {{ t.nombre }} ({{ t.tipo }})
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                URL Avatar / Foto
              </label>
              <input
                v-model="form.avatar_url"
                type="url"
                placeholder="https://ejemplo.com/foto.jpg"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Color Identificador (Hex)
              </label>
              <div class="flex items-center gap-2">
                <input
                  v-model="form.color_hex"
                  type="color"
                  class="w-10 h-10 rounded-xl cursor-pointer border border-slate-200 dark:border-slate-800 p-1"
                />
                <input
                  v-model="form.color_hex"
                  type="text"
                  class="flex-1 px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm font-mono"
                />
              </div>
            </div>
          </div>

          <!-- Es propio toggle -->
          <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <input
              id="es_propio"
              v-model="form.es_propio"
              type="checkbox"
              class="w-4 h-4 rounded text-cyan-500 focus:ring-cyan-400 cursor-pointer"
            />
            <label for="es_propio" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
              ¿Es el Candidato Propio de la Consultoría? (Habilita métricas internas y calibración de pauta)
            </label>
          </div>

          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
              Resumen Estratégico / Biografía
            </label>
            <textarea
              v-model="form.bio_resumen"
              rows="3"
              class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500"
            ></textarea>
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
              {{ isEditing ? 'Actualizar Candidato' : 'Guardar Candidato' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
