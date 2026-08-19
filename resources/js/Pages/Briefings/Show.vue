<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
  FileText,
  Printer,
  ArrowLeft,
  Calendar,
  Sparkles,
  Target,
  DollarSign,
  TrendingUp,
  ShieldCheck
} from 'lucide-vue-next';

defineProps({
  informe: {
    type: Object,
    required: true,
  }
});

const triggerPrint = () => {
  window.print();
};
</script>

<template>
  <Head :title="`Briefing: ${informe.titulo}`" />

  <div class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 p-4 sm:p-8 print:p-0 print:bg-white print:text-black">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Top Action Bar (hidden on print) -->
      <div class="flex items-center justify-between gap-4 print:hidden">
        <Link
          href="/briefings"
          class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Volver a Informes Ejecutivos</span>
        </Link>

        <button
          type="button"
          @click="triggerPrint"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-bold text-xs shadow-md cursor-pointer hover:opacity-90 transition-all"
        >
          <Printer class="w-4 h-4" />
          <span>Imprimir / Guardar en PDF</span>
        </button>
      </div>

      <!-- Printable Report Sheet -->
      <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-8 sm:p-12 shadow-md print:shadow-none print:border-none print:p-0 print:bg-white print:text-black">
        <!-- Header / Logo -->
        <div class="flex items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800">
          <div>
            <div class="flex items-center gap-2">
              <span class="text-xl font-extrabold tracking-tight font-mono text-cyan-600 dark:text-cyan-400 print:text-cyan-700">
                BRANDING<span class="text-slate-900 dark:text-slate-100 print:text-black">PO</span>
              </span>
              <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                CONFIDENCIAL
              </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Sala de Situación & Consultoría Política</p>
          </div>

          <div class="text-right text-xs font-mono text-slate-500">
            <div><strong>Fecha:</strong> {{ informe.fecha_generacion }}</div>
            <div><strong>Período:</strong> {{ informe.periodo_cubierto }}</div>
          </div>
        </div>

        <!-- Title -->
        <div class="mt-8">
          <span class="text-xs uppercase font-mono font-bold text-cyan-600 dark:text-cyan-400 block mb-1">
            {{ informe.ciclo }}
          </span>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 print:text-black tracking-tight leading-tight">
            {{ informe.titulo }}
          </h1>
        </div>

        <!-- Executive Summary Section -->
        <div class="mt-8">
          <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">
            1. Resumen Ejecutivo de Situación
          </h3>
          <p class="text-sm sm:text-base text-slate-700 dark:text-slate-300 print:text-slate-800 leading-relaxed bg-slate-50 dark:bg-slate-950/60 print:bg-slate-50 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 print:border-slate-300">
            {{ informe.resumen_ejecutivo }}
          </p>
        </div>

        <!-- Metrics Snapshot Grid -->
        <div v-if="informe.metricas_snapshot" class="mt-8">
          <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400 mb-3">
            2. Indicadores Clave de Rendimiento & Pauta
          </h3>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div
              v-for="(val, key) in informe.metricas_snapshot"
              :key="key"
              class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 print:border-slate-300 bg-white dark:bg-slate-900 print:bg-white text-center"
            >
              <span class="text-[10px] font-mono uppercase text-slate-400 block">
                {{ key.replace(/_/g, ' ') }}
              </span>
              <span class="text-lg font-extrabold font-mono text-slate-900 dark:text-slate-100 print:text-black mt-1 block">
                {{ typeof val === 'number' ? val.toLocaleString() : val }}
              </span>
            </div>
          </div>
        </div>

        <!-- Conclusions & Action Plan -->
        <div v-if="informe.conclusiones" class="mt-8">
          <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-slate-400 mb-2">
            3. Dictamen Estratégico & Recomendaciones del Comando
          </h3>
          <div class="p-5 rounded-2xl bg-cyan-500/10 dark:bg-cyan-500/15 print:bg-slate-50 border border-cyan-500/30 print:border-slate-300 text-sm text-slate-800 dark:text-slate-200 print:text-black leading-relaxed whitespace-pre-line">
            {{ informe.conclusiones }}
          </div>
        </div>

        <!-- Signature Footer -->
        <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800 print:border-slate-300 flex items-center justify-between text-xs text-slate-400 print:text-slate-600 font-mono">
          <div>BrandingPo • Documento Estratégico Reservado</div>
          <div>Página 1 de 1</div>
        </div>
      </div>
    </div>
  </div>
</template>
