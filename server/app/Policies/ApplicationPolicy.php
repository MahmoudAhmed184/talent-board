<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Employer;
    }

    public function view(User $user, Application $application): bool
    {
        return $user->role === UserRole::Employer
            && $application->employer_id === $user->id;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $this->view($user, $application);
    }
}
