<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AuthSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(LocationSeeder::class);
        $this->call(JobListingSeeder::class);
        $this->call(CandidateProfileSeeder::class);
        $this->call(ResumeSeeder::class);

        $employer = \App\Models\User::where('email', 'employer@example.com')->first();
        if ($employer) {
            \App\Models\JobListing::factory()->count(8)->approved()->create([
                'employer_user_id' => $employer->id,
            ]);
        }

        $this->call(ApplicationSeeder::class);
    }
}
