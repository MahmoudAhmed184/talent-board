<?php

namespace App\Repositories;

use App\Models\Resume;
use App\Models\User;
use App\Repositories\Contracts\ResumeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentResumeRepository implements ResumeRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Resume::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForUser(User $user, Resume $resume): ?Resume
    {
        return Resume::query()
            ->whereKey($resume->getKey())
            ->where('user_id', $user->id)
            ->first();
    }

    public function existsForUser(User $user, int $resumeId): bool
    {
        return Resume::query()
            ->whereKey($resumeId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes): Resume
    {
        return $user->resumes()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resume $resume, array $attributes): Resume
    {
        $resume->update($attributes);

        return $resume;
    }

    public function delete(Resume $resume): void
    {
        $resume->delete();
    }
}
