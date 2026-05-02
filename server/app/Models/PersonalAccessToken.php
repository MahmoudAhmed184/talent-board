<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

#[Table('personal_access_tokens')]
#[Fillable(['name', 'token', 'abilities', 'expires_at'])]
#[Hidden(['token'])]
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'abilities' => 'json',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
