<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Testing\TestResponse;

test('candidate users can register', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'role' => UserRole::Candidate->value,
        'name' => 'New Candidate',
        'email' => 'candidate.new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('data.user.email', 'candidate.new@example.com')
        ->assertJsonPath('data.role', UserRole::Candidate->value)
        ->assertJsonPath('data.profile', null);

    $user = User::query()->where('email', 'candidate.new@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(UserRole::Candidate);
});

test('admin users cannot self register', function () {
    $this->postJson('/api/v1/auth/register', [
        'role' => UserRole::Admin->value,
        'name' => 'New Admin',
        'email' => 'admin.new@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('role');
});

test('users can login', function () {
    User::factory()
        ->employer()
        ->withPassword('password123')
        ->create(['email' => 'employer.login@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'employer.login@example.com',
        'password' => 'password123',
    ])
        ->assertOk()
        ->assertJsonPath('data.user.email', 'employer.login@example.com')
        ->assertJsonPath('data.role', UserRole::Employer->value);
});

test('current user context can be loaded with auth helper setup', function () {
    $user = actingAsCandidate(['email' => 'candidate.me@example.com']);

    $this->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.role', UserRole::Candidate->value);
});

test('role scoped request helper authenticates the requested role', function () {
    roleScopedJson('GET', '/api/v1/auth/me', UserRole::Admin)
        ->assertOk()
        ->assertJsonPath('data.role', UserRole::Admin->value);
});

test('users can logout', function () {
    actingAsEmployer();

    $this->postJson('/api/v1/auth/logout')->assertNoContent();
});

test('json api pagination helper asserts shared envelope shape', function () {
    $response = TestResponse::fromBaseResponse(response()->json([
        'data' => [],
        'links' => [
            'first' => 'http://localhost/api/v1/jobs?page=1',
            'last' => 'http://localhost/api/v1/jobs?page=1',
            'prev' => null,
            'next' => null,
        ],
        'meta' => [
            'current_page' => 1,
            'from' => null,
            'last_page' => 1,
            'path' => 'http://localhost/api/v1/jobs',
            'per_page' => 15,
            'to' => null,
            'total' => 0,
        ],
    ]));

    assertJsonApiPaginated($response)->assertOk();
});
