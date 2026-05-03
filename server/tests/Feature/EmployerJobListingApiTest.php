<?php

use App\Enums\UserRole;
use App\Models\EmployerProfile;
use App\Models\JobListing;

function validJobPayload(array $overrides = []): array
{
    return array_replace([
        'title' => 'Senior Laravel Engineer',
        'description' => 'Build and maintain marketplace APIs with Laravel, queues, and Vue integrations.',
        'responsibilities' => 'Own API delivery, testing, and integration support.',
        'qualifications' => 'Strong Laravel, SQL, and pragmatic product delivery experience.',
        'location' => 'Cairo',
        'category' => 'Engineering',
        'work_type' => 'hybrid',
        'experience_level' => 'senior',
        'salary_min' => 60000,
        'salary_max' => 90000,
        'expires_at' => now()->addMonth()->toDateString(),
    ], $overrides);
}

test('employer registration creates related employer profile', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'role' => UserRole::Employer->value,
        'name' => 'Hiring Manager',
        'email' => 'employer.profile@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'company_name' => 'Acme Hiring',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.profile.company_name', 'Acme Hiring');

    expect(EmployerProfile::query()->where('company_name', 'Acme Hiring')->exists())->toBeTrue();
});

test('employer can create pending job listing through api', function () {
    $employer = actingAsEmployer();

    $this->postJson('/api/v1/employer/jobs', validJobPayload())
        ->assertCreated()
        ->assertJsonPath('data.title', 'Senior Laravel Engineer')
        ->assertJsonPath('data.approval_status', 'pending');

    $this->assertDatabaseHas('job_listings', [
        'employer_id' => $employer->id,
        'title' => 'Senior Laravel Engineer',
        'approval_status' => 'pending',
    ]);
});

test('listing owners can list view update and delete their jobs', function () {
    $employer = actingAsEmployer();
    $job = JobListing::factory()->create([
        'employer_id' => $employer->id,
        'title' => 'Original title',
        'approval_status' => 'approved',
        'published_at' => now()->subDay(),
    ]);

    $this->getJson('/api/v1/employer/jobs')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $job->id);

    $this->getJson("/api/v1/employer/jobs/{$job->id}")
        ->assertOk()
        ->assertJsonPath('data.title', 'Original title');

    $this->patchJson("/api/v1/employer/jobs/{$job->id}", validJobPayload([
        'title' => 'Updated title',
    ]))
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated title')
        ->assertJsonPath('data.approval_status', 'pending')
        ->assertJsonPath('data.published_at', null);

    $this->deleteJson("/api/v1/employer/jobs/{$job->id}")->assertNoContent();

    $this->assertDatabaseMissing('job_listings', ['id' => $job->id]);
});

test('non owners cannot access employer job listing mutations', function () {
    actingAsEmployer();
    $otherJob = JobListing::factory()->create();

    $this->getJson("/api/v1/employer/jobs/{$otherJob->id}")->assertForbidden();
    $this->patchJson("/api/v1/employer/jobs/{$otherJob->id}", validJobPayload())->assertForbidden();
    $this->deleteJson("/api/v1/employer/jobs/{$otherJob->id}")->assertForbidden();
});
