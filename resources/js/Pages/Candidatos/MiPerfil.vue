<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import {
  Sparkles,
  Users,
  Link2,
  ExternalLink,
  ShieldCheck,
  Flag,
  Calendar,
  Save,
  Edit3,
  MapPin,
  Vote,
  TrendingUp,
  Image as ImageIcon,
  CheckCircle,
  AlertCircle,
  Settings,
  X
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
  me_gusta_totales: currentRed.value?.me_gusta_totales || 0,
  visualizaciones_totales: currentRed.value?.visualizaciones_totales || 0,
  fecha_punto_cero: currentRed.value?.fecha_punto_cero || new Date().toISOString().slice(0, 10),
  seguidores_punto_cero: currentRed.value?.seguidores_punto_cero || currentRed.value?.seguidores_actuales || 0,
  seguidos_punto_cero: currentRed.value?.seguidos_punto_cero || currentRed.value?.seguidos_actuales || 0,
  publicaciones_punto_cero: currentRed.value?.publicaciones_punto_cero || currentRed.value?.publicaciones_totales || 0,
  me_gusta_punto_cero: currentRed.value?.me_gusta_punto_cero || currentRed.value?.me_gusta_totales || 0,
  visualizaciones_punto_cero: currentRed.value?.visualizaciones_punto_cero || currentRed.value?.visualizaciones_totales || 0,
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
    formRed.me_gusta_totales = red.me_gusta_totales || 0;
    formRed.visualizaciones_totales = red.visualizaciones_totales || 0;
    formRed.fecha_punto_cero = red.fecha_punto_cero || new Date().toISOString().slice(0, 10);
    formRed.seguidores_punto_cero = red.seguidores_punto_cero || red.seguidores_actuales || 0;
    formRed.seguidos_punto_cero = red.seguidos_punto_cero || red.seguidos_actuales || 0;
    formRed.publicaciones_punto_cero = red.publicaciones_punto_cero || red.publicaciones_totales || 0;
    formRed.me_gusta_punto_cero = red.me_gusta_punto_cero || red.me_gusta_totales || 0;
    formRed.visualizaciones_punto_cero = red.visualizaciones_punto_cero || red.visualizaciones_totales || 0;
    formRed.notas_punto_cero = red.notas_punto_cero || '';
    scrapeMessage.value = '';
  }
};

watch(() => props.redes, (newRedes) => {
  if (!newRedes || !newRedes.length) return;
  const current = newRedes.find(r => r.key === selectedPlatformKey.value);
  if (current) {
    formRed.handle_usuario = current.handle_usuario || '';
    formRed.url_perfil = current.url_perfil || '';
    formRed.foto_perfil_url = current.foto_perfil_url || '';
    formRed.esta_activo = current.esta_activo ?? true;
    formRed.esta_verificado = current.esta_verificado ?? false;
    formRed.seguidores_actuales = current.seguidores_actuales || 0;
    formRed.seguidos_actuales = current.seguidos_actuales || 0;
    formRed.publicaciones_totales = current.publicaciones_totales || 0;
    formRed.me_gusta_totales = current.me_gusta_totales || 0;
    formRed.visualizaciones_totales = current.visualizaciones_totales || 0;
    formRed.fecha_punto_cero = current.fecha_punto_cero || new Date().toISOString().slice(0, 10);
    formRed.seguidores_punto_cero = current.seguidores_punto_cero || 0;
    formRed.seguidos_punto_cero = current.seguidos_punto_cero || 0;
    formRed.publicaciones_punto_cero = current.publicaciones_punto_cero || 0;
    formRed.me_gusta_punto_cero = current.me_gusta_punto_cero || 0;
    formRed.visualizaciones_punto_cero = current.visualizaciones_punto_cero || 0;
    formRed.notas_punto_cero = current.notas_punto_cero || '';
  }
}, { deep: true });

const isScraping = ref(false);
const scrapeMessage = ref('');
const scrapeSuccess = ref(false);

