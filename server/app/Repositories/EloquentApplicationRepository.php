<?php

namespace App\Repositories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use App\Repositories\Contracts\ApplicationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentApplicationRepository implements ApplicationRepositoryInterface
{
    public function paginateForEmployer(User $employer, int $perPage, ?ApplicationStatus $status): LengthAwarePaginator
    {
        return Application::query()
            ->with(['candidate:id,name,email', 'jobListing:id,title,employer_user_id'])
            ->where('employer_id', $employer->id)
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForEmployer(User $employer, Application $application): ?Application
    {
        return Application::query()
            ->with(['candidate:id,name,email', 'jobListing:id,title,employer_user_id'])
            ->whereKey($application->getKey())
            ->where('employer_id', $employer->id)
            ->first();
    }

    public function paginateForCandidate(User $candidate, int $perPage, ?ApplicationStatus $status, ?string $fromDate = null, ?string $toDate = null): LengthAwarePaginator
    {
        return Application::query()
            ->with(['jobListing', 'jobListing.employer', 'jobListing.employer.employerProfile'])
            ->where('candidate_id', $candidate->id)
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->when($fromDate, fn ($query) => $query->whereDate('submitted_at', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('submitted_at', '<=', $toDate))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForCandidate(User $candidate, Application $application): ?Application
    {
        return Application::query()
            ->with(['jobListing', 'jobListing.employer', 'jobListing.employer.employerProfile'])
            ->whereKey($application->getKey())
            ->where('candidate_id', $candidate->id)
            ->first();
    }

    public function lock(Application $application): ?Application
    {
        return Application::query()
            ->whereKey($application->getKey())
            ->lockForUpdate()
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Application
    {
        $application = Application::create($attributes);

        return $application->refresh()->loadMissing(['jobListing', 'jobListing.employer', 'jobListing.employer.employerProfile']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Application $application, array $attributes): Application
    {
        $application->update($attributes);

        return $application->refresh()->loadMissing(['candidate:id,name,email', 'jobListing:id,title,employer_user_id']);
    }

    public function cancel(Application $application): Application
    {
        $application->update([
            'status' => ApplicationStatus::Cancelled,
            'decided_at' => now(),
        ]);

        return $application->refresh()->loadMissing(['jobListing', 'jobListing.employer', 'jobListing.employer.employerProfile']);
    }

    public function hasApplied(User $candidate, int $jobListingId): bool
    {
        return Application::query()
            ->where('candidate_id', $candidate->id)
            ->where('job_listing_id', $jobListingId)
            ->exists();
    }

    /**
     * @return array<int>
     */
    public function getAppliedJobIds(User $candidate): array
    {
        return Application::query()
            ->where('candidate_id', $candidate->id)
            ->pluck('job_listing_id')
            ->toArray();
    }
}
