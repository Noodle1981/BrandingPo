/**
 * Composable para formateo de números, monedas y fechas
 * Estandarizado para BrandingPo según GEMINI.md y vue-best-practices
 */

export function useFormatters() {
  /**
   * Formatea números en formato compacto (1.2K, 3.5M) o con separadores de miles
   */
  const formatNumber = (num, compact = true) => {
    if (num === null || num === undefined || isNaN(Number(num))) return '0';
    const n = Number(num);

    if (compact) {
      if (Math.abs(n) >= 1_000_000) {
        return (n / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
      }
      if (Math.abs(n) >= 1_000) {
        return (n / 1_000).toFixed(1).replace(/\.0$/, '') + 'K';
      }
    }

    return new Intl.NumberFormat('es-AR').format(n);
  };

  /**
   * Formatea moneda (por defecto ARS, sin decimales para lectura ejecutiva)
   */
  const formatCurrency = (amount, currency = 'ARS') => {
    if (amount === null || amount === undefined || isNaN(Number(amount))) return '$0';
    return new Intl.NumberFormat('es-AR', {
      style: 'currency',
      currency,
      maximumFractionDigits: 0,
    }).format(Number(amount));
  };

  /**
   * Formatea porcentajes con decimales opcionales
   */
  const formatPercent = (val, decimals = 1) => {
    if (val === null || val === undefined || isNaN(Number(val))) return '0%';
    return `${Number(val).toFixed(decimals).replace(/\.0$/, '')}%`;
  };

  /**
   * Formateo de fecha legible en español
   */
  const formatDate = (dateString, options = {}) => {
    if (!dateString) return '—';
    try {
      const d = new Date(dateString);
      if (isNaN(d.getTime())) return String(dateString);
      return new Intl.DateTimeFormat('es-AR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        ...options,
      }).format(d);
    } catch {
      return String(dateString);
    }
  };

  return {
    formatNumber,
    formatCurrency,
    formatPercent,
    formatDate,
  };
}
