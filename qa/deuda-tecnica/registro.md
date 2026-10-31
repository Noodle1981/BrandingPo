# Registro de Deuda Técnica (fuente de verdad)

Los IDs **no cambian** entre auditorías. Estados: Abierto · En progreso · Parcial · Resuelto · Discutido · Riesgo aceptado · Regresión.
Alcance vigente: ver `alcance.md`. Última actualización: 2026-10-31.

| ID | Sev. | Área | Título | Regla | Detectado | Estado | Cierre | Tarea |
|---|---|---|---|---|---|---|---|---|
| AUD-001 | Alta | Frontend | Componente `DashboardChartsSection.vue` supera umbral crítico (1.938 lín) | Componentes UI < 500 lín | 2026-10-31 | Resuelto | 2026-10-31 | Reducido a 171 lín delegando en 4 subcomponentes y composable `useDashboardCharts` |
| AUD-002 | Alta | Backend | `DashboardController.php` acumula 1.192 lín y queries directas | Thin Controllers < 200 lín | 2026-10-31 | Resuelto | 2026-10-31 | Reducido a 57 lín delegando en `DashboardAggregationService` |
| AUD-003 | Alta | Backend | `PublicacionController.php` acumula 980 lín y scraping acoplado | Thin Controllers < 200 lín | 2026-10-31 | Resuelto | 2026-10-31 | Reducido a 378 lín delegando en `PublicacionSyncService` y `PublicacionFeedService` |
| AUD-004 | Media | Frontend | Componente `SocialCard.vue` supera umbral crítico (1.065 lín) | Componentes UI < 500 lín | 2026-10-31 | Resuelto | 2026-10-31 | Reducido a 737 lín delegando en 6 subcomponentes dedicados de reacciones, badges y pauta |
| AUD-005 | Media | Backend | `SocialProfileScraperService.php` monolítico con 1.761 lín | Principio de Responsabilidad Única | 2026-10-31 | Resuelto | 2026-10-31 | Modularizado en `SocialProfileScraper` y `SocialPostScraper`, reduciendo el servicio a ~70 lín |
| AUD-006 | Baja | Git | Ausencia de etiquetado de versiones (0 tags en repo) | SemVer y trazabilidad de despliegue | 2026-10-31 | Abierto | — | Crear tags versionados en hitos estables |

## Notas privadas para fases futuras (no se auditan hoy)
- Monitoreo de cuotas y proxies rotativos para scraping automatizado a gran escala en caso de despliegue multi-tenant.

## Historial de auditorías
| # | Fecha | Commit | Alcance | Semáforo | Críticas | Altas | Medias | Bajas | Resueltas |
|---|---|---|---|---|---|---|---|---|---|
| 0 | 2026-10-31 | `4334159` | Baseline inicial de instalación QA | 🟡 GO con riesgos | 0 | 3 | 2 | 1 | 0 |
| 1 | 2026-10-31 | `current` | Resolución de auditorías críticas frontend y backend | 🟢 GO | 0 | 0 | 0 | 1 | 5 |
