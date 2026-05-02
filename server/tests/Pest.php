<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function testUserRole(UserRole|string $role): UserRole
{
    return $role instanceof UserRole ? $role : UserRole::from($role);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function actingAsRole(UserRole|string $role, array $attributes = []): User
{
    $role = testUserRole($role);

    $factory = match ($role) {
        UserRole::Candidate => User::factory()->candidate(),
        UserRole::Employer => User::factory()->employer(),
        UserRole::Admin => User::factory()->admin(),
    };

    $user = $factory->create($attributes);

    Sanctum::actingAs($user, $role->abilities());

    return $user;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function actingAsCandidate(array $attributes = []): User
{
    return actingAsRole(UserRole::Candidate, $attributes);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function actingAsEmployer(array $attributes = []): User
{
    return actingAsRole(UserRole::Employer, $attributes);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function actingAsAdmin(array $attributes = []): User
{
    return actingAsRole(UserRole::Admin, $attributes);
}

/**
 * @param  array<string, mixed>  $data
 * @param  array<string, string>  $headers
 */
function roleScopedJson(
    string $method,
    string $uri,
    UserRole|string $role,
    array $data = [],
    array $headers = [],
): TestResponse {
    actingAsRole($role);

    return test()->json($method, $uri, $data, $headers);
}

function assertJsonApiPaginated(TestResponse $response): TestResponse
{
    $response->assertJsonStructure([
        'data',
        'links' => ['first', 'last', 'prev', 'next'],
        'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total',
        ],
    ]);

    return $response;
}
