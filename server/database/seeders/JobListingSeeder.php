<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\EmployerProfile;
use App\Models\JobListing;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class JobListingSeeder extends Seeder
{
    private const SourceName = 'Himalayas';

    private const SourceUrl = 'https://himalayas.app/jobs/api';

    private const PageSize = 20;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $targetCount = $this->targetCount();
        $jobs = $this->fetchRemoteJobs($targetCount);

        if ($jobs === []) {
            $jobs = $this->fallbackJobs($targetCount);
        }

        foreach (array_slice($jobs, 0, $targetCount) as $job) {
            $this->seedJobListing($job);
        }
    }

    private function targetCount(): int
    {
        return max(1, min((int) config('seeders.job_listings.limit', env('JOB_SEEDER_LIMIT', 120)), 500));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchRemoteJobs(int $targetCount): array
    {
        $jobs = [];

        for ($offset = 0; count($jobs) < $targetCount; $offset += self::PageSize) {
            $response = Http::timeout(15)
                ->retry(2, 300, throw: false)
                ->acceptJson()
                ->get(config('seeders.job_listings.source_url', self::SourceUrl), [
                    'limit' => self::PageSize,
                    'offset' => $offset,
                ]);

            if (! $response->successful()) {
                break;
            }

            $payloadJobs = data_get($response->json(), 'jobs', []);

            if (! is_array($payloadJobs) || $payloadJobs === []) {
                break;
            }

            $jobs = array_merge($jobs, $payloadJobs);
        }

        return $jobs;
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function seedJobListing(array $job): void
    {
        $companyName = $this->stringValue(data_get($job, 'companyName')) ?: 'Remote Hiring Team';
        $companySlug = Str::slug($this->stringValue(data_get($job, 'companySlug')) ?: $companyName);
        $employer = $this->employerForCompany($companyName, $companySlug, $this->stringValue(data_get($job, 'companyLogo')));
        $description = $this->descriptionForJob($job);
        $salaryMin = $this->nullableInteger(data_get($job, 'minSalary'));
        $salaryMax = $this->nullableInteger(data_get($job, 'maxSalary'));

        if ($salaryMin !== null && $salaryMax !== null && $salaryMin > $salaryMax) {
            [$salaryMin, $salaryMax] = [$salaryMax, $salaryMin];
        }

        JobListing::query()->updateOrCreate(
            [
                'employer_id' => $employer->id,
                'title' => Str::limit($this->stringValue(data_get($job, 'title')) ?: 'Remote role', 160, ''),
                'location' => $this->locationForJob($job),
            ],
            [
                'description' => $description,
                'responsibilities' => $this->responsibilitiesForJob($job),
                'qualifications' => $this->qualificationsForJob($job),
                'category' => Str::limit($this->categoryForJob($job), 120, ''),
                'work_type' => 'remote',
                'experience_level' => $this->experienceLevelForJob($job),
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,
                'moderation_status' => 'approved',
                'published_at' => $this->dateValue(data_get($job, 'pubDate')) ?? now(),
                'expires_at' => $this->dateValue(data_get($job, 'expiryDate')) ?? now()->addDays(60),
            ],
        );
    }

    private function employerForCompany(string $companyName, string $companySlug, ?string $logoUrl): User
    {
        $slug = $companySlug !== '' ? $companySlug : Str::slug($companyName);
        $email = "seed+{$slug}@talent-board.local";

        $employer = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => "{$companyName} Recruiting",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => UserRole::Employer,
            ],
        );

        EmployerProfile::query()->updateOrCreate(
            ['user_id' => $employer->id],
            [
                'company_name' => $companyName,
                'company_summary' => 'Imported from the '.self::SourceName.' remote jobs feed.',
                'logo_disk' => $logoUrl ? 'external' : null,
                'logo_path' => $logoUrl,
                'logo_original_name' => $logoUrl ? basename(parse_url($logoUrl, PHP_URL_PATH) ?: 'logo') : null,
            ],
        );

        return $employer;
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function descriptionForJob(array $job): string
    {
        $description = $this->htmlToText(
            $this->stringValue(data_get($job, 'description'))
                ?: $this->stringValue(data_get($job, 'excerpt')),
        );
        $sourceLink = $this->stringValue(data_get($job, 'applicationLink')) ?: 'https://himalayas.app/jobs';
        $currency = $this->stringValue(data_get($job, 'currency'));
        $salaryContext = $currency ? "\n\nSalary currency: {$currency}." : '';

        return Str::limit(
            trim($description).$salaryContext."\n\nSource: ".self::SourceName." {$sourceLink}",
            6000,
        );
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function responsibilitiesForJob(array $job): ?string
    {
        $excerpt = $this->htmlToText($this->stringValue(data_get($job, 'excerpt')));

        if ($excerpt === '') {
            return 'Own role outcomes, collaborate with a distributed team, and communicate progress clearly.';
        }

        return Str::limit($excerpt, 1200);
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function qualificationsForJob(array $job): ?string
    {
        $seniority = $this->stringList(data_get($job, 'seniority'));
        $employmentType = $this->stringValue(data_get($job, 'employmentType'));
        $parts = array_filter([
            $seniority === [] ? null : 'Seniority: '.implode(', ', $seniority),
            $employmentType ? "Employment type: {$employmentType}" : null,
        ]);

        return $parts === [] ? null : implode("\n", $parts);
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function locationForJob(array $job): string
    {
        $countries = collect(data_get($job, 'locationRestrictions', []))
            ->map(fn (mixed $country): string => $this->stringValue(data_get($country, 'name')))
            ->filter()
            ->values()
            ->all();

        $location = $countries === []
            ? 'Remote - Worldwide'
            : 'Remote - '.implode(', ', array_slice($countries, 0, 3));

        return Str::limit($location, 120, '');
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function categoryForJob(array $job): string
    {
        $categories = $this->stringList(data_get($job, 'categories'));
        $parentCategories = $this->stringList(data_get($job, 'parentCategories'));

        return $categories[0] ?? $parentCategories[0] ?? 'Remote Work';
    }

    /**
     * @param  array<string, mixed>  $job
     */
    private function experienceLevelForJob(array $job): string
    {
        $seniority = Str::lower(implode(' ', $this->stringList(data_get($job, 'seniority'))));

        return match (true) {
            str_contains($seniority, 'entry'), str_contains($seniority, 'junior') => 'junior',
            str_contains($seniority, 'senior') => 'senior',
            str_contains($seniority, 'manager'),
            str_contains($seniority, 'director'),
            str_contains($seniority, 'executive'),
            str_contains($seniority, 'lead'),
            str_contains($seniority, 'principal') => 'lead',
            default => 'mid',
        };
    }

    private function htmlToText(?string $value): string
    {
        if (! $value) {
            return '';
        }

        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?: '';

        return trim($text);
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * @return array<int, string>
     */
    private function stringList(mixed $value): array
    {
        if (is_string($value)) {
            return [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (mixed $item): string => $this->stringValue($item), $value),
        ));
    }

    private function nullableInteger(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (int) $value;

        return $number >= 0 ? $number : null;
    }

    private function dateValue(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fallbackJobs(int $targetCount): array
    {
        $templates = [
            ['Backend Platform Engineer', 'Laravel, queues, APIs, and SQL performance.', 'Engineering', 'Senior'],
            ['Frontend Vue Engineer', 'Vue, TypeScript, accessibility, and design systems.', 'Engineering', 'Mid-level'],
            ['Product Designer', 'Research, flows, prototypes, and product delivery.', 'Design', 'Senior'],
            ['Data Analyst', 'Dashboards, cohort analysis, and stakeholder reporting.', 'Data', 'Mid-level'],
            ['DevOps Engineer', 'Cloud infrastructure, CI/CD, monitoring, and incident response.', 'Engineering', 'Senior'],
            ['Customer Success Manager', 'Onboarding, account health, and customer outcomes.', 'Customer Success', 'Manager'],
            ['QA Automation Engineer', 'Regression suites, API tests, and release confidence.', 'Quality Assurance', 'Mid-level'],
            ['Security Engineer', 'Application security reviews, threat modeling, and controls.', 'Security', 'Senior'],
        ];

        $jobs = [];

        for ($index = 0; $index < $targetCount; $index++) {
            [$title, $summary, $category, $seniority] = $templates[$index % count($templates)];
            $companyNumber = intdiv($index, count($templates)) + 1;

            $jobs[] = [
                'title' => "{$title} {$companyNumber}",
                'excerpt' => $summary,
                'companyName' => "Seed Company {$companyNumber}",
                'companySlug' => "seed-company-{$companyNumber}",
                'companyLogo' => null,
                'employmentType' => 'Full Time',
                'minSalary' => 50000 + ($index * 500),
                'maxSalary' => 85000 + ($index * 750),
                'seniority' => [$seniority],
                'currency' => 'USD',
                'locationRestrictions' => [],
                'categories' => [$category],
                'parentCategories' => [$category],
                'description' => "{$summary} This seeded listing keeps local development useful when the remote API is unavailable.",
                'pubDate' => now()->subDays($index % 20)->toIso8601String(),
                'expiryDate' => now()->addDays(45 + ($index % 30))->toIso8601String(),
                'applicationLink' => 'https://himalayas.app/jobs',
            ];
        }

        return $jobs;
    }
}
