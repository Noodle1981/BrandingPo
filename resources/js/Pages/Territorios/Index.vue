<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  MapPin,
  Users,
  Vote,
  TrendingUp,
  Sparkles,
  Search,
  Sliders,
  ShieldCheck,
  Building2,
  Crown,
  Share2,
  CheckCircle,
  AlertCircle,
  BarChart3,
  PieChart,
  Target,
  Smartphone,
  Radio,
  Newspaper,
  Compass,
  Layers,
  Edit3,
  Plus,
  Save,
  ArrowRight
} from '@lucide/vue';

const props = defineProps({
  nivel_politico: {
    type: String,
    default: 'intendente',
  },
  nivel_label: {
    type: String,
    default: '🏛️ Intendente / Municipio',
  },
  es_gobernador: {
    type: Boolean,
    default: false,
  },
  provincia: {
    type: Object,
    default: null,
  },
  departamentos: {
    type: Array,
    default: () => [],
  },
  territorio_activo: {
    type: Object,
    default: null,
  },
  provincias: {
    type: Array,
    default: () => [
      'San Juan', 'Mendoza', 'Córdoba', 'Buenos Aires', 'Ciudad Autónoma de Buenos Aires',
      'Santa Fe', 'Entre Ríos', 'Tucumán', 'Salta', 'Chaco', 'Corrientes', 'Misiones',
      'Santiago del Estero', 'San Luis', 'La Rioja', 'Catamarca', 'Jujuy', 'Neuquén',
      'Río Negro', 'Chubut', 'Santa Cruz', 'La Pampa', 'Formosa', 'Tierra del Fuego'
    ],
  },
  metricas_macro: {
    type: Object,
    default: () => ({}),
  }
});

// Búsqueda y filtrado de departamentos
const searchDept = ref('');
const departamentosFiltrados = computed(() => {
  if (!searchDept.value) return props.departamentos;
  const q = searchDept.value.toLowerCase();
  return props.departamentos.filter(d => 
    d.nombre.toLowerCase().includes(q) || 
    (d.candidato_propio?.nombre_completo || '').toLowerCase().includes(q)
  );
});

// Territorio seleccionado para ver en detalle
const seleccionarTerritorio = (id) => {
  router.get('/territorios', { 
    territorio_id: id 
  }, { preserveScroll: true });
};

// Modal para crear / editar territorio
const isModalOpen = ref(false);
const isEditing = ref(false);
const isDetecting = ref(false);
const detectMessage = ref('');
const detectSuccess = ref(false);

const formTerritorio = useForm({
  id: null,
  nombre: '',
  provincia_seleccionada: props.provincia?.nombre || 'San Juan',
  tipo: 'departamento',
  parent_id: props.provincia?.id || null,
  codigo_indec: '',
  poblacion_total: 0,
  padron_electoral: 0,
  poblacion_urbana_pct: 70.0,
  poblacion_rural_pct: 30.0,
  hogares_nbi_pct: 15.0,
  latitud: null,
  longitud: null,
});

const openCreateModal = () => {
  isEditing.value = false;
  formTerritorio.reset();
  formTerritorio.provincia_seleccionada = props.provincia?.nombre || 'San Juan';
  formTerritorio.parent_id = props.provincia?.id || null;
  formTerritorio.tipo = 'departamento';
  detectMessage.value = '';
  isModalOpen.value = true;
};

const openEditModal = (terr) => {
  isEditing.value = true;
  formTerritorio.id = terr.id;
  formTerritorio.nombre = terr.nombre;
  formTerritorio.provincia_seleccionada = props.provincia?.nombre || 'San Juan';
  formTerritorio.tipo = terr.tipo;
  formTerritorio.parent_id = terr.parent_id || props.provincia?.id;
  formTerritorio.codigo_indec = terr.codigo_indec || '';
  formTerritorio.poblacion_total = terr.poblacion_total || 0;
  formTerritorio.padron_electoral = terr.padron_electoral || 0;
  formTerritorio.poblacion_urbana_pct = terr.poblacion_urbana_pct || 70.0;
  formTerritorio.poblacion_rural_pct = terr.poblacion_rural_pct || 30.0;
  formTerritorio.hogares_nbi_pct = terr.hogares_nbi_pct || 15.0;
  formTerritorio.latitud = terr.latitud;
  formTerritorio.longitud = terr.longitud;
  detectMessage.value = '';
  isModalOpen.value = true;
};

