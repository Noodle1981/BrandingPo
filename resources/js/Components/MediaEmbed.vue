<script setup>
import { computed } from 'vue';
import { ExternalLink, Play, Image as ImageIcon, Video, Radio, DollarSign } from '@lucide/vue';

const props = defineProps({
  url: {
    type: String,
    default: null
  },
  mediaUrl: {
    type: String,
    default: null
  },
  formato: {
    type: String,
    default: 'Post'
  },
  plataforma: {
    type: String,
    default: 'facebook'
  },
  montoInvertido: {
    type: [Number, String],
    default: null
  },
  tipoPauta: {
    type: String,
    default: 'organico'
  }
});

const cleanUrl = computed(() => {
  const input = props.url || props.mediaUrl;
  if (!input) return null;
  const str = String(input).trim();
  if (str.includes('<iframe') || str.includes('src=')) {
    const match = str.match(/src=["']([^"']+)["']/i);
    if (match) {
      return match[1].replace(/&amp;/g, '&');
    }
  }
  return str;
});

const plataformaNormalized = computed(() => (props.plataforma || 'facebook').toLowerCase());

const plataformaMeta = computed(() => {
  switch (plataformaNormalized.value) {
    case 'facebook':
      return { nombre: 'Facebook', colorText: 'text-[#1877F2]', border: 'border-[#1877F2]/25' };
    case 'instagram':
      return { nombre: 'Instagram', colorText: 'text-[#E4405F]', border: 'border-[#E4405F]/25' };
    case 'threads':
      return { nombre: 'Threads', colorText: 'text-slate-800 dark:text-slate-200', border: 'border-slate-300 dark:border-slate-700' };
    case 'x_twitter':
    case 'twitter':
      return { nombre: 'X (Twitter)', colorText: 'text-slate-900 dark:text-slate-100', border: 'border-slate-400/30' };
    case 'tiktok':
      return { nombre: 'TikTok', colorText: 'text-[#00F2FE]', border: 'border-[#00F2FE]/25' };
    case 'youtube':
      return { nombre: 'YouTube', colorText: 'text-[#FF0000]', border: 'border-[#FF0000]/25' };
    case 'linkedin':
      return { nombre: 'LinkedIn', colorText: 'text-[#0A66C2]', border: 'border-[#0A66C2]/25' };
    default:
      return { nombre: 'Red Social', colorText: 'text-cyan-500', border: 'border-cyan-500/25' };
  }
});

const isVideo = computed(() => {
  const f = (props.formato || '').toLowerCase();
  return ['video', 'reel', 'shorts', 'tiktok'].includes(f);
});

const isPhoto = computed(() => {
  const f = (props.formato || '').toLowerCase();
  return ['foto', 'carrusel', 'imagen'].includes(f);
});

const formatCurrency = (val) => {
  if (!val) return '$0';
  return '$' + Number(val).toLocaleString('es-AR');
};

const budgetBadgeClass = computed(() => {
  const p = (props.tipoPauta || '').toLowerCase();
  switch (p) {
    case 'organico_impulsado':
      return 'bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 border border-cyan-500/40 font-extrabold';
    case 'pauta_paga':
      return 'bg-violet-500/20 text-violet-700 dark:text-violet-300 border border-violet-500/40 font-bold';
    case 'colaboracion_pagada':
      return 'bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/40 font-extrabold';
    default:
      return 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700';
  }
});
</script>

<template>
  <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 transition-all">
    <!-- Tarjeta con Enlace Externo Directo (Sin imágenes rotas ni iframes de terceros) -->
    <div
      v-if="cleanUrl"
      class="p-4 sm:p-4.5 bg-slate-50/80 dark:bg-slate-950/70 hover:bg-slate-100/90 dark:hover:bg-slate-900/80 transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3.5"
    >
      <div class="flex items-center gap-3 min-w-0 flex-1">
        <div
          class="w-11 h-11 rounded-2xl bg-white dark:bg-slate-900 border flex items-center justify-center shrink-0 shadow-2xs"
          :class="plataformaMeta.border"
        >
          <Video v-if="isVideo" class="w-5 h-5" :class="plataformaMeta.colorText" />
          <ImageIcon v-else-if="isPhoto" class="w-5 h-5" :class="plataformaMeta.colorText" />
          <Radio v-else class="w-5 h-5" :class="plataformaMeta.colorText" />
        </div>

        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-100">
              {{ formato }} en {{ plataformaMeta.nombre }}
            </span>
            <span class="px-2 py-0.2 rounded-md bg-slate-200/80 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-mono text-[10px] font-bold">
              {{ formato }}
            </span>
            <!-- Tag de Presupuesto Invertido (alineado sobre la misma línea sin colisiones) -->
            <span
              v-if="montoInvertido && Number(montoInvertido) > 0"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-mono shadow-2xs shrink-0"
              :class="budgetBadgeClass"
              title="Presupuesto asignado a esta publicación"
            >
              <DollarSign class="w-3 h-3" />
              <span>Invertido: {{ formatCurrency(montoInvertido) }}</span>
            </span>
          </div>
          <p
            class="text-[11px] font-mono text-slate-500 dark:text-slate-400 truncate mt-0.5"
            :title="cleanUrl"
          >
            {{ cleanUrl }}
          </p>
        </div>
      </div>

      <a
        :href="cleanUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-xs transition-all hover:scale-102 shrink-0 cursor-pointer"
        :title="`Abrir publicación original en ${plataformaMeta.nombre}`"
      >
        <span>Ver en {{ plataformaMeta.nombre }}</span>
        <ExternalLink class="w-3.5 h-3.5" />
      </a>
    </div>

    <!-- Si no hay enlace configurado -->
    <div v-else class="p-3 text-center bg-slate-50/50 dark:bg-slate-950/40 text-xs text-slate-400 font-mono">
      <span>Formato: {{ formato }} (Sin enlace externo adjunto)</span>
    </div>
  </div>
</template>
