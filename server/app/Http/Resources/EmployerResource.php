<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'company_name' => data_get($this->resource, 'company_name'),
            'company_summary' => data_get($this->resource, 'company_summary'),
            'logo' => [
                'disk' => data_get($this->resource, 'logo_disk'),
                'path' => data_get($this->resource, 'logo_path'),
                'original_name' => data_get($this->resource, 'logo_original_name'),
            ],
        ];
    }
}
