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
        if ($user->role === UserRole::Employer) {
            return $application->employer_id === $user->id;
        }

        if ($user->role === UserRole::Candidate) {
            return $application->candidate_id === $user->id;
        }

        return false;
    }

    public function viewOwn(User $user): bool
    {
        return $user->role === UserRole::Candidate;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Candidate;
    }

    public function cancel(User $user, Application $application): bool
    {
        return $user->role === UserRole::Candidate && $application->candidate_id === $user->id;
    }

    public function updateStatus(User $user, Application $application): bool
    {
        return $user->role === UserRole::Employer && $application->employer_id === $user->id;
    }
}
