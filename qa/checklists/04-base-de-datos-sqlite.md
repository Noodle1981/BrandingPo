# 04 — Base de Datos y Persistencia SQLite

## Integridad de Datos Reales y Protección de Campaña
- [ ] **Prohibición absoluta de seeders sintéticos en publicaciones**: Verificación de que no existan factories, seeders (`PublicacionSeeder`) ni comandos de testing que inyecten registros ficticios (`demo-*`) en la tabla `publicaciones`. (obligatorio de negocio)
- [ ] **Protección contra pérdida o truncado accidental**: Comandos tipo `migrate:fresh` o scripts de inicialización deben incluir salvaguardas explícitas para no destruir el histórico de publicaciones reales auditadas.
- [ ] **Trazabilidad de origen y URLs legítimas**: Toda publicación registrada debe contener una URL verídica hacia la red social de origen (`url_post`), sin enlaces simulados.

## Migraciones, Esquemas y Relaciones Eloquent
- [ ] **Migraciones versionadas**: Esquema de base de datos administrado exclusivamente mediante migraciones Laravel versionadas y ordenadas cronológicamente en `database/migrations/`.
- [ ] **Prevención de problemas N+1**: Consultas de listados (candidatos con perfiles, publicaciones con reacciones y medio) con Eager Loading explícito (`with(['candidato', 'perfilSocial', 'medio'])`).
- [ ] **Integridad referencial y claves foráneas**: Restricciones de clave foránea configuradas explícitamente (`foreignId()->constrained()`) con política de cascada adecuada (`onDelete('cascade')` en tablas secundarias, `restrict` en entidades principales).
- [ ] **Transaccionalidad en operaciones compuestas**: Sincronización de canales, importaciones de notas y creación de publicaciones agrupadas bajo `DB::transaction()` para garantizar consistencia atómica.

## Optimización para SQLite y Consultas Frecuentes
- [ ] **Índices compuestos de rendimiento**: Índices en tablas críticas (`publicaciones`, `menciones_medios`) combinando `workspace_id`, `created_at` y campos de filtrado (`sentimiento`, `tipo_pauta`).
- [ ] **Proyecciones acotadas**: Uso de `select()` en consultas pesadas del Dashboard para evitar traer columnas pesadas de texto o metadatos JSON completos cuando solo se requieren conteos o IDs.
- [ ] **Configuración de concurrencia SQLite**: Verificación de modo WAL (`journal_mode = WAL`) y timeout adecuado de bloqueo de base de datos en `config/database.php`.
