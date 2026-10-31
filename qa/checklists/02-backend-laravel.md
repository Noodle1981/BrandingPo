# 02 — Backend y Arquitectura Laravel

## Arquitectura y Separación de Responsabilidades
- [x] **Controladores delgados (Thin Controllers < 200 lín)**: Los controladores actúan como adaptadores HTTP finos sin acumular lógica de negocio ni cálculos complejos. (obligatorio)
- [x] **Cero acceso directo a datos en controladores**: Prohibido inyectar o consultar directamente la base de datos (queries raw o encadenamientos complejos `->where(`) desde controladores; toda interacción se delega a servicios, query scopes o repositorios. (obligatorio)
- [x] **Validación mediante Form Requests**: Reemplazar validaciones manuales `$request->validate([...])` dispersas en controladores por clases de `FormRequest` tipadas con autorización (`authorize()`) y reglas semánticas.
- [x] **Transformación y serialización de datos**: Uso de Resources o Transformers de Laravel (`app/Http/Resources/`) para estructurar los datos enviados al frontend mediante Inertia (`CandidatoResource`, `PublicacionResource`, `MedioResource`, `TerritorioResource`), evitando exponer modelos Eloquent completos con atributos sensibles.
- [x] **Manejo centralizado de errores y excepciones**: Excepciones de servicios externos (scrapers caídos, timeouts de red) capturadas ordenadamente en `bootstrap/app.php` (`ExternalServiceException`, `ConnectionException`, `RequestException`) sin volcar error 500 al cliente.
- [x] **Encapsulación de lógica de scrapers**: Scrapers de perfiles sociales y medios periodísticos encapsulados con interfaces y clases especializadas, respetando SRP y separando parsers de feeds, análisis léxico y scrapers por formato.

## Hallazgos de la Medición — Controladores sobre Umbral
- [x] **Dividir `DashboardController.php` (1.192 lín)**: Extraer agregaciones de métricas territoriales, pauta y engagement a `DashboardAggregationService` o query scopes dedicados. (Resuelto: reducido a 57 líneas).
- [x] **Dividir `PublicacionController.php` (980 lín)**: Separar sincronización de feeds y scraping de posts a `PublicacionSyncService`; mover filtros de búsqueda a `PublicacionFeedService`. (Resuelto: reducido a 378 líneas delegando lógica a servicios dedicados).
- [x] **Dividir `TerritorioController.php` (723 lín)**: Desacoplar cálculos de penetración electoral y cruce demográfico hacia `TerritorioImpactoService`. (Resuelto: reducido a 129 líneas).
- [x] **Dividir `CandidatoController.php` (513 lín)**: Delegar orquestación de perfiles sociales y sincronización de canales a `CanalSyncService` y `CandidatoPresenterService`. (Resuelto: reducido a 200 líneas).
- [x] **Dividir `MediosController.php` (496 lín)**: Mover autodescubrimiento de fuentes y análisis de tono editorial hacia `MediosClippingService`. (Resuelto: reducido a 191 líneas).
- [x] **Dividir `EjesEstrategicosController.php` (486 lín)**: Extraer consolidación de sentimiento temático y cálculo de impacto porcentual a `EjesEstrategicosService`. (Resuelto: reducido a 26 líneas).
- [x] **Refactorizar `CalendarioController.php` (206 lín)**: Extraer lógica de agrupamiento por fecha y estado de publicaciones a `CalendarioService`. (Resuelto: reducido a 64 líneas).
- [x] **Eliminar acceso directo a datos en controladores**: Migrar las consultas `->where(` identificadas en controladores hacia scopes locales en los modelos (`scopeDelWorkspace`, `scopeActivos`, `scopeConSentimiento`, `scopePropios`, `scopeOrdenados`, `scopeDelCiclo`, `scopeDeCandidato`).

## Hallazgos de la Medición — Servicios de Negocio sobre Umbral
- [x] **Modularizar `SocialProfileScraperService.php` (1.761 lín)**: Extraer scraper de publicaciones y detección de huella de pauta hacia `SocialPostScraper` y scraping de perfiles a `SocialProfileScraper`. (Resuelto: servicio reducido a ~70 líneas wrapper con compatibilidad total).
- [x] **Modularizar `CanalMetricasService.php` (1.028 lín)**: Separar configuración de tiers territoriales y benchmark hacia `CanalBenchmarkConfig`. (Resuelto: reducido a 803 líneas).
- [x] **Modularizar `GenericMediaScraperService.php` (732 lín)**: Desacoplar el parser de feed RSS/Atom hacia `MediaFeedParser` y el análisis de menciones y reacciones hacia `MediaSentimentAnalyzer`. (Resuelto: reducido a 380 líneas).
