<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import SocialCardHeader from './Social/SocialCardHeader.vue';
import SocialCardBody from './Social/SocialCardBody.vue';
import SocialCardEditModal from './SocialCardEditModal.vue';
import SocialReactionsBar from './Social/SocialReactionsBar.vue';
import SocialCommentsThermometer from './Social/SocialCommentsThermometer.vue';
import SocialPautaHistory from './Social/SocialPautaHistory.vue';
import SocialDeleteModal from './Social/SocialDeleteModal.vue';
import SocialPautaAlertModal from './Social/SocialPautaAlertModal.vue';

const props = defineProps({
  post: {
    type: Object,
    default: () => ({}),
  },
  canWrite: {
    type: Boolean,
    default: true,
  },
  ejes: {
    type: Array,
    default: () => [],
  }
});

const page = usePage();
const availableEjes = computed(() => {
  if (props.ejes && props.ejes.length) return props.ejes;
  return page.props.ejes || [];
});

const groupedEjes = computed(() => {
  const list = availableEjes.value || [];
  const groups = {};
  list.forEach((eje) => {
    if (!eje.pilar_principal) return;
    const pilar = eje.pilar_principal;
    if (!groups[pilar]) {
      groups[pilar] = [];
    }
    groups[pilar].push(eje);
  });
  return groups;
});

const showComments = ref(false);
const isEditing = ref(false);
const showDeleteModal = ref(false);
const deleting = ref(false);
const sugerenciaPautaModal = ref(null);

const platform = computed(() => (props.post?.plataforma || props.post?.perfil_social?.plataforma || 'instagram').toLowerCase());
const isInstagram = computed(() => platform.value === 'instagram');
const isFacebook = computed(() => platform.value === 'facebook');
const isThreads = computed(() => platform.value === 'threads');
const isTikTok = computed(() => platform.value === 'tiktok');
const isTwitter = computed(() => platform.value === 'x_twitter' || platform.value === 'twitter');
const isYouTube = computed(() => platform.value === 'youtube');

const isRival = computed(() => {
  if (props.post?.candidato && props.post.candidato.es_propio !== undefined) {
    return !props.post.candidato.es_propio;
  }
  return false;
});

const parseReacciones = (r, totalLikes = 0) => {
  if (r && typeof r === 'object') {
    return {
      me_gusta: Number(r.me_gusta ?? totalLikes),
      me_encanta: Number(r.me_encanta ?? 0),
      me_importa: Number(r.me_importa ?? 0),
      me_divierte: Number(r.me_divierte ?? 0),
      me_asombra: Number(r.me_asombra ?? 0),
      me_entristece: Number(r.me_entristece ?? 0),
      me_enoja: Number(r.me_enoja ?? 0),
    };
  }

  return {
    me_gusta: Number(totalLikes || 0),
    me_encanta: 0,
    me_importa: 0,
    me_divierte: 0,
    me_asombra: 0,
    me_entristece: 0,
    me_enoja: 0,
  };
};

const currentReacciones = computed(() => {
  return parseReacciones(props.post?.reacciones_detalladas, props.post?.total_likes);
});

const hasEmotionalBreakdown = computed(() => {
  const r = currentReacciones.value;
  const multiEmojiCount = (Number(r.me_encanta) || 0) +
    (Number(r.me_importa) || 0) +
    (Number(r.me_divierte) || 0) +
    (Number(r.me_asombra) || 0) +
    (Number(r.me_entristece) || 0) +
    (Number(r.me_enoja) || 0);
  return multiEmojiCount > 0;
});

const scoreImpacto = computed(() => {
  if (props.post?.score_impacto_organico !== undefined && props.post?.score_impacto_organico !== null) {
    return Number(props.post.score_impacto_organico);
  }
  const lk = Number(props.post?.total_likes || 0);
  const cm = Number(props.post?.total_comentarios || 0) * 3;
  const sh = Number(props.post?.total_compartidos || 0) * 5;
  const rp = Number(props.post?.total_republicados || 0) * 10;
  return lk + cm + sh + rp;
});

const isPostInActiveWindow = computed(() => {
  const raw = props.post?.fecha_publicacion_raw || props.post?.fecha_publicacion;
  if (!raw) return false;
  try {
    let d;
    if (typeof raw === 'string' && raw.includes('/')) {
      const parts = raw.split(' ')[0].split('/');
      d = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
    } else {
      d = new Date(raw);
    }
    const limit = new Date(Date.now() - 15 * 24 * 60 * 60 * 1000);
    return !isNaN(d.getTime()) && d >= limit;
  } catch (e) {
    return false;
  }
});

