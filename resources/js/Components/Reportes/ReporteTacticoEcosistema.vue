<script setup>
import {
  ShieldCheck,
  Award,
  CheckCircle2,
  Heart,
  MessageCircle,
  Share2,
  Sparkles,
} from '@lucide/vue';
import SocialPlatformIcon from '../SocialPlatformIcon.vue';

const props = defineProps({
  reporte: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <div class="space-y-4">
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

    <!-- ACTO 6: CLIMA DE OPINIÓN & HOJA DE RUTA TÁCTICA -->
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
  </div>
</template>
