<script setup>
import { ref, computed } from 'vue';
import MediaEmbed from '../MediaEmbed.vue';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  cardPautaStyles: {
    type: Object,
    required: true,
  },
});

const { formatCurrency } = useFormatters();

const isTextExpanded = ref(false);
const isLongText = computed(() => {
  const txt = (props.post?.contenido_resumen || '').trim();
  const saltosDeLinea = (txt.match(/\n/g) || []).length;
  return txt.length > 420 || saltosDeLinea > 8;
});
</script>

<template>
  <div class="p-4 sm:p-5 space-y-3 flex-1 flex flex-col justify-between">
    <div class="space-y-2.5">
      <!-- Tag de Eje Temático y Pilar Estratégico -->
      <div v-if="post.eje_tematico" class="flex items-center gap-2 flex-wrap">
        <span
          class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-xs font-bold border transition-all"
          :style="{
            backgroundColor: `${post.eje_tematico.color_badge || '#06b6d4'}15`,
            color: post.eje_tematico.color_badge || '#06b6d4',
            borderColor: `${post.eje_tematico.color_badge || '#06b6d4'}35`,
          }"
        >
          <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: post.eje_tematico.color_badge || '#06b6d4' }"></span>
          <span v-if="post.eje_tematico.pilar_principal" class="text-[10px] font-mono uppercase tracking-wider opacity-80 border-r pr-1.5 mr-0.5 border-current/30">
            {{ post.eje_tematico.pilar_principal.replace(/^\d+\.\s*/, '') }}
          </span>
          <span>{{ post.eje_tematico.nombre }}</span>
        </span>
      </div>

      <!-- Copy / Texto de Publicación con Truncamiento '... Ver más' -->
      <div class="space-y-1">
        <p
          class="text-slate-800 dark:text-slate-200 text-sm sm:text-base leading-relaxed whitespace-pre-line transition-all"
          :class="{ 'line-clamp-8': !isTextExpanded && isLongText }"
        >
          {{ post.contenido_resumen }}
        </p>

        <button
          v-if="isLongText"
          type="button"
          @click="isTextExpanded = !isTextExpanded"
          class="inline-flex items-center gap-1 text-xs font-bold text-cyan-600 dark:text-cyan-400 hover:text-cyan-500 hover:underline cursor-pointer pt-0.5 transition-colors"
        >
          <span>{{ isTextExpanded ? 'Ver menos ▲' : '... Ver más ▼' }}</span>
        </button>
      </div>
    </div>

    <!-- Zona Inferior del Body: Media Link Card + Figuras Acompañantes -->
    <div class="space-y-3 pt-1">
      <div class="relative mt-1">
        <div
          v-if="['pauta_paga', 'organico_impulsado', 'colaboracion_pagada'].includes(post.tipo_pauta) && post.monto_invertido_pauta"
          class="absolute -top-3.5 right-4 sm:right-5 px-3.5 py-1 rounded-full font-mono text-xs sm:text-sm font-black shadow-md z-10 tracking-tight flex items-center border-2 transition-transform hover:scale-105"
          :class="cardPautaStyles.budgetBadge"
          :title="`Inversión en pauta: ${formatCurrency(post.monto_invertido_pauta)}`"
        >
          <span>{{ formatCurrency(post.monto_invertido_pauta) }}</span>
        </div>

        <MediaEmbed
          :url="post.url_post"
          :media-url="post.media_url"
          :formato="post.tipo_formato || 'Post'"
          :plataforma="post.plataforma || post.perfil_social?.plataforma || 'facebook'"
        />
      </div>

      <!-- Figuras Acompañantes / Alianzas -->
      <div v-if="post.figuras_acompanantes && post.figuras_acompanantes.length" class="flex items-center gap-1.5 flex-wrap">
        <span class="text-xs text-slate-400 dark:text-slate-500">Con:</span>
        <span
          v-for="(figura, idx) in post.figuras_acompanantes"
          :key="idx"
          class="text-xs font-medium px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-cyan-600 dark:text-cyan-400 border border-slate-200 dark:border-slate-700"
        >
          🤝 {{ figura }}
        </span>
      </div>
    </div>
  </div>
</template>
