<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\JobListing;
use App\Models\User;

class JobListingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Employer;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Employer;
    }

    public function view(User $user, JobListing $jobListing): bool
    {
        return $user->role === UserRole::Employer
            && $jobListing->employer_id === $user->id;
    }

    public function update(User $user, JobListing $jobListing): bool
    {
        return $this->view($user, $jobListing);
    }

    public function delete(User $user, JobListing $jobListing): bool
    {
        return $this->view($user, $jobListing);
    }
}
