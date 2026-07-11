<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('search'))) {
            $this->merge([
                'search' => trim($this->input('search')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                Rule::in([
                    'all',
                    ...TaskStatus::values(),
                ]),
            ],
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],
            'sort' => [
                'sometimes',
                'string',
                Rule::in([
                    'newest',
                    'oldest',
                    'due_date_asc',
                    'due_date_desc',
                    'status_asc',
                    'status_desc',
                    'title_asc',
                    'title_desc',
                ]),
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:50',
            ],
            'page' => [
                'sometimes',
                'integer',
                'min:1',
            ],
        ];
    }
}
