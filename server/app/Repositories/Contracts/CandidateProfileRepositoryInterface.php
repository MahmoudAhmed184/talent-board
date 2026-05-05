<?php

namespace App\Repositories\Contracts;

use App\Models\CandidateProfile;
use App\Models\User;

interface CandidateProfileRepositoryInterface
{
    public function findByUser(User $user): ?CandidateProfile;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreate(User $user, array $attributes): CandidateProfile;
}
