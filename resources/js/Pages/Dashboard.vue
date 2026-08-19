<script setup>
import { Head, Link } from '@inertiajs/vue3';
import WarRoomLayout from '../Layouts/WarRoomLayout.vue';
import MetricCard from '../Components/MetricCard.vue';
import SocialCard from '../Components/SocialCard.vue';
import Badge from '../Components/Badge.vue';
import {
  Users,
  Eye,
  TrendingUp,
  DollarSign,
  Radio,
  Sparkles,
  ArrowRight,
  Flame,
  ShieldCheck,
  Zap,
  Target
} from 'lucide-vue-next';

defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_seguidores: '482.5K',
      total_vistas: '3.4M',
      engagement_promedio: '6.8%',
      inversion_pauta_total: 1850000,
      humor_social_promedio: '4.2',
    })
  },
  candidatos: {
    type: Array,
    default: () => []
  },
  ultimas_publicaciones: {
    type: Array,
    default: () => []
  },
  simulador_rapido: {
    type: Object,
    default: () => ({
      formato_ganador: 'Reel / Video Corto',
      roi_estimado: '+142%',
      certeza: '92%'
    })
  }
});
</script>

<template>
  <Head title="Sala de Situación (War Room)" />

  <WarRoomLayout>
    <!-- Welcome / Campaign Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-cyan-950 p-6 sm:p-8 text-white border border-slate-700/60 shadow-xl">
      <div class="relative z-10 max-w-3xl">
        <div class="flex items-center gap-2 mb-3">
          <span class="px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-xs font-mono font-bold tracking-wider border border-cyan-500/40 uppercase">
            🏛️ Sala de Situación Estratégica
          </span>
          <span class="text-xs text-slate-300 font-mono">Ciclo Electoral 2023 - 2025</span>
        </div>
        <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white">
          Monitor de Inteligencia, Medios & Benchmarking Digital
        </h1>
        <p class="mt-2 text-sm sm:text-base text-slate-300 leading-relaxed">
          Auditoría en tiempo real de <strong>4 actores políticos</strong> sobre <strong>6 redes sociales</strong> y el ecosistema de medios de prensa regional.
        </p>

        <div class="mt-5 flex items-center gap-3 flex-wrap">
          <Link
            href="/fast-flow"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm transition-all shadow-md shadow-cyan-500/30 hover:scale-102"
          >
            <Zap class="w-4 h-4" />
            <span>Carga Rápida Fast-Flow</span>
          </Link>
          <Link
            href="/predictor"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600/30 hover:bg-violet-600/50 text-violet-200 border border-violet-500/50 font-semibold text-sm transition-all hover:scale-102"
          >
            <Target class="w-4 h-4 text-violet-400" />
            <span>Simulador de Pauta & ROI</span>
          </Link>
          <Link
            href="/feed"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm transition-all"
          >
            <Radio class="w-4 h-4" />
            <span>Ver Feed Multired</span>
          </Link>
        </div>
      </div>

      <!-- Decorative background icon -->
      <Radio class="absolute right-4 -bottom-10 w-64 h-64 text-cyan-500/10 pointer-events-none" />
    </div>

    <!-- HUD Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <MetricCard
        title="Seguidores Totales Auditados"
        :value="stats.total_seguidores || '482.5K'"
        delta="+8.4%"
        delta-type="increase"
        delta-period="vs semana anterior"
        :icon="Users"
        color="cyan"
      />
      <MetricCard
        title="Visualizaciones Totales"
        :value="stats.total_vistas || '3.4M'"
        delta="+18.2%"
        delta-type="increase"
        delta-period="impacto combinado"
        :icon="Eye"
        color="emerald"
      />
      <MetricCard
        title="Inversión en Pauta Detectada"
        :value="'$1.85M'"
        delta="+12%"
        delta-type="increase"
        delta-period="en pauta Meta/TikTok"
        :icon="DollarSign"
        color="violet"
      />
      <MetricCard
        title="Clima Social Promedio"
        :value="`${stats.humor_social_promedio || '4.2'} / 5`"
        subtitle="Termómetro de opinión pública"
        :icon="Flame"
        color="amber"
      />
    </div>

    <!-- Two Columns: Candidatos Head-to-Head & Feed Highlights -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left 7 Cols: Comparativa Head-to-Head de Candidatos -->
      <div class="lg:col-span-7 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <ShieldCheck class="w-5 h-5 text-cyan-500" />
              <span>Actores Políticos & Estados de Campaña</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Comparativa directa de posicionamiento y tracción</p>
          </div>
          <Link href="/candidatos" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
            <span>Ver detalle</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </Link>
        </div>

        <!-- Candidatos Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
          <!-- Card Candidato Propio -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border-2 border-cyan-500/40 dark:border-cyan-500/50 shadow-sm relative group overflow-hidden">
            <div class="flex items-center gap-3">
              <img
                src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80"
                alt="Intendente Propio"
                class="w-12 h-12 rounded-full object-cover border-2 border-cyan-500"
              />
              <div>
                <div class="flex items-center gap-1.5 flex-wrap">
                  <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Martín Rodríguez</h3>
                  <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-sm bg-cyan-500 text-slate-950 font-mono">PROPIO</span>
                </div>
                <Badge variant="estado" value="en_funciones" size="sm" class="mt-1" />
              </div>
            </div>
            <div class="mt-3.5 grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 dark:border-slate-800 text-center font-mono">
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Seguidores</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">198.4K</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Vistas 7d</span>
                <span class="text-xs font-bold text-cyan-600 dark:text-cyan-400">1.2M</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Humor</span>
                <span class="text-xs font-bold text-emerald-500">4.6★</span>
              </div>
            </div>
          </div>

          <!-- Card Opositor Principal -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs relative group overflow-hidden">
            <div class="flex items-center gap-3">
              <img
                src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80"
                alt="Opositor"
                class="w-12 h-12 rounded-full object-cover border-2 border-purple-500"
              />
              <div>
                <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Carlos Morales</h3>
                <Badge variant="estado" value="opositor" size="sm" class="mt-1" />
              </div>
            </div>
            <div class="mt-3.5 grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 dark:border-slate-800 text-center font-mono">
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Seguidores</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">142.1K</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Vistas 7d</span>
                <span class="text-xs font-bold text-purple-500">890K</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Humor</span>
                <span class="text-xs font-bold text-amber-500">3.4★</span>
              </div>
            </div>
          </div>

          <!-- Card Precandidato Emergente -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs relative group overflow-hidden">
            <div class="flex items-center gap-3">
              <img
                src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80"
                alt="Precandidata"
                class="w-12 h-12 rounded-full object-cover border-2 border-amber-500"
              />
              <div>
                <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Lucía Fernández</h3>
                <Badge variant="estado" value="precandidato" size="sm" class="mt-1" />
              </div>
            </div>
            <div class="mt-3.5 grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 dark:border-slate-800 text-center font-mono">
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Seguidores</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">68.3K</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Vistas 7d</span>
                <span class="text-xs font-bold text-amber-500">540K</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Humor</span>
                <span class="text-xs font-bold text-emerald-400">4.1★</span>
              </div>
            </div>
          </div>

          <!-- Card Intendente Electo (Transición) -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs relative group overflow-hidden">
            <div class="flex items-center gap-3">
              <img
                src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80"
                alt="Electo"
                class="w-12 h-12 rounded-full object-cover border-2 border-emerald-500"
              />
              <div>
                <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm">Esteban Rossi</h3>
                <Badge variant="estado" value="intendente_electo" size="sm" class="mt-1" />
              </div>
            </div>
            <div class="mt-3.5 grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 dark:border-slate-800 text-center font-mono">
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Seguidores</span>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">73.7K</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Vistas 7d</span>
                <span class="text-xs font-bold text-emerald-500">770K</span>
              </div>
              <div>
                <span class="text-[10px] text-slate-400 block uppercase">Humor</span>
                <span class="text-xs font-bold text-emerald-400">4.5★</span>
              </div>
            </div>
          </div>
        </div>

        <!-- AI Paid Ads Predictor Teaser Card -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-violet-900/30 to-slate-900/40 border border-violet-500/30 text-slate-900 dark:text-slate-100 flex items-center justify-between gap-4 flex-wrap">
          <div class="flex items-center gap-3">
            <div class="p-3 rounded-xl bg-violet-600/20 text-violet-400 border border-violet-500/30">
              <Target class="w-6 h-6" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="font-bold text-sm sm:text-base">Predictor de Pauta & Proximidad Algorítmica</span>
                <span class="text-[10px] font-mono font-extrabold px-2 py-0.5 rounded-full bg-violet-500/20 text-violet-300 border border-violet-500/40">
                  92% PRECISIÓN
                </span>
              </div>
              <p class="text-xs text-slate-400 mt-0.5">
                Formato con mayor ROI actual: <strong>Reels & Shorts (CPV: $1.20)</strong>. Simula inversiones en 1 clic.
              </p>
            </div>
          </div>
          <Link
            href="/predictor"
            class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white font-bold text-xs transition-all shadow-md"
          >
            Abrir Simulador
          </Link>
        </div>
      </div>

      <!-- Right 5 Cols: Social Feed Highlights (Live Wall Preview) -->
      <div class="lg:col-span-5 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
              <Radio class="w-5 h-5 text-cyan-500" />
              <span>Últimas Publicaciones (Feed Vivo)</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Publicaciones orgánicas y pauta multired</p>
          </div>
          <Link href="/feed" class="text-xs font-semibold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
            <span>Ver todo</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </Link>
        </div>

        <!-- Sample Post Preview -->
        <SocialCard
          :post="{
            candidato: {
              nombre_completo: 'Martín Rodríguez',
              estado_politico: 'en_funciones',
              es_propio: true,
              avatar_url: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'
            },
            perfil_social: {
              plataforma: 'instagram',
              handle_usuario: '@martinrodriguez_ok'
            },
            plataforma: 'instagram',
            fecha_relativa: 'Hace 3 horas',
            tipo_formato: 'Reel',
            tipo_pauta: 'pauta_paga',
            monto_invertido_pauta: 45000,
            contenido_resumen: 'Recorriendo la nueva etapa de luminarias LED y pavimentación en el Barrio Norte. Más seguridad y mejor calidad de vida para cada vecino.',
            total_likes: 12480,
            total_vistas: 98400,
            total_comentarios: 342,
            total_compartidos: 810,
            termometro_humor_social: 5,
            figuras_acompanantes: ['Gobernador Prov.'],
            comentarios_destacados: [
              '¡Hacía años que esperábamos el asfalto! Excelente trabajo intendente.',
              'Se nota el avance en los barrios.'
            ]
          }"
        />
      </div>
    </div>
  </WarRoomLayout>
</template>
