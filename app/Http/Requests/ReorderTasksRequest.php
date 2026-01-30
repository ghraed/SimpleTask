<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderTasksRequest extends FormRequest
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
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', Rule::exists('tasks', 'id')],
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'task_ids.required' => 'No tasks to reorder.',
            'project_id.exists' => 'Invalid project context.',
        ];
    }
}
