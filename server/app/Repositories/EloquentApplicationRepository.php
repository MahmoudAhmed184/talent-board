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
            ->with(['candidate:id,name,email', 'jobListing:id,title,employer_id'])
            ->where('employer_id', $employer->id)
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForEmployer(User $employer, Application $application): ?Application
    {
        return Application::query()
            ->with(['candidate:id,name,email', 'jobListing:id,title,employer_id'])
            ->whereKey($application->getKey())
            ->where('employer_id', $employer->id)
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
    public function update(Application $application, array $attributes): Application
    {
        $application->update($attributes);

        return $application->refresh()->loadMissing(['candidate:id,name,email', 'jobListing:id,title,employer_id']);
    }
}
