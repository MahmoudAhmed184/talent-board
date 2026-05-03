<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListPublicJobListingsRequest;
use App\Http\Resources\JobListingResource;
use App\Models\JobListing;
use App\Services\PublicJobListingService;
use Illuminate\Support\Arr;

class JobListingController extends Controller
{
    public function __construct(
        private readonly PublicJobListingService $jobs,
    ) {}

    public function index(ListPublicJobListingsRequest $request): mixed
    {
        $validated = $request->validated();
        $paginator = $this->jobs->search($validated, (int) ($validated['per_page'] ?? 15));
        $paginator->appends(Arr::except($validated, ['page']));

        return JobListingResource::collection($paginator);
    }

    public function show(JobListing $jobListing): JobListingResource
    {
        return new JobListingResource($this->jobs->show($jobListing));
    }
}
