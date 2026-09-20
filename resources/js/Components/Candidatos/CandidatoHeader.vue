<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Badge from '../Badge.vue';
import {
  MapPin,
  Users,
  Edit3,
  ShieldCheck,
  ArrowLeft
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  tema: {
    type: String,
    default: 'propio', // 'propio' | 'rival'
  },
});

const emit = defineEmits(['editar']);

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? true);
const esPropio = computed(() => props.tema === 'propio' || props.candidato?.es_propio);

const colorAcentoHex = computed(() => {
  return esPropio.value ? (props.candidato.color_hex || '#06b6d4') : (props.candidato.color_hex || '#8b5cf6');
});
</script>

<template>
  <div class="space-y-4">
    <!-- Botón Volver si es rival -->
    <div v-if="!esPropio" class="mb-1">
      <Link
        href="/candidatos"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-purple-500 transition-colors"
      >
        <ArrowLeft class="w-4 h-4" />
        <span>Volver a Lista de Oposición & Rivales</span>
      </Link>
    </div>

    <!-- Header Principal -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="flex items-start sm:items-center gap-4">
          <!-- Avatar del Candidato -->
          <div class="relative shrink-0">
            <img
              :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=${esPropio ? '082f49' : '1e1b4b'}&color=${esPropio ? '38bdf8' : 'a855f7'}`"
              :alt="candidato.nombre_completo"
              referrerpolicy="no-referrer"
              class="w-20 h-20 rounded-2xl object-cover border-2 shadow-md"
              :style="{ borderColor: colorAcentoHex }"
              @error="$event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=${esPropio ? '082f49' : '1e1b4b'}&color=${esPropio ? '38bdf8' : 'a855f7'}&size=256&bold=true`"
            />
            <div
              class="absolute -top-2 -right-2 px-2 py-0.5 rounded-full text-white font-extrabold text-[10px] uppercase font-mono tracking-wider shadow-sm"
              :class="esPropio ? 'bg-cyan-600' : 'bg-purple-600'"
            >
              {{ esPropio ? 'CANDIDATO PROPIO' : 'RIVAL OPOSITOR' }}
            </div>
          </div>

          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {{ candidato.nombre_completo }}
              </h1>
              <Badge variant="estado" :value="candidato.estado_politico" size="sm" />
            </div>

            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-1">
              {{ candidato.cargo_aspirado }} &bull; <span class="text-slate-500 font-normal">{{ candidato.partido_coalicion }}</span>
            </p>

            <div class="mt-2.5 flex items-center gap-4 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
              <span class="inline-flex items-center gap-1">
                <MapPin class="w-3.5 h-3.5" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
                {{ candidato.territorio?.nombre || 'Territorio General' }}
              </span>
              <span
                class="inline-flex items-center gap-1 font-mono font-bold"
                :class="esPropio ? 'text-cyan-600 dark:text-cyan-400' : 'text-purple-600 dark:text-purple-400'"
              >
                <Users class="w-3.5 h-3.5" />
                {{ esPropio ? 'Comunidad de Campaña:' : 'Comunidad Rival:' }}
                {{ Number(candidato.total_seguidores_bruto ?? candidato.total_seguidores ?? 0).toLocaleString('es-AR') }} seguidores
              </span>
            </div>
          </div>
        </div>

        <div v-if="canWrite" class="flex items-center gap-2.5 self-start md:self-center">
          <button
            type="button"
            @click="emit('editar')"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer shadow-xs hover:scale-102"
          >
            <Edit3 class="w-4 h-4" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
            <span>Editar Datos Básicos</span>
          </button>
        </div>
      </div>

      <!-- Semáforo de Leyenda de Canales -->
      <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3 text-xs">
        <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 font-mono uppercase text-[11px]">
          <ShieldCheck class="w-4 h-4" :class="esPropio ? 'text-cyan-500' : 'text-purple-500'" />
          Semáforo de Canales Oficiales:
        </span>
        <div class="flex items-center gap-4 flex-wrap font-mono text-[11px]">
          <span class="inline-flex items-center gap-1.5 text-blue-500 dark:text-blue-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            🔵 Certificada / Verificada
          </span>
          <span class="inline-flex items-center gap-1.5 text-emerald-500 dark:text-emerald-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            🟢 Activa / En Uso
          </span>
          <span class="inline-flex items-center gap-1.5 text-rose-500 dark:text-rose-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            🔴 Inactiva / Sin Movimiento
          </span>
          <span class="inline-flex items-center gap-1.5 text-slate-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
            ⚪ Sin Configurar
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
