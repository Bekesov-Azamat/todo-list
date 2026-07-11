<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

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
}
