# 03 — Frontend y Experiencia de Usuario Vue 3

## Arquitectura de Componentes y Estado
- [x] **Límite de tamaño de vistas (< 500 lín)**: Se eliminaron el 100% de los componentes sobre umbral y críticos. 0 archivos sobre 500 líneas en todo el frontend de Vue. (obligatorio)
- [x] **Estructura Composition API estándar**: Uso uniforme de `<script setup>` con orden estricto `<script>` → `<template>` → `<style>` en todos los componentes `.vue`.
- [x] **Desacoplamiento de lógica a composables**: Extracción de gráficos, formateos de moneda, cálculos de porcentajes y reactividad a `composables/` (`useDashboardCharts`, `useMetricasCharts`, `useFastFlowScraper`, `useSocialMetrics`, `useFormatters`, `useModalKeydown`).
- [x] **Soporte Dual fluido (War Room vs. Executive Light)**: Clases Tailwind condicionales coherentes (`bg-slate-950` / `bg-slate-50`) sincronizadas con `brandingpo_theme` y sin parpadeos de estilo.
- [x] **Control de envíos duplicados**: Formularios y botones de acción en modales (`FeedFastFlowModal`, edición de perfiles, sincronización de canales) deshabilitados durante peticiones en curso con indicador de carga reactivo (`:disabled="form.processing"`, `isSyncingCanal`, `isDetecting`).
- [x] **Optimización de renderizado en feeds masivos**: Feeds con lazy pagination e interactividad reactiva por tarjetas.
- [x] **Manejo de errores visuales y enlaces rotos**: Imágenes de perfiles y notas con `referrerpolicy="no-referrer"` ante bloqueos de CDN de Meta o URLs caducadas.

## Hallazgos de la Medición — Vistas Críticas (> 800 lín) [TODAS RESUELTAS ✅]
- [x] **Dividir `DashboardChartsSection.vue` (1.938 lín -> 171 lín)**: Descompuesto en subcomponentes: `DashboardEvolutionTrilogy.vue`, `DashboardSocialDistribution.vue`, `DashboardThemeEjesGrid.vue`, `DashboardChartFullscreenModal.vue` y composable reactivo `useDashboardCharts.js`.
- [x] **Dividir `SocialCard.vue` (1.065 lín -> 318 lín)**: Extraídos `SocialCardHeader.vue`, `SocialCardBody.vue`, `SocialAdBadge.vue`, `SocialReactionsBar.vue`, `SocialCommentsThermometer.vue`, `SocialPautaHistory.vue`, `SocialDeleteModal.vue`, `SocialPautaAlertModal.vue`.
- [x] **Dividir `ImpactoElectoral.vue` (989 lín -> 425 lín)**: Separado en `ElectoralDonutsSection.vue`, `ElectoralTiersMatrix.vue`, `ElectoralDemographicPyramid.vue`, `ElectoralCampaignTiming.vue`, `ElectoralBudgetSimulator.vue`.
- [x] **Dividir `FeedFastFlowModal.vue` (971 lín -> 384 lín)**: Desacoplado en composable `useFastFlowScraper.js` y subcomponentes `FastFlowPostDetailsForm.vue`, `FastFlowAdBanner.vue`, `FastFlowDuplicateAlert.vue`, `FastFlowLastPostInfo.vue`, `FastFlowMediaPreview.vue`, `FastFlowPautaSection.vue`.

## Hallazgos de la Medición — Vistas sobre Umbral (500 a 800 lín) [TODAS RESUELTAS ✅]
- [x] **Modularizar `SocialCardEditModal.vue` (795 lín -> 485 lín)**: Separado formulario de metadatos del editor granular de métricas por plataforma (`SocialEditMetricsPanels.vue`).
- [x] **Modularizar `Pages/Ejes/Index.vue` (715 lín -> 390 lín)**: Extraída la sala de batalla (`EjesPilarBattleRoom.vue`) y tabla histórica (`EjesHistoricoTable.vue`) a subcomponentes en `Components/Ejes/`.
- [x] **Modularizar `Pages/Reportes/Ejecutivo.vue` (658 lín -> 321 lín)**: Extraídas secciones del reporte imprimible a `ReportePilaresYEvolucion.vue` y `ReporteTacticoEcosistema.vue`.
- [x] **Modularizar `Pages/Calendario/Index.vue` (655 lín -> 405 lín)**: Separada la cuadrícula de agenda mensual (`CalendarioAgendaGrid.vue`) del modal de eventos (`CalendarioEventoModal.vue`).
- [x] **Modularizar `Pages/Candidatos/MetricasCanal.vue` (638 lín -> 320 lín)**: Extraída configuración y reactividad de Chart.js al composable `useMetricasCharts.js`.
- [x] **Modularizar `Components/Candidatos/CandidatoCanalFicha.vue` (623 lín -> 230 lín)**: Extraídas métricas Punto Cero a `CandidatoCanalPuntoCeroGrid.vue` y modal de progreso a `CandidatoCanalSyncModal.vue`.
- [x] **Modularizar `Pages/Territorios/Index.vue` (623 lín -> 391 lín)**: Desacoplado modal y auto-detección Georef a `TerritorioFormModal.vue`.
- [x] **Modularizar `Components/Medios/MedioNotaModal.vue` (587 lín -> 440 lín)**: Extraído contexto de edición a `MedioNotaContextoHeader.vue` y campos de alta a `MedioNotaCreacionCampos.vue`.
- [x] **Modularizar `Pages/Crisis/Index.vue` (553 lín -> 366 lín)**: Extraídos modales a `CrisisModal.vue` y `CrisisAlianzaModal.vue`.
- [x] **Modularizar `Pages/Publicaciones/Feed.vue` (521 lín -> 345 lín)**: Extraído panel de filtros y buscador a `FeedFilterToolbar.vue`.
