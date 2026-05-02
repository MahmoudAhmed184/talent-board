<?php

namespace Database\Factories;

use App\Models\User;

class EmployerProfileFactory
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

    public function withLogo(string $path, string $originalName, string $disk = 's3'): self
    {
        return $this->state([
            'logo_disk' => $disk,
            'logo_path' => $path,
            'logo_original_name' => $originalName,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'company_name' => fake()->company(),
            'company_summary' => fake()->paragraph(),
            'logo_disk' => null,
            'logo_path' => null,
            'logo_original_name' => null,
        ];
    }
}
