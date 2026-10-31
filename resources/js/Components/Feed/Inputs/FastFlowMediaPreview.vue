<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
  <!-- Preview de Imagen / Portada -->
  <div v-if="modelValue" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
    <div class="flex items-center justify-between">
      <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">🖼️ Portada / Imagen Detectada</span>
      <button
        type="button"
        @click="emit('update:modelValue', '')"
        class="text-[10px] text-slate-400 hover:text-rose-500 cursor-pointer"
      >
        Quitar (✕)
      </button>
    </div>
    <div class="flex items-center gap-3">
      <img
        :src="modelValue"
        alt="Vista previa de imagen"
        referrerpolicy="no-referrer"
        class="w-16 h-16 object-cover rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs shrink-0"
      />
      <input
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        type="url"
        placeholder="https://..."
        class="flex-1 px-2.5 py-1.5 text-[11px] font-mono rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 truncate"
      />
    </div>
  </div>
</template>
