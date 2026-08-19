# 📖 Manual de Vistas y Guía Operativa - BrandingPo

Esta documentación detalla de forma sencilla cada una de las pantallas de la plataforma **BrandingPo**, explicando **qué se ve**, **cuál es su objetivo estratégico** y **cómo se opera** día a día según el rol del usuario.

---

## 📑 Índice de Vistas

1. [Inicio de Sesión (Login & Quick-Access)](#1-inicio-de-sesión-login--quick-access)
2. [Sala de Situación (Dashboard Principal)](#2-sala-de-situación-dashboard-principal)
3. [Feed Social Multired](#3-feed-social-multired)
4. [Carga Rápida Fast-Flow Desk](#4-carga-rápida-fast-flow-desk)
5. [Predictor de Pauta & War Room Analytics](#5-predictor-de-pauta--war-room-analytics)
6. [Candidatos & Perfiles Políticos](#6-candidatos--perfiles-políticos)
7. [Observatorio de Medios & Clipping](#7-observatorio-de-medios--clipping)
8. [Centro de Situación de Crisis & Matriz de Alianzas](#8-centro-de-situación-de-crisis--matriz-de-alianzas)
9. [Calendario & Agenda de Campaña](#9-calendario--agenda-de-campaña)
10. [Control de Presupuesto & Pauta](#10-control-de-presupuesto--pauta)
11. [Briefings Ejecutivos & Reportes Imprimibles (PDF)](#11-briefings-ejecutivos--reportes-imprimibles-pdf)
12. [Administración de Usuarios & Roles](#12-administración-de-usuarios--roles)

---

## 1. Inicio de Sesión (Login & Quick-Access)
- **Ruta:** `/login`
- **Componente:** `resources/js/Pages/Auth/Login.vue`

### ¿Qué se ve?
- Formulario estándar de correo electrónico y contraseña.
- Botones de **Acceso Rápido en 1 Clic (Quick Login)** para ingresar inmediatamente como **Administrador**, **Consultor** o **Visualizador**.
- Selector de tema **Modo Claro / Modo Oscuro**.

### ¿Cuál es el objetivo?
- Permitir la entrada segura al sistema y facilitar demostraciones y pruebas rápidas entre diferentes niveles de permisos.

### ¿Cómo se trabaja?
1. Para ingresar normalmente, escribe las credenciales y pulsa **"Iniciar Sesión"**.
2. Para pruebas rápidas, pulsa cualquiera de los 3 botones de rol en la parte inferior de la tarjeta.

---

## 2. Sala de Situación (Dashboard Principal)
- **Ruta:** `/dashboard` o `/`
- **Componente:** `resources/js/Pages/Dashboard.vue`

### ¿Qué se ve?
- **Banner de Campaña:** Ciclo activo (2023-2025) y accesos directos a la consola Fast-Flow, Predictor y Feed Vivo.
- **HUD de Métricas Clave:** Seguidores totales auditados, visualizaciones combinadas, inversión en pauta detectada y clima social promedio (estrellas).
- **Tarjetas Head-to-Head de Candidatos:** Comparativa directa de los 4 actores políticos (Propio, Opositor principal, Precandidata emergente e Intendente electo) con sus métricas semanales.
- **Acceso Directo al Predictor de Pauta IA.**
- **Preview del Feed Vivo:** Últimas publicaciones registradas con desglose de reacciones.

### ¿Cuál es el objetivo?
- Brindar una vista panorámica ejecutiva de 360° en tiempo real del estado de la campaña política y la correlación de fuerzas digitales.

### ¿Cómo se trabaja?
- Es la pantalla principal de monitoreo diario. El comando de campaña revisa las variaciones semanales y utiliza los accesos directos para profundizar en áreas específicas (medios, pauta o crisis).

---

## 3. Feed Social Multired
- **Ruta:** `/feed`
- **Componente:** `resources/js/Pages/Publicaciones/Feed.vue`

### ¿Qué se ve?
- **Filtros de Red Social:** Selector de plataformas (Todas, Instagram, Facebook, TikTok, X/Twitter, YouTube, LinkedIn).
- **Filtros de Candidato y Tipo de Publicación:** Selector por actor político y filtro por contenido **Orgánico** vs. **Con Pauta Paga**.
- **Social Wall:** Muro de tarjetas estilo red social con avatar, nombre, badge oficial de la plataforma, fecha relativa, badge de pauta con monto invertido, contenido del post, barra de reacciones con emojis nativos (👍 ❤️ 😂 😮 😡) y comentarios destacados.

### ¿Cuál es el objetivo?
- Auditar y comparar visualmente los mensajes, estética, narrativa y recepción ciudadana de todas las publicaciones de la contienda política.

### ¿Cómo se trabaja?
1. Filtra por la red social que desees analizar (ej. solo TikTok o solo Instagram Reels).
2. Activa el filtro **"Solo Pauta Paga"** para ver qué publicaciones están siendo impulsadas con dinero.
3. Observa el **termómetro de humor social (1 a 5 estrellas)** para detectar qué publicaciones generan rechazo o adhesión popular.

---

## 4. Carga Rápida Fast-Flow Desk
- **Ruta:** `/fast-flow`
- **Componente:** `resources/js/Pages/Publicaciones/FastFlow.vue`

### ¿Qué se ve?
- Formulario de captura rápida de alta velocidad optimizado para teclado.
- Selectores de red social con íconos oficiales y colores nativos.
- Selector de formato (Reel, Video, Foto, Carrusel, Tweet, Historia).
- Toggle interactivo **Orgánico vs. Pauta Paga ($)** con campo para registrar el monto invertido.
- Calificador de clima social de 1 a 5 estrellas.
- Atajo de teclado visible `Ctrl + Enter`.

### ¿Cuál es el objetivo?
- Permitir que los analistas y consultores carguen publicaciones y clipping digital en menos de 20 segundos por post sin fricciones.

### ¿Cómo se trabaja?
1. Selecciona el candidato y la plataforma.
2. Ingresa el texto o resumen de la publicación y la URL original.
3. Si la publicación tiene pauta pagada, activa el switch e ingresa el monto en pesos ($).
4. Asigna el nivel de humor social (estrellas).
5. Presiona `Ctrl + Enter` (o pulsa el botón **"Registrar Publicación"**). El formulario se guardará al instante.

---

## 5. Predictor de Pauta & War Room Analytics
- **Ruta:** `/predictor` o `/analytics`
- **Componente:** `resources/js/Pages/Analytics/Index.vue`
- **Servicio Backend:** `app/Services/AdsImpactPredictorService.php`

### ¿Qué se ve?
- **Simulador Interactivo de Pauta:**
  - Control deslizante (*slider*) y botones rápidos de presupuesto ($15K, $35K, $50K, $100K, $200K, $500K).
  - Selector de formato (Reel, Video, Foto, Carrusel, etc.) y red social.
  - **Medidor de Porcentaje de Proximidad / Certeza (%):** Indica qué tan afinada está la predicción según la cantidad de muestras históricas analizadas.
  - **Visualizaciones Proyectadas:** Muestra el valor esperado junto con el rango de dispersión mínima y máxima.
  - **Engagement Estimado:** Proyección de Likes, Comentarios y Compartidos.
  - **Dictamen Estratégico de IA:** Recomendación algorítmica sobre la eficiencia del costo por vista (CPV).
- **Share of Voice (Participación de Mercado Digital):** Porcentajes y barras de impacto visual por candidato.
- **Distribución de Tracción por Red:** Vistas acumuladas en Instagram, TikTok, Facebook, etc.

### ¿Cuál es el objetivo?
- Evaluar si a una publicación orgánica conviene inyectarle presupuesto, predecir el retorno de visualizaciones antes de gastar dinero y determinar qué formatos generan el mayor impacto por peso invertido.

### ¿Cómo se trabaja?
1. Mueve el slider de presupuesto para simular cuánto dinero se planea invertir.
2. Cambia entre formatos (ej. Reel vs. Foto) para comparar el costo por vista (CPV) proyectado.
3. Lee el porcentaje de proximidad: a mayor cantidad de publicaciones cargadas en el sistema, más alto y certero será este número (hasta 96%).
4. Utiliza el dictamen para fundamentar la asignación presupuestaria ante el comando de campaña.

---

## 6. Candidatos & Perfiles Políticos
- **Ruta:** `/candidatos` y `/candidatos/{id}`
- **Componentes:** `resources/js/Pages/Candidatos/Index.vue` y `Show.vue`

### ¿Qué se ve?
- **Catálogo de Actores Políticos:** Fichas con foto, nombre, territorio asignado, partido político y estado de campaña (**En Funciones / Reelección**, **Opositor Principal**, **Precandidato**, **Intendente Electo**).
- **Vista Detallada del Candidato (`/candidatos/{id}`):**
  - Menciones en prensa favorables vs. críticas.
  - Cuentas de redes sociales conectadas con recuento de seguidores.
  - Historial de publicaciones propias del candidato.

### ¿Cuál es el objetivo?
- Gestionar los perfiles de todos los competidores en el tablero electoral y segmentar su tracción digital y mediática de forma individual.

### ¿Cómo se trabaja?
1. Pulsa sobre cualquier candidato para abrir su expediente completo.
2. (Consultores/Admin): Usa el botón **"+ Nuevo Candidato"** para registrar nuevos competidores o precandidatos que surjan en la contienda.

---

## 7. Observatorio de Medios & Clipping
- **Ruta:** `/medios`
- **Componente:** `resources/js/Pages/Medios/Index.vue`

### ¿Qué se ve?
- **Directorio de Medios de Prensa:** Lista de medios monitoreados (diarios, radios, canales de TV, portales digitales) con su alcance y tendencia editorial.
- **Muro de Clipping Periodístico:** Artículos y notas de prensa clasificadas con semáforo de tono:
  - 🟢 **Favorable:** Cobertura positiva de obras o gestión.
  - 🟡 **Neutro:** Mención informativa estándar.
  - 🔴 **Crítico:** Denuncia, reclamo o cuestionamiento opositor.
- **Caja de Réplica Oficial:** Registro de comunicados y respuestas emitidas por el equipo de prensa.

### ¿Cuál es el objetivo?
- Medir el impacto de la prensa tradicional y digital sobre los candidatos y coordinar respuestas rápidas ante notas adversas.

### ¿Cómo se trabaja?
1. (Consultor/Admin): Haz clic en **"+ Registrar Nota de Prensa"**.
2. Selecciona el medio, el candidato mencionado, el titular, el enlace y clasifica el tono (Favorable / Neutro / Crítico).
3. Si la nota requiere respuesta, redacta la estrategia de réplica en el campo correspondiente.

---

## 8. Centro de Situación de Crisis & Matriz de Alianzas
- **Ruta:** `/crisis`
- **Componente:** `resources/js/Pages/Crisis/Index.vue`

### ¿Qué se ve?
- **Semáforo de Gravedad (HUD):** Conteo de incidentes **Críticos**, **Moderados** y **Leves**, junto con el tiempo promedio de respuesta en minutos.
- **Feed de Eventos de Crisis:** Casos abiertos y resueltos con su estrategia de contención y vocero asignado.
- **Matriz de Alianzas & Padrinos Políticos:** Mapa de apoyos institucionales clasificados por impacto (+Suma capital político, -Resta / Controversial, Neutro).

### ¿Cuál es el objetivo?
- Monitorear focos de conflicto en tiempo real, asegurar que el tiempo de respuesta no supere los estándares fijados y evaluar el costo/beneficio de alianzas partidarias.

### ¿Cómo se trabaja?
1. Ante un conflicto emergente (ej. reclamo gremial o fake news), pulsa **"+ Declarar Alerta de Crisis"**.
2. Asigna la gravedad (Leve, Moderado, Crítico) y las acciones inmediatas de contención.
3. Una vez superada la crisis, pulsa **"Resolver Incidente"** para cerrar el caso y calcular el tiempo final de resolución.

---

## 9. Calendario & Agenda de Campaña
- **Ruta:** `/calendario`
- **Componente:** `resources/js/Pages/Calendario/Index.vue`

### ¿Qué se ve?
- **Filtros por Tipo de Evento:** Actos públicos, debates televisivos, vencimientos de pauta publicitaria, caravanas y ruedas de prensa.
- **Cronograma de Hitos:** Tarjetas con fecha y hora de inicio/fin, locación geográfica, notas de logística y candidato asignado.

### ¿Cuál es el objetivo?
- Sincronizar la agenda territorial del candidato con la estrategia de comunicación digital y vencimientos de campañas pagas.

### ¿Cómo se trabaja?
1. (Consultor/Admin): Pulsa **"Programar Evento"**.
2. Completa el título, locación, fecha/hora y notas logísticas.
3. El comando utilizará esta agenda para coordinar coberturas de video en vivo y pauta localizada.

---

## 10. Control de Presupuesto & Pauta
- **Ruta:** `/presupuesto`
- **Componente:** `resources/js/Pages/Presupuesto/Index.vue`

### ¿Qué se ve?
- **Tarjetas de Balance Financiero:** Presupuesto asignado total, monto ejecutado a la fecha, saldo disponible libre y porcentaje global de ejecución.
- **Tabla de Partidas Presupuestarias:** Desglose por categorías (Pauta digital, Vía pública, Producción audiovisual, Eventos territoriales, Contingencias) con barras visuales de porcentaje de consumo.

### ¿Cuál es el objetivo?
- Garantizar el control fiscal de la campaña, evitar sobrecostos y asegurar fondos suficientes para los tramos decisivos previos a la elección.

### ¿Cómo se trabaja?
1. Revisa los saldos disponibles antes de autorizar nuevas compras de medios o pauta digital.
2. (Consultor/Admin): Usa **"Nueva Partida"** para asignar presupuesto o actualizar montos ejecutados contra facturación real.

---

## 11. Briefings Ejecutivos & Reportes Imprimibles (PDF)
- **Ruta:** `/briefings` (listado) y `/briefings/{id}` (dossier imprimible)
- **Componentes:** `resources/js/Pages/Briefings/Index.vue` y `Show.vue`

### ¿Qué se ve?
- **Listado de Briefings (`/briefings`):** Informes semanales y balances de ciclo con snapshots de métricas consolidadas.
- **Vista Imprimible / PDF-Ready (`/briefings/{id}`):**
  - Dossier ejecutivo con membrete institucional **BrandingPo CONFIDENCIAL**.
  - Resumen ejecutivo estructurado.
  - Snapshot de indicadores clave (Vistas, Pauta, CPV, Sentimiento).
  - Dictamen estratégico y conclusiones del comando.
  - Botón **"Imprimir / Guardar en PDF"** que abre el diálogo nativo optimizado para hoja A4 sin barras de navegación.

### ¿Cuál es el objetivo?
- Proveer a candidatos, jefes de campaña y mesas chicas de un reporte formal, claro y listo para imprimir o enviar en PDF por WhatsApp/Email.

### ¿Cómo se trabaja?
1. Para crear un nuevo informe, pulsa **"+ Generar Nuevo Briefing"**, escribe el resumen y las conclusiones recomendadas.
2. Para presentarlo o entregarlo, abre el briefing y pulsa **"Imprimir / Guardar en PDF"**.

---

## 12. Administración de Usuarios & Roles
- **Ruta:** `/usuarios` *(Exclusivo Administrador)*
- **Componente:** `resources/js/Pages/Usuarios/Index.vue`

### ¿Qué se ve?
- Listado de usuarios del sistema con su nombre, correo, rol actual y fecha de registro.
- Modal de creación y edición de usuarios para asignar roles:
  - **Administrador:** Control total, gestión de usuarios y parámetros del sistema.
  - **Consultor:** Carga de publicaciones en Fast-Flow, edición de pauta, clipping, eventos de crisis y generación de briefings.
  - **Visualizador:** Modo solo lectura limpio, sin botones de acción ni permisos de modificación.

### ¿Cuál es el objetivo?
- Gestionar los accesos del equipo de campaña garantizando la seguridad y confidencialidad de la información.

### ¿Cómo se trabaja?
1. Accede con un usuario administrador (ej. `admin@brandingpo.com`).
2. Pulsa **"+ Nuevo Usuario"** para dar de alta colaboradores asignándoles el rol correspondiente a su función en el comando.

---

*Documentación técnica y funcional generada para el ecosistema **BrandingPo**.*