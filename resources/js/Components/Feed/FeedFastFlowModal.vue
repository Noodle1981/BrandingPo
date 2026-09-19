<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
  Sparkles,
  X,
  AlertCircle,
  Calendar,
  RefreshCw,
  CheckCircle2,
  DollarSign,
  Eye,
  Heart,
  MessageCircle,
  Repeat,
  Bookmark,
  Send
} from '@lucide/vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  candidatos: {
    type: Array,
    default: () => []
  },
  groupedEjes: {
    type: Object,
    default: () => ({})
  },
  publicaciones: {
    type: Array,
    default: () => []
  },
  initialCandidatoId: {
    type: [Number, String],
    default: ''
  },
  initialPlataforma: {
    type: String,
    default: ''
  },
  initialEjeId: {
    type: [Number, String],
    default: ''
  },
  initialTipoPauta: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue', 'created']);

const isScraping = ref(false);
const scrapeSuccessMsg = ref('');
const scrapeErrorMsg = ref('');
const duplicateDetectedPost = ref(null);
const adDetectionAlert = ref(null);

const createForm = useForm({
  candidato_id: '',
  perfil_social_id: '',
  plataforma: 'instagram',
  eje_tematico_id: '',
  url_post: '',
  media_url: '',
  tipo_formato: 'Reel',
  tipo_pauta: 'organico',
  monto_invertido_pauta: 0,
  fecha_publicacion: '',
  vistas_organicas: 0,
  vistas_pagadas: 0,
  total_likes: 0,
  total_comentarios: 0,
  total_compartidos: 0,
  total_republicados: 0,
  total_guardados: 0,
  contenido_resumen: '',
  comentario_destacado: '',
  figura_acompanante: '',
  reacciones_detalladas: {
    like: 0,
    love: 0,
    care: 0,
    haha: 0,
    wow: 0,
    sad: 0,
    angry: 0,
  },
});

const initForm = () => {
  duplicateDetectedPost.value = null;
  adDetectionAlert.value = null;
  scrapeSuccessMsg.value = '';
  scrapeErrorMsg.value = '';

  // 1. Candidato
  if (props.initialCandidatoId) {
    createForm.candidato_id = props.initialCandidatoId;
  } else {
    const propio = props.candidatos.find(c => c.es_propio);
    createForm.candidato_id = propio ? propio.id : (props.candidatos[0]?.id ?? '');
  }

  // 2. Perfil social según plataforma inicial
  const cand = props.candidatos.find(c => c.id === Number(createForm.candidato_id));
  const perfiles = cand?.perfiles_sociales || cand?.perfilesSociales || [];
  const activePlatform = props.initialPlataforma;

  if (activePlatform) {
    const matchingPerfil = perfiles.find(p => p.plataforma.toLowerCase() === activePlatform.toLowerCase());
    if (matchingPerfil) {
      createForm.perfil_social_id = matchingPerfil.id;
      createForm.plataforma = matchingPerfil.plataforma;
    } else if (perfiles.length > 0) {
      createForm.perfil_social_id = perfiles[0].id;
      createForm.plataforma = perfiles[0].plataforma;
    } else {
      createForm.plataforma = activePlatform.toLowerCase();
    }
  } else if (perfiles.length > 0) {
    createForm.perfil_social_id = perfiles[0].id;
    createForm.plataforma = perfiles[0].plataforma;
  }

  // 3. Eje inicial
  if (props.initialEjeId) {
    createForm.eje_tematico_id = props.initialEjeId;
  }

  // 4. Tipo pauta inicial
  if (props.initialTipoPauta) {
    createForm.tipo_pauta = props.initialTipoPauta;
  }
};

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    initForm();
  }
});

const perfilesCandidatoSeleccionado = computed(() => {
  const c = props.candidatos.find(cand => cand.id === Number(createForm.candidato_id));
  return c?.perfiles_sociales || c?.perfilesSociales || [];
});

const platformMeta = computed(() => {
  const plat = (createForm.plataforma || 'instagram').toLowerCase();
  switch (plat) {
    case 'facebook':
      return {
        label: '🔗 Enlace o código embed de la publicación de Facebook:',
        placeholder: 'https://www.facebook.com/... o pega el código <iframe> del plugin',
        nombre: 'Facebook',
      };
    case 'tiktok':
      return {
        label: '🔗 Enlace del Video de TikTok:',
        placeholder: 'https://www.tiktok.com/@.../video/...',
        nombre: 'TikTok',
      };
    case 'x_twitter':
    case 'twitter':
      return {
        label: '🔗 Enlace del Post / Tweet de X (Twitter):',
        placeholder: 'https://x.com/.../status/...',
        nombre: 'X (Twitter)',
      };
    case 'youtube':
      return {
        label: '🔗 Enlace del Video o Short de YouTube:',
        placeholder: 'https://www.youtube.com/watch?v=... o https://youtu.be/...',
        nombre: 'YouTube',
      };
    case 'threads':
      return {
        label: '🔗 Enlace del Post de Threads:',
        placeholder: 'https://www.threads.net/@.../post/...',
        nombre: 'Threads',
      };
    case 'linkedin':
      return {
        label: '🔗 Enlace de la Publicación de LinkedIn:',
        placeholder: 'https://www.linkedin.com/posts/...',
        nombre: 'LinkedIn',
      };
    case 'instagram':
    default:
      return {
        label: '🔗 Enlace de la Publicación o Reel de Instagram:',
        placeholder: 'https://www.instagram.com/p/... o /reel/...',
        nombre: 'Instagram',
      };
  }
});

/**
 * Normaliza cualquier texto pegado en el campo URL/embed del Fast-Flow.
 * Soporta: URL directa, <iframe> FB plugin, plugin URL directa (sin iframe),
 * <blockquote> de Instagram, embed de Twitter/X, y cualquier URL suelta.
 * @param {string} raw - El texto crudo que el usuario pegó
 * @returns {string} La URL limpia y directa de la publicación
 */
