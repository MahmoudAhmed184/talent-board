<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyEmployerOfApplication implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Application $application,
    ) {}

    public function handle(NotificationRepositoryInterface $notifications): void
    {
        $employerId = $this->application->employer_id;

        $notifications->create(
            type: 'App\Notifications\NewApplicationReceived',
            notifiableType: User::class,
            notifiableId: $employerId,
            data: [
                'application_id' => $this->application->id,
                'job_listing_id' => $this->application->job_listing_id,
                'job_title' => $this->application->jobListing->title,
                'candidate_name' => $this->application->candidate->name,
                'message' => 'A new application has been submitted for your job listing.',
            ]
        );
    }
}
