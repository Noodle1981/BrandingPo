<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  Zap,
  Send,
  Sparkles,
  DollarSign,
  Star,
  Clock,
  Eye,
  CheckCircle2,
  AlertCircle,
  HelpCircle,
  Link2
} from '@lucide/vue';
import MediaEmbed from '../../Components/MediaEmbed.vue';

const props = defineProps({
  candidatos: {
    type: Array,
    default: () => [],
  },
  ejes: {
    type: Array,
    default: () => [],
  },
  ultimas_cargas: {
    type: Array,
    default: () => [],
  }
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);

const form = useForm({
  candidato_id: props.candidatos[0]?.id || '',
  perfil_social_id: props.candidatos[0]?.perfiles_sociales?.[0]?.id || '',
  eje_tematico_id: props.ejes[0]?.id || '',
  url_post: '',
  media_url: '',
  fecha_publicacion: new Date().toISOString().slice(0, 16),
  tipo_formato: 'Reel',
  tipo_pauta: 'organico', // 'organico' | 'pauta_paga'
  monto_invertido_pauta: 0,
  vistas_organicas: 15000,
  vistas_pagadas: 0,
  contenido_resumen: '',
  total_likes: 1200,
  total_comentarios: 45,
  total_compartidos: 80,
  termometro_humor_social: 4,
  comentario_destacado: '',
  figura_acompanante: '',
});

const selectedCandidate = computed(() => {
  return props.candidatos.find(c => c.id == form.candidato_id) || props.candidatos[0];
});

const availableProfiles = computed(() => {
  return selectedCandidate.value?.perfiles_sociales || [];
});

const currentPlatform = computed(() => {
  const p = availableProfiles.value.find(prof => prof.id == form.perfil_social_id);
  return p ? p.plataforma : 'facebook';
});

watch(() => form.candidato_id, () => {
  if (availableProfiles.value.length > 0) {
    form.perfil_social_id = availableProfiles.value[0].id;
  }
});

// Autodetección inteligente al pegar un enlace
watch(() => form.url_post, (newUrl) => {
  if (!newUrl) return;
  const url = newUrl.toLowerCase();
  
  if (url.includes('facebook.com') || url.includes('fb.watch')) {
    const fb = availableProfiles.value.find(p => p.plataforma === 'facebook');
    if (fb) form.perfil_social_id = fb.id;
    if (url.includes('photo') || url.includes('set=')) form.tipo_formato = 'Foto';
    else if (url.includes('watch') || url.includes('reel') || url.includes('video')) form.tipo_formato = 'Video';
  } else if (url.includes('instagram.com')) {
    const ig = availableProfiles.value.find(p => p.plataforma === 'instagram');
    if (ig) form.perfil_social_id = ig.id;
    if (url.includes('/reel/')) form.tipo_formato = 'Reel';
    else if (url.includes('/p/')) form.tipo_formato = 'Foto';
  } else if (url.includes('tiktok.com')) {
    const tt = availableProfiles.value.find(p => p.plataforma === 'tiktok');
    if (tt) form.perfil_social_id = tt.id;
    form.tipo_formato = 'Reel';
  } else if (url.includes('youtube.com') || url.includes('youtu.be')) {
    const yt = availableProfiles.value.find(p => p.plataforma === 'youtube');
    if (yt) form.perfil_social_id = yt.id;
    form.tipo_formato = url.includes('/shorts/') ? 'Shorts' : 'Video';
  } else if (url.includes('x.com') || url.includes('twitter.com')) {
    const tw = availableProfiles.value.find(p => p.plataforma === 'x_twitter');
    if (tw) form.perfil_social_id = tw.id;
    form.tipo_formato = 'Tweet';
  }
});

watch(() => form.tipo_pauta, (newVal) => {
  if (newVal === 'organico') {
    form.monto_invertido_pauta = 0;
    form.vistas_pagadas = 0;
  } else if (form.monto_invertido_pauta === 0) {
    form.monto_invertido_pauta = 25000;
    form.vistas_pagadas = 40000;
  }
});

const formatos = [
  'Reel',
  'Video',
  'Foto',
  'Carrusel',
  'Tweet',
  'Hilo/Thread',
  'Shorts',
  'Articulo'
];

const submitFastFlow = () => {
  if (!canWrite.value) return;
  form.post('/fast-flow', {
    preserveScroll: true,
    onSuccess: () => {
      form.url_post = '';
      form.media_url = '';
      form.contenido_resumen = '';
      form.comentario_destacado = '';
      form.figura_acompanante = '';
    }
  });
};

const handleKeydown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
    e.preventDefault();
    submitFastFlow();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});

const formatNumber = (num) => {
  if (!num) return '0';
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};
</script>

