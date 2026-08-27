<script setup>
import { computed } from 'vue';
import { ExternalLink, Play, Image as ImageIcon, Video } from '@lucide/vue';

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
  }
});

// Detect YouTube Video ID
const youtubeId = computed(() => {
  const target = props.url || props.mediaUrl;
  if (!target) return null;
  const match = target.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/i);
  return match ? match[1] : null;
});

// Detect TikTok Video ID / Embed
const tiktokVideoId = computed(() => {
  const target = props.url || props.mediaUrl;
  if (!target) return null;
  const match = target.match(/tiktok\.com\/(?:@[\w.-]+\/video\/|v\/|embed\/v2\/)(\d+)/i)
    || target.match(/tiktok\.com\/.*?(?:video\/)(\d+)/i);
  return match ? match[1] : null;
});

const tiktokEmbedUrl = computed(() => {
  if (tiktokVideoId.value) {
    return `https://www.tiktok.com/embed/v2/${tiktokVideoId.value}`;
  }
  return null;
});

// Detect Instagram Reel or Post
const isInstagramReel = computed(() => {
  const target = (props.url || props.mediaUrl || '').toLowerCase();
  return target.includes('/reel/') || target.includes('/reels/') || props.formato === 'Reel';
});

// Detect Instagram Embed
const instagramEmbedUrl = computed(() => {
  const target = props.url || props.mediaUrl;
  if (!target) return null;
  const match = target.match(/instagram\.com\/(?:p|reel|reels|tv)\/([^/?#&]+)/i);
  if (match) {
    return `https://www.instagram.com/p/${match[1]}/embed/`;
  }
  return null;
});

// Detect Facebook Video / Reel Embed
const isFacebookReel = computed(() => {
  const target = (props.url || props.mediaUrl || '').toLowerCase();
  return target.includes('/reel/') || target.includes('/reels/') || (props.plataforma === 'facebook' && props.formato === 'Reel');
});

const isFacebookVideo = computed(() => {
  const target = (props.url || props.mediaUrl || '').toLowerCase();
  return target.includes('/watch') || target.includes('/videos/') || target.includes('fb.watch') || (props.plataforma === 'facebook' && props.formato === 'Video');
});

const facebookVideoEmbedUrl = computed(() => {
  const target = props.url || props.mediaUrl;
  if (!target) return null;
  if (target.includes('facebook.com') || target.includes('fb.watch')) {
    if (isFacebookReel.value || isFacebookVideo.value) {
      const encoded = encodeURIComponent(target);
      return `https://www.facebook.com/plugins/video.php?href=${encoded}&show_text=false&width=500`;
    }
  }
  return null;
});

// Detect Direct Image (solo cuando no haya un embed de video disponible)
const isDirectImage = computed(() => {
  if (youtubeId.value || instagramEmbedUrl.value || tiktokEmbedUrl.value || facebookVideoEmbedUrl.value) {
    return false;
  }
  const target = props.mediaUrl;
  if (!target) return false;
  return target.startsWith('http://') || target.startsWith('https://') || target.startsWith('/');
});

const directImageUrl = computed(() => {
  if (isDirectImage.value) {
    return props.mediaUrl;
  }
  return null;
});

const plataformaName = computed(() => {
  const p = (props.plataforma || '').toLowerCase();
  switch (p) {
    case 'facebook': return 'Facebook';
    case 'instagram': return 'Instagram';
    case 'threads': return 'Threads';
    case 'x_twitter':
    case 'twitter': return 'X / Twitter';
    case 'tiktok': return 'TikTok';
    case 'youtube': return 'YouTube';
    case 'linkedin': return 'LinkedIn';
    default: return 'la red original';
  }
});
</script>

<template>
  <div class="rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 transition-all">
    <!-- 1. YouTube Video Embed -->
    <div v-if="youtubeId" class="relative w-full aspect-video">
      <iframe
        :src="`https://www.youtube.com/embed/${youtubeId}`"
        title="YouTube video player"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        class="w-full h-full"
      ></iframe>
    </div>

    <!-- 2. Instagram Interactive Video/Post Embed -->
    <div
      v-else-if="instagramEmbedUrl"
      class="relative w-full overflow-hidden flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-2 sm:p-4 rounded-2xl"
    >
      <iframe
        :src="instagramEmbedUrl"
        class="w-full max-w-[460px] rounded-2xl border-0 shadow-sm transition-all"
        :style="{
          height: isInstagramReel ? '620px' : '520px',
          minHeight: isInstagramReel ? '580px' : '480px'
        }"
        frameborder="0"
        scrolling="no"
        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
        allowfullscreen
        allowtransparency="true"
      ></iframe>
    </div>

    <!-- 3. TikTok Interactive Video Player Embed -->
    <div
      v-else-if="tiktokEmbedUrl"
      class="relative w-full overflow-hidden flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-2 sm:p-4 rounded-2xl"
    >
      <iframe
        :src="tiktokEmbedUrl"
        class="w-full max-w-[340px] sm:max-w-[380px] rounded-2xl border-0 shadow-sm transition-all"
        style="height: 620px; min-height: 580px;"
        frameborder="0"
        scrolling="no"
        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
        allowfullscreen
        allowtransparency="true"
      ></iframe>
    </div>

    <!-- 4. Facebook Interactive Video / Reel Player Embed -->
    <div
      v-else-if="facebookVideoEmbedUrl"
      class="relative w-full overflow-hidden flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-2 sm:p-4 rounded-2xl"
    >
      <iframe
        :src="facebookVideoEmbedUrl"
        class="w-full max-w-[500px] rounded-2xl border-0 shadow-sm transition-all"
        :style="{
          height: isFacebookReel ? '620px' : '380px',
          minHeight: isFacebookReel ? '580px' : '320px'
        }"
        frameborder="0"
        scrolling="no"
        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
        allowfullscreen
        allowtransparency="true"
      ></iframe>
    </div>

    <!-- 5. Direct Image Preview (Fotos estáticas y miniaturas con bypass CDN) -->
    <div v-else-if="directImageUrl" class="relative group">
      <img
        :src="directImageUrl"
        alt="Foto de la publicación"
        referrerpolicy="no-referrer"
        class="w-full max-h-[480px] object-cover hover:scale-101 transition-transform duration-200"
      />
    </div>

    <!-- 6. Facebook / General Social Media Link Preview Card -->
    <div v-else-if="url || mediaUrl" class="p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-400 flex items-center justify-center shrink-0">
          <ImageIcon v-if="formato === 'Foto' || formato === 'Carrusel'" class="w-5 h-5" />
          <Video v-else-if="formato === 'Video' || formato === 'Reel' || formato === 'Shorts'" class="w-5 h-5" />
          <Play v-else class="w-5 h-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
              Publicación en {{ plataformaName }}
            </span>
            <span class="text-[10px] px-2 py-0.2 rounded-md bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-mono">
              {{ formato }}
            </span>
          </div>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs sm:max-w-md mt-0.5" :title="url || mediaUrl">
            {{ url || mediaUrl }}
          </p>
        </div>
      </div>

      <a
        :href="url || mediaUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-xs transition-all shrink-0 hover:scale-102"
      >
        <ExternalLink class="w-3.5 h-3.5" />
        <span>Ver en {{ plataformaName }}</span>
      </a>
    </div>

    <!-- 7. Default Placeholder if no link -->
    <div v-else class="p-6 text-center">
      <span class="inline-block px-3 py-1 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs uppercase tracking-wider font-semibold font-mono">
        Formato: {{ formato }}
      </span>
      <p class="text-xs text-slate-400 dark:text-slate-500 mt-2">
        Sin enlace multimedia adjunto
      </p>
    </div>
  </div>
</template>
