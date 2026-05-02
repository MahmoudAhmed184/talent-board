<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedUser(User::factory()->admin()->make([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
        ]));

        $this->seedUser(User::factory()->candidate()->make([
            'name' => 'Sample Candidate',
            'email' => 'candidate@example.com',
        ]));

        $this->seedUser(User::factory()->employer()->make([
            'name' => 'Sample Employer',
            'email' => 'employer@example.com',
        ]));
    }

    private function seedUser(User $user): void
    {
        User::query()->updateOrCreate(
            ['email' => $user->email],
            $user->only(['name', 'password', 'email_verified_at', 'role']),
        );
    }
}
