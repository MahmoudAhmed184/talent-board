<?php

namespace App\Http\Resources;

use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CandidateProfile */
class CandidateProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'summary' => $this->summary,
            'location_text' => $this->location_text,
            'phone' => $this->phone,
            'skills' => $this->skills ?? [],
            'default_resume_id' => $this->default_resume_id,
            'default_resume' => $this->whenLoaded('defaultResume', fn (): array => [
                'id' => $this->defaultResume?->id,
                'original_name' => $this->defaultResume?->original_name,
            ]),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
