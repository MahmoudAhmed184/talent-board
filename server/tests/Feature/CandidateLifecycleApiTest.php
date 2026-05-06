<?php

use App\Enums\ApplicationStatus;
use App\Jobs\NotifyEmployerOfApplication;
use App\Jobs\ProcessResumeUpload;
use App\Models\Application;
use App\Models\CandidateProfile;
use App\Models\JobListing;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('candidate can view and update own profile with owned default resume', function () {
    $candidate = actingAsCandidate();
    CandidateProfile::factory()->forUser($candidate)->create([
        'summary' => 'Old summary',
    ]);
    $resume = Resume::factory()->forUser($candidate)->create();

    $this->getJson('/api/v1/candidate/profile')
        ->assertOk()
        ->assertJsonPath('data.summary', 'Old summary');

    $this->patchJson('/api/v1/candidate/profile', [
        'summary' => 'Updated summary for profile',
        'location_text' => 'Cairo, Egypt',
        'phone' => '+201000000000',
        'skills' => ['Laravel', 'Vue', 'Testing'],
        'default_resume_id' => $resume->id,
    ])
        ->assertOk()
        ->assertJsonPath('data.summary', 'Updated summary for profile')
        ->assertJsonPath('data.default_resume_id', $resume->id)
        ->assertJsonPath('data.default_resume.id', $resume->id);
});

test('candidate cannot set default resume that belongs to another user', function () {
    $candidate = actingAsCandidate();
    CandidateProfile::factory()->forUser($candidate)->create();
    $otherResume = Resume::factory()->create();

    $this->patchJson('/api/v1/candidate/profile', [
        'default_resume_id' => $otherResume->id,
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('default_resume_id');
});

test('candidate can upload resume and dispatches processing job', function () {
    Storage::fake('s3');
    Queue::fake();
    actingAsCandidate();

    $file = UploadedFile::fake()->create(
        'resume.pdf',
        200,
        'application/pdf'
    );

    $this->postJson('/api/v1/candidate/resumes', [
        'file' => $file,
    ])
        ->assertCreated()
        ->assertJsonPath('data.original_name', 'resume.pdf');

    $resume = Resume::query()->first();
    expect($resume)->not->toBeNull();

    Queue::assertPushed(ProcessResumeUpload::class, function (ProcessResumeUpload $job) use ($resume): bool {
        return $job->resume->is($resume);
    });
});

test('candidate can submit application and dispatches notification job', function () {
    Queue::fake();

    $candidate = actingAsCandidate();
    CandidateProfile::factory()->forUser($candidate)->create([
        'phone' => '+201234567890',
    ]);
    $resume = Resume::factory()->forUser($candidate)->create();

    $employer = User::factory()->employer()->create();
    $employer->employerProfile()->create([
        'company_name' => 'Acme Hiring',
    ]);

    $job = JobListing::factory()->approved()->create([
        'employer_user_id' => $employer->id,
        'title' => 'Backend Engineer',
    ]);

    $this->postJson("/api/v1/jobs/{$job->id}/applications", [
        'resume_id' => $resume->id,
        'cover_letter' => 'I am a great fit for this role.',
    ])
        ->assertOk()
        ->assertJsonPath('data.job_title', 'Backend Engineer')
        ->assertJsonPath('data.company_name', 'Acme Hiring')
        ->assertJsonPath('data.status', ApplicationStatus::Submitted->value);

    $application = Application::query()->first();
    expect($application)->not->toBeNull()
        ->and($application->candidate_id)->toBe($candidate->id)
        ->and($application->employer_id)->toBe($employer->id);

    Queue::assertPushed(NotifyEmployerOfApplication::class, function (NotifyEmployerOfApplication $job) use ($application): bool {
        return $job->application->is($application);
    });
});

test('candidate cannot submit duplicate application for same job', function () {
    $candidate = actingAsCandidate();
    $resume = Resume::factory()->forUser($candidate)->create();
    $job = JobListing::factory()->approved()->create();

    Application::factory()->create([
        'job_listing_id' => $job->id,
        'candidate_id' => $candidate->id,
        'employer_id' => $job->employer_user_id,
        'status' => ApplicationStatus::Submitted,
    ]);

    $this->postJson("/api/v1/jobs/{$job->id}/applications", [
        'resume_id' => $resume->id,
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'You have already applied to this job.');
});

test('candidate can list own application history and cancel eligible application', function () {
    $candidate = actingAsCandidate();

    $cancelable = Application::factory()->create([
        'candidate_id' => $candidate->id,
        'status' => ApplicationStatus::Submitted,
    ]);
    Application::factory()->create([
        'candidate_id' => $candidate->id,
        'status' => ApplicationStatus::Accepted,
    ]);
    Application::factory()->create([
        'status' => ApplicationStatus::Submitted,
    ]);

    $this->getJson('/api/v1/candidate/applications')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->deleteJson("/api/v1/candidate/applications/{$cancelable->id}")
        ->assertOk()
        ->assertJsonPath('data.status', ApplicationStatus::Cancelled->value);

    expect($cancelable->refresh()->status)->toBe(ApplicationStatus::Cancelled);
});

test('candidate cannot cancel finalized application', function () {
    $candidate = actingAsCandidate();
    $application = Application::factory()->create([
        'candidate_id' => $candidate->id,
        'status' => ApplicationStatus::Rejected,
    ]);

    $this->deleteJson("/api/v1/candidate/applications/{$application->id}")
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

