<script setup>
import { 
  Globe, 
  Rss, 
  RefreshCw, 
  Pencil, 
  Trash2, 
  ExternalLink,
  Radio,
  Tv,
  FileText,
  Building2,
  Clock
} from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';

defineProps({
  medios: {
    type: Array,
    default: () => [],
  },
  canWrite: {
    type: Boolean,
    default: false,
  },
  sincronizandoId: {
    type: Number,
    default: null,
  },
});

const emit = defineEmits(['sincronizar-medio', 'editar-medio', 'eliminar-medio', 'open-create-modal']);

const getTipoIcon = (tipo) => {
  switch (tipo) {
    case 'tv': return Tv;
    case 'radio': return Radio;
    case 'impreso': return FileText;
    default: return Globe;
  }
};

const getSesgoBadgeClass = (sesgo) => {
  switch (sesgo) {
    case 'oficialista':
      return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20';
    case 'opositor':
      return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20';
    default:
      return 'bg-slate-500/10 text-slate-600 dark:text-slate-300 border-slate-500/20';
  }
};
</script>

<template>
  <div>
    <!-- Empty State -->
    <div
      v-if="!medios.length"
      class="text-center py-16 px-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl"
    >
      <div class="w-16 h-16 mx-auto rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mb-4">
        <Building2 class="w-8 h-8" />
      </div>
      <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100">
        No hay medios de prensa registrados
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
        Comienza registrando los portales de noticias y medios locales que cubren la actualidad política de tu territorio.
      </p>
      <button
        v-if="canWrite"
        type="button"
        @click="emit('open-create-modal')"
        class="mt-5 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs transition-all shadow-md shadow-cyan-500/20 cursor-pointer"
      >
        Registrar Primer Medio
      </button>
    </div>

    <!-- Media Cards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="m in medios"
        :key="m.id"
        class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all group"
      >
        <div>
          <!-- Top Row: Avatar & Badges -->
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <!-- Avatar (Facebook prioritario) -->
              <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shrink-0 flex items-center justify-center shadow-xs">
                <img
                  v-if="m.avatar_url"
                  :src="m.avatar_url"
                  :alt="m.nombre"
                  class="w-full h-full object-cover"
                  referrerpolicy="no-referrer"
                />
                <component
                  v-else
                  :is="getTipoIcon(m.tipo_medio)"
                  class="w-6 h-6 text-slate-400"
                />
              </div>

              <div class="min-w-0">
                <h3 class="font-extrabold text-slate-900 dark:text-slate-100 text-sm truncate">
                  {{ m.nombre }}
                </h3>
                <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-slate-400 font-mono">
                  <span class="capitalize">{{ m.tipo_medio }}</span>
                  <span>•</span>
                  <span class="capitalize">{{ m.alcance_tipo }}</span>
                </div>
              </div>
            </div>

            <!-- Bias Badge -->
            <span
              class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase border shrink-0"
              :class="getSesgoBadgeClass(m.sesgo_editorial_estimado)"
            >
              {{ m.sesgo_editorial_estimado }}
            </span>
          </div>

          <!-- Links & Feeds Status -->
          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-2">
            <!-- Web Link -->
            <div v-if="m.url_sitio" class="flex items-center justify-between text-xs">
              <span class="text-slate-400 flex items-center gap-1.5">
                <Globe class="w-3.5 h-3.5 text-cyan-500" />
                <span>Web Oficial:</span>
              </span>
              <a
                :href="m.url_sitio"
                target="_blank"
                rel="noopener noreferrer"
                class="font-mono text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1 truncate max-w-[180px]"
              >
                <span class="truncate">{{ m.url_sitio.replace(/^https?:\/\/(www\.)?/, '') }}</span>
                <ExternalLink class="w-3 h-3 shrink-0" />
              </a>
            </div>

            <!-- Facebook Link -->
            <div v-if="m.url_facebook" class="flex items-center justify-between text-xs">
              <span class="text-slate-400 flex items-center gap-1.5">
                <SocialPlatformIcon platform="facebook" size="xs" />
                <span>Fanpage:</span>
              </span>
              <a
                :href="m.url_facebook"
                target="_blank"
                rel="noopener noreferrer"
                class="font-mono text-[#1877F2] hover:underline flex items-center gap-1 truncate max-w-[180px]"
              >
                <span>Facebook</span>
                <ExternalLink class="w-3 h-3 shrink-0" />
              </a>
            </div>

            <!-- RSS Status -->
            <div class="flex items-center justify-between text-xs font-mono">
              <span class="text-slate-400 flex items-center gap-1.5">
                <Rss class="w-3.5 h-3.5 text-amber-500" />
                <span>Feed RSS:</span>
              </span>
              <span
                class="px-2 py-0.2 rounded text-[10px] font-bold"
                :class="m.feed_rss_url ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400'"
              >
                {{ m.feed_rss_url ? 'Activo' : 'Sin RSS' }}
              </span>
            </div>
          </div>

          <!-- Notes Counters (Web vs Facebook) -->
          <div class="mt-3 grid grid-cols-3 gap-2 p-2 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800 text-center font-mono">
            <div>
              <span class="text-[10px] text-slate-400 block font-semibold">Total</span>
              <span class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                {{ m.notas_prensa_count }}
              </span>
            </div>
            <div>
              <span class="text-[10px] text-cyan-600 dark:text-cyan-400 block font-semibold">Web</span>
              <span class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                {{ m.notas_web_count }}
              </span>
            </div>
            <div>
              <span class="text-[10px] text-[#1877F2] block font-semibold">Facebook</span>
              <span class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                {{ m.notas_fb_count }}
              </span>
            </div>
          </div>
        </div>

        <!-- Bottom Row: Last Sync & Actions -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
          <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-mono">
            <Clock class="w-3 h-3 text-slate-400" />
            <span v-if="m.ultima_sincronizacion_diff">
              {{ m.ultima_sincronizacion_diff }}
            </span>
            <span v-else class="text-slate-400 italic">
              Sin sincronizar
            </span>
          </div>

          <!-- Actions -->
          <div v-if="canWrite" class="flex items-center gap-1">
            <button
              type="button"
              @click="emit('sincronizar-medio', m)"
              :disabled="sincronizandoId === m.id"
              class="p-1.5 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-cyan-500/10 transition-all cursor-pointer disabled:opacity-50"
              title="Escanear notas y publicaciones de este medio"
            >
              <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': sincronizandoId === m.id }" />
            </button>

            <button
              type="button"
              @click="emit('editar-medio', m)"
              class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
              title="Editar configuración del medio"
            >
              <Pencil class="w-4 h-4" />
            </button>

            <button
              type="button"
              @click="emit('eliminar-medio', m)"
              class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 transition-all cursor-pointer"
              title="Eliminar medio de prensa"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
