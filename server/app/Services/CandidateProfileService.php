<?php

namespace App\Services;

use App\Models\CandidateProfile;
use App\Models\User;
use App\Repositories\Contracts\CandidateProfileRepositoryInterface;
use App\Repositories\Contracts\ResumeRepositoryInterface;
use Illuminate\Validation\ValidationException;

class CandidateProfileService
{
    public function __construct(
        private readonly CandidateProfileRepositoryInterface $profiles,
        private readonly ResumeRepositoryInterface $resumes,
    ) {}

    public function getProfileForUser(User $user): CandidateProfile
    {
        return $this->profiles->findByUser($user) ?? $this->profiles->updateOrCreate($user, []);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @throws ValidationException
     */
    public function updateProfileForUser(User $user, array $attributes): CandidateProfile
    {
        if (isset($attributes['default_resume_id'])) {
            $resumeId = $attributes['default_resume_id'];
            if (! $this->resumes->existsForUser($user, $resumeId)) {
                throw ValidationException::withMessages([
                    'default_resume_id' => 'The selected resume does not belong to you or does not exist.',
                ]);
            }
        }

        return $this->profiles->updateOrCreate($user, $attributes);
    }
}
