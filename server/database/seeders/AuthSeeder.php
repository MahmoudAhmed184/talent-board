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
        $admin = $this->seedUser(User::factory()->admin()->make([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
        ]));

        $candidate = $this->seedUser(User::factory()->candidate()->make([
            'name' => 'Sample Candidate',
            'email' => 'candidate@example.com',
        ]));

        $employer = $this->seedUser(User::factory()->employer()->make([
            'name' => 'Sample Employer',
            'email' => 'employer@example.com',
        ]));

        // Generate an EmployerProfile for the Sample Employer
        \App\Models\EmployerProfile::factory()->forUser($employer)->create([
            'company_name' => 'Sample Company Inc.',
            'company_summary' => 'A great place to work with an amazing culture and standard tech stack.',
        ]);

        // Generate 10 random candidates
        User::factory()->candidate()->count(10)->create();

        // Generate 3 random employers with profiles
        $employers = User::factory()->employer()->count(3)->create();
        foreach ($employers as $emp) {
            \App\Models\EmployerProfile::factory()->forUser($emp)->create();
        }
    }

    private function seedUser(User $user): User
    {
        return User::query()->updateOrCreate(
            ['email' => $user->email],
            $user->only(['name', 'password', 'email_verified_at', 'role']),
        );
    }
}
