---
name: datos-reales-publicaciones
description: Regla y directiva obligatoria para BrandingPo. PROHIBIDO generar seeders o inyectar publicaciones ficticias/sintéticas en la base de datos. Se trabaja exclusivamente con publicaciones y métricas reales de redes sociales.
license: Proprietary
metadata:
  author: BrandingPo Team
  version: "1.0.0"
---

# Preservación de Datos Reales en Publicaciones (BrandingPo)

## 📌 Principio Fundamental
**Bajo ninguna circunstancia se deben generar seeders nuevos, factories o scripts que inyecten registros ficticios o simulados en la tabla `publicaciones`.**

El proyecto opera con **datos reales de campaña** (redes sociales oficiales, enlaces directos, métricas públicas auditadas y engagement genuino). Inyectar publicaciones falsas (como `demo-*` o textos simulados) desvirtúa el análisis de tracción, el cálculo del Índice de Aprobación Neta, la auditoría de Punto Cero y las métricas de penetración territorial.

---

## 🚫 Reglas Estrictas

1. **PROHIBICIÓN TOTAL DE SEEDERS EN `publicaciones`:**
   - No crear clases de seeders que hagan `Publicacion::create()` o `Publicacion::insert()`.
   - `PublicacionSeeder` queda permanentemente desactivado y fuera de `DatabaseSeeder`.
   - Los seeders del proyecto solo se reservan para catálogos estáticos base (usuarios de acceso, ejes temáticos vacíos, territorios y ciclos de campaña).

2. **PROTECCIÓN CONTRA SOBREESCRITURA O TRUNCADO:**
   - Nunca ejecutar comandos destructivos tipo `migrate:fresh --seed` sin antes respaldar o exceptuar la tabla `publicaciones`.
   - En cualquier script o seeder de inicialización, debe existir una guarda explícita:
     ```php
     if (Publicacion::exists()) {
         return; // Preservar datos reales existentes
     }
     ```

3. **ORIGEN EXCLUSIVO DE PUBLICACIONES:**
   Toda publicación en el sistema debe provenir únicamente de:
   - **Fast-Flow con 1 Clic:** Ingreso manual/asistido mediante URL legítima de red social (`instagram.com`, `facebook.com`, `tiktok.com`, `threads.net`, `x.com`).
   - **Lector Automático / Scraper:** `SocialProfileScraperService` sincronizando posts reales de perfiles auditados.
   - **Monitoreo Social de Medios:** Réplicas de notas vinculadas a Fanpages verificadas.

4. **INTEGRIDAD DE ENLACES (`url_post`):**
   - Toda publicación registrada debe contener una URL real y accesible hacia la plataforma social original.
   - Jamás inventar dominios o slugs tipo `/post/demo-XXXX`.
