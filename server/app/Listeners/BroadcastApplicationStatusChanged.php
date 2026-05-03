<?php

namespace App\Listeners;

use App\Events\ApplicationStatusBroadcast;
use App\Events\ApplicationStatusChanged;

class BroadcastApplicationStatusChanged
{
    public function handle(ApplicationStatusChanged $event): void
    {
        broadcast(new ApplicationStatusBroadcast($event->broadcastPayload()));
    }
}
