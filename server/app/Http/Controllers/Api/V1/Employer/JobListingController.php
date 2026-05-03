<?php

namespace App\Http\Controllers\Api\V1\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListEmployerJobListingsRequest;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use App\Models\User;
use App\Services\JobListingService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JobListingController extends Controller
{
    public function __construct(
        private readonly JobListingService $jobs,
    ) {}

    public function index(ListEmployerJobListingsRequest $request): mixed
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('viewAny', JobListing::class);
        $validated = $request->validated();

        return JobListingResource::collection(
            $this->jobs->listForEmployer(
                $user,
                $validated['status'] ?? null,
                $validated['per_page'] ?? 15,
            ),
        );
    }

    public function store(StoreJobListingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('create', JobListing::class);

        return (new JobListingResource($this->jobs->create($user, $request->validated())))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(JobListing $jobListing): JobListingResource
    {
        $this->authorize('view', $jobListing);

        return new JobListingResource($this->jobs->showForEmployer($jobListing));
    }

    public function update(UpdateJobListingRequest $request, JobListing $jobListing): JobListingResource
    {
        $this->authorize('update', $jobListing);

        return new JobListingResource(
            $this->jobs->update($jobListing, $request->validated()),
        );
    }

    public function destroy(JobListing $jobListing): Response
    {
        $this->authorize('delete', $jobListing);

        $this->jobs->delete($jobListing);

        return response()->noContent();
    }
}
