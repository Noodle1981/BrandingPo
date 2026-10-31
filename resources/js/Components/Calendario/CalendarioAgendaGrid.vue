<script setup>
import {
  Calendar,
  Clock,
  MapPin,
  Trash2,
} from '@lucide/vue';

const props = defineProps({
  eventos: {
    type: Array,
    default: () => [],
  },
  canWrite: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['openModal', 'deleteEvento']);
</script>

<template>
  <div class="space-y-4">
    <!-- Estado Vacío si no hay eventos -->
    <div v-if="!eventos.length" class="text-center py-16 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 space-y-3">
      <Calendar class="w-12 h-12 text-slate-400 mx-auto opacity-40" />
      <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">
        No hay actividades ni contenidos agendados con este filtro
      </h3>
      <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
        Prueba seleccionando otro eje temático, o programa una nueva publicación para reforzar la presencia de campaña.
      </p>
      <div v-if="canWrite" class="pt-2">
        <button
          type="button"
          @click="emit('openModal')"
          class="px-4 py-2 rounded-xl bg-cyan-500 text-slate-950 font-bold text-xs shadow-xs cursor-pointer hover:bg-cyan-400 transition-colors"
        >
          + Programar Actividad Ahora
        </button>
      </div>
    </div>

    <!-- Grilla de Tarjetas de la Agenda -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="ev in eventos"
        :key="ev.id"
        class="p-5 rounded-3xl bg-white dark:bg-slate-900 border shadow-xs hover:border-cyan-500/40 transition-all flex flex-col justify-between space-y-4"
        :class="ev.tipo_evento === 'publicacion_eje'
          ? 'border-cyan-500/30 dark:border-cyan-500/20 shadow-cyan-500/5'
          : 'border-slate-200 dark:border-slate-800'"
      >
        <!-- Parte Superior: Badges & Estado -->
        <div class="space-y-3">
          <div class="flex items-center justify-between gap-2 flex-wrap">
            <!-- Badge del Eje Temático -->
            <span
              v-if="ev.eje_tematico"
              class="px-2.5 py-1 rounded-lg text-xs font-mono font-extrabold flex items-center gap-1.5 border"
              :style="{
                backgroundColor: `${ev.eje_tematico.color_badge}15`,
                borderColor: `${ev.eje_tematico.color_badge}40`,
                color: ev.eje_tematico.color_badge || '#06b6d4'
              }"
            >
              <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: ev.eje_tematico.color_badge || '#06b6d4' }"></span>
              <span>{{ ev.eje_tematico.nombre }}</span>
            </span>
            <span
              v-else
              class="px-2 py-0.5 rounded-md text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800"
            >
              Sin Eje Asignado
            </span>

            <!-- Tipo de Evento & Estado -->
            <div class="flex items-center gap-1.5">
              <span
                class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase"
                :class="ev.tipo_evento === 'publicacion_eje'
                  ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30'
                  : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
              >
                {{ ev.tipo_evento.replace('_', ' ') }}
              </span>

              <button
                v-if="canWrite"
                type="button"
                @click="emit('deleteEvento', ev)"
                class="text-slate-400 hover:text-rose-500 p-1 rounded-lg hover:bg-rose-500/10 transition-colors cursor-pointer"
                title="Eliminar de la agenda"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>

          <!-- Título del Evento -->
          <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 leading-snug">
            {{ ev.titulo }}
          </h3>

          <!-- Datos de Fecha, Hora y Canal/Lugar -->
          <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 font-mono">
            <div class="flex items-center gap-2">
              <Clock class="w-3.5 h-3.5 text-cyan-500 shrink-0" />
              <span>{{ ev.fecha_inicio }} <span v-if="ev.fecha_fin">hasta {{ ev.fecha_fin }}</span></span>
            </div>
            <div v-if="ev.lugar" class="flex items-center gap-2">
              <MapPin class="w-3.5 h-3.5 text-emerald-500 shrink-0" />
              <span>{{ ev.lugar }}</span>
            </div>
          </div>

          <!-- Notas & Estrategia -->
          <p v-if="ev.notas" class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed pt-2.5 border-t border-slate-100 dark:border-slate-800">
            {{ ev.notas }}
          </p>
        </div>

        <!-- Footer: Candidato Asignado & Estado de Realización -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-mono">
          <div class="flex items-center gap-1.5">
            <span class="text-slate-400">Responsable:</span>
            <strong class="text-slate-800 dark:text-slate-200">{{ ev.candidato?.nombre || 'General' }}</strong>
          </div>

          <span
            class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
            :class="ev.estado === 'realizado'
              ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
              : (ev.estado === 'cancelado' ? 'bg-rose-500/10 text-rose-500' : 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400')"
          >
            {{ ev.estado }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
