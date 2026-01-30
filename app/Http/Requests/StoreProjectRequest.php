<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects')->ignore($this->route('project')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A project with this name already exists.',
            'name.max' => 'Project name must not exceed 255 characters.',
        ];
    }
}
