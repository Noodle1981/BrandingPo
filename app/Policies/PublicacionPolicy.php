<?php

namespace App\Policies;

use App\Models\Publicacion;
use App\Models\User;

class PublicacionPolicy
{
    /**
     * Determina si el usuario puede ver publicaciones.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver una publicación específica.
     */
    public function view(User $user, Publicacion $publicacion): bool
    {
        return (int) $publicacion->workspace_id === (int) $user->active_workspace_id || $user->isAdmin();
    }

    /**
     * Determina si el usuario puede crear publicaciones.
     */
    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    /**
     * Determina si el usuario puede actualizar la publicación.
     */
    public function update(User $user, Publicacion $publicacion): bool
    {
        return $user->canWrite() && ((int) $publicacion->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }

    /**
     * Determina si el usuario puede eliminar la publicación.
     */
    public function delete(User $user, Publicacion $publicacion): bool
    {
        return $user->canWrite() && ((int) $publicacion->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }
}
