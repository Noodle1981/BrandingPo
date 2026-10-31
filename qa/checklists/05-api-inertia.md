# 05 — Integración Inertia.js y Contratos de Datos

## Contratos y Sincronización Cliente ↔ Servidor
- [ ] **Sincronización de rutas con Ziggy**: Nombres de rutas en controladores coincidentes exactamente con llamadas `route('nombre.ruta')` en componentes Vue.
- [ ] **Protección Anti Mass-Assignment**: Controladores y Form Requests mapean únicamente campos autorizados mediante `$request->validated()`, evitando inyección de atributos protegidos (`role`, `is_admin`, `workspace_id`).
- [ ] **Optimización del Payload de Inertia**: Evitar el envío de colecciones masivas sin paginar en props de página; limitar proyecciones a los campos requeridos por la vista.
- [ ] **Uso de Props Perezosas (Lazy / Deferred Props)**: Datos pesados de gráficos secundarios o cálculos territoriales cargados mediante `Inertia::lazy()` o deferred props para acelerar la carga inicial de la página.

## Estado, Flashes y Navegación Reactiva
- [ ] **Manejo coherente de notificaciones y flashes**: Controladores retornan mensajes de éxito y advertencia con `with('success', ...)` y `with('error', ...)`, capturados uniformemente por el layout principal.
- [ ] **Preservación de scroll y estado**: Filtros de feed, paginación y ordenamiento con `preserveScroll: true` y `preserveState: true` para una experiencia de usuario fluida sin saltos de pantalla.
- [ ] **Manejo de respuestas de validación HTTP 422**: Errores de validación propagados reactivamente hacia el formulario del componente (`useForm`) con mensajes en español comprensibles.
