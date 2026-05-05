<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CandidateProfile;
use App\Models\User;

class CandidateProfilePolicy
{
    public function view(User $user): bool
    {
        return $user->role === UserRole::Candidate;
    }

    public function update(User $user): bool
    {
        return $user->role === UserRole::Candidate;
    }
}