const fetchScrapedData = async () => {
  if (!formRed.url_perfil) {
    scrapeMessage.value = 'Por favor pega el enlace del perfil primero.';
    scrapeSuccess.value = false;
    return;
  }
  isScraping.value = true;
  scrapeMessage.value = 'Leyendo foto, seguidores, seguidos y publicaciones desde la red social...';

  try {
    const response = await fetch('/perfiles-sociales/scrape', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        url: formRed.url_perfil,
        plataforma: formRed.plataforma,
      }),
    });

    const data = await response.json();
    if (data) {
      if (data.handle_usuario) formRed.handle_usuario = data.handle_usuario;
      if (data.foto_perfil_url) formRed.foto_perfil_url = data.foto_perfil_url;
      
      if (data.seguidores !== null && data.seguidores !== undefined) {
        formRed.seguidores_actuales = Number(data.seguidores);
        formRed.seguidores_punto_cero = Number(data.seguidores);
      }
      if (data.seguidos !== null && data.seguidos !== undefined) {
        formRed.seguidos_actuales = Number(data.seguidos);
        formRed.seguidos_punto_cero = Number(data.seguidos);
      }
      if (data.publicaciones !== null && data.publicaciones !== undefined) {
        formRed.publicaciones_totales = Number(data.publicaciones);
        formRed.publicaciones_punto_cero = Number(data.publicaciones);
      }
      if (data.me_gusta_totales !== null && data.me_gusta_totales !== undefined) {
        formRed.me_gusta_totales = Number(data.me_gusta_totales);
        formRed.me_gusta_punto_cero = Number(data.me_gusta_totales);
      }
      if (data.visualizaciones_totales !== null && data.visualizaciones_totales !== undefined) {
        formRed.visualizaciones_totales = Number(data.visualizaciones_totales);
        formRed.visualizaciones_punto_cero = Number(data.visualizaciones_totales);
      }
      
      formRed.esta_activo = true;
      scrapeSuccess.value = true;
      scrapeMessage.value = data.mensaje || '¡Datos extraídos con éxito!';
    }
  } catch (err) {
    scrapeSuccess.value = false;
    scrapeMessage.value = 'No se pudo conectar con el lector. Puedes ingresar los números manualmente.';
  } finally {
    isScraping.value = false;
  }
};

const isConfigModalOpen = ref(false);

const openConfigModal = (platformKey = null) => {
  if (platformKey) {
    selectPlatform(platformKey);
  }
  isConfigModalOpen.value = true;
};

