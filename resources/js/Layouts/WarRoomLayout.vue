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
  LogOut,
  Sparkles,
  Eye,
  CheckCircle2,
  AlertCircle,
  DollarSign,
  MapPin,
  Building2,
  Swords,
  Globe,
  ShieldHalf,
  ChevronRight
} from '@lucide/vue';
import ThemeToggle from '../Components/ThemeToggle.vue';
import Badge from '../Components/Badge.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Invitado', role: 'visualizador' });
const workspace = computed(() => page.props.workspace || null);
const workspacesDisponibles = computed(() => page.props.workspaces_disponibles || []);
const flash = computed(() => page.props.flash || {});
const canWrite = computed(() => page.props.auth?.user?.can_write ?? false);
const isAdmin = computed(() => page.props.auth?.user?.is_admin ?? false);

const cambiarWorkspace = (event) => {
  const wsId = event.target.value;
  if (wsId && wsId != workspace.value?.id) {
    router.post(`/workspace/cambiar/${wsId}`);
  }
};

// 4 Secciones Iconizadas con Globo Flotante Desplegable (Estilo Gemini)
const navigationSecciones = computed(() => [
  {
    id: 'propio',
    seccion: 'Mi Campaña',
    descripcion: 'Datos oficiales, redes y publicaciones propias',
    icon: Sparkles,
    colorClass: 'text-cyan-500',
    colorActive: 'text-cyan-500',
    bgActive: 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 ring-2 ring-cyan-500/40',
    borderBadge: 'border-cyan-500/30 text-cyan-600 dark:text-cyan-400 bg-cyan-500/10',
    items: [
      {
        name: 'Mi Candidato & Redes',
        desc: 'Ficha oficial, fotos y Punto Cero',
        href: '/mi-candidato',
        icon: Sparkles,
        current: route().current('mi-candidato*'),
      },
      {
        name: 'Fast-Flow Propio',
        desc: 'Carga ágil de publicaciones del candidato',
        href: '/fast-flow?tipo=propio',
        icon: Zap,
        current: route().current('fast-flow*') && page.url.includes('tipo=propio'),
        readOnly: !canWrite.value,
      },
      {
        name: 'Feed Propio',
        desc: 'Timeline de publicaciones propias',
        href: '/feed?filtro=propio',
        icon: Radio,
        current: route().current('feed*') && page.url.includes('filtro=propio'),
      },
    ],
  },
  {
    id: 'oposicion',
    seccion: 'Inteligencia de Oposición',
    descripcion: 'Monitoreo de rivales, benchmarking y auditoría',
    icon: Swords,
    colorClass: 'text-violet-500',
    colorActive: 'text-violet-500',
    bgActive: 'bg-violet-500/15 text-violet-600 dark:text-violet-400 ring-2 ring-violet-500/40',
    borderBadge: 'border-violet-500/30 text-violet-600 dark:text-violet-400 bg-violet-500/10',
    items: [
      {
        name: 'Fichas de Rivales',
        desc: 'Perfiles monitoreados y Punto Cero',
        href: '/candidatos',
        icon: Users,
        current: route().current('candidatos*') && !route().current('candidatos.benchmarking'),
      },
      {
        name: 'Fast-Flow Oposición',
        desc: 'Auditar publicaciones de los rivales',
        href: '/fast-flow?tipo=oposicion',
        icon: Zap,
        current: route().current('fast-flow*') && page.url.includes('tipo=oposicion'),
        readOnly: !canWrite.value,
      },
      {
        name: 'Benchmarking',
        desc: 'Comparativa de crecimiento neto vs Punto 0',
        href: '/candidatos/benchmarking',
        icon: TrendingUp,
        current: route().current('candidatos.benchmarking'),
      },
    ],
  },
  {
    id: 'territorio',
    seccion: 'Territorio, Medios & Entorno',
    descripcion: 'Demografía, clipping de medios y sala de crisis',
    icon: Globe,
    colorClass: 'text-emerald-500',
    colorActive: 'text-emerald-500',
    bgActive: 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500/40',
    borderBadge: 'border-emerald-500/30 text-emerald-600 dark:text-emerald-400 bg-emerald-500/10',
    items: [
      {
        name: 'Territorio & Demografía',
        desc: 'Padrón electoral, pirámide etaria y mapa',
        href: '/territorios',
        icon: MapPin,
        current: route().current('territorios*'),
      },
      {
        name: 'Observatorio de Medios',
        desc: 'Clipping de prensa y tono editorial',
        href: '/medios',
        icon: Newspaper,
        current: route().current('medios*'),
      },
      {
        name: 'Centro de Crisis',
        desc: 'Monitoreo de alertas rojas y alianzas',
        href: '/crisis',
        icon: AlertTriangle,
        current: route().current('crisis*'),
      },
    ],
  },
  {
    id: 'mando',
    seccion: 'Sala de Mando & Estrategia',
    descripcion: 'War Room, predictor algorítmico, presupuesto y agenda',
    icon: ShieldHalf,
    colorClass: 'text-amber-500',
    colorActive: 'text-amber-500',
    bgActive: 'bg-amber-500/15 text-amber-600 dark:text-amber-400 ring-2 ring-amber-500/40',
    borderBadge: 'border-amber-500/30 text-amber-600 dark:text-amber-400 bg-amber-500/10',
    items: [
      {
        name: 'Sala de Situación',
        desc: 'Tablero general ejecutivo (War Room)',
        href: '/dashboard',
        icon: LayoutDashboard,
        current: route().current('dashboard'),
      },
      {
        name: 'Predictor de Pauta',
        desc: 'Simulador algorítmico de presupuesto',
        href: '/predictor',
        icon: TrendingUp,
        current: route().current('predictor*'),
      },
      {
        name: 'Calendario & Agenda',
        desc: 'Actos, recorridas y pautas programadas',
        href: '/calendario',
        icon: Calendar,
        current: route().current('calendario*'),
      },
      {
        name: 'Presupuesto & Pauta',
        desc: 'Control financiero de campaña y pauta',
        href: '/presupuesto',
        icon: DollarSign,
        current: route().current('presupuesto*'),
      },
      {
        name: 'Briefings Ejecutivos',
        desc: 'Informes ejecutivos consolidados',
        href: '/briefings',
        icon: FileText,
        current: route().current('briefing*') || route().current('briefings*'),
      },
      ...(isAdmin.value ? [{
        name: 'Usuarios & Roles',
        desc: 'Gestión de cuentas y permisos',
        href: '/usuarios',
        icon: UserCheck,
        current: route().current('usuarios*'),
      }] : []),
    ],
  },
]);

