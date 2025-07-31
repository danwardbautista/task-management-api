<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** 
 * TaskRequest handles validation for my TaskController functions
 */
class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreating = $this->isMethod('POST');
        // $isUpdating = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [

             // Search rules we need to validate paramters to avoid SQLi
            'search'     => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\s@.\-_+()]*$/',
            'sort_by'    => 'nullable|string|in:title,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page'   => 'nullable|integer|min:1|max:100',
            'status_filter' => 'nullable|string|in:to-do,in-progress,done',

            // Rules for Task controller
            'title'      => $isCreating ? 'required|string|max:100' : 'sometimes|required|string|max:100',
            'content'    => 'nullable|string',
            'task_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:4096',
            'status'     => 'sometimes|required|string|in:to-do,in-progress,done',
            'task_state' => 'sometimes|string|in:draft,published',
        ];
    }

    // Override default message, just remove to use default messages
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.max' => 'Task title cannot exceed 100 characters.',
            'task_image.image' => 'Attachment must be an image file.',
            'task_image.mimes' => 'Attachment must be a JPEG, JPG, PNG, GIF, or WebP format.',
            'task_image.max' => 'Attachment cannot exceed 4MB.',
            'status.in' => 'Status must be one of: to-do, in-progress, done.',
            'task_state.in' => 'Task state must be either draft or published.',
            'search.max' => 'Search term cannot exceed 100 characters.',
            'search.regex' => 'Search term contains invalid characters.',
            'sort_by.in' => 'Sort field must be either title or created_at.',
            'sort_order.in' => 'Sort order must be either asc or desc.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }
}