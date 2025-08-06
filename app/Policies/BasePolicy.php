<?php


namespace app\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    public abstract function getModelName(): string;


    public function viewAny(User $user, $model): bool
    {
        return $user->hasPermission('view_any_' . $this->getModelName()) || $user->isSuperAdmin();
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasPermission('view_' . $this->getModelName()) ||
            $user->isSuperAdmin() ||
            $this->isOwner($user, $model);
    }

    /**
     * Check if user can create records
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_' . $this->getModelName()) ||
            $user->isSuperAdmin();
    }

    /**
     * Check if user can update the record
     */
    public function update(User $user, Model $model): bool
    {
        return $user->hasPermission('update_' . $this->getModelName()) ||
            $user->isSuperAdmin() ||
            $this->isOwner($user, $model);
    }

    /**
     * Check if user can delete the record
     */
    public function delete(User $user, Model $model): bool
    {
        return $user->hasPermission('delete_' . $this->getModelName()) ||
            $user->isSuperAdmin() ||
            ($this->isOwner($user, $model) && $user->hasPermission('delete_' . $this->getModelName()));
    }

    /**
     * Check if user can delete any records
     */
    public function deleteAny(User $user): bool
    {
        return $user->hasPermission('delete_any_' . $this->getModelName()) ||
            $user->isSuperAdmin();
    }

    /**
     * Check if user owns the record
     */
    protected function isOwner(User $user, Model $model): bool
    {
        return isset($model->user_id) && $model->user_id === $user->id;
    }
}
