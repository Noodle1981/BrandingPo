<script setup>
import { ref, computed } from 'vue';
import { ExternalLink, Play, Image as ImageIcon, Video, Radio, Eye } from '@lucide/vue';

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

const imageError = ref(false);
const showInteractiveEmbed = ref(false);

const cleanUrl = (input) => {
  if (!input) return null;
  const str = String(input).trim();
  if (str.includes('<iframe') || str.includes('src=')) {
    const match = str.match(/src=["']([^"']+)["']/i);
    if (match) {
      return match[1].replace(/&amp;/g, '&');
    }
  }
  return str;
};

// Detectar si una URL es una URL real de post de Facebook y no un dummy de seeder (evita cargar scripts fallidos de Meta)
const isValidFacebookPostUrl = (target) => {
  if (!target) return false;
  const s = String(target).toLowerCase();
  if (s.includes('/demo-') || s.includes('demo') || s.includes('example.com')) return false;
  return (
    s.includes('/posts/') ||
    s.includes('/videos/') ||
    s.includes('/watch') ||
    s.includes('/reel/') ||
    s.includes('/reels/') ||
    s.includes('fb.watch/') ||
    s.includes('story_fbid=')
  );
};

// Detectar si una URL es un post real de Instagram
const isValidInstagramPostUrl = (target) => {
  if (!target) return false;
  const s = String(target).toLowerCase();
  if (s.includes('/demo-') || s.includes('demo') || s.includes('example.com')) return false;
  return /instagram\.com\/(?:p|reel|reels|tv)\/([A-Za-z0-9_-]{5,})/i.test(s);
};

// Detect YouTube Video ID
const youtubeId = computed(() => {
  const target = cleanUrl(props.url || props.mediaUrl);
  if (!target) return null;
  const match = target.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/i);
  return match ? match[1] : null;
});

// Detect TikTok Video ID / Embed
const tiktokVideoId = computed(() => {
  const target = cleanUrl(props.url || props.mediaUrl);
  if (!target || target.includes('demo-')) return null;
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
  const target = (cleanUrl(props.url) || cleanUrl(props.mediaUrl) || '').toLowerCase();
  return target.includes('/reel/') || target.includes('/reels/') || props.formato === 'Reel';
});

