<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListAdminJobListingsRequest;
use App\Http\Requests\ModerateJobListingRequest;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use App\Models\User;
use App\Services\AdminJobService;
use Illuminate\Http\Request;

class JobModerationController extends Controller
{
    public function __construct(
        private readonly AdminJobService $jobs,
    ) {}

    public function index(ListAdminJobListingsRequest $request): mixed
    {
        $validated = $request->validated();

        return JobListingResource::collection(
            $this->jobs->listAll(
                $validated['status'] ?? null,
                (int) ($validated['per_page'] ?? 15),
            ),
        );
    }

    public function pending(Request $request): mixed
    {
        return JobListingResource::collection(
            $this->jobs->listPending((int) $request->integer('per_page', 15)),
        );
    }

    public function approve(Request $request, JobListing $jobListing): JobListingResource
    {
        /** @var User $user */
        $user = $request->user();

        return new JobListingResource($this->jobs->approve($jobListing, $user));
    }

    public function reject(
        ModerateJobListingRequest $request,
        JobListing $jobListing,
    ): JobListingResource {
        /** @var User $user */
        $user = $request->user();

        return new JobListingResource(
            $this->jobs->reject(
                $jobListing,
                $user,
                $request->validated('rejected_reason'),
            ),
        );
    }
}
