<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  Calendar,
  Check,
  Edit3,
  RefreshCw,
  Trash2,
  X
} from '@lucide/vue';
import Badge from '../Badge.vue';
import SocialAdBadge from './SocialAdBadge.vue';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  canWrite: {
    type: Boolean,
    default: true,
  },
  cardPautaStyles: {
    type: Object,
    required: true,
  },
  isPostInActiveWindow: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['openEdit', 'openDelete', 'convertirBooster', 'sugerirPauta']);

const editandoFecha = ref(false);
const fechaEditada = ref('');
const guardandoFecha = ref(false);
const fechaConfirmadaLocalmente = ref(false);
const fechaHumanaLocal = ref(null);

const esFechaSinConfirmar = computed(() => {
  if (!props.post) return false;
  if (props.post.fecha_confirmada || fechaConfirmadaLocalmente.value) return false;
  if (!props.post.fecha_publicacion_raw || !props.post.fecha_carga) return false;
  const fechaPub = props.post.fecha_publicacion_raw.slice(0, 10);
  return fechaPub === props.post.fecha_carga;
});

const fechaHumanaVisible = computed(() => {
  return fechaHumanaLocal.value || props.post?.fecha_publicacion_humana || props.post?.fecha_publicacion || '';
});

const formatearFechaHumana = (fechaStr) => {
  if (!fechaStr) return '';
  try {
    const d = new Date(fechaStr + 'T12:00:00');
    const anioActual = new Date().getFullYear();
    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const dia = d.getDate();
    const mes = meses[d.getMonth()];
    const anio = d.getFullYear();
    return anio === anioActual ? `${dia} de ${mes}` : `${dia} de ${mes} de ${anio}`;
  } catch (e) {
    return fechaStr;
  }
};

const abrirEdicionFecha = () => {
  fechaEditada.value = props.post.fecha_publicacion_raw?.slice(0, 10) || '';
  editandoFecha.value = true;
};

const cancelarEdicionFecha = () => {
  editandoFecha.value = false;
  fechaEditada.value = '';
};

const confirmarFecha = () => {
  if (!fechaEditada.value) return;
  guardandoFecha.value = true;
  router.patch(
    `/publicaciones/${props.post.id}/fecha`,
    { fecha_publicacion: fechaEditada.value },
    {
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => {
        editandoFecha.value = false;
        guardandoFecha.value = false;
        fechaConfirmadaLocalmente.value = true;
        fechaHumanaLocal.value = formatearFechaHumana(fechaEditada.value);
      },
      onError: () => {
        guardandoFecha.value = false;
      },
    }
  );
};

const puedeSincronizar = computed(() => {
  return Boolean(props.post.url_post && String(props.post.url_post).trim().length > 0);
});

const sincronizando = ref(false);
const syncFeedback = ref('');

const sincronizarPost = async () => {
  if (sincronizando.value) return;
  sincronizando.value = true;
  syncFeedback.value = '';

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const resp = await fetch(`/publicaciones/${props.post.id}/sincronizar`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
      },
    });
    const data = await resp.json();

    if (resp.ok && data.success) {
      syncFeedback.value = `+${data.delta_likes || 0} likes`;
      setTimeout(() => { syncFeedback.value = ''; }, 4000);

      if (data.sospecha_pauta && props.post.tipo_pauta === 'organico') {
        emit('sugerirPauta', {
          visible: true,
          motivo: data.motivo_sospecha_pauta,
          sugerido: data.tipo_pauta_sugerido || 'organico_impulsado',
        });
      } else {
        router.reload({ preserveScroll: true });
      }
    } else {
      syncFeedback.value = data.mensaje || 'Error al sincronizar';
      setTimeout(() => { syncFeedback.value = ''; }, 3000);
    }
  } catch (e) {
    syncFeedback.value = 'Error de conexión';
    setTimeout(() => { syncFeedback.value = ''; }, 3000);
  } finally {
    sincronizando.value = false;
  }
};
</script>

