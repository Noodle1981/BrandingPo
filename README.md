# 🏛️ BrandingPo - Sala de Situación (War Room) & Inteligencia Política

> **Plataforma de Consultoría Estratégica, Auditoría Digital, Benchmarking Político y Monitoreo de Humor Social en Tiempo Real.**

---

## 🌟 Visión del Producto

**BrandingPo** combina la potencia analítica de un **War Room Político** con el dinamismo visual de un **Feed de Redes Sociales Multired**. Permite a equipos de campaña, consultores y analistas auditar el crecimiento de su candidato desde el **Punto Cero (Línea de Base / Punto Alfa)**, medir las reacciones emocionales de la ciudadanía emoji por emoji, optimizar la inversión en pauta publicitaria y monitorear los movimientos de la oposición y de la prensa en un solo lugar.

---

## 🎯 Módulos Principales

### 1. 👤 Mi Candidato & Auditoría de Punto Cero (`/mi-candidato`)
- **Punto Alfa de Campaña:** Registro inicial de seguidores, seguidos, publicaciones totales y fecha de comienzo para auditar el crecimiento real día a día.
- **Botonera Multired Centrada:** Pestañas para **Instagram, Facebook, TikTok, X (Twitter), YouTube y LinkedIn** con logos oficiales de cada plataforma.
- **Semáforo de Canales Digitales:**
  - 🔵 **Certificada / Verificada:** Cuenta con badge oficial de autenticidad.
  - 🟠 **Vinculada / Activa:** Canal activo y operativo en campaña.
  - 🔴 **No Vinculada / Inactiva:** Red social pendiente de creación o sin actividad.
- **⚡ Lector Automático en 1 Clic (`SocialProfileScraperService`):**
  - Extracción automática de foto de perfil en alta resolución, `@handle`, seguidores y publicaciones desde la URL pública sin APIs pagas.
  - Soporte inteligente para páginas de **Facebook**, perfiles de **Instagram**, canales de **YouTube**, **TikTok** y **X**.
- **Tabla Adaptativa:** Adaptación inteligente según la red (oculta publicaciones acumuladas en Facebook y mantiene las 4 métricas en Instagram/TikTok/X).

---

### 2. 👥 Oposición & Rivales (`/candidatos`)
- **Benchmarking Competitivo:** Vista exclusiva de la competencia política sin mezclar los datos del candidato propio.
- **Auditoría de Rivales:** Misma botonera de semáforo de canales y Punto Cero independiente para contrastar el ritmo de crecimiento frente a los opositores.

---

### 3. ⚡ Carga Ágil de Publicaciones (Fast-Flow - `/fast-flow`)
- **Carga Ultrarrápida:** Registro de publicaciones con URL, título, eje temático (Seguridad, Salud, Obras, etc.) y formato (Reel, Carrusel, Tweet, Video).
- **Cuantificación Emocional Granular (Emoji a Emoji):**
  - Registro exacto de: 👍 Me gusta, ❤️ Me encanta, 🥰 Me importa, 😂 Me divierte, 😮 Me asombra, 😢 Me entristece, 😡 Me enoja.
- **Motor de Inteligencia Emocional & IA:**
  - Cálculo del **Índice de Aprobación Neta**.
  - **Sentimiento Predominante:** Favorable, Neutral o Crítico.
  - **Termómetro de Humor Social (1 a 5 estrellas).**
  - **Detector Automático de Crisis:** Alerta roja inmediata si las reacciones de enojo (😡) superan el 15%.

---

### 4. 💰 Inteligencia de Pauta & Rendimiento (Orgánico vs. Ads)
- **Tipos de Publicación Contemplados:**
  1. **Orgánica Pura:** Tracción natural de la comunidad sin inversión.
  2. **Orgánica Impulsada (Boosted Post):** Post orgánico con excelente recepción al que se le inyectó pauta para amplificar su impacto.
  3. **Anuncio Directo / Dark Post:** Anuncio creado exclusivamente en Meta Ads Manager / TikTok Ads que no aparece en el feed del candidato ni del rival.
- **Métricas Internas & Insights de Consultor:** Registro de visualizaciones, alcance real, reproducciones de video (views) y costos por interacción.

---

### 5. 📰 Sala de Prensa & Medios (Clipping Periodístico - `/clipping`)
- **Monitoreo de Medios:** Registro de notas en portales web, diarios, radio y televisión.
- **Tono Editorial:** Clasificación semafórica de la cobertura (🟢 Favorable, ⚪ Neutral, 🔴 Crítica).
- **Impacto y Portada:** Detección de notas de tapa/portada y réplicas necesarias de campaña.

---

### 6. 📊 Dashboard Ejecutivo & Modo Dual (`/dashboard`)
- **War Room Dual Theme:**
  - **Modo Oscuro (War Room):** Fondo Slate-950 con acentos cian, esmeralda y violeta para salas de situación y trabajo nocturno.
  - **Modo Claro (Executive Light):** Fondo Slate-50 y tarjetas blancas para lectura ejecutiva, reportes e impresión.
- **Gráficos & KPIs:** Distribución de ejes temáticos, termómetro de humor social y ranking de publicaciones de mayor impacto.

---

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Vue 3 (Composition API con `<script setup>`) + Inertia.js
- **Estilos & UI:** Tailwind CSS + Lucide Icons
- **Base de Datos:** SQLite (desarrollo ágil y portable)
- **Build Tool:** Vite 8

---

## 🚀 Instalación y Puesta en Marcha

```bash
# 1. Clonar el repositorio
git clone https://github.com/Noodle1981/BrandingPo.git
cd BrandingPo

# 2. Instalar dependencias PHP y Node
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Migrar y poblar base de datos con datos representativos
php artisan migrate --seed

# 5. Compilar assets frontend
npm run build
# o en modo desarrollo:
npm run dev

# 6. Servir la aplicación
php artisan serve
```

---

## 🔐 Roles y Permisos

| Rol | Acceso & Capacidades |
| :--- | :--- |
| **Administrador (`admin`)** | Control total del sistema, configuración de ciclos de campaña, territorios y usuarios. |
| **Consultor (`consultor`)** | Carga y edición en Fast-Flow, auditoría de Punto Cero, gestión de pauta y clipping de medios. |
| **Visualizador (`visualizador`)** | Acceso de solo lectura al War Room, métricas del Dashboard y reportes ejecutivos. |

---

## 📄 Licencia

Desarrollado bajo licencia privativa para **BrandingPo** & Consultoría Política.
