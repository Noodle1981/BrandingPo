<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $workspace = $request->get('_workspace') ?: ($user?->active_workspace_id ? Workspace::find($user->active_workspace_id) : Workspace::first());

        $rolEnWorkspace = $user ? $user->getRolEnWorkspaceActivo() : 'visualizador';
        $canWrite = $user ? $user->puedeEscribir() : false;
        $isAdmin = $user ? $user->esAdmin() : false;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $rolEnWorkspace,
                    'global_role' => $user->role ?? 'visualizador',
                    'can_write' => $canWrite,
                    'is_admin' => $isAdmin,
                    'active_workspace_id' => $workspace?->id,
                ] : null,
            ],
            'workspace' => $workspace ? [
                'id' => $workspace->id,
                'nombre' => $workspace->nombre,
                'slug' => $workspace->slug,
                'nivel_politico' => $workspace->nivel_politico,
                'nivel_label' => $workspace->nivel_politico_label,
                'provincia' => $workspace->provincia,
                'plan' => $workspace->plan,
            ] : null,
            'workspaces_disponibles' => fn () => $user
                ? ($user->role === 'admin'
                    ? Workspace::where('activo', true)->get(['id', 'nombre', 'slug', 'nivel_politico'])
                    : $user->workspaces()->where('activo', true)->get(['workspaces.id', 'workspaces.nombre', 'workspaces.slug', 'workspaces.nivel_politico']))
                : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
