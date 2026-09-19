<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Badge from '../Badge.vue';
import {
  MapPin,
  Vote,
  Layers,
  ChevronDown,
  Sparkles,
  Radio,
  Calendar
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    default: null
  },
  candidatosLista: {
    type: Array,
    default: () => []
  },
  periodoActivo: {
    type: String,
    default: 'todos'
  },
  periodosDisponibles: {
    type: Array,
    default: () => []
  },
  totalPublicaciones: {
    type: Number,
    default: 0
  }
});

const emit = defineEmits(['cambiar-candidato', 'cambiar-periodo', 'reset-periodo']);

const periodoActivoNombre = computed(() => {
  const match = props.periodosDisponibles.find(p => p.clave === props.periodoActivo);
  return match ? match.nombre : props.periodoActivo;
});
</script>

<template>
  <div v-if="candidato" class="space-y-4">
    <!-- CABECERA ESTRATÉGICA (SALA DE SITUACIÓN / WAR ROOM) -->
    <div class="p-5 sm:p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden">
      <!-- Glow decorativo de fondo -->
      <div class="absolute -right-20 -top-20 w-80 h-80 bg-cyan-500/10 dark:bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
        <!-- Candidato Identity -->
        <div class="flex items-start sm:items-center gap-4">
          <div class="relative shrink-0">
            <img
              :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="candidato.nombre_completo"
              referrerpolicy="no-referrer"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 shadow-md"
              :style="{ borderColor: candidato.color_hex || '#06b6d4' }"
            />
            <span
              v-if="candidato.es_propio"
              class="absolute -bottom-1.5 -right-1.5 px-2 py-0.5 rounded-md bg-cyan-500 text-slate-950 font-extrabold text-[10px] uppercase shadow-xs tracking-wider"
            >
              Propio
            </span>
          </div>

          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {{ candidato.nombre_completo }}
              </h1>
              <Badge variant="estado" :value="candidato.estado_politico" size="sm" />
            </div>

            <p class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300 mt-0.5">
              {{ candidato.cargo_aspirado }} &bull; <span class="text-slate-500 dark:text-slate-400 font-normal">{{ candidato.partido_coalicion }}</span>
            </p>

            <div class="mt-2 flex items-center gap-3.5 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
              <span class="inline-flex items-center gap-1">
                <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                {{ candidato.territorio_nombre }}
              </span>
              <span v-if="candidato.padron_electoral" class="inline-flex items-center gap-1 font-mono font-medium">
                <Vote class="w-3.5 h-3.5 text-emerald-500" />
                Padrón: {{ Number(candidato.padron_electoral).toLocaleString('es-AR') }} votantes
              </span>
              <span class="inline-flex items-center gap-1">
                <Layers class="w-3.5 h-3.5 text-violet-500" />
                {{ candidato.ciclo_nombre }}
              </span>
            </div>
          </div>
        </div>

        <!-- Selector & Accesos Rápidos -->
        <div class="flex items-center gap-2.5 flex-wrap self-start md:self-center">
          <!-- Selector de Candidato -->
          <div v-if="candidatosLista.length > 1" class="relative">
            <select
              :value="candidato.id"
              @change="emit('cambiar-candidato', $event.target.value)"
              class="appearance-none bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl pl-3 pr-8 py-2.5 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all cursor-pointer shadow-2xs"
            >
              <option v-for="cand in candidatosLista" :key="cand.id" :value="cand.id">
                {{ cand.es_propio ? '⭐ ' : '' }}{{ cand.nombre_completo }} ({{ cand.cargo_aspirado }})
              </option>
            </select>
            <ChevronDown class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>

          <!-- Selector de Período Temporal (Meses / Campaña) -->
          <div v-if="periodosDisponibles && periodosDisponibles.length > 0" class="relative">
            <select
              :value="periodoActivo"
              @change="emit('cambiar-periodo', $event.target.value)"
              class="appearance-none bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 text-xs font-semibold rounded-xl pl-3 pr-8 py-2.5 border border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-all cursor-pointer shadow-2xs"
            >
              <option v-for="per in periodosDisponibles" :key="per.clave" :value="per.clave">
                {{ per.nombre }}
              </option>
            </select>
            <ChevronDown class="w-4 h-4 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>

          <Link
            href="/mi-candidato"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs transition-all shadow-xs hover:scale-102"
          >
            <Sparkles class="w-3.5 h-3.5" />
            <span>Mi Candidato</span>
          </Link>
          <Link
            href="/feed"
            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold text-xs transition-all border border-slate-200 dark:border-slate-700"
          >
            <Radio class="w-3.5 h-3.5 text-cyan-500" />
            <span>Muro Social</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Banner Informativo si el Período no tiene publicaciones -->
    <div
      v-if="periodoActivo !== 'todos' && (!totalPublicaciones || totalPublicaciones === 0)"
      class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-200 text-xs flex items-center justify-between gap-3 shadow-xs"
    >
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center shrink-0">
          <Calendar class="w-4 h-4" />
        </div>
        <div>
          <p class="font-bold">Período Seleccionado: {{ periodoActivoNombre }}</p>
          <p class="text-[11px] opacity-90">No hay publicaciones cargadas todavía para este mes. Todas las métricas de actividad figuran en 0.</p>
        </div>
      </div>
      <button
        type="button"
        @click="emit('reset-periodo')"
        class="px-3 py-1.5 rounded-xl font-bold bg-amber-500/20 hover:bg-amber-500/30 text-amber-900 dark:text-amber-100 transition-all cursor-pointer shrink-0"
      >
        Ver Campaña Completa
      </button>
    </div>
  </div>
</template>
