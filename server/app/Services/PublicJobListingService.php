<?php

namespace App\Services;

use App\Models\JobListing;
use App\Repositories\Contracts\JobListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PublicJobListingService
{
    public function __construct(
        private readonly JobListingRepositoryInterface $jobListings,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->jobListings->paginatePublic($filters, $perPage);
    }

    public function show(JobListing $jobListing): JobListing
    {
        abort_unless($this->jobListings->isPublic($jobListing), 404);

        return $this->jobListings->loadEmployer($jobListing);
    }
}
