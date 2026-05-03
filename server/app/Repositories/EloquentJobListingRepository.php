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
            'employer_id' => $employer->id,
            'moderation_status' => 'pending',
            'published_at' => null,
        ]);
    }

    public function paginateForEmployer(User $employer, ?string $status, int $perPage): LengthAwarePaginator
    {
        return JobListing::query()
            ->where('employer_id', $employer->id)
            ->when($status, fn (Builder $query): Builder => $query->where('moderation_status', $status))
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
            ->when($filters['location'] ?? null, fn (Builder $query, string $value): Builder => $query->where('location', 'like', "%{$value}%"))
            ->when($filters['category'] ?? null, fn (Builder $query, string $value): Builder => $query->where('category', $value))
            ->when($filters['work_type'] ?? null, fn (Builder $query, string $value): Builder => $query->where('work_type', $value))
            ->when($filters['experience_level'] ?? null, fn (Builder $query, string $value): Builder => $query->where('experience_level', $value))
            ->when($filters['salary_min'] ?? null, fn (Builder $query, int $value): Builder => $query->where('salary_max', '>=', $value))
            ->when($filters['salary_max'] ?? null, fn (Builder $query, int $value): Builder => $query->where('salary_min', '<=', $value))
            ->when($filters['date_posted'] ?? null, function (Builder $query, string $value): Builder {
                $cutoff = match ($value) {
                    '24h' => now()->subDay(),
                    '7d' => now()->subDays(7),
                    '30d' => now()->subDays(30),
                    default => null,
                };

                return $cutoff ? $query->where('published_at', '>=', $cutoff) : $query;
            })
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
            ->with(['employer.employerProfile'])
            ->where('moderation_status', 'approved')
            ->whereNotNull('published_at')
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }
}
