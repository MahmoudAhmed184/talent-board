<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
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
            'submission_mode' => ['required', 'string', 'in:resume,contact'],
            'resume_id' => ['required_if:submission_mode,resume', 'exists:resumes,id'],
            'use_profile_contact' => ['required', 'boolean'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
