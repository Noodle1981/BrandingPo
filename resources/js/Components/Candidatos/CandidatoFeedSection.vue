<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import SocialCard from '../SocialCard.vue';
import FeedFastFlowModal from '../Feed/FeedFastFlowModal.vue';
import {
  Film,
  Plus,
  Filter,
  Search,
  RotateCcw,
  Sparkles,
  Flame,
  Radio
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  publicaciones: {
    type: Array,
    default: () => [],
  },
  ejes: {
    type: Array,
    default: () => [],
  },
  tema: {
    type: String,
    default: 'propio', // 'propio' | 'rival'
  },
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? true);
const esPropio = computed(() => props.tema === 'propio' || props.candidato?.es_propio);

// Estado de Filtros de Publicaciones
const filterPlatform = ref('todas');
const filterPauta = ref('todas');
const filterSearch = ref('');
const filterOrden = ref('recientes');

// Modal Fast-Flow para Nueva Publicación
const isFastFlowOpen = ref(false);

// Filtrado de Publicaciones
const filteredPublicaciones = computed(() => {
  let list = [...props.publicaciones];

  if (filterPlatform.value !== 'todas') {
    list = list.filter(p => p.plataforma === filterPlatform.value);
  }

  if (filterPauta.value !== 'todas') {
    list = list.filter(p => p.tipo_pauta === filterPauta.value);
  }

  if (filterSearch.value.trim()) {
    const q = filterSearch.value.toLowerCase().trim();
    list = list.filter(p =>
      (p.contenido_resumen && p.contenido_resumen.toLowerCase().includes(q)) ||
      (p.perfil_social?.handle_usuario && p.perfil_social.handle_usuario.toLowerCase().includes(q))
    );
  }

  if (filterOrden.value === 'interacciones') {
    list.sort((a, b) => (b.total_likes + b.total_comentarios + (b.total_compartidos || 0)) - (a.total_likes + a.total_comentarios + (a.total_compartidos || 0)));
  } else if (filterOrden.value === 'antiguos') {
    list.sort((a, b) => new Date(a.fecha_publicacion_raw || a.created_at) - new Date(b.fecha_publicacion_raw || b.created_at));
  } else {
    list.sort((a, b) => new Date(b.fecha_publicacion_raw || b.created_at) - new Date(a.fecha_publicacion_raw || a.created_at));
  }

  return list;
});

const resetFilters = () => {
  filterPlatform.value = 'todas';
  filterPauta.value = 'todas';
  filterSearch.value = '';
  filterOrden.value = 'recientes';
};

const candidatosListaParaModal = computed(() => {
  return [{
    id: props.candidato.id,
    nombre_completo: props.candidato.nombre_completo,
    partido_coalicion: props.candidato.partido_coalicion,
    color_hex: props.candidato.color_hex,
    avatar_url: props.candidato.avatar_url,
    perfiles: (props.candidato.perfiles || props.candidato.perfiles_sociales || []).map(p => ({
      id: p.id,
      plataforma: p.plataforma,
      handle_usuario: p.handle_usuario,
    })),
  }];
});
</script>

