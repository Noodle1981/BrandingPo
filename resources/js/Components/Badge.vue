<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'default', // 'facebook', 'instagram', 'x_twitter', 'tiktok', 'youtube', 'linkedin', 'estado', 'sentimiento', 'rol', 'pauta'
  },
  value: {
    type: String,
    default: '',
  },
  size: {
    type: String,
    default: 'md', // 'sm', 'md', 'lg'
  }
});

const badgeStyles = computed(() => {
  const val = (props.value || '').toLowerCase();
  const v = props.variant.toLowerCase();

  // Social Networks
  if (v === 'facebook' || val === 'facebook') {
    return 'bg-[#1877F2]/15 text-[#1877F2] border-[#1877F2]/30 dark:bg-[#1877F2]/20 dark:text-[#539dfc] dark:border-[#1877F2]/40';
  }
  if (v === 'instagram' || val === 'instagram') {
    return 'bg-[#E4405F]/15 text-[#E4405F] border-[#E4405F]/30 dark:bg-[#E4405F]/20 dark:text-[#ff6b87] dark:border-[#E4405F]/40';
  }
  if (v === 'x_twitter' || v === 'twitter' || val === 'x_twitter' || val === 'twitter' || val === 'x') {
    return 'bg-slate-900/10 text-slate-900 border-slate-400 dark:bg-white/10 dark:text-slate-100 dark:border-slate-600';
  }
  if (v === 'tiktok' || val === 'tiktok') {
    return 'bg-cyan-500/15 text-cyan-600 border-cyan-500/30 dark:bg-cyan-500/20 dark:text-cyan-400 dark:border-cyan-500/40';
  }
  if (v === 'youtube' || val === 'youtube') {
    return 'bg-red-600/15 text-red-600 border-red-600/30 dark:bg-red-600/20 dark:text-red-400 dark:border-red-600/40';
  }
  if (v === 'linkedin' || val === 'linkedin') {
    return 'bg-[#0A66C2]/15 text-[#0A66C2] border-[#0A66C2]/30 dark:bg-[#0A66C2]/20 dark:text-[#379bf7] dark:border-[#0A66C2]/40';
  }

  // Political States
  if (val === 'intendente_electo' || val === 'gobernador_electo' || val === 'electo') {
    return 'bg-emerald-500/15 text-emerald-700 border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/50 shadow-xs shadow-emerald-500/20 font-semibold';
  }
  if (val === 'en_funciones' || val === 'gestion') {
    return 'bg-teal-500/15 text-teal-700 border-teal-500/40 dark:bg-teal-500/20 dark:text-teal-300 dark:border-teal-500/50 font-semibold';
  }
  if (val === 'candidato' || val === 'candidato_oficial') {
    return 'bg-blue-500/15 text-blue-700 border-blue-500/40 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/50';
  }
  if (val === 'precandidato') {
    return 'bg-amber-500/15 text-amber-700 border-amber-500/40 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/50';
  }
  if (val === 'opositor') {
    return 'bg-purple-500/15 text-purple-700 border-purple-500/40 dark:bg-purple-500/20 dark:text-purple-300 dark:border-purple-500/50';
  }

  // Sentiment
  if (val === 'positivo' || val === 'favorable') {
    return 'bg-emerald-500/15 text-emerald-700 border-emerald-500/30 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/40';
  }
  if (val === 'neutro') {
    return 'bg-slate-500/15 text-slate-700 border-slate-400/30 dark:bg-slate-700/30 dark:text-slate-300 dark:border-slate-600/40';
  }
  if (val === 'critico' || val === 'negativo') {
    return 'bg-red-500/15 text-red-700 border-red-500/30 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/40';
  }

  // Roles
  if (val === 'admin') {
    return 'bg-rose-500/15 text-rose-700 border-rose-500/40 dark:bg-rose-500/20 dark:text-rose-300 dark:border-rose-500/50 font-bold';
  }
  if (val === 'consultor') {
    return 'bg-cyan-500/15 text-cyan-700 border-cyan-500/40 dark:bg-cyan-500/20 dark:text-cyan-300 dark:border-cyan-500/50 font-medium';
  }
  if (val === 'visualizador') {
    return 'bg-slate-200 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700';
  }

  // Pauta
  if (val === 'pauta_paga' || val === 'pago') {
    return 'bg-violet-500/15 text-violet-700 border-violet-500/40 dark:bg-violet-500/20 dark:text-violet-300 dark:border-violet-500/50 font-medium';
  }
  if (val === 'organico') {
    return 'bg-emerald-500/15 text-emerald-700 border-emerald-500/30 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/40';
  }

  return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
});

const sizeStyles = computed(() => {
  if (props.size === 'sm') return 'text-[10px] px-1.5 py-0.5';
  if (props.size === 'lg') return 'text-xs px-3 py-1';
  return 'text-xs px-2 py-0.5';
});

const labelText = computed(() => {
  const val = (props.value || '').toLowerCase();
  const map = {
    precandidato: 'Precandidato',
    candidato: 'Candidato Oficial',
    candidato_oficial: 'Candidato Oficial',
    intendente_electo: 'Intendente Electo',
    gobernador_electo: 'Gobernador Electo',
    en_funciones: 'En Funciones',
    opositor: 'Opositor',
    inactivo: 'Inactivo',
    facebook: 'Facebook',
    instagram: 'Instagram',
    x_twitter: 'X (Twitter)',
    tiktok: 'TikTok',
    youtube: 'YouTube',
    linkedin: 'LinkedIn',
    pauta_paga: 'Pauta Paga',
    organico: 'Orgánico',
    admin: 'Administrador',
    consultor: 'Consultor',
    visualizador: 'Visualizador (Solo Lectura)',
    favorable: 'Favorable',
    critico: 'Crítico',
    positivo: 'Positivo',
    negativo: 'Negativo',
    neutro: 'Neutro'
  };
  return map[val] || props.value;
});
</script>

<template>
  <span
    class="inline-flex items-center gap-1 font-medium rounded-full border tracking-wide uppercase transition-colors"
    :class="[badgeStyles, sizeStyles]"
  >
    <slot name="icon" />
    <span>{{ labelText }}</span>
  </span>
</template>
