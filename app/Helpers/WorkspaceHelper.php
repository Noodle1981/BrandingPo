<?php

namespace App\Helpers;

use App\Models\Workspace;
use Illuminate\Http\Request;

class WorkspaceHelper
{
    /**
     * Obtiene el workspace activo de la request actual.
     * Si no está en la request, intenta resolverlo desde el usuario autenticado o el primero de la BD.
     */
    public static function activo(Request $request): Workspace
    {
        $workspace = $request->get('_workspace');

        if ($workspace instanceof Workspace) {
            return $workspace;
        }

        $user = $request->user();
        if ($user && $user->active_workspace_id) {
            $ws = Workspace::find($user->active_workspace_id);
            if ($ws) {
                return $ws;
            }
        }

        $first = Workspace::first();
        if ($first) {
            return $first;
        }

        abort(403, 'No hay un workspace activo configurado.');
    }

    /**
     * Obtiene el workspace activo o null si no existe.
     */
    public static function activoONull(Request $request): ?Workspace
    {
        $workspace = $request->get('_workspace');
        if ($workspace instanceof Workspace) {
            return $workspace;
        }

        $user = $request->user();
        if ($user && $user->active_workspace_id) {
            return Workspace::find($user->active_workspace_id);
        }

        return Workspace::first();
    }

    /**
     * Valida que un modelo Eloquent pertenezca al workspace activo.
     * Si no pertenece, registra la alerta de seguridad y aborta con HTTP 403.
     */
    public static function validarPertenencia(mixed $recurso, Request|Workspace $contexto): void
    {
        $workspace = $contexto instanceof Workspace ? $contexto : self::activo($contexto);

        if (! $recurso) {
            abort(404, 'Recurso no encontrado.');
        }

        // Obtener el workspace_id del recurso o de su relación de pertenencia
        $workspaceIdRecurso = null;
        if (isset($recurso->workspace_id)) {
            $workspaceIdRecurso = $recurso->workspace_id;
        } elseif (method_exists($recurso, 'candidato') && $recurso->candidato) {
            $workspaceIdRecurso = $recurso->candidato->workspace_id;
        } elseif (isset($recurso->candidato_id) && class_exists(\App\Models\Candidato::class)) {
            $workspaceIdRecurso = \App\Models\Candidato::find($recurso->candidato_id)?->workspace_id;
        }

        if ($workspaceIdRecurso !== null && (int) $workspaceIdRecurso !== (int) $workspace->id) {
            SecurityHelper::logEvento('intento_acceso_cross_workspace', [
                'recurso_clase' => get_class($recurso),
                'recurso_id' => $recurso->id ?? null,
                'workspace_recurso' => $workspaceIdRecurso,
                'workspace_activo' => $workspace->id,
            ]);

            abort(403, 'Acceso denegado: El recurso solicitado no pertenece a tu campaña o workspace activo.');
        }
    }
}
