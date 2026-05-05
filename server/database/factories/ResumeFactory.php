<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resume>
 */
class ResumeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->candidate(),
            'storage_disk' => 's3',
            'storage_path' => 'resumes/' . fake()->uuid() . '.pdf',
            'original_name' => fake()->lexify('resume-????.pdf'),
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(50_000, 5_000_000),
            'checksum' => fake()->sha256(),
        ];
    }

    public function forUser(User|int $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user instanceof User ? $user->getKey() : $user,
        ]);
    }

    public function docx(): static
    {
        return $this->state(fn (): array => [
            'storage_path' => 'resumes/' . fake()->uuid() . '.docx',
            'original_name' => fake()->lexify('resume-????.docx'),
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
