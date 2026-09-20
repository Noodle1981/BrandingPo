<script setup>
import { computed } from 'vue';
import { 
  ExternalLink, 
  Trash2, 
  Pencil, 
  UserCheck, 
  AlertTriangle, 
  Heart, 
  Smile, 
  Frown, 
  Plus 
} from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';

const props = defineProps({
  notas: {
    type: Array,
    default: () => [],
  },
  canWrite: {
    type: Boolean,
    default: false,
  },
  sentimientos: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['editar-nota', 'eliminar-nota', 'open-nota-modal']);

const notasFb = computed(() => {
  return props.notas.filter(n => n.origen_tipo === 'facebook');
});

const getTonoBadgeClass = (tono) => {
  switch (tono) {
    case 'favorable':
      return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
    case 'critico':
      return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20';
    default:
      return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
  }
};
</script>

<template>
  <div class="space-y-4">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <SocialPlatformIcon platform="facebook" size="xs" />
        <h2 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
          Noticias Monitoreadas en Facebook ({{ notasFb.length }})
        </h2>
      </div>

      <button
        v-if="canWrite"
        type="button"
        @click="emit('open-nota-modal', { origen_tipo: 'facebook' })"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#1877F2]/10 hover:bg-[#1877F2]/20 text-[#1877F2] border border-[#1877F2]/30 text-xs font-bold transition-all cursor-pointer self-start sm:self-auto"
      >
        <Plus class="w-3.5 h-3.5" />
        <span>Agregar Noticia de Facebook</span>
      </button>
    </div>

    <!-- Sentiment Meter HUD for Facebook (Rules B.4) -->
    <div
      v-if="sentimientos && sentimientos.total_reacciones > 0"
      class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-900 border shadow-xs"
      :class="sentimientos.alerta_crisis ? 'border-rose-500/40 bg-rose-500/5' : 'border-slate-200 dark:border-slate-800'"
    >
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-bold uppercase text-slate-400 tracking-wider">
              Humor Social en Facebook
            </span>
            <span
              v-if="sentimientos.alerta_crisis"
              class="px-2 py-0.5 rounded-full bg-rose-500 text-white font-mono text-[10px] font-black uppercase tracking-wider flex items-center gap-1 animate-pulse"
            >
              <AlertTriangle class="w-3 h-3" />
              Alerta de Crisis (😡 &gt; 15%)
            </span>
          </div>

          <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 mt-1">
            {{ sentimientos.total_reacciones.toLocaleString() }} Reacciones Ciudadanas Auditadas
          </h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Desglose emocional acumulado en las publicaciones de medios que mencionan candidatos.
          </p>
        </div>

        <!-- Emoji Breakdown Bar -->
        <div class="flex flex-wrap items-center gap-2 text-xs font-mono font-bold">
          <div class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 shadow-xs">
            <span>👍</span>
            <span class="text-slate-700 dark:text-slate-300">{{ sentimientos.likes || 0 }}</span>
          </div>
          <div class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 shadow-xs">
            <span>❤️</span>
            <span class="text-rose-600 dark:text-rose-400">{{ sentimientos.love || 0 }}</span>
          </div>
          <div class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 shadow-xs">
            <span>😂</span>
            <span class="text-amber-500">{{ sentimientos.haha || 0 }}</span>
          </div>
          <div class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 shadow-xs">
            <span>😮</span>
            <span class="text-cyan-500">{{ sentimientos.wow || 0 }}</span>
          </div>
          <div class="px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 shadow-xs">
            <span>😢</span>
            <span class="text-indigo-400">{{ sentimientos.sad || 0 }}</span>
          </div>
          <div
            class="px-2.5 py-1.5 rounded-xl border flex items-center gap-1.5 shadow-xs"
            :class="sentimientos.alerta_crisis ? 'bg-rose-500/20 border-rose-500 text-rose-600 dark:text-rose-400' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300'"
          >
            <span>😡</span>
            <span>{{ sentimientos.angry || 0 }} ({{ sentimientos.porcentaje_enojo }}%)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="!notasFb.length"
      class="text-center py-14 px-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl"
    >
      <div class="flex justify-center mb-3 opacity-50">
        <SocialPlatformIcon platform="facebook" size="lg" />
      </div>
      <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
        No hay publicaciones de Facebook auditadas
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        Asegúrate de que los medios tengan cargada su URL de Facebook y haz clic en <strong>"Sincronizar Menciones"</strong>.
      </p>
    </div>

    <!-- List of Facebook Posts -->
    <div v-else class="space-y-3.5">
      <div
        v-for="nota in notasFb"
        :key="nota.id"
        class="p-5 rounded-3xl bg-white dark:bg-slate-900 border shadow-xs transition-all hover:border-slate-300 dark:hover:border-slate-700"
        :class="nota.tono_mencion === 'critico'
          ? 'border-rose-500/30 bg-rose-50/10 dark:bg-rose-950/10'
          : (nota.tono_mencion === 'favorable'
            ? 'border-emerald-500/30 bg-emerald-50/10 dark:bg-emerald-950/10'
            : 'border-slate-200 dark:border-slate-800')"
      >
        <!-- Top Row -->
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
          <div class="flex items-center gap-2">
            <!-- Media Avatar -->
            <div class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700 shadow-xs">
              <img
                v-if="nota.medio?.avatar_url"
                :src="nota.medio.avatar_url"
                :alt="nota.medio.nombre"
                class="w-full h-full object-cover"
                referrerpolicy="no-referrer"
              />
              <SocialPlatformIcon v-else platform="facebook" size="xs" />
            </div>

            <div>
              <span class="font-extrabold text-xs text-slate-900 dark:text-slate-100">
                {{ nota.medio?.nombre }}
              </span>
              <span class="text-xs text-slate-400 font-mono ml-1.5">• {{ nota.fecha }}</span>
            </div>
          </div>

          <!-- Badges & Actions -->
          <div class="flex items-center gap-2">
            <!-- Candidate Badge -->
            <span
              v-if="nota.candidato?.id"
              class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold flex items-center gap-1 border"
              :class="nota.candidato?.es_propio 
                ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20' 
                : 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20'"
            >
              <UserCheck class="w-3 h-3" />
              <span>{{ nota.candidato?.nombre_completo }}</span>
            </span>

            <!-- Tone Badge -->
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-mono font-extrabold uppercase border"
              :class="getTonoBadgeClass(nota.tono_mencion)"
            >
              {{ nota.tono_mencion }}
            </span>

            <!-- Actions -->
            <div v-if="canWrite" class="flex items-center gap-0.5 ml-1">
              <button
                type="button"
                @click="emit('editar-nota', nota)"
                class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-all cursor-pointer"
                title="Editar nota"
              >
                <Pencil class="w-3.5 h-3.5" />
              </button>
              <button
                type="button"
                @click="emit('eliminar-nota', nota)"
                class="p-1 text-slate-400 hover:text-rose-500 transition-all cursor-pointer"
                title="Eliminar publicación"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Post Title / Text -->
        <p class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100 mt-1 leading-snug">
          {{ nota.titulo }}
        </p>

        <!-- Post Link -->
        <div v-if="nota.url_nota" class="mt-2">
          <a
            :href="nota.url_nota"
            target="_blank"
            rel="noopener noreferrer"
            class="text-xs font-mono text-[#1877F2] hover:underline inline-flex items-center gap-1 font-semibold"
          >
            <span>Ver publicación original en Facebook</span>
            <ExternalLink class="w-3 h-3" />
          </a>
        </div>

        <!-- Granular Emoji Reactions Meter for this Note -->
        <div
          v-if="nota.reacciones_desglose"
          class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2 text-xs font-mono"
        >
          <div class="flex items-center gap-2">
            <span class="text-[10px] text-slate-400 uppercase font-semibold">Reacciones:</span>
            <span class="px-2 py-0.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] font-bold">
              👍 {{ nota.reacciones_desglose.likes || 0 }}
            </span>
            <span class="px-2 py-0.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] font-bold text-rose-500">
              ❤️ {{ nota.reacciones_desglose.love || 0 }}
            </span>
            <span class="px-2 py-0.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] font-bold text-amber-500">
              😂 {{ nota.reacciones_desglose.haha || 0 }}
            </span>
            <span class="px-2 py-0.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-[11px] font-bold text-rose-600">
              😡 {{ nota.reacciones_desglose.angry || 0 }}
            </span>
          </div>

          <span class="text-[11px] text-slate-400">
            {{ (nota.interacciones || 0).toLocaleString() }} interacciones estimadas
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
