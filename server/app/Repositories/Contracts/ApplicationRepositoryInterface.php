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

    public function lock(Application $application): ?Application;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Application $application, array $attributes): Application;
}
