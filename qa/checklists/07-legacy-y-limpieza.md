# 07 — Legacy, Limpieza y Mantenibilidad

## Limpieza del Repositorio y Archivos Huérfanos
- [ ] **Eliminación de volcados de base de datos obsoletos**: Retirar del repositorio archivos huérfanos como `database/database_old.sqlite` o copias de seguridad locales.
- [ ] **Unificación de gestores de paquetes**: Resolver duplicidad entre `package-lock.json` y `pnpm-lock.yaml`, manteniendo exclusivamente el gestor oficial adoptado por el equipo (`npm` o `pnpm`).
- [ ] **Sin artefactos temporales de build**: Verificación de que directorios temporales de compilación (`.vite/`, `storage/framework/cache/`, `public/hot`) permanezcan ignorados por git.
- [ ] **Limpieza de código comentado y funciones deprecadas**: Controladores y servicios sin bloques masivos de código comentado o métodos antiguos en desuso.

## Consistencia de Modelos y Migraciones
- [ ] **Sin migraciones redundantes o destructivas**: Archivos de migración ordenados y sin llamadas repetidas de alteración sobre las mismas tablas que puedan consolidarse.
- [ ] **Sin datos simulados en código productivo**: Código fuente sin diccionarios de fallback falsos o publicaciones dummy embebidas en frontend o backend.
