<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ReportePilaresYEvolucion from '../../Components/Reportes/ReportePilaresYEvolucion.vue';
import ReporteTacticoEcosistema from '../../Components/Reportes/ReporteTacticoEcosistema.vue';
import {
  Download,
  Printer,
  Loader2,
  ArrowLeft,
  Calendar,
  MapPin,
  Vote,
  TrendingUp,
  Sparkles,
  ChevronDown,
} from '@lucide/vue';

const props = defineProps({
  reporte: {
    type: Object,
    required: true,
  },
  candidatos_lista: {
    type: Array,
    default: () => [],
  },
});

const isDownloading = ref(false);

const cargarHtml2Pdf = () => {
  return new Promise((resolve, reject) => {
    if (window.html2pdf) {
      return resolve(window.html2pdf);
    }
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
    script.onload = () => resolve(window.html2pdf);
    script.onerror = reject;
    document.head.appendChild(script);
  });
};

const descargarPdf = async () => {
  if (isDownloading.value) return;
  isDownloading.value = true;

  try {
    // Asegurar posición inicial para evitar desfasajes en getBoundingClientRect de html2canvas
    window.scrollTo(0, 0);

    await cargarHtml2Pdf();

    const element = document.getElementById('reporte-documento');
    if (!element) return;

    const nombreArchivo = `Informe_Trimestral_${props.reporte.candidato.nombre_completo.replace(/\s+/g, '_')}_${props.reporte.candidato.territorio.replace(/\s+/g, '_')}.pdf`;

    const opt = {
      margin: [6, 6, 6, 6],
      filename: nombreArchivo,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        logging: false,
        letterRendering: true,
        scrollY: 0,
        scrollX: 0,
      },
      jsPDF: {
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait'
      },
      pagebreak: {
        mode: ['css', 'legacy'],
        after: '.html2pdf__page-break',
        avoid: ['.evitar-corte', '.directivas-card', 'section']
      }
    };

    await window.html2pdf().set(opt).from(element).save();
  } catch (err) {
    console.warn('Descarga directa falló, activando diálogo de impresión:', err);
    window.print();
  } finally {
    isDownloading.value = false;
  }
};

const imprimir = () => {
  window.print();
};

const cambiarCandidato = (e) => {
  const id = e.target.value;
  router.get('/reportes/ejecutivo', { candidato_id: id }, { preserveState: false });
};
</script>

