<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';

// Componentes Modulares de Medios
import MedioHeader from '../../Components/Medios/MedioHeader.vue';
import MedioUmbralAlerta from '../../Components/Medios/MedioUmbralAlerta.vue';
import MedioPestanasNav from '../../Components/Medios/MedioPestanasNav.vue';
import MedioDirectorioGrid from '../../Components/Medios/MedioDirectorioGrid.vue';
import MedioWebFeed from '../../Components/Medios/MedioWebFeed.vue';
import MedioFacebookFeed from '../../Components/Medios/MedioFacebookFeed.vue';
import MedioSesgoMatriz from '../../Components/Medios/MedioSesgoMatriz.vue';
import MedioFormModal from '../../Components/Medios/MedioFormModal.vue';
import MedioNotaModal from '../../Components/Medios/MedioNotaModal.vue';

const props = defineProps({
  medios: {
    type: Array,
    default: () => [],
  },
  notas: {
    type: Array,
    default: () => [],
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  pestana_activa: {
    type: String,
    default: 'directorio',
  },
  filtros: {
    type: Object,
    default: () => ({}),
  },
  umbral: {
    type: Object,
    required: true,
  },
  resumen_tonos: {
    type: Object,
    default: () => ({}),
  },
  sentimientos_facebook: {
    type: Object,
    default: () => ({}),
  },
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

// Pestaña Activa
const pestana = ref(props.pestana_activa || 'directorio');
const cambiarPestana = (nuevaPestana) => {
  pestana.value = nuevaPestana;
};

// Estado de Modales
const isMedioModalOpen = ref(false);
const medioParaEditar = ref(null);

const isNotaModalOpen = ref(false);
const notaParaEditar = ref(null);
const initialOrigenNota = ref('web');

// Estado de Sincronización
const sincronizandoTodos = ref(false);
const sincronizandoMedioId = ref(null);

// Acciones de Medios
const abrirCrearMedio = () => {
  medioParaEditar.value = null;
  isMedioModalOpen.value = true;
};

const abrirEditarMedio = (medio) => {
  medioParaEditar.value = medio;
  isMedioModalOpen.value = true;
};

const handleSincronizarMedio = (medio) => {
  sincronizandoMedioId.value = medio.id;
  router.post(`/medios/${medio.id}/sincronizar`, {}, {
    preserveScroll: true,
    onFinish: () => {
      sincronizandoMedioId.value = null;
    },
  });
};

const handleSincronizarTodos = () => {
  sincronizandoTodos.value = true;
  router.post('/medios/sincronizar-todos', {}, {
    preserveScroll: true,
    onFinish: () => {
      sincronizandoTodos.value = false;
    },
  });
};

const handleEliminarMedio = (medio) => {
  if (confirm(`¿Eliminar el medio "${medio.nombre}" y todas sus noticias asociadas?`)) {
    router.delete(`/medios/${medio.id}`, {
      preserveScroll: true,
    });
  }
};

// Acciones de Notas
const abrirCrearNota = ({ origen_tipo = 'web' } = {}) => {
  notaParaEditar.value = null;
  initialOrigenNota.value = origen_tipo;
  isNotaModalOpen.value = true;
};

const abrirEditarNota = (nota) => {
  notaParaEditar.value = nota;
  initialOrigenNota.value = nota.origen_tipo || 'web';
  isNotaModalOpen.value = true;
};

const handleEliminarNota = (nota) => {
  if (confirm(`¿Eliminar la nota "${nota.titulo}"?`)) {
    router.delete(`/medios/clipping/${nota.id}`, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head title="Observatorio de Medios & Prensa" />

  <WarRoomLayout>
    <div class="space-y-6 max-w-7xl mx-auto">
      <!-- 1. Cabecera Principal con Sincronismo Maestro -->
      <MedioHeader
        :can-write="canWrite"
        :sincronizando="sincronizandoTodos"
        :total-medios="medios.length"
        @open-create-modal="abrirCrearMedio"
        @sincronizar-todos="handleSincronizarTodos"
      />

      <!-- 2. Alerta Pedagógica del Umbral (Mínimo 5 Medios) -->
      <MedioUmbralAlerta :umbral="umbral" />

      <!-- 3. Navegación por Pestañas de la Sala de Situación -->
      <MedioPestanasNav
        :pestana-activa="pestana"
        :total-medios="medios.length"
        :total-web="resumen_tonos.web_count || 0"
        :total-facebook="resumen_tonos.facebook_count || 0"
        :total-notas="resumen_tonos.total || 0"
        @cambiar-pestana="cambiarPestana"
      />

      <!-- 4. Contenido Dinámico según la Pestaña Activa -->
      <!-- Pestaña 1: Directorio & Gestión de Medios -->
      <MedioDirectorioGrid
        v-if="pestana === 'directorio'"
        :medios="medios"
        :can-write="canWrite"
        :sincronizando-id="sincronizandoMedioId"
        @open-create-modal="abrirCrearMedio"
        @editar-medio="abrirEditarMedio"
        @sincronizar-medio="handleSincronizarMedio"
        @eliminar-medio="handleEliminarMedio"
      />

      <!-- Pestaña 2: Clipping Web Oficial (RSS / Portales) -->
      <MedioWebFeed
        v-else-if="pestana === 'web'"
        :notas="notas"
        :can-write="canWrite"
        :candidatos="candidatos"
        :medios="medios"
        :filtros="filtros"
        @open-nota-modal="abrirCrearNota"
        @editar-nota="abrirEditarNota"
        @eliminar-nota="handleEliminarNota"
      />

      <!-- Pestaña 3: Medios en Facebook (Métricas de Sentimientos) -->
      <MedioFacebookFeed
        v-else-if="pestana === 'facebook'"
        :notas="notas"
        :can-write="canWrite"
        :sentimientos="sentimientos_facebook"
        @open-nota-modal="abrirCrearNota"
        @editar-nota="abrirEditarNota"
        @eliminar-nota="handleEliminarNota"
      />

      <!-- Pestaña 4: Matriz de Sesgo & Tono Editorial -->
      <MedioSesgoMatriz
        v-else-if="pestana === 'sesgo'"
        :medios="medios"
        :notas="notas"
        :resumen-tonos="resumen_tonos"
        :umbral="umbral"
      />
    </div>

    <!-- Modales -->
    <MedioFormModal
      :is-open="isMedioModalOpen"
      :medio-editar="medioParaEditar"
      @close="isMedioModalOpen = false"
    />

    <MedioNotaModal
      :is-open="isNotaModalOpen"
      :nota-editar="notaParaEditar"
      :medios="medios"
      :candidatos="candidatos"
      :initial-origen="initialOrigenNota"
      @close="isNotaModalOpen = false"
    />
  </WarRoomLayout>
</template>
