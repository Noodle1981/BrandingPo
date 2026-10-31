# 02 — Backend y Arquitectura Laravel

## Arquitectura y Separación de Responsabilidades
- [ ] **Controladores delgados (Thin Controllers < 200 lín)**: Los controladores actúan como adaptadores HTTP finos sin acumular lógica de negocio ni cálculos complejos. (obligatorio)
- [ ] **Cero acceso directo a datos en controladores**: Prohibido inyectar o consultar directamente la base de datos (queries raw o encadenamientos complejos `->where(`) desde controladores; toda interacción se delega a servicios, query scopes o repositorios. (obligatorio)
- [ ] **Validación mediante Form Requests**: Reemplazar validaciones manuales `$request->validate([...])` dispersas en controladores por clases de `FormRequest` tipadas con autorización (`authorize()`) y reglas semánticas.
- [ ] **Transformación y serialización de datos**: Uso de Resources o Transformers de Laravel para estructurar los datos enviados al frontend mediante Inertia, evitando exponer modelos Eloquent completos con atributos sensibles.
- [ ] **Manejo centralizado de errores y excepciones**: Excepciones de servicios externos (scrapers caídos, timeouts de red) capturadas ordenadamente sin volcar error 500 al cliente.
- [ ] **Encapsulación de lógica de scrapers**: Scrapers de perfiles sociales y medios periodísticos encapsulados con interfaces claras, respetando SRP y manejando reintentos con backoff exponencial.

## Hallazgos de la Medición — Controladores sobre Umbral
- [x] **Dividir `DashboardController.php` (1.192 lín)**: Extraer agregaciones de métricas territoriales, pauta y engagement a `DashboardAggregationService` o query scopes dedicados. (Resuelto: reducido a 57 líneas).
- [x] **Dividir `PublicacionController.php` (980 lín)**: Separar sincronización de feeds y scraping de posts a `PublicacionSyncService`; mover filtros de búsqueda a `PublicacionFeedService`. (Resuelto: reducido a 378 líneas delegando lógica a servicios dedicados).
- [ ] **Dividir `TerritorioController.php` (723 lín)**: Desacoplar cálculos de penetración electoral y cruce demográfico hacia `DemographicIntelligenceService`.
- [ ] **Dividir `CandidatoController.php` (513 lín)**: Delegar orquestación de perfiles sociales y sincronización de canales a `CandidatoPresenterService` y `CanalMetricasService`.
- [ ] **Dividir `MediosController.php` (496 lín)**: Mover autodescubrimiento de fuentes y análisis de tono editorial hacia `GenericMediaScraperService`.
- [ ] **Dividir `EjesEstrategicosController.php` (486 lín)**: Extraer consolidación de sentimiento temático y cálculo de impacto porcentual a un servicio de dominio.
- [ ] **Refactorizar `CalendarioController.php` (206 lín)**: Extraer lógica de agrupamiento por fecha y estado de publicaciones a un método de servicio o scope.
- [ ] **Eliminar acceso directo a datos en controladores**: Migrar las consultas `->where(` identificadas en 12 controladores hacia scopes locales en los modelos (`scopeDelWorkspace`, `scopeActivos`, `scopeConSentimiento`).

## Hallazgos de la Medición — Servicios de Negocio sobre Umbral
- [ ] **Modularizar `SocialProfileScraperService.php` (1.761 lín)**: Dividir en scrapers especializados por red social (`InstagramScraper`, `FacebookScraper`, `TikTokScraper`, `TwitterScraper`, `YouTubeScraper`) bajo una interfaz común `SocialScraperInterface`.
- [ ] **Modularizar `CanalMetricasService.php` (1.028 lín)**: Separar cálculo de Punto Cero / Punto Alfa de la lógica de normalización de engagement por plataforma.
- [ ] **Modularizar `GenericMediaScraperService.php` (732 lín)**: Desacoplar el parser de feed RSS del extractor de contenido HTML y análisis de menciones de candidatos.
