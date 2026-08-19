# Reglas de Proyecto (BrandingPo)

Estas son las reglas y estándares obligatorios para el desarrollo de **BrandingPo**.

---

## 🎨 1. Reglas de la Visual & Sistema de Diseño (War Room + Social Feed UI/UX)

La interfaz de **BrandingPo** combina la potencia analítica de una **Sala de Situación (War Room)** con la familiaridad visual y dinamismo de un **Feed de Redes Sociales Moderno**.

### A. Soporte Dual: Modo Oscuro (War Room) & Modo Claro (Executive Light)
- **Selector de Tema:** Toggle interactivo persistente (Sol/Luna) en la cabecera principal, sincronizado con preferencias del usuario.
- **Modo Oscuro (Dark Slate - Predeterminado War Room):**
  - Fondo principal: `bg-slate-950` (`#020617` / `#0f172a`).
  - Tarjetas y Feeds: `bg-slate-900` (`#0f172a` / `#1e293b`) con bordes en `border-slate-800`.
  - Textos: `text-slate-100` y secundarios en `text-slate-400`.
- **Modo Claro (Executive Light - Lectura e Impresión):**
  - Fondo principal: `bg-slate-50` (`#f8fafc`).
  - Tarjetas y Feeds: `bg-white` (`#ffffff`) con bordes sutiles en `border-slate-200` y sombras suaves (`shadow-sm` / `shadow-md`).
  - Textos: `text-slate-900` y secundarios en `text-slate-600`.
- **Acentos y Semáforos de Estado (Compatibles en ambos modos):**
  - **Cian / Azul Eléctrico (`#06b6d4` / `#3b82f6`):** Alcance, botones primarios y engagement.
  - **Esmeralda / Verde (`#10b981`):** Crecimiento positivo, tono favorable, estado electo.
  - **Ámbar / Naranja (`#f59e0b`):** Alertas moderadas, precandidato, pauta en revisión.
  - **Carmesí / Rojo (`#ef4444`):** Crisis activa, tono crítico, alertas urgentes.
  - **Violeta / Púrpura (`#8b5cf6`):** Predictor de IA, simulaciones algorítmicas de pauta.
- **Identidad Oficial de Plataformas Sociales:**
  - Facebook (`#1877F2`), Instagram (`#E4405F`), X/Twitter (`#000000` / `#1DA1F2`), TikTok (`#00F2FE` / `#FF004F`), YouTube (`#FF0000`), LinkedIn (`#0A66C2`).

### B. Componentes Estilo Red Social (Social Network UI)
- **Feed Social Multired:** Visualización de posts en formato tarjeta de publicación con avatar del candidato, handle de la red, fecha relativa, badge oficial de la plataforma y preview de contenido (foto, carrusel, video o tweet).
- **Barra de Reacciones Interactivas:** Barra inferior de cada post con recuento de reacciones con emojis nativos (👍 ❤️ 😂 😮 😡), comentarios y compartidos.
- **Badge de Pauta vs. Orgánico:** Indicador visual claro cuando un post es orgánico o cuenta con pauta publicitaria paga (con monto invertido visible para usuarios autorizados).
- **Sección de Comentarios Destacados:** Visualización de los comentarios top con indicador del termómetro de humor social (1 a 5 estrellas).

### C. Tipografía, Espaciado y Micro-interacciones
- Tipografía moderna (Inter o system-ui sans-serif), números tabulares monoespaciados para métricas y moneda.
- Transiciones fluidas (`transition-all duration-200`) y micro-animaciones al alternar temas y al interactuar con el feed.

### D. Control de Permisos en la UI
- **Visualizador:** Modo solo lectura limpio, sin botones rotos ni acciones de mutación.
- **Consultor y Admin:** Acceso completo a carga Fast-Flow, edición de pauta, clipping y analítica.

---

## ⚡ 2. Estándares de Código y Arquitectura

- **Stack:** Laravel 11 + Inertia.js + Vue 3 (Composition API con `<script setup>`) + Tailwind CSS + SQLite.
- **Documentación:** Todo el código (modelos, migraciones, controladores, componentes Vue y comentarios) debe estar en **español**.
- **Tipado y Clean Code:** Usar Form Requests de Laravel para validación, Resources/Transformers para serializar datos hacia Inertia, y props tipadas con `defineProps` en Vue 3.
- **Seguridad y Autorización:** Proteger cada endpoint con Gates/Policies de Laravel acordes al rol del usuario (`admin`, `consultor`, `visualizador`).

---

## 🚀 3. Ciclo de Ejecución de Sprints

Cada Sprint debe desarrollarse de forma representativa, atómica y completa bajo el siguiente ciclo:
1. **Desarrollo del feature:** Modelos, migraciones, lógica de backend y vistas Vue (con soporte Dark/Light y estilo feed social).
2. **Seeders Representativos:** Crear seeders con datos realistas (candidato propio, opositores, electos, notas de medios, pauta).
3. **Testeo y Validación:** Comprobación funcional y validación de permisos por rol.
4. **Git Commit & Push:** Cerrar el sprint con commit descriptivo y push al repositorio.
