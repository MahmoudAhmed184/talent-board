<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthenticatedUserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        return (new AuthenticatedUserResource(
            $this->auth->register($request->validated(), $request),
        ))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource(
            $this->auth->login($request->validated(), $request),
        );
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout($request);

        return response()->noContent();
    }

    public function me(Request $request): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource(
            $this->auth->currentUser($request),
        );
    }
}
