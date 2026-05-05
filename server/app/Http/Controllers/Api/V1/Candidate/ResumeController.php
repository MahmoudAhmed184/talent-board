<?php

namespace App\Http\Controllers\Api\V1\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResumeRequest;
use App\Http\Resources\ResumeResource;
use App\Jobs\ProcessResumeUpload;
use App\Models\Resume;
use App\Models\User;
use App\Repositories\Contracts\ResumeRepositoryInterface;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ResumeController extends Controller
{
    public function __construct(
        private readonly ResumeRepositoryInterface $resumes,
        private readonly FileStorageService $storage,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('viewAny', Resume::class);

        $perPage = $request->query('per_page', 15);

        return ResumeResource::collection(
            $this->resumes->paginateForUser($user, (int) $perPage)
        );
    }

    public function store(StoreResumeRequest $request): ResumeResource
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('create', Resume::class);

        if ($user->resumes()->count() >= 3) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['You can only have up to 3 resumes.'],
            ]);
        }

        $file = $request->file('file');
        $metadata = $this->storage->storeResume($file, $user);

        $resume = $this->resumes->create($user, $metadata);

        ProcessResumeUpload::dispatch($resume)->onQueue('files');

        return new ResumeResource($resume);
    }

    public function destroy(Request $request, Resume $resume): Response
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('delete', $resume);

        $this->storage->deleteFile($resume->storage_disk, $resume->storage_path);
        $this->resumes->delete($resume);

        return response()->noContent();
    }
}
