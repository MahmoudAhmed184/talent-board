<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\JobListing;
use App\Models\Location;
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
            'employer_user_id' => User::factory()->employer(),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'responsibilities' => fake()->paragraph(),
            'required_skills' => fake()->randomElements(['PHP', 'Laravel', 'Vue.js', 'MySQL', 'TypeScript'], fake()->numberBetween(2, 4)),
            'qualifications' => fake()->paragraph(),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMin + fake()->numberBetween(10000, 50000),
            'benefits' => fake()->paragraph(),
            'work_type' => fake()->randomElement(['remote', 'on-site', 'hybrid']),
            'technologies' => fake()->randomElements(['Docker', 'AWS', 'Redis', 'Tailwind'], fake()->numberBetween(1, 3)),
            'experience_level' => fake()->randomElement(['junior', 'mid', 'senior', 'lead']),
            'application_deadline' => now()->addMonth(),
            'approval_status' => 'pending',
            'published_at' => null,
            'approved_by' => null,
            'rejected_reason' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'approval_status' => 'approved',
            'published_at' => now()->subDay(),
            'approved_by' => User::factory()->admin(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'approval_status' => 'rejected',
            'published_at' => null,
            'rejected_reason' => fake()->sentence(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'application_deadline' => now()->subDay(),
        ]);
    }
}
