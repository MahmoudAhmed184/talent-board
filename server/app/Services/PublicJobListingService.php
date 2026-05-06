<?php

namespace App\Services;

use App\Models\JobListing;
use App\Repositories\Contracts\JobListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PublicJobListingService
{
    public function __construct(
        private readonly JobListingRepositoryInterface $jobListings,
        private readonly SearchService $searchService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, int $perPage): LengthAwarePaginator
    {
        $preparedFilters = $this->searchService->prepareJobFilters($filters);

        return $this->jobListings->paginatePublic($preparedFilters, $perPage);
    }

    public function show(JobListing $jobListing): JobListing
    {
        abort_unless($this->jobListings->isPublic($jobListing), 404);

        return $this->jobListings->loadEmployer($jobListing);
    }
}
