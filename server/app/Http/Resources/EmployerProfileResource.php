<?php

namespace App\Http\Resources;

use App\Models\EmployerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EmployerProfile */
class EmployerProfileResource extends JsonResource
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
            'company_name' => $this->company_name,
            'company_summary' => $this->company_summary,
            'logo' => $this->logo_original_name ? [
                'original_name' => $this->logo_original_name,
            ] : null,
        ];
    }
}
