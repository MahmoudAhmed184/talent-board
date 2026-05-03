<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ApplicationStatusBroadcast implements ShouldBroadcast
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly array $payload,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("application-status.candidate.{$this->payload['candidate_id']}"),
            new PrivateChannel("application-status.employer.{$this->payload['employer_id']}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ApplicationStatusChanged';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
