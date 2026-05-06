<?php

use App\Models\Application;
use App\Models\JobListing;

test('admin can list pending and all moderated jobs', function () {
    actingAsAdmin();

    JobListing::factory()->count(2)->create(['approval_status' => 'pending']);
    JobListing::factory()->approved()->create();

    $this->getJson('/api/v1/admin/jobs/pending')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->getJson('/api/v1/admin/jobs')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin can approve and reject job listings', function () {
    $admin = actingAsAdmin();
    $pending = JobListing::factory()->create(['approval_status' => 'pending']);

    $this->patchJson("/api/v1/admin/jobs/{$pending->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.approval_status', 'approved');

    expect($pending->refresh()->approval_status)->toBe('approved')
        ->and($pending->approved_by)->toBe($admin->id)
        ->and($pending->published_at)->not->toBeNull();

    $otherPending = JobListing::factory()->create(['approval_status' => 'pending']);

    $this->patchJson("/api/v1/admin/jobs/{$otherPending->id}/reject", [
        'rejected_reason' => 'Duplicate listing.',
    ])
        ->assertOk()
        ->assertJsonPath('data.approval_status', 'rejected')
        ->assertJsonPath('data.published_at', null);

    expect($otherPending->refresh()->rejected_reason)->toBe('Duplicate listing.');
});

test('admin cannot moderate jobs that are already finalized', function () {
    actingAsAdmin();

    $approved = JobListing::factory()->approved()->create();
    $rejected = JobListing::factory()->rejected()->create();

    $this->patchJson("/api/v1/admin/jobs/{$approved->id}/reject", [
        'rejected_reason' => 'Attempted reversal.',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('job');

    $this->patchJson("/api/v1/admin/jobs/{$rejected->id}/approve")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('job');
});

test('admin activity endpoint returns totals and recent records', function () {
    actingAsAdmin();

    JobListing::factory()->approved()->create();
    Application::factory()->create();

    $this->getJson('/api/v1/admin/activity')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'totals' => [
                    'users',
                    'candidates',
                    'employers',
                    'admins',
                    'jobs',
                    'jobs_pending',
                    'jobs_approved',
                    'jobs_rejected',
                    'applications',
                ],
                'recent_users',
                'recent_jobs',
                'recent_applications',
            ],
        ]);
});
