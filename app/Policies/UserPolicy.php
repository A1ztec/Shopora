<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy extends BasePolicy
{
    public function getModelName(): string
    {
        return 'user';
    }
}

    /**
     * Determine whet}her the user can view any models.
     */

