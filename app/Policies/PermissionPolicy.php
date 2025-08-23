<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PermissionPolicy extends BasePolicy
{
    /**
     *
     * Determine whether the user can view any models.
     */

    public function getModelName(): string
    {
        return 'permission';
    }
}
