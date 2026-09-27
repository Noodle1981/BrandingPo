<script setup>
import { computed } from 'vue';
import { AlertTriangle, Flame } from '@lucide/vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      likes: 0,
      love: 0,
      haha: 0,
      wow: 0,
      sad: 0,
      angry: 0,
    }),
  },
});

const emit = defineEmits(['update:modelValue']);

const reacciones = computed(() => props.modelValue || {});

const totalReacciones = computed(() => {
  const r = reacciones.value;
  return (Number(r.likes) || 0) + (Number(r.love) || 0) + (Number(r.haha) || 0) +
         (Number(r.wow) || 0) + (Number(r.sad) || 0) + (Number(r.angry) || 0);
});

const porcentajeEnojo = computed(() => {
  if (totalReacciones.value <= 0) return 0;
  return Math.round(((Number(reacciones.value.angry) || 0) / totalReacciones.value) * 100);
});

const alertaCrisis = computed(() => porcentajeEnojo.value >= 15);

const updateReaction = (field, event) => {
  const val = Number(event.target.value) || 0;
  emit('update:modelValue', {
    ...reacciones.value,
    [field]: val >= 0 ? val : 0,
  });
};
</script>

<template>
  <div class="p-4 rounded-2xl bg-slate-100/70 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 space-y-3">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-base">💬</span>
        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
          Debate Social & Reacciones en Facebook
        </span>
      </div>
      <div class="flex items-center gap-2 font-mono text-xs">
        <span class="text-slate-400">Total:</span>
        <span class="font-extrabold text-cyan-600 dark:text-cyan-400">
          {{ totalReacciones.toLocaleString('es-AR') }}
        </span>
      </div>
    </div>

    <p class="text-[11px] text-slate-500 dark:text-slate-400">
      Registra o calibra el desglose emoji por emoji de la publicación para auditar el termómetro de humor social (Regla GEMINI 1.B.8).
    </p>

    <!-- Grid de 6 emojis de Facebook -->
    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
      <!-- Likes 👍 -->
      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
        <span class="text-base">👍</span>
        <span class="text-[10px] font-bold text-slate-500 uppercase mt-0.5">Me gusta</span>
        <input
          :value="reacciones.likes"
          @input="updateReaction('likes', $event)"
          type="number"
          min="0"
          class="w-full text-center mt-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-cyan-500"
        />
      </div>

      <!-- Love ❤️ -->
      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
        <span class="text-base">❤️</span>
        <span class="text-[10px] font-bold text-rose-500 uppercase mt-0.5">Encanta</span>
        <input
          :value="reacciones.love"
          @input="updateReaction('love', $event)"
          type="number"
          min="0"
          class="w-full text-center mt-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-rose-500"
        />
      </div>

      <!-- Haha 😂 -->
      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
        <span class="text-base">😂</span>
        <span class="text-[10px] font-bold text-amber-500 uppercase mt-0.5">Divierte</span>
        <input
          :value="reacciones.haha"
          @input="updateReaction('haha', $event)"
          type="number"
          min="0"
          class="w-full text-center mt-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-amber-500"
        />
      </div>

      <!-- Wow 😮 -->
      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
        <span class="text-base">😮</span>
        <span class="text-[10px] font-bold text-cyan-500 uppercase mt-0.5">Asombra</span>
        <input
          :value="reacciones.wow"
          @input="updateReaction('wow', $event)"
          type="number"
          min="0"
          class="w-full text-center mt-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-cyan-500"
        />
      </div>

      <!-- Sad 😢 -->
      <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col items-center">
        <span class="text-base">😢</span>
        <span class="text-[10px] font-bold text-indigo-400 uppercase mt-0.5">Entristece</span>
        <input
          :value="reacciones.sad"
          @input="updateReaction('sad', $event)"
          type="number"
          min="0"
          class="w-full text-center mt-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-indigo-500"
        />
      </div>

      <!-- Angry 😡 -->
      <div
        class="p-2 rounded-xl border flex flex-col items-center transition-colors"
        :class="alertaCrisis
          ? 'bg-rose-500/10 border-rose-500/50 dark:bg-rose-950/30'
          : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800'"
      >
        <span class="text-base">😡</span>
        <span class="text-[10px] font-bold text-rose-600 uppercase mt-0.5">Enoja</span>
        <input
          :value="reacciones.angry"
          @input="updateReaction('angry', $event)"
          type="number"
          min="0"
          class="w-full text-center mt-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-rose-500"
        />
      </div>
    </div>

    <!-- Alerta de Crisis Dinámica (😡 > 15%) -->
    <div
      v-if="alertaCrisis"
      class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-start gap-2.5 text-rose-600 dark:text-rose-400 text-xs"
    >
      <AlertTriangle class="w-4 h-4 shrink-0 mt-0.5 text-rose-500 animate-pulse" />
      <div>
        <p class="font-extrabold flex items-center gap-1">
          <span>Alerta de Crisis: {{ porcentajeEnojo }}% de Indignación</span>
        </p>
        <p class="text-[11px] text-rose-700/80 dark:text-rose-300/80 mt-0.5">
          La tasa de enojo supera el 15% del total de reacciones de la publicación. Monitorear de inmediato o preparar réplica oficial de campaña.
        </p>
      </div>
    </div>
  </div>
</template>
