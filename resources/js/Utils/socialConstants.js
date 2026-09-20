/**
 * Constantes y utilidades compartidas para redes sociales, formatos, pauta y semáforos.
 * BrandingPo War Room Design System.
 */

export const PLATFORM_FORMATS = {
  instagram: [
    { value: 'Reel', label: '🎬 Reel / Video Vertical (9:16)', desc: 'Algoritmo viral de Instagram Reels', default: true },
    { value: 'Foto', label: '🖼️ Foto / Post Simple (1:1 o 4:5)', desc: 'Publicación en cuadrícula del feed' },
    { value: 'Carrusel', label: '📚 Carrusel / Galería Deslizable', desc: 'Múltiples fotos o videos secuenciales (hasta 10)' },
    { value: 'Story', label: '⚡ Story / Historia (24 Horas)', desc: 'Contenido efímero de alta interacción' },
    { value: 'Collab', label: '🤝 Collab / Co-autoría', desc: 'Publicación compartida con otra cuenta o figura' },
    { value: 'Live', label: '🎙️ Instagram Live (En Vivo)', desc: 'Transmisión directa con la comunidad' },
  ],
  facebook: [
    { value: 'Post', label: '📄 Post / Publicación con Texto', desc: 'Actualización en muro de Facebook' },
    { value: 'Foto', label: '🖼️ Foto / Imagen en Feed', desc: 'Foto única o álbum' },
    { value: 'Video', label: '📹 Video en Feed', desc: 'Video horizontal o estándar' },
    { value: 'Reel', label: '🎬 Facebook Reel', desc: 'Video vertical en feed de Reels' },
    { value: 'Live', label: '🎙️ Facebook Live', desc: 'Streaming en directo' },
  ],
  tiktok: [
    { value: 'Video', label: '🎬 Video TikTok (Vertical)', desc: 'Video con audio y efectos' },
    { value: 'Foto', label: '🖼️ Modo Foto / Carrusel Musical', desc: 'Fotos deslizables con música' },
    { value: 'Live', label: '🎙️ TikTok Live', desc: 'En vivo interactivo' },
  ],
  threads: [
    { value: 'Post', label: '🧵 Post de Texto / Hilo', desc: 'Conversación ágil en Threads', default: true },
    { value: 'Foto', label: '🖼️ Post con Foto / Imagen', desc: 'Imagen en hilo de discusión' },
    { value: 'Video', label: '📹 Video Corto en Feed', desc: 'Audiovisual de debate' },
  ],
  x_twitter: [
    { value: 'Tweet', label: '🐦 Tweet / Post', desc: 'Post corto o comunicado' },
    { value: 'Hilo', label: '🧵 Hilo (Thread)', desc: 'Serie encadenada de tweets' },
    { value: 'Video', label: '📹 Video / Clip', desc: 'Audiovisual en el timeline' },
    { value: 'Foto', label: '🖼️ Foto / Infografía', desc: 'Imagen adjunta' },
  ],
  youtube: [
    { value: 'Shorts', label: '⚡ YouTube Short', desc: 'Video vertical menor a 60 seg' },
    { value: 'Video', label: '📹 Video Completo', desc: 'Video largo o spot de campaña' },
    { value: 'Live', label: '🎙️ Transmisión en Vivo', desc: 'Streaming oficial' },
  ],
  linkedin: [
    { value: 'Post', label: '📄 Post Profesional', desc: 'Artículo o actualización laboral' },
    { value: 'Documento', label: '📑 Documento PDF / Carrusel', desc: 'Presentación o infografía deslizable' },
    { value: 'Video', label: '📹 Video Profesional', desc: 'Discurso o video institucional' },
  ],
};

export const PLATFORM_DIFFUSION_TYPES = {
  instagram: [
    {
      value: 'organico',
      label: '🌱 Orgánica Pura',
      badge: 'Feed & Explorar',
      desc: 'Tracción 100% natural sin pauta paga',
      color: 'emerald',
      isPaid: false,
    },
    {
      value: 'organico_impulsado',
      label: '🚀 Post Impulsado (Boosted Post)',
      badge: 'Botón Promocionar',
      desc: 'Post del feed promocionado con presupuesto para ampliar alcance',
      color: 'cyan',
      isPaid: true,
    },
    {
      value: 'pauta_paga',
      label: '🎯 Dark Post / Anuncio Directo (Meta Ads)',
      badge: 'Ads Manager Exclusivo',
      desc: 'Anuncio segmentado que no figura en la cuadrícula del perfil',
      color: 'violet',
      isPaid: true,
    },
    {
      value: 'colaboracion_pagada',
      label: '🌟 Colaboración Pagada / Influencer',
      badge: 'Paid Partnership',
      desc: 'Acuerdo con influencers, creadores o medios locales',
      color: 'amber',
      isPaid: true,
    },
  ],
  default: [
    {
      value: 'organico',
      label: '🌱 Orgánica Pura',
      badge: 'Tracción Natural',
      desc: 'Distribución natural de la audiencia sin costo',
      color: 'emerald',
      isPaid: false,
    },
    {
      value: 'organico_impulsado',
      label: '🚀 Post Impulsado (Boosted)',
      badge: 'Promocionado',
      desc: 'Post orgánico potenciado con dinero tras registrar alto rendimiento',
      color: 'cyan',
      isPaid: true,
    },
    {
      value: 'pauta_paga',
      label: '📢 Pauta Paga / Anuncio Directo',
      badge: 'Inversión Publicitaria',
      desc: 'Publicación con presupuesto publicitario asignado',
      color: 'violet',
      isPaid: true,
    },
    {
      value: 'colaboracion_pagada',
      label: '🌟 Colaboración Pagada',
      badge: 'Partnership',
      desc: 'Mención o acuerdo con terceros',
      color: 'amber',
      isPaid: true,
    },
  ],
};

