<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $salaryMin = fake()->numberBetween(40000, 90000);

        return [
            'employer_id' => User::factory()->employer(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'responsibilities' => fake()->paragraph(),
            'qualifications' => fake()->paragraph(),
            'location' => fake()->city(),
            'category' => fake()->randomElement(['Engineering', 'Design', 'Product', 'Operations']),
            'work_type' => fake()->randomElement(['remote', 'on-site', 'hybrid']),
            'experience_level' => fake()->randomElement(['junior', 'mid', 'senior', 'lead']),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMin + fake()->numberBetween(10000, 50000),
            'moderation_status' => 'pending',
            'published_at' => null,
            'expires_at' => now()->addMonth(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'moderation_status' => 'approved',
            'published_at' => now()->subDay(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'moderation_status' => 'rejected',
            'published_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
