import { ref, computed } from 'vue';
import { useFormatters } from './useFormatters';
import { useSocialMetrics } from './useSocialMetrics';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
} from 'chart.js';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
);

export function useDashboardCharts(props) {
  const { formatNumber } = useFormatters();
  const { getSocialMeta } = useSocialMetrics();


// Redes con cuenta configurada o activas del candidato para los filtros de las gráficas
const redesDisponiblesTimeline = computed(() => {
  return props.redesDesglose.filter(r => r.handle_usuario || r.esta_activo || r.seguidores > 0);
});

// Redes que manejan métricas de visualizaciones activas (> 0 vistas) para la Gráfica 3
const redesConVistasDisponibles = computed(() => {
  return props.redesDesglose.filter(r => (r.vistas_acumuladas && r.vistas_acumuladas > 0));
});

// ─────────────────────────────────────────────────────────────────────────────
// ─────────────────────────────────────────────────────────────────────────────
// 1. GRÁFICA TEMPORAL: COMUNIDAD (SEGUIDORES NETOS) CON AGRUPACIÓN INTELIGENTE
// ─────────────────────────────────────────────────────────────────────────────
const selectedComunidadPlatform = ref('todas');
const agrupacionTemporalComunidad = ref('semana'); // 'semana' (default) | 'mes' | 'dia'
const agrupacionTemporalScore = ref('semana');
const agrupacionTemporalVistas = ref('semana');

// Helper para agrupar puntos por Semana o Mes reduciendo saturación visual
const agruparPuntosTimeline = (puntosArray, modo, hitos = [], platKey = null) => {
  if (!puntosArray || puntosArray.length === 0) return [];
  if (modo === 'dia') return puntosArray;

  const grupos = {};

  puntosArray.forEach(p => {
    let d;
    if (p.fecha_raw) {
      d = new Date(p.fecha_raw + 'T12:00:00');
    } else if (p.fecha && p.fecha.includes('/')) {
      const parts = p.fecha.split('/');
      d = new Date(2026, parseInt(parts[1], 10) - 1, parseInt(parts[0], 10), 12);
    } else {
      d = new Date();
    }

    let key, label;
    if (modo === 'mes') {
      const year = d.getFullYear();
      const month = d.getMonth();
      const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
      key = `${year}-${String(month + 1).padStart(2, '0')}`;
      label = `${meses[month]} '${String(year).slice(-2)}`;
    } else {
      // modo === 'semana': corte semanal al domingo
      const day = d.getDay();
      const diff = day === 0 ? 0 : 7 - day;
      const finSemana = new Date(d);
      finSemana.setDate(d.getDate() + diff);

      const y = finSemana.getFullYear();
      const m = String(finSemana.getMonth() + 1).padStart(2, '0');
      const dia = String(finSemana.getDate()).padStart(2, '0');
      key = `${y}-${m}-${dia}`;
      label = `Sem. ${dia}/${m}`;
    }

    if (!grupos[key]) {
      grupos[key] = {
        key,
        label,
        puntos: []
      };
    }
    grupos[key].puntos.push(p);
  });

  const keys = Object.keys(grupos).sort();

  return keys.map(k => {
    const g = grupos[k];
    const ultimoPunto = g.puntos[g.puntos.length - 1];

    const tieneBooster = g.puntos.some(pto => {
      return hitos.some(h =>
        (!platKey || platKey === 'todas' || h.plataforma === platKey) && (
          (h.fecha_raw && pto.fecha_raw && h.fecha_raw === pto.fecha_raw) ||
          (h.fecha && pto.fecha && h.fecha === pto.fecha)
        )
      );
    });

    const infoBooster = tieneBooster
      ? hitos.find(h =>
          (!platKey || platKey === 'todas' || h.plataforma === platKey) &&
          g.puntos.some(pto =>
            (h.fecha_raw && pto.fecha_raw && h.fecha_raw === pto.fecha_raw) ||
            (h.fecha && pto.fecha && h.fecha === pto.fecha)
          )
        )
      : null;

    return {
      fecha: g.label,
      fecha_raw: k,
      seguidores: ultimoPunto.seguidores ?? 0,
      vistas: ultimoPunto.vistas ?? 0,
      puntos: ultimoPunto.puntos ?? 0,
      interacciones: ultimoPunto.interacciones ?? 0,
      tieneBooster,
      infoBooster,
    };
  });
};

const comunidadChartData = computed(() => {
  const hitos = props.hitosBooster || [];
  const series = props.seriesPorRed || {};
  const plataformasKeys = Object.keys(series);
  const modo = agrupacionTemporalComunidad.value;

  if (selectedComunidadPlatform.value === 'todas') {
    const totalPuntosAgrupados = agruparPuntosTimeline(props.historicoMediciones || [], modo, hitos, 'todas');
    let labels = totalPuntosAgrupados.map(m => m.fecha);

    const datasets = [];

    plataformasKeys.forEach(platKey => {
      const s = series[platKey];
      if (!s || !s.puntos || s.puntos.length === 0) return;

      const puntosAgrupados = agruparPuntosTimeline(s.puntos, modo, hitos, platKey);
      if (labels.length === 0) {
        labels = puntosAgrupados.map(m => m.fecha);
      }

      const meta = getSocialMeta(platKey);
      const colorHex = s.color || meta.color;

      const pointRadiuses = [];
      const pointHoverRadiuses = [];
      const pointBackgroundColors = [];
      const pointBorderColors = [];
      const pointBorderWidths = [];

      puntosAgrupados.forEach(p => {
        if (p.tieneBooster) {
          pointRadiuses.push(7);
          pointHoverRadiuses.push(9);
          pointBackgroundColors.push('#ef4444');
          pointBorderColors.push('#ffffff');
          pointBorderWidths.push(2);
        } else {
          pointRadiuses.push(modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 2.5));
          pointHoverRadiuses.push(modo === 'mes' ? 7 : (modo === 'semana' ? 6 : 4.5));
          pointBackgroundColors.push(colorHex);
          pointBorderColors.push('#ffffff');
          pointBorderWidths.push(1);
        }
      });

      datasets.push({
        label: s.nombre,
        data: puntosAgrupados.map(m => m.seguidores),
        borderColor: colorHex,
        backgroundColor: `${colorHex}15`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        pointRadius: pointRadiuses,
        pointHoverRadius: pointHoverRadiuses,
        pointBackgroundColor: pointBackgroundColors,
        pointBorderColor: pointBorderColors,
        pointBorderWidth: pointBorderWidths,
        plataforma: platKey,
        puntosAgrupados,
      });
    });

    if (totalPuntosAgrupados.length > 0) {
      const colorTotal = '#06b6d4';
      datasets.unshift({
        label: 'Total Multired (Suma)',
        data: totalPuntosAgrupados.map(m => m.seguidores),
        borderColor: colorTotal,
        backgroundColor: `${colorTotal}10`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        borderDash: [5, 4],
        pointRadius: modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 3),
        pointHoverRadius: 6,
        pointBackgroundColor: colorTotal,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        plataforma: 'todas',
        puntosAgrupados: totalPuntosAgrupados,
      });
    }

    return { labels, datasets };
  }

  const s = series[selectedComunidadPlatform.value];
  if (!s || !s.puntos || s.puntos.length === 0) {
    return { labels: [], datasets: [] };
  }

  const puntosAgrupados = agruparPuntosTimeline(s.puntos, modo, hitos, selectedComunidadPlatform.value);
  const meta = getSocialMeta(selectedComunidadPlatform.value);
  const colorHex = s.color || meta.color;
  const labels = puntosAgrupados.map(m => m.fecha);
  const data = puntosAgrupados.map(m => m.seguidores);

  const pointRadiuses = [];
  const pointHoverRadiuses = [];
  const pointBackgroundColors = [];
  const pointBorderColors = [];
  const pointBorderWidths = [];

  puntosAgrupados.forEach(p => {
    if (p.tieneBooster) {
      pointRadiuses.push(7);
      pointHoverRadiuses.push(9);
      pointBackgroundColors.push('#ef4444');
      pointBorderColors.push('#ffffff');
      pointBorderWidths.push(2);
    } else {
      pointRadiuses.push(modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 2.5));
      pointHoverRadiuses.push(modo === 'mes' ? 7 : (modo === 'semana' ? 6 : 4.5));
      pointBackgroundColors.push(colorHex);
      pointBorderColors.push('#ffffff');
      pointBorderWidths.push(1);
    }
  });

  return {
    labels,
    datasets: [{
      label: s.nombre,
      data,
      borderColor: colorHex,
      backgroundColor: `${colorHex}18`,
      fill: true,
      tension: 0.35,
      borderWidth: 2.5,
      pointRadius: pointRadiuses,
      pointHoverRadius: pointHoverRadiuses,
      pointBackgroundColor: pointBackgroundColors,
      pointBorderColor: pointBorderColors,
      pointBorderWidth: pointBorderWidths,
      plataforma: selectedComunidadPlatform.value,
      puntosAgrupados,
    }]
  };
});

