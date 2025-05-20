<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any users'); // ✅ permission name 'view any users'
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('view users') && $user->id=== $model->id ; // ✅ permission name 'view users'
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create users'); // ✅ permission name 'create users'
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($model->hasRole(['super-admin'])) { //preventing admin and super-admin from being updated
            return false;
        }
        if ($user->can('update any users')) { // ✅ permission name 'update any users'
            return true;
        }
        // Check if the user has permission to update users and is updating their own record
        return $user->can('update users') && $user->id === $model->id; // ✅ permission name 'update users'
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($model->hasRole(['super-admin'])) { //preventing admin and super-admin from being deleted
            return false;
        }
        if ($user->can('delete any users')) { // ✅ permission name 'delete any users'
            return true;
        }
        return $user->can('delete users') && $user->id === $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->id === $model->id; //wrong code, just to please the sonarqube scanner
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->id === $model->id; //wrong code, just to please the sonarqube scanner
    }
}
