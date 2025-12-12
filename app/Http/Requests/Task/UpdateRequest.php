<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            $this->title => strip_tags($this->title),
            $this->description => strip_tags($this->description),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'min:4'],
            'description' => ['nullable', 'string', 'min:10'],
            'due_at' => ['nullable', 'date'],
        ];
    }

    public function messages()
    {
        return [
            'title.string' => 'task title must be string',
            'title.min' => 'task title must be at least :min chars',

            'description.string' => 'task description must be string',
            'description.min' => 'task description must be at least :min chars',

            'due_at.date' => 'task due_at must be date format',
        ];
    }
}