const diasDesdePublicacion = computed(() => {
  const raw = props.post?.fecha_publicacion_raw || props.post?.fecha_publicacion;
  if (!raw) return 999;
  try {
    let d;
    if (typeof raw === 'string' && raw.includes('/')) {
      const parts = raw.split(' ')[0].split('/');
      d = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
    } else {
      d = new Date(raw);
    }
    if (isNaN(d.getTime())) return 999;
    const diffMs = Date.now() - d.getTime();
    return Math.floor(diffMs / (1000 * 60 * 60 * 24));
  } catch (e) {
    return 999;
  }
});

const cardPautaStyles = computed(() => {
  const p = (props.post?.tipo_pauta || 'organico').toLowerCase();
  const dias = diasDesdePublicacion.value;
  const scoreTraccion = props.post?.analisis_traccion?.score_traccion_indexado ?? 50;

  if (scoreTraccion >= 75) {
    return {
      cardClass: 'border-amber-400/80 dark:border-amber-400/70 ring-2 ring-amber-400/40 border-t-4 border-t-amber-400 shadow-[0_4px_35px_-4px_rgba(251,191,36,0.45)] dark:shadow-[0_0_40px_-4px_rgba(251,191,36,0.50)] hover:shadow-[0_8px_45px_-2px_rgba(251,191,36,0.60)]',
      headerTint: 'bg-gradient-to-b from-amber-400/15 via-amber-400/4 to-transparent',
      budgetBadge: 'bg-gradient-to-r from-amber-400 to-yellow-500 text-slate-950 border-amber-300 dark:border-amber-400 shadow-md shadow-amber-400/30 font-black',
      scoreBadge: 'bg-amber-400/20 text-amber-600 dark:text-amber-300 border border-amber-400/40 font-black',
      estadoEstrategico: 'dorado',
    };
  }

  if (p === 'organico' && dias <= 10 && scoreTraccion >= 60) {
    return {
      cardClass: 'border-rose-500/70 dark:border-rose-500/60 ring-2 ring-rose-500/30 border-t-4 border-t-rose-500 shadow-[0_4px_35px_-4px_rgba(244,63,94,0.40)] dark:shadow-[0_0_40px_-4px_rgba(244,63,94,0.45)] hover:shadow-[0_8px_45px_-2px_rgba(244,63,94,0.55)]',
      headerTint: 'bg-gradient-to-b from-rose-500/12 via-rose-500/3 to-transparent',
      budgetBadge: '',
      scoreBadge: 'bg-rose-500/20 text-rose-600 dark:text-rose-400 border border-rose-500/40 font-black',
      estadoEstrategico: 'fuego',
    };
  }

  switch (p) {
    case 'organico_impulsado':
      return {
        cardClass: 'border-cyan-500/60 dark:border-cyan-500/40 ring-1 ring-cyan-500/25 border-t-4 border-t-cyan-500 shadow-[0_4px_30px_-4px_rgba(6,182,212,0.30)] dark:shadow-[0_0_35px_-4px_rgba(6,182,212,0.35)] hover:shadow-[0_8px_40px_-2px_rgba(6,182,212,0.45)]',
        headerTint: 'bg-gradient-to-b from-cyan-500/8 via-cyan-500/2 to-transparent',
        budgetBadge: 'bg-cyan-500 text-slate-950 border-cyan-400/80 shadow-md shadow-cyan-500/30',
        scoreBadge: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold',
        estadoEstrategico: 'booster',
      };
    case 'pauta_paga':
      return {
        cardClass: 'border-violet-500/60 dark:border-violet-500/40 ring-1 ring-violet-500/25 border-t-4 border-t-violet-500 shadow-[0_4px_30px_-4px_rgba(139,92,246,0.30)] dark:shadow-[0_0_35px_-4px_rgba(139,92,246,0.35)] hover:shadow-[0_8px_40px_-2px_rgba(139,92,246,0.45)]',
        headerTint: 'bg-gradient-to-b from-violet-500/8 via-violet-500/2 to-transparent',
        budgetBadge: 'bg-violet-600 text-white border-violet-400/80 shadow-md shadow-violet-500/30',
        scoreBadge: 'bg-violet-500/10 text-violet-600 dark:text-violet-400 font-bold',
        estadoEstrategico: 'pauta_paga',
      };
    case 'colaboracion_pagada':
      return {
        cardClass: 'border-amber-500/60 dark:border-amber-500/40 ring-1 ring-amber-500/25 border-t-4 border-t-amber-500 shadow-[0_4px_30px_-4px_rgba(245,158,11,0.30)] dark:shadow-[0_0_35px_-4px_rgba(245,158,11,0.35)] hover:shadow-[0_8px_40px_-2px_rgba(245,158,11,0.45)]',
        headerTint: 'bg-gradient-to-b from-amber-500/8 via-amber-500/2 to-transparent',
        budgetBadge: 'bg-amber-500 text-slate-950 border-amber-400/80 shadow-md shadow-amber-500/30',
        scoreBadge: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold',
        estadoEstrategico: 'colaboracion',
      };
    case 'organico':
    default:
      return {
        cardClass: 'border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md dark:shadow-none',
        headerTint: '',
        budgetBadge: '',
        scoreBadge: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium',
        estadoEstrategico: 'organico',
      };
  }
});

