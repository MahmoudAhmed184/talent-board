<?php

namespace App\Services;

use App\Models\JobListing;
use App\Repositories\Contracts\JobListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

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
        $cacheKey = 'public_jobs_search_' . md5(json_encode($preparedFilters) . "_perPage:{$perPage}");

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(60),
            fn (): LengthAwarePaginator => $this->jobListings->paginatePublic($preparedFilters, $perPage)
        );
    }

    public function show(JobListing $jobListing): JobListing
    {
        abort_unless($this->jobListings->isPublic($jobListing), 404);

        $cacheKey = "public_job_{$jobListing->id}";

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(60),
            fn (): JobListing => $this->jobListings->loadEmployer($jobListing)
        );
    }
}
