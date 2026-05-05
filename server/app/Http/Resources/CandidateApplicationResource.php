<?php

namespace App\Http\Resources;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Application */
class CandidateApplicationResource extends JsonResource
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
                'employer' => [
                    'id' => $this->jobListing?->employer?->id,
                    'company_name' => $this->jobListing?->employer?->employerProfile?->company_name,
                ],
            ]),
            'status' => $this->status->value,
            'submission_mode' => $this->resume_path ? 'resume' : 'contact',
            'cover_letter' => $this->cover_letter,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'resume' => [
                'original_name' => $this->resume_original_name,
            ],
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'decision' => [
                'note' => $this->decision_note,
                'decided_at' => $this->decided_at?->toIso8601String(),
            ],
        ];
    }
}
