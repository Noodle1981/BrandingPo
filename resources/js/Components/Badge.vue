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
  },
  showSocialText: {
    type: Boolean,
    default: false,
  }
});

const isSocial = computed(() => {
  const val = (props.value || '').toLowerCase();
  const v = props.variant.toLowerCase();
  return ['facebook', 'instagram', 'threads', 'x_twitter', 'twitter', 'x', 'tiktok', 'youtube', 'linkedin'].includes(v) ||
         ['facebook', 'instagram', 'threads', 'x_twitter', 'twitter', 'x', 'tiktok', 'youtube', 'linkedin'].includes(val);
});

const socialKey = computed(() => {
  const val = (props.value || '').toLowerCase();
  const v = props.variant.toLowerCase();
  if (v.includes('insta') || val.includes('insta')) return 'instagram';
  if (v.includes('face') || val.includes('face')) return 'facebook';
  if (v.includes('thread') || val.includes('thread')) return 'threads';
  if (v.includes('tik') || val.includes('tik')) return 'tiktok';
  if (v.includes('you') || val.includes('you') || v.includes('yt')) return 'youtube';
  if (v.includes('x_') || v.includes('twit') || val.includes('x_') || val.includes('twit') || v === 'x' || val === 'x') return 'x_twitter';
  if (v.includes('link') || val.includes('link')) return 'linkedin';
  return 'default';
});

const badgeStyles = computed(() => {
  const val = (props.value || '').toLowerCase();
  const v = props.variant.toLowerCase();

  // Social Networks
  if (socialKey.value === 'facebook') {
    return 'bg-[#1877F2]/15 text-[#1877F2] border-[#1877F2]/30 dark:bg-[#1877F2]/20 dark:text-[#539dfc] dark:border-[#1877F2]/40';
  }
  if (socialKey.value === 'instagram') {
    return 'bg-[#E4405F]/15 text-[#E4405F] border-[#E4405F]/30 dark:bg-[#E4405F]/20 dark:text-[#ff6b87] dark:border-[#E4405F]/40';
  }
  if (socialKey.value === 'threads') {
    return 'bg-slate-900/10 text-slate-900 border-slate-400 dark:bg-white/10 dark:text-slate-100 dark:border-slate-600';
  }
  if (socialKey.value === 'x_twitter') {
    return 'bg-slate-900/10 text-slate-900 border-slate-400 dark:bg-white/10 dark:text-slate-100 dark:border-slate-600';
  }
  if (socialKey.value === 'tiktok') {
    return 'bg-cyan-500/15 text-cyan-600 border-cyan-500/30 dark:bg-cyan-500/20 dark:text-cyan-400 dark:border-cyan-500/40';
  }
  if (socialKey.value === 'youtube') {
    return 'bg-red-600/15 text-red-600 border-red-600/30 dark:bg-red-600/20 dark:text-red-400 dark:border-red-600/40';
  }
  if (socialKey.value === 'linkedin') {
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
  if (val === 'pauta_paga' || val === 'pago' || val === 'anuncio_directo') {
    return 'bg-violet-500/15 text-violet-700 border-violet-500/40 dark:bg-violet-500/20 dark:text-violet-300 dark:border-violet-500/50 font-medium';
  }
  if (val === 'organico_impulsado') {
    return 'bg-cyan-500/15 text-cyan-700 border-cyan-500/40 dark:bg-cyan-500/20 dark:text-cyan-300 dark:border-cyan-500/50 font-semibold';
  }
  if (val === 'colaboracion_pagada' || val === 'partnership') {
    return 'bg-amber-500/15 text-amber-700 border-amber-500/40 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/50 font-semibold';
  }
  if (val === 'organico') {
    return 'bg-emerald-500/15 text-emerald-700 border-emerald-500/30 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/40';
  }

  return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
});

const sizeStyles = computed(() => {
  if (props.size === 'sm') return isSocial.value && !props.showSocialText ? 'p-1.5' : 'text-[10px] px-2 py-0.5';
  if (props.size === 'lg') return isSocial.value && !props.showSocialText ? 'p-2.5' : 'text-xs px-3 py-1';
  return isSocial.value && !props.showSocialText ? 'p-2' : 'text-xs px-2.5 py-0.5';
});

const iconSizeClass = computed(() => {
  if (props.size === 'sm') return 'w-3.5 h-3.5';
  if (props.size === 'lg') return 'w-5 h-5';
  return 'w-4 h-4';
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
    pauta_paga: 'Dark Post / Ads',
    organico_impulsado: 'Post Impulsado (Boosted)',
    colaboracion_pagada: 'Colaboración Pagada',
    organico: 'Orgánico',
    admin: 'Administrador',
    consultor: 'Consultor',
    visualizador: 'Visualizador',
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
    class="inline-flex items-center justify-center gap-1 font-bold rounded-xl border tracking-wide uppercase transition-all shadow-2xs"
    :class="[badgeStyles, sizeStyles]"
    :title="isSocial ? labelText : undefined"
  >
    <!-- Logos Oficiales SVG para Redes Sociales -->
    <template v-if="isSocial">
      <!-- Instagram -->
      <svg v-if="socialKey === 'instagram'" :class="iconSizeClass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
      </svg>
      <!-- Facebook -->
      <svg v-else-if="socialKey === 'facebook'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor">
        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
      </svg>
      <!-- Threads -->
      <svg v-else-if="socialKey === 'threads'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12.186 24C5.454 24 0 18.675 0 12.103 0 5.53 5.454.205 12.186.205c6.733 0 12.186 5.325 12.186 11.898 0 6.572-5.453 11.897-12.186 11.897zm-.055-2.285c5.385 0 9.771-4.27 9.771-9.512 0-5.243-4.386-9.513-9.77-9.513-5.385 0-9.772 4.27-9.772 9.513 0 5.242 4.387 9.512 9.772 9.512zm4.786-9.284c-.035 3.328-1.929 5.353-4.897 5.353-2.735 0-4.636-1.748-4.636-4.52 0-3.053 2.193-4.654 4.887-4.654 1.344 0 2.507.411 3.25 1.134l-1.378 1.458c-.469-.475-1.12-.72-1.85-.72-1.458 0-2.457.946-2.457 2.659 0 1.624 1.054 2.573 2.395 2.573 1.584 0 2.373-.974 2.45-2.283H12.15V9.458h4.722v2.973z"/>
      </svg>
      <!-- TikTok -->
      <svg v-else-if="socialKey === 'tiktok'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
      </svg>
      <!-- X / Twitter -->
      <svg v-else-if="socialKey === 'x_twitter'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
      </svg>
      <!-- YouTube -->
      <svg v-else-if="socialKey === 'youtube'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor">
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
      </svg>
      <!-- LinkedIn -->
      <svg v-else-if="socialKey === 'linkedin'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor">
        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
      </svg>

      <span v-if="showSocialText">{{ labelText }}</span>
    </template>

    <!-- Badges Normales (Estado, Sentimiento, Pauta, etc.) -->
    <template v-else>
      <slot name="icon" />
      <span>{{ labelText }}</span>
    </template>
  </span>
</template>