// Autodetección Georef / Demográfica con 1 Clic
const autoDetectGeoref = async () => {
  if (!formTerritorio.nombre) {
    detectMessage.value = 'Ingresa el nombre del departamento primero.';
    detectSuccess.value = false;
    return;
  }
  isDetecting.value = true;
  detectMessage.value = `Consultando Georef AR y datos censales en ${formTerritorio.provincia_seleccionada}...`;

  try {
    const response = await fetch('/territorios/auto-detect', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        nombre: formTerritorio.nombre,
        provincia: formTerritorio.provincia_seleccionada || 'San Juan',
      }),
    });

    const data = await response.json();
    if (data && data.success) {
      formTerritorio.nombre = data.nombre;
      if (data.codigo_indec) formTerritorio.codigo_indec = data.codigo_indec;
      if (data.latitud) formTerritorio.latitud = data.latitud;
      if (data.longitud) formTerritorio.longitud = data.longitud;
      if (data.poblacion_total) formTerritorio.poblacion_total = Number(data.poblacion_total);
      if (data.padron_electoral) formTerritorio.padron_electoral = Number(data.padron_electoral);
      if (data.poblacion_urbana_pct) formTerritorio.poblacion_urbana_pct = Number(data.poblacion_urbana_pct);
      if (data.poblacion_rural_pct) formTerritorio.poblacion_rural_pct = Number(data.poblacion_rural_pct);
      if (data.hogares_nbi_pct) formTerritorio.hogares_nbi_pct = Number(data.hogares_nbi_pct);

      detectSuccess.value = true;
      detectMessage.value = data.mensaje || `¡Datos de ${data.nombre} detectados con éxito!`;
    } else {
      detectSuccess.value = false;
      detectMessage.value = data.mensaje || 'No se encontraron datos automáticos.';
    }
  } catch (err) {
    detectSuccess.value = false;
    detectMessage.value = 'Error al conectar con el servicio demográfico.';
  } finally {
    isDetecting.value = false;
  }
};

