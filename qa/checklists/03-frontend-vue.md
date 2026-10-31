# 03 — Frontend y Experiencia de Usuario Vue 3

## Arquitectura de Componentes y Estado
- [ ] **Límite de tamaño de vistas (< 500 lín)**: Ningún componente de página o vista debe superar las ~500 líneas de código. (obligatorio)
- [ ] **Estructura Composition API estándar**: Uso uniforme de `<script setup>` con orden estricto `<script>` → `<template>` → `<style>` en todos los componentes `.vue`.
- [ ] **Desacoplamiento de lógica a composables**: Extracción de formateos de moneda, cálculos de porcentajes y suscripción a eventos de teclado hacia `composables/` (`useSocialMetrics`, `useChartTheme`, etc.).
- [ ] **Soporte Dual fluido (War Room vs. Executive Light)**: Clases Tailwind condicionales coherentes (`bg-slate-950` / `bg-slate-50`) sincronizadas con `brandingpo_theme` y sin parpadeos de estilo.
- [ ] **Control de envíos duplicados**: Formularios y botones de acción en modales (`FeedFastFlowModal`, edición de perfiles) deshabilitados durante peticiones en curso con indicador de carga reactivo.
- [ ] **Optimización de renderizado en feeds masivos**: Implementación de lazy loading o virtual scrolling en listados extensos de publicaciones para evitar degradación de memoria.
- [ ] **Manejo de errores visuales y enlaces rotos**: Imágenes de perfiles y notas con fallback visual ordenado ante bloqueos de CDN o URLs caducadas (`@error="handleImageFallback"`).

## Hallazgos de la Medición — Vistas Críticas (> 800 lín)
- [ ] **Dividir `DashboardChartsSection.vue` (1.938 lín)**: Descomponer en subcomponentes por tipo de gráfico: `MetricEvolutionChart.vue`, `SentimentDistributionChart.vue`, `ChannelComparisonChart.vue` y extraer lógica de opciones de Chart.js a composable.
- [ ] **Dividir `SocialCard.vue` (1.065 lín)**: Extraer barra de reacciones emoji a `SocialReactionsBar.vue`, vista previa de media a `SocialMediaEmbed.vue` y bloque de métricas de pauta a `SocialAdBadge.vue`.
- [ ] **Dividir `ImpactoElectoral.vue` (989 lín)**: Separar mapa SVG de circuitos territoriales en `ElectoralCircuitMap.vue` y panel de tabla comparativa en `ElectoralStatsTable.vue`.
- [ ] **Dividir `FeedFastFlowModal.vue` (971 lín)**: Desacoplar la calculadora de reacciones emoji en `FastFlowEmojiPicker.vue` y el predictor de alcance de pauta en `FastFlowAdEstimator.vue`.

## Hallazgos de la Medición — Vistas sobre Umbral (500 a 800 lín)
- [ ] **Modularizar `SocialCardEditModal.vue` (795 lín)**: Separar formulario de metadatos del editor granular de métricas por plataforma.
- [ ] **Modularizar `Pages/Ejes/Index.vue` (715 lín)**: Extraer tabla de ejes estratégicos y tarjeta de semáforo de sentimiento a subcomponentes en `Components/Ejes/`.
- [ ] **Modularizar `Pages/Reportes/Ejecutivo.vue` (658 lín)**: Extraer secciones del reporte imprimible (resumen ejecutivo, ranking de publicaciones y desglose de inversión) a componentes dedicados.
- [ ] **Modularizar `Pages/Calendario/Index.vue` (655 lín)**: Separar la cuadrícula mensual del panel lateral de detalles de evento.
- [ ] **Modularizar `Pages/Candidatos/MetricasCanal.vue` (638 lín)**: Extraer tabla de Punto Cero y comparador inter-redes a subcomponentes.
- [ ] **Modularizar `Pages/Territorios/Index.vue` (623 lín)**: Desacoplar panel de agregación departamental del formulario de nuevo territorio.
- [ ] **Modularizar `FeedFiltrosBar.vue` (593 lín)**: Extraer selector de rango de fechas y filtro de sentimientos a subcomponentes independientes.
- [ ] **Modularizar `Pages/Candidatos/Show.vue` (589 lín)**: Separar cabecera de perfil, pestañas de redes sociales y feed del candidato.
- [ ] **Modularizar `Pages/Medios/Index.vue` (542 lín)**: Desacoplar listado de notas periodísticas del modal de sincronización de medios.
- [ ] **Modularizar `Pages/Presupuesto/Index.vue` (519 lín)**: Separar gráficos de ejecución presupuestaria de la tabla de partidas de campaña.
