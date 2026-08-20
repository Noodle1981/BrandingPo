<script setup>
import { TrendingUp, TrendingDown, Minus } from '@lucide/vue';

defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  delta: {
    type: [String, Number],
    default: null,
  },
  deltaType: {
    type: String,
    default: 'increase', // 'increase', 'decrease', 'neutral'
  },
  deltaPeriod: {
    type: String,
    default: 'vs semana anterior',
  },
  icon: {
    type: Object,
    default: null,
  },
  color: {
    type: String,
    default: 'cyan', // 'cyan', 'emerald', 'amber', 'rose', 'violet', 'blue'
  },
  subtitle: {
    type: String,
    default: null,
  }
});
</script>

<template>
  <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-xs dark:shadow-md hover:border-slate-300 dark:hover:border-slate-700 transition-all duration-200 group">
    <!-- Top Row -->
    <div class="flex items-center justify-between">
      <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wider uppercase">
        {{ title }}
      </span>
      <div
        v-if="icon"
        class="w-10 h-10 rounded-xl flex items-center justify-center transition-transform group-hover:scale-105"
        :class="{
          'bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-400': color === 'cyan',
          'bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400': color === 'emerald',
          'bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400': color === 'amber',
          'bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400': color === 'rose',
          'bg-violet-500/10 text-violet-600 dark:bg-violet-500/20 dark:text-violet-400': color === 'violet',
          'bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400': color === 'blue',
        }"
      >
        <component :is="icon" class="w-5 h-5" />
      </div>
    </div>

    <!-- Metric Value -->
    <div class="mt-3 flex items-baseline gap-2">
      <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight font-mono">
        {{ value }}
      </span>
      <slot name="badge" />
    </div>

    <!-- Delta & Subtitle -->
    <div class="mt-3 flex items-center gap-1.5 text-xs">
      <template v-if="delta !== null">
        <span
          class="inline-flex items-center gap-0.5 font-semibold px-1.5 py-0.5 rounded-md"
          :class="{
            'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400': deltaType === 'increase',
            'bg-rose-500/15 text-rose-700 dark:text-rose-400': deltaType === 'decrease',
            'bg-slate-500/15 text-slate-700 dark:text-slate-400': deltaType === 'neutral',
          }"
        >
          <TrendingUp v-if="deltaType === 'increase'" class="w-3.5 h-3.5" />
          <TrendingDown v-else-if="deltaType === 'decrease'" class="w-3.5 h-3.5" />
          <Minus v-else class="w-3.5 h-3.5" />
          {{ delta }}
        </span>
        <span class="text-slate-500 dark:text-slate-400">{{ deltaPeriod }}</span>
      </template>
      <span v-else-if="subtitle" class="text-slate-500 dark:text-slate-400">
        {{ subtitle }}
      </span>
    </div>
  </div>
</template>
