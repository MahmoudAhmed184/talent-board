<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:20'],
            'responsibilities' => ['nullable', 'string', 'max:5000'],
            'qualifications' => ['nullable', 'string', 'max:5000'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'work_type' => ['required', 'string', Rule::in(['remote', 'on-site', 'hybrid'])],
            'experience_level' => ['required', 'string', Rule::in(['junior', 'mid', 'senior', 'lead'])],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ];
    }
}
