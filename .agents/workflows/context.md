Contexto Completo del Proyecto
BrandingPo es un SaaS de inteligencia política construido con:

Stack: Laravel 11 + Inertia.js + Vue 3 (Composition API / <script setup>) + Tailwind CSS + SQLite
Repo: d:\BrandingPo
Todo el código y comentarios deben estar en español
Estilo visual: Dark mode bg-slate-950 por defecto, light mode opcional. Acentos: cian #06b6d4, esmeralda #10b981, ámbar #f59e0b, carmesí #ef4444, violeta #8b5cf6
Roles actuales en users.role: admin, consultor, visualizador
Middleware existente: can_write (CheckCanWrite), is_admin (CheckIsAdmin)
Modelo de Negocio (leer antes de tocar código)
BrandingPo es un SaaS político donde cada cliente es una campaña. Un consultor político vende el acceso a la plataforma a distintos candidatos:

Workspace A: Campaña de Federico Sisterna (Intendente, Albardón, San Juan)
Workspace B: Campaña de otro candidato en otra provincia
Cada workspace tiene:

Un candidato PROPIO (es_propio = true): el cliente que paga
Múltiples contrincantes (es_propio = false): perfiles de monitoreo, no son usuarios, no saben que existen en la plataforma
Un nivel político que define la escala de la campaña: intendente, gobernador, legislador_nacional, legislador_provincial, senador, concejal
Un equipo de usuarios con roles por workspace (un mismo consultor puede estar en varios workspaces)
Los datos entre workspaces son completamente aislados. Un candidato "Romina Rosas" puede existir como contrincante en el Workspace A y como candidata propia en el Workspace B — son registros independientes sin relación técnica.