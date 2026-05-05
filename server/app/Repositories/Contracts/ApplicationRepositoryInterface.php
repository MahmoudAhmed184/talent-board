<?php

namespace App\Repositories\Contracts;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ApplicationRepositoryInterface
{
    public function paginateForEmployer(User $employer, int $perPage, ?ApplicationStatus $status): LengthAwarePaginator;

    public function findForEmployer(User $employer, Application $application): ?Application;

    public function paginateForCandidate(User $candidate, int $perPage, ?ApplicationStatus $status, ?string $fromDate = null, ?string $toDate = null): LengthAwarePaginator;

    public function findForCandidate(User $candidate, Application $application): ?Application;

    public function lock(Application $application): ?Application;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Application;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Application $application, array $attributes): Application;

    public function cancel(Application $application): Application;

    public function hasApplied(User $candidate, int $jobListingId): bool;

    /**
     * @return array<int>
     */
    public function getAppliedJobIds(User $candidate): array;
}
