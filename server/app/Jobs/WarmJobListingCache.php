<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use App\Services\PublicJobListingService;

class WarmJobListingCache implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(PublicJobListingService $publicJobListingService): void
    {
        // For simplicity, we can just clear the main cached pages or tags if we were using tags.
        // Since we are using md5 hashes for the keys, we might need a way to clear them or we rely on TTL.
        // Or if we know the default query, we can warm it:
        $publicJobListingService->search([], 15);
    }
}
