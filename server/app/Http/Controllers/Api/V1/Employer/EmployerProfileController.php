<?php

namespace App\Http\Controllers\Api\V1\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateEmployerProfileRequest;
use App\Http\Resources\EmployerProfileResource;
use App\Jobs\ProcessCompanyLogoUpload;
use App\Models\EmployerProfile;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployerProfileController extends Controller
{
    public function __construct(
        private readonly FileStorageService $storage,
    ) {}

    public function show(Request $request): EmployerProfileResource
    {
        /** @var User $user */
        $user = $request->user();
        $this->authorize('view', $user->employerProfile ?? EmployerProfile::class);

        return new EmployerProfileResource($user->employerProfile ?? new EmployerProfile(['user_id' => $user->id]));
    }

    public function update(UpdateEmployerProfileRequest $request): EmployerProfileResource
    {
        /** @var User $user */
        $user = $request->user();
        
        $profile = $user->employerProfile;
        if (! $profile) {
            $profile = new EmployerProfile(['user_id' => $user->id]);
        }

        $this->authorize('update', $profile);

        $validated = $request->validated();
        
        if (isset($validated['company_name'])) {
            $profile->company_name = $validated['company_name'];
        }
        
        if (isset($validated['company_summary'])) {
            $profile->company_summary = $validated['company_summary'];
        }

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $metadata = $this->storage->storeLogo($file, $user);

            // Delete old logo if it exists
            if ($profile->logo_disk && $profile->logo_path) {
                $this->storage->deleteFile($profile->logo_disk, $profile->logo_path);
            }

            $profile->logo_disk = $metadata['storage_disk'];
            $profile->logo_path = $metadata['storage_path'];
            $profile->logo_original_name = $metadata['original_name'];
            
            // Queue logo processing job
            ProcessCompanyLogoUpload::dispatch($profile)->onQueue('files');
        }

        $profile->save();

        return new EmployerProfileResource($profile);
    }

    public function destroyLogo(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->employerProfile;
        
        abort_unless($profile, 404);
        $this->authorize('update', $profile);

        if ($profile->logo_disk && $profile->logo_path) {
            $this->storage->deleteFile($profile->logo_disk, $profile->logo_path);
            
            $profile->logo_disk = null;
            $profile->logo_path = null;
            $profile->logo_original_name = null;
            $profile->save();
        }

        return response()->noContent();
    }
}
