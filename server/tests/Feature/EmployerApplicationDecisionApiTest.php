<?php

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;

test('employer can list only owned applications', function () {
    $employer = actingAsEmployer();

    Application::factory()->count(2)->create([
        'employer_id' => $employer->id,
        'status' => ApplicationStatus::Submitted,
    ]);

    Application::factory()->create([
        'status' => ApplicationStatus::Submitted,
    ]);

    $this->getJson('/api/v1/employer/applications')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('employer can view application details they own', function () {
    $employer = actingAsEmployer();

    $application = Application::factory()->create([
        'employer_id' => $employer->id,
        'status' => ApplicationStatus::UnderReview,
    ]);

    $this->getJson("/api/v1/employer/applications/{$application->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $application->id)
        ->assertJsonPath('data.status', ApplicationStatus::UnderReview->value);
});

test('employer cannot view applications they do not own', function () {
    actingAsEmployer();
    $application = Application::factory()->create();

    $this->getJson("/api/v1/employer/applications/{$application->id}")
        ->assertForbidden();
});

test('employer can accept submitted application', function () {
    $employer = actingAsEmployer();
    $application = Application::factory()->create([
        'employer_id' => $employer->id,
        'status' => ApplicationStatus::Submitted,
        'decided_at' => null,
        'decided_by_user_id' => null,
    ]);

    $this->patchJson("/api/v1/employer/applications/{$application->id}/status", [
        'status' => ApplicationStatus::Accepted->value,
        'decision_note' => 'Strong role fit and communication.',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', ApplicationStatus::Accepted->value)
        ->assertJsonPath('data.decision.decided_by_user_id', $employer->id);

    expect($application->refresh()->status)->toBe(ApplicationStatus::Accepted)
        ->and($application->decided_by_user_id)->toBe($employer->id)
        ->and($application->decided_at)->not->toBeNull();
});

test('employer can reject submitted application', function () {
    $employer = actingAsEmployer();
    $application = Application::factory()->create([
        'employer_id' => $employer->id,
        'status' => ApplicationStatus::Submitted,
        'decided_at' => null,
        'decided_by_user_id' => null,
    ]);

    $this->patchJson("/api/v1/employer/applications/{$application->id}/status", [
        'status' => ApplicationStatus::Rejected->value,
        'decision_note' => 'Skills do not match the role requirements.',
    ])
        ->assertOk()
        ->assertJsonPath('data.status', ApplicationStatus::Rejected->value)
        ->assertJsonPath('data.decision.decided_by_user_id', $employer->id);

    expect($application->refresh()->status)->toBe(ApplicationStatus::Rejected);
});

test('employer cannot decide already finalized application', function () {
    $employer = actingAsEmployer();
    $application = Application::factory()->create([
        'employer_id' => $employer->id,
        'status' => ApplicationStatus::Rejected,
    ]);

    $this->patchJson("/api/v1/employer/applications/{$application->id}/status", [
        'status' => ApplicationStatus::Accepted->value,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

test('status payload must be accepted or rejected', function () {
    $employer = actingAsEmployer();
    $application = Application::factory()->create([
        'employer_id' => $employer->id,
        'status' => ApplicationStatus::Submitted,
    ]);

    $this->patchJson("/api/v1/employer/applications/{$application->id}/status", [
        'status' => ApplicationStatus::UnderReview->value,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

test('non employers cannot access employer application endpoints', function () {
    $candidate = User::factory()->candidate()->create();
    \Laravel\Sanctum\Sanctum::actingAs($candidate, $candidate->role->abilities());

    $this->getJson('/api/v1/employer/applications')->assertForbidden();
});
