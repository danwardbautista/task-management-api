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
            'content'   => 'nullable|string',
            'task_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:4096',
            'status'     => 'sometimes|required|string|in:to-do,in-progress,done',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Subtask title is required.',
            'title.max' => 'Subtask title cannot exceed 255 characters.',
            'task_image.image' => 'Attachment must be an image file.',
            'task_image.mimes' => 'Attachment must be a JPEG, JPG, PNG, GIF, or WebP format.',
            'task_image.max' => 'Attachment cannot exceed 4MB.',
            'status.in' => 'Status must be one of: to-do, in-progress, done.',
        ];
    }
}