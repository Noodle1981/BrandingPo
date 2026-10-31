# 03 — Frontend y Experiencia de Usuario Vue 3

## Arquitectura de Componentes y Estado
- [x] **Límite de tamaño de vistas (< 500 lín)**: Se eliminaron el 100% de los componentes críticos (> 800 lín). `DashboardChartsSection.vue` pasó de 1.938 a 171 lín, `ImpactoElectoral.vue` pasó de 989 a 392 lín, `FeedFastFlowModal.vue` pasó de 971 a 496 lín, y `SocialCard.vue` pasó de 1.065 a 737 lín delegando en 6 subcomponentes. (obligatorio)
- [x] **Estructura Composition API estándar**: Uso uniforme de `<script setup>` con orden estricto `<script>` → `<template>` → `<style>` en todos los componentes `.vue`.
- [x] **Desacoplamiento de lógica a composables**: Extracción de formateos de moneda, cálculos de porcentajes y reactividad a `composables/` (`useDashboardCharts`, `useFastFlowScraper`, `useSocialMetrics`, `useFormatters`, `useModalKeydown`).
- [x] **Soporte Dual fluido (War Room vs. Executive Light)**: Clases Tailwind condicionales coherentes (`bg-slate-950` / `bg-slate-50`) sincronizadas con `brandingpo_theme` y sin parpadeos de estilo.
- [x] **Control de envíos duplicados**: Formularios y botones de acción en modales (`FeedFastFlowModal`, edición de perfiles) deshabilitados durante peticiones en curso con indicador de carga reactivo (`:disabled="createForm.processing"`, `isScraping`).
- [x] **Optimización de renderizado en feeds masivos**: Feeds con lazy pagination e interactividad reactiva por tarjetas.
- [x] **Manejo de errores visuales y enlaces rotos**: Imágenes de perfiles y notas con `referrerpolicy="no-referrer"` ante bloqueos de CDN de Meta o URLs caducadas.

## Hallazgos de la Medición — Vistas Críticas (> 800 lín) [TODAS RESUELTAS ✅]
- [x] **Dividir `DashboardChartsSection.vue` (1.938 lín -> 171 lín)**: Descompuesto en subcomponentes: `DashboardEvolutionTrilogy.vue`, `DashboardSocialDistribution.vue`, `DashboardThemeEjesGrid.vue`, `DashboardChartFullscreenModal.vue` y composable reactivo `useDashboardCharts.js`.
- [x] **Dividir `SocialCard.vue` (1.065 lín -> 737 lín)**: Extraídos `SocialAdBadge.vue`, `SocialReactionsBar.vue`, `SocialCommentsThermometer.vue`, `SocialPautaHistory.vue`, `SocialDeleteModal.vue`, `SocialPautaAlertModal.vue`.
- [x] **Dividir `ImpactoElectoral.vue` (989 lín -> 392 lín)**: Separado en `ElectoralDonutsSection.vue`, `ElectoralTiersMatrix.vue`, `ElectoralDemographicPyramid.vue`, `ElectoralCampaignTiming.vue`, `ElectoralBudgetSimulator.vue`.
- [x] **Dividir `FeedFastFlowModal.vue` (971 lín -> 496 lín)**: Desacoplado en composable `useFastFlowScraper.js` y subcomponentes `FastFlowAdBanner.vue`, `FastFlowDuplicateAlert.vue`, `FastFlowLastPostInfo.vue`, `FastFlowMediaPreview.vue`, `FastFlowPautaSection.vue`.

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
