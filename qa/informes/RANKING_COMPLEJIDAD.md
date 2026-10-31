# Ranking de complejidad — BrandingPo

> Generado: 2026-10-31 · commit `fd9de59` · rama `main`

## Vistas y componentes frontend (Vue) (umbral 500 lín · crítico 800 lín)

Archivos: 79 · sobre umbral: **12** · críticos: **0**

| Líneas | Estado | Archivo | Acceso a datos |
|---:|---|---|---|
| 795 | 🟡 Sobre umbral | `resources/js/Components/SocialCardEditModal.vue` | — |
| 737 | 🟡 Sobre umbral | `resources/js/Components/SocialCard.vue` | — |
| 715 | 🟡 Sobre umbral | `resources/js/Pages/Ejes/Index.vue` | — |
| 658 | 🟡 Sobre umbral | `resources/js/Pages/Reportes/Ejecutivo.vue` | — |
| 655 | 🟡 Sobre umbral | `resources/js/Pages/Calendario/Index.vue` | — |
| 638 | 🟡 Sobre umbral | `resources/js/Pages/Candidatos/MetricasCanal.vue` | — |
| 623 | 🟡 Sobre umbral | `resources/js/Pages/Territorios/Index.vue` | — |
| 623 | 🟡 Sobre umbral | `resources/js/Components/Candidatos/CandidatoCanalFicha.vue` | — |
| 587 | 🟡 Sobre umbral | `resources/js/Components/Medios/MedioNotaModal.vue` | — |
| 553 | 🟡 Sobre umbral | `resources/js/Pages/Crisis/Index.vue` | — |
| 541 | 🟡 Sobre umbral | `resources/js/Components/Feed/FeedFastFlowModal.vue` | — |
| 521 | 🟡 Sobre umbral | `resources/js/Pages/Publicaciones/Feed.vue` | — |
| 451 | 🔵 Moderado | `resources/js/Components/Candidatos/CandidatoCanalConfigModal.vue` | — |
| 438 | 🔵 Moderado | `resources/js/Pages/Analytics/Index.vue` | — |
| 436 | 🔵 Moderado | `resources/js/Components/Medios/MedioFormModal.vue` | — |
| 425 | 🔵 Moderado | `resources/js/Pages/Territorios/ImpactoElectoral.vue` | — |
| 386 | 🔵 Moderado | `resources/js/Components/Dashboard/Charts/DashboardChartFullscreenModal.vue` | — |
| 382 | 🔵 Moderado | `resources/js/Components/Dashboard/DashboardKpis.vue` | — |
| 353 | 🔵 Moderado | `resources/js/Components/Feed/Inputs/FeedPlatformMetricsForm.vue` | — |
| 351 | 🔵 Moderado | `resources/js/Components/MetricasCanal/MetricasChartModal.vue` | — |
| 339 | 🔵 Moderado | `resources/js/Components/Dashboard/Charts/DashboardEvolutionTrilogy.vue` | — |
| 309 | 🔵 Moderado | `resources/js/Pages/Presupuesto/Index.vue` | — |
| 309 | 🔵 Moderado | `resources/js/Components/MetricasCanal/ConsistenciaYHistoricoSection.vue` | — |
| 291 | 🟢 Óptimo | `resources/js/Pages/Medios/Index.vue` | — |
| 283 | 🟢 Óptimo | `resources/js/Components/MetricasCanal/MetricasCanalHeader.vue` | — |
| 275 | 🟢 Óptimo | `resources/js/Components/Medios/MedioFacebookFeed.vue` | — |
| 274 | 🟢 Óptimo | `resources/js/Pages/Usuarios/Index.vue` | — |
| 272 | 🟢 Óptimo | `resources/js/Components/Candidatos/CandidatoKpisTerritoriales.vue` | — |
| 265 | 🟢 Óptimo | `resources/js/Components/Badge.vue` | — |
| 264 | 🟢 Óptimo | `resources/js/Components/Medios/MedioDirectorioGrid.vue` | — |

**Acción sugerida:** Extraer subcomponentes a Components/ o lógica reactiva a composables/