const convertirAPostImpulsado = () => {
  router.put(`/publicaciones/${props.post.id}`, {
    ...props.post,
    tipo_pauta: 'organico_impulsado',
  }, {
    preserveScroll: true,
    onSuccess: () => {
      sugerenciaPautaModal.value = null;
    }
  });
};

const confirmDelete = () => {
  deleting.value = true;
  router.delete(`/publicaciones/${props.post.id}`, {
    preserveScroll: true,
    onFinish: () => {
      deleting.value = false;
      showDeleteModal.value = false;
    }
  });
};
</script>

<template>
  <div
    class="bg-white dark:bg-slate-900 border rounded-2xl overflow-hidden shadow-xs hover:shadow-md dark:shadow-none transition-all duration-200 relative flex flex-col justify-between h-full"
    :class="cardPautaStyles.cardClass"
  >
    <!-- Header Delegado -->
    <SocialCardHeader
      :post="post"
      :can-write="canWrite"
      :card-pauta-styles="cardPautaStyles"
      :is-post-in-active-window="isPostInActiveWindow"
      @open-edit="isEditing = true"
      @open-delete="showDeleteModal = true"
      @convertir-booster="convertirAPostImpulsado"
      @sugerir-pauta="sugerenciaPautaModal = $event"
    />

    <!-- Body Delegado -->
    <SocialCardBody
      :post="post"
      :card-pauta-styles="cardPautaStyles"
    />

    <!-- Barra de Reacciones y Métricas Delegada -->
    <SocialReactionsBar
      :post="post"
      :is-instagram="isInstagram"
      :is-tik-tok="isTikTok"
      :is-twitter="isTwitter"
      :is-threads="isThreads"
      :is-you-tube="isYouTube"
      :has-emotional-breakdown="hasEmotionalBreakdown"
      :current-reacciones="currentReacciones"
      :card-pauta-styles="cardPautaStyles"
      :is-rival="isRival"
      :score-impacto="scoreImpacto"
      :show-comments="showComments"
      @toggle-comments="showComments = !showComments"
    />

    <!-- Termómetro de Humor Social & Comentarios Destacados Delegado -->
    <SocialCommentsThermometer
      v-if="showComments || (post.comentarios_destacados && post.comentarios_destacados.length)"
      :termometro-humor-social="post.termometro_humor_social || 5"
      :comentarios-destacados="post.comentarios_destacados || []"
    />

    <!-- Historial de Pauta & Atribución de Impacto Delegado -->
    <SocialPautaHistory
      :pauta-eventos="post.pauta_eventos || []"
      :total-likes="post.total_likes || 0"
    />

    <!-- Modal de Edición Delegado -->
    <SocialCardEditModal
      v-if="isEditing"
      :show="isEditing"
      :post="post"
      :grouped-ejes="groupedEjes"
      @close="isEditing = false"
      @saved="isEditing = false"
    />

    <!-- Modal de Confirmación de Eliminación Delegado -->
    <SocialDeleteModal
      :show="showDeleteModal"
      :resumen="post.contenido_resumen || ''"
      :deleting="deleting"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
    />

    <!-- Modal Interactivo de Sugerencia de Pauta Activa Delegado -->
    <SocialPautaAlertModal
      :modal="sugerenciaPautaModal"
      @close="sugerenciaPautaModal = null; router.reload({ preserveScroll: true })"
      @convertir="convertirAPostImpulsado"
    />
  </div>
</template>
