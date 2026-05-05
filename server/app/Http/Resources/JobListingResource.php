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
            'location_id' => $this->location_id,
            'location' => $this->whenLoaded('location', fn (): ?string => $this->location?->name),
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn (): ?string => $this->category?->name),
            'work_type' => $this->work_type,
            'experience_level' => $this->experience_level,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'approval_status' => $this->approval_status,
            'published_at' => $this->published_at?->toIso8601String(),
            'expires_at' => $this->application_deadline?->toIso8601String(),
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
