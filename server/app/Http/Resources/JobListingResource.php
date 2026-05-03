<?php

namespace App\Http\Resources;

use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JobListing */
class JobListingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'responsibilities' => $this->responsibilities,
            'qualifications' => $this->qualifications,
            'location' => $this->location,
            'category' => $this->category,
            'work_type' => $this->work_type,
            'experience_level' => $this->experience_level,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'moderation_status' => $this->moderation_status,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'employer' => $this->whenLoaded('employer', fn (): array => [
                'id' => $this->employer?->id,
                'name' => $this->employer?->name,
                'company_name' => $this->employer?->employerProfile?->company_name,
                'logo' => [
                    'path' => $this->employer?->employerProfile?->logo_path,
                ],
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
