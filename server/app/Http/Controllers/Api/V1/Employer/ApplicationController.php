<?php

namespace App\Http\Controllers\Api\V1\Employer;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListEmployerApplicationsRequest;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Http\Resources\EmployerApplicationResource;
use App\Models\Application;
use App\Models\User;
use App\Services\ApplicationService;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applications,
    ) {}

    public function index(ListEmployerApplicationsRequest $request): mixed
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('viewAny', Application::class);

        $validated = $request->validated();

        $status = isset($validated['status'])
            ? ApplicationStatus::tryFrom($validated['status'])
            : null;
        $perPage = $validated['per_page'] ?? 15;

        return EmployerApplicationResource::collection(
            $this->applications->listForEmployer($user, $perPage, $status),
        );
    }

    public function show(ListEmployerApplicationsRequest $request, Application $application): EmployerApplicationResource
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('view', $application);

        return new EmployerApplicationResource(
            $this->applications->showForEmployer($user, $application),
        );
    }

    public function updateStatus(
        UpdateApplicationStatusRequest $request,
        Application $application,
    ): EmployerApplicationResource {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('updateStatus', $application);

        $payload = $request->validated();

        return new EmployerApplicationResource(
            $this->applications->applyEmployerDecision(
                $user,
                $application,
                ApplicationStatus::from($payload['status']),
                $payload['decision_note'] ?? null,
            ),
        );
    }
}
