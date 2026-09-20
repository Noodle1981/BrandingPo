<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import CandidatoHeader from '../../Components/Candidatos/CandidatoHeader.vue';
import CandidatoEditarModal from '../../Components/Candidatos/CandidatoEditarModal.vue';
import CandidatoKpisTerritoriales from '../../Components/Candidatos/CandidatoKpisTerritoriales.vue';
import CandidatoCanalesGrid from '../../Components/Candidatos/CandidatoCanalesGrid.vue';
import CandidatoCanalFicha from '../../Components/Candidatos/CandidatoCanalFicha.vue';
import CandidatoCanalConfigModal from '../../Components/Candidatos/CandidatoCanalConfigModal.vue';
import CandidatoFeedSection from '../../Components/Candidatos/CandidatoFeedSection.vue';

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  redes: {
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
  publicaciones: {
    type: Array,
    default: () => [],
  },
  ejes: {
    type: Array,
    default: () => [],
  },
});

// Pestaña de red social activa seleccionada
const selectedPlatformKey = ref(props.redes[0]?.key || 'instagram');

const currentRed = computed(() => {
  return props.redes.find(r => r.key === selectedPlatformKey.value) || props.redes[0] || {};
});

// Modales
const isEditingCandidato = ref(false);
const isConfigModalOpen = ref(false);

const openConfigModal = (platformKey = null) => {
  if (platformKey) {
    selectedPlatformKey.value = platformKey;
  }
  isConfigModalOpen.value = true;
};
</script>

<template>
  <Head :title="`Rival: ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <div class="space-y-6">
      <!-- 1. Cabecera del Candidato Rival Opositor -->
      <CandidatoHeader
        :candidato="candidato"
        tema="rival"
        @editar="isEditingCandidato = true"
      />

      <!-- 2. KPIs de Audiencia Real y Padrón Territorial del Rival (Tiers) -->
      <CandidatoKpisTerritoriales
        :candidato="candidato"
        tema="rival"
      />

      <!-- 3. Semáforo y Selector de Canales del Rival en 1 Fila -->
      <CandidatoCanalesGrid
        :redes="redes"
        v-model="selectedPlatformKey"
        tema="rival"
        @configurar="openConfigModal"
      />

      <!-- 4. Ficha Técnica de Punto Cero del Canal del Rival y Acciones -->
      <CandidatoCanalFicha
        :red="currentRed"
        :candidato="candidato"
        tema="rival"
        @configurar="openConfigModal"
      />

      <!-- 5. Muro de Publicaciones & Espionaje del Rival, Fast-Flow y Edición -->
      <CandidatoFeedSection
        :candidato="candidato"
        :publicaciones="publicaciones"
        :ejes="ejes"
        tema="rival"
      />

      <!-- Modales Modulares -->
      <CandidatoEditarModal
        :show="isEditingCandidato"
        :candidato="candidato"
        :ciclos="ciclos"
        :territorios="territorios"
        tema="rival"
        @close="isEditingCandidato = false"
      />

      <CandidatoCanalConfigModal
        :show="isConfigModalOpen"
        :candidato="candidato"
        :current-red="currentRed"
        tema="rival"
        @close="isConfigModalOpen = false"
      />
    </div>
  </WarRoomLayout>
</template>
