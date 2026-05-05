<?php

namespace Database\Factories;

use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CandidateProfile>
 */
class CandidateProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->candidate(),
            'summary' => fake()->paragraph(),
            'location_text' => fake()->city() . ', ' . fake()->country(),
            'phone' => fake()->phoneNumber(),
            'skills' => array_values(fake()->randomElements([
                'PHP',
                'Laravel',
                'Vue',
                'TypeScript',
                'JavaScript',
                'SQL',
                'Testing',
                'Docker',
                'Git',
                'REST APIs',
                'CSS',
                'HTML',
            ], fake()->numberBetween(2, 5))),
            'default_resume_id' => null,
        ];
    }

    public function forUser(User|int $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user instanceof User ? $user->getKey() : $user,
        ]);
    }
}
