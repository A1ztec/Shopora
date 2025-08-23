<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function getModelName(): string
    {
        return 'role';
    }
    public function viewAny(User $user): bool
    {
        return false;
    }
}
