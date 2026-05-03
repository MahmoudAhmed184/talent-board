<?php

namespace App\Repositories\Contracts;

use App\Models\EmployerProfile;
use App\Models\User;

interface EmployerProfileRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(User $user, array $attributes): EmployerProfile;
}
