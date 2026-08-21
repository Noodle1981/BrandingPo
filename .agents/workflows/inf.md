Estado Actual de la Base de Datos
Tablas existentes:
users (id, name, email, role, password)
ciclo_campanas (id, anio, nombre, fecha_inicio, fecha_fin, estado, es_activo)
territorios (id, parent_id, nombre, tipo, codigo_indec, latitud, longitud, poblacion_total, padron_electoral, poblacion_urbana_pct, poblacion_rural_pct, hogares_nbi_pct, piramide_etaria JSON, circuitos_electorales JSON, meta_electoral JSON)
candidatos (id, ciclo_campana_id, territorio_id, nombre_completo, partido_coalicion, cargo_aspirado, estado_politico, color_hex, es_propio bool, avatar_url, bio_resumen)
perfil_socials (id, candidato_id, plataforma, handle_usuario, url_perfil, ...)
publicacions (id, candidato_id, eje_tematico_id, ...)
publicacion_historicos (id, publicacion_id, ...)
medio_prensas (id, nombre, tipo, ...)
nota_prensas (id, candidato_id, medio_prensa_id, ...)
evento_crises (id, candidato_id, ...)
alianza_politicas (id, candidato_id, ...)
evento_calendarios (id, candidato_id, ...)
presupuesto_partidas (id, ciclo_campana_id, ...)
informe_ejecutivos (id, candidato_id, ...)
eje_tematicos (id, nombre, slug, color_badge, descripcion)
Modelos existentes en app/Models/:
AlianzaPolitica, Candidato, CicloCampana, EjeTematico, EventoCalendario, EventoCrisis, InformeEjecutivo, MedioPrensa, NotaPrensa, PerfilSocial, PresupuestoPartida, Publicacion, PublicacionHistorico, Territorio, User

Controladores existentes en app/Http/Controllers/:
AnalyticsController, AuthController, BriefingController, CalendarioController, CandidatoController, Controller, CrisisController, DashboardController, MediosController, PresupuestoController, PublicacionController, TerritorioController, UserController

Middleware existentes en app/Http/Middleware/:
CheckCanWrite, CheckIsAdmin, HandleInertiaRequests

Vistas Vue existentes en resources/js/:
Layouts/WarRoomLayout.vue
Components/Badge.vue, SocialCard.vue, MetricCard.vue, MediaEmbed.vue, ThemeToggle.vue
Pages/Dashboard.vue, Pages/Auth/Login.vue
Pages/Candidatos/Index.vue, Show.vue, MiPerfil.vue
Pages/Territorios/Index.vue
Pages/Publicaciones/FastFlow.vue, Feed.vue
Pages/Medios/Index.vue, Pages/Crisis/Index.vue
Pages/Analytics/Index.vue, Pages/Calendario/Index.vue
Pages/Presupuesto/Index.vue, Pages/Briefings/Index.vue, Show.vue
Pages/Usuarios/Index.vue