<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  Sparkles,
  Users,
  Eye,
  CheckCircle2,
  AlertCircle,
  Link2,
  ExternalLink,
  ShieldCheck,
  Flag,
  Calendar,
  Save,
  Plus,
  Trash2,
  Edit3,
  MapPin,
  Vote,
  Layers,
  ArrowUpRight,
  TrendingUp,
  Image as ImageIcon
} from '@lucide/vue';

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  redes: {
    type: Array,
    default: () => [],
  },
  ciclos: {
    type: Array,
    default: () => [],
  },
  territorios: {
    type: Array,
    default: () => [],
  }
});

// Pestaña de red social activa seleccionada
const selectedPlatformKey = ref(props.redes[0]?.key || 'instagram');

const currentRed = computed(() => {
  return props.redes.find(r => r.key === selectedPlatformKey.value) || props.redes[0];
});

// Formulario reactivo para la red seleccionada
const formRed = useForm({
  candidato_id: props.candidato.id,
  plataforma: currentRed.value?.key || 'instagram',
  handle_usuario: currentRed.value?.handle_usuario || '',
  url_perfil: currentRed.value?.url_perfil || '',
  foto_perfil_url: currentRed.value?.foto_perfil_url || '',
  esta_activo: currentRed.value?.esta_activo ?? true,
  esta_verificado: currentRed.value?.esta_verificado ?? false,
  seguidores_actuales: currentRed.value?.seguidores_actuales || 0,
  seguidos_actuales: currentRed.value?.seguidos_actuales || 0,
  publicaciones_totales: currentRed.value?.publicaciones_totales || 0,
  fecha_punto_cero: currentRed.value?.fecha_punto_cero || new Date().toISOString().slice(0, 10),
  seguidores_punto_cero: currentRed.value?.seguidores_punto_cero || 0,
  seguidos_punto_cero: currentRed.value?.seguidos_punto_cero || 0,
  publicaciones_punto_cero: currentRed.value?.publicaciones_punto_cero || 0,
  notas_punto_cero: currentRed.value?.notas_punto_cero || '',
});

const selectPlatform = (platformKey) => {
  selectedPlatformKey.value = platformKey;
  const red = props.redes.find(r => r.key === platformKey);
  if (red) {
    formRed.candidato_id = props.candidato.id;
    formRed.plataforma = red.key;
    formRed.handle_usuario = red.handle_usuario || '';
    formRed.url_perfil = red.url_perfil || '';
    formRed.foto_perfil_url = red.foto_perfil_url || '';
    formRed.esta_activo = red.esta_activo ?? true;
    formRed.esta_verificado = red.esta_verificado ?? false;
    formRed.seguidores_actuales = red.seguidores_actuales || 0;
    formRed.seguidos_actuales = red.seguidos_actuales || 0;
    formRed.publicaciones_totales = red.publicaciones_totales || 0;
    formRed.fecha_punto_cero = red.fecha_punto_cero || new Date().toISOString().slice(0, 10);
    formRed.seguidores_punto_cero = red.seguidores_punto_cero || 0;
    formRed.seguidos_punto_cero = red.seguidos_punto_cero || 0;
    formRed.publicaciones_punto_cero = red.publicaciones_punto_cero || 0;
    formRed.notas_punto_cero = red.notas_punto_cero || '';
  }
};

const savePerfilSocial = () => {
  formRed.post('/perfiles-sociales', {
    preserveScroll: true,
  });
};

// Modal de edición de datos generales del candidato
const isEditingCandidato = ref(false);
const formCandidato = useForm({
  nombre_completo: props.candidato.nombre_completo || '',
  partido_coalicion: props.candidato.partido_coalicion || '',
  cargo_aspirado: props.candidato.cargo_aspirado || '',
  estado_politico: props.candidato.estado_politico || 'candidato',
  ciclo_campana_id: props.candidato.ciclo_campana_id || props.ciclos[0]?.id,
  territorio_id: props.candidato.territorio_id || props.territorios[0]?.id,
  color_hex: props.candidato.color_hex || '#06b6d4',
  avatar_url: props.candidato.avatar_url || '',
  bio_resumen: props.candidato.bio_resumen || '',
});