## Controladores backend (Laravel) (umbral 200 lín · crítico 300 lín)

Archivos: 15 · sobre umbral: **1** · críticos: **1**

| Líneas | Estado | Archivo | Acceso a datos |
|---:|---|---|---|
| 304 | 🔴 Crítico | `app/Http/Controllers/PublicacionController.php` | — |
| 200 | 🔵 Moderado | `app/Http/Controllers/CandidatoController.php` | — |
| 191 | 🔵 Moderado | `app/Http/Controllers/MediosController.php` | — |
| 168 | 🔵 Moderado | `app/Http/Controllers/CrisisController.php` | — |
| 129 | 🔵 Moderado | `app/Http/Controllers/TerritorioController.php` | — |
| 125 | 🔵 Moderado | `app/Http/Controllers/PresupuestoController.php` | — |
| 120 | 🟢 Óptimo | `app/Http/Controllers/AnalyticsController.php` | — |
| 117 | 🟢 Óptimo | `app/Http/Controllers/UserController.php` | — |
| 116 | 🟢 Óptimo | `app/Http/Controllers/BriefingController.php` | — |
| 106 | 🟢 Óptimo | `app/Http/Controllers/AuthController.php` | — |
| 68 | 🟢 Óptimo | `app/Http/Controllers/CalendarioController.php` | — |
| 62 | 🟢 Óptimo | `app/Http/Controllers/DashboardController.php` | — |
| 50 | 🟢 Óptimo | `app/Http/Controllers/ReporteController.php` | — |
| 30 | 🟢 Óptimo | `app/Http/Controllers/EjesEstrategicosController.php` | — |
| 9 | 🟢 Óptimo | `app/Http/Controllers/Controller.php` | — |

**Acción sugerida:** Mover lógica a app/Services o FormRequests; el controlador solo adapta HTTP

## Servicios de negocio (Laravel) (umbral 300 lín · crítico 500 lín)

Archivos: 17 · sobre umbral: **10** · críticos: **6**

| Líneas | Estado | Archivo | Acceso a datos |
|---:|---|---|---|
| 1119 | 🔴 Crítico | `app/Services/DashboardAggregationService.php` | — |
| 803 | 🔴 Crítico | `app/Services/CanalMetricasService.php` | — |
| 596 | 🔴 Crítico | `app/Services/TerritorioImpactoService.php` | — |
| 537 | 🔴 Crítico | `app/Services/ReporteEjecutivoService.php` | — |
| 523 | 🔴 Crítico | `app/Services/PublicacionSyncService.php` | — |
| 508 | 🔴 Crítico | `app/Services/DemographicIntelligenceService.php` | — |
| 440 | 🟡 Sobre umbral | `app/Services/EjesEstrategicosService.php` | — |
| 413 | 🟡 Sobre umbral | `app/Services/CandidatoPresenterService.php` | — |
| 380 | 🟡 Sobre umbral | `app/Services/GenericMediaScraperService.php` | — |
| 359 | 🟡 Sobre umbral | `app/Services/MediosClippingService.php` | — |
| 259 | 🔵 Moderado | `app/Services/PublicacionFeedService.php` | — |
| 256 | 🔵 Moderado | `app/Services/CanalSyncService.php` | — |
| 192 | 🔵 Moderado | `app/Services/MediaStorageService.php` | — |
| 162 | 🟢 Óptimo | `app/Services/PoliticaEngagementService.php` | — |
| 156 | 🟢 Óptimo | `app/Services/CalendarioService.php` | — |
| 126 | 🟢 Óptimo | `app/Services/AdsImpactPredictorService.php` | — |
| 66 | 🟢 Óptimo | `app/Services/SocialProfileScraperService.php` | — |

**Acción sugerida:** Dividir responsabilidades y extraer scrapers o procesadores específicos

## Control de versiones

- Commits totales: 144
- Convención de commits (últimos 50): **100%**
- Tags: 1 (último: `v1.0.0`)

## Documentación

- Última actualización de docs (README.md, doc, GEMINI.md, plan_flujo.md, plan_maestro.md): nunca
- Último cambio de código: —
- Commits de código desde la última actualización de docs: **—**
