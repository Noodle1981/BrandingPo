# 05 — Integración Inertia.js y Contratos de Datos

## Contratos y Sincronización Cliente ↔ Servidor
- [x] **Sincronización de rutas con Ziggy**: Rutas de controladores (`publicaciones.store`, `medios.index`, `territorios.impacto-electoral`, etc.) sincronizadas con llamadas en componentes Vue.
- [x] **Protección Anti Mass-Assignment**: Controladores y Form Requests mapean exclusivamente campos autorizados mediante `$request->validated()`, impidiendo la inyección o alteración indebida de atributos protegidos (`role`, `workspace_id`).
- [x] **Optimización del Payload de Inertia**: Proyecciones acotadas en `DashboardController` y serialización de publicaciones acotada en `PublicacionFeedService` para evitar transferencia de blobs o atributos redundantes hacia el cliente.
- [x] **Uso de Props Perezosas (Lazy / Deferred Props)**: Datos compartidos globales (`workspaces_disponibles`, `flash`) encapsulados en closures diferidas (`fn () => ...`) en `HandleInertiaRequests` para evitar ejecución innecesaria en requests parciales.

## Estado, Flashes y Navegación Reactiva
- [x] **Manejo coherente de notificaciones y flashes**: Controladores retornan mensajes de éxito, advertencia e información (`with('success', ...)`, `with('warning', ...)`, `with('error', ...)`) propagados por `HandleInertiaRequests.php` y renderizados con banners accesibles en `WarRoomLayout.vue`.
- [x] **Preservación de scroll y estado**: Filtros de feed, cambio de territorio en `ImpactoElectoral.vue` y envío en `FeedFastFlowModal.vue` configurados con `preserveScroll: true` y `preserveState: true`.
- [x] **Manejo de respuestas de validación HTTP 422**: Respuestas de validación propagadas reactivamente hacia `useForm` con soporte completo de traducciones al español en `lang/es/validation.php` con nombres legibles de atributos.