const comunidadChartOptions = computed(() => {
  const isAll = selectedComunidadPlatform.value === 'todas';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: isAll,
        position: 'top',
        align: 'end',
        labels: {
          boxWidth: 8,
          boxHeight: 8,
          usePointStyle: true,
          color: '#94a3b8',
          padding: 8,
          font: { size: 10, family: 'monospace', weight: 'bold' }
        }
      },
      tooltip: {
        backgroundColor: '#0f172a',
        titleColor: '#06b6d4',
        bodyColor: '#f8fafc',
        padding: 10,
        cornerRadius: 8,
        borderColor: 'rgba(148, 163, 184, 0.2)',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')} seg`,
          afterLabel: (ctx) => {
            const dataset = ctx.dataset;
            const idx = ctx.dataIndex;
            const pto = dataset.puntosAgrupados ? dataset.puntosAgrupados[idx] : null;
            if (pto && pto.tieneBooster && pto.infoBooster) {
              const h = pto.infoBooster;
              return `🚀 BOOSTER ${h.plataforma.toUpperCase()}: ${h.monto_formateado}\n📌 ${h.titulo}`;
            }
            return '';
          }
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 14 }
      },
      y: {
        grid: { color: 'rgba(148, 163, 184, 0.1)' },
        ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, callback: (v) => formatNumber(v) }
      }
    }
  };
});

