<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { 
  X, 
  Globe, 
  Calendar, 
  User, 
  Link as LinkIcon, 
  ExternalLink, 
  ChevronDown, 
  ChevronUp, 
  Building2, 
  Newspaper,
  ShieldAlert,
  Flame,
  Star,
  AlertTriangle
} from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  notaEditar: {
    type: Object,
    default: null,
  },
  medios: {
    type: Array,
    default: () => [],
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
  initialOrigen: {
    type: String,
    default: 'web',
  },
});

const emit = defineEmits(['close']);

const mostrarDatosTecnicos = ref(false);

const form = useForm({
  medio_prensa_id: '',
  candidato_id: '',
  origen_tipo: 'web',
  tipo_mencion: 'titular',
  fecha_publicacion: new Date().toISOString().slice(0, 10),
  titulo: '',
  resumen: '',
  url_nota: '',
  tono_mencion: 'favorable',
  puntuacion_sentimiento: 0,
  es_tapa_o_principal: false,
  interacciones_en_redes_del_medio: 0,
  respuesta_replica_candidato: '',
  reacciones_desglose: {
    likes: 0,
    love: 0,
    haha: 0,
    wow: 0,
    sad: 0,
    angry: 0,
  },
});

const totalReaccionesCalculado = computed(() => {
  const r = form.reacciones_desglose || {};
  return (Number(r.likes) || 0) + (Number(r.love) || 0) + (Number(r.haha) || 0) + 
         (Number(r.wow) || 0) + (Number(r.sad) || 0) + (Number(r.angry) || 0);
});

const porcentajeEnojoCalculado = computed(() => {
  if (totalReaccionesCalculado.value <= 0) return 0;
  return Math.round(((Number(form.reacciones_desglose?.angry) || 0) / totalReaccionesCalculado.value) * 100);
});

const alertaCrisisModal = computed(() => {
  return porcentajeEnojoCalculado.value >= 15;
});

const limpiarFormulario = () => {
  form.reset();
  form.medio_prensa_id = props.medios[0]?.id || '';
  form.candidato_id = props.candidatos[0]?.id || '';
  form.origen_tipo = props.initialOrigen || 'web';
  form.tipo_mencion = 'titular';
  form.fecha_publicacion = new Date().toISOString().slice(0, 10);
  form.titulo = '';
  form.resumen = '';
  form.url_nota = '';
  form.tono_mencion = 'favorable';
  form.puntuacion_sentimiento = 0;
  form.es_tapa_o_principal = false;
  form.interacciones_en_redes_del_medio = 0;
  form.respuesta_replica_candidato = '';
  form.reacciones_desglose = {
    likes: 0,
    love: 0,
    haha: 0,
    wow: 0,
    sad: 0,
    angry: 0,
  };
  form.clearErrors();
  mostrarDatosTecnicos.value = false;
};

const handleClose = () => {
  limpiarFormulario();
  emit('close');
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && props.isOpen) {
    handleClose();
  }
};

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    window.addEventListener('keydown', handleKeyDown);
    form.clearErrors();
    mostrarDatosTecnicos.value = false;

    if (props.notaEditar) {
      form.medio_prensa_id = props.notaEditar.medio?.id || props.medios[0]?.id || '';
      form.candidato_id = props.notaEditar.candidato?.id || '';
      form.origen_tipo = props.notaEditar.origen_tipo || 'web';
      form.tipo_mencion = props.notaEditar.tipo_mencion || 'titular';
      form.fecha_publicacion = props.notaEditar.fecha_raw || new Date().toISOString().slice(0, 10);
      form.titulo = props.notaEditar.titulo || '';
      form.resumen = props.notaEditar.resumen || '';
      form.url_nota = props.notaEditar.url_nota || '';
      form.tono_mencion = props.notaEditar.tono_mencion || 'favorable';
      form.puntuacion_sentimiento = props.notaEditar.puntuacion_sentimiento || 0;
      form.es_tapa_o_principal = props.notaEditar.es_tapa_o_principal || false;
      form.interacciones_en_redes_del_medio = props.notaEditar.interacciones || 0;
      form.respuesta_replica_candidato = props.notaEditar.respuesta_replica || '';
      if (props.notaEditar.reacciones_desglose) {
        form.reacciones_desglose = {
          likes: Number(props.notaEditar.reacciones_desglose.likes) || 0,
          love: Number(props.notaEditar.reacciones_desglose.love) || 0,
          haha: Number(props.notaEditar.reacciones_desglose.haha) || 0,
          wow: Number(props.notaEditar.reacciones_desglose.wow) || 0,
          sad: Number(props.notaEditar.reacciones_desglose.sad) || 0,
          angry: Number(props.notaEditar.reacciones_desglose.angry) || 0,
        };
      } else {
        form.reacciones_desglose = { likes: 0, love: 0, haha: 0, wow: 0, sad: 0, angry: 0 };
      }
    } else {
      limpiarFormulario();
    }
  } else {
    window.removeEventListener('keydown', handleKeyDown);
    limpiarFormulario();
  }
});

