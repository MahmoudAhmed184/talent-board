<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_listing_id' => fake()->numberBetween(1, 5000),
            'employer_id' => User::factory()->employer(),
            'candidate_id' => User::factory()->candidate(),
            'status' => ApplicationStatus::Submitted,
            'cover_letter' => fake()->optional()->paragraph(),
            'resume_disk' => fake()->optional(0.7)->passthrough('s3'),
            'resume_path' => fake()->optional(0.7)->filePath(),
            'resume_original_name' => fake()->optional(0.7)->lexify('resume-????.pdf'),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->optional()->phoneNumber(),
            'submitted_at' => now()->subDay(),
            'decided_by_user_id' => null,
            'decided_at' => null,
            'decision_note' => null,
        ];
    }

    public function status(ApplicationStatus $status): static
    {
        return $this->state(fn (): array => [
            'status' => $status,
        ]);
    }
}