// ─────────────────────────────────────────────────────────────────────────────
// 2. GRÁFICA TEMPORAL: TRACCIÓN / SCORE DE IMPACTO PONDERADO (PUNTOS 🔥)
// ─────────────────────────────────────────────────────────────────────────────
const selectedScorePlatform = ref('todas');

const scoreChartData = computed(() => {
  const series = props.seriesPorRed || {};
  const plataformasKeys = Object.keys(series);
  const modo = agrupacionTemporalScore.value;

  if (selectedScorePlatform.value === 'todas') {
    const totalPuntosAgrupados = agruparPuntosTimeline(props.historicoMediciones || [], modo, [], 'todas');
    let labels = totalPuntosAgrupados.map(m => m.fecha);

    const datasets = [];

    plataformasKeys.forEach(platKey => {
      const s = series[platKey];
      if (!s || !s.puntos || s.puntos.length === 0) return;

      const puntosAgrupados = agruparPuntosTimeline(s.puntos, modo, [], platKey);
      if (labels.length === 0) {
        labels = puntosAgrupados.map(m => m.fecha);
      }

      const meta = getSocialMeta(platKey);
      const colorHex = s.color || meta.color;

      datasets.push({
        label: s.nombre,
        data: puntosAgrupados.map(m => m.puntos || 0),
        borderColor: colorHex,
        backgroundColor: `${colorHex}15`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        pointRadius: modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 2.5),
        pointHoverRadius: 6,
        pointBackgroundColor: colorHex,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1,
        plataforma: platKey,
        puntosAgrupados,
      });
    });

    if (totalPuntosAgrupados.length > 0) {
      const colorTotal = '#f59e0b';
      datasets.unshift({
        label: 'Total Score (Suma)',
        data: totalPuntosAgrupados.map(m => m.puntos || 0),
        borderColor: colorTotal,
        backgroundColor: `${colorTotal}10`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        borderDash: [5, 4],
        pointRadius: modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 3),
        pointHoverRadius: 6,
        pointBackgroundColor: colorTotal,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        plataforma: 'todas',
        puntosAgrupados: totalPuntosAgrupados,
      });
    }

    return { labels, datasets };
  }

  const s = series[selectedScorePlatform.value];
  if (!s || !s.puntos || s.puntos.length === 0) {
    return { labels: [], datasets: [] };
  }

  const puntosAgrupados = agruparPuntosTimeline(s.puntos, modo, [], selectedScorePlatform.value);
  const meta = getSocialMeta(selectedScorePlatform.value);
  const colorHex = s.color || meta.color;
  const labels = puntosAgrupados.map(m => m.fecha);
  const data = puntosAgrupados.map(m => m.puntos || 0);

  return {
    labels,
    datasets: [{
      label: s.nombre,
      data,
      borderColor: colorHex,
      backgroundColor: `${colorHex}18`,
      fill: true,
      tension: 0.35,
      borderWidth: 2.5,
      pointRadius: modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 2.5),
      pointHoverRadius: 6,
      pointBackgroundColor: colorHex,
      pointBorderColor: '#ffffff',
      pointBorderWidth: 1,
      plataforma: selectedScorePlatform.value,
      puntosAgrupados,
    }]
  };
});

