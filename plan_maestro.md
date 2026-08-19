# 🏛️ PLAN MAESTRO - BRANDINGPO
### *Plataforma Monolítica de Inteligencia Política, Monitoreo de Medios & Benchmarking Digital Multired*

---

## 📌 1. Visión General del Producto

**BrandingPo** es una suite integral de inteligencia, estrategia y consultoría política digital diseñada para:
1. **Auditar y comparar** el posicionamiento, ritmo de publicación, engagement e impacto de un candidato frente a sus competidores en **múltiples redes sociales (Facebook, Instagram, X/Twitter, TikTok, YouTube, LinkedIn)**.
2. **Cuantificar y mapear el ecosistema de medios de prensa** (portales digitales, diarios, radios y TV local), analizando líneas editoriales, notas a favor/en contra, apoyos y réplicas.
3. **Motor de Inteligencia Temporal y Correlación Causa-Efecto:** Conectar publicaciones, notas de prensa y eventos con picos de crecimiento de seguidores, saltos de engagement y alertas de crisis.
4. **Módulos Estratégicos Avanzados:** Ejes discursivos/temáticos, clima de comentarios, alianzas/padrinos políticos, radar de pauta (Ads Spy), índice de penetración electoral y reportes ejecutivos en 1 clic.
5. **Ergonomía de Carga "Fast-Flow":** Interfaz ultra-rápida con atajos de teclado y formularios adaptativos para carga manual eficiente.

---

