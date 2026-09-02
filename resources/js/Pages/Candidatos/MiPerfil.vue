<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3';
import WarRoomLayout from '../../Layouts/WarRoomLayout.vue';
import Badge from '../../Components/Badge.vue';
import MediaEmbed from '../../Components/MediaEmbed.vue';
import {
  Sparkles,
  Users,
  Link2,
  ExternalLink,
  ShieldCheck,
  Flag,
  Calendar,
  Save,
  Edit3,
  MapPin,
  Vote,
  TrendingUp,
  TrendingDown,
  RefreshCw,
  Zap,
  Image as ImageIcon,
  CheckCircle,
  AlertCircle,
  Settings,
  X,
  Plus,
  DollarSign,
  Heart,
  MessageCircle,
  Eye,
  Share2,
  Star,
  Film,
  Send,
  Layers,
  Flame,
  Check,
  Clock,
  Radio,
  Bookmark,
  Filter,
  Search,
  RotateCcw,
  Target,
  BarChart3
} from '@lucide/vue';

// Configuración de Formatos Específicos por Red Social
const platformFormats = {
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

// Tipos de Difusión & Pauta Específicos por Red Social
const platformDiffusionTypes = {
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
      value: 'pauta_paga',
      label: '📢 Pauta Paga / Promocionada',
      badge: 'Inversión Publicitaria',
      desc: 'Publicación con presupuesto publicitario asignado',
      color: 'violet',
      isPaid: true,
    },
  ],
};

const currentPlatformFormats = computed(() => {
  return platformFormats[selectedPlatformKey.value] || platformFormats.instagram;
});

const currentPlatformDiffusionTypes = computed(() => {
  return platformDiffusionTypes[selectedPlatformKey.value] || platformDiffusionTypes.default;
});

const isPaidDiffusion = computed(() => {
  return formPost.tipo_pauta !== 'organico';
});

const props = defineProps({
  candidato: {
    type: Object,
    required: true,
  },
  redes: {
    type: Array,
    default: () => [],
  },
  ciclos: {
    type: Array,
    default: () => [],
  },
  territorios: {
    type: Array,
    default: () => [],
  },
  publicaciones: {
    type: Array,
    default: () => [],
  },
  ejes: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const canWrite = computed(() => page.props.auth?.user?.can_write ?? true);

// Pestaña de red social activa seleccionada
const selectedPlatformKey = ref(props.redes[0]?.key || 'instagram');

const currentRed = computed(() => {
  return props.redes.find(r => r.key === selectedPlatformKey.value) || props.redes[0];
});

// Formulario reactivo para la red seleccionada
const formRed = useForm({
  candidato_id: props.candidato.id,
  plataforma: currentRed.value?.key || 'instagram',
  handle_usuario: currentRed.value?.handle_usuario || '',
  url_perfil: currentRed.value?.url_perfil || '',
  foto_perfil_url: currentRed.value?.foto_perfil_url || '',
  esta_activo: currentRed.value?.esta_activo ?? true,
  esta_verificado: currentRed.value?.esta_verificado ?? false,
  seguidores_actuales: currentRed.value?.seguidores_actuales || 0,
  seguidos_actuales: currentRed.value?.seguidos_actuales || 0,
  publicaciones_totales: currentRed.value?.publicaciones_totales || 0,
  me_gusta_totales: currentRed.value?.me_gusta_totales || 0,
  visualizaciones_totales: currentRed.value?.visualizaciones_totales || 0,
  fecha_punto_cero: currentRed.value?.fecha_punto_cero || new Date().toISOString().slice(0, 10),
  seguidores_punto_cero: currentRed.value?.seguidores_punto_cero || currentRed.value?.seguidores_actuales || 0,
  seguidos_punto_cero: currentRed.value?.seguidos_punto_cero || currentRed.value?.seguidos_actuales || 0,
  publicaciones_punto_cero: currentRed.value?.publicaciones_punto_cero || currentRed.value?.publicaciones_totales || 0,
  me_gusta_punto_cero: currentRed.value?.me_gusta_punto_cero || currentRed.value?.me_gusta_totales || 0,
  visualizaciones_punto_cero: currentRed.value?.visualizaciones_punto_cero || currentRed.value?.visualizaciones_totales || 0,
  notas_punto_cero: currentRed.value?.notas_punto_cero || '',
});

const selectPlatform = (platformKey) => {
  selectedPlatformKey.value = platformKey;
  const red = props.redes.find(r => r.key === platformKey);
  if (red) {
    formRed.candidato_id = props.candidato.id;
    formRed.plataforma = red.key;
    formRed.handle_usuario = red.handle_usuario || '';
    formRed.url_perfil = red.url_perfil || '';
    formRed.foto_perfil_url = red.foto_perfil_url || '';
    formRed.esta_activo = red.esta_activo ?? true;
    formRed.esta_verificado = red.esta_verificado ?? false;
    formRed.seguidores_actuales = red.seguidores_actuales || 0;
    formRed.seguidos_actuales = red.seguidos_actuales || 0;
    formRed.publicaciones_totales = red.publicaciones_totales || 0;
    formRed.me_gusta_totales = red.me_gusta_totales || 0;
    formRed.visualizaciones_totales = red.visualizaciones_totales || 0;
    formRed.fecha_punto_cero = red.fecha_punto_cero || new Date().toISOString().slice(0, 10);
    formRed.seguidores_punto_cero = red.seguidores_punto_cero || red.seguidores_actuales || 0;
    formRed.seguidos_punto_cero = red.seguidos_punto_cero || red.seguidos_actuales || 0;
    formRed.publicaciones_punto_cero = red.publicaciones_punto_cero || red.publicaciones_totales || 0;
    formRed.me_gusta_punto_cero = red.me_gusta_punto_cero || red.me_gusta_totales || 0;
    formRed.visualizaciones_punto_cero = red.visualizaciones_punto_cero || red.visualizaciones_totales || 0;
    formRed.notas_punto_cero = red.notas_punto_cero || '';
    scrapeMessage.value = '';
  }
};

watch(() => props.redes, (newRedes) => {
  if (!newRedes || !newRedes.length) return;
  const current = newRedes.find(r => r.key === selectedPlatformKey.value);
  if (current) {
    formRed.handle_usuario = current.handle_usuario || '';
    formRed.url_perfil = current.url_perfil || '';
    formRed.foto_perfil_url = current.foto_perfil_url || '';
    formRed.esta_activo = current.esta_activo ?? true;
    formRed.esta_verificado = current.esta_verificado ?? false;
    formRed.seguidores_actuales = current.seguidores_actuales || 0;
    formRed.seguidos_actuales = current.seguidos_actuales || 0;
    formRed.publicaciones_totales = current.publicaciones_totales || 0;
    formRed.me_gusta_totales = current.me_gusta_totales || 0;
    formRed.visualizaciones_totales = current.visualizaciones_totales || 0;
    formRed.fecha_punto_cero = current.fecha_punto_cero || new Date().toISOString().slice(0, 10);
    formRed.seguidores_punto_cero = current.seguidores_punto_cero || 0;
    formRed.seguidos_punto_cero = current.seguidos_punto_cero || 0;
    formRed.publicaciones_punto_cero = current.publicaciones_punto_cero || 0;
    formRed.me_gusta_punto_cero = current.me_gusta_punto_cero || 0;
    formRed.visualizaciones_punto_cero = current.visualizaciones_punto_cero || 0;
    formRed.notas_punto_cero = current.notas_punto_cero || '';
  }
}, { deep: true });

const isScraping = ref(false);
const scrapeMessage = ref('');
const scrapeSuccess = ref(false);

const fetchScrapedData = async () => {
  if (!formRed.url_perfil) {
    scrapeMessage.value = 'Por favor pega el enlace del perfil primero.';
    scrapeSuccess.value = false;
    return;
  }
  isScraping.value = true;
  scrapeMessage.value = 'Leyendo foto, seguidores, seguidos y publicaciones desde la red social...';

  try {
    const response = await fetch('/perfiles-sociales/scrape', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        url: formRed.url_perfil,
        plataforma: formRed.plataforma,
      }),
    });

    const data = await response.json();
    if (data) {
      if (data.handle_usuario) formRed.handle_usuario = data.handle_usuario;
      if (data.foto_perfil_url) formRed.foto_perfil_url = data.foto_perfil_url;
      
      if (data.seguidores !== null && data.seguidores !== undefined) {
        formRed.seguidores_actuales = Number(data.seguidores);
        formRed.seguidores_punto_cero = Number(data.seguidores);
      }
      if (data.seguidos !== null && data.seguidos !== undefined) {
        formRed.seguidos_actuales = Number(data.seguidos);
        formRed.seguidos_punto_cero = Number(data.seguidos);
      }
      if (data.publicaciones !== null && data.publicaciones !== undefined) {
        formRed.publicaciones_totales = Number(data.publicaciones);
        formRed.publicaciones_punto_cero = Number(data.publicaciones);
      }
      if (data.me_gusta_totales !== null && data.me_gusta_totales !== undefined) {
        formRed.me_gusta_totales = Number(data.me_gusta_totales);
        formRed.me_gusta_punto_cero = Number(data.me_gusta_totales);
      }
      if (data.visualizaciones_totales !== null && data.visualizaciones_totales !== undefined) {
        formRed.visualizaciones_totales = Number(data.visualizaciones_totales);
        formRed.visualizaciones_punto_cero = Number(data.visualizaciones_totales);
      }
      
      formRed.esta_activo = true;
      scrapeSuccess.value = true;
      scrapeMessage.value = data.mensaje || '¡Datos extraídos con éxito!';
    }
  } catch (err) {
    scrapeSuccess.value = false;
    scrapeMessage.value = 'No se pudo conectar con el lector. Puedes ingresar los números manualmente.';
  } finally {
    isScraping.value = false;
  }
};

const isConfigModalOpen = ref(false);

const openConfigModal = (platformKey = null) => {
  if (platformKey) {
    selectPlatform(platformKey);
  }
  isConfigModalOpen.value = true;
};

const savePerfilSocial = () => {
  // Sincronizar Punto Cero con actuales si están en 0
  if (!formRed.seguidores_punto_cero) formRed.seguidores_punto_cero = formRed.seguidores_actuales;
  if (!formRed.seguidos_punto_cero) formRed.seguidos_punto_cero = formRed.seguidos_actuales;
  if (!formRed.publicaciones_punto_cero) formRed.publicaciones_punto_cero = formRed.publicaciones_totales;
  if (!formRed.me_gusta_punto_cero) formRed.me_gusta_punto_cero = formRed.me_gusta_totales;
  if (!formRed.visualizaciones_punto_cero) formRed.visualizaciones_punto_cero = formRed.visualizaciones_totales;

  formRed.post('/perfiles-sociales', {
    preserveScroll: true,
    onSuccess: () => {
      scrapeMessage.value = '¡Canal guardado correctamente!';
      scrapeSuccess.value = true;
      isConfigModalOpen.value = false;
    }
  });
};