const scoreChartOptions = computed(() => {
  const isAll = selectedScorePlatform.value === 'todas';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: isAll,
        position: 'top',
        align: 'end',
        labels: {
          boxWidth: 8,
          boxHeight: 8,
          usePointStyle: true,
          color: '#94a3b8',
          padding: 8,
          font: { size: 10, family: 'monospace', weight: 'bold' }
        }
      },
      tooltip: {
        backgroundColor: '#0f172a',
        titleColor: '#f59e0b',
        bodyColor: '#f8fafc',
        padding: 10,
        cornerRadius: 8,
        borderColor: 'rgba(245, 158, 11, 0.3)',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')} pts`
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 14 } },
      y: { grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, callback: (v) => formatNumber(v) } }
    }
  };
});

// ─────────────────────────────────────────────────────────────────────────────
// 2.B. GRÁFICA Y COMPARATIVA MENSUAL DE SCORE
// ─────────────────────────────────────────────────────────────────────────────
const listaDesgloseMensual = computed(() => {
  if (props.stats?.desglose_mensual && props.stats.desglose_mensual.length > 0) {
    return props.stats.desglose_mensual;
  }
  return [];
});

const scoreMensualChartData = computed(() => {
  const meses = listaDesgloseMensual.value;
  const labels = meses.map(m => m.mes_corto || m.nombre_mes);
  const dataPromedios = meses.map(m => m.score_promedio_post || 0);
  const dataPosts = meses.map(m => m.total_posts || 0);

  return {
    labels,
    datasets: [
      {
        type: 'bar',
        label: 'Score Promedio por Post (pts/post)',
        data: dataPromedios,
        backgroundColor: '#8b5cf6',
        borderRadius: 8,
        barThickness: 32,
        yAxisID: 'y',
      },
      {
        type: 'line',
        label: 'Publicaciones Realizadas',
        data: dataPosts,
        borderColor: '#06b6d4',
        backgroundColor: 'rgba(6, 182, 212, 0.15)',
        borderWidth: 2.5,
        pointRadius: 4,
        pointBackgroundColor: '#06b6d4',
        tension: 0.3,
        yAxisID: 'y1',
      }
    ]
  };
});

const scoreMensualChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        color: '#94a3b8',
        font: { size: 11, weight: 'bold' },
        usePointStyle: true,
      }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      titleColor: '#8b5cf6',
      bodyColor: '#f8fafc',
      padding: 12,
      cornerRadius: 10,
      borderColor: 'rgba(139, 92, 246, 0.3)',
      borderWidth: 1,
      callbacks: {
        label: (ctx) => {
          if (ctx.dataset.type === 'line') {
            return `Publicaciones: ${ctx.raw} posts`;
          }
          return `Score Promedio: ${Number(ctx.raw).toLocaleString('es-AR')} pts/post`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, family: 'monospace', weight: 'bold' } }
    },
    y: {
      position: 'left',
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: { color: '#8b5cf6', font: { size: 10, family: 'monospace' }, callback: (v) => `${formatNumber(v)} pts` },
      title: { display: true, text: 'Score Promedio (pts/post)', color: '#8b5cf6', font: { size: 10 } }
    },
    y1: {
      position: 'right',
      grid: { display: false },
      ticks: { color: '#06b6d4', font: { size: 10, family: 'monospace' }, stepSize: 1 },
      title: { display: true, text: 'Cant. Posts', color: '#06b6d4', font: { size: 10 } }
    }
  }
}));

// ─────────────────────────────────────────────────────────────────────────────
// 3. GRÁFICA TEMPORAL: REPRODUCCIONES (VISUALIZACIONES / REELS 👁️)
// ─────────────────────────────────────────────────────────────────────────────
const selectedVistasPlatform = ref('todas');

const vistasChartData = computed(() => {
  const series = props.seriesPorRed || {};
  const plataformasConVistas = redesConVistasDisponibles.value.map(r => r.plataforma);
  const modo = agrupacionTemporalVistas.value;

  if (selectedVistasPlatform.value === 'todas') {
    const totalPuntosAgrupados = agruparPuntosTimeline(props.historicoMediciones || [], modo, [], 'todas');
    let labels = totalPuntosAgrupados.map(m => m.fecha);

    const datasets = [];

    plataformasConVistas.forEach(platKey => {
      const s = series[platKey];
      if (!s || !s.puntos || s.puntos.length === 0) return;

      const puntosAgrupados = agruparPuntosTimeline(s.puntos, modo, [], platKey);
      if (labels.length === 0) {
        labels = puntosAgrupados.map(m => m.fecha);
      }

      const meta = getSocialMeta(platKey);
      const colorHex = s.color || meta.color;

      datasets.push({
        label: s.nombre,
        data: puntosAgrupados.map(m => m.vistas || 0),
        borderColor: colorHex,
        backgroundColor: `${colorHex}15`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        pointRadius: modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 2.5),
        pointHoverRadius: 6,
        pointBackgroundColor: colorHex,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1,
        plataforma: platKey,
        puntosAgrupados,
      });
    });

    if (totalPuntosAgrupados.length > 0) {
      const colorTotal = '#10b981';
      datasets.unshift({
        label: 'Total Vistas (Suma)',
        data: totalPuntosAgrupados.map(m => m.vistas || 0),
        borderColor: colorTotal,
        backgroundColor: `${colorTotal}10`,
        fill: false,
        tension: 0.35,
        borderWidth: 2.5,
        borderDash: [5, 4],
        pointRadius: modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 3),
        pointHoverRadius: 6,
        pointBackgroundColor: colorTotal,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 1.5,
        plataforma: 'todas',
        puntosAgrupados: totalPuntosAgrupados,
      });
    }

    return { labels, datasets };
  }

  const s = series[selectedVistasPlatform.value];
  if (!s || !s.puntos || s.puntos.length === 0) {
    return { labels: [], datasets: [] };
  }

  const puntosAgrupados = agruparPuntosTimeline(s.puntos, modo, [], selectedVistasPlatform.value);
  const meta = getSocialMeta(selectedVistasPlatform.value);
  const colorHex = s.color || meta.color;
  const labels = puntosAgrupados.map(m => m.fecha);
  const data = puntosAgrupados.map(m => m.vistas || 0);

  return {
    labels,
    datasets: [{
      label: s.nombre,
      data,
      borderColor: colorHex,
      backgroundColor: `${colorHex}18`,
      fill: true,
      tension: 0.35,
      borderWidth: 2.5,
      pointRadius: modo === 'mes' ? 5 : (modo === 'semana' ? 4 : 2.5),
      pointHoverRadius: 6,
      pointBackgroundColor: colorHex,
      pointBorderColor: '#ffffff',
      pointBorderWidth: 1,
      plataforma: selectedVistasPlatform.value,
      puntosAgrupados,
    }]
  };
});

