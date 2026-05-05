<?php

namespace App\Repositories;

use App\Models\CandidateProfile;
use App\Models\User;
use App\Repositories\Contracts\CandidateProfileRepositoryInterface;

class EloquentCandidateProfileRepository implements CandidateProfileRepositoryInterface
{
    public function findByUser(User $user): ?CandidateProfile
    {
        return $user->candidateProfile;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreate(User $user, array $attributes): CandidateProfile
    {
        return $user->candidateProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $attributes
        );
    }
}
