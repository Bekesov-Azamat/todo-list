<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $attributes = [];

        if (
            $this->has('title')
            && is_string($this->input('title'))
        ) {
            $attributes['title'] = trim($this->input('title'));
        }

        if (
            $this->has('description')
            && is_string($this->input('description'))
        ) {
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
                'sometimes',
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:5000',
            ],
            'is_completed' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->hasAny([
                    'title',
                    'description',
                    'is_completed',
                ])) {
                    $validator->errors()->add(
                        'task',
                        'At least one task field must be provided.',
                    );
                }
            },
        ];
    }
}
