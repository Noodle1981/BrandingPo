<script setup>
import { Fingerprint, Sparkles } from '@lucide/vue';
import Badge from '../Badge.vue';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  cardPautaStyles: {
    type: Object,
    default: () => ({}),
  },
  canWrite: {
    type: Boolean,
    default: true,
  },
  esFechaSinConfirmar: {
    type: Boolean,
    default: false,
  },
  isPostInActiveWindow: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['convertir-booster']);
</script>

<template>
  <div class="flex flex-wrap items-center gap-1.5 font-mono justify-end shrink-0">
    <Badge
      variant="pauta"
      :value="post.tipo_pauta || 'organico'"
      size="sm"
    />

    <!-- Badge de Huella de Pauta Detectada -->
    <span
      v-if="post.tipo_pauta === 'organico' && post.huella_pauta?.tiene_huella"
      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-pink-500/15 text-pink-600 dark:text-pink-300 text-[10px] font-black border border-pink-500/30 whitespace-nowrap shadow-2xs animate-pulse"
      :title="post.huella_pauta.motivo"
    >
      <Fingerprint class="w-3 h-3 text-pink-500" />
      <span>Huella: {{ post.huella_pauta.token_detectado }}</span>
    </span>

    <button
      v-if="post.tipo_pauta === 'organico' && post.huella_pauta?.tiene_huella && canWrite"
      type="button"
      @click="emit('convertir-booster')"
      class="px-2 py-0.5 rounded-md bg-violet-600 hover:bg-violet-500 text-white text-[10px] font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1"
      title="Convertir a Booster (Orgánica Impulsada) y registrar punto de corte"
    >
      <Sparkles class="w-2.5 h-2.5" />
      <span>Poner Booster</span>
    </button>

    <!-- Badge Estratégico Especial: Fuego u Oro -->
    <span
      v-if="cardPautaStyles.estadoEstrategico === 'fuego'"
      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-rose-500/15 text-rose-600 dark:text-rose-300 text-[10px] font-black border border-rose-500/30 animate-pulse whitespace-nowrap"
      title="Oportunidad de Boost: Publicación orgánica con tracción destacada (Score >= 60/100) dentro de los 10 días"
    >
      <span>🔥 Oportunidad Boost</span>
    </span>
    <span
      v-else-if="cardPautaStyles.estadoEstrategico === 'dorado'"
      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-amber-400/20 text-amber-700 dark:text-amber-300 text-[10px] font-black border border-amber-400/40 shadow-2xs whitespace-nowrap"
      title="Post Estrella Consagrado: Tracción electoral sobresaliente (Score >= 75/100)"
    >
      <span>✨ Estrella ({{ (post.tipo_pauta || 'organico') === 'organico' ? 'Viral' : 'Boost' }})</span>
    </span>

    <span
      v-if="esFechaSinConfirmar"
      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-rose-500/10 text-rose-600 dark:text-rose-400 text-[10px] font-bold whitespace-nowrap"
    >
      <span>⚠️ Por confirmar</span>
    </span>
    <span
      v-else-if="isPostInActiveWindow"
      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold whitespace-nowrap"
      title="En ventana de sincronización activa (menos de 15 días)"
    >
      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
      <span>En vivo (15d)</span>
    </span>
    <span
      v-else
      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-400 text-[10px] whitespace-nowrap"
      title="Métrica histórica consolidada"
    >
      <span>🔒 Histórico</span>
    </span>
  </div>
</template>