<template>
  <Head :title="`Informe Estratégico Trimestral - ${reporte.candidato.nombre_completo}`" />

  <div class="min-h-screen bg-slate-100 text-slate-900 font-sans antialiased py-6 sm:py-10 print:py-0 print:bg-white">
    
    <!-- BARRA FLOTANTE DE CONTROL (NO SE IMPRIME) -->
    <div class="no-print max-w-5xl mx-auto px-4 mb-6">
      <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-md flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
          <Link
            href="/dashboard"
            class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition-all"
          >
            <ArrowLeft class="w-3.5 h-3.5" />
            <span>Volver al Dashboard</span>
          </Link>

          <!-- Selector de Candidato si hay varios -->
          <div v-if="candidatos_lista.length > 1" class="relative">
            <select
              :value="reporte.candidato.id"
              @change="cambiarCandidato"
              class="appearance-none bg-slate-50 text-slate-800 text-xs font-semibold rounded-xl pl-3 pr-8 py-2 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500 cursor-pointer"
            >
              <option v-for="c in candidatos_lista" :key="c.id" :value="c.id">
                {{ c.nombre_completo }}
              </option>
            </select>
            <ChevronDown class="w-3.5 h-3.5 text-slate-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button
            type="button"
            @click="descargarPdf"
            :disabled="isDownloading"
            class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 disabled:opacity-50 text-slate-950 font-extrabold text-xs flex items-center gap-2 shadow-sm transition-all hover:scale-102 cursor-pointer"
          >
            <Loader2 v-if="isDownloading" class="w-4 h-4 animate-spin text-slate-950" />
            <Download v-else class="w-4 h-4 text-slate-950" />
            <span>{{ isDownloading ? 'Generando Archivo PDF...' : 'Descargar PDF Directo' }}</span>
          </button>

          <button
            type="button"
            @click="imprimir"
            class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer"
            title="Abrir diálogo de impresión"
          >
            <Printer class="w-3.5 h-3.5 text-slate-500" />
            <span>Imprimir</span>
          </button>
        </div>
      </div>
    </div>

    <!-- DOCUMENTO EJECUTIVO (ID PARA GENERACIÓN DIRECTA DE PDF) -->
    <main
      id="reporte-documento"
      class="max-w-5xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-10 print:p-0 print:border-none print:shadow-none print:rounded-none space-y-6"
    >
      
      <!-- CABECERA INSTITUCIONAL & METADATOS -->
      <header class="border-b-2 border-slate-900 pb-5">
        <div class="flex items-center justify-between flex-wrap gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-slate-950 text-cyan-400 flex items-center justify-center font-extrabold text-lg shadow-sm">
              BP
            </div>
            <div>
              <div class="text-xs font-bold tracking-widest uppercase text-slate-500 font-mono">
                BrandingPo &bull; Situational War Room & Intelligence
              </div>
              <h1 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">
                INFORME ESTRATÉGICO DE RENDIMIENTO POLÍTICO
              </h1>
            </div>
          </div>

          <div class="text-right font-mono text-xs text-slate-600">
            <div>Período: <strong class="text-slate-900 uppercase font-black">{{ reporte.periodo_etiqueta }}</strong></div>
            <div class="text-[11px] text-slate-500">Auditoría Trimestral Cerrada &bull; Emisión: {{ reporte.fecha_emision }}</div>
          </div>
        </div>
      </header>

      <!-- ACTO 1: PERFIL DEL CANDIDATO, PUNTO CERO & RESUMEN EJECUTIVO -->
      <section class="evitar-corte space-y-4">
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-5">
          <div class="flex items-start sm:items-center gap-4">
            <img
              :src="reporte.candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(reporte.candidato.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="reporte.candidato.nombre_completo"
              referrerpolicy="no-referrer"
              class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 shadow-xs shrink-0"
              :style="{ borderColor: reporte.candidato.color_hex }"
            />
            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">
                  {{ reporte.candidato.nombre_completo }}
                </h2>
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-cyan-100 text-cyan-900 border border-cyan-300">
                  {{ reporte.candidato.estado_politico }}
                </span>
              </div>
              <p class="text-xs sm:text-sm font-semibold text-slate-700 mt-0.5">
                {{ reporte.candidato.cargo_aspirado }} &bull; <span class="font-normal text-slate-500">{{ reporte.candidato.partido_coalicion }}</span>
              </p>
              <div class="flex items-center gap-4 text-xs font-mono text-slate-600 mt-2 flex-wrap">
                <span class="flex items-center gap-1">
                  <MapPin class="w-3.5 h-3.5 text-cyan-600" />
                  {{ reporte.candidato.territorio }}
                </span>
                <span class="flex items-center gap-1">
                  <Vote class="w-3.5 h-3.5 text-emerald-600" />
                  Padrón: <strong>{{ Number(reporte.candidato.padron_electoral).toLocaleString('es-AR') }}</strong> electores
                </span>
                <span class="flex items-center gap-1">
                  <TrendingUp class="w-3.5 h-3.5 text-purple-600" />
                  Penetración Padronal: <strong>{{ reporte.candidato.ratio_penetracion_padron }}%</strong>
                </span>
              </div>
            </div>
          </div>

          <!-- Diagnóstico Punto Cero -->
          <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs font-mono min-w-[210px] space-y-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">
              Auditoría Punto Cero
            </span>
            <div class="flex items-baseline justify-between gap-3">
              <span class="text-xs text-slate-600">Base Inicial:</span>
              <span class="font-bold text-slate-900">{{ Number(reporte.punto_cero.seguidores_iniciales).toLocaleString('es-AR') }}</span>
            </div>
            <div class="flex items-baseline justify-between gap-3">
              <span class="text-xs text-slate-600">Comunidad Actual:</span>
              <span class="font-bold text-slate-900">{{ Number(reporte.punto_cero.seguidores_actuales).toLocaleString('es-AR') }}</span>
            </div>
            <div class="border-t border-slate-100 pt-1 flex items-baseline justify-between gap-3 text-emerald-600 font-bold text-xs">
              <span>Crecimiento Neto:</span>
              <span>+{{ Number(reporte.punto_cero.crecimiento_neto).toLocaleString('es-AR') }} (+{{ reporte.punto_cero.crecimiento_pct }}%)</span>
            </div>
          </div>
        </div>

        <!-- Párrafo de Resumen Ejecutivo Automático -->
        <div class="p-4 sm:p-5 rounded-2xl bg-cyan-50/50 border border-cyan-200 text-slate-800 text-xs sm:text-sm leading-relaxed space-y-1">
          <div class="font-bold text-cyan-950 flex items-center gap-1.5 uppercase tracking-wide text-xs font-mono">
            <Sparkles class="w-4 h-4 text-cyan-600" />
            <span>Diagnóstico Ejecutivo del Trimestre</span>
          </div>
          <p>{{ reporte.narrativa.resumen_macro }}</p>
        </div>
      </section>

      <!-- ACTOS 2 Y 3: MATRIZ DE PILARES Y EVOLUCIÓN MENSUAL DELEGADOS -->
      <ReportePilaresYEvolucion :reporte="reporte" />

      <!-- SALTO DE PÁGINA LIMPIO (PÁGINA 1: ESTRATEGIA Y BALANCE -> PÁGINA 2: ECOSISTEMA Y ACCIÓN) -->
      <div class="html2pdf__page-break my-5 print:my-0 border-t border-slate-200/60 print:border-none"></div>

      <!-- ACTOS 4, 5 Y 6: EFICACIA MULTIPLATAFORMA, POSTS ESTRELLA Y DIRECTIVAS DELEGADOS -->
      <ReporteTacticoEcosistema :reporte="reporte" />

      <!-- PIE DE PÁGINA INSTITUCIONAL -->
      <footer class="evitar-corte border-t border-slate-200 pt-4 flex items-center justify-between text-[11px] font-mono text-slate-400 flex-wrap gap-2">
        <div>
          BrandingPo &bull; Situational War Room &bull; Documento Confidencial - Comando de Campaña
        </div>
        <div>
          Generado automáticamente &bull; {{ reporte.fecha_emision }} {{ reporte.hora_emision }}
        </div>
      </footer>
    </main>
  </div>
</template>

<style>
/* Reglas anti-corte y saltos de página (html2canvas y print) */
.evitar-corte {
  page-break-inside: avoid !important;
  break-inside: avoid !important;
}

.directivas-card {
  page-break-inside: avoid !important;
  break-inside: avoid !important;
}

.html2pdf__page-break {
  page-break-before: always !important;
  break-before: page !important;
}

@media print {
  @page {
    size: A4;
    margin: 8mm 8mm;
  }
  body {
    background-color: white !important;
    color: #0f172a !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  .no-print {
    display: none !important;
  }
  .html2pdf__page-break {
    page-break-before: always !important;
    break-before: page !important;
    height: 0 !important;
    margin: 0 !important;
    border: none !important;
  }
}
</style>
