# 06 — Testing y Verificación Automatizada

## Cobertura de Lógica de Negocio Crítica
- [x] **Pruebas unitarias de algoritmos de engagement**: Cobertura automática para el cálculo del Índice de Aprobación Neta, ponderación de interacciones y clasificación de tiers en `PoliticaEngagementTest.php`.
- [x] **Pruebas del detector automático de crisis**: Verificación de alertas y umbrales de severidad ante crisis en `MediosAndCrisisTest.php` y `MediosObservatoryAndScraperTest.php`.
- [x] **Pruebas de cálculo de Punto Cero**: Validación del cálculo de crecimiento neto de seguidores, publicaciones y tracción de canales respecto a la fecha alfa en `AuditoriaPerfilesSocialesTest.php` y `MetricasCanalTest.php`.

## Pruebas de Autorización y Flujos de Campaña
- [x] **Pruebas de permisos por rol (RBAC)**: Tests de integración que comprueban rechazo HTTP 403 para usuarios con rol `visualizador` intentando crear o editar publicaciones, perfiles o territorios en `AuthenticationAndRolesTest.php` y `SecurityHardeningTest.php`.
- [x] **Aislamiento de workspaces en tests**: Casos de prueba exhaustivos que confirman que un usuario autenticado no pueda acceder ni mutar datos pertenecientes a otro workspace (`SecurityHardeningTest::test_bloqueo_acceso_cross_workspace_candidato`).

## Aislamiento de Entornos y Simulación
- [x] **Entorno de pruebas efímero**: Pruebas configuradas en `phpunit.xml` con base de datos SQLite en memoria (`DB_DATABASE=:memory:`) ejecutando 64 tests y 362 aserciones sin alterar la base de datos de desarrollo o producción.
- [x] **Simulación de scrapers externos (Mocking)**: Pruebas automatizadas sin realizar llamadas HTTP externas reales a Meta, TikTok, X ni portales periodísticos en `MediosObservatoryAndScraperTest.php` y `PautaDetectionTest.php`.
- [x] **Ausencia de datos personales reales en tests**: Suites de prueba utilizando entidades aisladas y datos sintéticos creados exclusivamente en el ciclo efímero de prueba.
