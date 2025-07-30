<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubtaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreating = $this->isMethod('POST');

        return [
            'title'   => $isCreating ? 'required|string|max:255' : 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'status'  => 'sometimes|required|string|in:to-do,in-progress,done',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Subtask title is required.',
            'title.max' => 'Subtask title cannot exceed 255 characters.',
            'status.in' => 'Status must be one of: to-do, in-progress, done.',
        ];
    }
}