<template>
  <Head title="Carga Rápida Fast-Flow Desk" />

  <WarRoomLayout>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2">
          <Zap class="w-6 h-6 text-cyan-500" />
          <h1 class="text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
            Fast-Flow Entry Desk
          </h1>
          <span class="text-[10px] uppercase font-mono font-bold px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/40">
            Ergonómico • Atajo: Ctrl+Enter
          </span>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
          Formulario adaptativo de alta velocidad para registro de publicaciones, métricas y pauta publicitaria.
        </p>
      </div>
    </div>

    <!-- Read-Only Notice for Visualizer -->
    <div
      v-if="!canWrite"
      class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs flex items-center gap-3"
    >
      <AlertCircle class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" />
      <div>
        <strong>Modo de Carga Deshabilitado:</strong> Tu rol actual es <strong>Visualizador</strong>. Puedes observar la estructura del Fast-Flow Desk, pero el botón de guardado está restringido para consultores y administradores.
      </div>
    </div>

    <!-- Main Workspace: Form Desk + Recent Entries -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left 8 Cols: Fast-Flow Form Desk -->
      <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xs">
        <form @submit.prevent="submitFastFlow" class="space-y-5 text-sm">
          <!-- Step 1: Candidate Selector with Visual Pills -->
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
              1. Selecciona el Candidato Político *
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              <button
                v-for="cand in candidatos"
                :key="cand.id"
                type="button"
                @click="form.candidato_id = cand.id"
                class="p-3 rounded-2xl border text-left flex items-center gap-3 transition-all cursor-pointer"
                :class="form.candidato_id == cand.id ? 'border-cyan-500 bg-cyan-500/10 dark:bg-cyan-500/20 ring-2 ring-cyan-500/30 shadow-xs' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/60 hover:border-slate-400'"
              >
                <img
                  :src="cand.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(cand.nombre_completo)}`"
                  class="w-10 h-10 rounded-xl object-cover border"
                  :style="{ borderColor: cand.color_hex || '#06b6d4' }"
                />
                <div class="min-w-0 flex-1">
                  <p class="font-bold text-slate-900 dark:text-slate-100 text-xs sm:text-sm truncate">
                    {{ cand.nombre_completo }}
                  </p>
                  <Badge variant="estado" :value="cand.estado_politico" size="sm" class="mt-0.5" />
                </div>
              </button>
            </div>
          </div>

          <!-- Step 2: Social Network Pill Selector & Post Format -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <!-- Platform -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                2. Red Social *
              </label>
              <div v-if="availableProfiles.length" class="flex flex-wrap gap-1.5">
                <button
                  v-for="p in availableProfiles"
                  :key="p.id"
                  type="button"
                  @click="form.perfil_social_id = p.id"
                  class="px-3 py-1.5 rounded-xl border text-xs font-mono font-semibold transition-all cursor-pointer"
                  :class="form.perfil_social_id == p.id ? 'bg-cyan-500 text-slate-950 font-bold border-cyan-500 shadow-xs' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300'"
                >
                  {{ p.plataforma.toUpperCase() }} ({{ p.handle_usuario }})
                </button>
              </div>
              <p v-else class="text-xs text-slate-400">Sin perfiles sociales cargados.</p>
            </div>

            <!-- Format -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
                Formato de Contenido *
              </label>
              <select
                v-model="form.tipo_formato"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-medium"
              >
                <option v-for="f in formatos" :key="f" :value="f">{{ f }}</option>
              </select>
            </div>
          </div>

          <!-- Step 3: Thematic Axis & Date -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Eje Temático / Narrativa
              </label>
              <select
                v-model="form.eje_tematico_id"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500"
              >
                <option value="">Sin clasificar</option>
                <option v-for="eje in ejes" :key="eje.id" :value="eje.id">
                  {{ eje.nombre }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1">
                Fecha & Hora de Publicación *
              </label>
              <input
                v-model="form.fecha_publicacion"
                type="datetime-local"
                required
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <!-- Step 4: URL / Link del Post (Facebook, Instagram, YouTube, TikTok, X, Fotos) -->
          <div class="space-y-2">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
              Enlace directo del Post / Video / Foto (URL)
            </label>
            <div class="relative">
              <input
                v-model="form.url_post"
                type="url"
                placeholder="Pega aquí el link de Facebook, Instagram, YouTube, TikTok o X..."
                class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-xs sm:text-sm focus:ring-2 focus:ring-cyan-500 font-mono"
              />
              <Link2 class="w-4 h-4 text-cyan-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>

            <!-- Live Media Preview if URL is pasted -->
            <div v-if="form.url_post" class="mt-2.5">
              <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">
                Previsualización del Contenido Multimedia:
              </span>
              <MediaEmbed
                :url="form.url_post"
                :formato="form.tipo_formato"
                :plataforma="currentPlatform"
              />
            </div>
          </div>

          <!-- Step 5: Post Content Textarea -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                Texto de la Publicación / Resumen *
              </label>
              <span class="text-[11px] font-mono text-slate-400">
                {{ form.contenido_resumen.length }} caracteres
              </span>
            </div>
            <textarea
              v-model="form.contenido_resumen"
              required
              rows="3"
              placeholder="Escribe o pega el texto del post, hashtags o resumen del discurso..."
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-cyan-500 placeholder-slate-400"
            ></textarea>
          </div>

          <!-- Step 5: Pauta Publicitaria & Monto ($) Switch -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <DollarSign class="w-4 h-4 text-violet-500" />
                <span>Régimen de Distribución (Pauta vs. Orgánico)</span>
              </span>
              <div class="flex items-center gap-2">
                <label class="text-xs text-slate-600 dark:text-slate-400">
                  <input
                    type="radio"
                    value="organico"
                    v-model="form.tipo_pauta"
                    class="mr-1 text-cyan-500"
                  />
                  Orgánico
                </label>
                <label class="text-xs text-slate-600 dark:text-slate-400">
                  <input
                    type="radio"
                    value="pauta_paga"
                    v-model="form.tipo_pauta"
                    class="mr-1 text-violet-500"
                  />
                  Pauta Paga
                </label>
              </div>
            </div>

            <div v-if="form.tipo_pauta === 'pauta_paga'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-200 dark:border-slate-800">
              <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Monto Invertido ($ ARS)</label>
                <input
                  v-model="form.monto_invertido_pauta"
                  type="number"
                  min="0"
                  step="1000"
                  class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-violet-600 dark:text-violet-400"
                />
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Vistas Orgánicas</label>
                <input
                  v-model="form.vistas_organicas"
                  type="number"
                  min="0"
                  class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono"
                />
              </div>
              <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Vistas Pagadas</label>
                <input
                  v-model="form.vistas_pagadas"
                  type="number"
                  min="0"
                  class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono"
                />
              </div>
            </div>
          </div>

          <!-- Step 6: Reactions & Metrics Row -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1">Total Likes / Reacciones</label>
              <input
                v-model="form.total_likes"
                type="number"
                min="0"
                class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1">Comentarios</label>
              <input
                v-model="form.total_comentarios"
                type="number"
                min="0"
                class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1">Compartidos</label>
              <input
                v-model="form.total_compartidos"
                type="number"
                min="0"
                class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold"
              />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1">Humor Social (1 a 5★)</label>
              <select
                v-model="form.termometro_humor_social"
                class="w-full px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-amber-500"
              >
                <option :value="5">⭐⭐⭐⭐⭐ (5) Muy Favorable</option>
                <option :value="4">⭐⭐⭐⭐ (4) Favorable</option>
                <option :value="3">⭐⭐⭐ (3) Neutro</option>
                <option :value="2">⭐⭐ (2) Crítico</option>
                <option :value="1">⭐ (1) Muy Crítico</option>
              </select>
            </div>
          </div>

          <!-- Step 7: Comments & Alliances -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                Comentario Destacado (Top Comentario)
              </label>
              <input
                v-model="form.comentario_destacado"
                type="text"
                placeholder="ej. ¡Excelente propuesta intendente!"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">
                Figuras Acompañantes / Alianzas (separadas por coma)
              </label>
              <input
                v-model="form.figura_acompanante"
                type="text"
                placeholder="ej. Gobernador Prov., Ministro X"
                class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <!-- Submit Button -->
          <div class="pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-400 flex items-center gap-1 font-mono">
              <Clock class="w-3.5 h-3.5" />
              <span>Presiona <strong>Ctrl + Enter</strong> para guardar inmediatamente.</span>
            </span>

            <button
              type="submit"
              :disabled="form.processing || !canWrite"
              class="px-6 py-3 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-sm transition-all shadow-md shadow-cyan-500/25 flex items-center gap-2 disabled:opacity-50 cursor-pointer"
            >
              <Send class="w-4 h-4" />
              <span>{{ form.processing ? 'Guardando...' : 'Registrar en Fast-Flow' }}</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Right 4 Cols: Recent Entries Log -->
      <div class="lg:col-span-4 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Clock class="w-4 h-4 text-cyan-500" />
            <span>Últimas Cargas Rápidas</span>
          </h2>
          <span class="text-xs font-mono text-slate-400">{{ ultimas_cargas.length }} registros</span>
        </div>

        <div class="space-y-2.5">
          <div
            v-for="c in ultimas_cargas"
            :key="c.id"
            class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs text-xs space-y-1.5"
          >
            <div class="flex items-center justify-between">
              <span class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ c.candidato }}</span>
              <Badge :variant="c.plataforma" size="sm" />
            </div>
            <div class="flex items-center justify-between text-slate-500 font-mono text-[11px]">
              <span>{{ c.tipo_formato }} • {{ c.fecha }}</span>
              <Badge variant="pauta" :value="c.tipo_pauta" size="sm" />
            </div>
            <div class="flex items-center justify-between pt-1 border-t border-slate-100 dark:border-slate-800/80 font-mono">
              <span class="text-slate-400">Vistas: <strong>{{ formatNumber(c.vistas) }}</strong></span>
              <span class="text-slate-400">Likes: <strong>{{ formatNumber(c.likes) }}</strong></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </WarRoomLayout>
</template>
