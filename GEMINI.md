# Reglas de Proyecto (BrandingPo)

Estas son las reglas y estándares obligatorios para el desarrollo de **BrandingPo**.

---

## 🎨 1. Reglas de la Visual & Sistema de Diseño (War Room + Social Feed UI/UX)

La interfaz de **BrandingPo** combina la potencia analítica de una **Sala de Situación (War Room)** con la familiaridad visual y dinamismo de un **Feed de Redes Sociales Moderno**.

### A. Soporte Dual: Modo Oscuro (War Room) & Modo Claro (Executive Light)
- **Selector de Tema:** Toggle interactivo persistente (Sol/Luna) en la cabecera principal, sincronizado con preferencias del usuario (`brandingpo_theme`).
- **Modo Oscuro (Dark Slate - Predeterminado War Room):**
  - Fondo principal: `bg-slate-950` (`#020617` / `#0f172a`).
  - Tarjetas y Feeds: `bg-slate-900` (`#0f172a` / `#1e293b`) con bordes en `border-slate-800`.
  - Textos: `text-slate-100` y secundarios en `text-slate-400`.
- **Modo Claro (Executive Light - Lectura e Impresión):**
  - Fondo principal: `bg-slate-50` (`#f8fafc`).
  - Tarjetas y Feeds: `bg-white` (`#ffffff`) con bordes sutiles en `border-slate-200` y sombras suaves (`shadow-sm` / `shadow-md`).
  - Textos: `text-slate-900` y secundarios en `text-slate-600`.
- **Acentos y Semáforos de Estado (Compatibles en ambos modos):**
  - **Cian / Azul Eléctrico (`#06b6d4` / `#3b82f6`):** Alcance, candidato propio, botones primarios y engagement.
  - **Esmeralda / Verde (`#10b981`):** Crecimiento positivo, tono favorable, estado electo.
  - **Ámbar / Naranja (`#f59e0b`):** Pestañas activas, alertas moderadas, precandidato, pauta en revisión.
  - **Carmesí / Rojo (`#ef4444`):** Pestañas inactivas, crisis activa, tono crítico, alertas urgentes.
  - **Violeta / Púrpura (`#8b5cf6`):** Oposición, candidatos rivales, simulaciones algorítmicas de pauta.
- **Identidad Oficial de Plataformas Sociales:**
  - Facebook (`#1877F2`), Instagram (`#E4405F`), X/Twitter (`#000000` / `#1DA1F2`), TikTok (`#00F2FE` / `#FF004F`), YouTube (`#FF0000`), LinkedIn (`#0A66C2`).

### B. Módulos Estratégicos & Lógica de Negocio
- **1. Punto Cero / Punto Alfa:**
  - Toda red social del candidato propio o de rivales registra: seguidores iniciales, seguidos, publicaciones iniciales y fecha de inicio de auditoría para medir crecimiento neto.
  - Las tablas deben ser adaptativas: en Facebook se oculta la tarjeta de publicaciones acumuladas ya que Facebook no la provee públicamente.
- **2. Lector Automático de Redes (`SocialProfileScraperService`):**
  - Extrae fotos de alta resolución, handles y métricas públicas con 1 clic mediante agentes especializados (Twitterbot, WhatsApp, FacebookBot).
  - Toda imagen externa debe incluir `referrerpolicy="no-referrer"` para evitar bloqueos 403 de CDNs de Meta.
- **3. Semáforo de Pestañas de Canales:**
  - 🔵 **Azul (Verificada):** Cuenta oficialmente certificada por la red social.
  - 🟠 **Naranja (Activa):** Cuenta vinculada y en uso activo de campaña.
  - 🔴 **Roja (Inactiva):** Canal pendiente de creación o sin actividad.
