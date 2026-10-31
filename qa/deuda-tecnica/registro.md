# Registro de Deuda Técnica (fuente de verdad)

Los IDs **no cambian** entre auditorías. Estados: Abierto · En progreso · Parcial · Resuelto · Discutido · Riesgo aceptado · Regresión.
Alcance vigente: ver `alcance.md`. Última actualización: 2026-10-31 · commit `4334159`.

| ID | Sev. | Área | Título | Regla | Detectado | Estado | Cierre | Tarea |
|---|---|---|---|---|---|---|---|---|
| AUD-001 | Alta | Frontend | Componente `DashboardChartsSection.vue` supera umbral crítico (1.938 lín) | Componentes UI < 500 lín | 2026-10-31 | Abierto | — | Extraer gráficos a subcomponentes dedicados |
| AUD-002 | Alta | Backend | `DashboardController.php` acumula 1.192 lín y queries directas | Thin Controllers < 200 lín | 2026-10-31 | Abierto | — | Delegar agregaciones en servicios y scopes |
| AUD-003 | Alta | Backend | `PublicacionController.php` acumula 980 lín y scraping acoplado | Thin Controllers < 200 lín | 2026-10-31 | Abierto | — | Extraer sincronización a `PublicacionSyncService` |
| AUD-004 | Media | Frontend | Componente `SocialCard.vue` supera umbral crítico (1.065 lín) | Componentes UI < 500 lín | 2026-10-31 | Abierto | — | Descomponer reacciones y embeds |
| AUD-005 | Media | Backend | `SocialProfileScraperService.php` monolítico con 1.761 lín | Principio de Responsabilidad Única | 2026-10-31 | Abierto | — | Dividir por adaptadores de plataforma |
| AUD-006 | Baja | Git | Ausencia de etiquetado de versiones (0 tags en repo) | SemVer y trazabilidad de despliegue | 2026-10-31 | Abierto | — | Crear tags versionados en hitos estables |

## Notas privadas para fases futuras (no se auditan hoy)
- Monitoreo de cuotas y proxies rotativos para scraping automatizado a gran escala en caso de despliegue multi-tenant.

## Historial de auditorías
| # | Fecha | Commit | Alcance | Semáforo | Críticas | Altas | Medias | Bajas | Resueltas |
|---|---|---|---|---|---|---|---|---|---|
| 0 | 2026-10-31 | `4334159` | Baseline inicial de instalación QA | 🟡 GO con riesgos | 0 | 3 | 2 | 1 | 0 |