<template>
  <div
    class="p-3.5 sm:p-4 border-b border-slate-100 dark:border-slate-800/80 space-y-2.5 shrink-0 transition-colors"
    :class="cardPautaStyles.headerTint"
  >
    <!-- Fila 1: Autor (Avatar + Nombre + Handle) | Badges de Red & Acciones -->
    <div class="flex items-center justify-between gap-2">
      <div class="flex items-center gap-2.5 min-w-0">
        <div class="relative shrink-0">
          <img
            :src="post.candidato?.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.candidato?.nombre_completo || 'Candidato')}&background=0f172a&color=06b6d4`"
            :alt="post.candidato?.nombre_completo"
            referrerpolicy="no-referrer"
            class="w-10 h-10 rounded-full object-cover border-2 border-slate-200 dark:border-slate-700 shadow-xs"
            @error="$event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(post.candidato?.nombre_completo || 'Candidato')}&background=0f172a&color=06b6d4&size=128&bold=true`"
          />
        </div>

        <div class="min-w-0">
          <h4 class="font-extrabold text-slate-900 dark:text-slate-100 text-sm leading-tight truncate" :title="post.candidato?.nombre_completo">
            {{ post.candidato?.nombre_completo || 'Candidato' }}
          </h4>
          <p class="font-mono text-xs text-slate-500 dark:text-slate-400 truncate">
            {{ post.perfil_social?.handle_usuario || '@cuenta' }}
          </p>
        </div>
      </div>

      <!-- Badges de Red Social & Acciones -->
      <div class="flex items-center gap-1.5 shrink-0">
        <Badge :variant="post.plataforma || post.perfil_social?.plataforma || 'facebook'" size="sm" />

        <div v-if="canWrite" class="flex items-center gap-0.5 ml-1 pl-1 border-l border-slate-200 dark:border-slate-800">
          <button
            v-if="puedeSincronizar"
            type="button"
            @click="sincronizarPost"
            :disabled="sincronizando"
            class="p-1 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer disabled:opacity-50"
            :title="sincronizando ? 'Sincronizando métricas...' : 'Sincronizar métricas en vivo desde la red social'"
          >
            <RefreshCw class="w-3.5 h-3.5" :class="sincronizando ? 'animate-spin text-cyan-500' : ''" />
          </button>
          <span v-if="syncFeedback" class="text-[10px] font-mono font-bold text-emerald-500 px-1 animate-pulse">
            {{ syncFeedback }}
          </span>

          <button
            type="button"
            @click="emit('openEdit')"
            class="p-1 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            title="Editar publicación y métricas"
          >
            <Edit3 class="w-3.5 h-3.5" />
          </button>
          <button
            type="button"
            @click="emit('openDelete')"
            class="p-1 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            title="Eliminar publicación"
          >
            <Trash2 class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Fila 2: Fecha de Origen con Semáforo de Confirmación + Pauta & Sincronización -->
    <div class="flex flex-wrap items-center justify-between gap-x-2 gap-y-2 pt-2 border-t border-slate-100 dark:border-slate-800/60 text-xs">
      <div v-if="!editandoFecha" class="flex items-center gap-1.5 min-w-0 flex-wrap">
        <Calendar class="w-3.5 h-3.5 shrink-0" :class="esFechaSinConfirmar ? 'text-rose-500' : 'text-emerald-500'" />

        <button
          v-if="canWrite"
          type="button"
          @click="abrirEdicionFecha"
          :title="esFechaSinConfirmar ? '⚠️ Fecha pendiente de confirmar — Hacer clic para corregirla' : '✅ Fecha confirmada — Hacer clic para cambiarla'"
          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md font-bold text-[11px] border transition-all cursor-pointer hover:opacity-80 shrink-0 whitespace-nowrap"
          :class="esFechaSinConfirmar
            ? 'bg-rose-500/10 border-rose-400/40 text-rose-700 dark:text-rose-300 animate-pulse'
            : 'bg-emerald-500/10 border-emerald-400/40 text-emerald-700 dark:text-emerald-300'"
        >
          <span>{{ fechaHumanaVisible }}</span>
          <span v-if="esFechaSinConfirmar" class="text-rose-400 font-bold">⚠️</span>
          <span v-else class="text-emerald-400">✓</span>
        </button>

        <span v-else class="font-bold text-[11px] shrink-0 whitespace-nowrap"
          :class="esFechaSinConfirmar ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-300'">
          {{ fechaHumanaVisible }}
        </span>

        <span v-if="post.fecha_relativa" class="text-[10px] text-slate-400 dark:text-slate-500 font-medium shrink-0 whitespace-nowrap">
          ({{ post.fecha_relativa }})
        </span>
      </div>

      <div v-else class="flex items-center gap-1.5 flex-1 min-w-0">
        <Calendar class="w-3.5 h-3.5 text-amber-500 shrink-0" />
        <input
          v-model="fechaEditada"
          type="date"
          class="flex-1 min-w-0 px-2 py-0.5 text-[11px] font-mono rounded-lg border border-amber-400 bg-amber-50 dark:bg-amber-950/40 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-amber-400 focus:outline-none"
          :max="new Date().toISOString().slice(0, 10)"
        />
        <button
          type="button"
          @click="confirmarFecha"
          :disabled="guardandoFecha || !fechaEditada"
          class="p-1 rounded-md bg-emerald-500 text-white hover:bg-emerald-400 disabled:opacity-50 transition-all cursor-pointer shrink-0"
          title="Confirmar fecha"
        >
          <Check class="w-3.5 h-3.5" />
        </button>
        <button
          type="button"
          @click="cancelarEdicionFecha"
          class="p-1 rounded-md bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-300 dark:hover:bg-slate-600 transition-all cursor-pointer shrink-0"
          title="Cancelar"
        >
          <X class="w-3.5 h-3.5" />
        </button>
      </div>

      <SocialAdBadge
        :post="post"
        :card-pauta-styles="cardPautaStyles"
        :can-write="canWrite"
        :es-fecha-sin-confirmar="esFechaSinConfirmar"
        :is-post-in-active-window="isPostInActiveWindow"
        @convertir-booster="emit('convertirBooster')"
      />
    </div>
  </div>
</template>
