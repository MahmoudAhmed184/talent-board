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
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $employer, array $attributes): JobListing
    {
        return $this->jobListings->createForEmployer($employer, $attributes);
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
        return $this->jobListings->update($jobListing, [
            ...$attributes,
            'moderation_status' => 'pending',
            'published_at' => null,
        ]);
    }

    public function delete(JobListing $jobListing): void
    {
        $this->jobListings->delete($jobListing);
    }
}
