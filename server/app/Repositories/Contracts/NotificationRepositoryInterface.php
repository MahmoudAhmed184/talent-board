<?php

namespace App\Repositories\Contracts;

interface NotificationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(string $type, string $notifiableType, int|string $notifiableId, array $data): void;
}
