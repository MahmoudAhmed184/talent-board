<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Resume;
use App\Models\User;

class ResumePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Candidate;
    }

    public function view(User $user, Resume $resume): bool
    {
        return $user->role === UserRole::Candidate
            && $resume->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Candidate;
    }

    public function delete(User $user, Resume $resume): bool
    {
        return $user->role === UserRole::Candidate
            && $resume->user_id === $user->id;
    }
}
