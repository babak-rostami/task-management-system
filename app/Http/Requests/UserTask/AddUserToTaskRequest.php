<?php

namespace App\Http\Requests\UserTask;

use Illuminate\Foundation\Http\FormRequest;

class AddUserToTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id']
        ];
    }

    public function messages()
    {
        return [
            'user_id.exists' => 'user id not exist in database'
        ];
    }
}
