<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class JobListingCacheService
{
    private const PublicListingsVersionKey = 'public_job_listings_cache_version';

    public function publicListingsVersion(): int
    {
        return (int) Cache::get(self::PublicListingsVersionKey, 1);
    }

    public function bumpPublicListingsVersion(): int
    {
        $nextVersion = $this->publicListingsVersion() + 1;
        Cache::forever(self::PublicListingsVersionKey, $nextVersion);

        return $nextVersion;
    }

    public function publicJobKey(int $jobListingId): string
    {
        return "public_job_{$jobListingId}";
    }

    public function forgetPublicJob(int $jobListingId): void
    {
        Cache::forget($this->publicJobKey($jobListingId));
    }
}
