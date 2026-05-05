<?php

namespace App\Services;

use App\Models\JobListing;
use App\Models\User;
use App\Repositories\Contracts\JobListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JobListingService
{
    public function __construct(
        private readonly JobListingRepositoryInterface $jobListings,
        private readonly JobListingCacheService $jobListingCache,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $employer, array $attributes): JobListing
    {
        $jobListing = $this->jobListings->createForEmployer(
            $employer,
            $this->normalizeAttributes($attributes),
        );

        $this->jobListingCache->bumpPublicListingsVersion();

        return $jobListing;
    }

    public function listForEmployer(User $employer, ?string $status, int $perPage): LengthAwarePaginator
    {
        return $this->jobListings->paginateForEmployer($employer, $status, $perPage);
    }

    public function showForEmployer(JobListing $jobListing): JobListing
    {
        return $this->jobListings->loadEmployer($jobListing);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(JobListing $jobListing, array $attributes): JobListing
    {
        $updated = $this->jobListings->update($jobListing, [
            ...$this->normalizeAttributes($attributes),
            'approval_status' => 'pending',
            'published_at' => null,
            'approved_by' => null,
            'rejected_reason' => null,
        ]);

        $this->jobListingCache->forgetPublicJob($jobListing->id);
        $this->jobListingCache->bumpPublicListingsVersion();

        return $updated;
    }

    public function delete(JobListing $jobListing): void
    {
        $this->jobListings->delete($jobListing);
        $this->jobListingCache->forgetPublicJob($jobListing->id);
        $this->jobListingCache->bumpPublicListingsVersion();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes): array
    {
        $normalized = $attributes;

        if (array_key_exists('expires_at', $normalized)) {
            $normalized['application_deadline'] = $normalized['expires_at'];
            unset($normalized['expires_at']);
        }

        return $normalized;
    }
}