// Modal de edición de datos generales del candidato
const isEditingCandidato = ref(false);
const formCandidato = useForm({
  workspace_nombre: page.props.workspace?.nombre || '',
  nombre_completo: props.candidato.nombre_completo || '',
  partido_coalicion: props.candidato.partido_coalicion || '',
  cargo_aspirado: props.candidato.cargo_aspirado || '',
  estado_politico: props.candidato.estado_politico || 'candidato',
  ciclo_campana_id: props.candidato.ciclo_campana_id || props.ciclos[0]?.id || '',
  territorio_id: props.candidato.territorio_id || props.candidato.territorio?.id || '',
  territorio_nombre: props.candidato.territorio?.nombre || '',
  padron_electoral: props.candidato.territorio?.padron_electoral || 0,
  poblacion_total: props.candidato.territorio?.poblacion_total || 0,
  tipo_territorio: props.candidato.territorio?.tipo || 'municipio',
  color_hex: props.candidato.color_hex || '#06b6d4',
  avatar_url: props.candidato.avatar_url || '',
  bio_resumen: props.candidato.bio_resumen || '',
});

const isDetectingTerritorio = ref(false);
const detectTerritorioMessage = ref('');
const detectTerritorioSuccess = ref(false);

const autoDetectTerritorio = async () => {
  if (!formCandidato.territorio_nombre) return;
  isDetectingTerritorio.value = true;
  detectTerritorioMessage.value = 'Buscando en Georef e INDEC...';
  try {
    const res = await fetch('/territorios/auto-detect', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        nombre: formCandidato.territorio_nombre,
        provincia: 'San Juan',
      }),
    });
    const data = await res.json();
    if (data && data.success) {
      formCandidato.territorio_nombre = `Departamento ${data.nombre}`;
      if (data.padron_electoral) formCandidato.padron_electoral = Number(data.padron_electoral);
      if (data.poblacion_total) formCandidato.poblacion_total = Number(data.poblacion_total);
      detectTerritorioSuccess.value = true;
      detectTerritorioMessage.value = `¡${data.nombre} detectado! Padrón: ${Number(data.padron_electoral).toLocaleString('es-AR')}`;
    } else {
      detectTerritorioSuccess.value = false;
      detectTerritorioMessage.value = data.mensaje || 'No se encontraron datos automáticos.';
    }
  } catch (e) {
    detectTerritorioSuccess.value = false;
    detectTerritorioMessage.value = 'Error de conexión.';
  } finally {
    isDetectingTerritorio.value = false;
  }
};

