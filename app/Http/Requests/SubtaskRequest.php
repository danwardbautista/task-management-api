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
        $isUpdating = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $subtaskId = $this->route('subtask'); // Get subtask ID for update operations

        return [
            // Title must be unique across ALL tasks (main tasks nd subtasks) for the user
            // Exclude permanently deleted tasks from uniqueness check
            'title'      => ($isCreating || $isUpdating) ? (
                $isCreating 
                    ? 'required|string|max:100|unique:tasks,title,NULL,id,user_id,' . auth()->id() . ',permanent_delete_at,NULL'
                    : 'sometimes|required|string|max:100|unique:tasks,title,' . $subtaskId . ',id,user_id,' . auth()->id() . ',permanent_delete_at,NULL'
            ) : 'nullable|string|max:100',
            'content'    => 'required|string',
            'task_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:4096',
            'status'     => 'sometimes|required|string|in:to-do,in-progress,done',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Subtask title is required.',
            'title.max' => 'Subtask title cannot exceed 100 characters.',
            'title.unique' => 'A task or subtask with this title already exists.',
            'content.required' => 'Subtask content is required.',
            'task_image.image' => 'Attachment must be an image file.',
            'task_image.mimes' => 'Attachment must be a JPEG, JPG, PNG, GIF, or WebP format.',
            'task_image.max' => 'Attachment cannot exceed 4MB.',
            'status.in' => 'Status must be one of: to-do, in-progress, done.',
        ];
    }
}