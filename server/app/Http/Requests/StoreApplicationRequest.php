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
            'resume_id' => ['required', 'exists:resumes,id'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
