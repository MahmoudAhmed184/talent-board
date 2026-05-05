<?php

namespace Database\Seeders;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResumeSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = User::where('role', 'candidate')->get();

        foreach ($candidates as $candidate) {
            // Give each candidate 1-3 resumes
            $resumes = Resume::factory()
                ->count(fake()->numberBetween(1, 3))
                ->forUser($candidate)
                ->create();

            // Set the first one as default
            $candidate->candidateProfile()->update([
                'default_resume_id' => $resumes->first()->id,
            ]);
        }
    }
}