const submit = () => {
  if (props.notaEditar) {
    form.put(`/medios/clipping/${props.notaEditar.id}`, {
      onSuccess: () => handleClose(),
    });
  } else {
    form.post('/medios/clipping', {
      onSuccess: () => handleClose(),
    });
  }
};
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop interactivo que cierra al hacer clic fuera -->
    <div 
      class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs transition-opacity" 
      @click="handleClose"
    />

    <!-- Contenedor con centrado seguro que previene desbordes superiores (items-start sm:items-center) -->
    <div class="flex min-h-full items-start sm:items-center justify-center p-3 sm:p-4 text-center">
      <!-- Tarjeta del Modal con altura máxima contenida y estructura flex-col -->
      <div 
        class="relative w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl text-left overflow-hidden flex flex-col max-h-[90vh] my-4 sm:my-8 transform transition-all"
        @click.stop
      >
        <!-- Modal Header Fijo -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900 z-10">
          <div>
            <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Newspaper class="w-5 h-5 text-cyan-500 shrink-0" />
              <span>{{ notaEditar ? 'Calibración Editorial de la Noticia' : 'Registrar Nota en el Clipping' }}</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              {{ notaEditar 
                ? 'Audita el tono editorial, enriquece el resumen y registra la réplica oficial de campaña.' 
                : 'Carga manual de coberturas de radio, TV, impreso o portales web.' }}
            </p>
          </div>
          <button
            type="button"
            @click="handleClose"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Form con Body scrolleable y Footer fijo -->
        <form @submit.prevent="submit" class="flex flex-col overflow-hidden flex-1">
          <!-- Body con scroll interno suave -->
          <div class="p-6 overflow-y-auto space-y-4 flex-1 overscroll-contain">

        <!-- ============================================================== -->
        <!-- MODO EDICIÓN: TARJETA DE CONTEXTO INFORMATIVA FIJA            -->
        <!-- ============================================================== -->
        <div v-if="notaEditar" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 space-y-3">
          <!-- Medio & Link -->
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800 overflow-hidden shrink-0 border border-slate-300 dark:border-slate-700 flex items-center justify-center">
                <img
                  v-if="notaEditar.medio?.avatar_url"
                  :src="notaEditar.medio.avatar_url"
                  :alt="notaEditar.medio.nombre"
                  class="w-full h-full object-cover"
                  referrerpolicy="no-referrer"
                />
                <Building2 v-else class="w-4 h-4 text-slate-400" />
              </div>
              <span class="text-xs font-black text-slate-900 dark:text-slate-100 truncate">
                {{ notaEditar.medio?.nombre || 'Medio de Prensa' }}
              </span>
            </div>

            <!-- Botón abrir enlace original si existe -->
            <a
              v-if="form.url_nota"
              :href="form.url_nota"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 font-bold text-[11px] transition-all shrink-0"
            >
              <span>Leer Nota</span>
              <ExternalLink class="w-3.5 h-3.5" />
            </a>
          </div>

          <!-- Titular Original -->
          <div>
            <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-200 leading-snug">
              {{ form.titulo }}
            </h4>
          </div>

          <!-- Badges de contexto: Fecha, Origen, Mención -->
          <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-slate-200/60 dark:border-slate-800/60 text-[11px] font-mono text-slate-500">
            <span class="flex items-center gap-1">
              <Calendar class="w-3 h-3 text-slate-400" />
              <span>{{ notaEditar.fecha || form.fecha_publicacion }}</span>
            </span>
            <span>•</span>
            <span class="capitalize">
              {{ form.origen_tipo === 'facebook' ? 'Facebook' : 'Portal Web' }}
            </span>
            <span>•</span>
            <span>
              Mención en {{ form.tipo_mencion === 'titular' ? 'Titular' : (form.tipo_mencion === 'cuerpo' ? 'Cuerpo' : 'Etiqueta @') }}
            </span>
          </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODO CREACIÓN MANUAL: SELECTORES TÉCNICOS                     -->
        <!-- ============================================================== -->
        <template v-if="!notaEditar">
          <!-- Media Outlet & Candidate Selectors -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Medio de Prensa: *
              </label>
              <select
                v-model="form.medio_prensa_id"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
              >
                <option value="" disabled>Seleccione un medio</option>
                <option v-for="m in medios" :key="m.id" :value="m.id">
                  {{ m.nombre }} ({{ m.tipo_medio }})
                </option>
              </select>
              <p v-if="form.errors.medio_prensa_id" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.medio_prensa_id }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Candidato Mencionado:
              </label>
              <select
                v-model="form.candidato_id"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
              >
                <option value="">(Sin asignar / General)</option>
                <option v-for="c in candidatos" :key="c.id" :value="c.id">
                  {{ c.nombre_completo }} {{ c.es_propio ? '★ (Propio)' : '(Rival)' }}
                </option>
              </select>
            </div>
          </div>

          <!-- Origen & Tipo Mención & Fecha -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Origen:
              </label>
              <select
                v-model="form.origen_tipo"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
              >
                <option value="web">Portal Web / RSS</option>
                <option value="facebook">Publicación Facebook</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Tipo de Mención:
              </label>
              <select
                v-model="form.tipo_mencion"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
              >
                <option value="titular">En el Titular</option>
                <option value="cuerpo">En el Cuerpo</option>
                <option value="etiqueta_directa">Etiqueta Directa (@)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                Fecha de Publicación:
              </label>
              <input
                v-model="form.fecha_publicacion"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-mono"
              />
            </div>
          </div>

          <!-- Titular Manual -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Titular o Texto de la Noticia: *
            </label>
            <input
              v-model="form.titulo"
              type="text"
              required
              placeholder="Ej: Presentaron el nuevo plan de obras públicas para el departamento"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-bold"
            />
            <p v-if="form.errors.titulo" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.titulo }}</p>
          </div>

          <!-- URL Nota Manual -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Enlace Directo a la Nota / Post:
            </label>
            <div class="relative">
              <LinkIcon class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
              <input
                v-model="form.url_nota"
                type="url"
                placeholder="https://..."
                class="w-full pl-9 pr-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>
        </template>

        <!-- Reasignar Candidato en Edición -->
        <div v-if="notaEditar" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Candidato Asociado:
            </label>
            <select
              v-model="form.candidato_id"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
            >
              <option value="">(Sin asignar / General)</option>
              <option v-for="c in candidatos" :key="c.id" :value="c.id">
                {{ c.nombre_completo }} {{ c.es_propio ? '★ (Propio)' : '(Rival)' }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Tipo de Mención:
            </label>
            <select
              v-model="form.tipo_mencion"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-medium"
            >
              <option value="titular">En el Titular</option>
              <option value="cuerpo">En el Cuerpo</option>
              <option value="etiqueta_directa">Etiqueta Directa (@)</option>
            </select>
          </div>
        </div>

        <!-- ============================================================== -->
        <!-- CAMPOS CLAVE DE AUDITORÍA Y CALIBRACIÓN (Tono, Tapa, Resumen, Réplica) -->
        <!-- ============================================================== -->
        
        <!-- Tono Editorial & Noticia de Tapa -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
              Tono Editorial de la Noticia: *
            </label>
            <span class="text-[10px] font-mono text-slate-400">
              Calibra el cálculo de la Matriz de Sesgo
            </span>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <button
              type="button"
              @click="form.tono_mencion = 'favorable'"
              class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer flex flex-col items-center gap-1"
              :class="form.tono_mencion === 'favorable' 
                ? 'bg-emerald-500/20 border-emerald-500 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500/20 shadow-xs' 
                : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
            >
              <span class="text-sm">🟢</span>
              <span>Favorable</span>
            </button>
            <button
              type="button"
              @click="form.tono_mencion = 'neutro'"
              class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer flex flex-col items-center gap-1"
              :class="form.tono_mencion === 'neutro' 
                ? 'bg-amber-500/20 border-amber-500 text-amber-600 dark:text-amber-400 ring-2 ring-amber-500/20 shadow-xs' 
                : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
            >
              <span class="text-sm">🟡</span>
              <span>Neutro</span>
            </button>
            <button
              type="button"
              @click="form.tono_mencion = 'critico'"
              class="py-2.5 px-3 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer flex flex-col items-center gap-1"
              :class="form.tono_mencion === 'critico' 
                ? 'bg-rose-500/20 border-rose-500 text-rose-600 dark:text-rose-400 ring-2 ring-rose-500/20 shadow-xs' 
                : 'border-slate-200 dark:border-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/50'"
            >
              <span class="text-sm">🔴</span>
              <span>Crítico</span>
            </button>
          </div>

          <!-- Toggle Noticia Principal / Tapa -->
          <div class="pt-2 border-t border-slate-100 dark:border-slate-800/70">
            <label class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800/80 cursor-pointer hover:border-amber-500/40 transition-colors">
              <input
                v-model="form.es_tapa_o_principal"
                type="checkbox"
                class="w-4 h-4 rounded text-amber-500 focus:ring-amber-500 border-slate-300 shrink-0"
              />
              <div class="flex items-center justify-between w-full">
                <div class="text-xs">
                  <span class="font-bold text-slate-800 dark:text-slate-200 block">Es Noticia Principal / Portada de Tapa</span>
                  <span class="text-slate-400 text-[11px]">Pondera con mayor peso en el tablero de situación del War Room</span>
                </div>
                <Flame class="w-4 h-4 text-amber-500 shrink-0" />
              </div>
            </label>
          </div>
        </div>

        <!-- ============================================================== -->
        <!-- MÓDULO FAST-FLOW: REACCIONES E INTERACCIÓN EN FACEBOOK         -->
        <!-- ============================================================== -->
        <div v-if="form.origen_tipo === 'facebook'" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <SocialPlatformIcon platform="facebook" size="xs" />
              <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                Cuantificación de Reacciones en Facebook (Fast-Flow):
              </label>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono font-bold">
              <span class="text-slate-400 text-[11px]">Total:</span>
              <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                {{ totalReaccionesCalculado.toLocaleString() }}
              </span>
            </div>
          </div>

          <p class="text-[11px] text-slate-500 dark:text-slate-400">
            Registra o calibra el desglose emoji por emoji de la publicación para auditar el termómetro de humor social (Regla GEMINI 1.B.4).
          </p>

          <!-- Grid de 6 emojis de Facebook -->
          <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
            <!-- Likes 👍 -->
            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
              <span class="text-base">👍</span>
              <span class="text-[10px] font-bold text-slate-500 uppercase mt-0.5">Me gusta</span>
              <input
                v-model.number="form.reacciones_desglose.likes"
                type="number"
                min="0"
                class="w-full text-center mt-1 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-cyan-500"
              />
            </div>

            <!-- Love ❤️ -->
            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
              <span class="text-base">❤️</span>
              <span class="text-[10px] font-bold text-rose-500 uppercase mt-0.5">Encanta</span>
              <input
                v-model.number="form.reacciones_desglose.love"
                type="number"
                min="0"
                class="w-full text-center mt-1 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-rose-500"
              />
            </div>

            <!-- Haha 😂 -->
            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
              <span class="text-base">😂</span>
              <span class="text-[10px] font-bold text-amber-500 uppercase mt-0.5">Divierte</span>
              <input
                v-model.number="form.reacciones_desglose.haha"
                type="number"
                min="0"
                class="w-full text-center mt-1 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-amber-500"
              />
            </div>

            <!-- Wow 😮 -->
            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
              <span class="text-base">😮</span>
              <span class="text-[10px] font-bold text-cyan-500 uppercase mt-0.5">Asombra</span>
              <input
                v-model.number="form.reacciones_desglose.wow"
                type="number"
                min="0"
                class="w-full text-center mt-1 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-cyan-500"
              />
            </div>

            <!-- Sad 😢 -->
            <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
              <span class="text-base">😢</span>
              <span class="text-[10px] font-bold text-indigo-400 uppercase mt-0.5">Entristece</span>
              <input
                v-model.number="form.reacciones_desglose.sad"
                type="number"
                min="0"
                class="w-full text-center mt-1 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-indigo-500"
              />
            </div>

            <!-- Angry 😡 -->
            <div 
              class="p-2 rounded-xl border flex flex-col items-center transition-colors"
              :class="alertaCrisisModal 
                ? 'bg-rose-500/10 border-rose-500/50 dark:bg-rose-950/30' 
                : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800'"
            >
              <span class="text-base">😡</span>
              <span class="text-[10px] font-bold text-rose-600 uppercase mt-0.5">Enoja</span>
              <input
                v-model.number="form.reacciones_desglose.angry"
                type="number"
                min="0"
                class="w-full text-center mt-1 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-rose-500"
              />
            </div>
          </div>

          <!-- Alerta de Crisis Dinámica (😡 > 15%) -->
          <div 
            v-if="alertaCrisisModal"
            class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-start gap-2.5 text-rose-600 dark:text-rose-400 text-xs"
          >
            <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5 text-rose-500 animate-pulse" />
            <div>
              <p class="font-extrabold flex items-center gap-1">
                <span>Alerta de Crisis: {{ porcentajeEnojoCalculado }}% de Indignación</span>
              </p>
              <p class="text-[11px] text-rose-700/80 dark:text-rose-300/80 mt-0.5">
                La tasa de enojo supera el 15% del total de reacciones de la publicación. Monitorear de inmediato o preparar réplica oficial de campaña.
              </p>
            </div>
          </div>
        </div>

        <!-- Bajada / Resumen Informativo -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Bajada / Resumen Informativo:
          </label>
          <textarea
            v-model="form.resumen"
            rows="3"
            placeholder="Síntesis de los puntos destacados o argumentos expresados en la nota..."
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 leading-relaxed"
          />
        </div>

        <!-- Respuesta / Réplica Oficial de Campaña -->
        <div>
          <div class="flex items-center justify-between mb-1">
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
              <ShieldAlert class="w-3.5 h-3.5 text-cyan-500" />
              <span>Respuesta / Réplica Oficial del Equipo de Campaña (Opcional):</span>
            </label>
          </div>
          <textarea
            v-model="form.respuesta_replica_candidato"
            rows="3"
            placeholder="Comunicado emitido, desmentida, derecho a réplica enviado o postura oficial fijada..."
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 leading-relaxed"
          />
        </div>

        <!-- Acordeón Desplegable para Modificar Datos Técnicos en Edición -->
        <div v-if="notaEditar" class="pt-1">
          <button
            type="button"
            @click="mostrarDatosTecnicos = !mostrarDatosTecnicos"
            class="text-[11px] font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 flex items-center gap-1.5 cursor-pointer py-1"
          >
            <ChevronUp v-if="mostrarDatosTecnicos" class="w-3.5 h-3.5" />
            <ChevronDown v-else class="w-3.5 h-3.5" />
            <span>{{ mostrarDatosTecnicos ? 'Ocultar datos de origen' : 'Modificar titular, enlace o fecha de origen' }}</span>
          </button>

          <div v-if="mostrarDatosTecnicos" class="mt-2 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                Editar Titular:
              </label>
              <input
                v-model="form.titulo"
                type="text"
                required
                class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
              />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                  Enlace directo:
                </label>
                <input
                  v-model="form.url_nota"
                  type="url"
                  class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">
                  Fecha de publicación:
                </label>
                <input
                  v-model="form.fecha_publicacion"
                  type="date"
                  class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-mono"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer Fijo (Siempre visible con Cancelar y Guardar) -->
      <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 dark:border-slate-800 shrink-0 bg-slate-50/90 dark:bg-slate-950/90 backdrop-blur-xs z-10">
        <button
          type="button"
          @click="handleClose"
          class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-colors"
        >
          Cancelar
        </button>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs transition-all shadow-md shadow-cyan-500/20 disabled:opacity-50 cursor-pointer"
        >
          {{ form.processing ? 'Guardando...' : (notaEditar ? 'Guardar Calibración' : 'Registrar Nota') }}
        </button>
      </div>
    </form>
  </div>
</div>
</div>
</template>