- **4. Cuantificación Emoji por Emoji (Fast-Flow):**
  - Desglose granular: 👍, ❤️, 🥰, 😂, 😮, 😢, 😡.
  - Cálculo de Índice de Aprobación Neta, Sentimiento Predominante y Termómetro de Humor Social (1 a 5 estrellas).
  - Alerta roja inmediata si 😡 > 15%.
- **5. Distinción de Pauta:**
  - **Orgánica Pura:** Tracción natural sin inversión.
  - **Orgánica Impulsada (Boosted Post):** Post orgánico potenciado con dinero tras registrar alto rendimiento.
  - **Anuncio Directo / Dark Post:** Anuncio exclusivo de Ads Manager que no figura en el feed orgánico.
- **6. Monitoreo de Medios & Prensa (Clipping):**
  - Auditoría de notas periodísticas clasificadas por medio, periodista, candidato mencionado (Propio vs. Oposición) y tono editorial (Favorable / Neutral / Crítico).
- **7. Jerarquía y Normalización de Métricas de Interacción:**
  - **Instagram (Estándar Base Normalizado):**
    - ❤️ **Me gusta (Likes / Corazones):** Peso $1\text{ pt}$ (aprobación directa).
    - 💬 **Comentarios:** Peso $3\text{ pts}$ (involucramiento activo y conversación).
    - ✈️ **Compartidos / Envíos:** Peso $5\text{ pts}$ (difusión Dark Social / recomendación boca a boca).
    - 🔁 **Republicar / Reposts:** Peso $10\text{ pts}$ (endoso público y megáfono orgánico).
    - 👁️ **Reproducciones / Vistas:** Base de cálculo para el Engagement Rate Efectivo.
    - 🔖 **Guardados:** Ítem propio de auditoría interna y seguimiento de campaña (separado de las métricas públicas).
  - *Referencia extensible:* Este estándar de pesos e independencia de ítems internos servirá de matriz comparativa cuando se normalicen las demás redes (Facebook, TikTok, X, YouTube, LinkedIn).

### C. Tipografía, Espaciado y Micro-interacciones
- Tipografía moderna (Inter o system-ui sans-serif), números tabulares monoespaciados (`font-mono`) para métricas y moneda.
- Transiciones fluidas (`transition-all duration-200`) y micro-animaciones al alternar temas y al interactuar con el feed.

### D. Control de Permisos en la UI
- **Visualizador (`visualizador`):** Modo solo lectura limpio, sin botones rotos ni acciones de mutación.
- **Consultor (`consultor`) y Admin (`admin`):** Acceso completo a carga Fast-Flow, edición de pauta, clipping, Punto Cero y analítica.

---

## ⚡ 2. Estándares de Código y Arquitectura

- **Stack:** Laravel 11 + Inertia.js + Vue 3 (Composition API con `<script setup>`) + Tailwind CSS + SQLite.
- **Documentación:** Todo el código (modelos, migraciones, controladores, componentes Vue y comentarios) debe estar en **español**.
- **Tipado y Clean Code:** Usar Form Requests de Laravel para validación, Resources/Transformers para serializar datos hacia Inertia, y props tipadas con `defineProps` en Vue 3.
- **Seguridad y Autorización:** Proteger cada endpoint con Gates/Policies de Laravel acordes al rol del usuario (`admin`, `consultor`, `visualizador`). Mantener el token CSRF configurado en `app.blade.php`.

---

## 🚀 3. Ciclo de Ejecución de Sprints

Cada Sprint debe desarrollarse de forma representativa, atómica y completa bajo el siguiente ciclo:
1. **Desarrollo del feature:** Modelos, migraciones, lógica de backend y vistas Vue (con soporte Dark/Light y estilo feed social).
2. **Seeders Representativos:** Crear seeders con datos realistas (candidato propio, opositores, electos, notas de medios, pauta).
3. **Testeo y Validación:** Comprobación funcional y validación de permisos por rol.
4. **Git Commit & Push:** Cerrar el sprint con commit descriptivo y push al repositorio.
