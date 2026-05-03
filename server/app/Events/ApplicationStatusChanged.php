<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ApplicationStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly int $applicationId,
        public readonly int $candidateId,
        public readonly int $employerId,
        public readonly ?int $jobListingId,
        public readonly string $status,
        public readonly string $changedAt,
    ) {}

    public static function fromApplication(Application $application): self
    {
        return new self(
            applicationId: $application->id,
            candidateId: $application->candidate_id,
            employerId: $application->employer_id,
            jobListingId: $application->job_listing_id,
            status: $application->status->value,
            changedAt: $application->decided_at?->toIso8601String() ?? now()->toIso8601String(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastPayload(): array
    {
        return [
            'application_id' => $this->applicationId,
            'candidate_id' => $this->candidateId,
            'employer_id' => $this->employerId,
            'job_listing_id' => $this->jobListingId,
            'status' => $this->status,
            'changed_at' => $this->changedAt,
        ];
    }
}
