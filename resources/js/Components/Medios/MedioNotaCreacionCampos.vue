<script setup>
import { Link as LinkIcon } from '@lucide/vue';

defineProps({
  form: {
    type: Object,
    required: true,
  },
  medios: {
    type: Array,
    default: () => [],
  },
  candidatos: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div class="space-y-4">
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
  </div>
</template>
