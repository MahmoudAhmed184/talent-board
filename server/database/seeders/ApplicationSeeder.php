<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = User::where('role', 'candidate')->has('resumes')->get();
        $jobListings = JobListing::where('approval_status', 'approved')->get();

        if ($candidates->isEmpty() || $jobListings->isEmpty()) {
            return;
        }

        foreach ($candidates as $candidate) {
            // Apply to 2-5 random jobs
            $jobsToApply = $jobListings->random(min($jobListings->count(), fake()->numberBetween(2, 5)));

            foreach ($jobsToApply as $job) {
                $resume = $candidate->resumes->first();

                $status = fake()->randomElement([
                    ApplicationStatus::Submitted,
                    ApplicationStatus::Submitted,
                    ApplicationStatus::UnderReview,
                    ApplicationStatus::Accepted,
                    ApplicationStatus::Rejected,
                ]);

                $decidedAt = $status->isEmployerDecision() ? now()->subDays(fake()->numberBetween(1, 5)) : null;

                Application::factory()->create([
                    'job_listing_id' => $job->id,
                    'employer_id' => $job->employer_user_id,
                    'candidate_id' => $candidate->id,
                    'status' => $status,
                    'resume_disk' => $resume->storage_disk,
                    'resume_path' => $resume->storage_path,
                    'resume_original_name' => $resume->original_name,
                    'contact_email' => $candidate->email,
                    'contact_phone' => $candidate->candidateProfile?->phone ?? fake()->phoneNumber(),
                    'submitted_at' => now()->subDays(fake()->numberBetween(6, 14)),
                    'decided_by_user_id' => $decidedAt ? $job->employer_user_id : null,
                    'decided_at' => $decidedAt,
                    'decision_note' => $status->isEmployerDecision() ? fake()->optional()->sentence() : null,
                ]);
            }
        }
    }
}
