# 10 — Documentación Técnica y de Negocio

## Frescura y Alineación de Documentación
- [x] **Frescura de la documentación de arquitectura**: Documentación técnica (`README.md`, `GEMINI.md`, `doc/GUIA_RAPIDA_VISTAS.md`) actualizada y alineada con los últimos estándares del proyecto. (obligatorio)
- [x] **Variables de entorno (.env.example)**: Cada variable requerida por Laravel declarada con valores representativos (`APP_LOCALE=es`, `DB_BUSY_TIMEOUT=5000`, `DB_JOURNAL_MODE=WAL`) y sin filtrar secretos reales. (obligatorio)
- [x] **Guía de instalación y puesta en marcha**: Instrucciones reproducibles documentadas para levantar el entorno local (`composer install`, `php artisan migrate`, `npm run dev`).
- [x] **Documentación de contratos y OpenAPI**: Especificación de rutas y esquemas de datos estructurada en `doc/openapi.yaml`.
- [x] **Reglas de negocio vigentes**: Lógica de cálculo del Índice de Aprobación Neta, umbral de crisis del 15% y política de Punto Cero documentadas con precisión en `GEMINI.md`.

## Hallazgos de la Medición — Documentación
- [x] **Mantener sincronía código ↔ documentación**: Sincronía y frescura documental verificada.
- [x] **Documentar límites y requerimientos de scraping**: Documentados encabezados, agentes y `referrerpolicy="no-referrer"` para evitar bloqueos en el scraping asistido.
