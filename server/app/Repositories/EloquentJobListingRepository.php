<?php

namespace App\Repositories;

use App\Models\JobListing;
use App\Models\User;
use App\Repositories\Contracts\JobListingRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentJobListingRepository implements JobListingRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForEmployer(User $employer, array $attributes): JobListing
    {
        return JobListing::query()->create([
            ...$attributes,
            'employer_user_id' => $employer->id,
            'approval_status' => 'pending',
            'published_at' => null,
        ]);
    }

    public function paginateForEmployer(User $employer, ?string $status, int $perPage): LengthAwarePaginator
    {
        return JobListing::query()
            ->where('employer_user_id', $employer->id)
            ->when($status, fn (Builder $query): Builder => $query->where('approval_status', $status))
            ->latest('updated_at')
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(JobListing $jobListing, array $attributes): JobListing
    {
        $jobListing->update($attributes);

        return $jobListing->refresh();
    }

    public function delete(JobListing $jobListing): void
    {
        $jobListing->delete();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginatePublic(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->publicQuery()
            ->when($filters['q'] ?? null, function (Builder $query, string $keyword): Builder {
                return $query->where(function (Builder $nested) use ($keyword): void {
                    $nested
                        ->where('title', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['location_id'] ?? null, fn (Builder $query, int $value): Builder => $query->where('location_id', $value))
            ->when($filters['category_id'] ?? null, fn (Builder $query, int $value): Builder => $query->where('category_id', $value))
            ->when($filters['work_type'] ?? null, fn (Builder $query, string $value): Builder => $query->where('work_type', $value))
            ->when($filters['experience_level'] ?? null, fn (Builder $query, string $value): Builder => $query->where('experience_level', $value))
            ->when($filters['salary_min'] ?? null, fn (Builder $query, int $value): Builder => $query->where('salary_max', '>=', $value))
            ->when($filters['salary_max'] ?? null, fn (Builder $query, int $value): Builder => $query->where('salary_min', '<=', $value))
            ->when($filters['posted_after'] ?? null, fn (Builder $query, string $value): Builder => $query->where('published_at', '>=', $value))
            ->when($filters['posted_before'] ?? null, fn (Builder $query, string $value): Builder => $query->where('published_at', '<=', $value))
            ->latest('published_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function isPublic(JobListing $jobListing): bool
    {
        return $this->publicQuery()
            ->whereKey($jobListing->getKey())
            ->exists();
    }

    public function loadEmployer(JobListing $jobListing): JobListing
    {
        return $jobListing->loadMissing(['employer.employerProfile']);
    }

    private function publicQuery(): Builder
    {
        return JobListing::query()
            ->with(['employer.employerProfile', 'category', 'location'])
            ->where('approval_status', 'approved')
            ->whereNotNull('published_at')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', now());
            });
    }
}
