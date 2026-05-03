<?php

namespace App\Repositories;

use App\Models\EmployerProfile;
use App\Models\User;
use App\Repositories\Contracts\EmployerProfileRepositoryInterface;

class EloquentEmployerProfileRepository implements EmployerProfileRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createForUser(User $user, array $attributes): EmployerProfile
    {
        return EmployerProfile::query()->create([
            ...$attributes,
            'user_id' => $user->id,
        ]);
    }
}
