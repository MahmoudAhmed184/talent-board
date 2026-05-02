<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function listForEmployer(
        User $employer,
        int $perPage = 15,
        ?ApplicationStatus $status = null,
    ): LengthAwarePaginator {
        return Application::query()
            ->with(['candidate:id,name,email'])
            ->where('employer_id', $employer->id)
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->latest('submitted_at')
            ->latest('id')
            ->paginate($perPage);
    }

    public function showForEmployer(User $employer, Application $application): Application
    {
        abort_unless($application->employer_id === $employer->id, 403);

        return $application->loadMissing(['candidate:id,name,email']);
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

        return DB::transaction(function () use ($employer, $application, $status, $decisionNote): Application {
            $application = Application::query()
                ->where('id', $application->id)
                ->lockForUpdate()
                ->first();

            if (! in_array($application->status, [ApplicationStatus::Submitted, ApplicationStatus::UnderReview], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only submitted or under-review applications can be decided.',
                ]);
            }

            $application->update([
                'status' => $status,
                'decided_by_user_id' => $employer->id,
                'decided_at' => now(),
                'decision_note' => $decisionNote,
            ]);

            return $application->refresh()->loadMissing(['candidate:id,name,email']);
        });
    }
}
