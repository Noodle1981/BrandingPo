# 10 — Documentación Técnica y de Negocio

## Frescura y Alineación de Documentación
- [ ] **Frescura de la documentación de arquitectura**: Documentación técnica (`README.md`, `GEMINI.md`, `doc/GUIA_RAPIDA_VISTAS.md`) actualizada y alineada con los últimos commits del código fuente. (obligatorio)
- [ ] **Variables de entorno (.env.example)**: Cada variable requerida por Laravel (`APP_KEY`, `DB_CONNECTION`, etc.) declarada con valor de ejemplo representativo y sin filtrar secretos reales. (obligatorio)
- [ ] **Guía de instalación y puesta en marcha**: Instrucciones precisas y reproducibles para levantar el entorno local (`composer install`, `php artisan migrate`, `npm run dev`).
- [ ] **Documentación de contratos y OpenAPI**: Especificación de rutas y esquemas de datos coherente con `doc/openapi.yaml`.
- [ ] **Reglas de negocio vigentes**: Lógica de cálculo del Índice de Aprobación Neta, umbral de crisis del 15% y política de Punto Cero documentadas con precisión.

## Hallazgos de la Medición — Documentación
- [ ] **Mantener sincronía código ↔ documentación**: Preservar el alineamiento actual verificado en la medición de frescura documental.
- [ ] **Documentar límites y requerimientos de scraping**: Detallar en la documentación técnica las consideraciones sobre CDNs de Meta, User-Agents y encabezados para evitar bloqueos en el scraping asistido.
