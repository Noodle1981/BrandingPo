# 📋 PLAN DE FLUJO OPERATIVO & ARQUITECTURA DE CAMPAÑA
## BrandingPo — War Room & Intelligence Suite (Multi-Tenant Edition)

Este documento detalla la arquitectura de **Workspaces Multi-Tenant**, el **Sidebar estilo Gemini Portal con globos desplegables** y los **flujos 100% enfocados** para **Mi Campaña (Oficial)** y para la **Inteligencia de Oposición (Rivales)**.

---

## 🏢 1. Arquitectura Multi-Tenant (Workspaces Aislados)

Cada cliente político dispone de su propio **Workspace** completamente aislado en base de datos (`workspace_id`):
- **Candidato Propio:** `es_propio = true` (solo 1 candidato oficial por campaña).
- **Rivales Monitoreados:** `es_propio = false` (perfiles de datos públicos seguidos, sin cuenta en el sistema).
- **Separación de Datos:** 0% de solapamiento entre diferentes campañas.

---

## 🧭 2. Sidebar Estilo Gemini Portal (Globos Flotantes Desplegables)

En lugar de una lista plana saturada, el sidebar utiliza un ancho ultra-limpio (`w-16`) con **4 iconos maestros de sección**. Al posar el cursor sobre cualquier icono maestro, se despliega hacia la derecha un **globo flotante (flyout popover)** con los accesos directos temáticos:

```text
┌────────────────────────────────────────────────────────────────────────┐
│ 📊 [Dashboard]  → Acceso directo a Sala de Situación                   │
├────────────────────────────────────────────────────────────────────────┤
│ ✨ [Mi Campaña]                                                        │
│    └─ GLOBO: 👤 Mi Candidato & Redes  (/mi-candidato)                  │
│              ⚡ Fast-Flow Propio       (/fast-flow?tipo=propio)        │
│              📱 Feed Propio             (/feed?filtro=propio)           │
├────────────────────────────────────────────────────────────────────────┤
│ ⚔️ [Oposición]                                                         │
│    └─ GLOBO: 👥 Fichas de Rivales       (/candidatos)                   │
│              ⚡ Fast-Flow Oposición     (/fast-flow?tipo=oposicion)     │
│              📊 Benchmarking            (/candidatos/benchmarking)      │
├────────────────────────────────────────────────────────────────────────┤
│ 🌍 [Territorio]                                                        │
│    └─ GLOBO: 📍 Territorio & Demografía (/territorios)                  │
│              📰 Observatorio de Medios  (/medios)                       │
│              🚨 Centro de Crisis        (/crisis)                       │
├────────────────────────────────────────────────────────────────────────┤
│ 🛡️ [Sala de Mando]                                                    │
│    └─ GLOBO: 📊 Sala de Situación       (/dashboard)                    │
│              🎯 Predictor de Pauta      (/predictor)                    │
│              📅 Calendario & Agenda     (/calendario)                   │
│              💰 Presupuesto & Pauta     (/presupuesto)                  │
│              📄 Briefings Ejecutivos    (/briefings)                    │
│              👤 Usuarios & Roles        (/usuarios - solo admin)        │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 3. Flujo Operativo Separado por Enfoque

### A. Flujo Oficial (Mi Campaña)
1. **Punto Cero:** En `/mi-candidato`, carga de enlaces oficiales (Instagram, Facebook, etc.) con el auto-lector de 1 clic para fijar la línea de base (seguidores y posts iniciales).
2. **Fast-Flow Propio:** En `/fast-flow?tipo=propio`, pantalla 100% dedicada a tu candidato (sin dropdowns de rivales ni selectores cruzados). Carga ágil de emojis (👍, ❤️, 🥰, 😂, 😮, 😢, 😡), comentarios y pauta.
3. **Feed Propio:** En `/feed?filtro=propio`, timeline exclusivo de publicaciones del candidato oficial.

### B. Flujo de Oposición (Inteligencia Competitiva)
1. **Alta de Rivales:** En `/candidatos`, creación de perfiles opositores a auditar con su respectivo Punto Cero.
2. **Fast-Flow Oposición:** En `/fast-flow?tipo=oposicion`, auditoría de publicaciones de la competencia.
3. **Benchmarking:** En `/candidatos/benchmarking`, comparativa visual de quién crece más rápido desde el Punto Cero por red social.

---

## 🧹 4. Puesta a Cero para Campaña Real (2026 / 2027)

Para reiniciar la base de datos limpia y comenzar con datos 100% reales:

```powershell
php artisan migrate:fresh --seed --seeder=CleanCampanaBaseSeeder
```

### Usuarios del Sistema:
- **Admin:** `admin@brandingpo.com` / `password`
- **Consultor:** `consultor@brandingpo.com` / `password`
- **Visualizador:** `visualizador@brandingpo.com` / `password`

---
*BrandingPo v2.5 — Multi-Tenant War Room & Intelligence Suite.* 🎯
