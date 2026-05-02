<?php

namespace Database\Factories;

use App\Models\User;

class CandidateProfileFactory
{
    /**
     * @param  array<string, mixed>  $state
     */
    public function __construct(
        private array $state = [],
    ) {}

    public static function new(): self
    {
        return new self;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function make(array $overrides = []): array
    {
        return array_replace($this->definition(), $this->state, $overrides);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function state(array $state): self
    {
        $this->state = array_replace($this->state, $state);

        return $this;
    }

    public function forUser(User|int $user): self
    {
        return $this->state([
            'user_id' => $user instanceof User ? $user->getKey() : $user,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'summary' => fake()->paragraph(),
            'location_text' => fake()->city().', '.fake()->country(),
            'phone' => fake()->phoneNumber(),
            'skills' => array_values(fake()->randomElements([
                'PHP',
                'Laravel',
                'Vue',
                'TypeScript',
                'SQL',
                'Testing',
            ], 3)),
            'default_resume_id' => null,
        ];
    }
}
