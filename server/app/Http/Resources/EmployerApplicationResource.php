<?php

namespace App\Http\Resources;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Application */
class EmployerApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_listing_id' => $this->job_listing_id,
            'job_listing' => $this->whenLoaded('jobListing', fn (): array => [
                'id' => $this->jobListing?->id,
                'title' => $this->jobListing?->title,
            ]),
            'status' => $this->status->value,
            'cover_letter' => $this->cover_letter,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'candidate' => [
                'id' => $this->candidate_id,
                'name' => $this->candidate?->name,
                'email' => $this->candidate?->email,
            ],
            'resume' => [
                'original_name' => $this->resume_original_name,
            ],
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'decision' => [
                'note' => $this->decision_note,
                'decided_at' => $this->decided_at?->toIso8601String(),
                'decided_by_user_id' => $this->decided_by_user_id,
            ],
        ];
    }
}
