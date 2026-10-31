# 04 — Base de Datos y Persistencia SQLite

## Integridad de Datos Reales y Protección de Campaña
- [x] **Prohibición absoluta de seeders sintéticos en publicaciones**: Guard estricto de entorno en `PublicacionSeeder.php` que aborta y emite advertencia ante cualquier intento de ejecución en entornos no aislados (local, staging, producción), cumpliendo la regla de negocio `datos-reales-publicaciones`. (obligatorio de negocio)
- [x] **Protección contra pérdida o truncado accidental**: Publicaciones protegidas contra borrado masivo accidental con verificación de integridad referencial.
- [x] **Trazabilidad de origen y URLs legítimas**: Toda publicación registrada procesada con canonicalización automática (`SocialProfileScraperService::canonicalizePostUrl`) y verificación previa contra duplicados por URL o hash de contenido.

## Migraciones, Esquemas y Relaciones Eloquent
- [x] **Migraciones versionadas**: Esquema administrado exclusivamente mediante migraciones cronológicas en `database/migrations/`.
- [x] **Prevención de problemas N+1**: Consultas de listados (candidatos con perfiles, publicaciones con reacciones y medios) ejecutadas con Eager Loading explícito (`with(['candidato', 'perfilSocial', 'ejeTematico'])`).
- [x] **Integridad referencial y claves foráneas**: Restricciones de clave foránea configuradas explícitamente (`foreignId()->constrained()`) con política de cascada (`onDelete('cascade')` en tablas secundarias, `nullOnDelete()` en relaciones opcionales).
- [x] **Transaccionalidad en operaciones compuestas**: Operaciones de guardado y actualización de publicaciones con recuento de perfiles y trazabilidad de pauta ejecutadas dentro de `DB::transaction()`.

## Optimización para SQLite y Consultas Frecuentes
- [x] **Índices compuestos de rendimiento**: Creados y migrados 9 índices compuestos en tablas críticas (`publicaciones` y `notas_prensa`) combinando `workspace_id`, `fecha_publicacion`, `candidato_id`, `perfil_social_id`, `tipo_pauta` y `tono_mencion` (`2026_10_31_180000_add_performance_indexes_to_core_tables.php`).
- [x] **Proyecciones acotadas**: Consultas agregadas del Dashboard (`DashboardAggregationService`, `PublicacionFeedService`) optimizadas con proyecciones acotadas para evitar transferencias innecesarias de JSON masivo.
- [x] **Configuración de concurrencia SQLite**: Configuración de alto rendimiento en `config/database.php` con modo WAL (`journal_mode = WAL`), espera activa de 5.000 ms (`busy_timeout = 5000`), sincronización balanceada (`synchronous = NORMAL`) y modo de transacción `IMMEDIATE` para prevenir bloqueos de concurrencia.
