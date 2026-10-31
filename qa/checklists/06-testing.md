# 06 — Testing y Verificación Automatizada

## Cobertura de Lógica de Negocio Crítica
- [ ] **Pruebas unitarias de algoritmos de engagement**: Cobertura automática para el cálculo del Índice de Aprobación Neta, ponderación de interacciones y clasificación del termómetro de humor social.
- [ ] **Pruebas del detector automático de crisis**: Verificación de que la regla de alerta roja ante indignación (😡 > 15%) se dispare fehacientemente ante diferentes combinaciones de reacciones.
- [ ] **Pruebas de cálculo de Punto Cero**: Validación del cálculo de crecimiento neto de seguidores, publicaciones y tracción de canales respecto a la fecha alfa.

## Pruebas de Autorización y Flujos de Campaña
- [ ] **Pruebas de permisos por rol (RBAC)**: Tests de integración que comprueben rechazo HTTP 403 para usuarios con rol `visualizador` intentando crear o editar publicaciones, perfiles o territorios.
- [ ] **Aislamiento de workspaces en tests**: Casos de prueba que confirmen que un usuario autenticado no pueda acceder ni mutar datos pertenecientes a otro workspace.

## Aislamiento de Entornos y Simulación
- [ ] **Entorno de pruebas efímero**: Pruebas configuradas en `phpunit.xml` con base de datos SQLite en memoria (`:memory:`) sin alterar la base de datos de desarrollo o producción.
- [ ] **Simulación de scrapers externos (Mocking)**: Pruebas automatizadas sin realizar llamadas HTTP reales a servidores de Meta, TikTok, X ni portales periodísticos (`Http::fake()`).
- [ ] **Ausencia de datos personales reales en tests**: Suites de prueba utilizando Faker o datos sintéticos aislados, sin volcar información confidencial de dirigentes o equipos de campaña.
