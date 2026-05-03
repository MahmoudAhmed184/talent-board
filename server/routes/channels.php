<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('application-status.candidate.{candidateId}', function (User $user, string|int $candidateId): bool {
    return $user->isCandidate() && $user->id === (int) $candidateId;
}, ['guards' => ['sanctum']]);

Broadcast::channel('application-status.employer.{employerId}', function (User $user, string|int $employerId): bool {
    return $user->isEmployer() && $user->id === (int) $employerId;
}, ['guards' => ['sanctum']]);
