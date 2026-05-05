<?php

namespace App\Repositories;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(string $type, string $notifiableType, int|string $notifiableId, array $data): void
    {
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => $type,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'data' => json_encode($data),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
