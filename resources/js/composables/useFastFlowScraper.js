import { ref, computed, watch } from 'vue';

/**
 * Composable para la normalización de URLs, autocompletado con scraper 1-clic,
 * detección automática de plataforma y alertas de duplicados / pauta activa.
 */
export function useFastFlowScraper({ createForm, props, perfilesCandidatoSeleccionado }) {
  const isScraping = ref(false);
  const scrapeSuccessMsg = ref('');
  const scrapeErrorMsg = ref('');
  const duplicateDetectedPost = ref(null);
  const adDetectionAlert = ref(null);

  /**
   * Normaliza cualquier texto pegado en el campo URL/embed del Fast-Flow.
   * Soporta: URL directa, <iframe> FB plugin, plugin URL directa (sin iframe),
   * <blockquote> de Instagram, embed de Twitter/X, y cualquier URL suelta.
   */
  const normalizarUrlPegada = (raw) => {
    if (!raw || typeof raw !== 'string') return raw;
    const txt = raw.trim();
    if (txt.length < 8) return txt;

    // Caso 1: Twitter/X status (máxima prioridad)
    const twitterMatch = txt.match(/(?:twitter\.com|x\.com)\/[a-zA-Z0-9_]+\/status\/(\d+)/i);
    if (twitterMatch) {
      const url = twitterMatch[0];
      return url.startsWith('http') ? url : 'https://' + url;
    }

    // Caso 2: Facebook plugin URL dentro de un <iframe src="...">
    const fbIframeMatch = txt.match(/src=["']([^"']+plugins\/(?:post|video)\.php[^"']*)['"]/i);
    if (fbIframeMatch) {
      const pluginSrc = fbIframeMatch[1].replace(/&amp;/g, '&');
      const hrefMatch = pluginSrc.match(/\bhref=([^&"']+)/i);
      if (hrefMatch) return decodeURIComponent(hrefMatch[1]);
    }

    // Caso 3: Facebook plugin URL directa (sin <iframe>)
    const fbPluginDirecto = txt.match(/plugins\/(?:post|video)\.php[^"'<\s]*\bhref=([^&"'<\s]+)/i);
    if (fbPluginDirecto) return decodeURIComponent(fbPluginDirecto[1]);

    // Caso 4: Instagram blockquote embed
    const igBlockquote = txt.match(/data-instgrm-permalink="([^"]+)"/i);
    if (igBlockquote) return igBlockquote[1].replace(/&amp;/g, '&');

    // Caso 5: Twitter/X blockquote embed (href del último <a> con /status/)
    if (txt.includes('twitter-tweet') || txt.includes('class="tweet"')) {
      const tweetLinks = [...txt.matchAll(/href="(https?:\/\/(?:twitter\.com|x\.com)\/[^"]+)"/gi)];
      if (tweetLinks.length) {
        const last = tweetLinks[tweetLinks.length - 1][1];
        if (last.includes('/status/')) return last.replace(/&amp;/g, '&');
      }
    }

    // Caso 6: <iframe src="..."> genérico
    const iframeMatch = txt.match(/(?:iframe|frame)[^>]+src=["']([^"']+)['"]/i);
    if (iframeMatch) return iframeMatch[1].replace(/&amp;/g, '&');

    // Caso 7: href="..." en cualquier etiqueta HTML
    const hrefMatch = txt.match(/href=["']([^"']+)['"]/i);
    if (hrefMatch) {
      try {
        const u = new URL(hrefMatch[1]);
        return u.href;
      } catch {}
    }

    // Caso 8: URL suelta en el texto (fallback universal)
    const urlMatch = txt.match(/https?:\/\/[^\s"'<>]+/i);
    if (urlMatch) return urlMatch[0].replace(/[.,;:)\]}>]+$/, '');

    return txt;
  };

  const platformMeta = computed(() => {
    const plat = (createForm.plataforma || 'instagram').toLowerCase();
    switch (plat) {
      case 'facebook':
        return {
          label: '🔗 Enlace o código embed de la publicación de Facebook:',
          placeholder: 'https://www.facebook.com/... o pega el código <iframe> del plugin',
          nombre: 'Facebook',
        };
      case 'tiktok':
        return {
          label: '🔗 Enlace del Video de TikTok:',
          placeholder: 'https://www.tiktok.com/@.../video/...',
          nombre: 'TikTok',
        };
      case 'x_twitter':
      case 'twitter':
        return {
          label: '🔗 Enlace del Post / Tweet de X (Twitter):',
          placeholder: 'https://x.com/.../status/...',
          nombre: 'X (Twitter)',
        };
      case 'youtube':
        return {
          label: '🔗 Enlace del Video o Short de YouTube:',
          placeholder: 'https://www.youtube.com/watch?v=... o https://youtu.be/...',
          nombre: 'YouTube',
        };
      case 'threads':
        return {
          label: '🔗 Enlace del Post de Threads:',
          placeholder: 'https://www.threads.net/@.../post/...',
          nombre: 'Threads',
        };
      case 'linkedin':
        return {
          label: '🔗 Enlace de la Publicación de LinkedIn:',
          placeholder: 'https://www.linkedin.com/posts/...',
          nombre: 'LinkedIn',
        };
      case 'instagram':
      default:
        return {
          label: '🔗 Enlace de la Publicación o Reel de Instagram:',
          placeholder: 'https://www.instagram.com/p/... o /reel/...',
          nombre: 'Instagram',
        };
    }
  });

  watch(() => createForm.url_post, (newUrl) => {
    if (!newUrl || typeof newUrl !== 'string' || newUrl.trim().length < 8) return;

    const cleaned = normalizarUrlPegada(newUrl);
    if (cleaned !== newUrl) {
      createForm.url_post = cleaned;
      return;
    }

    const lower = newUrl.toLowerCase();
    let detectedPlatform = null;
    if (lower.includes('facebook.com') || lower.includes('fb.watch') || lower.includes('fb.com')) {
      detectedPlatform = 'facebook';
    } else if (lower.includes('instagram.com')) {
      detectedPlatform = 'instagram';
    } else if (lower.includes('tiktok.com')) {
      detectedPlatform = 'tiktok';
    } else if (lower.includes('twitter.com') || lower.includes('x.com')) {
      detectedPlatform = 'x_twitter';
    } else if (lower.includes('threads.net') || lower.includes('threads.com')) {
      detectedPlatform = 'threads';
    } else if (lower.includes('youtube.com') || lower.includes('youtu.be')) {
      detectedPlatform = 'youtube';
    } else if (lower.includes('linkedin.com')) {
      detectedPlatform = 'linkedin';
    }

    if (detectedPlatform && detectedPlatform !== createForm.plataforma) {
      createForm.plataforma = detectedPlatform;
      const cand = props.candidatos.find(c => c.id === Number(createForm.candidato_id));
      const perfiles = cand?.perfiles_sociales || cand?.perfilesSociales || [];
      const matching = perfiles.find(p => p.plataforma.toLowerCase() === detectedPlatform);
      if (matching) {
        createForm.perfil_social_id = matching.id;
      }
    }
  });

  const localDuplicatePost = computed(() => {
    if (!createForm.url_post || createForm.url_post.trim().length < 10) return null;
    const cleanUrl = createForm.url_post.trim().toLowerCase().replace(/\?.*$/, '').replace(/\/+$/, '');
    return props.publicaciones.find(p => {
      if (!p.url_post) return false;
      const pClean = p.url_post.trim().toLowerCase().replace(/\?.*$/, '').replace(/\/+$/, '');
      return pClean === cleanUrl;
    });
  });

  const duplicateAlert = computed(() => localDuplicatePost.value || duplicateDetectedPost.value);

  const ultimaPublicacionRegistrada = computed(() => {
    const cid = Number(createForm.candidato_id);
    const plat = (createForm.plataforma || '').toLowerCase();

    const matches = props.publicaciones.filter(p => {
      const matchCand = !cid || p.candidato?.id === cid;
      const matchPlat = !plat || (p.plataforma || p.perfil_social?.plataforma || '').toLowerCase() === plat;
      return matchCand && matchPlat;
    });

    if (matches.length === 0) return null;

    return [...matches].sort((a, b) => {
      const dateA = new Date(a.fecha_publicacion_raw || a.fecha_publicacion || 0);
      const dateB = new Date(b.fecha_publicacion_raw || b.fecha_publicacion || 0);
      return dateB - dateA;
    })[0];
  });

  const autocompletarScrape = async () => {
    if (!createForm.url_post) {
      scrapeErrorMsg.value = 'Ingresa primero la URL de la publicación de Instagram, TikTok o Facebook.';
      return;
    }

    isScraping.value = true;
    scrapeSuccessMsg.value = '';
    scrapeErrorMsg.value = '';

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const response = await fetch('/publicaciones/scrape-post', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          url_post: createForm.url_post,
          plataforma: createForm.plataforma,
        }),
      });

      const data = await response.json();

      if (response.ok && data.success) {
        if (data.data.tipo_formato) createForm.tipo_formato = data.data.tipo_formato;
        if (data.data.total_likes > 0 || !createForm.total_likes) {
          createForm.total_likes = data.data.total_likes || 0;
        }
        if (data.data.total_comentarios > 0 || !createForm.total_comentarios) {
          createForm.total_comentarios = data.data.total_comentarios || 0;
        }
        if (data.data.total_vistas || data.data.vistas_organicas) {
          createForm.vistas_organicas = data.data.total_vistas || data.data.vistas_organicas;
        }
        if (data.data.contenido_resumen) createForm.contenido_resumen = data.data.contenido_resumen;
        if (data.data.fecha_publicacion) createForm.fecha_publicacion = data.data.fecha_publicacion.slice(0, 16);
        if (data.data.url_post) createForm.url_post = data.data.url_post;
        if (data.data.media_url) createForm.media_url = data.data.media_url;
        if (data.data.plataforma) {
          createForm.plataforma = data.data.plataforma;
          const matchingPerfil = perfilesCandidatoSeleccionado.value.find(p => p.plataforma.toLowerCase() === data.data.plataforma.toLowerCase());
          if (matchingPerfil) {
            createForm.perfil_social_id = matchingPerfil.id;
          }
        }

        const lk = data.data.total_likes || 0;
        if (createForm.plataforma === 'facebook') {
          createForm.reacciones_detalladas.like = lk;
          createForm.reacciones_detalladas.love = 0;
          createForm.reacciones_detalladas.care = 0;
          createForm.reacciones_detalladas.haha = 0;
          createForm.reacciones_detalladas.wow = 0;
          createForm.reacciones_detalladas.sad = 0;
          createForm.reacciones_detalladas.angry = 0;
        } else {
          createForm.total_likes = lk;
        }

        if (data.data.sospecha_pauta) {
          adDetectionAlert.value = {
            motivo: data.data.motivo_sospecha_pauta || 'Detectados parámetros de campaña publicitaria o de anuncio en el enlace.',
            sugerido: data.data.tipo_pauta_sugerido || 'organico_impulsado',
          };
        } else {
          adDetectionAlert.value = null;
        }

        scrapeSuccessMsg.value = '✅ ¡Datos, texto e imagen extraídos exitosamente!';
      } else {
        if (data.ya_registrada) {
          duplicateDetectedPost.value = data;
        }
        scrapeErrorMsg.value = data.error || data.mensaje || 'No se pudieron extraer los datos automáticamente. Puedes completarlos a mano.';
      }
    } catch (err) {
      scrapeErrorMsg.value = 'Error al consultar el lector de redes. Completa los datos a mano.';
    } finally {
      isScraping.value = false;
    }
  };

  const aplicarPautaDetectada = () => {
    if (adDetectionAlert.value?.sugerido) {
      createForm.tipo_pauta = adDetectionAlert.value.sugerido;
    } else {
      createForm.tipo_pauta = 'organico_impulsado';
    }
  };

  const limpiarAlertas = () => {
    duplicateDetectedPost.value = null;
    adDetectionAlert.value = null;
    scrapeSuccessMsg.value = '';
    scrapeErrorMsg.value = '';
  };

  return {
    isScraping,
    scrapeSuccessMsg,
    scrapeErrorMsg,
    duplicateDetectedPost,
    adDetectionAlert,
    normalizarUrlPegada,
    platformMeta,
    duplicateAlert,
    ultimaPublicacionRegistrada,
    autocompletarScrape,
    aplicarPautaDetectada,
    limpiarAlertas,
  };
}
