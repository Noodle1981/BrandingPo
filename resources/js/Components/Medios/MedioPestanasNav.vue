<script setup>
import { LayoutGrid, Globe, BarChart3 } from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';

defineProps({
  pestanaActiva: {
    type: String,
    default: 'directorio',
  },
  totalMedios: {
    type: Number,
    default: 0,
  },
  totalWeb: {
    type: Number,
    default: 0,
  },
  totalFacebook: {
    type: Number,
    default: 0,
  },
  totalNotas: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits(['cambiar-pestana']);
</script>

<template>
  <div class="border-b border-slate-200 dark:border-slate-800">
    <nav class="flex space-x-2 sm:space-x-6 overflow-x-auto pb-px" aria-label="Pestañas de Medios">
      <!-- 1. Directorio de Medios -->
      <button
        type="button"
        @click="emit('cambiar-pestana', 'directorio')"
        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer"
        :class="pestanaActiva === 'directorio'
          ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400'
          : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
      >
        <LayoutGrid class="w-4 h-4" />
        <span>Directorio de Medios</span>
        <span
          class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
          :class="pestanaActiva === 'directorio' ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
        >
          {{ totalMedios }}
        </span>
      </button>

      <!-- 2. Clipping Web Oficial -->
      <button
        type="button"
        @click="emit('cambiar-pestana', 'web')"
        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer"
        :class="pestanaActiva === 'web'
          ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400'
          : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
      >
        <Globe class="w-4 h-4" />
        <span>Clipping Web & Portales</span>
        <span
          class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
          :class="pestanaActiva === 'web' ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
        >
          {{ totalWeb }}
        </span>
      </button>

      <!-- 3. Medios en Redes (Facebook) -->
      <button
        type="button"
        @click="emit('cambiar-pestana', 'facebook')"
        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer"
        :class="pestanaActiva === 'facebook'
          ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400'
          : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
      >
        <SocialPlatformIcon platform="facebook" size="xs" />
        <span>Medios en Facebook</span>
        <span
          class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
          :class="pestanaActiva === 'facebook' ? 'bg-[#1877F2]/10 text-[#1877F2]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
        >
          {{ totalFacebook }}
        </span>
      </button>

      <!-- 4. Termómetro de Sesgo -->
      <button
        type="button"
        @click="emit('cambiar-pestana', 'sesgo')"
        class="inline-flex items-center gap-2 py-3 px-1 border-b-2 text-xs sm:text-sm font-bold transition-all whitespace-nowrap cursor-pointer"
        :class="pestanaActiva === 'sesgo'
          ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400'
          : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300'"
      >
        <BarChart3 class="w-4 h-4 text-violet-500" />
        <span>Matriz de Sesgo & Tono</span>
        <span
          class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
          :class="pestanaActiva === 'sesgo' ? 'bg-violet-500/10 text-violet-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
        >
          {{ totalNotas }}
        </span>
      </button>
    </nav>
  </div>
</template>
