<script setup>
import { ref, computed, watch } from 'vue';
import { 
  Filter, 
  Calendar, 
  X, 
  UserCheck, 
  Building2, 
  Smile, 
  RotateCcw,
  Sparkles
} from '@lucide/vue';

const props = defineProps({
  candidatos: {
    type: Array,
    default: () => [],
  },
  medios: {
    type: Array,
    default: () => [],
  },
  aniosDisponibles: {
    type: Array,
    default: () => [],
  },
  mesesDisponibles: {
    type: Array,
    default: () => [],
  },
  filtros: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['filter-change', 'clear-filters']);

const selectedAnio = ref(props.filtros.anio || '');
const selectedMes = ref(props.filtros.mes || '');
const selectedCandidato = ref(props.filtros.candidato_id || '');
const selectedMedio = ref(props.filtros.medio_id || '');
const selectedTono = ref(props.filtros.tono || '');

watch(() => props.filtros, (newFiltros) => {
  selectedAnio.value = newFiltros.anio || '';
  selectedMes.value = newFiltros.mes || '';
  selectedCandidato.value = newFiltros.candidato_id || '';
  selectedMedio.value = newFiltros.medio_id || '';
  selectedTono.value = newFiltros.tono || '';
}, { deep: true });

const hayFiltrosActivos = computed(() => {
  return !!(
    selectedAnio.value || 
    selectedMes.value || 
    selectedCandidato.value || 
    selectedMedio.value || 
    selectedTono.value
  );
});

const esHistoricoCompleto = computed(() => {
  return !selectedAnio.value && !selectedMes.value;
});

const periodoLabel = computed(() => {
  if (esHistoricoCompleto.value) {
    return 'Histórico Completo';
  }
  const mesObj = props.mesesDisponibles.find(m => m.numero === selectedMes.value);
  const mesNombre = mesObj ? mesObj.nombre : (selectedMes.value ? `Mes ${selectedMes.value}` : '');
  
  if (mesNombre && selectedAnio.value) {
    return `${mesNombre} ${selectedAnio.value}`;
  }
  if (selectedAnio.value) {
    return `Año ${selectedAnio.value}`;
  }
  return mesNombre || 'Período Personalizado';
});

const emitChange = () => {
  emit('filter-change', {
    anio: selectedAnio.value || null,
    mes: selectedMes.value || null,
    candidato_id: selectedCandidato.value || null,
    medio_id: selectedMedio.value || null,
    tono: selectedTono.value || null,
  });
};

const setHistorico = () => {
  selectedAnio.value = '';
  selectedMes.value = '';
  emitChange();
};

const onAnioChange = () => {
  // Al cambiar año, si el mes seleccionado ya no existe en el año, se limpia
  emitChange();
};

const handleClear = () => {
  selectedAnio.value = '';
  selectedMes.value = '';
  selectedCandidato.value = '';
  selectedMedio.value = '';
  selectedTono.value = '';
  emit('clear-filters');
};
</script>

<template>
  <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3.5">
    <!-- Top Bar: Title, Active Badge & Reset -->
    <div class="flex flex-wrap items-center justify-between gap-2.5">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs font-mono font-extrabold uppercase text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
          <Filter class="w-3.5 h-3.5 text-cyan-500" />
          <span>Filtros Estratégicos</span>
        </span>

        <!-- Badge Período Auditado -->
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold border transition-all"
          :class="esHistoricoCompleto 
            ? 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700' 
            : 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20'"
        >
          <Calendar class="w-3 h-3" />
          <span>{{ periodoLabel }}</span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <!-- Botón Rápido Histórico -->
        <button
          type="button"
          @click="setHistorico"
          class="px-2.5 py-1 rounded-xl text-xs font-mono font-bold transition-all cursor-pointer border"
          :class="esHistoricoCompleto
            ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900 border-transparent shadow-2xs'
            : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
          title="Ver todo el período histórico disponible"
        >
          🏛️ Histórico
        </button>

        <!-- Botón Limpiar Todos los Filtros -->
        <button
          v-if="hayFiltrosActivos"
          type="button"
          @click="handleClear"
          class="text-xs text-rose-500 hover:text-rose-400 font-bold flex items-center gap-1 px-2.5 py-1 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 transition-all cursor-pointer"
          title="Restablecer todos los filtros"
        >
          <X class="w-3.5 h-3.5" />
          <span>Limpiar</span>
        </button>
      </div>
    </div>

    <!-- Selectores en Grid Adaptativa -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 font-mono text-xs">
      <!-- 1. Año -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Año</label>
        <select
          v-model="selectedAnio"
          @change="onAnioChange"
          class="w-full px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all cursor-pointer"
        >
          <option value="">Año (Todos)</option>
          <option v-for="a in aniosDisponibles" :key="a" :value="a">
            {{ a }}
          </option>
        </select>
      </div>

      <!-- 2. Mes -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Mes</label>
        <select
          v-model="selectedMes"
          @change="emitChange"
          class="w-full px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all cursor-pointer"
        >
          <option value="">Mes (Todos)</option>
          <option v-for="m in mesesDisponibles" :key="m.numero" :value="m.numero">
            {{ m.nombre }}
          </option>
        </select>
      </div>

      <!-- 3. Candidato -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Candidato</label>
        <select
          v-model="selectedCandidato"
          @change="emitChange"
          class="w-full px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all cursor-pointer"
        >
          <option value="">👤 Todos</option>
          <option v-for="c in candidatos" :key="c.id" :value="c.id">
            {{ c.nombre_completo }} {{ c.es_propio ? '(Propio)' : '(Rival)' }}
          </option>
        </select>
      </div>

      <!-- 4. Medio / Portal -->
      <div>
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Portal / Medio</label>
        <select
          v-model="selectedMedio"
          @change="emitChange"
          class="w-full px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all cursor-pointer"
        >
          <option value="">📰 Todos los Medios</option>
          <option v-for="m in medios" :key="m.id" :value="m.id">
            {{ m.nombre }}
          </option>
        </select>
      </div>

      <!-- 5. Tono Editorial -->
      <div class="col-span-2 sm:col-span-1">
        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tono Editorial</label>
        <select
          v-model="selectedTono"
          @change="emitChange"
          class="w-full px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 focus:outline-none transition-all cursor-pointer"
        >
          <option value="">🎭 Todos los Tonos</option>
          <option value="favorable">🟢 Favorable</option>
          <option value="neutro">🟡 Neutro</option>
          <option value="critico">🔴 Crítico</option>
        </select>
      </div>
    </div>
  </div>
</template>
