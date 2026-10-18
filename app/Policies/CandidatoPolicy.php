<?php

namespace App\Policies;

use App\Models\Candidato;
use App\Models\User;

class CandidatoPolicy
{
    /**
     * Determina si el usuario puede ver la lista de candidatos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina si el usuario puede ver un candidato específico.
     */
    public function view(User $user, Candidato $candidato): bool
    {
        return (int) $candidato->workspace_id === (int) $user->active_workspace_id || $user->isAdmin();
    }

    /**
     * Determina si el usuario puede crear candidatos.
     */
    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    /**
     * Determina si el usuario puede actualizar el candidato.
     */
    public function update(User $user, Candidato $candidato): bool
    {
        return $user->canWrite() && ((int) $candidato->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }

    /**
     * Determina si el usuario puede eliminar el candidato.
     */
    public function delete(User $user, Candidato $candidato): bool
    {
        return $user->canWrite() && ((int) $candidato->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }
}