// Helper para saber si una sección tiene algún sub-enlace activo
const isSeccionActiva = (seccion) => {
  return seccion.items.some(item => item.current);
};

const logout = () => {
  router.post('/logout');
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col transition-colors duration-200">

    <!-- Top Header -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 py-2.5 flex items-center justify-between shadow-xs">

      <!-- Left: Logo & Workspace Selector -->
      <div class="flex items-center gap-3">
        <Link href="/dashboard" class="flex items-center gap-2.5 group">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 flex items-center justify-center text-white shadow-md shadow-cyan-500/20 group-hover:scale-105 transition-transform">
            <Radio class="w-5 h-5 animate-pulse" />
          </div>
          <div>
            <div class="flex items-center gap-1.5">
              <span class="font-extrabold tracking-tight text-lg bg-gradient-to-r from-slate-900 to-slate-700 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
                BrandingPo
              </span>
              <span class="text-[10px] uppercase tracking-widest font-mono font-bold px-1.5 py-0.5 rounded-md bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30">
                WAR ROOM
              </span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 hidden sm:block">Inteligencia Política & Benchmarking Digital</p>
          </div>
        </Link>

        <!-- Selector / Badge de Campaña Activa -->
        <div v-if="workspace" class="hidden md:flex items-center gap-2 pl-3 border-l border-slate-200 dark:border-slate-800">
          <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-600 dark:text-cyan-400 text-xs font-semibold">
            <Building2 class="w-3.5 h-3.5 shrink-0" />
            <select
              v-if="workspacesDisponibles.length > 1"
              :value="workspace.id"
              @change="cambiarWorkspace"
              class="bg-transparent text-xs font-bold font-mono focus:outline-none cursor-pointer border-none p-0 pr-4 text-cyan-700 dark:text-cyan-300"
            >
              <option
                v-for="ws in workspacesDisponibles"
                :key="ws.id"
                :value="ws.id"
                class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100"
              >
                {{ ws.nombre }}
              </option>
            </select>
            <span v-else class="font-mono font-bold max-w-44 truncate">
              {{ workspace.nombre }}
            </span>
          </div>
        </div>
      </div>

      <!-- Right: Live, Theme, Role, User -->
      <div class="flex items-center gap-2 sm:gap-3">
        <!-- Live Pulse -->
        <div class="hidden lg:flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          <span class="font-mono">LIVE INTELLIGENCE</span>
        </div>

        <ThemeToggle />
        <Badge variant="rol" :value="user.role || 'visualizador'" size="md" />

        <!-- User & Logout -->
        <div class="flex items-center gap-2 pl-2 border-l border-slate-200 dark:border-slate-800">
          <div class="hidden sm:block text-right">
            <p class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-none">{{ user.name }}</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ user.email }}</p>
          </div>
          <button
            type="button"
            @click="logout"
            title="Cerrar Sesión"
            class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-500/10 dark:hover:bg-rose-500/20 transition-colors cursor-pointer"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </header>

    <!-- Visualizer Banner -->
    <div
      v-if="user.role === 'visualizador'"
      class="bg-cyan-500/10 dark:bg-cyan-500/15 border-b border-cyan-500/20 px-4 py-2 text-xs flex items-center justify-between text-cyan-800 dark:text-cyan-300"
    >
      <div class="flex items-center gap-2 mx-auto sm:mx-0">
        <Eye class="w-4 h-4 text-cyan-500 shrink-0 animate-pulse" />
        <span>
          <strong>Modo Solo Lectura:</strong> Tienes perfil de <em>Visualizador Ejecutivo</em>. Puedes auditar métricas y descargar briefings, pero las cargas de datos están restringidas.
        </span>
      </div>
      <span class="hidden sm:inline font-mono text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-cyan-500/20">
        SOLO LECTURA
      </span>
    </div>

    <!-- Flash Notifications -->
    <div v-if="flash.success" class="bg-emerald-500/10 border-b border-emerald-500/30 px-4 py-2 text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
      <CheckCircle2 class="w-4 h-4 text-emerald-500 shrink-0" />
      <span>{{ flash.success }}</span>
    </div>
    <div v-if="flash.error" class="bg-rose-500/10 border-b border-rose-500/30 px-4 py-2 text-xs text-rose-800 dark:text-rose-300 flex items-center gap-2">
      <AlertCircle class="w-4 h-4 text-rose-500 shrink-0" />
      <span>{{ flash.error }}</span>
    </div>

    <!-- Layout Principal: Sidebar Fijo + Main Content -->
    <div class="flex-1 flex relative">

      <!-- ─────────────────────────────────────────────────────────────
           SIDEBAR ICONIZADO CON GLOBOS DESPLEGABLES (GEMINI PORTAL STYLE)
           ───────────────────────────────────────────────────────────── -->
      <aside class="w-16 shrink-0 relative z-30 border-r border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md flex flex-col items-center py-4 gap-3 select-none">

        <!-- Acceso Directo Dashboard / War Room (Top) -->
        <div class="relative group/dash flex justify-center w-full">
          <Link
            href="/dashboard"
            class="w-11 h-11 rounded-2xl flex items-center justify-center transition-all duration-200"
            :class="route().current('dashboard')
              ? 'bg-gradient-to-tr from-cyan-600 to-blue-600 text-white shadow-lg shadow-cyan-500/25 ring-2 ring-cyan-400'
              : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800'"
          >
            <LayoutDashboard class="w-5 h-5" />
          </Link>

          <!-- Globo Simple Dashboard -->
          <div class="
            pointer-events-none opacity-0 scale-95 group-hover/dash:opacity-100 group-hover/dash:scale-100
            transition-all duration-150 ease-out
            absolute left-[calc(100%+12px)] top-1/2 -translate-y-1/2 z-50
            whitespace-nowrap
          ">
            <span class="absolute right-full top-1/2 -translate-y-1/2 border-[6px] border-transparent border-r-slate-900 dark:border-r-slate-800" />
            <div class="px-3 py-1.5 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold font-mono shadow-xl border border-slate-800 dark:border-slate-700">
              📊 Sala de Situación (Dashboard)
            </div>
          </div>
        </div>

        <div class="w-8 border-t border-slate-200 dark:border-slate-800 my-1" />

        <!-- 4 Iconos de Sección con Menú Globo Flotante al Hover -->
        <div
          v-for="seccion in navigationSecciones"
          :key="seccion.id"
          class="relative group/sec w-full flex justify-center"
        >
          <!-- Botón Icono de Sección -->
          <button
            type="button"
            class="
              relative w-11 h-11 rounded-2xl flex items-center justify-center
              transition-all duration-200 cursor-pointer
            "
            :class="[
              isSeccionActiva(seccion)
                ? seccion.bgActive
                : 'text-slate-400 dark:text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800/80'
            ]"
            :title="seccion.seccion"
          >
            <!-- Barra indicadora lateral izquierda si está activa la sección -->
            <span
              v-if="isSeccionActiva(seccion)"
              class="absolute -left-2.5 top-1/2 -translate-y-1/2 w-1.5 h-6 rounded-r-full bg-cyan-500 shadow-sm"
            />

            <!-- Icono principal de la sección -->
            <component
              :is="seccion.icon"
              class="w-5 h-5 transition-transform duration-200 group-hover/sec:scale-110"
              :class="isSeccionActiva(seccion) ? seccion.colorActive : ''"
            />
          </button>

          <!-- ─────────────────────────────────────────────────────────────
               GLOBO FLOTANTE (FLYOUT POPOVER) CON ENLACES INTERNOS
               ───────────────────────────────────────────────────────────── -->
          <div class="
            pointer-events-none group-hover/sec:pointer-events-auto
            opacity-0 scale-95 -translate-x-2 group-hover/sec:opacity-100 group-hover/sec:scale-100 group-hover/sec:translate-x-0
            transition-all duration-200 ease-out
            absolute left-[calc(100%+8px)] top-0 z-50
            w-72 sm:w-80
            before:content-[''] before:absolute before:-left-3 before:top-0 before:w-3 before:h-full
          ">
            <!-- Flecha exterior del globo -->
            <span class="absolute -left-2.5 top-4 border-[6px] border-transparent border-r-white dark:border-r-slate-900 drop-shadow-xs" />

            <!-- Contenedor del Globo -->
            <div class="
              p-3.5 rounded-3xl
              bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl
              border border-slate-200/90 dark:border-slate-800/90
              shadow-2xl shadow-slate-950/20 dark:shadow-slate-950/60
              space-y-2
            ">
              <!-- Encabezado de la Sección dentro del Globo -->
              <div class="flex items-center justify-between pb-2 px-1 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-lg flex items-center justify-center" :class="seccion.borderBadge">
                    <component :is="seccion.icon" class="w-3.5 h-3.5" />
                  </div>
                  <div>
                    <h3 class="text-xs font-extrabold text-slate-900 dark:text-slate-100 tracking-tight leading-none">
                      {{ seccion.seccion }}
                    </h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 leading-none">
                      {{ seccion.descripcion }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Lista de Enlaces de la Sección -->
              <div class="space-y-1 pt-1">
                <Link
                  v-for="item in seccion.items"
                  :key="item.name"
                  :href="item.href"
                  class="
                    flex items-center justify-between p-2 rounded-2xl
                    text-xs font-semibold transition-all duration-150 group/link
                  "
                  :class="[
                    item.current
                      ? 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 font-bold ring-1 ring-cyan-500/30'
                      : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/80'
                  ]"
                >
                  <div class="flex items-center gap-2.5 min-w-0">
                    <div
                      class="w-7 h-7 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover/link:scale-110"
                      :class="item.current ? 'bg-cyan-500/20 text-cyan-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                    >
                      <component :is="item.icon" class="w-3.5 h-3.5" />
                    </div>
                    <div class="min-w-0">
                      <p class="leading-snug truncate font-bold">{{ item.name }}</p>
                      <p class="text-[10px] text-slate-400 dark:text-slate-500 font-normal truncate">{{ item.desc }}</p>
                    </div>
                  </div>

                  <!-- Badge de Solo Lectura o Flecha -->
                  <span
                    v-if="item.readOnly"
                    class="shrink-0 text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-rose-500/15 text-rose-500"
                  >
                    LECTURA
                  </span>
                  <ChevronRight
                    v-else
                    class="w-3.5 h-3.5 text-slate-300 dark:text-slate-600 group-hover/link:translate-x-0.5 transition-transform shrink-0"
                  />
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Sidebar: Cerrar Sesión (Bottom) -->
        <div class="mt-auto pt-3 border-t border-slate-200 dark:border-slate-800 w-full flex justify-center relative group/logout">
          <button
            type="button"
            @click="logout"
            class="w-11 h-11 rounded-2xl flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-500/10 transition-all cursor-pointer"
            title="Cerrar Sesión"
          >
            <LogOut class="w-5 h-5" />
          </button>

          <!-- Tooltip Cerrar Sesión -->
          <div class="
            pointer-events-none opacity-0 scale-95 group-hover/logout:opacity-100 group-hover/logout:scale-100
            transition-all duration-150 ease-out
            absolute left-[calc(100%+12px)] bottom-0 z-50
            whitespace-nowrap
          ">
            <span class="absolute right-full top-1/2 -translate-y-1/2 border-[6px] border-transparent border-r-rose-600" />
            <div class="px-3 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-bold font-mono shadow-xl">
              Cerrar Sesión
            </div>
          </div>
        </div>
      </aside>

      <!-- ─────────────────────────────────────────────────────────────
           MAIN WAR ROOM VIEW CONTENT
           ───────────────────────────────────────────────────────────── -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 relative z-10">
        <slot />
      </main>
    </div>
  </div>
</template>
