<?php

namespace App\Repositories\Contracts;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResumeRepositoryInterface
{
    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator;

    public function findForUser(User $user, Resume $resume): ?Resume;

    public function existsForUser(User $user, int $resumeId): bool;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(User $user, array $attributes): Resume;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resume $resume, array $attributes): Resume;

    public function delete(Resume $resume): void;
}
