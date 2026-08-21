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
}
