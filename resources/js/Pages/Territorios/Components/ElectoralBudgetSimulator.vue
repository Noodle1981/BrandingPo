<script setup>
import { ref, computed } from 'vue';
import { Calculator, Compass } from '@lucide/vue';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  territorioNombre: {
    type: String,
    default: ''
  },
  formatNumber: {
    type: Function,
    required: true
  },
  formatCurrency: {
    type: Function,
    required: true
  }
});

// --- SIMULADOR INTERACTIVO DE PAUTA VS PADRÓN ---
const budgetSlider = ref(50000); // 50.000 ARS por defecto

const simulatedElectoresImpactados = computed(() => {
  const padron = props.stats.padron_electoral || 31000;
  const estimated = Math.round(budgetSlider.value / 1.6);
  return Math.min(padron, estimated);
});

const simulatedCoberturaPadronPct = computed(() => {
  const padron = props.stats.padron_electoral || 1;
  return Math.min(100, Number(((simulatedElectoresImpactados.value / padron) * 100).toFixed(1)));
});

const simulatedCoberturaMetaVotosPct = computed(() => {
  const meta = props.stats.meta_votos || 1;
  return Math.min(100, Number(((simulatedElectoresImpactados.value / meta) * 100).toFixed(1)));
});

const simulatedFrecuenciaImpacto = computed(() => {
  if (budgetSlider.value < 20000) return '1.2x (Baja exposición)';
  if (budgetSlider.value < 60000) return '2.4x (Frecuencia óptima de recordación)';
  if (budgetSlider.value < 150000) return '3.8x (Alta saturación de campaña)';
  return '5.2x (Dominio absoluto del feed)';
});
</script>

<template>
  <!-- 🎛️ Simulador Interactivo de Inversión en Pauta vs. Padrón -->
  <div class="p-6 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-950 text-white border border-slate-800 shadow-xl space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
          <Calculator class="w-5 h-5" />
        </div>
        <div>
          <h3 class="text-base sm:text-lg font-extrabold text-slate-100 flex items-center gap-2">
            <span>Simulador de Presupuesto & Cobertura del Padrón</span>
          </h3>
          <p class="text-xs text-slate-400">
            Mueve el presupuesto de pauta geolocalizada para simular el impacto en electores de <strong>{{ territorioNombre }}</strong>.
          </p>
        </div>
      </div>

      <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-cyan-500 text-slate-950">
        Algoritmo Predictivo War Room
      </span>
    </div>

    <!-- Slider de Presupuesto -->
    <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
      <div class="flex items-center justify-between flex-wrap gap-2">
        <span class="text-xs font-mono font-bold text-slate-400 uppercase tracking-wider">
          Presupuesto Mensual de Pauta Asignado:
        </span>
        <span class="text-2xl sm:text-3xl font-black font-mono text-cyan-400">
          {{ formatCurrency(budgetSlider) }} ARS
        </span>
      </div>

      <input
        v-model.number="budgetSlider"
        type="range"
        min="10000"
        max="500000"
        step="5000"
        class="w-full h-3 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-cyan-400"
      />

      <div class="flex items-center justify-between text-[10px] font-mono text-slate-500">
        <span>$10.000 (Mínimo Táctico)</span>
        <span>$100.000 (Campaña Media)</span>
        <span>$250.000 (Alta Intensidad)</span>
        <span>$500.000 (Saturación)</span>
      </div>
    </div>

    <!-- Resultados del Simulador -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
      <!-- 1. Electores Únicos Impactados -->
      <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">Electores Impactados</span>
        <span class="text-2xl font-extrabold text-cyan-400 block">
          {{ formatNumber(simulatedElectoresImpactados) }}
        </span>
        <span class="text-[10px] text-slate-400 block">
          de {{ formatNumber(stats.padron_electoral) }} del padrón
        </span>
      </div>

      <!-- 2. Cobertura del Padrón -->
      <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">% Cobertura Padrón</span>
        <span class="text-2xl font-extrabold text-emerald-400 block">
          {{ simulatedCoberturaPadronPct }}%
        </span>
        <span class="text-[10px] text-emerald-500/80 block">
          del electorado total
        </span>
      </div>

      <!-- 3. Cobertura de Meta de Votos -->
      <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">Meta de Victoria (40%)</span>
        <span class="text-2xl font-extrabold text-blue-400 block">
          {{ simulatedCoberturaMetaVotosPct }}%
        </span>
        <span class="text-[10px] text-blue-400/80 block">
          de los {{ formatNumber(stats.meta_votos) }} votos objetivo
        </span>
      </div>

      <!-- 4. Frecuencia Estimada -->
      <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-800/80 space-y-1">
        <span class="text-[10px] text-slate-400 uppercase font-bold block">Frecuencia por Elector</span>
        <span class="text-lg font-extrabold text-amber-400 block">
          {{ simulatedFrecuenciaImpacto }}
        </span>
        <span class="text-[10px] text-slate-400 block">
          repetición del mensaje
        </span>
      </div>
    </div>

    <!-- Sugerencia de Distribución de Canales para esta Inversión -->
    <div class="p-4 rounded-2xl bg-slate-900/40 border border-slate-800/60 flex items-center justify-between flex-wrap gap-4 text-xs font-mono">
      <div class="flex items-center gap-2">
        <Compass class="w-4 h-4 text-cyan-400" />
        <span class="font-bold text-slate-300">Mix de Medios Recomendado:</span>
      </div>
      <div class="flex items-center gap-4 flex-wrap text-[11px]">
        <span class="text-cyan-400">Meta Ads (IG/FB): 60% ({{ formatCurrency(budgetSlider * 0.6) }})</span>
        <span class="text-blue-400">TikTok Ads: 25% ({{ formatCurrency(budgetSlider * 0.25) }})</span>
        <span class="text-red-400">YouTube / Prensa: 15% ({{ formatCurrency(budgetSlider * 0.15) }})</span>
      </div>
    </div>
  </div>
</template>
