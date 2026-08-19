---
name: brandingpo-sprint-workflow
description: Flujo de trabajo estándar para la ejecución de Sprints en BrandingPo (Desarrollo, Seeders representativos, Testing por roles, Commit & Push).
---

# Flujo de Trabajo por Sprints - BrandingPo

Este skill define el procedimiento obligatorio para ejecutar cada Sprint del proyecto.

## 📋 Pasos para la ejecución de cada Sprint:

1. **Construcción y Lógica (Backend + Frontend):**
   - Crear o modificar modelos, migraciones, controladores, Form Requests y policies.
   - Diseñar las vistas Inertia / Vue 3 siguiendo el sistema de diseño War Room (Dark Slate, badges, responsive).
   
2. **Generación de Seeders Representativos:**
   - Para cada entidad creada, implementar seeders con datos realistas (no texto dummy ilegible).
   - Incluir escenarios con:
     - 1 Perfil Propio (ej. Intendente en funciones / Candidato a reelección).
     - 2 Candidatos opositores / rivales con datos comparables.
     - 1 Candidato o Intendente Electo (fase de transición).
     - Publicaciones históricas con pauta orgánica vs. paga, métricas y clipping de medios.

3. **Validación de Roles y Permisos:**
   - Probar y verificar accesos para los 3 roles:
     - `Admin`: Control total.
     - `Consultor`: Operativo y carga de datos.
     - `Visualizador`: Solo lectura, sin botones de acción ni mutaciones.

4. **Testing Funcional:**
   - Ejecutar pruebas automáticas o validaciones de endpoints/rutas.
   - Comprobar que no existan errores de consola ni de renderizado en Vue.

5. **Cierre de Sprint (Git Commit & Push):**
   - Revisar estado con `git status`.
   - Registrar commit descriptivo con convención de nombres: `feat(sprint-X): <descripción del sprint>`.
   - Realizar `git push` a la rama de trabajo.


