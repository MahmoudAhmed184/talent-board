<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\EmployerProfile;
use App\Models\User;

class EmployerProfilePolicy
{
    public function view(User $user, EmployerProfile $employerProfile): bool
    {
        return $user->role === UserRole::Employer && 
            ($employerProfile->exists === false || $employerProfile->user_id === $user->id);
    }

    public function update(User $user, EmployerProfile $employerProfile): bool
    {
        return $user->role === UserRole::Employer && 
            ($employerProfile->exists === false || $employerProfile->user_id === $user->id);
    }
}