<template>
  <div class="space-y-6 pt-2">
    <!-- Barra de Título & Acciones del Feed -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-5">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-xl font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2.5">
            <Film class="w-5 h-5" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
            <span>{{ esPropio ? 'Publicaciones de Campaña (Social Feed)' : 'Publicaciones & Espionaje del Rival' }}</span>
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Muro de contenidos, análisis de tracción, pauta publicitaria y reacciones emoji por emoji.
          </p>
        </div>

        <button
          v-if="canWrite"
          type="button"
          @click="isFastFlowOpen = true"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-white font-extrabold text-xs shadow-md transition-all hover:scale-102 cursor-pointer shrink-0"
          :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500 shadow-cyan-600/25' : 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/25'"
        >
          <Plus class="w-4 h-4" />
          <span>+ Cargar Post (Fast-Flow)</span>
        </button>
      </div>

      <!-- Barra de Filtros -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
        <!-- Filtro Plataforma -->
        <div>
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 font-mono">Plataforma</label>
          <select
            v-model="filterPlatform"
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-900 dark:text-slate-100"
          >
            <option value="todas">🌐 Todas las redes</option>
            <option value="instagram">Instagram</option>
            <option value="facebook">Facebook</option>
            <option value="threads">Threads</option>
            <option value="tiktok">TikTok</option>
            <option value="x_twitter">X (Twitter)</option>
            <option value="youtube">YouTube</option>
            <option value="linkedin">LinkedIn</option>
          </select>
        </div>

        <!-- Filtro Pauta -->
        <div>
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 font-mono">Tipo de Pauta</label>
          <select
            v-model="filterPauta"
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-900 dark:text-slate-100"
          >
            <option value="todas">🎯 Todo tipo de difusión</option>
            <option value="organico">🌱 Orgánico Puro</option>
            <option value="organico_impulsado">🚀 Post Impulsado (Boosted)</option>
            <option value="pauta_paga">📢 Dark Post / Anuncio Directo</option>
            <option value="colaboracion_pagada">🌟 Colaboración Pagada</option>
          </select>
        </div>

        <!-- Ordenamiento -->
        <div>
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 font-mono">Orden Cronológico</label>
          <select
            v-model="filterOrden"
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-900 dark:text-slate-100"
          >
            <option value="recientes">⏱️ Más Recientes Primero</option>
            <option value="antiguos">📅 Más Antiguos Primero</option>
            <option value="interacciones">🔥 Más Interacciones Totales</option>
          </select>
        </div>

        <!-- Búsqueda por Texto -->
        <div>
          <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 font-mono">Búsqueda</label>
          <div class="relative">
            <input
              v-model="filterSearch"
              type="text"
              placeholder="Buscar por palabra o tema..."
              class="w-full pl-8 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-slate-100 placeholder:text-slate-400"
            />
            <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
          </div>
        </div>
      </div>

      <!-- Resumen de Resultados & Botón Limpiar -->
      <div class="flex items-center justify-between text-xs text-slate-500 font-mono pt-1">
        <span>
          Mostrando <strong>{{ filteredPublicaciones.length }}</strong> de <strong>{{ publicaciones.length }}</strong> publicaciones
        </span>
        <button
          v-if="filterPlatform !== 'todas' || filterPauta !== 'todas' || filterSearch || filterOrden !== 'recientes'"
          type="button"
          @click="resetFilters"
          class="text-xs text-slate-400 hover:text-slate-200 underline inline-flex items-center gap-1 cursor-pointer font-sans"
        >
          <RotateCcw class="w-3 h-3" />
          <span>Restablecer Filtros</span>
        </button>
      </div>
    </div>

    <!-- Lista de Publicaciones con SocialCard -->
    <div v-if="filteredPublicaciones.length > 0" class="space-y-6">
      <SocialCard
        v-for="post in filteredPublicaciones"
        :key="post.id"
        :post="post"
        :ejes="ejes"
        :can-write="canWrite"
      />
    </div>

    <!-- Estado Vacío -->
    <div
      v-else
      class="p-12 text-center rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4"
    >
      <div
        class="w-16 h-16 mx-auto rounded-3xl flex items-center justify-center"
        :class="esPropio ? 'bg-cyan-500/10 text-cyan-500' : 'bg-purple-500/10 text-purple-500'"
      >
        <Film class="w-8 h-8" />
      </div>
      <div>
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
          No se encontraron publicaciones
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mt-1">
          {{ publicaciones.length === 0 ? 'Aún no hay publicaciones registradas para este candidato. Utiliza el botón Fast-Flow para cargar la primera.' : 'No hay publicaciones que coincidan con los filtros aplicados. Prueba restablecerlos.' }}
        </p>
      </div>
      <button
        v-if="canWrite && publicaciones.length === 0"
        type="button"
        @click="isFastFlowOpen = true"
        class="px-5 py-2.5 rounded-2xl text-white font-bold text-xs shadow-md inline-flex items-center gap-2 cursor-pointer"
        :class="esPropio ? 'bg-cyan-600 hover:bg-cyan-500' : 'bg-purple-600 hover:bg-purple-500'"
      >
        <Plus class="w-4 h-4" />
        <span>Cargar Primer Post con 1 Clic</span>
      </button>
    </div>

    <!-- Modal Fast-Flow -->
    <FeedFastFlowModal
      :show="isFastFlowOpen"
      :candidatos="candidatosListaParaModal"
      :ejes="ejes"
      :candidato-preseleccionado-id="candidato.id"
      @close="isFastFlowOpen = false"
      @saved="isFastFlowOpen = false"
    />
  </div>
</template>
