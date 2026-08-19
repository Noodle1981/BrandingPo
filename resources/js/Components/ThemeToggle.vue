<script setup>
import { ref, onMounted } from 'vue';
import { Sun, Moon } from 'lucide-vue-next';

const isDark = ref(true);

const toggleTheme = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    localStorage.setItem('brandingpo_theme', 'dark');
  } else {
    document.documentElement.classList.remove('dark');
    localStorage.setItem('brandingpo_theme', 'light');
  }
};

onMounted(() => {
  const saved = localStorage.getItem('brandingpo_theme');
  if (saved === 'light') {
    isDark.value = false;
    document.documentElement.classList.remove('dark');
  } else {
    isDark.value = true;
    document.documentElement.classList.add('dark');
  }
});
</script>

<template>
  <button
    type="button"
    @click="toggleTheme"
    class="relative p-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 hover:text-cyan-600 dark:hover:text-cyan-400 hover:border-cyan-500/50 transition-all duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-500"
    :title="isDark ? 'Cambiar a Modo Claro (Executive Light)' : 'Cambiar a Modo Oscuro (War Room)'"
  >
    <Sun v-if="isDark" class="w-5 h-5 transition-transform duration-200 hover:rotate-45 text-amber-400" />
    <Moon v-else class="w-5 h-5 transition-transform duration-200 hover:-rotate-12 text-slate-700" />
  </button>
</template>