export const getSocialMeta = (key) => {
  switch (key) {
    case 'instagram':
      return {
        name: 'Instagram',
        color: '#E4405F',
        bgLight: 'bg-[#E4405F]/15',
        borderFocus: 'focus:ring-[#E4405F]',
      };
    case 'facebook':
      return {
        name: 'Facebook',
        color: '#1877F2',
        bgLight: 'bg-[#1877F2]/15',
        borderFocus: 'focus:ring-[#1877F2]',
      };
    case 'tiktok':
      return {
        name: 'TikTok',
        color: '#00F2FE',
        bgLight: 'bg-cyan-500/15',
        borderFocus: 'focus:ring-cyan-500',
      };
    case 'threads':
      return {
        name: 'Threads',
        color: '#000000',
        bgLight: 'bg-slate-900/15 dark:bg-slate-100/15',
        borderFocus: 'focus:ring-slate-500',
      };
    case 'x_twitter':
    case 'twitter':
      return {
        name: 'X (Twitter)',
        color: '#000000',
        bgLight: 'bg-slate-500/15',
        borderFocus: 'focus:ring-slate-500',
      };
    case 'youtube':
      return {
        name: 'YouTube',
        color: '#FF0000',
        bgLight: 'bg-red-500/15',
        borderFocus: 'focus:ring-red-500',
      };
    case 'linkedin':
      return {
        name: 'LinkedIn',
        color: '#0A66C2',
        bgLight: 'bg-[#0A66C2]/15',
        borderFocus: 'focus:ring-[#0A66C2]',
      };
    default:
      return {
        name: 'Red Social',
        color: '#06b6d4',
        bgLight: 'bg-cyan-500/15',
        borderFocus: 'focus:ring-cyan-500',
      };
  }
};

export const getSocialPlaceholder = (key) => {
  switch (key) {
    case 'instagram':
      return 'https://www.instagram.com/usuario/';
    case 'facebook':
      return 'https://www.facebook.com/usuario/';
    case 'threads':
      return 'https://www.threads.net/@usuario';
    case 'tiktok':
      return 'https://www.tiktok.com/@usuario';
    case 'x_twitter':
    case 'twitter':
      return 'https://x.com/usuario';
    case 'youtube':
      return 'https://www.youtube.com/@usuario';
    case 'linkedin':
      return 'https://www.linkedin.com/in/usuario/';
    default:
      return 'https://...';
  }
};

export const getHandlePlaceholder = (key) => {
  switch (key) {
    case 'instagram':
      return 'ej. @usuario';
    case 'facebook':
      return 'ej. @usuario.oficial';
    case 'threads':
      return 'ej. @usuario';
    case 'tiktok':
      return 'ej. @usuario';
    case 'x_twitter':
    case 'twitter':
      return 'ej. @usuario';
    case 'youtube':
      return 'ej. @canal';
    case 'linkedin':
      return 'ej. in/usuario';
    default:
      return 'ej. @usuario';
  }
};

export const tabBadgeStyle = (colorEstado) => {
  switch (colorEstado) {
    case 'azul':
      // 🔵 Certificada / Verificada
      return {
        tab: 'border-blue-500 bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold ring-2 ring-blue-500/30',
        pill: 'bg-blue-500 text-white font-bold',
        label: 'Verificada',
      };
    case 'verde':
    case 'naranja':
      // 🟢 Activa con movimiento de campaña
      return {
        tab: 'border-emerald-500 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold ring-2 ring-emerald-500/30',
        pill: 'bg-emerald-500 text-white font-bold',
        label: 'Activa',
      };
    case 'rojo':
      // 🔴 Vinculada pero Inactiva
      return {
        tab: 'border-rose-500 bg-rose-500/10 text-rose-600 dark:text-rose-400 font-semibold ring-2 ring-rose-500/30',
        pill: 'bg-rose-500 text-white font-bold',
        label: 'Inactiva',
      };
    case 'gris':
    default:
      // ⚪ Sin uso / Pendiente de configuración
      return {
        tab: 'border-slate-300 dark:border-slate-700 bg-slate-100/70 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-medium ring-1 ring-slate-400/30',
        pill: 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium',
        label: 'Configurar',
      };
  }
};
