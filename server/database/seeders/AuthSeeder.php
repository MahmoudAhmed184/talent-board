<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = $this->seedUser([
            'name' => 'Platform Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $candidate = $this->seedUser([
            'name' => 'Sample Candidate',
            'email' => 'candidate@example.com',
            'password' => 'password',
            'role' => UserRole::Candidate,
            'email_verified_at' => now(),
        ]);

        $employer = $this->seedUser([
            'name' => 'Sample Employer',
            'email' => 'employer@example.com',
            'password' => 'password',
            'role' => UserRole::Employer,
            'email_verified_at' => now(),
        ]);

        // Generate an EmployerProfile for the Sample Employer
        \App\Models\EmployerProfile::query()->updateOrCreate(
            ['user_id' => $employer->id],
            [
                'company_name' => 'Sample Company Inc.',
                'company_summary' => 'A great place to work with an amazing culture and standard tech stack.',
            ]
        );

        // Generate 10 explicit candidates
        for ($i = 1; $i <= 10; $i++) {
            $this->seedUser([
                'name' => "Candidate $i",
                'email' => "candidate$i@example.com",
                'password' => 'password',
                'role' => UserRole::Candidate,
                'email_verified_at' => now(),
            ]);
        }

        // Generate 3 explicit employers with profiles
        for ($i = 1; $i <= 3; $i++) {
            $emp = $this->seedUser([
                'name' => "Employer $i",
                'email' => "employer$i@example.com",
                'password' => 'password',
                'role' => UserRole::Employer,
                'email_verified_at' => now(),
            ]);

            \App\Models\EmployerProfile::query()->updateOrCreate(
                ['user_id' => $emp->id],
                ['company_name' => "Company $i Inc."]
            );
        }
    }

    private function seedUser(array $attributes): User
    {
        return User::query()->updateOrCreate(
            ['email' => $attributes['email']],
            $attributes
        );
    }
}
