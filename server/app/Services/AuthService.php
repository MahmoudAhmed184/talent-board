<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\Contracts\EmployerProfileRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly EmployerProfileRepositoryInterface $employerProfiles,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string, role: string}  $attributes
     */
    public function register(array $attributes, Request $request): User
    {
        try {
            $role = UserRole::from($attributes['role']);

            if (! $role->canSelfRegister()) {
                throw ValidationException::withMessages([
                    'role' => 'Admin accounts must be provisioned by platform operators.',
                ]);
            }

            $user = $this->users->create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
                'role' => $role,
            ]);

            if ($role === UserRole::Employer) {
                $this->employerProfiles->createForUser($user, [
                    'company_name' => $attributes['company_name'],
                ]);
            }

            $this->startSession($user, $request);

            return $user->refresh()->loadMissing('employerProfile');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'email' => $attributes['email'] ?? 'unknown',
            ]);
            throw $e;
        }
    }

    /**
     * @param  array{email: string, password: string}  $credentials
     *
     * @throws ValidationException
     */
    public function login(array $credentials, Request $request): User
    {
        $user = $this->users->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $this->startSession($user, $request);

        return $user->refresh()->loadMissing('employerProfile');
    }

    public function logout(Request $request): void
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    /**
     * @throws AuthenticationException
     */
    public function currentUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        return $user->loadMissing('employerProfile');
    }

    private function startSession(User $user, Request $request): void
    {
        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
    }
}
