<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Radio, Lock, Mail, ArrowRight, ShieldCheck, UserCheck, Eye, Sparkles } from '@lucide/vue';
import ThemeToggle from '../../Components/ThemeToggle.vue';
import Badge from '../../Components/Badge.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};

const quickLogin = (role) => {
  router.post('/quick-login', { role });
};
</script>

<template>
  <Head title="Iniciar Sesión | BrandingPo War Room" />

  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative transition-colors duration-200">
    <!-- Top Theme Toggle -->
    <div class="absolute top-5 right-5 z-20">
      <ThemeToggle />
    </div>

    <!-- Background Glow Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[600px] h-[350px] bg-cyan-500/10 dark:bg-cyan-500/15 blur-[120px] rounded-full"></div>
      <div class="absolute bottom-0 right-10 w-[400px] h-[250px] bg-violet-500/10 dark:bg-violet-500/15 blur-[100px] rounded-full"></div>
    </div>

    <!-- Header Logo -->
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center relative z-10">
      <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white shadow-xl shadow-cyan-500/25 mb-4">
        <Radio class="w-7 h-7 animate-pulse" />
      </div>
      <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
        BrandingPo
      </h2>
      <p class="mt-1 text-xs uppercase font-mono tracking-widest font-bold text-cyan-600 dark:text-cyan-400">
        🏛️ Suite de Inteligencia Política & Benchmarking
      </p>
    </div>

    <!-- Login Box -->
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="bg-white dark:bg-slate-900 py-8 px-6 sm:px-10 shadow-xl border border-slate-200 dark:border-slate-800 rounded-3xl">
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Email Input -->
          <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
              Correo Electrónico
            </label>
            <div class="relative rounded-xl shadow-xs">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Mail class="w-4 h-4" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                placeholder="tu_correo@brandingpo.com"
                class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all"
              />
            </div>
            <p v-if="form.errors.email" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.email }}</p>
          </div>

          <!-- Password Input -->
          <div>
            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
              Contraseña
            </label>
            <div class="relative rounded-xl shadow-xs">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <Lock class="w-4 h-4" />
              </div>
              <input
                id="password"
                v-model="form.password"
                type="password"
                autocomplete="current-password"
                required
                placeholder="••••••••"
                class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-cyan-500 text-sm transition-all"
              />
            </div>
            <p v-if="form.errors.password" class="text-xs text-rose-500 mt-1 font-medium">{{ form.errors.password }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full mt-2 py-3 px-4 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/25 flex items-center justify-center gap-2 disabled:opacity-50"
          >
            <span v-if="form.processing">Ingresando...</span>
            <template v-else>
              <span>Acceder al War Room</span>
              <ArrowRight class="w-4 h-4" />
            </template>
          </button>
        </form>

        <!-- Quick Access Demo Buttons (Role Switching for Evaluation) -->
        <div class="mt-8 pt-6 border-t border-slate-200 dark:border-slate-800 text-center">
          <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3 flex items-center justify-center gap-1.5">
            <Sparkles class="w-3.5 h-3.5 text-cyan-500" />
            <span>Acceso Rápido por Rol (Demostración)</span>
          </p>

          <div class="space-y-2">
            <!-- 1. Admin -->
            <button
              type="button"
              @click="quickLogin('admin')"
              class="w-full p-2.5 rounded-xl border border-rose-500/30 bg-rose-500/5 hover:bg-rose-500/15 text-rose-700 dark:text-rose-300 text-xs font-semibold flex items-center justify-between transition-all group"
            >
              <div class="flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 text-rose-500" />
                <div class="text-left">
                  <p class="font-bold">Administrador General</p>
                  <p class="text-[10px] text-slate-500">admin@brandingpo.com (Control Total)</p>
                </div>
              </div>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-rose-500/20">ADMIN</span>
            </button>

            <!-- 2. Consultor -->
            <button
              type="button"
              @click="quickLogin('consultor')"
              class="w-full p-2.5 rounded-xl border border-cyan-500/30 bg-cyan-500/5 hover:bg-cyan-500/15 text-cyan-700 dark:text-cyan-300 text-xs font-semibold flex items-center justify-between transition-all group"
            >
              <div class="flex items-center gap-2">
                <UserCheck class="w-4 h-4 text-cyan-500" />
                <div class="text-left">
                  <p class="font-bold">Consultor Estratégico</p>
                  <p class="text-[10px] text-slate-500">consultor@brandingpo.com (Operativo & Carga)</p>
                </div>
              </div>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-cyan-500/20">CONSULTOR</span>
            </button>

            <!-- 3. Visualizador -->
            <button
              type="button"
              @click="quickLogin('visualizador')"
              class="w-full p-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100/60 dark:bg-slate-800/40 hover:bg-slate-200/60 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold flex items-center justify-between transition-all group"
            >
              <div class="flex items-center gap-2">
                <Eye class="w-4 h-4 text-slate-500" />
                <div class="text-left">
                  <p class="font-bold">Visualizador Ejecutivo</p>
                  <p class="text-[10px] text-slate-500">visualizador@brandingpo.com (Solo Lectura)</p>
                </div>
              </div>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-700">READ-ONLY</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
