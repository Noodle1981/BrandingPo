/**
 * Composable para métricas de redes sociales, pesos de interacción y metadatos de plataformas
 * Alineado con GEMINI.md (Sección 1.A y 1.B) y vue-best-practices
 */

export function useSocialMetrics() {
  /**
   * Normaliza el identificador de plataforma
   */
  const normalizePlatform = (platform) => {
    const p = (platform || '').toLowerCase().trim();
    if (p.includes('insta')) return 'instagram';
    if (p.includes('face')) return 'facebook';
    if (p.includes('thread')) return 'threads';
    if (p.includes('tik')) return 'tiktok';
    if (p.includes('you') || p.includes('yt')) return 'youtube';
    if (p.includes('twit') || p.includes('x_') || p === 'x') return 'x_twitter';
    if (p.includes('link')) return 'linkedin';
    return 'default';
  };

  /**
   * Obtiene colores y etiquetas oficiales por plataforma
   */
  const getSocialMeta = (platform) => {
    const key = normalizePlatform(platform);
    switch (key) {
      case 'instagram':
        return {
          key: 'instagram',
          name: 'Instagram',
          color: '#E4405F',
          colorText: 'text-[#E4405F]',
          bgLight: 'bg-[#E4405F]/15',
          border: 'border-[#E4405F]/30',
        };
      case 'facebook':
        return {
          key: 'facebook',
          name: 'Facebook',
          color: '#1877F2',
          colorText: 'text-[#1877F2]',
          bgLight: 'bg-[#1877F2]/15',
          border: 'border-[#1877F2]/30',
        };
      case 'threads':
        return {
          key: 'threads',
          name: 'Threads',
          color: '#000000',
          colorText: 'text-slate-800 dark:text-slate-200',
          bgLight: 'bg-slate-900/15',
          border: 'border-slate-300 dark:border-slate-700',
        };
      case 'tiktok':
        return {
          key: 'tiktok',
          name: 'TikTok',
          color: '#00F2FE',
          colorText: 'text-[#00F2FE]',
          bgLight: 'bg-[#00F2FE]/15',
          border: 'border-[#00F2FE]/30',
        };
      case 'youtube':
        return {
          key: 'youtube',
          name: 'YouTube',
          color: '#FF0000',
          colorText: 'text-[#FF0000]',
          bgLight: 'bg-[#FF0000]/15',
          border: 'border-[#FF0000]/30',
        };
      case 'x_twitter':
        return {
          key: 'x_twitter',
          name: 'X',
          color: '#000000',
          colorText: 'text-slate-900 dark:text-slate-100',
          bgLight: 'bg-slate-500/15',
          border: 'border-slate-400/30',
        };
      case 'linkedin':
        return {
          key: 'linkedin',
          name: 'LinkedIn',
          color: '#0A66C2',
          colorText: 'text-[#0A66C2]',
          bgLight: 'bg-[#0A66C2]/15',
          border: 'border-[#0A66C2]/30',
        };
      default:
        return {
          key: 'default',
          name: 'Red Social',
          color: '#06b6d4',
          colorText: 'text-cyan-500',
          bgLight: 'bg-cyan-500/15',
          border: 'border-cyan-500/30',
        };
    }
  };

  /**
   * Semáforo de estado de canal (Regla 1.B.3 de GEMINI.md)
   * Azul: Verificada | Verde/Naranja: Activa | Rojo: Inactiva
   */
  const tabBadgeStyle = (colorEstado) => {
    switch (colorEstado) {
      case 'azul':
        return {
          tab: 'border-blue-500 bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold ring-2 ring-blue-500/30',
          pill: 'bg-blue-500 text-white font-bold',
          label: 'Verificada',
        };
      case 'verde':
      case 'naranja':
        return {
          tab: 'border-emerald-500 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold ring-2 ring-emerald-500/30',
          pill: 'bg-emerald-500 text-white font-bold',
          label: 'Activa',
        };
      case 'rojo':
        return {
          tab: 'border-rose-500 bg-rose-500/10 text-rose-600 dark:text-rose-400 font-semibold ring-2 ring-rose-500/30',
          pill: 'bg-rose-500 text-white font-bold',
          label: 'Inactiva',
        };
      case 'gris':
      default:
        return {
          tab: 'border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-400 opacity-75 hover:opacity-100 hover:border-slate-400 hover:bg-white dark:hover:bg-slate-900 border-dashed',
          pill: 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium',
          label: 'Configurar',
        };
    }
  };

  /**
   * Cálculo del VTP Ponderado (Valor Total Ponderado de Interacción)
   * Likes: 1pt | Comentarios: 3pts | Compartidos: 5pts | Reposts: 10pts
   */
  const calcularVtpPonderado = (post = {}) => {
    const likes = Number(post.total_likes || post.me_gusta || 0);
    const comments = Number(post.total_comentarios || 0);
    const shares = Number(post.total_compartidos || 0);
    const reposts = Number(post.total_republicados || 0);

    return (likes * 1) + (comments * 3) + (shares * 5) + (reposts * 10);
  };

  return {
    normalizePlatform,
    getSocialMeta,
    tabBadgeStyle,
    calcularVtpPonderado,
  };
}
