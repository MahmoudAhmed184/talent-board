<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobListingRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'description' => ['sometimes', 'required', 'string', 'min:20'],
            'responsibilities' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'qualifications' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'location' => ['sometimes', 'required', 'string', 'max:120'],
            'category' => ['sometimes', 'required', 'string', 'max:120'],
            'work_type' => ['sometimes', 'required', 'string', Rule::in(['remote', 'on-site', 'hybrid'])],
            'experience_level' => ['sometimes', 'required', 'string', Rule::in(['junior', 'mid', 'senior', 'lead'])],
            'salary_min' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'salary_max' => ['sometimes', 'nullable', 'integer', 'min:0', 'gte:salary_min'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:today'],
        ];
    }
}
