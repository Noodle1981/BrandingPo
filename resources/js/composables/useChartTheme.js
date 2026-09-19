/**
 * Composable para configuración de estilos y temas de Chart.js
 * Compatible con Modo Oscuro (War Room) y Modo Claro (Executive Light)
 */

import { computed } from 'vue';

export function useChartTheme() {
  const isDark = computed(() => {
    if (typeof document === 'undefined') return true;
    return document.documentElement.classList.contains('dark');
  });

  const textColor = computed(() => (isDark.value ? '#94a3b8' : '#64748b'));
  const gridColor = computed(() => (isDark.value ? 'rgba(148, 163, 184, 0.1)' : 'rgba(100, 116, 139, 0.1)'));
  const tooltipBg = computed(() => (isDark.value ? '#0f172a' : '#1e293b'));

  /**
   * Genera opciones estándar de escalas (X e Y) para gráficos cartesianos (Line / Bar)
   */
  const getCartesianScales = (formatYCallback = null) => ({
    x: {
      grid: { display: false },
      ticks: {
        color: textColor.value,
        font: { size: 10, family: 'monospace' },
      },
    },
    y: {
      grid: { color: gridColor.value },
      ticks: {
        color: textColor.value,
        font: { size: 10, family: 'monospace' },
        callback: formatYCallback || ((v) => v),
      },
    },
  });

  /**
   * Tooltip estándar elegante
   */
  const getTooltipOptions = (callbacks = {}) => ({
    backgroundColor: tooltipBg.value,
    titleColor: '#f8fafc',
    bodyColor: '#e2e8f0',
    borderColor: 'rgba(148, 163, 184, 0.2)',
    borderWidth: 1,
    padding: 10,
    cornerRadius: 8,
    callbacks,
  });

  return {
    isDark,
    textColor,
    gridColor,
    tooltipBg,
    getCartesianScales,
    getTooltipOptions,
  };
}
