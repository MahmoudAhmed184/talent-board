<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            'Remote', 'New York', 'San Francisco', 'London', 'Berlin'
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate([
                'slug' => Str::slug($location)
            ], [
                'name' => $location
            ]);
        }
    }
}
