<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceActive
{
    /**
     * Verifica que el usuario tiene un workspace activo.
     * Si no tiene, intenta asignarle el primero disponible.
     * Comparte el workspace activo con la request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Si el usuario no tiene workspace activo, asignarle el primero al que tiene acceso
        if (! $user->active_workspace_id) {
            $primerWorkspace = $user->workspaces()->first();
            if ($primerWorkspace) {
                $user->update(['active_workspace_id' => $primerWorkspace->id]);
                $user->refresh();
            } else {
                // Si es admin y no tiene asignación directa, asignarle el primer workspace del sistema
                $primerWorkspaceGlobal = Workspace::first();
                if ($primerWorkspaceGlobal) {
                    $roleToAssign = in_array($user->role, ['admin', 'consultor', 'visualizador']) ? $user->role : 'consultor';
                    $primerWorkspaceGlobal->usuarios()->syncWithoutDetaching([
                        $user->id => ['role' => $roleToAssign],
                    ]);
                    $user->update(['active_workspace_id' => $primerWorkspaceGlobal->id]);
                    $user->refresh();
                }
            }
        }

        // Cargar workspace activo con datos necesarios
        $workspaceActivo = $user->active_workspace_id
            ? Workspace::find($user->active_workspace_id)
            : null;

        // Si no se encuentra (fue borrado), buscar otro
        if (! $workspaceActivo) {
            $primerWorkspace = $user->workspaces()->first() ?? Workspace::first();
            if ($primerWorkspace) {
                $user->update(['active_workspace_id' => $primerWorkspace->id]);
                $user->refresh();
                $workspaceActivo = $primerWorkspace;
            }
        }

        // Compartir con la request para que los controladores y helpers lo accedan
        $request->merge(['_workspace' => $workspaceActivo]);

        return $next($request);
    }
}
