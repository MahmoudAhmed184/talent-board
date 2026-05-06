<?php

use App\Jobs\NotifyEmployerOfApplication;
use App\Jobs\ProcessCompanyLogoUpload;
use App\Jobs\ProcessResumeUpload;
use App\Models\Application;
use App\Models\EmployerProfile;
use App\Models\Resume;
use App\Models\User;

test('resume processing job can run after upload metadata is stored', function () {
    $candidate = actingAsCandidate();
    $resume = Resume::factory()->forUser($candidate)->create();

    expect(fn () => (new ProcessResumeUpload($resume))->handle())->not->toThrow(Exception::class);

    $this->assertDatabaseHas('resumes', [
        'id' => $resume->id,
        'user_id' => $candidate->id,
    ]);
});

test('application notification job persists employer database notification', function () {
    $employer = User::factory()->employer()->create();
    $candidate = User::factory()->candidate()->create();
    $job = \App\Models\JobListing::factory()->create([
        'employer_user_id' => $employer->id,
    ]);
    $application = Application::factory()->create([
        'job_listing_id' => $job->id,
        'employer_id' => $employer->id,
        'candidate_id' => $candidate->id,
    ]);

    (new NotifyEmployerOfApplication($application))->handle(
        app(\App\Repositories\Contracts\NotificationRepositoryInterface::class)
    );

    $this->assertDatabaseHas('notifications', [
        'type' => 'App\Notifications\NewApplicationReceived',
        'notifiable_type' => User::class,
        'notifiable_id' => (string) $application->employer_id,
    ]);
});

test('company logo processing job can run for employer profile metadata', function () {
    $employer = User::factory()->employer()->create();
    $profile = EmployerProfile::query()->create([
        'user_id' => $employer->id,
        'company_name' => 'Acme Corp',
        'logo_disk' => 's3',
        'logo_path' => 'company-logos/acme.png',
        'logo_original_name' => 'acme.png',
    ]);

    expect(fn () => (new ProcessCompanyLogoUpload($profile))->handle())->not->toThrow(Exception::class);

    $this->assertDatabaseHas('employer_profiles', [
        'id' => $profile->id,
        'logo_disk' => 's3',
        'logo_path' => 'company-logos/acme.png',
    ]);
});

