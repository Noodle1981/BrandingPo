<script setup>
import { computed } from 'vue';

const props = defineProps({
  platform: {
    type: String,
    required: true,
  },
  size: {
    type: String,
    default: 'md', // 'xs', 'sm', 'md', 'lg'
  },
  showName: {
    type: Boolean,
    default: false,
  },
  showBadge: {
    type: Boolean,
    default: false,
  }
});

const normalizedPlatform = computed(() => {
  const p = (props.platform || '').toLowerCase().trim();
  if (p.includes('insta')) return 'instagram';
  if (p.includes('face')) return 'facebook';
  if (p.includes('thread')) return 'threads';
  if (p.includes('tik')) return 'tiktok';
  if (p.includes('you') || p.includes('yt')) return 'youtube';
  if (p.includes('twit') || p.includes('x_') || p === 'x') return 'x_twitter';
  if (p.includes('link')) return 'linkedin';
  return 'default';
});

const meta = computed(() => {
  switch (normalizedPlatform.value) {
    case 'instagram':
      return { name: 'Instagram', color: '#E4405F', bg: 'bg-[#E4405F]/15' };
    case 'facebook':
      return { name: 'Facebook', color: '#1877F2', bg: 'bg-[#1877F2]/15' };
    case 'threads':
      return { name: 'Threads', color: '#000000', bg: 'bg-slate-900/15' };
    case 'tiktok':
      return { name: 'TikTok', color: '#00F2FE', bg: 'bg-[#00F2FE]/15' };
    case 'youtube':
      return { name: 'YouTube', color: '#FF0000', bg: 'bg-[#FF0000]/15' };
    case 'x_twitter':
      return { name: 'X', color: '#000000', bg: 'bg-slate-500/15' };
    case 'linkedin':
      return { name: 'LinkedIn', color: '#0A66C2', bg: 'bg-[#0A66C2]/15' };
    default:
      return { name: 'Red Social', color: '#06b6d4', bg: 'bg-cyan-500/15' };
  }
});

const iconSizeClass = computed(() => {
  switch (props.size) {
    case 'xs': return 'w-3.5 h-3.5';
    case 'sm': return 'w-4 h-4';
    case 'lg': return 'w-6 h-6';
    case 'md':
    default: return 'w-5 h-5';
  }
});

const containerSizeClass = computed(() => {
  switch (props.size) {
    case 'xs': return 'w-6 h-6 rounded-lg';
    case 'sm': return 'w-7 h-7 rounded-xl';
    case 'lg': return 'w-10 h-10 rounded-2xl';
    case 'md':
    default: return 'w-8 h-8 rounded-xl';
  }
});
</script>

<template>
  <div
    class="inline-flex items-center gap-1.5 shrink-0"
    :title="meta.name"
  >
    <div
      v-if="showBadge"
      class="flex items-center justify-center transition-transform hover:scale-105"
      :class="[containerSizeClass, meta.bg]"
    >
      <!-- Instagram -->
      <svg v-if="normalizedPlatform === 'instagram'" :class="iconSizeClass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: meta.color }">
        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
      </svg>
      <!-- Facebook -->
      <svg v-else-if="normalizedPlatform === 'facebook'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor" :style="{ color: meta.color }">
        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
      </svg>
      <!-- Threads -->
      <svg v-else-if="normalizedPlatform === 'threads'" :class="iconSizeClass" class="text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12.186 24C5.454 24 0 18.675 0 12.103 0 5.53 5.454.205 12.186.205c6.733 0 12.186 5.325 12.186 11.898 0 6.572-5.453 11.897-12.186 11.897zm-.055-2.285c5.385 0 9.771-4.27 9.771-9.512 0-5.243-4.386-9.513-9.77-9.513-5.385 0-9.772 4.27-9.772 9.513 0 5.242 4.387 9.512 9.772 9.512zm4.786-9.284c-.035 3.328-1.929 5.353-4.897 5.353-2.735 0-4.636-1.748-4.636-4.52 0-3.053 2.193-4.654 4.887-4.654 1.344 0 2.507.411 3.25 1.134l-1.378 1.458c-.469-.475-1.12-.72-1.85-.72-1.458 0-2.457.946-2.457 2.659 0 1.624 1.054 2.573 2.395 2.573 1.584 0 2.373-.974 2.45-2.283H12.15V9.458h4.722v2.973z"/>
      </svg>
      <!-- TikTok -->
      <svg v-else-if="normalizedPlatform === 'tiktok'" :class="iconSizeClass" class="text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
      </svg>
      <!-- X / Twitter -->
      <svg v-else-if="normalizedPlatform === 'x_twitter'" :class="iconSizeClass" class="text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
      </svg>
      <!-- YouTube -->
      <svg v-else-if="normalizedPlatform === 'youtube'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor" :style="{ color: meta.color }">
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
      </svg>
      <!-- LinkedIn -->
      <svg v-else-if="normalizedPlatform === 'linkedin'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor" :style="{ color: meta.color }">
        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
      </svg>
    </div>

    <!-- Icono Directo (sin badge contenedor) -->
    <template v-else>
      <svg v-if="normalizedPlatform === 'instagram'" :class="iconSizeClass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: meta.color }">
        <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
      </svg>
      <svg v-else-if="normalizedPlatform === 'facebook'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor" :style="{ color: meta.color }">
        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
      </svg>
      <!-- Threads -->
      <svg v-else-if="normalizedPlatform === 'threads'" :class="iconSizeClass" class="text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12.186 24C5.454 24 0 18.675 0 12.103 0 5.53 5.454.205 12.186.205c6.733 0 12.186 5.325 12.186 11.898 0 6.572-5.453 11.897-12.186 11.897zm-.055-2.285c5.385 0 9.771-4.27 9.771-9.512 0-5.243-4.386-9.513-9.77-9.513-5.385 0-9.772 4.27-9.772 9.513 0 5.242 4.387 9.512 9.772 9.512zm4.786-9.284c-.035 3.328-1.929 5.353-4.897 5.353-2.735 0-4.636-1.748-4.636-4.52 0-3.053 2.193-4.654 4.887-4.654 1.344 0 2.507.411 3.25 1.134l-1.378 1.458c-.469-.475-1.12-.72-1.85-.72-1.458 0-2.457.946-2.457 2.659 0 1.624 1.054 2.573 2.395 2.573 1.584 0 2.373-.974 2.45-2.283H12.15V9.458h4.722v2.973z"/>
      </svg>
      <svg v-else-if="normalizedPlatform === 'tiktok'" :class="iconSizeClass" class="text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
      </svg>
      <svg v-else-if="normalizedPlatform === 'x_twitter'" :class="iconSizeClass" class="text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
      </svg>
      <svg v-else-if="normalizedPlatform === 'youtube'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor" :style="{ color: meta.color }">
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
      </svg>
      <svg v-else-if="normalizedPlatform === 'linkedin'" :class="iconSizeClass" viewBox="0 0 24 24" fill="currentColor" :style="{ color: meta.color }">
        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
      </svg>
    </template>

    <span v-if="showName" class="font-bold text-xs">
      {{ meta.name }}
    </span>
  </div>
</template>
