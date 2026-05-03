<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Events\ApplicationStatusChanged;
use App\Models\Application;
use App\Models\User;
use App\Repositories\Contracts\ApplicationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function __construct(
        private readonly ApplicationRepositoryInterface $applications,
    ) {}

    public function listForEmployer(
        User $employer,
        int $perPage = 15,
        ?ApplicationStatus $status = null,
    ): LengthAwarePaginator {
        return $this->applications->paginateForEmployer($employer, $perPage, $status);
    }

    public function showForEmployer(User $employer, Application $application): Application
    {
        $application = $this->applications->findForEmployer($employer, $application);
        abort_unless($application, 403);

        return $application;
    }

    /**
     * @throws ValidationException
     */
    public function applyEmployerDecision(
        User $employer,
        Application $application,
        ApplicationStatus $status,
        ?string $decisionNote = null,
    ): Application {
        abort_unless($application->employer_id === $employer->id, 403);

        if (! $status->isEmployerDecision()) {
            throw ValidationException::withMessages([
                'status' => 'Employers may only set accepted or rejected decisions.',
            ]);
        }

        $updated = DB::transaction(function () use ($employer, $application, $status, $decisionNote): Application {
            $application = $this->applications->lock($application);
            abort_unless($application, 404);

            if (! in_array($application->status, [ApplicationStatus::Submitted, ApplicationStatus::UnderReview], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only submitted or under-review applications can be decided.',
                ]);
            }

            return $this->applications->update($application, [
                'status' => $status,
                'decided_by_user_id' => $employer->id,
                'decided_at' => now(),
                'decision_note' => $decisionNote,
            ]);
        });

        event(ApplicationStatusChanged::fromApplication($updated));

        return $updated;
    }
}
