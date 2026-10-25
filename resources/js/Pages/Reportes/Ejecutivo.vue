<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
  Award,
  CheckCircle2,
  AlertTriangle,
  Heart,
  MessageCircle,
  Share2,
  Eye,
  Layers,
  ShieldCheck,
  ChevronDown,
  Compass,
  Target,
  Briefcase,
  HeartHandshake,
  BarChart3,
  HelpCircle
} from '@lucide/vue';
import SocialPlatformIcon from '../../Components/SocialPlatformIcon.vue';

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

      <!-- ACTO 2: MATRIZ DE LOS 3 PILARES ESTRATÉGICOS & FRAMING DISCURSIVO -->
      <section class="evitar-corte space-y-4 pt-1">
        <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
          <h3 class="text-base font-black text-slate-950 tracking-tight flex items-center gap-2">
            <Target class="w-4 h-4 text-cyan-600" />
            <span>2. Balance de Framing & Penetración por Pilares Estratégicos</span>
          </h3>
          <span class="text-xs font-mono text-slate-500">Distribución de la Voz de Campaña</span>
        </div>

        <!-- Tarjetas de los 3 Pilares -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div
            v-for="pilar in reporte.matriz_pilares.pilares"
            :key="pilar.clave"
            class="p-4 rounded-xl border space-y-2 bg-slate-50 border-slate-200 flex flex-col justify-between"
          >
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase" :class="pilar.color_badge">
                  {{ pilar.nombre }}
                </span>
                <span class="text-xs font-mono font-black text-slate-900">
                  {{ pilar.porcentaje_share }}% del mensaje
                </span>
              </div>
              <div class="text-xs font-bold text-slate-900 font-sans">
                {{ pilar.subtitulo }}
              </div>
              <p class="text-[11px] text-slate-500 leading-snug">
                <strong>Demanda:</strong> {{ pilar.demanda_central }}
              </p>
            </div>

            <!-- Métrica y sub-ejes -->
            <div class="border-t border-slate-200 pt-2 font-mono text-xs space-y-1">
              <div class="flex justify-between items-center text-slate-700">
                <span>Publicaciones:</span>
                <strong class="text-slate-900">{{ pilar.total_publicaciones }} posts</strong>
              </div>
              <div class="flex justify-between items-center text-purple-700 font-bold">
                <span>Score Ponderado:</span>
                <span>{{ Number(pilar.score_impacto).toLocaleString('es-AR') }} pts</span>
              </div>
              <div class="text-[10px] text-slate-500 pt-1 border-t border-slate-100 flex flex-wrap gap-1">
                <span v-for="sub in pilar.subejes.slice(0, 2)" :key="sub.nombre" class="bg-white px-1.5 py-0.5 rounded border border-slate-200">
                  {{ sub.nombre }} ({{ sub.posts }})
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Narrativa de Pilares -->
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
          <strong>Análisis de Framing & Equilibrio:</strong> {{ reporte.narrativa.narrativa_pilares }}
        </p>
      </section>

      <!-- ACTO 3: EVOLUCIÓN MENSUAL CERRADA & RITMO DE CAMPAÑA -->
      <section class="evitar-corte space-y-4 pt-1">
        <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
          <h3 class="text-base font-black text-slate-950 tracking-tight flex items-center gap-2">
            <Layers class="w-4 h-4 text-emerald-600" />
            <span>3. Balance de Impacto y Evolución Mensual Cerrada</span>
          </h3>
          <span class="text-xs font-mono text-slate-500">Julio &bull; Agosto &bull; Septiembre</span>
        </div>

        <!-- Tarjetas Macro del Trimestre -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono">
          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
            <span class="text-[10px] text-slate-500 uppercase font-bold block">Publicaciones</span>
            <div class="text-xl sm:text-2xl font-black text-slate-900">
              {{ reporte.kpis_trimestre.total_publicaciones }}
            </div>
            <span class="text-[10px] text-slate-500 block">Contenidos auditados</span>
          </div>

          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
            <span class="text-[10px] text-slate-500 uppercase font-bold block">Visualizaciones</span>
            <div class="text-xl sm:text-2xl font-black text-cyan-700">
              {{ Number(reporte.kpis_trimestre.total_visualizaciones).toLocaleString('es-AR') }}
            </div>
            <span class="text-[10px] text-slate-500 block">Alcance acumulado</span>
          </div>

          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
            <span class="text-[10px] text-slate-500 uppercase font-bold block">Score de Impacto</span>
            <div class="text-xl sm:text-2xl font-black text-purple-700">
              {{ Number(reporte.kpis_trimestre.score_impacto_total).toLocaleString('es-AR') }}
            </div>
            <span class="text-[10px] text-slate-500 block">Ponderación cívica</span>
          </div>

          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
            <span class="text-[10px] text-slate-500 uppercase font-bold block">Aprobación Neta</span>
            <div class="text-xl sm:text-2xl font-black text-emerald-700">
              {{ reporte.kpis_trimestre.aprobacion_neta_pct }}%
            </div>
            <span class="text-[10px] text-emerald-600 font-semibold block">Clima altamente favorable</span>
          </div>
        </div>

        <!-- Tabla Mes a Mes -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
          <table class="w-full text-left text-xs border-collapse">
            <thead class="bg-slate-100 font-mono text-[11px] text-slate-700 uppercase">
              <tr class="border-b border-slate-200">
                <th class="py-2.5 px-3 font-bold">Mes Evaluado</th>
                <th class="py-2.5 px-3 font-bold text-center">Publicaciones</th>
                <th class="py-2.5 px-3 font-bold text-center">Me Gusta</th>
                <th class="py-2.5 px-3 font-bold text-center">Comentarios</th>
                <th class="py-2.5 px-3 font-bold text-center">Compartidos</th>
                <th class="py-2.5 px-3 font-bold text-right">Score Impacto</th>
                <th class="py-2.5 px-3 font-bold">Eje Principal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-mono">
              <tr v-for="mes in reporte.desglose_mensual" :key="mes.clave" class="hover:bg-slate-50">
                <td class="py-2.5 px-3 font-bold text-slate-900 font-sans">
                  {{ mes.nombre }}
                </td>
                <td class="py-2.5 px-3 text-center font-bold text-slate-800">
                  {{ mes.publicaciones }}
                </td>
                <td class="py-2.5 px-3 text-center text-slate-600">
                  {{ Number(mes.likes).toLocaleString('es-AR') }}
                </td>
                <td class="py-2.5 px-3 text-center text-slate-600">
                  {{ Number(mes.comentarios).toLocaleString('es-AR') }}
                </td>
                <td class="py-2.5 px-3 text-center text-slate-600">
                  {{ Number(mes.compartidos).toLocaleString('es-AR') }}
                </td>
                <td class="py-2.5 px-3 text-right font-black text-purple-700">
                  {{ Number(mes.score_impacto).toLocaleString('es-AR') }} pts
                </td>
                <td class="py-2.5 px-3 font-sans text-slate-600 text-[11px]">
                  {{ mes.eje_lider }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Narrativa de Evolución Temporal -->
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
          <strong>Análisis de Ritmo Comunicacional:</strong> {{ reporte.narrativa.narrativa_evolucion }}
        </p>
      </section>

      <!-- SALTO DE PÁGINA LIMPIO (PÁGINA 1: ESTRATEGIA Y BALANCE -> PÁGINA 2: ECOSISTEMA Y ACCIÓN) -->
      <div class="html2pdf__page-break my-5 print:my-0 border-t border-slate-200/60 print:border-none"></div>

      <!-- ACTO 4: EFICACIA MULTIPLATAFORMA & MISIÓN DE CANALES -->
      <section class="evitar-corte space-y-4 pt-1">
        <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
          <h3 class="text-base font-black text-slate-950 tracking-tight flex items-center gap-2">
            <ShieldCheck class="w-4 h-4 text-cyan-600" />
            <span>4. Eficacia Multiplataforma & Misión Táctica por Red</span>
          </h3>
          <span class="text-xs font-mono text-slate-500">Segmentación de Audiencias</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div
            v-for="canal in reporte.canales_rendimiento"
            :key="canal.plataforma"
            class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 flex flex-col justify-between"
          >
            <div class="space-y-1.5">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <SocialPlatformIcon :plataforma="canal.plataforma" size="sm" />
                  <span class="font-bold text-xs text-slate-900">{{ canal.nombre }}</span>
                </div>
                <span class="text-[10px] font-mono text-slate-400">{{ canal.handle }}</span>
              </div>
              <div class="text-[11px] font-bold text-cyan-800 leading-tight">
                {{ canal.mision }}
              </div>
              <p class="text-[10px] text-slate-500 leading-snug">
                {{ canal.rol_estrategico }}
              </p>
            </div>

            <div class="space-y-1 font-mono text-xs border-t border-slate-200 pt-2">
              <div class="flex justify-between text-slate-600">
                <span>Comunidad:</span>
                <strong class="text-slate-900">{{ Number(canal.seguidores_actuales).toLocaleString('es-AR') }}</strong>
              </div>
              <div class="flex justify-between text-emerald-600 font-bold">
                <span>Crecimiento:</span>
                <span>+{{ Number(canal.crecimiento_neto).toLocaleString('es-AR') }} (+{{ canal.crecimiento_pct }}%)</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>Posts en Trimestre:</span>
                <span class="text-slate-900 font-medium">{{ canal.publicaciones_trimestre }}</span>
              </div>
              <div class="flex justify-between text-purple-700 font-bold border-t border-slate-200 pt-1">
                <span>Score Impacto:</span>
                <span>{{ Number(canal.score_impacto).toLocaleString('es-AR') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Narrativa del Ecosistema -->
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
          <strong>Diagnóstico de Ecosistema:</strong> {{ reporte.narrativa.narrativa_canales }}
        </p>
      </section>

      <!-- ACTO 5: PUBLICACIONES ESTRELLA DEL TRIMESTRE -->
      <section class="evitar-corte space-y-4 pt-1">
        <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
          <h3 class="text-base font-black text-slate-950 tracking-tight flex items-center gap-2">
            <Award class="w-4 h-4 text-amber-500" />
            <span>5. Publicaciones Estrella del Trimestre (Mayor Resonancia)</span>
          </h3>
          <span class="text-xs font-mono text-slate-500">Top 3 por Score Ponderado</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div
            v-for="(post, idx) in reporte.top_publicaciones"
            :key="post.id"
            class="evitar-corte p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5 flex flex-col justify-between"
          >
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-amber-100 text-amber-900 border border-amber-300">
                  Puesto #{{ idx + 1 }}
                </span>
                <div class="flex items-center gap-1.5 text-xs font-mono text-slate-500">
                  <SocialPlatformIcon :plataforma="post.plataforma" size="xs" />
                  <span>{{ post.fecha_publicacion }}</span>
                </div>
              </div>

              <div class="text-[11px] font-mono font-bold text-cyan-700 uppercase">
                🏷️ Eje: {{ post.eje_nombre }}
              </div>

              <p class="text-xs text-slate-800 line-clamp-4 leading-relaxed font-sans italic">
                "{{ post.contenido_resumen }}"
              </p>
            </div>

            <div class="border-t border-slate-200 pt-2 font-mono text-xs space-y-1">
              <div class="flex items-center justify-between text-slate-600 text-[11px]">
                <span class="flex items-center gap-1"><Heart class="w-3 h-3 text-rose-500" /> {{ post.likes }}</span>
                <span class="flex items-center gap-1"><MessageCircle class="w-3 h-3 text-cyan-600" /> {{ post.comentarios }}</span>
                <span class="flex items-center gap-1"><Share2 class="w-3 h-3 text-emerald-600" /> {{ post.compartidos }}</span>
              </div>
              <div class="flex items-center justify-between text-purple-700 font-bold pt-1 border-t border-slate-100">
                <span>Score de Tracción:</span>
                <span>{{ post.score_impacto }} pts</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Narrativa de Contenido -->
        <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-200">
          <strong>Patrón de Conexión Emocional:</strong> {{ reporte.narrativa.narrativa_contenido }}
        </p>
      </section>

      <!-- ACTO 6: CLIMA DE OPINIÓN & HOJA DE RUTA TÁCTICA PARA EL MES EN CURSO -->
      <section class="evitar-corte space-y-4 pt-1">
        <div class="border-b border-slate-200 pb-2 flex items-center justify-between">
          <h3 class="text-base font-black text-slate-950 tracking-tight flex items-center gap-2">
            <CheckCircle2 class="w-4 h-4 text-emerald-600" />
            <span>6. Clima Ciudadano & Hoja de Ruta Táctica para el Mes en Curso</span>
          </h3>
          <span class="text-xs font-mono text-slate-500">Plan de Acción Inmediato</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="evitar-corte p-4 rounded-xl bg-emerald-50 border border-emerald-200 space-y-1">
            <span class="text-[10px] font-mono uppercase font-bold text-emerald-800">Aprobación Neta Ciudadana</span>
            <div class="text-2xl font-black text-emerald-900 font-mono">
              {{ reporte.kpis_trimestre.aprobacion_neta_pct }}%
            </div>
            <p class="text-[11px] text-emerald-700 leading-snug">
              Sentimiento orgánico marcadamente positivo y constructivo en comunidades barriales.
            </p>
          </div>

          <div class="evitar-corte p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
            <span class="text-[10px] font-mono uppercase font-bold text-slate-600">Semáforo de Indignación (😡)</span>
            <div class="text-2xl font-black text-slate-900 font-mono">
              {{ reporte.kpis_trimestre.tasa_indignacion_pct }}%
            </div>
            <p class="text-[11px] text-slate-600 leading-snug">
              Totalmente blindado (muy por debajo del umbral crítico de alerta roja del 15%).
            </p>
          </div>

          <div class="evitar-corte p-4 rounded-xl bg-purple-50 border border-purple-200 space-y-1">
            <span class="text-[10px] font-mono uppercase font-bold text-purple-800">Calificación de Humor Social</span>
            <div class="text-2xl font-black text-purple-900 font-mono">
              ⭐⭐⭐⭐⭐ {{ reporte.kpis_trimestre.humor_social_score }} / 5
            </div>
            <p class="text-[11px] text-purple-700 leading-snug">
              Excelente receptividad comunitaria ante el despliegue territorial.
            </p>
          </div>
        </div>

        <!-- Prioridades Tácticas Recomendadas -->
        <div class="evitar-corte directivas-card p-4 sm:p-5 rounded-2xl bg-slate-900 text-white space-y-3">
          <div class="text-xs font-mono font-bold text-cyan-400 uppercase tracking-wider flex items-center gap-1.5">
            <Sparkles class="w-4 h-4" />
            <span>Directivas Estratégicas para el Comando de Campaña</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs leading-relaxed">
            <div
              v-for="(rec, idx) in reporte.narrativa.prioridades_tacticas"
              :key="idx"
              class="evitar-corte p-3.5 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1.5"
            >
              <div class="text-cyan-400 font-bold font-mono text-xs">Directiva #{{ idx + 1 }}</div>
              <p class="text-slate-300 text-xs leading-relaxed">{{ rec }}</p>
            </div>
          </div>
        </div>
      </section>

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
