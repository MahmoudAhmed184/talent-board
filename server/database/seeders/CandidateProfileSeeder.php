<?php

namespace Database\Seeders;

use App\Models\CandidateProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class CandidateProfileSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = User::where('role', 'candidate')->doesntHave('candidateProfile')->get();

        foreach ($candidates as $candidate) {
            CandidateProfile::factory()->forUser($candidate)->create();
        }
    }
}