const saveCandidato = () => {
  formCandidato.put(`/candidatos/${props.candidato.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      isEditingCandidato.value = false;
    }
  });
};

const tabBadgeStyle = (colorEstado) => {
  switch (colorEstado) {
    case 'azul':
      // 🔵 Certificada / Verificada
      return {
        tab: 'border-blue-500 bg-blue-500/10 text-blue-400 font-bold',
        pill: 'bg-blue-500 text-white',
        label: 'Verificada (Azul)'
      };
    case 'naranja':
      // 🟠 Activa / Vinculada
      return {
        tab: 'border-amber-500 bg-amber-500/10 text-amber-400 font-semibold',
        pill: 'bg-amber-500 text-slate-950 font-bold',
        label: 'Activa (Naranja)'
      };
    case 'rojo':
    default:
      // 🔴 No vinculada / Inactiva
      return {
        tab: 'border-rose-500/50 bg-rose-500/10 text-rose-400 font-medium',
        pill: 'bg-rose-500 text-white',
        label: 'Inactiva (Roja)'
      };
  }
};
</script>

<template>
  <Head :title="`Perfil Propio: ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <!-- 1. Header del Candidato Propio / Cliente -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="flex items-start sm:items-center gap-4">
          <div class="relative shrink-0">
            <img
              :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="candidato.nombre_completo"
              class="w-20 h-20 rounded-2xl object-cover border-2 shadow-md"
              :style="{ borderColor: candidato.color_hex || '#06b6d4' }"
            />
            <div class="absolute -top-2 -right-2 px-2 py-0.5 rounded-full bg-cyan-500 text-slate-950 font-extrabold text-[10px] uppercase font-mono tracking-wider shadow-sm">
              CLIENTE OFICIAL
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
                <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                {{ candidato.territorio?.nombre || 'Territorio General' }}
              </span>
              <span v-if="candidato.territorio?.padron_electoral" class="inline-flex items-center gap-1 font-mono">
                <Vote class="w-3.5 h-3.5 text-emerald-500" />
                Padrón: {{ Number(candidato.territorio.padron_electoral).toLocaleString('es-AR') }} electores
              </span>
              <span class="inline-flex items-center gap-1 font-mono text-cyan-500 font-bold">
                <Users class="w-3.5 h-3.5" />
                Comunidad: {{ Number(candidato.total_seguidores).toLocaleString('es-AR') }} seguidores
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-center">
          <button
            type="button"
            @click="isEditingCandidato = true"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer"
          >
            <Edit3 class="w-4 h-4 text-cyan-500" />
            <span>Editar Datos Generales</span>
          </button>
          <Link
            href="/fast-flow"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs shadow-sm transition-all hover:scale-102"
          >
            <Sparkles class="w-4 h-4" />
            <span>Cargar Publicación</span>
          </Link>
        </div>
      </div>

      <!-- Semáforo de Redes Sociales Leyenda Informativa -->
      <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3 text-xs">
        <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 font-mono uppercase text-[11px]">
          <ShieldCheck class="w-4 h-4 text-cyan-500" />
          Semáforo de Canales Digitales:
        </span>
        <div class="flex items-center gap-4 flex-wrap font-mono text-[11px]">
          <span class="inline-flex items-center gap-1.5 text-blue-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            🔵 Certificada / Verificada
          </span>
          <span class="inline-flex items-center gap-1.5 text-amber-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            🟠 Vinculada / Activa
          </span>
          <span class="inline-flex items-center gap-1.5 text-rose-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            🔴 No Vinculada / Inactiva
          </span>
        </div>
      </div>
    </div>

    <!-- 2. Pestañas de Redes Sociales (Tabs de Semáforo de Color) -->
    <div class="space-y-4">
      <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <button
          v-for="red in redes"
          :key="red.key"
          type="button"
          @click="selectPlatform(red.key)"
          class="px-4 py-3 rounded-2xl border-2 transition-all flex items-center gap-2.5 shrink-0 cursor-pointer text-xs font-mono"
          :class="[
            selectedPlatformKey === red.key
              ? 'ring-2 ring-cyan-500 shadow-md scale-102 ' + tabBadgeStyle(red.color_estado).tab
              : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700'
          ]"
        >
          <!-- Color Status Dot -->
          <span
            class="w-3 h-3 rounded-full shrink-0 shadow-xs"
            :class="{
              'bg-blue-500 ring-2 ring-blue-300': red.color_estado === 'azul',
              'bg-amber-500 ring-2 ring-amber-300': red.color_estado === 'naranja',
              'bg-rose-500 ring-2 ring-rose-300': red.color_estado === 'rojo',
            }"
          ></span>

          <span class="font-bold text-sm">{{ red.nombre }}</span>

          <span
            class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase"
            :class="tabBadgeStyle(red.color_estado).pill"
          >
            {{ red.color_estado === 'azul' ? '✓ Verificada' : (red.color_estado === 'naranja' ? 'Activa' : 'Inactiva') }}
          </span>
        </button>
      </div>

      <!-- 3. Formulario & Configuración de la Red Seleccionada (Punto Cero / Baseline) -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <span class="w-3 h-3 rounded-full" :class="{
                'bg-blue-500': currentRed.color_estado === 'azul',
                'bg-amber-500': currentRed.color_estado === 'naranja',
                'bg-rose-500': currentRed.color_estado === 'rojo',
              }"></span>
              <span>Canal: {{ currentRed.nombre }}</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Configuración de acceso, estado de verificación y línea de base inicial (Punto Cero).
            </p>
          </div>

          <span
            class="px-3 py-1 rounded-xl text-xs font-mono font-bold uppercase border"
            :class="tabBadgeStyle(currentRed.color_estado).tab"
          >
            Estado: {{ tabBadgeStyle(currentRed.color_estado).label }}
          </span>
        </div>

        <form @submit.prevent="savePerfilSocial" class="space-y-6">
          <!-- A. Estado & Enlaces de la Cuenta -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Switches de Estado -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3.5">
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono">
                Estado de la Cuenta en {{ currentRed.nombre }}
              </h3>

              <!-- Switch Activa -->
              <label class="flex items-center justify-between cursor-pointer">
                <div>
                  <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">¿El candidato tiene esta red activa?</span>
                  <span class="text-[11px] text-slate-400">Si está activa saldrá en 🟠 naranja</span>
                </div>
                <input
                  v-model="formRed.esta_activo"
                  type="checkbox"
                  class="w-5 h-5 rounded-md text-cyan-500 focus:ring-cyan-500"
                />
              </label>

              <!-- Switch Verificada -->
              <label class="flex items-center justify-between cursor-pointer pt-2 border-t border-slate-200 dark:border-slate-800">
                <div>
                  <span class="text-xs font-bold text-blue-500 dark:text-blue-400 block">¿Cuenta Verificada / Certificada oficialmente?</span>
                  <span class="text-[11px] text-slate-400">Si está verificada saldrá en 🔵 azul</span>
                </div>
                <input
                  v-model="formRed.esta_verificado"
                  type="checkbox"
                  class="w-5 h-5 rounded-md text-blue-500 focus:ring-blue-500"
                />
              </label>
            </div>

            <!-- Handle & Enlace -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Usuario / Handle en {{ currentRed.nombre }} *
                </label>
                <input
                  v-model="formRed.handle_usuario"
                  type="text"
                  required
                  placeholder="ej. @martinrodriguez_ok"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Enlace directo al Perfil (URL)
                </label>
                <div class="relative">
                  <input
                    v-model="formRed.url_perfil"
                    type="url"
                    placeholder="https://www.instagram.com/usuario/"
                    class="w-full pl-8 pr-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                  />
                  <Link2 class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                </div>
              </div>
            </div>
          </div>

          <!-- B. 🏁 PUNTO CERO / PUNTO ALFA (Línea de Base Inicial del Nacimiento) -->
          <div class="p-5 rounded-3xl bg-gradient-to-br from-slate-900 to-cyan-950 text-white border border-cyan-500/30 shadow-md space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <div class="flex items-center gap-2">
                <Flag class="w-5 h-5 text-cyan-400" />
                <h3 class="font-bold text-sm sm:text-base text-white">
                  Punto Cero / Punto Alfa (Línea de Base Inicial de Campaña)
                </h3>
              </div>
              <span class="text-[10px] font-mono px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 uppercase">
                Punto de Partida
              </span>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
              Registra los números exactos con los que <strong>nace la auditoría de esta red social</strong> (ej: 64 publicaciones, 1.360 seguidores, 578 seguidos) para medir el crecimiento neto de campaña a partir de esta fecha.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 font-mono">
              <!-- Publicaciones Iniciales -->
              <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                <label class="block text-[10px] uppercase text-slate-300 mb-1">Publicaciones Iniciales</label>
                <input
                  v-model.number="formRed.publicaciones_punto_cero"
                  type="number"
                  min="0"
                  placeholder="ej. 64"
                  class="w-full px-2.5 py-1.5 rounded-xl bg-slate-900/80 border border-white/20 text-sm font-bold text-white text-center focus:ring-2 focus:ring-cyan-400"
                />
              </div>

              <!-- Seguidores Iniciales -->
              <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                <label class="block text-[10px] uppercase text-slate-300 mb-1">Seguidores (Punto Alfa)</label>
                <input
                  v-model.number="formRed.seguidores_punto_cero"
                  type="number"
                  min="0"
                  placeholder="ej. 1360"
                  class="w-full px-2.5 py-1.5 rounded-xl bg-slate-900/80 border border-white/20 text-sm font-bold text-cyan-300 text-center focus:ring-2 focus:ring-cyan-400"
                />
              </div>

              <!-- Seguidos Iniciales -->
              <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                <label class="block text-[10px] uppercase text-slate-300 mb-1">Seguidos Iniciales</label>
                <input
                  v-model.number="formRed.seguidos_punto_cero"
                  type="number"
                  min="0"
                  placeholder="ej. 578"
                  class="w-full px-2.5 py-1.5 rounded-xl bg-slate-900/80 border border-white/20 text-sm font-bold text-white text-center focus:ring-2 focus:ring-cyan-400"
                />
              </div>

              <!-- Fecha Punto Cero -->
              <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                <label class="block text-[10px] uppercase text-slate-300 mb-1">Fecha de Inicio</label>
                <input
                  v-model="formRed.fecha_punto_cero"
                  type="date"
                  class="w-full px-2.5 py-1.5 rounded-xl bg-slate-900/80 border border-white/20 text-xs font-bold text-white text-center focus:ring-2 focus:ring-cyan-400"
                />
              </div>
            </div>
          </div>

          <!-- C. 📈 ESTADO ACTUAL & SEGUIMIENTO EN TIEMPO REAL -->
          <div class="p-5 rounded-3xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-1.5">
                <TrendingUp class="w-4 h-4 text-emerald-500" />
                <span>Estado Actual & Crecimiento Acumulado</span>
              </h3>
              <span class="text-xs text-slate-400 font-mono">Actualiza con la última lectura</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 font-mono">
              <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Seguidores Actuales</label>
                <input
                  v-model.number="formRed.seguidores_actuales"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-sm font-bold text-slate-900 dark:text-slate-100"
                />
                <span class="text-[10px] text-emerald-500 font-bold block mt-1">
                  Ganados desde el inicio: +{{ (formRed.seguidores_actuales || 0) - (formRed.seguidores_punto_cero || 0) }}
                </span>
              </div>

              <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Seguidos Actuales</label>
                <input
                  v-model.number="formRed.seguidos_actuales"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-sm font-bold text-slate-900 dark:text-slate-100"
                />
              </div>

              <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Publicaciones Totales</label>
                <input
                  v-model.number="formRed.publicaciones_totales"
                  type="number"
                  min="0"
                  class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-sm font-bold text-slate-900 dark:text-slate-100"
                />
                <span class="text-[10px] text-cyan-500 font-bold block mt-1">
                  Nuevas en campaña: +{{ (formRed.publicaciones_totales || 0) - (formRed.publicaciones_punto_cero || 0) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex items-center justify-end gap-3 pt-2">
            <button
              type="submit"
              :disabled="formRed.processing"
              class="px-6 py-3 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs sm:text-sm shadow-md shadow-cyan-500/25 flex items-center gap-2 cursor-pointer transition-all hover:scale-102"
            >
              <Save class="w-4 h-4" />
              <span>{{ formRed.processing ? 'Guardando...' : `Guardar Configuración de ${currentRed.nombre}` }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal para Editar Datos Generales del Candidato -->
    <div
      v-if="isEditingCandidato"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Edit3 class="w-5 h-5 text-cyan-500" />
            <span>Editar Datos de Mi Candidato</span>
          </h3>
          <button
            type="button"
            @click="isEditingCandidato = false"
            class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          >
            &times;
          </button>
        </div>

        <form @submit.prevent="saveCandidato" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre Completo *</label>
            <input
              v-model="formCandidato.nombre_completo"
              type="text"
              required
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Partido / Coalición *</label>
              <input
                v-model="formCandidato.partido_coalicion"
                type="text"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cargo al que aspira</label>
              <input
                v-model="formCandidato.cargo_aspirado"
                type="text"
                placeholder="ej. Intendente Municipal"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Foto / Avatar URL</label>
            <input
              v-model="formCandidato.avatar_url"
              type="url"
              placeholder="https://..."
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Biografía / Eje de Campaña</label>
            <textarea
              v-model="formCandidato.bio_resumen"
              rows="3"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="isEditingCandidato = false"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="formCandidato.processing"
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-sm"
            >
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </WarRoomLayout>
</template>
