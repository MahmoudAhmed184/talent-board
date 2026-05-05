<?php

namespace App\Http\Controllers\Api\V1\Candidate;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Resources\CandidateApplicationResource;
use App\Jobs\NotifyEmployerOfApplication;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use App\Repositories\Contracts\ApplicationRepositoryInterface;
use App\Repositories\Contracts\CandidateProfileRepositoryInterface;
use App\Repositories\Contracts\ResumeRepositoryInterface;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applicationService,
        private readonly ApplicationRepositoryInterface $applications,
        private readonly ResumeRepositoryInterface $resumes,
        private readonly CandidateProfileRepositoryInterface $profiles,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('viewAny', Application::class);

        $status = $request->query('status');
        $statusEnum = $status ? ApplicationStatus::tryFrom($status) : null;
        $perPage = $request->query('per_page', 15);

        return CandidateApplicationResource::collection(
            $this->applicationService->listForCandidate($user, (int) $perPage, $statusEnum)
        );
    }

    public function store(StoreApplicationRequest $request, JobListing $jobListing): CandidateApplicationResource
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('create', Application::class);

        $validated = $request->validated();
        
        $resume = null;
        if ($validated['submission_mode'] === 'resume') {
            $resume = $this->resumes->findForUser($user, app(\App\Models\Resume::class)->forceFill(['id' => $validated['resume_id']]));
            abort_unless($resume, 403, 'Invalid resume.');
        }

        $contactPhone = null;
        if ($validated['use_profile_contact']) {
            $profile = $this->profiles->findByUser($user);
            $contactPhone = $profile?->phone;
        }

        $application = $this->applications->create([
            'job_listing_id' => $jobListing->id,
            'candidate_id' => $user->id,
            'employer_id' => $jobListing->employer_user_id,
            'status' => ApplicationStatus::Submitted,
            'resume_id' => $resume?->id,
            'resume_disk' => $resume?->storage_disk,
            'resume_path' => $resume?->storage_path,
            'resume_original_name' => $resume?->original_name,
            'contact_email' => $user->email,
            'contact_phone' => $contactPhone,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'submission_mode' => $validated['submission_mode'],
            'submitted_at' => now(),
        ]);

        NotifyEmployerOfApplication::dispatch($application)->onQueue('notifications');

        return new CandidateApplicationResource($application);
    }

    public function cancel(Request $request, Application $application): CandidateApplicationResource
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('cancel', $application);

        return new CandidateApplicationResource(
            $this->applicationService->cancelApplication($user, $application)
        );
    }
}