// Detect Instagram Embed URL (solo si es URL real para evitar que Meta lance miles de excepciones)
const instagramEmbedUrl = computed(() => {
  const target = cleanUrl(props.url || props.mediaUrl);
  if (!isValidInstagramPostUrl(target)) return null;
  const match = target.match(/instagram\.com\/(?:p|reel|reels|tv)\/([^/?#&]+)/i);
  if (match) {
    return `https://www.instagram.com/p/${match[1]}/embed/`;
  }
  return null;
});

// Detect Facebook Video / Reel / Post Embed
const isFacebookReel = computed(() => {
  const target = (cleanUrl(props.url) || cleanUrl(props.mediaUrl) || '').toLowerCase();
  return target.includes('/reel/') || target.includes('/reels/') || (props.plataforma === 'facebook' && props.formato === 'Reel');
});

const isFacebookVideo = computed(() => {
  const target = (cleanUrl(props.url) || cleanUrl(props.mediaUrl) || '').toLowerCase();
  return target.includes('/watch') || target.includes('/videos/') || target.includes('fb.watch') || (props.plataforma === 'facebook' && props.formato === 'Video');
});

const facebookEmbedUrl = computed(() => {
  const rawTarget = cleanUrl(props.mediaUrl) || cleanUrl(props.url);
  if (!rawTarget) return null;

  // Si ya es un URL del plugin de Facebook
  if (rawTarget.includes('facebook.com/plugins/post.php') || rawTarget.includes('facebook.com/plugins/video.php')) {
    return rawTarget;
  }

  const postUrl = cleanUrl(props.url) || rawTarget;
  if (!isValidFacebookPostUrl(postUrl)) {
    return null;
  }

  const isVideoOrReel = isFacebookReel.value || isFacebookVideo.value;
  const encoded = encodeURIComponent(postUrl);
  if (isVideoOrReel) {
    return `https://www.facebook.com/plugins/video.php?href=${encoded}&show_text=false&width=500`;
  }
  return `https://www.facebook.com/plugins/post.php?href=${encoded}&show_text=true&width=500`;
});

// Prioridad 1: Imagen directa disponible (descargada localmente o URL válida)
const directImageUrl = computed(() => {
  if (imageError.value) return null;
  const target = cleanUrl(props.mediaUrl);
  if (!target) return null;
  if (target.includes('<iframe') || target.includes('/plugins/')) return null;
  if (target.includes('lookaside.fbsbx.com')) return null; // Bloquea hotlinking directo sin firma
  if (target.startsWith('http://') || target.startsWith('https://') || target.startsWith('/')) {
    return target;
  }
  return null;
});

const hasAnyEmbed = computed(() => {
  return !!(youtubeId.value || instagramEmbedUrl.value || tiktokEmbedUrl.value || facebookEmbedUrl.value);
});

const isVideoFormat = computed(() => {
  const f = (props.formato || '').toLowerCase();
  return ['video', 'reel', 'shorts', 'tiktok'].includes(f) || isFacebookReel.value || isInstagramReel.value || isFacebookVideo.value || !!youtubeId.value;
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
    
    <!-- CASO 1: IMAGEN DIRECTA CON PRIORIDAD (Sin scripts de terceros ni errores de cookies) -->
    <div v-if="directImageUrl && !showInteractiveEmbed" class="relative group">
      <img
        :src="directImageUrl"
        alt="Foto de la publicación"
        referrerpolicy="no-referrer"
        loading="lazy"
        @error="imageError = true"
        class="w-full max-h-[480px] object-cover hover:scale-101 transition-transform duration-200"
      />

      <!-- Si es video o tiene embed interactivo disponible, mostrar botón de Play superpuesto -->
      <div
        v-if="hasAnyEmbed"
        class="absolute inset-0 bg-slate-950/35 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center cursor-pointer"
        @click="showInteractiveEmbed = true"
      >
        <button
          type="button"
          class="px-4 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs flex items-center gap-2 shadow-lg transition-transform hover:scale-105 cursor-pointer"
        >
          <Play class="w-4 h-4 fill-slate-950" />
          <span>Reproducir en vivo</span>
        </button>
      </div>
    </div>

    <!-- CASO 2: EMBEDS INTERACTIVOS (YouTube, Instagram, Facebook, TikTok) -->
    <!-- Se cargan bajo demanda para evitar 3,000+ errores de tracking y cookies de Meta en consola -->
    <template v-else-if="hasAnyEmbed">
      <!-- 2.A Si el usuario activó la vista interactiva para este post: -->
      <div v-if="showInteractiveEmbed" class="relative">
        <!-- Botón para cerrar reproductor interactivo -->
        <div class="p-2 bg-slate-100 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs">
          <span class="font-mono text-[11px] text-slate-500">Reproductor {{ plataformaName }} activo</span>
          <button
            type="button"
            @click="showInteractiveEmbed = false"
            class="text-[11px] font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer"
          >
            ✕ Cerrar vista interactiva
          </button>
        </div>

        <!-- YouTube Video Embed (con youtube-nocookie para prevenir cookies de terceros) -->
        <div v-if="youtubeId" class="relative w-full aspect-video">
          <iframe
            :src="`https://www.youtube-nocookie.com/embed/${youtubeId}?autoplay=1`"
            title="YouTube video player"
            frameborder="0"
            loading="lazy"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            class="w-full h-full"
          ></iframe>
        </div>

        <!-- Instagram Video/Post Embed -->
        <div
          v-else-if="instagramEmbedUrl"
          class="relative w-full overflow-hidden flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-2 sm:p-4"
        >
          <iframe
            :src="instagramEmbedUrl"
            class="w-full max-w-[460px] rounded-2xl border-0 shadow-sm"
            :style="{
              height: isInstagramReel ? '620px' : '520px',
              minHeight: isInstagramReel ? '580px' : '480px'
            }"
            loading="lazy"
            referrerpolicy="no-referrer"
            scrolling="no"
            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
            allowfullscreen
          ></iframe>
        </div>

        <!-- TikTok Video Embed -->
        <div
          v-else-if="tiktokEmbedUrl"
          class="relative w-full overflow-hidden flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-2 sm:p-4"
        >
          <iframe
            :src="tiktokEmbedUrl"
            class="w-full max-w-[340px] sm:max-w-[380px] rounded-2xl border-0 shadow-sm"
            style="height: 620px; min-height: 580px;"
            loading="lazy"
            referrerpolicy="no-referrer"
            scrolling="no"
            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
            allowfullscreen
          ></iframe>
        </div>

        <!-- Facebook Video / Reel / Post Embed -->
        <div
          v-else-if="facebookEmbedUrl"
          class="relative w-full overflow-hidden flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-950 p-2 sm:p-4"
        >
          <iframe
            :src="facebookEmbedUrl"
            class="w-full max-w-[500px] rounded-2xl border-0 shadow-sm"
            :style="{
              height: isFacebookReel ? '620px' : (isFacebookVideo ? '380px' : '640px'),
              minHeight: isFacebookReel ? '580px' : (isFacebookVideo ? '320px' : '560px')
            }"
            loading="lazy"
            referrerpolicy="no-referrer"
            scrolling="no"
            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
            allowfullscreen
          ></iframe>
        </div>
      </div>

      <!-- 2.B Facade / Vista Previa Limpia (No carga iframes pesados hasta que el usuario hace clic) -->
      <div
        v-else
        class="p-5 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 text-slate-100 flex flex-col items-center justify-center text-center space-y-3 min-h-[190px]"
      >
        <div class="w-12 h-12 rounded-2xl bg-cyan-500/15 text-cyan-400 flex items-center justify-center shadow-inner">
          <Play v-if="isVideoFormat" class="w-6 h-6 fill-cyan-400" />
          <Radio v-else class="w-6 h-6" />
        </div>

        <div class="space-y-0.5 max-w-sm">
          <span class="text-xs font-mono font-bold uppercase tracking-wider text-cyan-400 block">
            {{ formato }} en {{ plataformaName }}
          </span>
          <p class="text-xs text-slate-400 line-clamp-1">
            {{ cleanUrl(url || mediaUrl) }}
          </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap justify-center pt-1">
          <button
            type="button"
            @click="showInteractiveEmbed = true"
            class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs font-mono flex items-center gap-1.5 transition-all shadow-md hover:scale-102 cursor-pointer"
          >
            <Eye class="w-3.5 h-3.5" />
            <span>Cargar vista previa en vivo</span>
          </button>

          <a
            v-if="url || mediaUrl"
            :href="cleanUrl(url || mediaUrl)"
            target="_blank"
            rel="noopener noreferrer"
            class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold font-mono flex items-center gap-1.5 transition-all cursor-pointer"
          >
            <ExternalLink class="w-3.5 h-3.5 text-slate-400" />
            <span>Abrir en {{ plataformaName }}</span>
          </a>
        </div>
      </div>
    </template>

    <!-- CASO 3: TARJETA DE ENLACE GENERAL (Sin embed ni imagen directa) -->
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
          <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs sm:max-w-md mt-0.5" :title="cleanUrl(url || mediaUrl)">
            {{ cleanUrl(url || mediaUrl) }}
          </p>
        </div>
      </div>

      <a
        :href="cleanUrl(url || mediaUrl)"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-xs transition-all shrink-0 hover:scale-102"
      >
        <ExternalLink class="w-3.5 h-3.5" />
        <span>Ver en {{ plataformaName }}</span>
      </a>
    </div>

    <!-- CASO 4: PLACEHOLDER SI NO HAY NINGÚN ENLACE -->
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
