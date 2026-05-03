<?php

use App\Models\EmployerProfile;
use App\Models\JobListing;
use Database\Seeders\JobListingSeeder;
use Illuminate\Support\Facades\Http;

test('job listing seeder imports jobs from the Himalayas API', function () {
    config(['seeders.job_listings.limit' => 2]);

    Http::fake([
        'himalayas.app/jobs/api*' => Http::response([
            'jobs' => [
                [
                    'title' => 'Senior Laravel Engineer',
                    'excerpt' => 'Build APIs for a remote product team.',
                    'companyName' => 'Remote API Co',
                    'companySlug' => 'remote-api-co',
                    'companyLogo' => 'https://example.com/logo.png',
                    'employmentType' => 'Full Time',
                    'minSalary' => 100000,
                    'maxSalary' => 150000,
                    'seniority' => ['Senior'],
                    'currency' => 'USD',
                    'locationRestrictions' => [
                        ['name' => 'United States'],
                    ],
                    'categories' => ['Engineering'],
                    'parentCategories' => ['Engineering'],
                    'description' => '<p>Own Laravel APIs and ship tested features.</p>',
                    'pubDate' => '2026-05-01T12:00:00Z',
                    'expiryDate' => '2026-08-01T12:00:00Z',
                    'applicationLink' => 'https://himalayas.app/jobs/example',
                ],
                [
                    'title' => 'Product Designer',
                    'excerpt' => 'Design accessible workflows.',
                    'companyName' => 'Design Remote Co',
                    'companySlug' => 'design-remote-co',
                    'companyLogo' => null,
                    'employmentType' => 'Contractor',
                    'minSalary' => null,
                    'maxSalary' => null,
                    'seniority' => ['Mid-level'],
                    'currency' => 'USD',
                    'locationRestrictions' => [],
                    'categories' => ['Design'],
                    'parentCategories' => ['Design'],
                    'description' => '<p>Lead research and design delivery.</p>',
                    'pubDate' => '2026-05-02T12:00:00Z',
                    'expiryDate' => '2026-08-02T12:00:00Z',
                    'applicationLink' => 'https://himalayas.app/jobs/design',
                ],
            ],
        ]),
    ]);

    $this->seed(JobListingSeeder::class);

    expect(JobListing::query()->count())->toBe(2)
        ->and(EmployerProfile::query()->where('company_name', 'Remote API Co')->exists())->toBeTrue();

    $job = JobListing::query()->where('title', 'Senior Laravel Engineer')->firstOrFail();

    expect($job->approval_status)->toBe('approved')
        ->and($job->work_type)->toBe('remote')
        ->and($job->experience_level)->toBe('senior')
        ->and($job->location)->toBe('Remote - United States')
        ->and($job->description)->toContain('Source: Himalayas https://himalayas.app/jobs/example');
});

test('job listing seeder falls back to generated listings when api fails', function () {
    config(['seeders.job_listings.limit' => 5]);

    Http::fake([
        'himalayas.app/jobs/api*' => Http::response([], 500),
    ]);

    $this->seed(JobListingSeeder::class);

    expect(JobListing::query()->count())->toBe(5)
        ->and(EmployerProfile::query()->count())->toBeGreaterThan(0);
});
