<?php

namespace App\Http\Controllers\Api\V1\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCandidateProfileRequest;
use App\Http\Resources\CandidateProfileResource;
use App\Models\CandidateProfile;
use App\Models\User;
use App\Services\CandidateProfileService;
use Illuminate\Http\Request;

class CandidateProfileController extends Controller
{
    public function __construct(
        private readonly CandidateProfileService $profileService,
    ) {}

    public function show(Request $request): CandidateProfileResource
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('view', CandidateProfile::class);

        $profile = $this->profileService->getProfileForUser($user);
        $profile->load('defaultResume');

        return new CandidateProfileResource($profile);
    }

    public function update(UpdateCandidateProfileRequest $request): CandidateProfileResource
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('update', CandidateProfile::class);

        $profile = $this->profileService->updateProfileForUser($user, $request->validated());
        $profile->load('defaultResume');

        return new CandidateProfileResource($profile);
    }
}
