<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListEmployerApplicationsRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(array_map(
                fn (ApplicationStatus $status): string => $status->value,
                ApplicationStatus::cases(),
            ))],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
