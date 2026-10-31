# Ranking de complejidad — BrandingPo

> Generado: 2026-10-31 · commit `4334159` · rama `main`

## Vistas y componentes frontend (Vue) (umbral 500 lín · crítico 800 lín)

Archivos: 59 · sobre umbral: **14** · críticos: **4**

| Líneas | Estado | Archivo | Acceso a datos |
|---:|---|---|---|
| 1938 | 🔴 Crítico | `resources/js/Components/Dashboard/DashboardChartsSection.vue` | — |
| 1065 | 🔴 Crítico | `resources/js/Components/SocialCard.vue` | — |
| 989 | 🔴 Crítico | `resources/js/Pages/Territorios/ImpactoElectoral.vue` | — |
| 971 | 🔴 Crítico | `resources/js/Components/Feed/FeedFastFlowModal.vue` | — |
| 795 | 🟡 Sobre umbral | `resources/js/Components/SocialCardEditModal.vue` | — |
| 715 | 🟡 Sobre umbral | `resources/js/Pages/Ejes/Index.vue` | — |
| 658 | 🟡 Sobre umbral | `resources/js/Pages/Reportes/Ejecutivo.vue` | — |
| 655 | 🟡 Sobre umbral | `resources/js/Pages/Calendario/Index.vue` | — |
| 638 | 🟡 Sobre umbral | `resources/js/Pages/Candidatos/MetricasCanal.vue` | — |
| 623 | 🟡 Sobre umbral | `resources/js/Pages/Territorios/Index.vue` | — |
| 623 | 🟡 Sobre umbral | `resources/js/Components/Candidatos/CandidatoCanalFicha.vue` | — |
| 587 | 🟡 Sobre umbral | `resources/js/Components/Medios/MedioNotaModal.vue` | — |
| 553 | 🟡 Sobre umbral | `resources/js/Pages/Crisis/Index.vue` | — |
| 521 | 🟡 Sobre umbral | `resources/js/Pages/Publicaciones/Feed.vue` | — |
| 451 | 🔵 Moderado | `resources/js/Components/Candidatos/CandidatoCanalConfigModal.vue` | — |
| 438 | 🔵 Moderado | `resources/js/Pages/Analytics/Index.vue` | — |
| 436 | 🔵 Moderado | `resources/js/Components/Medios/MedioFormModal.vue` | — |
| 382 | 🔵 Moderado | `resources/js/Components/Dashboard/DashboardKpis.vue` | — |
| 353 | 🔵 Moderado | `resources/js/Components/Feed/Inputs/FeedPlatformMetricsForm.vue` | — |
| 351 | 🔵 Moderado | `resources/js/Components/MetricasCanal/MetricasChartModal.vue` | — |
| 309 | 🔵 Moderado | `resources/js/Pages/Presupuesto/Index.vue` | — |
| 309 | 🔵 Moderado | `resources/js/Components/MetricasCanal/ConsistenciaYHistoricoSection.vue` | — |
| 291 | 🟢 Óptimo | `resources/js/Pages/Medios/Index.vue` | — |
| 283 | 🟢 Óptimo | `resources/js/Components/MetricasCanal/MetricasCanalHeader.vue` | — |
| 275 | 🟢 Óptimo | `resources/js/Components/Medios/MedioFacebookFeed.vue` | — |
| 274 | 🟢 Óptimo | `resources/js/Pages/Usuarios/Index.vue` | — |
| 272 | 🟢 Óptimo | `resources/js/Components/Candidatos/CandidatoKpisTerritoriales.vue` | — |
| 265 | 🟢 Óptimo | `resources/js/Components/Badge.vue` | — |
| 264 | 🟢 Óptimo | `resources/js/Components/Medios/MedioDirectorioGrid.vue` | — |
| 263 | 🟢 Óptimo | `resources/js/Components/Candidatos/CandidatoFeedSection.vue` | — |

**Acción sugerida:** Extraer subcomponentes a Components/ o lógica reactiva a composables/

## Controladores backend (Laravel) (umbral 200 lín · crítico 300 lín)

Archivos: 15 · sobre umbral: **7** · críticos: **6** · con acceso directo a datos: **12**

| Líneas | Estado | Archivo | Acceso a datos |
|---:|---|---|---|
| 1192 | 🔴 Crítico | `app/Http/Controllers/DashboardController.php` | `->where\(` (L34) |
| 980 | 🔴 Crítico | `app/Http/Controllers/PublicacionController.php` | `->where\(` (L174) |
| 723 | 🔴 Crítico | `app/Http/Controllers/TerritorioController.php` | `->where\(` (L39) |
| 513 | 🔴 Crítico | `app/Http/Controllers/CandidatoController.php` | `->where\(` (L57) |
| 496 | 🔴 Crítico | `app/Http/Controllers/MediosController.php` | `->where\(` (L46) |
| 486 | 🔴 Crítico | `app/Http/Controllers/EjesEstrategicosController.php` | `->where\(` (L27) |
| 206 | 🟡 Sobre umbral | `app/Http/Controllers/CalendarioController.php` | `->where\(` (L34) |
| 168 | 🔵 Moderado | `app/Http/Controllers/CrisisController.php` | `->where\(` (L36) |
| 125 | 🔵 Moderado | `app/Http/Controllers/PresupuestoController.php` | `->where\(` (L29) |
| 117 | 🟢 Óptimo | `app/Http/Controllers/AnalyticsController.php` | `->where\(` (L44) |
| 117 | 🟢 Óptimo | `app/Http/Controllers/UserController.php` | — |
| 116 | 🟢 Óptimo | `app/Http/Controllers/BriefingController.php` | `->where\(` (L30) |
| 112 | 🟢 Óptimo | `app/Http/Controllers/AuthController.php` | — |
| 51 | 🟢 Óptimo | `app/Http/Controllers/ReporteController.php` | `->where\(` (L32) |
| 9 | 🟢 Óptimo | `app/Http/Controllers/Controller.php` | — |

**Acción sugerida:** Mover lógica a app/Services o FormRequests; el controlador solo adapta HTTP

## Servicios de negocio (Laravel) (umbral 300 lín · crítico 500 lín)

Archivos: 9 · sobre umbral: **6** · críticos: **5**

| Líneas | Estado | Archivo | Acceso a datos |
|---:|---|---|---|
| 1761 | 🔴 Crítico | `app/Services/SocialProfileScraperService.php` | — |
| 1028 | 🔴 Crítico | `app/Services/CanalMetricasService.php` | — |
| 732 | 🔴 Crítico | `app/Services/GenericMediaScraperService.php` | — |
| 537 | 🔴 Crítico | `app/Services/ReporteEjecutivoService.php` | — |
| 508 | 🔴 Crítico | `app/Services/DemographicIntelligenceService.php` | — |
| 310 | 🟡 Sobre umbral | `app/Services/CandidatoPresenterService.php` | — |
| 192 | 🔵 Moderado | `app/Services/MediaStorageService.php` | — |
| 162 | 🟢 Óptimo | `app/Services/PoliticaEngagementService.php` | — |
| 126 | 🟢 Óptimo | `app/Services/AdsImpactPredictorService.php` | — |

**Acción sugerida:** Dividir responsabilidades y extraer scrapers o procesadores específicos

## Control de versiones

- Commits totales: 135
- Convención de commits (últimos 50): **100%**
- Tags: 0 — **sin tags de versión**

## Documentación

- Última actualización de docs (README.md, doc, GEMINI.md, plan_flujo.md, plan_maestro.md): nunca
- Último cambio de código: —
- Commits de código desde la última actualización de docs: **—**
