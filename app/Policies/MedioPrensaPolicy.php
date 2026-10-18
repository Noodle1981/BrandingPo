<?php

namespace App\Policies;

use App\Models\MedioPrensa;
use App\Models\User;

class MedioPrensaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MedioPrensa $medio): bool
    {
        return (int) $medio->workspace_id === (int) $user->active_workspace_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function update(User $user, MedioPrensa $medio): bool
    {
        return $user->canWrite() && ((int) $medio->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }

    public function delete(User $user, MedioPrensa $medio): bool
    {
        return $user->canWrite() && ((int) $medio->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }
}