const normalizarUrlPegada = (raw) => {
  if (!raw || typeof raw !== 'string') return raw;
  const txt = raw.trim();
  if (txt.length < 8) return txt;

  // Caso 1: Twitter/X status (máxima prioridad)
  const twitterMatch = txt.match(/(?:twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/status\/(\d+)/i);
  if (twitterMatch) {
    const url = twitterMatch[0];
    return url.startsWith('http') ? url : 'https://' + url;
  }

  // Caso 2: Facebook plugin URL dentro de un <iframe src="...">
  const fbIframeMatch = txt.match(/src=["']([^"']+plugins\/(?:post|video)\.php[^"']*)['"]/i);
  if (fbIframeMatch) {
    const pluginSrc = fbIframeMatch[1].replace(/&amp;/g, '&');
    const hrefMatch = pluginSrc.match(/\bhref=([^&"']+)/i);
    if (hrefMatch) return decodeURIComponent(hrefMatch[1]);
  }

  // Caso 3: Facebook plugin URL directa (sin <iframe>)
  // Ejemplo: https://www.facebook.com/plugins/post.php?href=https%3A...&show_text=true
  const fbPluginDirecto = txt.match(/plugins\/(?:post|video)\.php[^"'<\s]*\bhref=([^&"'<\s]+)/i);
  if (fbPluginDirecto) return decodeURIComponent(fbPluginDirecto[1]);

  // Caso 4: Instagram blockquote embed
  const igBlockquote = txt.match(/data-instgrm-permalink="([^"]+)"/i);
  if (igBlockquote) return igBlockquote[1].replace(/&amp;/g, '&');

  // Caso 5: Twitter/X blockquote embed (href del último <a> con /status/)
  if (txt.includes('twitter-tweet') || txt.includes('class="tweet"')) {
    const tweetLinks = [...txt.matchAll(/href="(https?:\/\/(?:twitter\.com|x\.com)\/[^"]+)"/gi)];
    if (tweetLinks.length) {
      const last = tweetLinks[tweetLinks.length - 1][1];
      if (last.includes('/status/')) return last.replace(/&amp;/g, '&');
    }
  }

  // Caso 6: <iframe src="..."> genérico
  const iframeMatch = txt.match(/(?:iframe|frame)[^>]+src=["']([^"']+)['"]/i);
  if (iframeMatch) return iframeMatch[1].replace(/&amp;/g, '&');

  // Caso 7: href="..." en cualquier etiqueta HTML
  const hrefMatch = txt.match(/href=["']([^"']+)['"]/i);
  if (hrefMatch) {
    try {
      const u = new URL(hrefMatch[1]);
      return u.href;
    } catch {}
  }

  // Caso 8: URL suelta en el texto (fallback universal)
  const urlMatch = txt.match(/https?:\/\/[^\s"'<>]+/i);
  if (urlMatch) return urlMatch[0].replace(/[.,;:)\]}>]+$/, '');

  // Sin coincidencia: devolver tal cual
  return txt;
};

watch(() => createForm.url_post, (newUrl) => {
  if (!newUrl || typeof newUrl !== 'string' || newUrl.trim().length < 8) return;

  // Limpiar embeds/iframes automáticamente — si el valor cambió, actualizar el campo
  const cleaned = normalizarUrlPegada(newUrl);
  if (cleaned !== newUrl) {
    createForm.url_post = cleaned;
    return; // El watcher se re-ejecutará con el valor limpio
  }

  const lower = newUrl.toLowerCase();
  let detectedPlatform = null;
  if (lower.includes('facebook.com') || lower.includes('fb.watch') || lower.includes('fb.com')) {
    detectedPlatform = 'facebook';
  } else if (lower.includes('instagram.com')) {
    detectedPlatform = 'instagram';
  } else if (lower.includes('tiktok.com')) {
    detectedPlatform = 'tiktok';
  } else if (lower.includes('twitter.com') || lower.includes('x.com')) {
    detectedPlatform = 'x_twitter';
  } else if (lower.includes('threads.net') || lower.includes('threads.com')) {
    detectedPlatform = 'threads';
  } else if (lower.includes('youtube.com') || lower.includes('youtu.be')) {
    detectedPlatform = 'youtube';
  } else if (lower.includes('linkedin.com')) {
    detectedPlatform = 'linkedin';
  }

  if (detectedPlatform && detectedPlatform !== createForm.plataforma) {
    createForm.plataforma = detectedPlatform;
    const cand = props.candidatos.find(c => c.id === Number(createForm.candidato_id));
    const perfiles = cand?.perfiles_sociales || cand?.perfilesSociales || [];
    const matching = perfiles.find(p => p.plataforma.toLowerCase() === detectedPlatform);
    if (matching) {
      createForm.perfil_social_id = matching.id;
    }
  }
});

const localDuplicatePost = computed(() => {
  if (!createForm.url_post || createForm.url_post.trim().length < 10) return null;
  const cleanUrl = createForm.url_post.trim().toLowerCase().replace(/\?.*$/, '').replace(/\/+$/, '');
  return props.publicaciones.find(p => {
    if (!p.url_post) return false;
    const pClean = p.url_post.trim().toLowerCase().replace(/\?.*$/, '').replace(/\/+$/, '');
    return pClean === cleanUrl;
  });
});

const duplicateAlert = computed(() => localDuplicatePost.value || duplicateDetectedPost.value);

const ultimaPublicacionRegistrada = computed(() => {
  const cid = Number(createForm.candidato_id);
  const plat = (createForm.plataforma || '').toLowerCase();

  const matches = props.publicaciones.filter(p => {
    const matchCand = !cid || p.candidato?.id === cid;
    const matchPlat = !plat || (p.plataforma || p.perfil_social?.plataforma || '').toLowerCase() === plat;
    return matchCand && matchPlat;
  });

  if (matches.length === 0) return null;

  return [...matches].sort((a, b) => {
    const dateA = new Date(a.fecha_publicacion_raw || a.fecha_publicacion || 0);
    const dateB = new Date(b.fecha_publicacion_raw || b.fecha_publicacion || 0);
    return dateB - dateA;
  })[0];
});

const onCandidatoChange = () => {
  const cand = props.candidatos.find(c => c.id === Number(createForm.candidato_id));
  const perfiles = cand?.perfiles_sociales || cand?.perfilesSociales || [];
  const activePlatform = props.initialPlataforma;
  
  if (activePlatform) {
    const matchingPerfil = perfiles.find(p => p.plataforma.toLowerCase() === activePlatform.toLowerCase());
    if (matchingPerfil) {
      createForm.perfil_social_id = matchingPerfil.id;
      createForm.plataforma = matchingPerfil.plataforma;
      return;
    }
  }

  if (perfiles.length > 0) {
    createForm.perfil_social_id = perfiles[0].id;
    createForm.plataforma = perfiles[0].plataforma;
  } else {
    createForm.perfil_social_id = '';
  }
};

const onPerfilChange = () => {
  const perfiles = perfilesCandidatoSeleccionado.value;
  const p = perfiles.find(item => item.id === Number(createForm.perfil_social_id));
  if (p) {
    createForm.plataforma = p.plataforma;
  }
};

const autocompletarScrape = async () => {
  if (!createForm.url_post) {
    scrapeErrorMsg.value = 'Ingresa primero la URL de la publicación de Instagram, TikTok o Facebook.';
    return;
  }

  isScraping.value = true;
  scrapeSuccessMsg.value = '';
  scrapeErrorMsg.value = '';

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const response = await fetch('/publicaciones/scrape-post', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        url_post: createForm.url_post,
        plataforma: createForm.plataforma,
      }),
    });

    const data = await response.json();

    if (response.ok && data.success) {
      if (data.data.tipo_formato) createForm.tipo_formato = data.data.tipo_formato;
      if (data.data.total_likes > 0 || !createForm.total_likes) {
        createForm.total_likes = data.data.total_likes || 0;
      }
      if (data.data.total_comentarios > 0 || !createForm.total_comentarios) {
        createForm.total_comentarios = data.data.total_comentarios || 0;
      }
      if (data.data.total_vistas || data.data.vistas_organicas) {
        createForm.vistas_organicas = data.data.total_vistas || data.data.vistas_organicas;
      }
      if (data.data.contenido_resumen) createForm.contenido_resumen = data.data.contenido_resumen;
      if (data.data.fecha_publicacion) createForm.fecha_publicacion = data.data.fecha_publicacion.slice(0, 16);
      if (data.data.url_post) createForm.url_post = data.data.url_post;
      if (data.data.media_url) createForm.media_url = data.data.media_url;
      if (data.data.plataforma) {
        createForm.plataforma = data.data.plataforma;
        const matchingPerfil = perfilesCandidatoSeleccionado.value.find(p => p.plataforma.toLowerCase() === data.data.plataforma.toLowerCase());
        if (matchingPerfil) {
          createForm.perfil_social_id = matchingPerfil.id;
        }
      }

      const lk = data.data.total_likes || 0;
      if (createForm.plataforma === 'facebook') {
        createForm.reacciones_detalladas.like = lk;
        createForm.reacciones_detalladas.love = 0;
        createForm.reacciones_detalladas.care = 0;
        createForm.reacciones_detalladas.haha = 0;
        createForm.reacciones_detalladas.wow = 0;
        createForm.reacciones_detalladas.sad = 0;
        createForm.reacciones_detalladas.angry = 0;
      } else {
        createForm.total_likes = lk;
      }

      if (data.data.sospecha_pauta) {
        adDetectionAlert.value = {
          motivo: data.data.motivo_sospecha_pauta || 'Detectados parámetros de campaña publicitaria o de anuncio en el enlace.',
          sugerido: data.data.tipo_pauta_sugerido || 'organico_impulsado',
        };
      } else {
        adDetectionAlert.value = null;
      }

      scrapeSuccessMsg.value = '✅ ¡Datos, texto e imagen extraídos exitosamente!';
    } else {
      if (data.ya_registrada) {
        duplicateDetectedPost.value = data;
      }
      scrapeErrorMsg.value = data.error || data.mensaje || 'No se pudieron extraer los datos automáticamente. Puedes completarlos a mano.';
    }
  } catch (err) {
    scrapeErrorMsg.value = 'Error al consultar el lector de redes. Completa los datos a mano.';
  } finally {
    isScraping.value = false;
  }
};

const aplicarPautaDetectada = () => {
  if (adDetectionAlert.value?.sugerido) {
    createForm.tipo_pauta = adDetectionAlert.value.sugerido;
  } else {
    createForm.tipo_pauta = 'organico_impulsado';
  }
};

watch(() => createForm.reacciones_detalladas, (reacs) => {
  if (createForm.plataforma === 'facebook') {
    const sum = Object.values(reacs).reduce((acc, val) => acc + (Number(val) || 0), 0);
    if (sum > 0) {
      createForm.total_likes = sum;
    }
  }
}, { deep: true });

const netApproval = computed(() => {
  if (createForm.plataforma !== 'facebook') {
    return 100;
  }
  const r = createForm.reacciones_detalladas;
  const pos = (Number(r.like) || 0) + (Number(r.love) || 0) + (Number(r.care) || 0) + (Number(r.haha) || 0) + (Number(r.wow) || 0);
  const neg = (Number(r.sad) || 0) + (Number(r.angry) || 0);
  const tot = pos + neg;
  if (tot === 0) return 100;
  return Math.round(((pos - neg) / tot) * 100);
});

const submitCreatePost = () => {
  createForm.clearErrors();

  const payload = {
    ...createForm.data(),
    candidato_id: createForm.candidato_id ? Number(createForm.candidato_id) : null,
    perfil_social_id: createForm.perfil_social_id ? Number(createForm.perfil_social_id) : null,
    eje_tematico_id: createForm.eje_tematico_id ? Number(createForm.eje_tematico_id) : null,
    total_likes: Number(createForm.total_likes || 0),
    total_comentarios: Number(createForm.total_comentarios || 0),
    total_compartidos: Number(createForm.total_compartidos || 0),
    total_republicados: Number(createForm.total_republicados || 0),
    total_guardados: Number(createForm.total_guardados || 0),
    vistas_organicas: Number(createForm.vistas_organicas || 0),
    vistas_pagadas: Number(createForm.vistas_pagadas || 0),
    monto_invertido_pauta: Number(createForm.monto_invertido_pauta || 0),
    me_gusta: createForm.plataforma === 'facebook' 
      ? Number(createForm.reacciones_detalladas.like || createForm.total_likes || 0)
      : Number(createForm.total_likes || 0),
    me_encanta: Number(createForm.reacciones_detalladas.love || 0),
    me_importa: Number(createForm.reacciones_detalladas.care || 0),
    me_divierte: Number(createForm.reacciones_detalladas.haha || 0),
    me_asombra: Number(createForm.reacciones_detalladas.wow || 0),
    me_entristece: Number(createForm.reacciones_detalladas.sad || 0),
    me_enoja: Number(createForm.reacciones_detalladas.angry || 0),
  };

  createForm.transform(() => payload).post('/publicaciones', {
    preserveScroll: true,
    onSuccess: () => {
      emit('update:modelValue', false);
      emit('created');
      createForm.reset();
    },
    onError: (errors) => {
      console.warn('Errores de validación al guardar publicación:', errors);
    }
  });
};
</script>

<template>
  <!-- MODAL DE CARGA INTELIGENTE DE PUBLICACIÓN (+ AUTOCOMPLETAR 1 CLIC) -->
  <div
    v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-950/80 backdrop-blur-sm animate-fade-in overflow-hidden"
  >
    <div class="relative w-full max-w-5xl max-h-[90vh] sm:max-h-[86vh] flex flex-col rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden">
      
      <!-- Modal Header -->
      <div class="flex items-center justify-between px-6 py-3.5 border-b border-slate-100 dark:border-slate-800 shrink-0 bg-slate-50/70 dark:bg-slate-950/70">
        <div class="flex items-center gap-2.5">
          <div class="p-2 rounded-xl bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
            <Sparkles class="w-4 h-4 text-cyan-500" />
          </div>
          <div>
            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
              Cargar Nueva Publicación en el Feed
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">
              Pega el enlace para autocompletar con 1 clic o ingresa los datos de campaña manualmente.
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="emit('update:modelValue', false)"
          class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          title="Cerrar ventana"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Modal Form -->
      <form @submit.prevent="submitCreatePost" class="flex flex-col flex-1 overflow-hidden">
        
        <!-- Scrollable Body Container -->
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4 text-xs font-sans">
          
          <!-- Banner de Errores de Validación -->
          <div
            v-if="createForm.hasErrors"
            class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 space-y-1.5 animate-shake"
          >
            <div class="flex items-center gap-2 font-bold text-xs">
              <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
              <span>No se pudo guardar la publicación. Revisa los siguientes campos:</span>
            </div>
            <ul class="list-disc list-inside text-[11px] space-y-0.5 pl-1">
              <li v-for="(errorMsg, fieldKey) in createForm.errors" :key="fieldKey">
                <strong class="capitalize">{{ fieldKey.replace('_', ' ') }}:</strong> {{ errorMsg }}
              </li>
            </ul>
          </div>

          <!-- Grilla 2 Columnas -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- COLUMNA IZQUIERDA (7 cols) -->
            <div class="lg:col-span-7 space-y-3.5">
              
              <!-- Fila 1: Candidato y Red Social -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Candidato *</label>
                  <select
                    v-model="createForm.candidato_id"
                    @change="onCandidatoChange"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-bold focus:ring-2 focus:ring-cyan-500"
                    :class="createForm.errors.candidato_id ? 'border-rose-500 ring-1 ring-rose-500' : ''"
                  >
                    <option v-for="cand in candidatos" :key="cand.id" :value="cand.id">
                      {{ cand.es_propio ? '⭐ ' : '' }}{{ cand.nombre_completo }}
                    </option>
                  </select>
                  <span v-if="createForm.errors.candidato_id" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.candidato_id }}</span>
                </div>

                <div>
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Canal Social / Red *</label>
                  <select
                    v-model="createForm.perfil_social_id"
                    @change="onPerfilChange"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-bold focus:ring-2 focus:ring-cyan-500"
                    :class="createForm.errors.perfil_social_id ? 'border-rose-500 ring-1 ring-rose-500' : ''"
                  >
                    <option v-for="p in perfilesCandidatoSeleccionado" :key="p.id" :value="p.id">
                      {{ p.plataforma.toUpperCase() }} ({{ p.handle_usuario }})
                    </option>
                  </select>
                  <span v-if="createForm.errors.perfil_social_id" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.perfil_social_id }}</span>
                </div>
              </div>

              <!-- Tarjeta Auxiliar: Última publicación -->
              <div
                v-if="ultimaPublicacionRegistrada"
                class="p-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 flex items-start gap-2.5"
              >
                <div class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 shrink-0 mt-0.5">
                  <Calendar class="w-3.5 h-3.5" />
                </div>
                <div class="text-[11px] leading-tight flex-1 min-w-0">
                  <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 flex-wrap">
                    <span>Último post en {{ (createForm.plataforma || 'red').toUpperCase() }}:</span>
                    <span class="text-cyan-600 dark:text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded-md font-bold text-xs">
                      🗓️ {{ ultimaPublicacionRegistrada.fecha_publicacion_humana || ultimaPublicacionRegistrada.fecha_publicacion }}
                    </span>
                    <span class="text-slate-400 font-normal">({{ ultimaPublicacionRegistrada.fecha_relativa }})</span>
                  </div>
                  <p class="text-slate-500 dark:text-slate-400 mt-1 truncate italic">
                    "{{ ultimaPublicacionRegistrada.contenido_resumen }}"
                  </p>
                </div>
              </div>

              <!-- Alerta Inmediata si se detecta URL duplicada -->
              <div
                v-if="duplicateAlert"
                class="p-3 rounded-2xl bg-amber-500/15 border border-amber-500/40 text-amber-900 dark:text-amber-200 space-y-1 animate-fade-in"
              >
                <div class="flex items-center gap-1.5 font-bold text-xs">
                  <AlertCircle class="w-4 h-4 text-amber-500 shrink-0" />
                  <span>⚠️ Esta publicación ya está registrada en el sistema</span>
                </div>
                <p class="text-[11px] text-amber-800 dark:text-amber-300 font-mono">
                  <strong>ID:</strong> #{{ duplicateAlert.id || duplicateAlert.publicacion_id }} • 
                  <strong>Fecha de Origen:</strong> {{ duplicateAlert.fecha_publicacion }} • 
                  <strong>Autor:</strong> {{ duplicateAlert.autor || duplicateAlert.candidato?.nombre_completo }}
                </p>
                <p v-if="duplicateAlert.contenido_resumen" class="text-[11px] text-slate-500 dark:text-slate-400 italic truncate">
                  "{{ duplicateAlert.contenido_resumen }}"
                </p>
              </div>

              <!-- Fila 2: URL del Post + Botón Scraper 1 Clic -->
              <div class="p-3.5 rounded-2xl bg-cyan-500/5 dark:bg-cyan-500/10 border border-cyan-500/20 space-y-2">
                <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center justify-between">
                  <span>{{ platformMeta.label }}</span>
                  <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400 font-bold uppercase tracking-wider">{{ platformMeta.nombre }}</span>
                </label>
                <div class="flex items-center gap-2">
                  <input
                    v-model="createForm.url_post"
                    type="text"
                    :placeholder="platformMeta.placeholder"
                    class="flex-1 px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs focus:ring-2 focus:ring-cyan-500 font-mono"
                    :class="(createForm.errors.url_post || duplicateAlert) ? 'border-amber-500 ring-1 ring-amber-500' : ''"
                    @paste.prevent="(e) => {
                      const raw = (e.clipboardData || window.clipboardData).getData('text');
                      createForm.url_post = normalizarUrlPegada(raw);
                    }"
                  />
                  <button
                    type="button"
                    @click="autocompletarScrape"
                    :disabled="isScraping"
                    class="px-3.5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition-all shadow-xs hover:scale-102 disabled:opacity-50 cursor-pointer shrink-0"
                  >
                    <RefreshCw class="w-3.5 h-3.5" :class="isScraping ? 'animate-spin' : ''" />
                    <span>{{ isScraping ? 'Leyendo...' : '⚡ Autocompletar (1 Clic)' }}</span>
                  </button>
                </div>

                <div v-if="scrapeSuccessMsg" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                  <CheckCircle2 class="w-3.5 h-3.5 shrink-0" />
                  <span>{{ scrapeSuccessMsg }}</span>
                </div>

                <!-- Banner Interactivo: Detección Temprana de Pauta -->
                <div
                  v-if="adDetectionAlert"
                  class="p-3 rounded-xl bg-violet-500/15 border border-violet-500/40 text-violet-900 dark:text-violet-200 space-y-2 animate-fade-in"
                >
                  <div class="flex items-start justify-between gap-2">
                    <div class="flex items-start gap-2">
                      <DollarSign class="w-4 h-4 text-violet-500 shrink-0 mt-0.5" />
                      <div>
                        <p class="font-bold text-xs flex items-center gap-1">
                          <span>🎯 Sospecha de Pauta Activa / Boosted Post</span>
                          <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-violet-500/20 text-violet-700 dark:text-violet-300 font-black uppercase">Detectada</span>
                        </p>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5 leading-snug">
                          {{ adDetectionAlert.motivo }}
                        </p>
                      </div>
                    </div>
                    <button
                      v-if="createForm.tipo_pauta === 'organico'"
                      type="button"
                      @click="aplicarPautaDetectada"
                      class="px-2.5 py-1 rounded-lg bg-violet-600 hover:bg-violet-500 text-white font-bold text-[11px] shrink-0 transition-all shadow-xs cursor-pointer"
                    >
                      ⚡ Clasificar como Boosted
                    </button>
                    <span
                      v-else
                      class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1 shrink-0"
                    >
                      <CheckCircle2 class="w-3 h-3" /> Clasificada
                    </span>
                  </div>
                </div>

                <div v-if="scrapeErrorMsg" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                  <AlertCircle class="w-3.5 h-3.5 shrink-0" />
                  <span>{{ scrapeErrorMsg }}</span>
                </div>
                <div v-if="createForm.errors.url_post" class="text-[11px] font-bold text-rose-500 flex items-center gap-1">
                  <AlertCircle class="w-3.5 h-3.5 shrink-0" />
                  <span>{{ createForm.errors.url_post }}</span>
                </div>
              </div>

              <!-- Fila 3: Eje Temático, Formato y Fecha -->
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Eje Temático</label>
                  <select
                    v-model="createForm.eje_tematico_id"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-semibold"
                  >
                    <option value="">-- Sin Eje Temático --</option>
                    <optgroup v-for="(ejesInGroup, pilar) in groupedEjes" :key="pilar" :label="pilar">
                      <option v-for="e in ejesInGroup" :key="e.id" :value="e.id">
                        • {{ e.nombre }}
                      </option>
                    </optgroup>
                  </select>
                </div>

                <div>
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Formato *</label>
                  <select
                    v-model="createForm.tipo_formato"
                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
                  >
                    <option value="Reel">Reel / Short / TikTok</option>
                    <option value="Video">Video Largo</option>
                    <option value="Foto">Foto Única</option>
                    <option value="Carrusel">Carrusel / Álbum</option>
                    <option value="Historia">Historia / Story</option>
                    <option value="Tweet">Post Texto / Tweet</option>
                  </select>
                </div>

                <div>
                  <div class="flex items-center justify-between gap-1 mb-1">
                    <label class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1 text-xs">
                      <Calendar class="w-3.5 h-3.5 text-cyan-500" />
                      <span>Fecha de Publicación *</span>
                    </label>
                    <span
                      v-if="!createForm.fecha_publicacion"
                      class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 shrink-0"
                    >
                      Requerida
                    </span>
                    <span
                      v-else
                      class="text-[10px] font-mono font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5 shrink-0"
                    >
                      <CheckCircle2 class="w-3 h-3" />
                      Lista
                    </span>
                  </div>
                  <input
                    v-model="createForm.fecha_publicacion"
                    type="datetime-local"
                    required
                    class="w-full px-3 py-2 rounded-xl font-mono text-xs transition-all bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                    :class="[
                      createForm.errors.fecha_publicacion ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50 dark:bg-rose-950/30' :
                      !createForm.fecha_publicacion ? 'border-amber-400/80 bg-amber-500/5 ring-1 ring-amber-400/40' :
                      ''
                    ]"
                  />
                  <span v-if="createForm.errors.fecha_publicacion" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.fecha_publicacion }}</span>
                  <p class="text-[10px] text-slate-400 mt-1">
                    La fecha y hora que figura en la red social
                  </p>
                </div>
              </div>

              <!-- Fila 4: Tipo de Pauta y Monto Invertido -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                <div>
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tipo de Pauta *</label>
                  <select
                    v-model="createForm.tipo_pauta"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-semibold"
                  >
                    <option value="organico">Orgánica Pura ($0)</option>
                    <option value="organico_impulsado">Orgánica Impulsada (Boost)</option>
                    <option value="pauta_paga">Anuncio Directo / Dark Post</option>
                    <option value="colaboracion_pagada">🌟 Colaboración Pagada / Influencer</option>
                  </select>
                </div>

                <div>
                  <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Inversión Publicitaria (ARS):</label>
                  <input
                    v-model.number="createForm.monto_invertido_pauta"
                    type="number"
                    min="0"
                    step="500"
                    :disabled="createForm.tipo_pauta === 'organico'"
                    placeholder="$0"
                    class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 font-mono disabled:opacity-50"
                  />
                </div>
              </div>

              <!-- Vistas separadas cuando hay inversión publicitaria -->
              <div v-if="createForm.tipo_pauta !== 'organico'" class="grid grid-cols-2 gap-2 mt-2">
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 mb-0.5 flex items-center gap-1">
                    <Eye class="w-3 h-3 text-emerald-500" />
                    Vistas Orgánicas
                  </label>
                  <input
                    v-model.number="createForm.vistas_organicas"
                    type="number"
                    min="0"
                    placeholder="ej. 1200"
                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono text-xs text-emerald-600 dark:text-emerald-400"
                  />
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-violet-600 dark:text-violet-400 mb-0.5 flex items-center gap-1">
                    <Eye class="w-3 h-3 text-violet-500" />
                    Vistas Pagadas (Alcance Pauta)
                  </label>
                  <input
                    v-model.number="createForm.vistas_pagadas"
                    type="number"
                    min="0"
                    placeholder="ej. 8500"
                    class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-violet-500/40 font-mono text-xs text-violet-600 dark:text-violet-400"
                    :class="createForm.monto_invertido_pauta > 0 && createForm.vistas_pagadas === 0 ? 'border-amber-400 ring-1 ring-amber-300' : ''"
                  />
                  <p v-if="createForm.monto_invertido_pauta > 0 && createForm.vistas_pagadas === 0"
                     class="text-[10px] text-amber-600 dark:text-amber-400 mt-0.5 flex items-center gap-1">
                    ⚠️ Hay inversión registrada pero sin vistas pagadas cargadas
                  </p>
                </div>
              </div>

              <!-- Fila 5: Resumen del Contenido / Copy -->
              <div>
                <div class="flex items-center justify-between mb-1">
                  <label class="block font-bold text-slate-700 dark:text-slate-300">Texto / Copy del Post *</label>
                  <span class="text-[11px] text-slate-400 font-mono">{{ createForm.contenido_resumen?.length || 0 }} caracteres</span>
                </div>
                <textarea
                  v-model="createForm.contenido_resumen"
                  rows="3"
                  required
                  placeholder="Escribe o pega aquí el texto completo o resumen de la publicación..."
                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 leading-relaxed text-xs focus:ring-2 focus:ring-cyan-500"
                  :class="createForm.errors.contenido_resumen ? 'border-rose-500 ring-1 ring-rose-500' : ''"
                ></textarea>
                <span v-if="createForm.errors.contenido_resumen" class="text-[11px] font-bold text-rose-500 block mt-0.5">{{ createForm.errors.contenido_resumen }}</span>
              </div>

            </div>

            <!-- COLUMNA DERECHA (5 cols) -->
            <div class="lg:col-span-5 space-y-3.5">
              
              <!-- Preview de Imagen / Portada -->
              <div v-if="createForm.media_url" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                  <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">🖼️ Portada / Imagen Detectada</span>
                  <button
                    type="button"
                    @click="createForm.media_url = ''"
                    class="text-[10px] text-slate-400 hover:text-rose-500 cursor-pointer"
                  >
                    Quitar (✕)
                  </button>
                </div>
                <div class="flex items-center gap-3">
                  <img
                    :src="createForm.media_url"
                    alt="Vista previa de imagen"
                    referrerpolicy="no-referrer"
                    class="w-16 h-16 object-cover rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs shrink-0"
                  />
                  <input
                    v-model="createForm.media_url"
                    type="url"
                    placeholder="https://..."
                    class="flex-1 px-2.5 py-1.5 text-[11px] font-mono rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 truncate"
                  />
                </div>
              </div>

              <!-- CASO 1: INSTAGRAM -->
              <div v-if="createForm.plataforma === 'instagram'" class="p-4 rounded-2xl bg-gradient-to-r from-[#E4405F]/10 via-[#F77737]/5 to-transparent border border-[#E4405F]/20 space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <Heart class="w-4 h-4 text-[#E4405F] fill-[#E4405F]" />
                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de Instagram</span>
                  </div>
                  <span class="text-[10px] font-mono text-[#E4405F] font-bold uppercase">Interacciones</span>
                </div>

                <div class="grid grid-cols-2 gap-2 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-[#E4405F]/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5 flex items-center justify-center gap-1">
                      <Heart class="w-3 h-3 fill-rose-500" />
                      <span>Me gusta (❤️)</span>
                    </label>
                    <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
                      <MessageCircle class="w-3 h-3" />
                      <span>Comentarios (💬)</span>
                    </label>
                    <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5 flex items-center justify-center gap-1">
                      <Repeat class="w-3 h-3" />
                      <span>Republicar (🔁)</span>
                    </label>
                    <input v-model.number="createForm.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
                      <Send class="w-3 h-3" />
                      <span>Compartidos (✈️)</span>
                    </label>
                    <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-2 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
                      <Eye class="w-3 h-3" />
                      <span>Vistas / Plays</span>
                    </label>
                    <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5 flex items-center justify-center gap-1">
                      <Bookmark class="w-3 h-3 fill-amber-500" />
                      <span>Guardados (🔖)</span>
                    </label>
                    <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
                  </div>
                </div>
              </div>

              <!-- CASO 2: FACEBOOK -->
              <div v-else-if="createForm.plataforma === 'facebook'" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-xs text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <Sparkles class="w-3.5 h-3.5 text-blue-500" />
                    <span>Reacciones Facebook</span>
                  </span>
                  <span class="font-mono text-cyan-500 font-bold text-xs">Aprobación: {{ netApproval }}%</span>
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5 text-center font-mono">
                  <div>
                    <span class="text-xs">👍</span>
                    <input v-model.number="createForm.reacciones_detalladas.like" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
                  </div>
                  <div>
                    <span class="text-xs">❤️</span>
                    <input v-model.number="createForm.reacciones_detalladas.love" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
                  </div>
                  <div>
                    <span class="text-xs">🥰</span>
                    <input v-model.number="createForm.reacciones_detalladas.care" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
                  </div>
                  <div>
                    <span class="text-xs">😂</span>
                    <input v-model.number="createForm.reacciones_detalladas.haha" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
                  </div>
                  <div>
                    <span class="text-xs">😮</span>
                    <input v-model.number="createForm.reacciones_detalladas.wow" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
                  </div>
                  <div>
                    <span class="text-xs">😢</span>
                    <input v-model.number="createForm.reacciones_detalladas.sad" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
                  </div>
                  <div>
                    <span class="text-xs">😡</span>
                    <input v-model.number="createForm.reacciones_detalladas.angry" type="number" min="0" class="w-full mt-0.5 p-1 text-center text-[11px] rounded-lg border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" />
                  </div>
                </div>

                <div class="grid grid-cols-3 gap-2 pt-1 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5">👁️ Vistas</label>
                    <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5">💬 Comentarios</label>
                    <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5">🔄 Shares</label>
                    <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>
                </div>
              </div>

              <!-- CASO 3: TIKTOK -->
              <div v-else-if="createForm.plataforma === 'tiktok'" class="p-4 rounded-2xl bg-gradient-to-r from-[#00F2FE]/10 via-[#FF004F]/5 to-transparent border border-[#00F2FE]/20 space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <span class="text-xs">🎵</span>
                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de TikTok</span>
                  </div>
                  <span class="text-[10px] font-mono text-[#00F2FE] font-bold uppercase">Video Corto</span>
                </div>

                <div class="grid grid-cols-2 gap-2 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-[#FF004F]/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-[#FF004F] mb-0.5 flex items-center justify-center gap-1">
                      <Heart class="w-3 h-3 fill-[#FF004F]" />
                      <span>Me gusta (❤️)</span>
                    </label>
                    <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
                      <MessageCircle class="w-3 h-3" />
                      <span>Comentarios (💬)</span>
                    </label>
                    <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5 flex items-center justify-center gap-1">
                      <Bookmark class="w-3 h-3 fill-amber-500" />
                      <span>Favoritos (🔖)</span>
                    </label>
                    <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
                      <Send class="w-3 h-3" />
                      <span>Compartidos (↗️)</span>
                    </label>
                    <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>
                </div>

                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-[#00F2FE]/30 shadow-2xs text-center font-mono">
                  <label class="block text-[10px] uppercase font-bold text-[#00F2FE] mb-0.5 flex items-center justify-center gap-1">
                    <Eye class="w-3 h-3" />
                    <span>Reproducciones / Plays</span>
                  </label>
                  <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
                </div>
              </div>

              <!-- CASO 4A: THREADS -->
              <div v-else-if="createForm.plataforma === 'threads'" class="p-4 rounded-2xl bg-slate-900/50 border border-slate-700/60 space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <span class="font-black text-xs text-slate-100 font-mono">@</span>
                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de Threads</span>
                  </div>
                  <span class="text-[10px] font-mono text-cyan-400 font-bold uppercase">Feed de Conversación</span>
                </div>

                <div class="grid grid-cols-2 gap-2 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-rose-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5 flex items-center justify-center gap-1">
                      <Heart class="w-3 h-3 fill-rose-500" />
                      <span>Me gusta (❤️)</span>
                    </label>
                    <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
                      <MessageCircle class="w-3 h-3" />
                      <span>Respuestas (💬)</span>
                    </label>
                    <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5 flex items-center justify-center gap-1">
                      <Repeat class="w-3 h-3" />
                      <span>Reposts (🔁)</span>
                    </label>
                    <input v-model.number="createForm.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
                      <Send class="w-3 h-3" />
                      <span>Compartir (✈️)</span>
                    </label>
                    <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-indigo-600 dark:text-indigo-400" />
                  </div>
                </div>

                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
                  <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
                    <Eye class="w-3 h-3" />
                    <span>Visualizaciones / Vistas</span>
                  </label>
                  <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
                </div>
              </div>

              <!-- CASO 4B: X / TWITTER -->
              <div v-else-if="createForm.plataforma === 'x_twitter' || createForm.plataforma === 'twitter'" class="p-4 rounded-2xl bg-slate-900/50 border border-slate-700/60 space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-1.5">
                    <span class="font-black text-xs text-slate-100 font-mono">𝕏</span>
                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Métricas de X (Twitter)</span>
                  </div>
                  <span class="text-[10px] font-mono text-cyan-400 font-bold uppercase">Timeline</span>
                </div>

                <div class="grid grid-cols-2 gap-2 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-rose-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5 flex items-center justify-center gap-1">
                      <Heart class="w-3 h-3 fill-rose-500" />
                      <span>Me gusta (❤️)</span>
                    </label>
                    <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-blue-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5 flex items-center justify-center gap-1">
                      <MessageCircle class="w-3 h-3" />
                      <span>Respuestas (💬)</span>
                    </label>
                    <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5 flex items-center justify-center gap-1">
                      <Repeat class="w-3 h-3" />
                      <span>Reposts (🔁)</span>
                    </label>
                    <input v-model.number="createForm.total_republicados" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5 flex items-center justify-center gap-1">
                      <Bookmark class="w-3 h-3 fill-amber-500" />
                      <span>Guardados (🔖)</span>
                    </label>
                    <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-amber-600 dark:text-amber-400" />
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-2 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
                      <Eye class="w-3 h-3" />
                      <span>Impresiones</span>
                    </label>
                    <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
                  </div>
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-indigo-500/30 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-indigo-500 mb-0.5 flex items-center justify-center gap-1">
                      <Send class="w-3 h-3" />
                      <span>Compartidos (↗️)</span>
                    </label>
                    <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
                  </div>
                </div>
              </div>

              <!-- CASO 5: OTRAS REDES (YOUTUBE, LINKEDIN) -->
              <div v-else class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-xs text-slate-700 dark:text-slate-300">Métricas ({{ createForm.plataforma.toUpperCase() }}):</span>
                  <span class="font-mono text-cyan-500 font-bold text-xs">Total</span>
                </div>

                <div class="grid grid-cols-2 gap-2 font-mono text-center">
                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-rose-500 mb-0.5">👍 Me gusta</label>
                    <input v-model.number="createForm.total_likes" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-blue-500 mb-0.5">💬 Comentarios</label>
                    <input v-model.number="createForm.total_comentarios" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5">🔄 Shares</label>
                    <input v-model.number="createForm.total_compartidos" type="number" min="0" class="w-full text-center text-xs font-bold" />
                  </div>

                  <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs">
                    <label class="block text-[10px] uppercase font-bold text-amber-500 mb-0.5">🔖 Guardados</label>
                    <input v-model.number="createForm.total_guardados" type="number" min="0" class="w-full text-center text-xs font-bold text-slate-800 dark:text-slate-100" />
                  </div>
                </div>

                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/30 shadow-2xs text-center font-mono">
                  <label class="block text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 mb-0.5 flex items-center justify-center gap-1">
                    <Eye class="w-3 h-3" />
                    <span>Reproducciones / Alcance</span>
                  </label>
                  <input v-model.number="createForm.vistas_organicas" type="number" min="0" class="w-full text-center text-xs font-bold text-cyan-600 dark:text-cyan-400" />
                </div>
              </div>

            </div>

          </div>

        </div>

        <!-- Sticky Footer -->
        <div class="flex items-center justify-between px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-950/95 backdrop-blur-md shrink-0 sticky bottom-0 z-20">
          <div>
            <span v-if="createForm.hasErrors" class="text-xs font-bold text-rose-500 flex items-center gap-1.5">
              <AlertCircle class="w-4 h-4 text-rose-500 animate-bounce" />
              <span>Corrige los errores antes de guardar</span>
            </span>
            <span v-else class="text-xs font-mono text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
              <Sparkles class="w-3.5 h-3.5 text-cyan-500" />
              <span>Canal: <strong class="text-slate-800 dark:text-slate-200 uppercase">{{ createForm.plataforma }}</strong></span>
            </span>
          </div>

          <div class="flex items-center gap-2.5">
            <button
              type="button"
              @click="emit('update:modelValue', false)"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-bold transition-all cursor-pointer text-xs"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="createForm.processing"
              class="px-6 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/20 disabled:opacity-50 cursor-pointer flex items-center gap-1.5 hover:scale-102 transition-all"
            >
              <RefreshCw v-if="createForm.processing" class="w-3.5 h-3.5 animate-spin" />
              <span>{{ createForm.processing ? 'Guardando en Feed...' : 'Publicar en Feed' }}</span>
            </button>
          </div>
        </div>

      </form>

    </div>
  </div>
</template>