const saveCandidato = () => {
  formCandidato.put(`/candidatos/${props.candidato.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      isEditingCandidato.value = false;
    }
  });
};

const tabBadgeStyle = (colorEstado) => {
  switch (colorEstado) {
    case 'azul':
      // 🔵 Certificada / Verificada
      return {
        tab: 'border-blue-500 bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold ring-2 ring-blue-500/30',
        pill: 'bg-blue-500 text-white font-bold',
        label: 'Verificada'
      };
    case 'verde':
    case 'naranja': // retrocompatibilidad
      // 🟢 Activa / En uso de campaña
      return {
        tab: 'border-emerald-500 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold ring-2 ring-emerald-500/30',
        pill: 'bg-emerald-500 text-white font-bold',
        label: 'Activa'
      };
    case 'rojo':
      // 🔴 Vinculada pero Inactiva / Sin movimiento
      return {
        tab: 'border-rose-500 bg-rose-500/10 text-rose-600 dark:text-rose-400 font-semibold ring-2 ring-rose-500/30',
        pill: 'bg-rose-500 text-white font-bold',
        label: 'Inactiva'
      };
    case 'gris':
    default:
      // ⚪ Sin uso / Pendiente de configuración
      return {
        tab: 'border-slate-300 dark:border-slate-700 bg-slate-100/70 dark:bg-slate-950 text-slate-500 dark:text-slate-400 font-medium ring-1 ring-slate-400/30',
        pill: 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-medium',
        label: 'Configurar'
      };
  }
};
const getSocialMeta = (key) => {
  switch (key) {
    case 'instagram':
      return {
        color: '#E4405F',
        bgLight: 'bg-[#E4405F]/15',
      };
    case 'facebook':
      return {
        color: '#1877F2',
        bgLight: 'bg-[#1877F2]/15',
      };
    case 'tiktok':
      return {
        color: '#00F2FE',
        bgLight: 'bg-cyan-500/15',
      };
    case 'threads':
      return {
        color: '#000000',
        bgLight: 'bg-slate-900/15',
      };
    case 'x_twitter':
      return {
        color: '#000000',
        bgLight: 'bg-slate-500/15',
      };
    case 'youtube':
      return {
        color: '#FF0000',
        bgLight: 'bg-red-500/15',
      };
    case 'linkedin':
      return {
        color: '#0A66C2',
        bgLight: 'bg-[#0A66C2]/15',
      };
    default:
      return { color: '#06b6d4', bgLight: 'bg-cyan-500/15' };
  }
};

const getSocialPlaceholder = (key) => {
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
      return 'https://x.com/usuario';
    case 'youtube':
      return 'https://www.youtube.com/@usuario';
    case 'linkedin':
      return 'https://www.linkedin.com/in/usuario/';
    default:
      return 'https://...';
  }
};

const getHandlePlaceholder = (key) => {
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
      return 'ej. @usuario';
    case 'youtube':
      return 'ej. @canal';
    case 'linkedin':
      return 'ej. in/usuario';
    default:
      return 'ej. @usuario';
  }
};

// --- Publicaciones & Muro por Red Social ---
const currentRedPublicaciones = computed(() => {
  return props.publicaciones.filter(p => {
    if (p.plataforma) return p.plataforma === selectedPlatformKey.value;
    if (currentRed.value?.perfil_id) return p.perfil_social_id === currentRed.value.perfil_id;
    return false;
  });
});

const currentRedStats = computed(() => {
  const posts = currentRedPublicaciones.value;
  const totalPosts = posts.length;
  const totalVistas = posts.reduce((sum, p) => sum + Number(p.total_vistas || 0), 0);
  const totalLikes = posts.reduce((sum, p) => sum + Number(p.total_likes || 0), 0);
  const totalComentarios = posts.reduce((sum, p) => sum + Number(p.total_comentarios || 0), 0);
  const totalCompartidos = posts.reduce((sum, p) => sum + Number(p.total_compartidos || 0), 0);
  const totalRepublicados = posts.reduce((sum, p) => sum + Number(p.total_republicados || 0), 0);
  const totalGuardados = posts.reduce((sum, p) => sum + Number(p.total_guardados || 0), 0);
  
  // Interacciones Públicas Directas (Sin Guardados)
  const totalInteracciones = totalLikes + totalComentarios + totalCompartidos + totalRepublicados;
  
  // Score de Impacto Ponderado de Campaña (1/3/5/10)
  const scoreImpactoTotal = (totalLikes * 1) + (totalComentarios * 3) + (totalCompartidos * 5) + (totalRepublicados * 10);
  
  const totalPauta = posts.reduce((sum, p) => sum + Number(p.monto_invertido_pauta || 0), 0);
  const postsOrganicos = posts.filter(p => p.tipo_pauta === 'organico' || p.tipo_pauta === 'organico_impulsado').length;
  const postsPauta = posts.filter(p => p.tipo_pauta === 'pauta_paga' || p.tipo_pauta === 'anuncio_directo').length;

  return {
    totalPosts,
    totalVistas,
    totalLikes,
    totalComentarios,
    totalCompartidos,
    totalRepublicados,
    totalGuardados,
    totalInteracciones,
    scoreImpactoTotal,
    totalPauta,
    postsOrganicos,
    postsPauta,
  };
});



// --- Sincronización en Vivo de Publicaciones (Ventana Móvil 15 Días) ---
const isSyncModalOpen = ref(false);
const isSyncingRecientes = ref(false);
const syncProgress = ref({
  current: 0,
  total: 0,
  currentUrl: '',
  currentTitle: '',
  isFinished: false,
  totalNewLikes: 0,
  totalNewComments: 0,
});
const syncLogs = ref([]);

const postsInActiveWindow = computed(() => {
  const now = new Date();
  const limit = new Date(now.getTime() - 15 * 24 * 60 * 60 * 1000);

  return currentRedPublicaciones.value.filter(p => {
    const raw = p.fecha_publicacion_raw || p.fecha_publicacion;
    if (!raw || !p.url_post) return false;
    try {
      let d;
      if (typeof raw === 'string' && raw.includes('/')) {
        const parts = raw.split(' ')[0].split('/');
        d = new Date(Number(parts[2]), Number(parts[1]) - 1, Number(parts[0]));
      } else {
        d = new Date(raw);
      }
      return !isNaN(d.getTime()) && d >= limit;
    } catch (e) {
      return false;
    }
  });
});

const postsIn15DaysCount = computed(() => postsInActiveWindow.value.length);

const sincronizarPublicacionesRecientes = async () => {
  const targetPosts = postsInActiveWindow.value;
  if (!targetPosts.length || isSyncingRecientes.value) return;

  isSyncingRecientes.value = true;
  isSyncModalOpen.value = true;
  syncProgress.value = {
    current: 0,
    total: targetPosts.length,
    currentUrl: '',
    currentTitle: '',
    isFinished: false,
    totalNewLikes: 0,
    totalNewComments: 0,
  };
  syncLogs.value = [];

  for (let i = 0; i < targetPosts.length; i++) {
    const post = targetPosts[i];
    syncProgress.value.currentUrl = post.url_post;
    syncProgress.value.currentTitle = post.contenido_resumen || 'Publicación';

    try {
      const res = await window.axios.post(`/publicaciones/${post.id}/sincronizar`);
      if (res.data && res.data.success) {
        syncLogs.value.unshift({
          status: 'success',
          url: res.data.url_post,
          resumen: res.data.resumen,
          likes: res.data.total_likes,
          deltaLikes: res.data.delta_likes || 0,
          comments: res.data.total_comentarios,
          deltaComments: res.data.delta_comentarios || 0,
          fecha: res.data.fecha,
        });
        syncProgress.value.totalNewLikes += Number(res.data.delta_likes || 0);
        syncProgress.value.totalNewComments += Number(res.data.delta_comentarios || 0);
      } else {
        syncLogs.value.unshift({
          status: 'warning',
          url: post.url_post,
          resumen: (post.contenido_resumen || '').slice(0, 45) + '...',
          error: res.data?.mensaje || 'No se pudieron extraer datos nuevos',
        });
      }
    } catch (err) {
      syncLogs.value.unshift({
        status: 'error',
        url: post.url_post,
        resumen: (post.contenido_resumen || '').slice(0, 45) + '...',
        error: 'Error de conexión',
      });
    }

    syncProgress.value.current = i + 1;
  }

  syncProgress.value.isFinished = true;
  isSyncingRecientes.value = false;

  // Recargar datos en Inertia para refrescar cards y barras
  router.reload({ preserveScroll: true });
};

const getDefaultFormatForPlatform = (key) => {
  switch (key) {
    case 'instagram': return 'Reel';
    case 'tiktok': return 'Video';
    case 'threads': return 'Post';
    case 'facebook': return 'Post';
    case 'youtube': return 'Shorts';
    case 'x_twitter': return 'Tweet';
    case 'linkedin': return 'Articulo';
    default: return 'Post';
  }
};

const isPostModalOpen = ref(false);
const showAdvancedEmotions = ref(false);

const formPost = useForm({
  candidato_id: props.candidato.id,
  perfil_social_id: null,
  plataforma: 'instagram',
  url_post: '',
  media_url: '',
  fecha_publicacion: new Date().toISOString().slice(0, 16),
  tipo_formato: 'Reel',
  tipo_pauta: 'organico',
  monto_invertido_pauta: 0,
  eje_tematico_id: props.ejes[0]?.id || null,
  eje_tematico_nombre: '',
  vistas_organicas: 0,
  vistas_pagadas: 0,
  contenido_resumen: '',
  total_likes: 0,
  total_comentarios: 0,
  total_compartidos: 0,
  total_guardados: 0,
  me_gusta: 0,
  me_encanta: 0,
  me_importa: 0,
  me_divierte: 0,
  me_asombra: 0,
  me_entristece: 0,
  me_enoja: 0,
  termometro_humor_social: 4,
  comentario_destacado: '',
  figura_acompanante: '',
});

const openCreatePostModal = () => {
  formPost.candidato_id = props.candidato.id;
  formPost.perfil_social_id = currentRed.value?.perfil_id || null;
  formPost.plataforma = selectedPlatformKey.value;
  formPost.tipo_formato = getDefaultFormatForPlatform(selectedPlatformKey.value);
  formPost.tipo_pauta = 'organico';
  formPost.monto_invertido_pauta = 0;
  formPost.url_post = '';
  formPost.media_url = '';
  formPost.contenido_resumen = '';
  formPost.fecha_publicacion = new Date().toISOString().slice(0, 16);
  formPost.total_likes = 0;
  formPost.total_comentarios = 0;
  formPost.total_compartidos = 0;
  formPost.total_guardados = 0;
  formPost.vistas_organicas = 0;
  formPost.vistas_pagadas = 0;
  formPost.me_gusta = 0;
  formPost.me_encanta = 0;
  formPost.me_importa = 0;
  formPost.me_divierte = 0;
  formPost.me_asombra = 0;
  formPost.me_entristece = 0;
  formPost.me_enoja = 0;
  formPost.eje_tematico_id = props.ejes[0]?.id || null;
  formPost.eje_tematico_nombre = '';
  showAdvancedEmotions.value = false;
  scrapePostFeedback.value = null;
  isPostModalOpen.value = true;
};

const isScrapingPost = ref(false);
const scrapePostFeedback = ref(null);

const scrapePostData = async () => {
  if (!formPost.url_post) return;
  isScrapingPost.value = true;
  scrapePostFeedback.value = null;

  try {
    const response = await window.axios.post('/publicaciones/scrape-post', {
      url: formPost.url_post,
      plataforma: selectedPlatformKey.value,
    });

    if (response.data && response.data.success) {
      if (response.data.url_post) formPost.url_post = response.data.url_post;
      if (response.data.contenido_resumen) formPost.contenido_resumen = response.data.contenido_resumen;
      if (response.data.total_likes > 0) formPost.total_likes = response.data.total_likes;
      if (response.data.total_comentarios > 0) formPost.total_comentarios = response.data.total_comentarios;
      if (response.data.fecha_publicacion) formPost.fecha_publicacion = response.data.fecha_publicacion;
      if (response.data.tipo_formato) formPost.tipo_formato = response.data.tipo_formato;
      if (response.data.media_url) formPost.media_url = response.data.media_url;

      scrapePostFeedback.value = {
        type: 'success',
        text: `✨ ¡Datos extraídos! (${response.data.total_likes} likes, ${response.data.total_comentarios} comentarios, fecha y texto completos)`,
      };
    } else {
      scrapePostFeedback.value = {
        type: 'warning',
        text: response.data?.mensaje || 'No se pudieron extraer los datos automáticamente.',
      };
    }
  } catch (error) {
    scrapePostFeedback.value = {
      type: 'error',
      text: 'Error de conexión al extraer el post. Puedes completar los campos manualmente.',
    };
  } finally {
    isScrapingPost.value = false;
  }
};

// Autodetección de formato al pegar enlace
watch(() => formPost.url_post, (newUrl) => {
  if (!newUrl) return;
  const url = newUrl.toLowerCase();
  if (url.includes('/reel/')) {
    formPost.tipo_formato = 'Reel';
  } else if (url.includes('/p/')) {
    formPost.tipo_formato = 'Foto';
  } else if (url.includes('/tv/')) {
    formPost.tipo_formato = 'Video';
  } else if (url.includes('/shorts/')) {
    formPost.tipo_formato = 'Shorts';
  } else if (url.includes('tiktok.com')) {
    formPost.tipo_formato = 'Video';
  } else if (url.includes('twitter.com') || url.includes('x.com')) {
    formPost.tipo_formato = 'Tweet';
  }
});

// Sincronizar likes totales con desglose si el panel avanzado está cerrado
watch(() => formPost.total_likes, (val) => {
  const num = Number(val || 0);
  if (!showAdvancedEmotions.value) {
    formPost.me_gusta = Math.round(num * 0.75);
    formPost.me_encanta = Math.round(num * 0.20);
    formPost.me_importa = Math.round(num * 0.05);
    formPost.me_enoja = 0;
  }
});

const submitPublicacion = () => {
  formPost.candidato_id = props.candidato.id;
  formPost.perfil_social_id = currentRed.value?.perfil_id || null;
  formPost.plataforma = selectedPlatformKey.value;

  formPost.post('/publicaciones', {
    preserveScroll: true,
    onSuccess: () => {
      isPostModalOpen.value = false;
      formPost.reset();
    }
  });
};

const isRefreshingCanal = ref(false);

const refrescarCanal = () => {
  if (!currentRed.value?.perfil_id || !currentRed.value?.url_perfil) {
    openConfigModal(selectedPlatformKey.value);
    return;
  }

  isRefreshingCanal.value = true;
  router.post(`/perfiles-sociales/${currentRed.value.perfil_id}/refrescar`, {}, {
    preserveScroll: true,
    onFinish: () => {
      isRefreshingCanal.value = false;
    }
  });
};
</script>

<template>
  <Head :title="`Perfil Propio: ${candidato.nombre_completo}`" />

  <WarRoomLayout>
    <!-- 1. Header Principal del Cliente de Campaña -->
    <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="flex items-start sm:items-center gap-4">
          <div class="relative shrink-0">
            <img
              :src="candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(candidato.nombre_completo)}&background=0f172a&color=06b6d4`"
              :alt="candidato.nombre_completo"
              referrerpolicy="no-referrer"
              class="w-20 h-20 rounded-2xl object-cover border-2 shadow-md"
              :style="{ borderColor: candidato.color_hex || '#06b6d4' }"
            />
          </div>

          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-slate-100 tracking-tight">
                {{ candidato.nombre_completo }}
              </h1>
              <Badge variant="estado" :value="candidato.estado_politico" size="sm" />
            </div>

            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-1">
              {{ candidato.cargo_aspirado }} &bull; <span class="text-slate-500 font-normal">{{ candidato.partido_coalicion }}</span>
            </p>

            <div class="mt-2.5 flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
              <span class="inline-flex items-center gap-1">
                <MapPin class="w-3.5 h-3.5 text-cyan-500" />
                {{ candidato.territorio?.nombre || 'Territorio General' }}
              </span>
              <span v-if="candidato.territorio?.padron_electoral" class="inline-flex items-center gap-1 font-mono">
                <Vote class="w-3.5 h-3.5 text-emerald-500" />
                Padrón: {{ Number(candidato.territorio.padron_electoral).toLocaleString('es-AR') }} electores
              </span>
              <!-- Seguidores Únicos Reales (TIERS) -->
              <span
                class="inline-flex items-center gap-1.5 font-mono text-cyan-600 dark:text-cyan-400 font-bold bg-cyan-500/10 px-2.5 py-1 rounded-xl border border-cyan-500/20"
                :title="`Desduplicación por TIERS de canales activos: ${Number(candidato.total_seguidores_netos || candidato.total_seguidores).toLocaleString('es-AR')} electores únicos no repetidos`"
              >
                <Users class="w-3.5 h-3.5" />
                <span>{{ Number(candidato.total_seguidores_netos || candidato.total_seguidores).toLocaleString('es-AR') }}</span>
                <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-500">Únicos Reales</span>
                <span class="text-[10px] text-slate-400 font-normal">({{ Number(candidato.total_seguidores_bruto || candidato.total_seguidores).toLocaleString('es-AR') }} brutos)</span>
              </span>
              <span
                v-if="candidato.penetracion_neta_pct"
                class="inline-flex items-center gap-1 font-mono text-emerald-600 dark:text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded-lg border border-emerald-500/20 text-[11px]"
                :title="`Penetración Neta sobre el padrón electoral`"
              >
                <Target class="w-3 h-3" />
                <span>{{ candidato.penetracion_neta_pct }}% del padrón</span>
              </span>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2.5 self-start md:self-center">
          <button
            type="button"
            @click="isEditingCandidato = true"
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-xs transition-all cursor-pointer"
          >
            <Edit3 class="w-4 h-4 text-cyan-500" />
            <span>Editar Datos Generales</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 2. Pestañas de Redes Sociales (Grid de 7 Canales en 1 Sola Fila con Logos Oficiales) -->
    <div class="space-y-4">
      <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 lg:grid-cols-7 gap-2.5 sm:gap-3">
        <div
          v-for="red in redes"
          :key="red.key"
          @click="selectPlatform(red.key)"
          class="p-3 sm:p-3.5 rounded-2xl border-2 transition-all flex flex-col items-center justify-center text-center gap-2 cursor-pointer relative shadow-xs group"
          :class="[
            selectedPlatformKey === red.key
              ? 'shadow-md scale-102 ' + tabBadgeStyle(red.color_estado).tab
              : (red.color_estado === 'gris'
                  ? 'border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 text-slate-400 opacity-75 hover:opacity-100 hover:border-slate-400 hover:bg-white dark:hover:bg-slate-900'
                  : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-sm')
          ]"
        >
          <!-- Botón de Engranaje para Configurar Directamente -->
          <button
            type="button"
            @click.stop="openConfigModal(red.key)"
            class="absolute top-1.5 right-1.5 p-1 rounded-lg text-slate-400 hover:text-cyan-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
            :class="selectedPlatformKey === red.key ? 'text-cyan-500' : 'opacity-70 group-hover:opacity-100'"
            title="Configurar canal y Punto Cero"
          >
            <Settings class="w-3.5 h-3.5" />
          </button>

          <!-- Logo Oficial de la Red -->
          <div class="flex items-center justify-center w-9 h-9 rounded-xl shadow-2xs shrink-0" :class="getSocialMeta(red.key).bgLight">
            <!-- Instagram -->
            <svg v-if="red.key === 'instagram'" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: getSocialMeta(red.key).color }">
              <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
              <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
            </svg>
            <!-- Facebook -->
            <svg v-else-if="red.key === 'facebook'" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(red.key).color }">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            <!-- Threads -->
            <svg v-else-if="red.key === 'threads'" class="w-4.5 h-4.5 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.186 24C5.454 24 0 18.675 0 12.103 0 5.53 5.454.205 12.186.205c6.733 0 12.186 5.325 12.186 11.898 0 6.572-5.453 11.897-12.186 11.897zm-.055-2.285c5.385 0 9.771-4.27 9.771-9.512 0-5.243-4.386-9.513-9.77-9.513-5.385 0-9.772 4.27-9.772 9.513 0 5.242 4.387 9.512 9.772 9.512zm4.786-9.284c-.035 3.328-1.929 5.353-4.897 5.353-2.735 0-4.636-1.748-4.636-4.52 0-3.053 2.193-4.654 4.887-4.654 1.344 0 2.507.411 3.25 1.134l-1.378 1.458c-.469-.475-1.12-.72-1.85-.72-1.458 0-2.457.946-2.457 2.659 0 1.624 1.054 2.573 2.395 2.573 1.584 0 2.373-.974 2.45-2.283H12.15V9.458h4.722v2.973z"/>
            </svg>
            <!-- TikTok -->
            <svg v-else-if="red.key === 'tiktok'" class="w-4.5 h-4.5 text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
            </svg>
            <!-- X / Twitter -->
            <svg v-else-if="red.key === 'x_twitter'" class="w-4.5 h-4.5 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
            <!-- YouTube -->
            <svg v-else-if="red.key === 'youtube'" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(red.key).color }">
              <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            <!-- LinkedIn -->
            <svg v-else-if="red.key === 'linkedin'" class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(red.key).color }">
              <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
            </svg>
          </div>

          <span class="font-bold text-xs leading-tight truncate max-w-full">{{ red.nombre }}</span>

          <span
            class="text-[9px] sm:text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider font-mono truncate max-w-full"
            :class="tabBadgeStyle(red.color_estado).pill"
          >
            {{ tabBadgeStyle(red.color_estado).label }}
          </span>
        </div>
      </div>

      <!-- 3. FICHA RESUMEN EJECUTIVA DEL CANAL & PUNTO CERO -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4 flex-wrap gap-4">
          <div class="flex items-center gap-4">
            <!-- Foto de Perfil de la Red Social -->
            <div class="relative shrink-0">
              <img
                :src="currentRed.foto_perfil_url || candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(currentRed.handle_usuario || 'User')}&background=0f172a&color=06b6d4`"
                alt="Foto Canal"
                referrerpolicy="no-referrer"
                class="w-14 h-14 rounded-2xl object-cover border-2 border-cyan-500 shadow-sm"
              />
              <div
                class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full flex items-center justify-center border-2 border-white dark:border-slate-900 shadow-xs"
                :class="{
                  'bg-blue-500 text-white': currentRed.color_estado === 'azul',
                  'bg-emerald-500 text-white': currentRed.color_estado === 'verde' || currentRed.color_estado === 'naranja',
                  'bg-rose-500 text-white': currentRed.color_estado === 'rojo',
                  'bg-slate-400 text-white': currentRed.color_estado === 'gris',
                }"
              >
                <CheckCircle v-if="currentRed.color_estado === 'azul'" class="w-3 h-3" />
                <span v-else-if="currentRed.color_estado === 'verde' || currentRed.color_estado === 'naranja'" class="text-[9px] font-extrabold">●</span>
                <span v-else-if="currentRed.color_estado === 'rojo'" class="text-[9px] font-extrabold">×</span>
                <span v-else class="text-[9px] font-extrabold">+</span>
              </div>
            </div>

            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                  <span>{{ currentRed.nombre }}</span>
                </h2>
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
                  :class="tabBadgeStyle(currentRed.color_estado).pill"
                >
                  {{ tabBadgeStyle(currentRed.color_estado).label }}
                </span>
                <span
                  v-if="currentRed.ultima_auditoria_at"
                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-mono font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20"
                  :title="`Última lectura: ${currentRed.ultima_auditoria_fecha || currentRed.ultima_auditoria_at}`"
                >
                  <Zap class="w-3 h-3 text-amber-500" />
                  <span>Auditado {{ currentRed.ultima_auditoria_at }}</span>
                </span>
              </div>

              <div class="flex items-center gap-3 mt-1 text-xs font-mono text-slate-600 dark:text-slate-400 flex-wrap">
                <span class="font-bold text-slate-800 dark:text-slate-200">
                  {{ currentRed.handle_usuario || 'Sin handle asignado' }}
                </span>
                <a
                  v-if="currentRed.url_perfil"
                  :href="currentRed.url_perfil"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-cyan-500 hover:text-cyan-400 inline-flex items-center gap-1 text-[11px] underline font-semibold"
                >
                  <span>Abrir enlace</span>
                  <ExternalLink class="w-3 h-3" />
                </a>
                <span v-else class="text-slate-400 text-[11px] italic">
                  (Enlace no configurado)
                </span>
              </div>
            </div>
          </div>

          <!-- Botones de Acción: Métricas & Dashboard, Refrescar en Vivo & Configurar Punto Cero -->
          <div class="flex items-center gap-2.5 flex-wrap">
            <Link
              v-if="currentRed.perfil_id"
              :href="`/perfiles-sociales/${currentRed.perfil_id}/metricas`"
              class="px-4 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold font-mono flex items-center gap-2 transition-all cursor-pointer shadow-sm hover:scale-102"
              :title="`Abrir Dashboard de Métricas y Analítica Avanzada de ${currentRed.nombre}`"
            >
              <BarChart3 class="w-4 h-4" />
              <span>Métricas & Dashboard</span>
            </Link>

            <button
              type="button"
              @click="refrescarCanal"
              :disabled="isRefreshingCanal"
              class="px-4 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold font-mono flex items-center gap-2 transition-all cursor-pointer shadow-sm hover:scale-102 disabled:opacity-50"
              title="Escanear perfil y registrar nueva medición en el histórico"
            >
              <RefreshCw class="w-4 h-4" :class="isRefreshingCanal ? 'animate-spin' : ''" />
              <span>{{ isRefreshingCanal ? 'Auditando...' : 'Auditar Ahora (1 Clic)' }}</span>
            </button>

            <button
              type="button"
              @click="openConfigModal(currentRed.key)"
              class="px-4 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold font-mono flex items-center gap-2 transition-all cursor-pointer shadow-xs hover:scale-102"
            >
              <Settings class="w-4 h-4 text-cyan-500" />
              <span>Configurar Canal & Punto Cero</span>
            </button>
          </div>
        </div>

        <!-- Tarjetas de Métricas Ejecutivas del Canal (Punto Cero vs Actual + Deltas Diarios) -->
        <div
          class="grid gap-4 font-mono"
          :class="currentRed.key === 'facebook' ? 'grid-cols-1 sm:grid-cols-3' : (currentRed.key === 'tiktok' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-1 sm:grid-cols-2 md:grid-cols-4')"
        >
          <!-- Seguidores / Suscriptores / Contactos -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
              <span>👥 {{ currentRed.key === 'youtube' ? 'Suscriptores' : (currentRed.key === 'linkedin' ? 'Contactos / Red' : 'Seguidores') }}</span>
              <!-- Mini-métrica de Crecimiento / Pérdida del Día -->
              <span
                v-if="currentRed.delta_seguidores_hoy > 0"
                class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-500/10 inline-flex items-center gap-0.5"
                title="Variación respecto a la última medición"
              >
                <TrendingUp class="w-3 h-3" />
                +{{ Number(currentRed.delta_seguidores_hoy).toLocaleString('es-AR') }} hoy
              </span>
              <span
                v-else-if="currentRed.delta_seguidores_hoy < 0"
                class="text-rose-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-rose-500/10 inline-flex items-center gap-0.5"
                title="Pérdida respecto a la última medición"
              >
                <TrendingDown class="w-3 h-3" />
                {{ Number(currentRed.delta_seguidores_hoy).toLocaleString('es-AR') }} hoy
              </span>
              <span
                v-else
                class="text-slate-400 text-[10px] px-1.5 py-0.5 rounded-md bg-slate-500/10"
              >
                0 hoy
              </span>
            </span>
            <div class="text-2xl font-extrabold text-cyan-600 dark:text-cyan-400">
              {{ Number(currentRed.seguidores_actuales || 0).toLocaleString('es-AR') }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa: <strong class="text-slate-700 dark:text-slate-300">{{ Number(currentRed.seguidores_punto_cero || 0).toLocaleString('es-AR') }}</strong></span>
              <span
                :class="currentRed.crecimiento_neto_seguidores >= 0 ? 'text-emerald-500 font-bold' : 'text-rose-500 font-bold'"
              >
                {{ currentRed.crecimiento_neto_seguidores >= 0 ? '+' : '' }}{{ Number(currentRed.crecimiento_neto_seguidores).toLocaleString('es-AR') }} neto
              </span>
            </div>
          </div>

          <!-- Cuentas Seguidas (Oculto en YouTube) -->
          <div
            v-if="currentRed.key !== 'youtube'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
              <span>🔄 Seguidos</span>
              <span
                v-if="currentRed.delta_seguidos_hoy > 0"
                class="text-cyan-600 dark:text-cyan-400 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-cyan-500/10 inline-flex items-center gap-0.5"
              >
                <TrendingUp class="w-3 h-3" />
                +{{ Number(currentRed.delta_seguidos_hoy).toLocaleString('es-AR') }} hoy
              </span>
              <span
                v-else-if="currentRed.delta_seguidos_hoy < 0"
                class="text-amber-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-amber-500/10 inline-flex items-center gap-0.5"
              >
                <TrendingDown class="w-3 h-3" />
                {{ Number(currentRed.delta_seguidos_hoy).toLocaleString('es-AR') }} hoy
              </span>
              <span
                v-else
                class="text-slate-400 text-[10px] px-1.5 py-0.5 rounded-md bg-slate-500/10"
              >
                0 hoy
              </span>
            </span>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-200">
              {{ Number(currentRed.seguidos_actuales || 0).toLocaleString('es-AR') }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa (Inicio):</span>
              <span class="font-bold text-slate-700 dark:text-slate-300">{{ Number(currentRed.seguidos_punto_cero || 0).toLocaleString('es-AR') }}</span>
            </div>
          </div>

          <!-- Me Gusta Acumulados (Específico Cabecera TikTok) -->
          <div
            v-if="currentRed.key === 'tiktok'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-rose-500/30 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-rose-500 font-bold block flex items-center justify-between">
              <span>❤️ Me Gusta</span>
              <span
                v-if="currentRed.delta_me_gusta_hoy > 0"
                class="text-rose-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-rose-500/10 inline-flex items-center gap-0.5"
              >
                <TrendingUp class="w-3 h-3" />
                +{{ Number(currentRed.delta_me_gusta_hoy).toLocaleString('es-AR') }} hoy
              </span>
              <span
                v-else
                class="text-slate-400 text-[10px] px-1.5 py-0.5 rounded-md bg-slate-500/10"
              >
                0 hoy
              </span>
            </span>
            <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">
              {{ Number(currentRed.me_gusta_totales || 0).toLocaleString('es-AR') }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa: <strong class="text-slate-700 dark:text-slate-300">{{ Number(currentRed.me_gusta_punto_cero || 0).toLocaleString('es-AR') }}</strong></span>
              <span class="text-emerald-500 font-bold">
                +{{ Number(currentRed.crecimiento_neto_me_gusta).toLocaleString('es-AR') }} neto
              </span>
            </div>
          </div>

          <!-- Publicaciones / Videos Totales (Oculto en Facebook) -->
          <div
            v-if="currentRed.key !== 'facebook'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
              <span>{{ currentRed.key === 'tiktok' || currentRed.key === 'youtube' ? '🎬 Videos' : (currentRed.key === 'linkedin' ? '📝 Posts / Artículos' : '📄 Publicaciones') }}</span>
              <span
                v-if="currentRed.delta_posts_hoy > 0"
                class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-500/10 inline-flex items-center gap-0.5"
              >
                <TrendingUp class="w-3 h-3" />
                +{{ Number(currentRed.delta_posts_hoy).toLocaleString('es-AR') }} hoy
              </span>
              <span
                v-else
                class="text-slate-400 text-[10px] px-1.5 py-0.5 rounded-md bg-slate-500/10"
              >
                0 hoy
              </span>
            </span>
            <div class="text-2xl font-extrabold text-slate-800 dark:text-slate-200">
              {{ Number(currentRed.publicaciones_totales || 0).toLocaleString('es-AR') }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa: <strong class="text-slate-700 dark:text-slate-300">{{ Number(currentRed.publicaciones_punto_cero || 0).toLocaleString('es-AR') }}</strong></span>
              <span class="text-emerald-500 font-bold">
                +{{ Number(currentRed.crecimiento_neto_posts).toLocaleString('es-AR') }} neto
              </span>
            </div>
          </div>

          <!-- Visualizaciones Totales (Específico Cabecera YouTube) -->
          <div
            v-if="currentRed.key === 'youtube'"
            class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-red-500/30 space-y-1"
          >
            <span class="text-[11px] uppercase tracking-wider text-red-500 font-bold block flex items-center justify-between">
              <span>👁️ Visualizaciones</span>
              <span
                v-if="currentRed.delta_views_hoy > 0"
                class="text-emerald-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-500/10 inline-flex items-center gap-0.5"
              >
                <TrendingUp class="w-3 h-3" />
                +{{ Number(currentRed.delta_views_hoy).toLocaleString('es-AR') }} hoy
              </span>
              <span
                v-else
                class="text-slate-400 text-[10px] px-1.5 py-0.5 rounded-md bg-slate-500/10"
              >
                0 hoy
              </span>
            </span>
            <div class="text-2xl font-extrabold text-red-600 dark:text-red-400">
              {{ Number(currentRed.visualizaciones_totales || 0).toLocaleString('es-AR') }}
            </div>
            <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/50 dark:border-slate-800/50">
              <span>Punto Alfa: <strong class="text-slate-700 dark:text-slate-300">{{ Number(currentRed.visualizaciones_punto_cero || 0).toLocaleString('es-AR') }}</strong></span>
              <span class="text-emerald-500 font-bold">
                +{{ Number(currentRed.crecimiento_neto_visualizaciones).toLocaleString('es-AR') }} neto
              </span>
            </div>
          </div>

          <!-- Fecha Punto Cero & Sincronización 24h -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
            <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold block flex items-center justify-between">
              <span>📅 Punto Cero & 24h</span>
              <span class="text-[9px] px-1.5 py-0.5 rounded font-mono font-bold bg-cyan-500/10 text-cyan-500">
                Auto-Cron
              </span>
            </span>
            <div class="text-xs font-bold text-slate-800 dark:text-slate-200 pt-1">
              Inicio: <span class="font-mono text-cyan-600 dark:text-cyan-400">{{ currentRed.fecha_punto_cero || 'No registrada' }}</span>
            </div>
            <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-200/50 dark:border-slate-800/50 flex items-center justify-between">
              <span>Lectura 24h:</span>
              <span class="font-bold text-emerald-500 flex items-center gap-1">
                <Clock class="w-3 h-3" />
                {{ currentRed.ultima_auditoria_at || 'Hoy' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Banner si el canal está inactivo / pendiente -->
        <div
          v-if="currentRed.color_estado === 'rojo'"
          class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-between flex-wrap gap-3"
        >
          <div class="flex items-center gap-2.5">
            <AlertCircle class="w-5 h-5 text-rose-500 shrink-0" />
            <p class="text-xs text-rose-600 dark:text-rose-300">
              Este canal digital figura como <strong>Inactivo o No Vinculado</strong>. Puedes configurarlo y comenzar a auditarlo en cualquier momento.
            </p>
          </div>
          <button
            type="button"
            @click="openConfigModal(currentRed.key)"
            class="px-3.5 py-1.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs font-mono transition-all cursor-pointer"
          >
            Configurar Canal Ahora
          </button>
        </div>
      </div>

      <!-- 4. RESUMEN DE RENDIMIENTO & ACCESO AL MURO SOCIAL DE PUBLICACIONES -->
      <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
        <!-- Cabecera de la Sección de Publicaciones -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-5">
          <div>
            <div class="flex items-center gap-2.5 flex-wrap">
              <div class="flex items-center justify-center w-8 h-8 rounded-xl shadow-2xs" :class="getSocialMeta(currentRed.key).bgLight">
                <svg v-if="currentRed.key === 'instagram'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: getSocialMeta(currentRed.key).color }">
                  <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                  <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                  <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
                </svg>
                <svg v-else-if="currentRed.key === 'facebook'" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                  <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <svg v-else-if="currentRed.key === 'threads'" class="w-4 h-4 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12.186 24C5.454 24 0 18.675 0 12.103 0 5.53 5.454.205 12.186.205c6.733 0 12.186 5.325 12.186 11.898 0 6.572-5.453 11.897-12.186 11.897zm-.055-2.285c5.385 0 9.771-4.27 9.771-9.512 0-5.243-4.386-9.513-9.77-9.513-5.385 0-9.772 4.27-9.772 9.513 0 5.242 4.387 9.512 9.772 9.512zm4.786-9.284c-.035 3.328-1.929 5.353-4.897 5.353-2.735 0-4.636-1.748-4.636-4.52 0-3.053 2.193-4.654 4.887-4.654 1.344 0 2.507.411 3.25 1.134l-1.378 1.458c-.469-.475-1.12-.72-1.85-.72-1.458 0-2.457.946-2.457 2.659 0 1.624 1.054 2.573 2.395 2.573 1.584 0 2.373-.974 2.45-2.283H12.15V9.458h4.722v2.973z"/>
                </svg>
                <svg v-else-if="currentRed.key === 'tiktok'" class="w-4 h-4 text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                </svg>
                <svg v-else-if="currentRed.key === 'x_twitter'" class="w-4 h-4 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                <svg v-else-if="currentRed.key === 'youtube'" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
                <svg v-else-if="currentRed.key === 'linkedin'" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                  <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                </svg>
              </div>
              <h3 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-slate-100">
                Rendimiento de Publicaciones en {{ currentRed.nombre }}
              </h3>
              <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
                {{ currentRedPublicaciones.length }} {{ currentRedPublicaciones.length === 1 ? 'post auditado' : 'posts auditados' }}
              </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              Métricas consolidadas de engagement, alcance y humor social de las publicaciones en este canal.
            </p>
          </div>

          <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto">
            <button
              v-if="canWrite && currentRed.perfil_id && postsIn15DaysCount > 0"
              type="button"
              @click="sincronizarPublicacionesRecientes"
              :disabled="isSyncingRecientes"
              class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30 font-bold text-xs transition-all cursor-pointer disabled:opacity-50"
              :title="`Sincronizar métricas en vivo de publicaciones con menos de 15 días (${postsIn15DaysCount} en ventana activa)`"
            >
              <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isSyncingRecientes }" />
              <span>{{ isSyncingRecientes ? 'Sincronizando 15d...' : 'Sincronizar (15 Días)' }}</span>
              <span class="px-1.5 py-0.2 rounded-full bg-cyan-500 text-slate-950 text-[10px] font-mono font-black">
                {{ postsIn15DaysCount }}
              </span>
            </button>

            <Link
              v-if="canWrite"
              :href="`/feed?filtro=propio&plataforma=${currentRed.key}`"
              class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/20 transition-all hover:scale-102 cursor-pointer"
              :title="`Abrir muro de publicaciones y feed completo de ${currentRed.nombre}`"
            >
              <Radio class="w-4 h-4" />
              <span>Ver en Feed / Muro Social</span>
            </Link>
          </div>
        </div>

        <!-- Barra Resumen de Métricas de Publicaciones en esta Red -->
        <div v-if="currentRedPublicaciones.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 font-mono text-xs">
          <!-- 🔥 1. Reacciones & Interacciones Totales (Engagement Bruto) -->
          <div
            class="p-3.5 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-700 dark:text-cyan-300"
            :title="`Score Ponderado de Tracción: ${currentRedStats.scoreImpactoTotal} pts (Likes [x1] + Coment [x3] + Shares [x5] + Reposts [x10])`"
          >
            <div class="flex items-center justify-between">
              <span class="text-[10px] uppercase text-cyan-600 dark:text-cyan-400 font-extrabold flex items-center gap-1">
                🔥 Interacciones
              </span>
              <span class="text-[9px] px-1.5 py-0.2 rounded bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 font-bold">
                Engagement
              </span>
            </div>
            <span class="text-xl font-extrabold text-cyan-600 dark:text-cyan-400 block mt-1">
              {{ Number(currentRedStats.totalInteracciones).toLocaleString() }}
            </span>
            <span class="text-[9px] text-slate-500 dark:text-slate-400 font-sans block truncate mt-0.5">
              Likes + Coment + Reposts + Shares
            </span>
          </div>

          <!-- ❤️ 2. Total Me Gusta -->
          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <span class="text-[10px] uppercase text-slate-400 font-bold block">❤️ Total Me Gusta</span>
            <span class="text-xl font-extrabold text-rose-500 block mt-1">
              {{ Number(currentRedStats.totalLikes).toLocaleString() }}
            </span>
            <span class="text-[9px] text-slate-400 font-sans block mt-0.5">Reacciones directas</span>
          </div>

          <!-- 💬 3. Total Comentarios -->
          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <span class="text-[10px] uppercase text-slate-400 font-bold block">💬 Comentarios</span>
            <span class="text-xl font-extrabold text-blue-500 block mt-1">
              {{ Number(currentRedStats.totalComentarios).toLocaleString() }}
            </span>
            <span class="text-[9px] text-slate-400 font-sans block mt-0.5">Mensajes recibidos</span>
          </div>

          <!-- 👁️ 4. Alcance / Vistas Totales -->
          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <span class="text-[10px] uppercase text-slate-400 font-bold block">👁️ Alcance Total</span>
            <span class="text-xl font-extrabold text-slate-700 dark:text-slate-300 block mt-1">
              {{ Number(currentRedStats.totalVistas).toLocaleString() }}
            </span>
            <span class="text-[9px] text-slate-400 font-sans block mt-0.5">Vistas o insights</span>
          </div>

          <!-- 📢 5. Pauta Invertida -->
          <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
            <span class="text-[10px] uppercase text-slate-400 font-bold block">📢 Pauta Invertida</span>
            <span class="text-xl font-extrabold text-violet-500 block mt-1">
              ${{ Number(currentRedStats.totalPauta).toLocaleString('es-AR') }}
            </span>
            <span class="text-[9px] text-slate-400 font-sans block mt-0.5">Presupuesto asignado</span>
          </div>
        </div>


      </div>
    </div>



    <!-- MODAL PARA CONFIGURAR CANAL & PUNTO CERO -->
    <div
      v-if="isConfigModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl" :class="getSocialMeta(currentRed.key).bgLight">
              <!-- Instagram -->
              <svg v-if="currentRed.key === 'instagram'" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: getSocialMeta(currentRed.key).color }">
                <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
              </svg>
              <!-- Facebook -->
              <svg v-else-if="currentRed.key === 'facebook'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
              <!-- Threads -->
              <svg v-else-if="currentRed.key === 'threads'" class="w-5 h-5 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.186 24C5.454 24 0 18.675 0 12.103 0 5.53 5.454.205 12.186.205c6.733 0 12.186 5.325 12.186 11.898 0 6.572-5.453 11.897-12.186 11.897zm-.055-2.285c5.385 0 9.771-4.27 9.771-9.512 0-5.243-4.386-9.513-9.77-9.513-5.385 0-9.772 4.27-9.772 9.513 0 5.242 4.387 9.512 9.772 9.512zm4.786-9.284c-.035 3.328-1.929 5.353-4.897 5.353-2.735 0-4.636-1.748-4.636-4.52 0-3.053 2.193-4.654 4.887-4.654 1.344 0 2.507.411 3.25 1.134l-1.378 1.458c-.469-.475-1.12-.72-1.85-.72-1.458 0-2.457.946-2.457 2.659 0 1.624 1.054 2.573 2.395 2.573 1.584 0 2.373-.974 2.45-2.283H12.15V9.458h4.722v2.973z"/>
              </svg>
              <!-- TikTok -->
              <svg v-else-if="currentRed.key === 'tiktok'" class="w-5 h-5 text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
              </svg>
              <!-- X / Twitter -->
              <svg v-else-if="currentRed.key === 'x_twitter'" class="w-5 h-5 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
              <!-- YouTube -->
              <svg v-else-if="currentRed.key === 'youtube'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              <!-- LinkedIn -->
              <svg v-else-if="currentRed.key === 'linkedin'" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <Settings class="w-4 h-4 text-cyan-500" />
                <span>Configurar Canal: {{ currentRed.nombre }}</span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Punto Cero, enlace oficial, estado de canal y lector con 1 clic.
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="isConfigModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="savePerfilSocial" class="space-y-5">
          <!-- A. Enlace, Lector Automático y Estados -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-4">
            <!-- Enlace con Botón Auto-Lector -->
            <div>
              <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                  1. Enlace Directo al Perfil de {{ currentRed.nombre }} (URL)
                </label>
                <button
                  type="button"
                  @click="fetchScrapedData"
                  :disabled="isScraping || !formRed.url_perfil"
                  class="px-3 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-xs font-mono flex items-center gap-1.5 transition-all shadow-sm cursor-pointer disabled:opacity-50"
                  title="Leer automáticamente foto, seguidores, seguidos y publicaciones con 1 clic"
                >
                  <Sparkles class="w-3.5 h-3.5" />
                  <span>{{ isScraping ? 'Leyendo datos...' : '⚡ Leer Datos & Foto con 1 Clic' }}</span>
                </button>
              </div>

              <div class="relative">
                <input
                  v-model="formRed.url_perfil"
                  type="url"
                  :placeholder="getSocialPlaceholder(currentRed.key)"
                  class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                />
                <Link2 class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              </div>

              <div v-if="scrapeMessage" class="mt-2 flex items-center gap-2 text-xs font-mono" :class="scrapeSuccess ? 'text-emerald-500' : 'text-amber-500'">
                <CheckCircle v-if="scrapeSuccess" class="w-4 h-4" />
                <AlertCircle v-else class="w-4 h-4" />
                <span>{{ scrapeMessage }}</span>
              </div>
            </div>

            <!-- Handle & Switches -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-200 dark:border-slate-800">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Usuario / Handle *
                </label>
                <input
                  v-model="formRed.handle_usuario"
                  type="text"
                  required
                  :placeholder="getHandlePlaceholder(currentRed.key)"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100"
                />
              </div>

              <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div>
                  <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 block">Canal Activo</span>
                  <span class="text-[10px] text-emerald-500 font-semibold">🟢 Pestaña Verde (Con actividad)</span>
                </div>
                <input
                  v-model="formRed.esta_activo"
                  type="checkbox"
                  class="w-5 h-5 rounded text-emerald-500 focus:ring-emerald-500"
                />
              </div>

              <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div>
                  <span class="text-xs font-bold text-blue-500 dark:text-blue-400 block">Cuenta Verificada</span>
                  <span class="text-[10px] text-blue-400 font-semibold">🔵 Pestaña Azul (Oficial)</span>
                </div>
                <input
                  v-model="formRed.esta_verificado"
                  type="checkbox"
                  class="w-5 h-5 rounded text-blue-500 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- B. FOTO DE PERFIL & TABLA ÚNICA DE NÚMEROS (PUNTO CERO) -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-2">
              <Flag class="w-4 h-4 text-cyan-500" />
              <span>2. Foto de Perfil & Punto Cero (Línea de Base de Inicio)</span>
            </h3>

            <!-- Preview Foto & Input URL -->
            <div class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex-wrap">
              <div class="relative shrink-0">
                <img
                  :src="formRed.foto_perfil_url || candidato.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(formRed.handle_usuario || 'User')}&background=0f172a&color=06b6d4`"
                  alt="Foto Perfil"
                  referrerpolicy="no-referrer"
                  class="w-14 h-14 rounded-2xl object-cover border-2 border-cyan-500 shadow-sm"
                />
              </div>
              <div class="flex-1 min-w-[240px]">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Foto de Perfil (URL Extraída)
                </label>
                <div class="relative">
                  <input
                    v-model="formRed.foto_perfil_url"
                    type="url"
                    placeholder="https://..."
                    class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-slate-100"
                  />
                  <ImageIcon class="w-4 h-4 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                </div>
              </div>
            </div>

            <!-- Tabla Única de Métricas del Punto Cero -->
            <div
              class="grid gap-3 font-mono pt-1"
              :class="currentRed.key === 'facebook' ? 'grid-cols-1 sm:grid-cols-3' : (currentRed.key === 'tiktok' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-2 sm:grid-cols-4')"
            >
              <!-- Seguidores / Suscriptores / Contactos -->
              <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-cyan-500/40 text-center space-y-1">
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  👥 {{ currentRed.key === 'youtube' ? 'Suscriptores Iniciales' : (currentRed.key === 'linkedin' ? 'Contactos Iniciales' : 'Seguidores Iniciales') }}
                </span>
                <input
                  v-model.number="formRed.seguidores_actuales"
                  type="number"
                  min="0"
                  placeholder="ej. 1359"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-cyan-600 dark:text-cyan-400"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Punto Alfa de Inicio</span>
              </div>

              <!-- Seguidos (Oculto en YouTube) -->
              <div
                v-if="currentRed.key !== 'youtube'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  🔄 Seguidos
                </span>
                <input
                  v-model.number="formRed.seguidos_actuales"
                  type="number"
                  min="0"
                  placeholder="ej. 588"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-slate-800 dark:text-slate-200"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Cuentas seguidas</span>
              </div>

              <!-- Me Gusta Iniciales (Específico TikTok) -->
              <div
                v-if="currentRed.key === 'tiktok'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-rose-500/40 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-rose-500 font-bold block">
                  ❤️ Me Gusta Iniciales
                </span>
                <input
                  v-model.number="formRed.me_gusta_totales"
                  type="number"
                  min="0"
                  placeholder="ej. 7063"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-rose-600 dark:text-rose-400"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Likes acumulados</span>
              </div>

              <!-- Publicaciones / Videos Totales (Oculto en Facebook porque no aplica) -->
              <div
                v-if="currentRed.key !== 'facebook'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  {{ currentRed.key === 'tiktok' || currentRed.key === 'youtube' ? '🎬 Videos' : (currentRed.key === 'linkedin' ? '📝 Posts / Artículos' : '📄 Publicaciones Totales') }}
                </span>
                <input
                  v-model.number="formRed.publicaciones_totales"
                  type="number"
                  min="0"
                  placeholder="ej. 64"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-slate-800 dark:text-slate-200"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Videos al comenzar</span>
              </div>

              <!-- Visualizaciones Iniciales (Específico YouTube) -->
              <div
                v-if="currentRed.key === 'youtube'"
                class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border-2 border-red-500/40 text-center space-y-1"
              >
                <span class="text-[10px] uppercase tracking-wider text-red-500 font-bold block">
                  👁️ Visualizaciones Iniciales
                </span>
                <input
                  v-model.number="formRed.visualizaciones_totales"
                  type="number"
                  min="0"
                  placeholder="ej. 6210"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-red-600 dark:text-red-400"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Vistas totales canal</span>
              </div>

              <!-- Fecha Punto Cero -->
              <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-1">
                <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold block">
                  📅 Fecha de Comienzo
                </span>
                <input
                  v-model="formRed.fecha_punto_cero"
                  type="date"
                  class="w-full text-center px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200"
                />
                <span class="text-[9px] text-slate-400 block font-mono">Nacimiento auditoría</span>
              </div>
            </div>
          </div>

          <!-- Submit Button & Cancel -->
          <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="isConfigModalOpen = false"
              class="px-4 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="formRed.processing"
              class="px-6 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/25 flex items-center gap-2 cursor-pointer transition-all hover:scale-102"
            >
              <Save class="w-4 h-4" />
              <span>{{ formRed.processing ? 'Guardando...' : `Guardar y Establecer Punto Cero en ${currentRed.nombre}` }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal para Editar Datos Generales del Candidato -->
    <div
      v-if="isEditingCandidato"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
          <h3 class="font-bold text-base text-slate-900 dark:text-slate-100 flex items-center gap-2">
            <Edit3 class="w-5 h-5 text-cyan-500" />
            <span>Editar Datos de Mi Candidato</span>
          </h3>
          <button
            type="button"
            @click="isEditingCandidato = false"
            class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
          >
            &times;
          </button>
        </div>

        <form @submit.prevent="saveCandidato" class="space-y-4">
          <!-- 1. NOMBRE DE LA CAMPAÑA / WORKSPACE (CABECERA PRINCIPAL) -->
          <div class="p-4 rounded-2xl bg-cyan-500/5 border border-cyan-500/20 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400 font-mono flex items-center gap-1.5">
                <Flag class="w-4 h-4 text-cyan-500" />
                <span>Nombre de la Campaña Activa (Cabecera Superior)</span>
              </span>
              <span class="text-[10px] text-cyan-500 font-mono font-bold uppercase">Workspace</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Título de la Campaña / Cliente *
                </label>
                <input
                  v-model="formCandidato.workspace_nombre"
                  type="text"
                  required
                  placeholder="ej. Campaña Sisterna — Albardón 2027"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-bold focus:ring-2 focus:ring-cyan-500"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Ciclo / Año Electoral
                </label>
                <select
                  v-model="formCandidato.ciclo_campana_id"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-bold focus:ring-2 focus:ring-cyan-500"
                >
                  <option v-for="c in ciclos" :key="c.id" :value="c.id">
                    {{ c.nombre }} ({{ c.anio }})
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre Completo del Candidato *</label>
            <input
              v-model="formCandidato.nombre_completo"
              type="text"
              required
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Partido / Coalición *</label>
              <input
                v-model="formCandidato.partido_coalicion"
                type="text"
                required
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cargo al que aspira</label>
              <input
                v-model="formCandidato.cargo_aspirado"
                type="text"
                placeholder="ej. Intendente Municipal"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
            </div>
          </div>

          <!-- DATOS GEOGRÁFICOS Y ELECTORALES -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-1.5">
                <MapPin class="w-4 h-4 text-cyan-500" />
                <span>Territorio Geográfico & Padrón Electoral</span>
              </span>
              <span class="text-[10px] text-cyan-500 font-mono uppercase font-bold">Base Electoral</span>
            </div>

            <div>
              <div class="flex items-center justify-between mb-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                  Nombre del Territorio / Departamento / Municipio *
                </label>
                <button
                  type="button"
                  @click="autoDetectTerritorio"
                  :disabled="isDetectingTerritorio || !formCandidato.territorio_nombre"
                  class="text-[10px] font-mono text-cyan-500 hover:text-cyan-400 font-bold flex items-center gap-1 cursor-pointer disabled:opacity-50"
                >
                  <Sparkles class="w-3 h-3" />
                  <span>{{ isDetectingTerritorio ? 'Detectando...' : '⚡ Autocompletar con Georef' }}</span>
                </button>
              </div>
              <input
                v-model="formCandidato.territorio_nombre"
                type="text"
                required
                placeholder="ej. Albardón o Rawson"
                class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              />
              <div v-if="detectTerritorioMessage" class="mt-1.5 text-[11px] font-mono" :class="detectTerritorioSuccess ? 'text-emerald-500 font-bold' : 'text-amber-500'">
                {{ detectTerritorioMessage }}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 font-mono">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Padrón Electoral (Votantes) *
                </label>
                <input
                  v-model.number="formCandidato.padron_electoral"
                  type="number"
                  min="0"
                  placeholder="ej. 24500"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-extrabold text-emerald-500"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Población Total Estimada
                </label>
                <input
                  v-model.number="formCandidato.poblacion_total"
                  type="number"
                  min="0"
                  placeholder="ej. 31000"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100"
                />
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Foto / Avatar URL</label>
            <input
              v-model="formCandidato.avatar_url"
              type="url"
              placeholder="https://..."
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Biografía / Eje de Campaña</label>
            <textarea
              v-model="formCandidato.bio_resumen"
              rows="3"
              class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
            ></textarea>
          </div>

          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="isEditingCandidato = false"
              class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="formCandidato.processing"
              class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold shadow-sm cursor-pointer"
            >
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL PARA CARGAR PUBLICACIÓN EN LA RED SOCIAL ACTIVA -->
    <div
      v-if="isPostModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs"
    >
      <div class="w-full max-w-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-11 h-11 rounded-2xl shadow-xs" :class="getSocialMeta(currentRed.key).bgLight">
              <svg v-if="currentRed.key === 'instagram'" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: getSocialMeta(currentRed.key).color }">
                <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
              </svg>
              <svg v-else-if="currentRed.key === 'facebook'" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
              <svg v-else-if="currentRed.key === 'threads'" class="w-6 h-6 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.186 24C5.454 24 0 18.675 0 12.103 0 5.53 5.454.205 12.186.205c6.733 0 12.186 5.325 12.186 11.898 0 6.572-5.453 11.897-12.186 11.897zm-.055-2.285c5.385 0 9.771-4.27 9.771-9.512 0-5.243-4.386-9.513-9.77-9.513-5.385 0-9.772 4.27-9.772 9.513 0 5.242 4.387 9.512 9.772 9.512zm4.786-9.284c-.035 3.328-1.929 5.353-4.897 5.353-2.735 0-4.636-1.748-4.636-4.52 0-3.053 2.193-4.654 4.887-4.654 1.344 0 2.507.411 3.25 1.134l-1.378 1.458c-.469-.475-1.12-.72-1.85-.72-1.458 0-2.457.946-2.457 2.659 0 1.624 1.054 2.573 2.395 2.573 1.584 0 2.373-.974 2.45-2.283H12.15V9.458h4.722v2.973z"/>
              </svg>
              <svg v-else-if="currentRed.key === 'tiktok'" class="w-6 h-6 text-cyan-500 dark:text-cyan-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.24 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
              </svg>
              <svg v-else-if="currentRed.key === 'x_twitter'" class="w-6 h-6 text-slate-900 dark:text-slate-100" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
              <svg v-else-if="currentRed.key === 'youtube'" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
              <svg v-else-if="currentRed.key === 'linkedin'" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" :style="{ color: getSocialMeta(currentRed.key).color }">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
              </svg>
            </div>
            <div>
              <h3 class="font-extrabold text-lg text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span>Cargar Publicación en {{ currentRed.nombre }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-mono font-bold bg-cyan-500/10 text-cyan-500">
                  {{ candidato.nombre_completo }}
                </span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Audita reels, videos, fotos, métricas de engagement, eje temático y pauta.
              </p>
            </div>
          </div>

          <button
            type="button"
            @click="isPostModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="submitPublicacion" class="space-y-5">
          <!-- 1. Enlace & Autodetección -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center justify-between">
              <span class="flex items-center gap-1.5">
                <Link2 class="w-4 h-4 text-cyan-500" />
                <span>1. Enlace Oficial o Código de Inserción (URL / Embed)</span>
              </span>
              <span class="text-[10px] text-cyan-500 font-semibold lowercase">
                ej. https://www.instagram.com/reel/...
              </span>
            </label>

            <!-- URL Input + Botón Autocompletar con 1 Clic -->
            <div class="flex flex-col sm:flex-row gap-2">
              <div class="relative flex-1">
                <input
                  v-model="formPost.url_post"
                  type="text"
                  placeholder="Pega el enlace de Instagram o el bloque de inserción..."
                  class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                />
                <Link2 class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              </div>
              <button
                type="button"
                @click="scrapePostData"
                :disabled="isScrapingPost || !formPost.url_post"
                class="px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 disabled:opacity-50 text-slate-950 font-bold text-xs shadow-xs flex items-center justify-center gap-1.5 transition-all shrink-0 cursor-pointer"
                title="Extraer automáticamente el copy, likes, comentarios, fecha y formato"
              >
                <RefreshCw v-if="isScrapingPost" class="w-3.5 h-3.5 animate-spin" />
                <Sparkles v-else class="w-3.5 h-3.5 fill-current" />
                <span>{{ isScrapingPost ? 'Extrayendo...' : '⚡ Autocompletar (1 Clic)' }}</span>
              </button>
            </div>

            <!-- Feedback Message -->
            <div
              v-if="scrapePostFeedback"
              class="p-2.5 rounded-xl text-xs font-semibold flex items-center gap-2"
              :class="scrapePostFeedback.type === 'success' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20'"
            >
              <CheckCircle v-if="scrapePostFeedback.type === 'success'" class="w-4 h-4 shrink-0" />
              <AlertCircle v-else class="w-4 h-4 shrink-0" />
              <span>{{ scrapePostFeedback.text }}</span>
            </div>

            <!-- Formato & Fecha Adaptados a la Red Activa -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                  <span>Tipo de Formato ({{ currentRed.nombre }}) *</span>
                  <span class="text-[10px] text-cyan-500 font-semibold lowercase">Específico de {{ currentRed.nombre }}</span>
                </label>
                <select
                  v-model="formPost.tipo_formato"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-semibold text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                >
                  <option
                    v-for="fmt in currentPlatformFormats"
                    :key="fmt.value"
                    :value="fmt.value"
                  >
                    {{ fmt.label }}
                  </option>
                </select>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                  {{ currentPlatformFormats.find(f => f.value === formPost.tipo_formato)?.desc || 'Formato de publicación' }}
                </p>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                  Fecha y Hora de Publicación *
                </label>
                <input
                  v-model="formPost.fecha_publicacion"
                  type="datetime-local"
                  required
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100"
                />
              </div>
            </div>
          </div>

          <!-- 2. Estrategia de Difusión & Pauta Específica por Red -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono flex items-center gap-1.5">
                <DollarSign class="w-4 h-4 text-cyan-500" />
                <span>2. Tipo de Difusión & Pauta en {{ currentRed.nombre }}</span>
              </label>
              <span class="text-[10px] text-cyan-500 font-bold uppercase font-mono">
                Estrategia de Difusión
              </span>
            </div>

            <!-- Grid de Tipos de Difusión Propios de Instagram / Red -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              <button
                v-for="dif in currentPlatformDiffusionTypes"
                :key="dif.value"
                type="button"
                @click="formPost.tipo_pauta = dif.value"
                class="p-3 rounded-2xl border-2 text-left transition-all cursor-pointer relative overflow-hidden"
                :class="[
                  formPost.tipo_pauta === dif.value
                    ? 'border-cyan-500 bg-cyan-500/10 shadow-sm'
                    : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-700'
                ]"
              >
                <div class="flex items-center justify-between gap-2 mb-1">
                  <span class="text-xs font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                    <span
                      class="w-2.5 h-2.5 rounded-full shrink-0"
                      :class="formPost.tipo_pauta === dif.value ? 'bg-cyan-500' : 'bg-slate-300 dark:bg-slate-700'"
                    ></span>
                    <span>{{ dif.label }}</span>
                  </span>
                  <span
                    class="text-[9px] font-mono px-1.5 py-0.5 rounded font-bold uppercase"
                    :class="[
                      dif.isPaid
                        ? 'bg-violet-500/15 text-violet-600 dark:text-violet-400'
                        : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'
                    ]"
                  >
                    {{ dif.badge }}
                  </span>
                </div>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug pl-4">
                  {{ dif.desc }}
                </p>
              </button>
            </div>

            <!-- Campos adicionales si es Pauta Paga / Impulsada / Colaboración -->
            <div v-if="isPaidDiffusion" class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-slate-200 dark:border-slate-800 animate-fadeIn">
              <div>
                <label class="block text-xs font-bold text-violet-600 dark:text-violet-400 mb-1 flex items-center justify-between">
                  <span>Monto Invertido en Pauta ($ ARS — Pesos) *</span>
                  <span class="text-[10px] font-mono">Presupuesto Asignado</span>
                </label>
                <input
                  v-model.number="formPost.monto_invertido_pauta"
                  type="number"
                  min="0"
                  placeholder="ej. 25000"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-violet-500/40 text-xs sm:text-sm font-mono font-bold text-violet-600 dark:text-violet-400 focus:ring-2 focus:ring-violet-500"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                  <span>Vistas / Alcance Pagado Estimado</span>
                  <span class="text-[10px] font-mono">Impactos Pauta</span>
                </label>
                <input
                  v-model.number="formPost.vistas_pagadas"
                  type="number"
                  min="0"
                  placeholder="ej. 15000"
                  class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-mono text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
                />
              </div>
            </div>
          </div>

          <!-- 3. Eje Temático & Resumen -->
          <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono mb-1.5 flex items-center justify-between">
                <span>3. Eje Temático de Campaña</span>
                <span class="text-[10px] text-slate-400 lowercase">Clasificación estratégica</span>
              </label>
              <select
                v-model="formPost.eje_tematico_id"
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-semibold focus:ring-2 focus:ring-cyan-500"
              >
                <option :value="null">-- Seleccionar Eje Temático --</option>
                <option v-for="eje in ejes" :key="eje.id" :value="eje.id">
                  🎯 {{ eje.nombre }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono mb-1.5">
                Texto / Copy o Resumen de la Publicación *
              </label>
              <textarea
                v-model="formPost.contenido_resumen"
                rows="3"
                required
                placeholder="Escribe el texto de la publicación, tema abordado o propuesta comunicacional..."
                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-cyan-500"
              ></textarea>
            </div>
          </div>

          <!-- 4. Métricas Clave de Rendimiento (Adaptadas a Instagram: Likes, Comentarios, Vistas, Guardados) -->
          <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 font-mono">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                <Flame class="w-4 h-4 text-cyan-500" />
                <span>4. Métricas de Interacción en {{ currentRed.nombre }}</span>
              </label>
              <span class="text-[10px] text-cyan-500 font-bold">Fast Entry</span>
            </div>

            <div
              class="grid gap-3"
              :class="currentRed.key === 'instagram' ? 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5' : 'grid-cols-2 sm:grid-cols-4'"
            >
              <!-- Likes -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="text-[10px] uppercase font-bold text-rose-500 block">❤️ Me Gusta</span>
                <input
                  v-model.number="formPost.total_likes"
                  type="number"
                  min="0"
                  placeholder="ej. 81"
                  class="w-full text-center px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-rose-500"
                />
              </div>

              <!-- Comentarios -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="text-[10px] uppercase font-bold text-blue-500 block">💬 Mensajes / Coment.</span>
                <input
                  v-model.number="formPost.total_comentarios"
                  type="number"
                  min="0"
                  placeholder="ej. 8"
                  class="w-full text-center px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-blue-500"
                />
              </div>

              <!-- Vistas / Plays -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="text-[10px] uppercase font-bold text-cyan-600 dark:text-cyan-400 block">
                  {{ currentRed.key === 'instagram' ? '👁️ Plays / Alcance' : '👁️ Vistas' }}
                </span>
                <input
                  v-model.number="formPost.vistas_organicas"
                  type="number"
                  min="0"
                  placeholder="ej. 1200"
                  class="w-full text-center px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-cyan-600 dark:text-cyan-400"
                />
              </div>

              <!-- Compartidos -->
              <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-500 block">🔄 Compartidos</span>
                <input
                  v-model.number="formPost.total_compartidos"
                  type="number"
                  min="0"
                  placeholder="ej. 0"
                  class="w-full text-center px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-base font-extrabold text-slate-800 dark:text-slate-200"
                />
              </div>

              <!-- Guardados (Específico de Instagram) -->
              <div
                v-if="currentRed.key === 'instagram'"
                class="p-3 rounded-xl bg-white dark:bg-slate-900 border-2 border-amber-500/30 space-y-1"
              >
                <span class="text-[10px] uppercase font-bold text-amber-500 block flex items-center justify-between">
                  <span>🔖 Guardados</span>
                  <span class="text-[8px] bg-amber-500/15 text-amber-600 dark:text-amber-400 px-1 rounded font-bold">Algoritmo</span>
                </span>
                <input
                  v-model.number="formPost.total_guardados"
                  type="number"
                  min="0"
                  placeholder="ej. 12"
                  class="w-full text-center px-2 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-amber-500/20 text-base font-extrabold text-amber-500"
                />
              </div>
            </div>

            <!-- Desplegable Emociones Avanzadas & Termómetro -->
            <div class="pt-2 border-t border-slate-200 dark:border-slate-800">
              <button
                type="button"
                @click="showAdvancedEmotions = !showAdvancedEmotions"
                class="text-xs text-cyan-500 hover:text-cyan-400 font-bold flex items-center gap-1.5 cursor-pointer"
              >
                <Sparkles class="w-3.5 h-3.5" />
                <span>{{ showAdvancedEmotions ? 'Ocultar Desglose Emoji & Termómetro' : '⚡ Desglosar Emojis Granulares & Termómetro de Humor Social' }}</span>
              </button>

              <div v-if="showAdvancedEmotions" class="mt-3 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="grid grid-cols-4 sm:grid-cols-7 gap-2 text-center text-xs">
                  <div>
                    <label class="block text-slate-500 font-bold mb-1">👍 Likes</label>
                    <input v-model.number="formPost.me_gusta" type="number" min="0" class="w-full text-center px-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold" />
                  </div>
                  <div>
                    <label class="block text-rose-500 font-bold mb-1">❤️ Love</label>
                    <input v-model.number="formPost.me_encanta" type="number" min="0" class="w-full text-center px-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-rose-500" />
                  </div>
                  <div>
                    <label class="block text-amber-500 font-bold mb-1">🥰 Care</label>
                    <input v-model.number="formPost.me_importa" type="number" min="0" class="w-full text-center px-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-amber-500" />
                  </div>
                  <div>
                    <label class="block text-amber-500 font-bold mb-1">😂 Haha</label>
                    <input v-model.number="formPost.me_divierte" type="number" min="0" class="w-full text-center px-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-amber-500" />
                  </div>
                  <div>
                    <label class="block text-purple-500 font-bold mb-1">😮 Wow</label>
                    <input v-model.number="formPost.me_asombra" type="number" min="0" class="w-full text-center px-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-purple-500" />
                  </div>
                  <div>
                    <label class="block text-blue-500 font-bold mb-1">😢 Sad</label>
                    <input v-model.number="formPost.me_entristece" type="number" min="0" class="w-full text-center px-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-blue-500" />
                  </div>
                  <div>
                    <label class="block text-rose-600 font-bold mb-1">😡 Angry</label>
                    <input v-model.number="formPost.me_enoja" type="number" min="0" class="w-full text-center px-1 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-rose-500/30 text-xs font-bold text-rose-600" />
                  </div>
                </div>

                <!-- Termómetro de Humor Social (1-5 estrellas) -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-200 dark:border-slate-800 flex-wrap gap-2">
                  <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Termómetro de Humor Social:
                  </span>
                  <div class="flex items-center gap-1.5">
                    <button
                      v-for="star in 5"
                      :key="star"
                      type="button"
                      @click="formPost.termometro_humor_social = star"
                      class="p-1 text-amber-400 hover:scale-110 transition-transform cursor-pointer"
                    >
                      <Star
                        class="w-5 h-5"
                        :class="star <= formPost.termometro_humor_social ? 'fill-amber-400 text-amber-400' : 'text-slate-300 dark:text-slate-700'"
                      />
                    </button>
                    <span class="text-xs font-bold text-amber-500 ml-1">
                      ({{ formPost.termometro_humor_social }}/5 estrellas)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 5. Live Media Preview -->
          <div v-if="formPost.url_post" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 font-mono block">
              Vista Previa del Contenido Embebido:
            </span>
            <MediaEmbed
              :url="formPost.url_post"
              :media-url="formPost.media_url"
              :formato="formPost.tipo_formato"
              :plataforma="formPost.plataforma"
            />
          </div>

          <!-- Submit Buttons -->
          <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            <button
              type="button"
              @click="isPostModalOpen = false"
              class="px-4 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-semibold cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="formPost.processing"
              class="px-6 py-2.5 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs shadow-md shadow-cyan-500/25 flex items-center gap-2 cursor-pointer transition-all hover:scale-102 disabled:opacity-50"
            >
              <Send class="w-4 h-4" />
              <span>{{ formPost.processing ? 'Guardando...' : `Guardar Publicación en ${currentRed.nombre}` }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL DE SINCRONIZACIÓN EN VIVO (VENTANA 15 DÍAS) -->
    <div
      v-if="isSyncModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs"
    >
      <div class="w-full max-w-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl space-y-5 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-cyan-500/15 text-cyan-500 flex items-center justify-center shadow-xs">
              <RefreshCw class="w-5 h-5" :class="{ 'animate-spin': isSyncingRecientes }" />
            </div>
            <div>
              <h3 class="font-extrabold text-base sm:text-lg text-slate-900 dark:text-slate-100 flex items-center gap-2">
                <span>Sincronizador en Vivo</span>
                <span class="px-2 py-0.5 rounded-md bg-cyan-500/10 text-cyan-500 text-xs font-mono font-bold">
                  Últimos 15 Días
                </span>
              </h3>
              <p class="text-xs text-slate-500 dark:text-slate-400">
                Extrayendo métricas públicas de publicaciones activas en {{ currentRed.nombre }}.
              </p>
            </div>
          </div>

          <button
            v-if="!isSyncingRecientes"
            type="button"
            @click="isSyncModalOpen = false"
            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Barra de Progreso y Estado -->
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5">
          <div class="flex items-center justify-between text-xs font-mono">
            <span class="text-slate-600 dark:text-slate-400 font-semibold flex items-center gap-1.5">
              <RefreshCw v-if="isSyncingRecientes" class="w-3.5 h-3.5 animate-spin text-cyan-500" />
              <CheckCircle v-else class="w-3.5 h-3.5 text-emerald-500" />
              <span>{{ isSyncingRecientes ? 'Leyendo enlaces en tiempo real...' : 'Sincronización Completada' }}</span>
            </span>
            <span class="font-bold text-slate-900 dark:text-slate-100">
              {{ syncProgress.current }} / {{ syncProgress.total }} publicaciones
            </span>
          </div>

          <!-- Progress Bar -->
          <div class="w-full h-2.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
            <div
              class="h-full bg-gradient-to-r from-cyan-500 to-blue-500 transition-all duration-300 rounded-full"
              :style="{ width: `${syncProgress.total ? (syncProgress.current / syncProgress.total) * 100 : 0}%` }"
            ></div>
          </div>

          <!-- Enlace actual en lectura -->
          <div v-if="isSyncingRecientes && syncProgress.currentUrl" class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-cyan-500/20 text-xs font-mono">
            <span class="text-[10px] text-cyan-500 font-bold block">🔗 LEYENDO AHORA:</span>
            <p class="text-slate-700 dark:text-slate-300 truncate text-[11px] mt-0.5">
              {{ syncProgress.currentUrl }}
            </p>
          </div>
        </div>

        <!-- Resumen de Resultados si finalizó -->
        <div v-if="syncProgress.isFinished" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between flex-wrap gap-3">
          <div class="flex items-center gap-2.5">
            <CheckCircle class="w-5 h-5 text-emerald-500 shrink-0" />
            <div>
              <span class="text-xs font-extrabold text-emerald-800 dark:text-emerald-300 block">
                ¡Sincronización finalizada exitosamente!
              </span>
              <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-mono">
                +{{ syncProgress.totalNewLikes }} likes y +{{ syncProgress.totalNewComments }} comentarios actualizados en vivo.
              </span>
            </div>
          </div>
          <button
            type="button"
            @click="isSyncModalOpen = false"
            class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-xs cursor-pointer"
          >
            Aceptar
          </button>
        </div>

        <!-- Log / Lista en Vivo de Publicaciones Procesadas -->
        <div class="space-y-2">
          <span class="text-xs font-bold text-slate-700 dark:text-slate-300 font-mono block">
            📋 Registro de Enlaces Procesados en Vivo:
          </span>

          <div class="max-h-60 overflow-y-auto space-y-2 pr-1 font-mono text-xs">
            <div
              v-for="(log, idx) in syncLogs"
              :key="idx"
              class="p-3 rounded-xl border flex items-start justify-between gap-3 transition-all"
              :class="log.status === 'success' ? 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' : 'bg-amber-500/10 border-amber-500/20'"
            >
              <div class="space-y-0.5 flex-1 min-w-0">
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold">
                    {{ log.fecha || 'Reciente' }}
                  </span>
                  <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate block">
                    {{ log.resumen }}
                  </span>
                </div>
                <p class="text-[10px] text-slate-400 truncate">{{ log.url }}</p>
              </div>

              <div v-if="log.status === 'success'" class="text-right shrink-0">
                <span class="text-xs font-extrabold text-cyan-600 dark:text-cyan-400 block">
                  ❤️ {{ log.likes }} <span v-if="log.deltaLikes > 0" class="text-emerald-500 font-bold">(+{{ log.deltaLikes }})</span>
                </span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">
                  💬 {{ log.comments }} <span v-if="log.deltaComments > 0" class="text-blue-500 font-bold">(+{{ log.deltaComments }})</span>
                </span>
              </div>
              <div v-else class="text-right shrink-0 text-amber-500 text-[11px] font-bold">
                ⚠️ {{ log.error }}
              </div>
            </div>

            <div v-if="!syncLogs.length && isSyncingRecientes" class="p-6 text-center text-slate-400 text-xs">
              Iniciando lectura de publicaciones...
            </div>
          </div>
        </div>
      </div>
    </div>
  </WarRoomLayout>
</template>
