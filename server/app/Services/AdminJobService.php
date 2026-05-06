<?php

namespace App\Services;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AdminJobService
{
    public function __construct(
        private readonly JobListingCacheService $jobListingCache,
    ) {}

    public function listAll(?string $status, int $perPage): LengthAwarePaginator
    {
        return JobListing::query()
            ->with(['employer.employerProfile', 'category', 'location'])
            ->when($status, fn ($query) => $query->where('approval_status', $status))
            ->latest('updated_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function listPending(int $perPage): LengthAwarePaginator
    {
        return $this->listAll('pending', $perPage);
    }

    public function approve(JobListing $jobListing, User $admin): JobListing
    {
        if ($jobListing->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'job' => 'Only pending jobs can be approved.',
            ]);
        }

        $jobListing->update([
            'approval_status' => 'approved',
            'published_at' => now(),
            'approved_by' => $admin->id,
            'rejected_reason' => null,
        ]);

        return $this->afterModerationChange($jobListing);
    }

    public function reject(JobListing $jobListing, User $admin, ?string $reason): JobListing
    {
        if ($jobListing->approval_status !== 'pending') {
            throw ValidationException::withMessages([
                'job' => 'Only pending jobs can be rejected.',
            ]);
        }

        $jobListing->update([
            'approval_status' => 'rejected',
            'published_at' => null,
            'approved_by' => $admin->id,
            'rejected_reason' => $reason,
        ]);

        return $this->afterModerationChange($jobListing);
    }

    private function afterModerationChange(JobListing $jobListing): JobListing
    {
        $this->jobListingCache->forgetPublicJob($jobListing->id);
        $this->jobListingCache->bumpPublicListingsVersion();

        return $jobListing->refresh()->loadMissing(['employer.employerProfile', 'category', 'location']);
    }
}