## 💎 2. Módulos Estratégicos de Alto Valor

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                            SUITE DE INTELIGENCIA POLÍTICA INTEGRADA                         │
├──────────────────────────────────────────┬──────────────────────────────────────────────────┤
│ 🏷️ EJES DISCURSIVOS & NARRATIVA          │ 💬 CLIMA SOCIAL & COMENTARIOS DESTACADOS         │
│ • Clasificación temática (Seguridad,     │ • Registro de los 2-3 comentarios más votados    │
│   Economía, Obras, Denuncias, Familia).  │ • Termómetro de humor social (1 a 5 estrellas).  │
│ • Share of Voice temático vs. rivales.   │ • Detección de argumentos para contra-discursos. │
├──────────────────────────────────────────┼──────────────────────────────────────────────────┤
│ 🤝 ALIANZAS & PADRINOS POLÍTICOS         │ 🚨 SEMÁFORO DE CRISIS & RESPUESTA                │
│ • Registro de figuras en foto/video      │ • Registro de eventos críticos / ataques.        │
│   (Gobernador, Intendente, Influencer).  │ • Medición de tiempo de reacción del equipo.     │
│ • Impacto: ¿La alianza suma o resta?     │ • Monitoreo de contención de daños.              │
├──────────────────────────────────────────┼──────────────────────────────────────────────────┤
│ 🕵️ RADAR DE PAUTA (ADS SPY)              │ 🗳️ ÍNDICE DE PENETRACIÓN ELECTORAL              │
│ • Monitoreo de Meta Ads / TikTok Ads.    │ • Cruce con padrón electoral del departamento.   │
│ • Temáticas y audiencias pagadas rivales.│ • Índice de Afinidad Digital por municipio.      │
├──────────────────────────────────────────┼──────────────────────────────────────────────────┤
│ 📄 BRIEFING EJECUTIVO EN 1 CLIC          │ ⌨️ MODO CARGA "FAST-FLOW" ERGONÓMICO             │
│ • Reporte semanal para WhatsApp/PDF.     │ • Atajos de teclado completos (Ctrl+Enter, etc). │
│ • Ficha de situación para el candidato.  │ • Pegado inteligente de enlaces y hashtags.      │
└──────────────────────────────────────────┴──────────────────────────────────────────────────┘
```

---

## ⚖️ 3. Asimetría de Métricas: Perfil Propio vs. Competencia

```
┌──────────────────────────────────────────┬──────────────────────────────────────────────────┐
│ 👁️ CAPA PÚBLICA (Competencia y Propio)    │ 🔐 CAPA INTERNA / AVANZADA (Solo Perfil Propio)  │
├──────────────────────────────────────────┼──────────────────────────────────────────────────┤
│ • Seguidores / Suscriptores públicos     │ • Cuentas alcanzadas reales (Reach único)        │
│ • Reacciones detalladas (👍, ❤️, 😂, 😡)  │ • Impresiones totales y desglose por origen      │
│ • Comentarios y Compartidos/Reposts      │ • Clics en enlace de biografía / WhatsApp web    │
│ • Reproducciones / Vistas públicas       │ • Demografía real (% Edad, Género, Localidades)  │
│ • Pauta detectada (Biblioteca de Ads)    │ • Retención de video y tasa de finalización      │
│ • Formato y ritmo de publicación         │ • Inversión real exacta en Pauta (CPC, CPM)      │
└──────────────────────────────────────────┴──────────────────────────────────────────────────┘
```

---

## 🌐 4. Matriz de Redes Sociales & Métricas Específicas

| Red Social | Reacciones / Métricas Específicas | Formatos de Contenido |
| :--- | :--- | :--- |
| **📘 Facebook** | 👍 ❤️ 🥰 😂 😮 😢 😡 \| Comentarios, Compartidos, Vistas Video, Clics enlace | Post Foto, Nota/Link, Video, Reel, Live |
| **📸 Instagram** | Likes, Comentarios, Compartidos, Guardados (Saves), Reproducciones Reels | Reel, Carrusel, Foto simple, Historia/Story |
| **🐦 X (Twitter)** | Vistas/Impresiones públicas (Views), Reposts, Citas, Likes, Respuestas, Guardados | Tweet, Hilo/Thread, Video, Encuesta, Space |
| **🎵 TikTok** | Reproducciones (Views), Likes, Comentarios, Compartidos, Guardados/Favoritos | Video corto, Video largo, Carrusel fotos |
| **🔴 YouTube** | Visualizaciones, Likes, Comentarios, Suscriptores estimados | Video largo, Shorts, Directo, Post Comunidad |
| **💼 LinkedIn** | 👍 👏 🤝 ❤️ 💡 🎯 \| Comentarios, Veces compartido, Impresiones | Artículo, Documento PDF/Carrusel, Video |

---

## 🗄️ 5. Modelo de Datos y Entidades Principales

```mermaid
erDiagram
    TERRITORIO ||--o{ CANDIDATO : pertenece_a
    CANDIDATO ||--o{ PERFIL_SOCIAL : posee
    CANDIDATO ||--o{ PUBLICACION : realiza
    CANDIDATO ||--o{ NOTA_PRENSA : mencion_en
    CANDIDATO ||--o{ EVENTO_CRISIS : sufre_o_gestiona
    PERFIL_SOCIAL ||--o{ PERFIL_SNAPSHOT : evoluciona_en_tiempo
    PERFIL_SOCIAL ||--o{ PUBLICACION : aloja
    PUBLICACION ||--o{ PUBLICACION_HISTORICO : evoluciona_en_tiempo
    PUBLICACION }o--o{ EJE_TEMATICO : clasificado_en
    CANDIDATO ||--o{ PLAN_CAMPANA : asignado
    PLAN_CAMPANA ||--o{ GASTO_PRESUPUESTO : registra
    MEDIO_PRENSA ||--o{ NOTA_PRENSA : publica
    TERRITORIO ||--o{ MEDIO_PRENSA : ubicado_en

    TERRITORIO {
        int id
        string nombre "Provincia / Departamento / Municipio"
        int poblacion_total
        int padron_electoral
    }

    CANDIDATO {
        int id
        string nombre_completo
        string partido_coalicion
        string cargo_aspirado
        string color_hex "Color identificador para gráficos"
        boolean es_propio "true: propio / false: competencia"
        string avatar_url
    }

    PERFIL_SOCIAL {
        int id
        int candidato_id
        string plataforma "facebook, instagram, x_twitter, tiktok, youtube, linkedin"
        string handle_usuario "@usuario"
        string url_perfil
        int seguidores_actuales
        json demografia_interna_propia "Solo si es_propio"
    }

    PERFIL_SNAPSHOT {
        int id
        int perfil_social_id
        date fecha_registro
        int total_seguidores
        int crecimiento_neto
        int publicaciones_totales_perfil
        int alcance_cuentas_propio
        int clics_enlace_bio_propio
        text notas_observaciones
    }

    EJE_TEMATICO {
        int id
        string nombre "Seguridad, Economía, Salud, Obras, etc."
        string color_badge
    }

    PUBLICACION {
        int id
        int candidato_id
        int perfil_social_id
        datetime fecha_publicacion
        string tipo_formato "reel, video, nota, carrusel, foto, tweet, shorts, articulo"
        string tipo_pauta "organico, pauta_paga"
        string url_post
        text contenido_resumen
        int total_vistas
        int total_likes
        int total_comentarios
        int total_compartidos
        int total_guardados
        json reacciones_detalladas "{me_gusta, me_encanta, me_importa, me_divierte, me_asombra, me_entristece, me_enoja, etc.}"
        string sentimiento_predominante "positivo, neutro, negativo"
        json figuras_acompanantes "['Gobernador X', 'Influencer Y']"
        json comentarios_destacados "['Comentario top 1', 'Comentario top 2']"
        int termometro_humor_social "1 a 5 estrellas"
        json insights_internos_propios "Solo si es_propio"
    }

    PUBLICACION_HISTORICO {
        int id
        int publicacion_id
        datetime fecha_corte
        int vistas_corte
        int likes_corte
        int comentarios_corte
        int compartidos_corte
    }

    MEDIO_PRENSA {
        int id
        int territorio_id
        string nombre "Diario Clarín, El Litoral, etc."
        string tipo_medio "digital, impreso, radio, tv"
        string url_sitio
        string alcance_tipo "local, provincial, nacional"
        string sesgo_editorial_estimado "oficialista, opositor, independiente"
    }

    NOTA_PRENSA {
        int id
        int medio_prensa_id
        int candidato_id
        date fecha_publicacion
        string titulo
        string url_nota
        text resumen
        string tono_mencion "favorable, neutro, critico"
        boolean es_tapa_o_principal
        int interacciones_en_redes_del_medio
        text respuesta_replica_candidato
    }

    EVENTO_CRISIS {
        int id
        int candidato_id
        string titulo "Denuncia X / Furcio en debate"
        datetime fecha_evento
        string nivel_gravedad "leve, moderado, critico"
        int minutos_tiempo_respuesta
        text estrategia_contencion
        string estado "abierto, en_contencion, resuelto"
    }

    PLAN_CAMPANA {
        int id
        int candidato_id
        string titulo
        text objetivo_estrategico
        date fecha_inicio
        date fecha_fin
        decimal presupuesto_asignado
    }

    GASTO_PRESUPUESTO {
        int id
        int plan_campana_id
        string concepto "Pauta Meta Ads, TikTok Ads, Gráfica, Eventos"
        string canal_o_proveedor
        decimal monto
        date fecha_gasto
        boolean es_gasto_propio
    }
```

---

## 💻 6. Módulos del Sistema

1. **⚡ Fast-Flow Entry Desk:** Formulario adaptativo por red social, teclado numérico rápido, autoguardado y modo tabla batch.
2. **📊 War Room & Head-to-Head Analytics:** Comparativas de crecimiento temporal, distribución de emociones, formatos ganadores e índice de afinidad electoral.
3. **🏷️ Narrativa & Ejes Discursivos:** Análisis de qué temas traccionan más y de qué habla la competencia.
4. **📰 Observatorio de Medios & Clipping:** Directorio cuantificado de portales, sesgo editorial, portadas y réplicas.
5. **🚨 Centro de Crisis & Alianzas:** Monitoreo de eventos críticos, tiempos de respuesta y análisis de figuras que suman o restan.
6. **📅 Calendario, Presupuesto & Pauta (Ads Spy):** Cronograma editorial, control financiero y radar de anuncios rivales.
7. **📄 Generador de Briefings Ejecutivos:** Exportación en 1 clic de fichas de situación semanales/diarias.

---

## 🛠️ 7. Arquitectura Técnica

- **Backend:** Laravel 11 (PHP 8.2+).
- **Frontend / SPA:** Inertia.js + Vue 3 (Composition API / `<script setup>`).
- **Base de Datos:** SQLite (diseño relacional + columnas JSON para escalabilidad).
- **Estilos:** Tailwind CSS (Dark/Light Slate, UI tipo War Room moderna).
- **Visualización:** Chart.js / ApexCharts + Lucide Icons.

---

## 🗺️ 8. Roadmap de Desarrollo

| Fase | Alcance |
| :--- | :--- |
| **Fase 1: Setup & Arquitectura Base** | Instalación Laravel 11 + Inertia Vue 3 + Tailwind. Migraciones y modelos completos (Territorios, Candidatos, Redes, Medios, Ejes, Snapshots). |
| **Fase 2: Motor Fast-Flow Multired & Medios** | Formularios dinámicos adaptativos (Facebook, Instagram, X, TikTok, YouTube, LinkedIn), reacciones, clipping de diarios y comentarios destacados. |
| **Fase 3: Sala de Situación (War Room) & Inteligencia Temporal** | Dashboards interactivos: Crecimiento temporal, correlación contenido ➡️ seguidores, radar temático y observatorio de medios. |
| **Fase 4: Crisis, Calendario, Presupuesto & Reportes** | Módulo de crisis, radar de pauta, calendario, presupuesto y generador de briefings en 1 clic. |
