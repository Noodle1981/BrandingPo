<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import WarRoomLayout from '../Layouts/WarRoomLayout.vue';
import DashboardHeader from '../Components/Dashboard/DashboardHeader.vue';
import DashboardKpis from '../Components/Dashboard/DashboardKpis.vue';
import DashboardChartsSection from '../Components/Dashboard/DashboardChartsSection.vue';
import DashboardCanalesGrid from '../Components/Dashboard/DashboardCanalesGrid.vue';
import DashboardRoiSection from '../Components/Dashboard/DashboardRoiSection.vue';
import DashboardClippingSummary from '../Components/Dashboard/DashboardClippingSummary.vue';

const props = defineProps({
  candidato: {
    type: Object,
    default: null
  },
  candidatos_lista: {
    type: Array,
    default: () => []
  },
  periodo_activo: {
    type: String,
    default: 'todos'
  },
  periodos_disponibles: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_seguidores: '0',
      total_seguidores_raw: 0,
      total_seguidores_netos: '0',
      total_seguidores_netos_raw: 0,
      crecimiento_neto_seguidores: 0,
      total_publicaciones: 0,
      total_vistas: '0',
      total_vistas_raw: 0,
      total_likes: '0',
      total_likes_raw: 0,
      total_comentarios: '0',
      total_compartidos: '0',
      total_republicados: '0',
      total_guardados: '0',
      engagement_promedio: '0.0%',
      engagement_promedio_raw: 0,
      humor_social_promedio: '5.0',
      humor_social_promedio_raw: 5,
      humor_clima_texto: 'Favorable',
      ratio_penetracion: '0.0%',
      ratio_penetracion_raw: 0,
      score_promedio_post: '0',
      score_promedio_post_raw: 0,
      score_promedio_post_meta: 200,
      score_promedio_post_pct: 0,
      meta_score_base_texto: 'Media Territorial',
      score_promedio_mensual: '0',
      score_promedio_mensual_raw: 0,
      score_promedio_mensual_meta: 2000,
      score_promedio_mensual_pct: 0,
      score_impacto_total: '0',
      score_impacto_total_raw: 0,
      avance_campana_padron_pct: 0,
      meta_score_campana: '0',
      record_mensual_score: '0',
      record_mensual_nombre: '',
      record_mensual_corto: '',
      score_promedio_diario: '0',
      tendencia_score_mes: null,
      desglose_mensual: []
    })
  },
  redes_desglose: {
    type: Array,
    default: () => []
  },
  distribucion_plataformas: {
    type: Array,
    default: () => []
  },
  rendimiento_por_formato: {
    type: Array,
    default: () => []
  },
  distribucion_ejes: {
    type: Array,
    default: () => []
  },
  historico_mediciones: {
    type: Array,
    default: () => []
  },
  series_por_red: {
    type: Object,
    default: () => ({})
  },
  organico_vs_pauta: {
    type: Object,
    default: () => ({
      total_posts_organicos: 0,
      total_posts_pautados: 0,
      vistas_organicas: 0,
      vistas_pagadas: 0,
      interacciones_organicas: 0,
      interacciones_pautadas: 0,
      porcentaje_vistas_organicas: 100,
      porcentaje_vistas_pagadas: 0,
      inversion_total: 0,
      costo_por_interaccion: 0,
      cpm_estimado: 0,
      posts_impulsados: []
    })
  },
  top_publicaciones: {
    type: Array,
    default: () => []
  },
  ultimas_publicaciones: {
    type: Array,
    default: () => []
  },
  ultimas_notas_prensa: {
    type: Array,
    default: () => []
  },
  hitos_booster: {
    type: Array,
    default: () => []
  },
  formatos_por_red: {
    type: Array,
    default: () => []
  }
});

// Modal de gráfico ampliado compartido (sincronizable desde KPIs o desde los gráficos)
const modalGraficoActivo = ref(null);

const cambiarCandidato = (id) => {
  router.get('/dashboard', { candidato_id: id, periodo: props.periodo_activo }, { preserveState: true, replace: true });
};

const cambiarPeriodo = (periodo) => {
  router.get('/dashboard', { candidato_id: props.candidato?.id, periodo }, { preserveState: true, replace: true });
};

const resetPeriodo = () => {
  router.get('/dashboard', { candidato_id: props.candidato?.id, periodo: 'todos' }, { preserveState: true, replace: true });
};
</script>

<template>
  <Head :title="candidato ? `Sala de Situación: ${candidato.nombre_completo}` : 'Dashboard Central'" />

  <WarRoomLayout>
    <div class="space-y-6">
      <!-- 1. Cabecera Estratégica & Selectores -->
      <DashboardHeader
        :candidato="candidato"
        :candidatos-lista="candidatos_lista"
        :periodo-activo="periodo_activo"
        :periodos-disponibles="periodos_disponibles"
        :total-publicaciones="stats.total_publicaciones"
        @cambiar-candidato="cambiarCandidato"
        @cambiar-periodo="cambiarPeriodo"
        @reset-periodo="resetPeriodo"
      />

      <!-- 2. HUD Central de KPIs Estratégicos -->
      <DashboardKpis
        :stats="stats"
        :candidato="candidato"
        @abrir-modal-grafico="(tipo) => modalGraficoActivo = tipo"
      />

      <!-- 3. Centro de Analítica Visual & Gráficos con Modal Fullscreen -->
      <DashboardChartsSection
        :historico-mediciones="historico_mediciones"
        :series-por-red="series_por_red"
        :redes-desglose="redes_desglose"
        :distribucion-plataformas="distribucion_plataformas"
        :rendimiento-por-formato="rendimiento_por_formato"
        :formatos-por-red="formatos_por_red"
        :distribucion-ejes="distribucion_ejes"
        :hitos-booster="hitos_booster"
        :stats="stats"
        v-model:modal-grafico-externo="modalGraficoActivo"
      />

      <!-- 4. Malla de Auditoría de Canales Sociales Conectados -->
      <DashboardCanalesGrid
        :redes-desglose="redes_desglose"
      />

      <!-- 5. Inteligencia de Pauta (ROI) & Observatorio de Prensa (2 Columnas) -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <DashboardRoiSection
          :organico-vs-pauta="organico_vs_pauta"
        />

        <DashboardClippingSummary
          :ultimas-notas-prensa="ultimas_notas_prensa"
        />
      </div>
    </div>
  </WarRoomLayout>
</template>
