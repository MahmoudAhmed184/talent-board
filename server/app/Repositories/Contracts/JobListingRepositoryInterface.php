<?php

namespace App\Repositories\Contracts;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface JobListingRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForEmployer(User $employer, array $attributes): JobListing;

    public function paginateForEmployer(User $employer, ?string $status, int $perPage): LengthAwarePaginator;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(JobListing $jobListing, array $attributes): JobListing;

    public function delete(JobListing $jobListing): void;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginatePublic(array $filters, int $perPage): LengthAwarePaginator;

    public function isPublic(JobListing $jobListing): bool;

    public function loadEmployer(JobListing $jobListing): JobListing;
}
