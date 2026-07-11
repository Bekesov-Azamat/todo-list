<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $attributes = [];

        if (is_string($this->input('title'))) {
            $attributes['title'] = trim($this->input('title'));
        }

        if (is_string($this->input('description'))) {
            $description = trim($this->input('description'));

            $attributes['description'] = $description === ''
                ? null
                : $description;
        }

        $this->merge($attributes);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
            'due_date' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d',
            ],
            'status' => [
                'required',
                Rule::enum(TaskStatus::class),
            ],
        ];
    }
}
