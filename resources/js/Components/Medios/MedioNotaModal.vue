<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, Globe, Calendar, User, Link as LinkIcon } from '@lucide/vue';

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
});

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    form.clearErrors();

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
    } else {
      form.reset();
      form.medio_prensa_id = props.medios[0]?.id || '';
      form.candidato_id = props.candidatos[0]?.id || '';
      form.origen_tipo = props.initialOrigen || 'web';
      form.tipo_mencion = 'titular';
      form.fecha_publicacion = new Date().toISOString().slice(0, 10);
      form.tono_mencion = 'favorable';
    }
  }
});

const submit = () => {
  if (props.notaEditar) {
    form.put(`/medios/clipping/${props.notaEditar.id}`, {
      onSuccess: () => emit('close'),
    });
  } else {
    form.post('/medios/clipping', {
      onSuccess: () => emit('close'),
    });
  }
};
</script>

<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs overflow-y-auto"
  >
    <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl space-y-6 my-8">
      <!-- Modal Header -->
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
        <div>
          <h2 class="text-lg font-black text-slate-900 dark:text-slate-100">
            {{ notaEditar ? 'Editar Nota de Prensa' : 'Registrar Nota en el Clipping' }}
          </h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            Ingreso manual de noticias o menciones periodísticas.
          </p>
        </div>
        <button
          type="button"
          @click="emit('close')"
          class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-4">
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

        <!-- Origen & Tipo Mención -->
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

        <!-- Titular -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Titular o Texto de la Noticia: *
          </label>
          <input
            v-model="form.titulo"
            type="text"
            required
            placeholder="Ej: Sisterna encabezó la presentación del nuevo plan de obras viales"
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500 font-bold"
          />
          <p v-if="form.errors.titulo" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.titulo }}</p>
        </div>

        <!-- Resumen -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Bajada / Resumen Informativo:
          </label>
          <textarea
            v-model="form.resumen"
            rows="2"
            placeholder="Breve síntesis de los puntos destacados de la nota..."
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          />
        </div>

        <!-- URL Nota -->
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

        <!-- Tono & Portada -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
              Tono Editorial:
            </label>
            <div class="grid grid-cols-3 gap-1.5">
              <button
                type="button"
                @click="form.tono_mencion = 'favorable'"
                class="py-1.5 px-2 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer"
                :class="form.tono_mencion === 'favorable' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-800 text-slate-500'"
              >
                Favorable
              </button>
              <button
                type="button"
                @click="form.tono_mencion = 'neutro'"
                class="py-1.5 px-2 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer"
                :class="form.tono_mencion === 'neutro' ? 'bg-amber-500/20 border-amber-500 text-amber-600 dark:text-amber-400 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800 text-slate-500'"
              >
                Neutro
              </button>
              <button
                type="button"
                @click="form.tono_mencion = 'critico'"
                class="py-1.5 px-2 rounded-xl border text-xs font-bold transition-all text-center cursor-pointer"
                :class="form.tono_mencion === 'critico' ? 'bg-rose-500/20 border-rose-500 text-rose-600 dark:text-rose-400 ring-2 ring-rose-500/20' : 'border-slate-200 dark:border-slate-800 text-slate-500'"
              >
                Crítico
              </button>
            </div>
          </div>

          <div class="flex items-center gap-2 pt-4">
            <input
              v-model="form.es_tapa_o_principal"
              id="es_tapa"
              type="checkbox"
              class="w-4 h-4 rounded text-cyan-500 focus:ring-cyan-500 border-slate-300"
            />
            <label for="es_tapa" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
              Es Nota Principal / Noticia de Tapa
            </label>
          </div>
        </div>

        <!-- Réplica Oficial -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
            Respuesta / Réplica Oficial del Equipo de Campaña (Opcional):
          </label>
          <textarea
            v-model="form.respuesta_replica_candidato"
            rows="2"
            placeholder="Comunicado emitido, desmentida o aclaración enviada a la redacción..."
            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-cyan-500"
          />
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs transition-all shadow-md shadow-cyan-500/20 disabled:opacity-50 cursor-pointer"
          >
            {{ form.processing ? 'Guardando...' : (notaEditar ? 'Actualizar Nota' : 'Guardar Nota') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
