<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'summary' => ['nullable', 'string', 'max:2000'],
            'location_text' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'skills' => ['nullable', 'array', 'max:20'],
            'skills.*' => ['string', 'max:50'],
            'default_resume_id' => ['nullable', 'integer', 'exists:resumes,id'],
        ];
    }
}
