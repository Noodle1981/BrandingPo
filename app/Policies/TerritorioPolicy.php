<?php

namespace App\Policies;

use App\Models\Territorio;
use App\Models\User;

class TerritorioPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Territorio $territorio): bool
    {
        return (int) $territorio->workspace_id === (int) $user->active_workspace_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->canWrite();
    }

    public function update(User $user, Territorio $territorio): bool
    {
        return $user->canWrite() && ((int) $territorio->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }

    public function delete(User $user, Territorio $territorio): bool
    {
        return $user->canWrite() && ((int) $territorio->workspace_id === (int) $user->active_workspace_id || $user->isAdmin());
    }
}
