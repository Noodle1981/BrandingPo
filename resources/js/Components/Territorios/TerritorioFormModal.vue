<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { MapPin, Sparkles, CheckCircle, AlertCircle } from '@lucide/vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  territorioAEditar: {
    type: Object,
    default: null,
  },
  provinciaDefault: {
    type: String,
    default: 'San Juan',
  },
  provinciaId: {
    type: [Number, String],
    default: null,
  },
  provincias: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close']);

const isDetecting = ref(false);
const detectMessage = ref('');
const detectSuccess = ref(false);

const form = useForm({
  id: null,
  nombre: '',
  provincia_seleccionada: props.provinciaDefault || 'San Juan',
  tipo: 'departamento',
  parent_id: props.provinciaId || null,
  codigo_indec: '',
  poblacion_total: 0,
  padron_electoral: 0,
  poblacion_urbana_pct: 70.0,
  poblacion_rural_pct: 30.0,
  hogares_nbi_pct: 15.0,
  latitud: null,
  longitud: null,
});

watch(() => props.isOpen, (open) => {
  if (open) {
    detectMessage.value = '';
    detectSuccess.value = false;
    if (props.territorioAEditar) {
      form.id = props.territorioAEditar.id;
      form.nombre = props.territorioAEditar.nombre;
      form.provincia_seleccionada = props.provinciaDefault || 'San Juan';
      form.tipo = props.territorioAEditar.tipo || 'departamento';
      form.parent_id = props.territorioAEditar.parent_id || props.provinciaId;
      form.codigo_indec = props.territorioAEditar.codigo_indec || '';
      form.poblacion_total = props.territorioAEditar.poblacion_total || 0;
      form.padron_electoral = props.territorioAEditar.padron_electoral || 0;
      form.poblacion_urbana_pct = props.territorioAEditar.poblacion_urbana_pct || 70.0;
      form.poblacion_rural_pct = props.territorioAEditar.poblacion_rural_pct || 30.0;
      form.hogares_nbi_pct = props.territorioAEditar.hogares_nbi_pct || 15.0;
      form.latitud = props.territorioAEditar.latitud;
      form.longitud = props.territorioAEditar.longitud;
    } else {
      form.reset();
      form.provincia_seleccionada = props.provinciaDefault || 'San Juan';
      form.parent_id = props.provinciaId || null;
      form.tipo = 'departamento';
    }
  }
});

// Autodetección Georef / Demográfica con 1 Clic
const autoDetectGeoref = async () => {
  if (!form.nombre) {
    detectMessage.value = 'Ingresa el nombre del departamento primero.';
    detectSuccess.value = false;
    return;
  }
  isDetecting.value = true;
  detectMessage.value = `Consultando Georef AR y datos censales en ${form.provincia_seleccionada}...`;

  try {
    const response = await fetch('/territorios/auto-detect', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        nombre: form.nombre,
        provincia: form.provincia_seleccionada || 'San Juan',
      }),
    });

    const data = await response.json();
    if (data && data.success) {
      form.nombre = data.nombre;
      if (data.codigo_indec) form.codigo_indec = data.codigo_indec;
      if (data.latitud) form.latitud = data.latitud;
      if (data.longitud) form.longitud = data.longitud;
      if (data.poblacion_total) form.poblacion_total = Number(data.poblacion_total);
      if (data.padron_electoral) form.padron_electoral = Number(data.padron_electoral);
      if (data.poblacion_urbana_pct) form.poblacion_urbana_pct = Number(data.poblacion_urbana_pct);
      if (data.poblacion_rural_pct) form.poblacion_rural_pct = Number(data.poblacion_rural_pct);
      if (data.hogares_nbi_pct) form.hogares_nbi_pct = Number(data.hogares_nbi_pct);

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

const submit = () => {
  if (form.id) {
    form.put(`/territorios/${form.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('close');
      }
    });
  } else {
    form.post('/territorios', {
      preserveScroll: true,
      onSuccess: () => {
        emit('close');
      }
    });
  }
};
</script>

<template>
  <!-- MODAL CREAR / EDITAR TERRITORIO CON AUTO-DETECCIÓN GEOREF -->
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
  >
    <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
        <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
          <MapPin class="w-5 h-5 text-cyan-500" />
          <span>{{ form.id ? 'Editar Territorio' : 'Registrar Nuevo Territorio' }}</span>
        </h3>
        <button
          type="button"
          @click="emit('close')"
          class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
        >
          &times;
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Selector de Provincia -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            Provincia *
          </label>
          <select
            v-model="form.provincia_seleccionada"
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
              :disabled="isDetecting || !form.nombre"
              class="px-2.5 py-1 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-[11px] font-mono flex items-center gap-1 transition-all shadow-sm cursor-pointer disabled:opacity-50"
            >
              <Sparkles class="w-3 h-3" />
              <span>{{ isDetecting ? 'Detectando...' : '⚡ Detectar con 1 Clic (Georef/INDEC)' }}</span>
            </button>
          </div>

          <input
            v-model="form.nombre"
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
              v-model.number="form.padron_electoral"
              type="number"
              min="0"
              required
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-cyan-600 dark:text-cyan-400 font-extrabold"
            />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Población Total</label>
            <input
              v-model.number="form.poblacion_total"
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
              v-model.number="form.poblacion_urbana_pct"
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
              v-model.number="form.poblacion_rural_pct"
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
            @click="emit('close')"
            class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-sm"
          >
            {{ form.id ? 'Guardar Cambios' : 'Registrar Territorio' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