const savePerfilSocial = () => {
  // Sincronizar Punto Cero con actuales si están en 0
  if (!formRed.seguidores_punto_cero) formRed.seguidores_punto_cero = formRed.seguidores_actuales;
  if (!formRed.seguidos_punto_cero) formRed.seguidos_punto_cero = formRed.seguidos_actuales;
  if (!formRed.publicaciones_punto_cero) formRed.publicaciones_punto_cero = formRed.publicaciones_totales;
  if (!formRed.me_gusta_punto_cero) formRed.me_gusta_punto_cero = formRed.me_gusta_totales;
  if (!formRed.visualizaciones_punto_cero) formRed.visualizaciones_punto_cero = formRed.visualizaciones_totales;

  formRed.post('/perfiles-sociales', {
    preserveScroll: true,
    onSuccess: () => {
      scrapeMessage.value = '¡Canal guardado correctamente!';
      scrapeSuccess.value = true;
      isConfigModalOpen.value = false;
    }
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
  territorio_id: props.candidato.territorio_id || props.candidato.territorio?.id || '',
  territorio_nombre: props.candidato.territorio?.nombre || '',
  padron_electoral: props.candidato.territorio?.padron_electoral || 0,
  poblacion_total: props.candidato.territorio?.poblacion_total || 0,
  tipo_territorio: props.candidato.territorio?.tipo || 'municipio',
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
const getSocialMeta = (key) => {
  switch (key) {
    case 'instagram':
      return {
        color: '#E4405F',
        bgLight: 'bg-[#E4405F]/15',
      };
    case 'facebook':
      return {
        color: '#1877F2',
        bgLight: 'bg-[#1877F2]/15',
      };
    case 'tiktok':
      return {
        color: '#00F2FE',
        bgLight: 'bg-cyan-500/15',
      };
    case 'x_twitter':
      return {
        color: '#000000',
        bgLight: 'bg-slate-500/15',
      };
    case 'youtube':
      return {
        color: '#FF0000',
        bgLight: 'bg-red-500/15',
      };
    case 'linkedin':
      return {
        color: '#0A66C2',
        bgLight: 'bg-[#0A66C2]/15',
      };
    default:
      return { color: '#06b6d4', bgLight: 'bg-cyan-500/15' };
  }
};

const getSocialPlaceholder = (key) => {
  switch (key) {
    case 'instagram':
      return 'https://www.instagram.com/usuario/';
    case 'facebook':
      return 'https://www.facebook.com/usuario/';
    case 'tiktok':
      return 'https://www.tiktok.com/@usuario';
    case 'x_twitter':
      return 'https://x.com/usuario';
    case 'youtube':
      return 'https://www.youtube.com/@usuario';
    case 'linkedin':
      return 'https://www.linkedin.com/in/usuario/';
    default:
      return 'https://...';
  }
};

const getHandlePlaceholder = (key) => {
  switch (key) {
    case 'instagram':
      return 'ej. @usuario';
    case 'facebook':
      return 'ej. @usuario.oficial';
    case 'tiktok':
      return 'ej. @usuario';
    case 'x_twitter':
      return 'ej. @usuario';
    case 'youtube':
      return 'ej. @canal';
    case 'linkedin':
      return 'ej. in/usuario';
    default:
      return 'ej. @usuario';
  }
};
</script>

<template>
  <Head :title="`Perfil Propio: ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <!-- 1. Header Principal del Cliente de Campaña -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="flex items-start sm:items-center gap-4">
          <div class="relative shrink-0">
            <img
              :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="candidato.nombre_completo"
              referrerpolicy="no-referrer"
              class="w-20 h-20 rounded-2xl object-cover border-2 shadow-md"
              :style="{ borderColor: candidato.color_hex || '#06b6d4' }"
            />
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

      <!-- Semáforo de Canales -->
      <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3 text-xs">
        <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 font-mono uppercase text-[11px]">
          <ShieldCheck class="w-4 h-4 text-cyan-500" />
          Semáforo de Canales Digitales:
        </span>
        <div class="flex items-center gap-4 flex-wrap font-mono text-[11px]">
          <span class="inline-flex items-center gap-1.5 text-blue-500 dark:text-blue-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            Certificada / Verificada
          </span>
          <span class="inline-flex items-center gap-1.5 text-amber-500 dark:text-amber-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            Vinculada / Activa
          </span>
          <span class="inline-flex items-center gap-1.5 text-rose-500 dark:text-rose-400 font-semibold">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            No Vinculada / Inactiva
          </span>
        </div>
      </div>
    </div>

    <!-- 2. Pestañas de Redes Sociales (Grid Centrado y Proporcionado con Logos Oficiales) -->
    <div class="space-y-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div
          v-for="red in redes"
          :key="red.key"
          @click="selectPlatform(red.key)"
          class="p-4 rounded-2xl border-2 transition-all flex flex-col items-center justify-center text-center gap-2 cursor-pointer relative shadow-xs group"
          :class="[
            selectedPlatformKey === red.key
              ? 'ring-2 ring-cyan-500 shadow-md scale-102 ' + tabBadgeStyle(red.color_estado).tab
              : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-sm'
          ]"
        >
          <!-- Botón de Engranaje para Configurar Directamente -->
          <button
            type="button"
            @click.stop="openConfigModal(red.key)"
            class="absolute top-2 right-2 p-1.5 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
            :class="selectedPlatformKey === red.key ? 'text-cyan-500' : 'opacity-70 group-hover:opacity-100'"
            title="Configurar canal y Punto Cero"
          >
            <Settings class="w-3.5 h-3.5" />
          </button>

          <!-- Logo Oficial de la Red -->
          <div class="flex items-center justify-center w-10 h-10 rounded-xl shadow-2xs" :class="getSocialMeta(red.key).bgLight">
            <!-- Instagram -->
            <svg v-if="red.key === 'instagram'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: getSocialMeta(red.key).color }">
              <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
              <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
            </svg>
            <!-- Facebook -->
            <svg v-else-if="red.key === 'facebook'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(red.key).color }">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            <!-- TikTok -->
            <svg v-else-if="red.key === 'tiktok'" class="w-5 h-5 text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
            </svg>
            <!-- X / Twitter -->
            <svg v-else-if="red.key === 'x_twitter'" class="w-5 h-5 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
            <!-- YouTube -->
            <svg v-else-if="red.key === 'youtube'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(red.key).color }">
              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            <!-- LinkedIn -->
            <svg v-else-if="red.key === 'linkedin'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(red.key).color }">
              <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
            </svg>
          </div>

          <span class="font-bold text-xs leading-tight">{{ red.nombre }}</span>

          <span
            class="text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider font-mono"
            :class="tabBadgeStyle(red.color_estado).pill"
          >
            {{ red.color_estado === 'azul' ? '✓ Verificada' : (red.color_estado === 'naranja' ? 'Activa' : 'Inactiva') }}
          </span>
        </div>
      </div>

      <!-- 3. FICHA RESUMEN EJECUTIVA DEL CANAL & PUNTO CERO -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-4">
          <div class="flex items-center gap-4">
            <!-- Foto de Perfil de la Red Social -->
            <div class="relative shrink-0">
              <img
                :src="currentRed.foto_perfil_url || candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(currentRed.handle_usuario || 'User')}&background=0f172a&color=06b6d4`"
                alt="Foto Canal"
                referrerpolicy="no-referrer"
                class="w-14 h-14 rounded-2xl object-cover border-2 border-cyan-500 shadow-sm"
              />
              <div
                class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900"
                :class="{
                  'bg-blue-500 text-white': currentRed.color_estado === 'azul',
                  'bg-amber-500 text-slate-950': currentRed.color_estado === 'naranja',
                  'bg-rose-500 text-white': currentRed.color_estado === 'rojo',
                }"
              >
                <CheckCircle v-if="currentRed.color_estado === 'azul'" class="w-3 h-3" />
                <span v-else class="text-[9px] font-extrabold">{{ currentRed.color_estado === 'naranja' ? '●' : '×' }}</span>
              </div>
            </div>

            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                  <span>{{ currentRed.nombre }}</span>
                </h2>
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
                  :class="tabBadgeStyle(currentRed.color_estado).pill"
                >
                  {{ tabBadgeStyle(currentRed.color_estado).label }}
                </span>
              </div>

              <div class="flex items-center gap-3 mt-1 text-xs font-mono text-slate-600 dark:text-slate-400 flex-wrap">
                <span class="font-bold text-slate-800 dark:text-slate-200">
                  {{ currentRed.handle_usuario || 'Sin handle asignado' }}
                </span>
                <a
                  v-if="currentRed.url_perfil"
                  :href="currentRed.url_perfil"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-cyan-500 hover:text-cyan-400 inline-flex items-center gap-1 text-[11px] underline font-semibold"
                >
                  <span>Abrir enlace</span>
                  <ExternalLink class="w-3 h-3" />
                </a>
                <span v-else class="text-slate-400 text-[11px] italic">
                  (Enlace no configurado)
                </span>
              </div>
            </div>
          </div>

          <!-- Botón para Abrir Modal de Configuración -->
          <button
            type="button"
            @click="openConfigModal(currentRed.key)"
            class="px-4 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold font-mono flex items-center gap-2 transition-all cursor-pointer shadow-sm hover:scale-102"
          >
            <Settings class="w-4 h-4" />
            <span>Configurar Canal & Punto Cero</span>
          </button>
        </div>

        <!-- Tarjetas de Métricas Ejecutivas del Canal (Punto Cero vs Actual) -->
        <div
          class="grid gap-4 font-mono"
          :class="currentRed.key === 'facebook' ? 'grid-cols-1 sm:grid-cols-3' : (currentRed.key === 'tiktok' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4')"
        >
          <!-- Seguidores / Suscriptores / Contactos -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
              <span>👥 {{ currentRed.key === 'youtube' ? 'Suscriptores' : (currentRed.key === 'linkedin' ? 'Contactos / Red' : 'Seguidores') }}</span>
              <span
                v-if="currentRed.crecimiento_neto_seguidores > 0"
                class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10"
              >
                +{{ Number(currentRed.crecimiento_neto_seguidores).toLocaleString() }}
              </span>
            </span>
            <div class="text-2xl font-extrabold text-cyan-600 dark:text-cyan-400">
              {{ Number(currentRed.seguidores_actuales || 0).toLocaleString() }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa (Inicio):</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(currentRed.seguidores_punto_cero || 0).toLocaleString() }}</span>
            </div>
          </div>

          <!-- Cuentas Seguidas (Oculto en YouTube) -->
          <div
            v-if="currentRed.key !== 'youtube'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block">
              🔄 Seguidos
            </span>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-200">
              {{ Number(currentRed.seguidos_actuales || 0).toLocaleString() }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa (Inicio):</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(currentRed.seguidos_punto_cero || 0).toLocaleString() }}</span>
            </div>
          </div>

          <!-- Me Gusta Acumulados (Específico Cabecera TikTok) -->
          <div
            v-if="currentRed.key === 'tiktok'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-rose-500/30 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-rose-500 font-bold block flex items-center justify-between">
              <span>❤️ Me Gusta</span>
              <span
                v-if="currentRed.crecimiento_neto_me_gusta > 0"
                class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10"
              >
                +{{ Number(currentRed.crecimiento_neto_me_gusta).toLocaleString() }}
              </span>
            </span>
            <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">
              {{ Number(currentRed.me_gusta_totales || 0).toLocaleString() }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa (Inicio):</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(currentRed.me_gusta_punto_cero || 0).toLocaleString() }}</span>
            </div>
          </div>

          <!-- Publicaciones / Videos Totales (Oculto en Facebook) -->
          <div
            v-if="currentRed.key !== 'facebook'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
              <span>{{ currentRed.key === 'tiktok' || currentRed.key === 'youtube' ? '🎬 Videos' : (currentRed.key === 'linkedin' ? '📝 Posts / Artículos' : '📄 Publicaciones') }}</span>
              <span
                v-if="currentRed.crecimiento_neto_posts > 0"
                class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10"
              >
                +{{ Number(currentRed.crecimiento_neto_posts).toLocaleString() }}
              </span>
            </span>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-200">
              {{ Number(currentRed.publicaciones_totales || 0).toLocaleString() }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa (Inicio):</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(currentRed.publicaciones_punto_cero || 0).toLocaleString() }}</span>
            </div>
          </div>

          <!-- Visualizaciones Totales (Específico Cabecera YouTube) -->
          <div
            v-if="currentRed.key === 'youtube'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-red-500/30 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-red-500 font-bold block flex items-center justify-between">
              <span>👁️ Visualizaciones</span>
              <span
                v-if="currentRed.crecimiento_neto_visualizaciones > 0"
                class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10"
              >
                +{{ Number(currentRed.crecimiento_neto_visualizaciones).toLocaleString() }}
              </span>
            </span>
            <div class="text-2xl font-extrabold text-red-600 dark:text-red-400">
              {{ Number(currentRed.visualizaciones_totales || 0).toLocaleString() }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa (Inicio):</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(currentRed.visualizaciones_punto_cero || 0).toLocaleString() }}</span>
            </div>
          </div>

          <!-- Fecha de Inicio & Estado -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block">
              📅 Fecha Punto Cero
            </span>
            <div class="text-sm font-bold text-slate-800 dark:text-slate-200 pt-1">
              {{ currentRed.fecha_punto_cero || 'No registrada' }}
            </div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/50 truncate" :title="currentRed.notas_punto_cero || 'Línea de partida de campaña'">
              {{ currentRed.notas_punto_cero || 'Línea de partida de campaña' }}
            </div>
          </div>
        </div>

        <!-- Banner si el canal está inactivo / pendiente -->
        <div
          v-if="currentRed.color_estado === 'rojo'"
          class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-between flex-wrap gap-3"
        >
          <div class="flex items-center gap-2.5">
            <AlertCircle class="w-5 h-5 text-rose-500 shrink-0" />
            <p class="text-xs text-rose-600 dark:text-rose-300">
              Este canal digital figura como <strong>Inactivo o No Vinculado</strong>. Puedes configurarlo y comenzar a auditarlo en cualquier momento.
            </p>
          </div>
          <button
            type="button"
            @click="openConfigModal(currentRed.key)"
            class="px-3.5 py-1.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs font-mono transition-all cursor-pointer"
          >
            Configurar Ahora
          </button>
        </div>
      </div>
    </div>



    <!-- MODAL PARA CONFIGURAR CANAL & PUNTO CERO -->
    <div
      v-if="isConfigModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl" :class="getSocialMeta(currentRed.key).bgLight">
              <!-- Instagram -->
              <svg v-if="currentRed.key === 'instagram'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: getSocialMeta(currentRed.key).color }">
                <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
              </svg>
              <!-- Facebook -->
              <svg v-else-if="currentRed.key === 'facebook'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
              <!-- TikTok -->
              <svg v-else-if="currentRed.key === 'tiktok'" class="w-5 h-5 text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
              </svg>
              <!-- X / Twitter -->
              <svg v-else-if="currentRed.key === 'x_twitter'" class="w-5 h-5 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
              <!-- YouTube -->
              <svg v-else-if="currentRed.key === 'youtube'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              <!-- LinkedIn -->
              <svg v-else-if="currentRed.key === 'linkedin'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Settings class="w-4 h-4 text-cyan-500" />
                <span>Configurar Canal: {{ currentRed.nombre }}</span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Punto Cero, enlace oficial, estado de canal y lector con 1 clic.
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="isConfigModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="savePerfilSocial" class="space-y-5">
          <!-- A. Enlace, Lector Automático y Estados -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
            <!-- Enlace con Botón Auto-Lector -->
            <div>
              <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                  1. Enlace Directo al Perfil de {{ currentRed.nombre }} (URL)
                </label>
                <button
                  type="button"
                  @click="fetchScrapedData"
                  :disabled="isScraping || !formRed.url_perfil"
                  class="px-3 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono flex items-center gap-1.5 transition-all shadow-sm cursor-pointer disabled:opacity-50"
                  title="Leer automáticamente foto, seguidores, seguidos y publicaciones con 1 clic"
                >
                  <Sparkles class="w-3.5 h-3.5" />
                  <span>{{ isScraping ? 'Leyendo datos...' : '⚡ Leer Datos & Foto con 1 Clic' }}</span>
                </button>
              </div>

              <div class="relative">
                <input
                  v-model="formRed.url_perfil"
                  type="url"
                  :placeholder="getSocialPlaceholder(currentRed.key)"
                  class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                />
                <Link2 class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              </div>

              <div v-if="scrapeMessage" class="mt-2 flex items-center gap-2 text-xs font-mono" :class="scrapeSuccess ? 'text-emerald-500' : 'text-amber-500'">
                <CheckCircle v-if="scrapeSuccess" class="w-4 h-4" />
                <AlertCircle v-else class="w-4 h-4" />
                <span>{{ scrapeMessage }}</span>
              </div>
            </div>

            <!-- Handle & Switches -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-200 dark:border-slate-800">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Usuario / Handle *
                </label>
                <input
                  v-model="formRed.handle_usuario"
                  type="text"
                  required
                  :placeholder="getHandlePlaceholder(currentRed.key)"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100"
                />
              </div>

              <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div>
                  <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Canal Activo</span>
                  <span class="text-[10px] text-amber-500 font-semibold">🟠 Pestaña Naranja</span>
                </div>
                <input
                  v-model="formRed.esta_activo"
                  type="checkbox"
                  class="w-5 h-5 rounded text-cyan-500"
                />
              </div>

              <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div>
                  <span class="text-xs font-bold text-blue-500 dark:text-blue-400 block">Cuenta Verificada</span>
                  <span class="text-[10px] text-blue-400 font-semibold">🔵 Pestaña Azul</span>
                </div>
                <input
                  v-model="formRed.esta_verificado"
                  type="checkbox"
                  class="w-5 h-5 rounded text-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- B. FOTO DE PERFIL & TABLA ÚNICA DE NÚMEROS (PUNTO CERO) -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-2">
              <Flag class="w-4 h-4 text-cyan-500" />
              <span>2. Foto de Perfil & Punto Cero (Línea de Base de Inicio)</span>
            </h3>

            <!-- Preview Foto & Input URL -->
            <div class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex-wrap">
              <div class="relative shrink-0">
                <img
                  :src="formRed.foto_perfil_url || candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(formRed.handle_usuario || 'User')}&background=0f172a&color=06b6d4`"
                  alt="Foto Perfil"
                  referrerpolicy="no-referrer"
                  class="w-14 h-14 rounded-2xl object-cover border-2 border-cyan-500 shadow-sm"
                />
              </div>
              <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Foto de Perfil (URL Extraída)
                </label>
                <div class="relative">
                  <input
                    v-model="formRed.foto_perfil_url"
                    type="url"
                    placeholder="https://..."
                    class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100"
                  />
                  <ImageIcon class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                </div>
              </div>
            </div>

            <!-- Tabla Única de Métricas del Punto Cero -->
            <div
              class="grid gap-3 font-mono pt-1"
              :class="currentRed.key === 'facebook' ? 'grid-cols-1 sm:grid-cols-3' : (currentRed.key === 'tiktok' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-2 sm:grid-cols-4')"
            >
              <!-- Seguidores / Suscriptores / Contactos -->
              <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-cyan-500/40 text-center space-y-1">
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  👥 {{ currentRed.key === 'youtube' ? 'Suscriptores Iniciales' : (currentRed.key === 'linkedin' ? 'Contactos Iniciales' : 'Seguidores Iniciales') }}
                </span>
                <input
                  v-model.number="formRed.seguidores_actuales"
                  type="number"
                  min="0"
                  placeholder="ej. 1359"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-cyan-600 dark:text-cyan-400"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Punto Alfa de Inicio</span>
              </div>

              <!-- Seguidos (Oculto en YouTube) -->
              <div
                v-if="currentRed.key !== 'youtube'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  🔄 Seguidos
                </span>
                <input
                  v-model.number="formRed.seguidos_actuales"
                  type="number"
                  min="0"
                  placeholder="ej. 588"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-slate-800 dark:text-slate-200"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Cuentas seguidas</span>
              </div>

              <!-- Me Gusta Iniciales (Específico TikTok) -->
              <div
                v-if="currentRed.key === 'tiktok'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-rose-500/40 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-rose-500 font-bold block">
                  ❤️ Me Gusta Iniciales
                </span>
                <input
                  v-model.number="formRed.me_gusta_totales"
                  type="number"
                  min="0"
                  placeholder="ej. 7063"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-rose-600 dark:text-rose-400"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Likes acumulados</span>
              </div>

              <!-- Publicaciones / Videos Totales (Oculto en Facebook porque no aplica) -->
              <div
                v-if="currentRed.key !== 'facebook'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  {{ currentRed.key === 'tiktok' || currentRed.key === 'youtube' ? '🎬 Videos' : (currentRed.key === 'linkedin' ? '📝 Posts / Artículos' : '📄 Publicaciones Totales') }}
                </span>
                <input
                  v-model.number="formRed.publicaciones_totales"
                  type="number"
                  min="0"
                  placeholder="ej. 64"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-slate-800 dark:text-slate-200"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Videos al comenzar</span>
              </div>

              <!-- Visualizaciones Iniciales (Específico YouTube) -->
              <div
                v-if="currentRed.key === 'youtube'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-red-500/40 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-red-500 font-bold block">
                  👁️ Visualizaciones Iniciales
                </span>
                <input
                  v-model.number="formRed.visualizaciones_totales"
                  type="number"
                  min="0"
                  placeholder="ej. 6210"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-red-600 dark:text-red-400"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Vistas totales canal</span>
              </div>

              <!-- Fecha Punto Cero -->
              <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1">
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  📅 Fecha de Comienzo
                </span>
                <input
                  v-model="formRed.fecha_punto_cero"
                  type="date"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Nacimiento auditoría</span>
              </div>
            </div>
          </div>

          <!-- Submit Button & Cancel -->
          <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="isConfigModalOpen = false"
              class="px-4 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="formRed.processing"
              class="px-6 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/25 flex items-center gap-2 cursor-pointer transition-all hover:scale-102"
            >
              <Save class="w-4 h-4" />
              <span>{{ formRed.processing ? 'Guardando...' : `Guardar y Establecer Punto Cero en ${currentRed.nombre}` }}</span>
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

          <!-- DATOS GEOGRÁFICOS Y ELECTORALES -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-1.5">
                <MapPin class="w-4 h-4 text-cyan-500" />
                <span>Territorio Geográfico & Padrón Electoral</span>
              </span>
              <span class="text-[10px] text-cyan-500 font-mono uppercase font-bold">Base Electoral</span>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                Nombre del Territorio / Departamento / Municipio *
              </label>
              <input
                v-model="formCandidato.territorio_nombre"
                type="text"
                required
                placeholder="ej. Departamento Albardón / San Juan"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div class="grid grid-cols-2 gap-3 font-mono">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Padrón Electoral (Votantes) *
                </label>
                <input
                  v-model.number="formCandidato.padron_electoral"
                  type="number"
                  min="0"
                  placeholder="ej. 24500"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-extrabold text-emerald-500"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Población Total Estimada
                </label>
                <input
                  v-model.number="formCandidato.poblacion_total"
                  type="number"
                  min="0"
                  placeholder="ej. 31000"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100"
                />
              </div>
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
