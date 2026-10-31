import { computed } from 'vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
} from 'chart.js';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  ArcElement,
  LinearScale,
  PointElement,
  CategoryScale,
  Filler
);

/**
 * Composable para encapsular la lógica y datasets de Chart.js en la vista de Métricas de Canal.
 * Soporta tema dual (Dark War Room vs Executive Light).
 */
export function useMetricasCharts(props) {
  // 1. AUDIENCIA & SEGUIDORES (Time-Series)
  const audienciaChartData = computed(() => {
    const data = props.historicoMediciones || [];
    const labels = data.map(m => m.fecha_corta || m.fecha);
    const seguidoresData = data.map(m => m.seguidores);
    const crecimientoData = data.map(m => m.crecimiento_neto_seguidores);

    return {
      labels,
      datasets: [
        {
          label: 'Seguidores Totales',
          data: seguidoresData,
          borderColor: '#06b6d4',
          backgroundColor: 'rgba(6, 182, 212, 0.12)',
          fill: true,
          tension: 0.35,
          borderWidth: 3,
          pointBackgroundColor: '#06b6d4',
          pointBorderColor: '#ffffff',
          pointRadius: 4,
          pointHoverRadius: 6,
          yAxisID: 'y',
        },
        {
          label: 'Crecimiento Neto (+/-)',
          data: crecimientoData,
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.08)',
          fill: false,
          borderDash: [5, 5],
          tension: 0.3,
          borderWidth: 2,
          pointBackgroundColor: '#10b981',
          pointBorderColor: '#ffffff',
          pointRadius: 3,
          pointHoverRadius: 5,
          yAxisID: 'y1',
        }
      ]
    };
  });

  const audienciaChartOptions = computed(() => {
    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

    return {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          position: 'top',
          labels: { color: textColor, font: { family: 'ui-monospace, monospace', size: 12, weight: 'bold' }, usePointStyle: true, padding: 15 }
        },
        tooltip: {
          backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
          titleColor: isDark ? '#f8fafc' : '#0f172a',
          bodyColor: isDark ? '#cbd5e1' : '#334155',
          borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 12,
          callbacks: {
            label: (context) => `${context.dataset.label}: ${Number(context.parsed.y).toLocaleString('es-AR')}`
          }
        }
      },
      scales: {
        x: { grid: { color: gridColor, drawBorder: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
        y: {
          type: 'linear',
          display: true,
          position: 'left',
          grid: { color: gridColor, drawBorder: false },
          ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => Number(v).toLocaleString('es-AR') }
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          grid: { drawOnChartArea: false },
          ticks: { color: '#10b981', font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => (v >= 0 ? '+' : '') + Number(v).toLocaleString('es-AR') }
        }
      }
    };
  });

  // 2. INTERACCIONES & ENGAGEMENT MENSUAL
  const interaccionesChartData = computed(() => {
    const meses = props.consistenciaMensual || [];
    const labels = meses.map(m => m.mes_nombre);
    const interacciones = meses.map(m => m.total_interacciones);

    return {
      labels,
      datasets: [
        {
          label: 'Interacciones Totales (Likes + Coment + Comp)',
          data: interacciones,
          backgroundColor: '#06b6d4',
          borderRadius: 10,
          hoverBackgroundColor: '#22d3ee',
        }
      ]
    };
  });

  const interaccionesChartOptions = computed(() => {
    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
          titleColor: isDark ? '#f8fafc' : '#0f172a',
          bodyColor: isDark ? '#cbd5e1' : '#334155',
          borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 12,
          callbacks: {
            label: (context) => `🔥 Interacciones: ${Number(context.parsed.y).toLocaleString('es-AR')}`
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
        y: {
          grid: { color: gridColor, drawBorder: false },
          ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => Number(v).toLocaleString('es-AR') }
        }
      }
    };
  });

  // 3. CADENCIA & VOLUMEN DE PUBLICACIONES VS META
  const cadenciaChartData = computed(() => {
    const meses = props.consistenciaMensual || [];
    const labels = meses.map(m => m.mes_nombre);
    const postsCount = meses.map(m => m.posts_count);
    const metas = meses.map(m => m.meta_mensual || 16);

    return {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Posts Publicados',
          data: postsCount,
          backgroundColor: postsCount.map(c => (c >= 16 ? '#10b981' : (c >= 12 ? '#f59e0b' : (c === 0 ? '#ef4444' : '#64748b')))),
          borderRadius: 10,
          order: 2,
        },
        {
          type: 'line',
          label: 'Meta de Campaña (≥ 16 posts)',
          data: metas,
          borderColor: '#f59e0b',
          borderWidth: 2,
          borderDash: [6, 6],
          pointRadius: 0,
          fill: false,
          order: 1,
        }
      ]
    };
  });

  const cadenciaChartOptions = computed(() => {
    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top',
          labels: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11, weight: 'bold' }, usePointStyle: true }
        },
        tooltip: {
          backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
          titleColor: isDark ? '#f8fafc' : '#0f172a',
          bodyColor: isDark ? '#cbd5e1' : '#334155',
          borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 12,
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
        y: {
          grid: { color: gridColor, drawBorder: false },
          ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, stepSize: 4 }
        }
      }
    };
  });

  // 4. FORMATOS & MIX DE CONTENIDO (Doughnut)
  const formatosChartData = computed(() => {
    const formatos = props.rendimientoPorFormato || [];
    const labels = formatos.map(f => f.tipo_formato || f.formato || 'Otro');
    const data = formatos.map(f => Number(f.cantidad_posts ?? f.cantidad ?? 0));
    const colors = ['#06b6d4', '#ec4899', '#8b5cf6', '#f59e0b', '#10b981', '#64748b', '#3b82f6'];

    return {
      labels,
      datasets: [
        {
          data,
          backgroundColor: colors.slice(0, labels.length),
          borderWidth: 2,
          borderColor: typeof document !== 'undefined' && document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
          hoverOffset: 8,
        }
      ]
    };
  });

  const formatosChartOptions = computed(() => {
    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';

    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { color: textColor, font: { family: 'ui-monospace, monospace', size: 12, weight: 'bold' }, padding: 15, usePointStyle: true }
        },
        tooltip: {
          backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
          titleColor: isDark ? '#f8fafc' : '#0f172a',
          bodyColor: isDark ? '#cbd5e1' : '#334155',
          borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 12,
          callbacks: {
            label: (context) => ` ${context.label}: ${context.parsed} posts`
          }
        }
      }
    };
  });

  // 5. PAUTA VS ORGÁNICO & INVERSIÓN MENSUAL
  const pautaChartData = computed(() => {
    const meses = props.consistenciaMensual || [];
    const labels = meses.map(m => m.mes_nombre);
    const pautaInvertida = meses.map(m => m.total_pauta || 0);

    return {
      labels,
      datasets: [
        {
          label: 'Inversión en Pauta ($)',
          data: pautaInvertida,
          backgroundColor: '#8b5cf6',
          borderRadius: 10,
          hoverBackgroundColor: '#a78bfa',
        }
      ]
    };
  });

  const pautaChartOptions = computed(() => {
    const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.35)' : 'rgba(226, 232, 240, 0.8)';

    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)',
          titleColor: isDark ? '#f8fafc' : '#0f172a',
          bodyColor: isDark ? '#cbd5e1' : '#334155',
          borderColor: isDark ? 'rgba(51, 65, 85, 0.6)' : 'rgba(203, 213, 225, 0.8)',
          borderWidth: 1,
          padding: 12,
          cornerRadius: 12,
          callbacks: {
            label: (context) => ` Inversión: $${Number(context.parsed.y).toLocaleString('es-AR')}`
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 } } },
        y: {
          grid: { color: gridColor, drawBorder: false },
          ticks: { color: textColor, font: { family: 'ui-monospace, monospace', size: 11 }, callback: (v) => '$' + Number(v).toLocaleString('es-AR') }
        }
      }
    };
  });

  return {
    audienciaChartData,
    audienciaChartOptions,
    interaccionesChartData,
    interaccionesChartOptions,
    cadenciaChartData,
    cadenciaChartOptions,
    formatosChartData,
    formatosChartOptions,
    pautaChartData,
    pautaChartOptions,
  };
}
