<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
  LayoutDashboard,
  Radio,
  Zap,
  TrendingUp,
  Users,
  Newspaper,
  AlertTriangle,
  Calendar,
  FileText,
  UserCheck,
  Menu,
  X,
  LogOut,
  ShieldAlert,
  Sparkles,
  Eye,
  CheckCircle2,
  AlertCircle,
  Info,
  DollarSign
} from 'lucide-vue-next';
import ThemeToggle from '../Components/ThemeToggle.vue';
import Badge from '../Components/Badge.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Invitado', role: 'visualizador' });
const flash = computed(() => page.props.flash || {});
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);
const isAdmin = computed(() => page.props.auth?.user?.is_admin ?? false);

const isSidebarOpen = ref(true);
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};

const navigation = computed(() => [
  { name: 'Sala de Situación', href: '/dashboard', icon: LayoutDashboard, current: route().current('dashboard') },
  { name: 'Feed Social Multired', href: '/feed', icon: Radio, current: route().current('feed*') },
  { name: 'Carga Fast-Flow', href: '/fast-flow', icon: Zap, current: route().current('fast-flow*'), readOnlyBadge: !canWrite.value },
  { name: 'Predictor de Pauta', href: '/predictor', icon: TrendingUp, current: route().current('predictor*'), isNew: true },
  { name: 'Candidatos & Perfiles', href: '/candidatos', icon: Users, current: route().current('candidatos*') },
  { name: 'Observatorio de Medios', href: '/medios', icon: Newspaper, current: route().current('medios*') },
  { name: 'Centro de Crisis', href: '/crisis', icon: AlertTriangle, current: route().current('crisis*') },
  { name: 'Calendario & Agenda', href: '/calendario', icon: Calendar, current: route().current('calendario*') },
  { name: 'Presupuesto & Pauta', href: '/presupuesto', icon: DollarSign, current: route().current('presupuesto*') },
  { name: 'Briefings Ejecutivos', href: '/briefings', icon: FileText, current: route().current('briefing*') || route().current('briefings*') },
  ...(isAdmin.value ? [{ name: 'Usuarios & Roles', href: '/usuarios', icon: UserCheck, current: route().current('usuarios*') }] : []),
]);

const logout = () => {
  router.post('/logout');
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col transition-colors duration-200">
    <!-- Top Header -->
    <header class="sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 py-2.5 flex items-center justify-between shadow-xs">
      <!-- Left: Logo & Sidebar Toggle -->
      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="toggleSidebar"
          class="p-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none"
        >
          <Menu class="w-5 h-5" />
        </button>

        <Link href="/dashboard" class="flex items-center gap-2.5 group">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center text-white shadow-md shadow-cyan-500/20 group-hover:scale-105 transition-transform">
            <Radio class="w-5 h-5 animate-pulse" />
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="font-extrabold tracking-tight text-lg bg-gradient-to-r from-slate-900 to-slate-700 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
                BrandingPo
              </span>
              <span class="text-[10px] uppercase tracking-widest font-mono font-bold px-1.5 py-0.2 rounded-md bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30">
                WAR ROOM
              </span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 hidden sm:block">Inteligencia Política & Benchmarking Digital</p>
          </div>
        </Link>
      </div>

      <!-- Right Actions: System Live, Theme Toggle, Role & User -->
      <div class="flex items-center gap-2 sm:gap-3">
        <!-- Live Status Pulse -->
        <div class="hidden md:flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          <span class="font-mono">LIVE INTELLIGENCE</span>
        </div>

        <!-- Theme Toggle -->
        <ThemeToggle />

        <!-- User Role Tag -->
        <Badge variant="rol" :value="user.role || 'visualizador'" size="md" />

        <!-- User Profile Pill & Logout -->
        <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
          <div class="hidden sm:block text-right">
            <p class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-none">{{ user.name }}</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ user.email }}</p>
          </div>
          <button
            type="button"
            @click="logout"
            title="Cerrar Sesión"
            class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-500/10 dark:hover:bg-rose-500/20 transition-colors"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </header>

    <!-- Visualizer Mode Alert Banner -->
    <div
      v-if="!canWrite"
      class="bg-amber-500/15 border-b border-amber-500/30 px-4 py-2 text-xs text-amber-800 dark:text-amber-300 flex items-center justify-between"
    >
      <div class="flex items-center gap-2">
        <Eye class="w-4 h-4 text-amber-600 dark:text-amber-400" />
        <span><strong>Modo Visualizador Activo:</strong> Estás explorando la plataforma en modo solo lectura. Las acciones de carga y edición están deshabilitadas para tu perfil.</span>
      </div>
      <span class="font-mono text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-sm bg-amber-500/20">Solo Lectura</span>
    </div>

    <!-- Flash Messages Container -->
    <div v-if="flash.success || flash.error || flash.info" class="p-4 max-w-7xl mx-auto w-full">
      <div
        v-if="flash.success"
        class="p-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm flex items-center gap-2"
      >
        <CheckCircle2 class="w-4 h-4 text-emerald-500" />
        <span>{{ flash.success }}</span>
      </div>
      <div
        v-if="flash.error"
        class="p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-700 dark:text-rose-300 text-sm flex items-center gap-2"
      >
        <AlertCircle class="w-4 h-4 text-rose-500" />
        <span>{{ flash.error }}</span>
      </div>
    </div>

    <!-- Main Workspace (Sidebar + Content) -->
    <div class="flex-1 flex overflow-hidden">
      <!-- Sidebar -->
      <aside
        class="bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-200 z-30 flex flex-col justify-between"
        :class="isSidebarOpen ? 'w-64' : 'w-20'"
      >
        <!-- Nav Items -->
        <nav class="p-3 space-y-1 overflow-y-auto">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all group"
            :class="item.current ? 'bg-cyan-500 text-white font-bold shadow-md shadow-cyan-500/25' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-cyan-600 dark:hover:text-cyan-400'"
            :title="!isSidebarOpen ? item.name : undefined"
          >
            <component :is="item.icon" class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" />
            <span v-if="isSidebarOpen" class="truncate flex-1">{{ item.name }}</span>
            <span
              v-if="isSidebarOpen && item.isNew"
              class="text-[9px] uppercase font-mono font-extrabold px-1.5 py-0.5 rounded-full bg-violet-500/20 text-violet-600 dark:text-violet-300 border border-violet-500/40"
            >
              IA ROI
            </span>
            <span
              v-if="isSidebarOpen && item.readOnlyBadge"
              class="text-[9px] uppercase font-mono px-1 py-0.5 rounded-sm bg-slate-200 dark:bg-slate-800 text-slate-500"
            >
              Ver
            </span>
          </Link>
        </nav>

        <!-- Sidebar Footer Status -->
        <div v-if="isSidebarOpen" class="p-4 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400">
          <div class="flex items-center justify-between">
            <span>Campaña:</span>
            <span class="font-mono font-bold text-slate-700 dark:text-slate-300">2023 - 2025</span>
          </div>
          <div class="mt-1 flex items-center justify-between">
            <span>Candidatos auditados:</span>
            <span class="font-mono font-bold text-cyan-600 dark:text-cyan-400">4 perfiles</span>
          </div>
        </div>
      </aside>

      <!-- Main Scrollable Content -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        <div class="max-w-7xl mx-auto space-y-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
