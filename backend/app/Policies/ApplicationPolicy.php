<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function canDownloadApplication(User $user, Application $application): bool
    {
        // Check if the user is the owner of the application
        return $user->id === $application->user_id || $user->id === $application->authorized_by;
    }

    public function canDownloadAttachment(User $user, Application $application): bool
    {
        // Check if the user is the owner of the application
        return $user->id === $application->user_id || $user->id === $application->authorized_by;
    }
}