const saveTerritorio = () => {
  if (isEditing.value) {
    formTerritorio.put(`/territorios/${formTerritorio.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  } else {
    formTerritorio.post('/territorios', {
      preserveScroll: true,
      onSuccess: () => {
        isModalOpen.value = false;
      }
    });
  }
};
</script>

<template>
  <Head title="Inteligencia Demográfica & Mapa Territorial" />

  <WarRoomLayout>
    <div class="space-y-6">
      <!-- 1. Cabecera Principal & Selector de Escala Multi-Nivel -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
                <Compass class="w-6 h-6" />
              </span>
              <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                Inteligencia Demográfica & Mapa de Situación
              </h1>
              <span class="text-[11px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-purple-500/15 text-purple-400 border border-purple-500/30 uppercase">
                San Juan (19 Departamentos)
              </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
              Radiografía censal, pirámide de edades de votantes, perfil urbano/rural y estrategia por red social.
            </p>
          </div>

          <!-- Nivel Político del Workspace Activo -->
          <div class="flex items-center gap-2 px-4 py-2 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-700 dark:text-cyan-300 self-start lg:self-center">
            <component :is="props.es_gobernador ? Crown : Building2" class="w-4 h-4 text-cyan-500 shrink-0" />
            <span class="text-xs font-bold font-mono">
              {{ props.nivel_label || '🏛️ Intendente / Municipio' }}
            </span>
          </div>
        </div>

        <!-- Banner Explicativo según el Nivel Político de la Campaña -->
        <div class="p-4 rounded-2xl border text-xs leading-relaxed flex items-center justify-between gap-3 flex-wrap bg-cyan-500/10 border-cyan-500/30 text-cyan-700 dark:text-cyan-300">
          <div class="flex items-center gap-2.5">
            <Sparkles class="w-5 h-5 shrink-0" />
            <span v-if="!props.es_gobernador">
              <strong>Campaña Municipal / Local:</strong> Análisis hiperlocal enfocado en el "metro cuadrado", pirámide barrial de votantes y penetración digital en <strong>{{ territorio_activo?.nombre || 'Municipio' }}</strong>.
            </span>
            <span v-else>
              <strong>Campaña Provincial:</strong> Diagnóstico macro sistémico para la <strong>{{ provincia?.nombre || 'Provincia' }}</strong> ({{ Number(metricas_macro?.padron_total_provincial || 610000).toLocaleString('es-AR') }} electores) y cobertura departamento por departamento.
            </span>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <Link
              href="/territorios/impacto-electoral"
              class="px-3.5 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow-sm hover:scale-102 transition-all cursor-pointer shrink-0"
              title="Abrir Matriz de Impacto & Penetración Electoral"
            >
              <Target class="w-4 h-4" />
              <span>Matriz de Impacto & Padrón</span>
            </Link>

            <button
              type="button"
              @click="openCreateModal"
              class="px-3.5 py-1.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs flex items-center gap-1.5 shadow-sm hover:scale-102 transition-all cursor-pointer shrink-0"
            >
              <Plus class="w-4 h-4" />
              <span>Agregar Territorio</span>
            </button>
          </div>
        </div>
      </div>

      <!-- 2. KPIs Macro del Territorio Activo -->
      <div v-if="territorio_activo" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Padrón Electoral -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">
              🗳️ Padrón Electoral
            </span>
            <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-500">
              <Vote class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold font-mono text-cyan-600 dark:text-cyan-400">
            {{ Number(territorio_activo.padron_electoral).toLocaleString('es-AR') }}
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Electores habilitados en {{ territorio_activo.nombre }}
          </p>
        </div>

        <!-- Población Total & Densidad -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">
              👥 Población Total
            </span>
            <span class="p-2 rounded-xl bg-purple-500/10 text-purple-500">
              <Users class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold font-mono text-purple-600 dark:text-purple-400">
            {{ Number(territorio_activo.poblacion_total).toLocaleString('es-AR') }}
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Habitantes censales (Censo INDEC)
          </p>
        </div>

        <!-- Perfil Urbano vs Rural -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">
              🚜 Urbano vs. Campo
            </span>
            <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500">
              <PieChart class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold font-mono text-emerald-600 dark:text-emerald-400">
            {{ territorio_activo.poblacion_urbana_pct }}% <span class="text-xs font-normal text-slate-500">urb.</span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            {{ territorio_activo.poblacion_rural_pct }}% territorio rural / viñatero
          </p>
        </div>

        <!-- Voto Joven (16 a 29 años) -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 font-mono">
              ⚡ Voto Joven (16-29)
            </span>
            <span class="p-2 rounded-xl bg-amber-500/10 text-amber-500">
              <Smartphone class="w-4 h-4" />
            </span>
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold font-mono text-amber-600 dark:text-amber-400">
            ~29.0% <span class="text-xs font-normal text-slate-500 font-mono">({{ Number(territorio_activo.piramide?.resumen_voto_joven || 0).toLocaleString('es-AR') }})</span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Decisivo en TikTok e Instagram
          </p>
        </div>
      </div>

      <!-- 3. PIRÁMIDE ETARIA ELECTORAL & RECOMENDADOR ESTRATÉGICO -->
      <div v-if="territorio_activo?.piramide" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- A. Pirámide Etaria de Votantes -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
          <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-2">
            <div>
              <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <BarChart3 class="w-5 h-5 text-cyan-500" />
                <span>Pirámide Etaria Electoral: {{ territorio_activo.nombre }}</span>
              </h2>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Distribución de votantes por franja etaria y red social de mayor impacto.
              </p>
            </div>

            <button
              type="button"
              @click="openEditModal(territorio_activo)"
              class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <Edit3 class="w-3.5 h-3.5" />
              <span>Editar Demografía</span>
            </button>
          </div>

          <!-- Barras de la Pirámide -->
          <div class="space-y-4 font-mono">
            <div
              v-for="grupo in territorio_activo.piramide.grupos_etarios"
              :key="grupo.id"
              class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2"
            >
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                  <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: grupo.color_hex }"></span>
                  <span class="font-bold text-slate-900 dark:text-slate-100">{{ grupo.rango }}</span>
                  <span class="text-[10px] text-slate-500 font-normal">({{ grupo.categoria }})</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="text-slate-500">{{ Number(grupo.electores).toLocaleString('es-AR') }} electores</span>
                  <span class="font-bold text-sm" :style="{ color: grupo.color_hex }">{{ grupo.porcentaje }}%</span>
                </div>
              </div>

              <!-- Barra de Progreso -->
              <div class="w-full h-3 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :style="{ width: `${grupo.porcentaje * 2.5}%`, backgroundColor: grupo.color_hex }"
                ></div>
              </div>

              <div class="flex items-center justify-between text-[11px] pt-1 text-slate-500 flex-wrap gap-2">
                <span class="font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                  📱 Red Óptima: <strong class="text-cyan-500">{{ grupo.red_principal }}</strong>
                </span>
                <span class="text-[10px] text-slate-400">
                  Temas: {{ grupo.temas_clave.join(' • ') }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- B. Recomendador de Pauta & Discurso -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
          <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Target class="w-5 h-5 text-purple-500" />
              <span>Estrategia de Pauta Sugerida</span>
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Distribución de inversión según la demografía territorial.
            </p>
          </div>

          <div v-if="territorio_activo.estrategia" class="space-y-4 text-xs">
            <!-- Reparto de Pauta -->
            <div class="space-y-2.5">
              <div
                v-for="(pauta, idx) in territorio_activo.estrategia.distribucion_pauta"
                :key="idx"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5"
              >
                <div class="flex items-center justify-between font-mono">
                  <span class="font-bold text-slate-800 dark:text-slate-200">{{ pauta.plataforma }}</span>
                  <span class="px-2 py-0.5 rounded-full bg-cyan-500/15 text-cyan-400 font-bold text-xs">
                    {{ pauta.porcentaje_sugerido }}% Presupuesto
                  </span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                  {{ pauta.audiencia_objetivo }}
                </p>
                <div class="text-[10px] text-slate-400 font-mono">
                  💡 Mensaje: {{ pauta.tipo_mensaje }}
                </div>
              </div>
            </div>

            <!-- Eje Discursivo Recomendado -->
            <div class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/30 text-purple-800 dark:text-purple-300 space-y-1">
              <span class="font-bold text-xs uppercase font-mono block">Eje Discursivo Recomendado:</span>
              <p class="text-xs leading-relaxed">
                {{ territorio_activo.estrategia.recomendacion_eje_discursivo }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. MATRIZ Y MAPA DE LOS 19 DEPARTAMENTOS DE SAN JUAN (RED DE INTENDENTES) -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Layers class="w-5 h-5 text-emerald-500" />
              <span>Matriz Departamental de San Juan (19 Territorios)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Auditoría territorial y cobertura del padrón por candidato oficial.
            </p>
          </div>

          <!-- Buscador -->
          <div class="relative w-full sm:w-64">
            <input
              v-model="searchDept"
              type="text"
              placeholder="Buscar departamento o candidato..."
              class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
            />
            <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          </div>
        </div>

        <!-- Tabla de Departamentos -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs border-collapse">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 uppercase font-mono text-[11px]">
                <th class="py-3 px-4 font-bold">Departamento</th>
                <th class="py-3 px-4 font-bold">Padrón Electoral</th>
                <th class="py-3 px-4 font-bold">Población</th>
                <th class="py-3 px-4 font-bold">% Urbano / Campo</th>
                <th class="py-3 px-4 font-bold">Candidato Propio</th>
                <th class="py-3 px-4 font-bold">Cobertura Padrón</th>
                <th class="py-3 px-4 font-bold text-right">Acción</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
              <tr
                v-for="dept in departamentosFiltrados"
                :key="dept.id"
                class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors"
                :class="{ 'bg-cyan-500/5 dark:bg-cyan-500/10 font-bold': territorio_activo?.id === dept.id }"
              >
                <!-- Departamento -->
                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                  <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                  <span>{{ dept.nombre }}</span>
                </td>

                <!-- Padrón -->
                <td class="py-3.5 px-4 text-slate-700 dark:text-slate-300">
                  {{ Number(dept.padron_electoral).toLocaleString('es-AR') }}
                </td>

                <!-- Población -->
                <td class="py-3.5 px-4 text-slate-500">
                  {{ Number(dept.poblacion_total).toLocaleString('es-AR') }}
                </td>

                <!-- % Urbano -->
                <td class="py-3.5 px-4">
                  <span class="text-emerald-500 font-bold">{{ dept.poblacion_urbana_pct }}%</span>
                  <span class="text-slate-400 text-[10px]"> / {{ dept.poblacion_rural_pct }}%</span>
                </td>

                <!-- Candidato Propio -->
                <td class="py-3.5 px-4">
                  <div v-if="dept.candidato_propio" class="flex items-center gap-2">
                    <img
                      :src="dept.candidato_propio.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(dept.candidato_propio.nombre_completo)}`"
                      class="w-6 h-6 rounded-full object-cover border border-cyan-500"
                    />
                    <span class="text-slate-900 dark:text-slate-100">{{ dept.candidato_propio.nombre_completo }}</span>
                  </div>
                  <span v-else class="text-[10px] text-slate-400 uppercase">Sin definir</span>
                </td>

                <!-- Cobertura Padrón -->
                <td class="py-3.5 px-4">
                  <div v-if="dept.candidato_propio?.cobertura_padron_pct > 0" class="flex items-center gap-2">
                    <div class="w-16 h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                      <div
                        class="h-full bg-cyan-500 rounded-full"
                        :style="{ width: `${Math.min(dept.candidato_propio.cobertura_padron_pct, 100)}%` }"
                      ></div>
                    </div>
                    <span class="text-cyan-500 font-bold text-[11px]">{{ dept.candidato_propio.cobertura_padron_pct }}%</span>
                  </div>
                  <span v-else class="text-slate-400 text-[10px]">-</span>
                </td>

                <!-- Acción -->
                <td class="py-3.5 px-4 text-right">
                  <button
                    type="button"
                    @click="seleccionarTerritorio(dept.id)"
                    class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-cyan-500 hover:text-slate-950 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all cursor-pointer flex items-center gap-1 ml-auto"
                  >
                    <span>Ver Pirámide</span>
                    <ArrowRight class="w-3 h-3" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL CREAR / EDITAR TERRITORIO CON AUTO-DETECCIÓN GEOREF -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <MapPin class="w-5 h-5 text-cyan-500" />
            <span>{{ isEditing ? 'Editar Territorio' : 'Registrar Nuevo Territorio' }}</span>
          </h3>
          <button
            type="button"
            @click="isModalOpen = false"
            class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          >
            &times;
          </button>
        </div>

        <form @submit.prevent="saveTerritorio" class="space-y-4">
          <!-- Selector de Provincia -->
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Provincia *
            </label>
            <select
              v-model="formTerritorio.provincia_seleccionada"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500 font-mono"
            >
              <option v-for="prov in provincias" :key="prov" :value="prov">
                {{ prov }}
              </option>
            </select>
          </div>

          <!-- Nombre del Departamento + Auto-detección -->
          <div>
            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Departamento / Municipio *
              </label>
              <button
                type="button"
                @click="autoDetectGeoref"
                :disabled="isDetecting || !formTerritorio.nombre"
                class="px-2.5 py-1 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-[11px] font-mono flex items-center gap-1 transition-all shadow-sm cursor-pointer disabled:opacity-50"
              >
                <Sparkles class="w-3 h-3" />
                <span>{{ isDetecting ? 'Detectando...' : '⚡ Detectar con 1 Clic (Georef/INDEC)' }}</span>
              </button>
            </div>

            <input
              v-model="formTerritorio.nombre"
              type="text"
              required
              placeholder="ej. Albardón, Rawson, Capital..."
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500 font-mono"
            />

            <div v-if="detectMessage" class="mt-2 flex items-center gap-2 text-xs font-mono" :class="detectSuccess ? 'text-emerald-500' : 'text-amber-500'">
              <CheckCircle v-if="detectSuccess" class="w-4 h-4" />
              <AlertCircle v-else class="w-4 h-4" />
              <span>{{ detectMessage }}</span>
            </div>
          </div>

          <!-- Métricas Electorales -->
          <div class="grid grid-cols-2 gap-3 font-mono">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Padrón Electoral *</label>
              <input
                v-model.number="formTerritorio.padron_electoral"
                type="number"
                min="0"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-cyan-600 dark:text-cyan-400 font-extrabold"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Población Total</label>
              <input
                v-model.number="formTerritorio.poblacion_total"
                type="number"
                min="0"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100"
              />
            </div>
          </div>

          <!-- Urbano vs Rural -->
          <div class="grid grid-cols-2 gap-3 font-mono">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">% Población Urbana</label>
              <input
                v-model.number="formTerritorio.poblacion_urbana_pct"
                type="number"
                step="0.1"
                min="0"
                max="100"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">% Población Rural</label>
              <input
                v-model.number="formTerritorio.poblacion_rural_pct"
                type="number"
                step="0.1"
                min="0"
                max="100"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100"
              />
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="isModalOpen = false"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="formTerritorio.processing"
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-sm"
            >
              {{ isEditing ? 'Guardar Cambios' : 'Registrar Territorio' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
