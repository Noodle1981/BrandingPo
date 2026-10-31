# 07 — Legacy, Limpieza y Mantenibilidad

## Limpieza del Repositorio y Archivos Huérfanos
- [x] **Eliminación de volcados de base de datos obsoletos**: Retirado el volcado huérfano `database/database_old.sqlite` (352 KB) y agregadas las exclusiones correspondientes a `.gitignore` (`*.sqlite`, `*.sqlite-wal`, `*.sqlite-shm`).
- [x] **Unificación de gestores de paquetes**: Eliminada la duplicidad de gestores retirando `pnpm-lock.yaml` y consolidando `npm` con `package-lock.json` como el gestor oficial y consistente del proyecto.
- [x] **Sin artefactos temporales de build**: Verificación y protección de directorios temporales de compilación (`.vite/`, `public/build/`, `public/hot/`, `storage/framework/cache/`) correctamente ignorados en `.gitignore`.
- [x] **Limpieza de código comentado y funciones deprecadas**: Controladores (`PublicacionController`, `DashboardController`, `MediosController`) y componentes principales depurados y libres de bloques masivos de código muerto o comentado.

## Consistencia de Modelos y Migraciones
- [x] **Sin migraciones redundantes o destructivas**: Historial cronológico limpio en `database/migrations/`, con depuración previa de rivales (`remove_contrincantes_from_database`) e índices compuestos atómicos.
- [x] **Sin datos simulados en código productivo**: Código fuente productivo sin diccionarios de fallback falsos o publicaciones dummy embebidas, con guard estricto de entorno en `PublicacionSeeder.php` cumpliendo la regla de negocio `datos-reales-publicaciones`.
