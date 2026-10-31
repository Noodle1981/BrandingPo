# 01 — Seguridad y Control de Accesos

## Control de Acceso y Autorización (RBAC)
- [x] **Aislamiento por Workspace activo**: Todo acceso a candidatos, publicaciones y analítica valida que pertenezcan al `active_workspace_id` de la sesión en `web.php` y en cada consulta mediante `EnsureWorkspaceActive` y `WorkspaceHelper::validarPertenencia`.
- [x] **Permisos de escritura estrictos**: Verificación de middleware `can_write` en todas las rutas de mutación (`POST`, `PUT`, `PATCH`, `DELETE`) incluyendo `auto-detect`, impidiendo que el rol `visualizador` altere datos (rechazo HTTP 403).
- [x] **Prevención de escalado horizontal (IDOR/BOLA)**: Las operaciones sobre `perfiles-sociales`, `publicaciones` y `candidatos` validan propiedad y pertenencia al workspace antes de actualizar o eliminar con `WorkspaceHelper::validarPertenencia`.
- [x] **Control de cambio de workspace**: Verificación en `/workspace/cambiar/{workspace}` de que usuarios no administradores solo puedan cambiar a workspaces asignados en su relación (abort 403 ante manipulación de ID).

## Autenticación y Manejo de Sesión
- [x] **Seguridad de sesión en producción**: Configuración en `config/session.php` con `secure => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production')`, `http_only => true`, `same_site => lax` y serialización segura en JSON para mitigar ataques de deserialización.
- [x] **Protección de endpoints de acceso rápido**: El endpoint `/quick-login` está estrictamente restringido al entorno local/testing (`! app()->environment('local', 'testing') => abort(403)`) y los botones de demostración en `Login.vue` se ocultan condicionalmente con `v-if="allowQuickLogin"`.
- [x] **Rate Limiting activo**: Middleware `throttle:auth` (5 intentos/min en prod) en login y `throttle:scraping` (20 llamadas/min) en operaciones de sincronización de perfiles y scraping para prevenir ataques de denegación o bloqueo de IPs por CDNs de Meta/TikTok.

## Sanitización, Inyección y Cabeceras
- [x] **Prevención de inyección SQL**: Consultas Eloquent parametrizadas sin interpolación de variables directas en `DB::raw()` o `whereRaw()`.
- [x] **Sanitización de contenido externo y XSS**: Datos raspados de redes y notas periodísticas renderizados con escape estándar en Vue (`{{ }}`), sin uso de `v-html` en todo el frontend.
- [x] **Cabeceras HTTP de protección y referrer**: Meta tag `referrerpolicy="no-referrer"` en imágenes de CDNs externas para evitar bloqueos 403, `<meta name="referrer">` en `app.blade.php` y cabeceras de seguridad CSP/HSTS/X-Frame-Options inyectadas por `SecurityHeaders`.
- [x] **Gestión de variables de entorno**: `.env` excluido del control de versiones; sin claves secretas (`APP_KEY`) ni credenciales hardcodeadas en controladores ni servicios, con guarda de validación en `AppServiceProvider::boot()`.
