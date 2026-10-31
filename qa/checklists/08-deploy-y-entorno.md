# 08 — Deploy y Configuración de Entorno

## Optimización y Compilación Productiva
- [ ] **Compilación de assets en producción**: Ejecución exitosa de `npm run build` sin errores, generando bundles optimizados y minificados en `public/build/`.
- [ ] **Caché de configuración y rutas**: Comandos `php artisan config:cache`, `php artisan route:cache` y `php artisan view:cache` operativos sin fallos de serialización.
- [ ] **Configuración de entorno de producción**: `APP_ENV=production` y `APP_DEBUG=false` asegurados en el entorno de despliegue.

## Persistencia, Permisos y Almacenamiento
- [ ] **Permisos de carpetas de almacenamiento**: Directorios `storage/` y `bootstrap/cache/` con permisos de escritura correctos para el usuario del servidor web (`www-data`).
- [ ] **Persistencia y respaldo de SQLite**: Base de datos SQLite ubicada en ruta protegida y respaldada antes de cada despliegue o ejecución de migraciones.
- [ ] **Enlace simbólico de storage público**: Enlace `public/storage` generado correctamente mediante `php artisan storage:link` para fotos de perfil y multimedia de campañas.
- [ ] **Plan de Rollback y contingencia**: Procedimiento documentado para revertir el despliegue al commit anterior ante fallos imprevistos.