const vistasChartOptions = computed(() => {
  const isAll = selectedVistasPlatform.value === 'todas';

  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: isAll,
        position: 'top',
        align: 'end',
        labels: {
          boxWidth: 8,
          boxHeight: 8,
          usePointStyle: true,
          color: '#94a3b8',
          padding: 8,
          font: { size: 10, family: 'monospace', weight: 'bold' }
        }
      },
      tooltip: {
        backgroundColor: '#0f172a',
        titleColor: '#10b981',
        bodyColor: '#f8fafc',
        padding: 10,
        cornerRadius: 8,
        borderColor: 'rgba(16, 185, 129, 0.3)',
        borderWidth: 1,
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')} vistas`
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 14 } },
      y: { grid: { color: 'rgba(148, 163, 184, 0.1)' }, ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, callback: (v) => formatNumber(v) } }
    }
  };
});

// ─────────────────────────────────────────────────────────────────────────────
// 4. DONUT DE PARTICIPACIÓN POR RED SOCIAL
// ─────────────────────────────────────────────────────────────────────────────
const doughnutChartData = computed(() => {
  const labels = props.distribucionPlataformas.map(p => p.nombre);
  const data = props.distribucionPlataformas.map(p => p.interacciones);
  const colors = props.distribucionPlataformas.map(p => {
    if (p.plataforma === 'threads' || p.plataforma === 'x_twitter' || p.plataforma === 'twitter') {
      return '#94a3b8';
    }
    return p.color;
  });

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: colors.length ? colors : ['#06b6d4', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444'],
        borderWidth: 2,
        borderColor: '#0f172a',
      }
    ]
  };
});

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        boxWidth: 10,
        padding: 12,
        color: '#94a3b8',
        font: { size: 11 }
      }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: function(context) {
          const total = context.dataset.data.reduce((a, b) => a + b, 0);
          const val = context.raw || 0;
          const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
          return ` ${context.label}: ${Number(val).toLocaleString('es-AR')} interacciones (${pct}%)`;
        }
      }
    }
  },
  cutout: '68%'
};

// ─────────────────────────────────────────────────────────────────────────────
// 5. BARRAS: RENDIMIENTO POR FORMATO
// ─────────────────────────────────────────────────────────────────────────────
const formatBarChartData = computed(() => {
  const labels = props.rendimientoPorFormato.map(f => f.formato);
  const dataVistas = props.rendimientoPorFormato.map(f => f.promedio_vistas);
  const dataInt = props.rendimientoPorFormato.map(f => f.promedio_interacciones);

  return {
    labels,
    datasets: [
      {
        label: 'Promedio Interacciones / Post',
        data: dataInt,
        backgroundColor: '#8b5cf6',
        borderRadius: 8,
      },
      {
        label: 'Promedio Vistas / Post',
        data: dataVistas,
        backgroundColor: '#06b6d4',
        borderRadius: 8,
      }
    ]
  };
});

const formatBarChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { boxWidth: 10, padding: 10, color: '#94a3b8', font: { size: 10 } }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ` ${ctx.dataset.label}: ${Number(ctx.raw).toLocaleString('es-AR')}`
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 10 } }
    },
    y: {
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        callback: (value) => formatNumber(value)
      }
    }
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// 6. MATRIZ DE FORMATOS POR RED SOCIAL ACTIVA
// ─────────────────────────────────────────────────────────────────────────────
const formatColorsMap = {
  'Reel': '#f43f5e',
  'Foto': '#06b6d4',
  'Video': '#8b5cf6',
  'Carrusel': '#f59e0b',
  'Texto': '#10b981',
  'Historia': '#ec4899',
  'Enlace': '#3b82f6',
};

const matrizFormatosBarChartData = computed(() => {
  const redes = (props.formatosPorRed || []).filter(r => r.total_posts > 0);
  const labels = redes.map(r => r.plataforma.toUpperCase());

  const formatosSet = new Set();
  redes.forEach(r => {
    (r.formatos || []).forEach(f => formatosSet.add(f.formato));
  });
  const formatosUnicos = Array.from(formatosSet);

  const datasets = formatosUnicos.map(fmt => {
    const color = formatColorsMap[fmt] || '#64748b';
    return {
      label: fmt,
      data: redes.map(r => {
        const item = (r.formatos || []).find(f => f.formato === fmt);
        return item ? item.cantidad : 0;
      }),
      backgroundColor: color,
      borderRadius: 6,
    };
  });

  return { labels, datasets };
});

const matrizFormatosBarChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
      labels: { boxWidth: 10, padding: 8, color: '#94a3b8', font: { size: 10 } }
    },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw} publicaciones`
      }
    }
  },
  scales: {
    x: {
      stacked: true,
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 10, weight: 'bold' } }
    },
    y: {
      stacked: true,
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: { color: '#94a3b8', font: { size: 10, family: 'monospace' }, precision: 0 }
    }
  }
};

// ─────────────────────────────────────────────────────────────────────────────
// 7. EJES TEMÁTICOS (TRACCIÓN, IMPACTO Y VOLUMEN)
// ─────────────────────────────────────────────────────────────────────────────
const ejesTraccionBarChartData = computed(() => {
  const sorted = [...props.distribucionEjes].sort((a, b) => (b.score_traccion_promedio || 0) - (a.score_traccion_promedio || 0));
  const labels = sorted.map(e => e.nombre);
  const data = sorted.map(e => e.score_traccion_promedio || 50);
  const colors = sorted.map(e => {
    const tr = e.score_traccion_promedio || 50;
    if (tr >= 75) return '#f59e0b';
    if (tr >= 60) return '#10b981';
    if (tr >= 40) return '#06b6d4';
    return '#64748b';
  });

  return {
    labels,
    datasets: [{
      label: 'Tracción Electoral Promedio (/100)',
      data,
      backgroundColor: colors,
      borderRadius: 6,
    }]
  };
});

const ejesTraccionBarChartOptions = computed(() => ({
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 12,
      cornerRadius: 10,
      borderColor: 'rgba(245, 158, 11, 0.3)',
      borderWidth: 1,
      callbacks: {
        label: (ctx) => {
          const sorted = [...props.distribucionEjes].sort((a, b) => (b.score_traccion_promedio || 0) - (a.score_traccion_promedio || 0));
          const eje = sorted[ctx.dataIndex];
          const val = ctx.raw;
          const cualidad = val >= 75 ? 'Sobresaliente / Viral' : (val >= 60 ? 'Sólida / Favorable' : (val >= 40 ? 'Estándar' : 'Bajo Rendimiento'));
          return ` Tracción Promedio: ${val}/100 (${cualidad})`;
        },
        afterLabel: (ctx) => {
          const sorted = [...props.distribucionEjes].sort((a, b) => (b.score_traccion_promedio || 0) - (a.score_traccion_promedio || 0));
          const eje = sorted[ctx.dataIndex];
          if (!eje) return '';
          return ` Publicaciones: ${eje.posts_count || 0} | Impacto Total: ${Number(eje.score_impacto_ponderado || 0).toLocaleString('es-AR')} pts`;
        }
      }
    }
  },
  scales: {
    x: {
      min: 0,
      max: 100,
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        callback: (value) => `${value}/100`
      }
    },
    y: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
    }
  }
}));

const ejesBarChartData = computed(() => {
  const labels = props.distribucionEjes.map(e => e.nombre);
  const data = props.distribucionEjes.map(e => e.score_impacto_ponderado ?? e.total_interacciones ?? 0);
  const colors = props.distribucionEjes.map(e => e.color_badge || '#06b6d4');

  return {
    labels,
    datasets: [{
      label: 'Score de Impacto (Puntos Ponderados)',
      data,
      backgroundColor: colors,
      borderRadius: 6,
    }]
  };
});

const ejesBarChartOptions = computed(() => ({
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      bodyColor: '#f8fafc',
      padding: 12,
      cornerRadius: 10,
      borderColor: 'rgba(251, 191, 36, 0.3)',
      borderWidth: 1,
      callbacks: {
        label: (ctx) => {
          const eje = props.distribucionEjes[ctx.dataIndex];
          const val = Number(ctx.raw).toLocaleString('es-AR');
          return ` Impacto: ${val} pts (${eje?.posts_count || 0} publicaciones)`;
        },
        afterLabel: (ctx) => {
          const eje = props.distribucionEjes[ctx.dataIndex];
          if (!eje) return '';
          return ` Tracción: ${eje.score_traccion_promedio || 50}/100 | Interacciones brutas: ${Number(eje.total_interacciones || 0).toLocaleString('es-AR')}`;
        }
      }
    }
  },
  scales: {
    x: {
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        callback: (value) => `${formatNumber(value)} pts`
      }
    },
    y: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
    }
  }
}));

const ejesVolumenBarChartData = computed(() => {
  const labels = props.distribucionEjes.map(e => e.nombre);
  const data = props.distribucionEjes.map(e => e.posts_count);
  const colors = props.distribucionEjes.map(e => e.color_badge || '#3b82f6');

  return {
    labels,
    datasets: [{
      label: 'Publicaciones Emitidas',
      data,
      backgroundColor: colors,
      borderRadius: 6,
    }]
  };
});

const ejesVolumenBarChartOptions = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#0f172a',
      titleColor: '#38bdf8',
      bodyColor: '#f8fafc',
      padding: 10,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ` Publicaciones: ${ctx.raw} posts`
      }
    }
  },
  scales: {
    x: {
      grid: { color: 'rgba(148, 163, 184, 0.1)' },
      ticks: {
        color: '#94a3b8',
        font: { size: 10, family: 'monospace' },
        precision: 0,
      }
    },
    y: {
      grid: { display: false },
      ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
    }
  }
};

  return {
    redesDisponiblesTimeline,
    redesConVistasDisponibles,
    selectedComunidadPlatform,
    agrupacionTemporalComunidad,
    selectedScorePlatform,
    agrupacionTemporalScore,
    selectedVistasPlatform,
    agrupacionTemporalVistas,
    comunidadChartData,
    comunidadChartOptions,
    scoreChartData,
    scoreChartOptions,
    scoreMensualChartData,
    scoreMensualChartOptions,
    listaDesgloseMensual,
    vistasChartData,
    vistasChartOptions,
    doughnutChartData,
    doughnutChartOptions,
    formatBarChartData,
    formatBarChartOptions,
    matrizFormatosBarChartData,
    matrizFormatosBarChartOptions,
    ejesTraccionBarChartData,
    ejesTraccionBarChartOptions,
    ejesBarChartData,
    ejesBarChartOptions,
    ejesVolumenBarChartData,
    ejesVolumenBarChartOptions,
  };
}
