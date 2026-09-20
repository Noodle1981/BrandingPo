<script setup>
import { computed } from 'vue';
import { 
  Globe, 
  ExternalLink, 
  Trash2, 
  Pencil, 
  Tag, 
  UserCheck, 
  FileText, 
  Plus 
} from '@lucide/vue';

const props = defineProps({
  notas: {
    type: Array,
    default: () => [],
  },
  canWrite: {
    type: Boolean,
    default: false,
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  medios: {
    type: Array,
    default: () => [],
  },
  filtros: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['filtrar', 'editar-nota', 'eliminar-nota', 'open-nota-modal']);

const notasWeb = computed(() => {
  return props.notas.filter(n => n.origen_tipo === 'web');
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

const getTipoMencionLabel = (tipo) => {
  switch (tipo) {
    case 'etiqueta_directa': return 'Etiqueta @';
    case 'titular': return 'En Titular';
    case 'cuerpo': return 'En Cuerpo';
    default: return 'Mención';
  }
};
</script>

<template>
  <div class="space-y-4">
    <!-- Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <Globe class="w-4 h-4 text-cyan-500" />
        <h2 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
          Noticias Monitoreadas en Portales Web ({{ notasWeb.length }})
        </h2>
      </div>

      <button
        v-if="canWrite"
        type="button"
        @click="emit('open-nota-modal', { origen_tipo: 'web' })"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30 text-xs font-bold transition-all cursor-pointer self-start sm:self-auto"
      >
        <Plus class="w-3.5 h-3.5" />
        <span>Agregar Nota Web Manual</span>
      </button>
    </div>

    <!-- Empty State -->
    <div
      v-if="!notasWeb.length"
      class="text-center py-14 px-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl"
    >
      <FileText class="w-12 h-12 text-slate-400 mx-auto mb-3 opacity-40" />
      <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
        No hay artículos de prensa registrados
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
        Utiliza el botón <strong>"Sincronizar Menciones"</strong> en la cabecera para rastrear los portales web o agrega notas manualmente.
      </p>
    </div>

    <!-- List of Articles -->
    <div v-else class="space-y-3.5">
      <div
        v-for="nota in notasWeb"
        :key="nota.id"
        class="p-5 rounded-3xl bg-white dark:bg-slate-900 border shadow-xs transition-all hover:border-slate-300 dark:hover:border-slate-700"
        :class="nota.tono_mencion === 'critico'
          ? 'border-rose-500/30 bg-rose-50/10 dark:bg-rose-950/10'
          : (nota.tono_mencion === 'favorable'
            ? 'border-emerald-500/30 bg-emerald-50/10 dark:bg-emerald-950/10'
            : 'border-slate-200 dark:border-slate-800')"
      >
        <!-- Top Row: Outlet, Date, Badges -->
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
          <div class="flex items-center gap-2">
            <!-- Media Avatar / Icon -->
            <div class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 overflow-hidden flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
              <img
                v-if="nota.medio?.avatar_url"
                :src="nota.medio.avatar_url"
                :alt="nota.medio.nombre"
                class="w-full h-full object-cover"
                referrerpolicy="no-referrer"
              />
              <Globe v-else class="w-3.5 h-3.5 text-slate-400" />
            </div>

            <span class="font-extrabold text-xs text-slate-900 dark:text-slate-100">
              {{ nota.medio?.nombre }}
            </span>
            <span class="text-xs text-slate-400 font-mono">• {{ nota.fecha }}</span>

            <span
              v-if="nota.es_tapa_o_principal"
              class="px-2 py-0.5 rounded-md bg-amber-500 text-slate-950 font-mono text-[9px] font-black uppercase tracking-wider"
            >
              Portada / Tapa
            </span>
          </div>

          <!-- Badges & Actions -->
          <div class="flex items-center gap-2">
            <!-- Candidate Mentioned Badge -->
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

            <!-- Mention Type Badge -->
            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
              {{ getTipoMencionLabel(nota.tipo_mencion) }}
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
                title="Eliminar del clipping"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Headline & Link -->
        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-slate-100 mt-1 leading-snug">
          <a
            v-if="nota.url_nota"
            :href="nota.url_nota"
            target="_blank"
            rel="noopener noreferrer"
            class="hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors inline-flex items-baseline gap-1"
          >
            <span>{{ nota.titulo }}</span>
            <ExternalLink class="w-3.5 h-3.5 text-slate-400 shrink-0 inline" />
          </a>
          <span v-else>{{ nota.titulo }}</span>
        </h3>

        <!-- Summary -->
        <p v-if="nota.resumen" class="text-xs text-slate-600 dark:text-slate-400 mt-2 leading-relaxed">
          {{ nota.resumen }}
        </p>

        <!-- Official Candidate Replica Response if present -->
        <div
          v-if="nota.respuesta_replica"
          class="mt-3 p-3 rounded-2xl bg-cyan-500/5 dark:bg-cyan-500/10 border border-cyan-500/20 text-xs"
        >
          <span class="font-extrabold text-cyan-600 dark:text-cyan-400 block font-mono text-[11px] mb-0.5">
            Réplica / Posición Oficial de Campaña:
          </span>
          <p class="text-slate-700 dark:text-slate-300">
            {{ nota.respuesta_replica }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
