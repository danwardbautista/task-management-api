<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\TaskRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AuditLogger;
use App\Models\Task;

class TaskController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(TaskRequest $request)
    {
        try {

            // Initial query where we exclude soft delete and sub tasks
            $query = Task::query()
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->where('is_sub_task', false);

            // Simple search function for small dataset
            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }

            // Status filtering 
            if ($statusFilter = $request->input('status_filter')) {
                $query->where('status', $statusFilter); //to-do,in-progress,done
            }

            // Sort with default func
            if ($request->filled('sort_by')) {
                $sortBy = $request->input('sort_by');
                $sortOrder = $request->input('sort_order', 'asc');
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $perPage = $request->input('per_page', 10);
            $tasks = $query->paginate($perPage);

            $this->auditLogger->logSuccess('tasks.index', ['count' => $tasks->count()]);

            return ApiResponse::success('Tasks retrieved successfully', $tasks);
            
        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.index', $e);

            return ApiResponse::error('The application encountered an error while retrieving tasks. Please try again later.');
        }
    }

    public function store(TaskRequest $request)
    {
        try {
            // Create task with validated data - user_id auto-set by model
            $task = Task::create([
                'title'    => $request->input('title'),
                'content'  => $request->input('content'),
                'status'   => $request->input('status', 'to-do'),
            ]);

            $this->auditLogger->logSuccess('tasks.store', ['task_id' => $task->id, 'title' => $task->title]);

            return ApiResponse::success('Task created successfully', $task, 201);

        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.store', $e);

            return ApiResponse::error('The application encountered an error while creating the task. Please try again later.');
        }
    }

    public function update(TaskRequest $request, Task $task)
    {
        try {
            // Update task fields except user_id to prevent ownership changes
            $task->update([
                'title'    => $request->input('title', $task->title),
                'content'  => $request->input('content', $task->content),
                'status'   => $request->input('status', $task->status),
            ]);

            $updatedTask = $task->fresh();
            $this->auditLogger->logSuccess('tasks.update', ['task_id' => $task->id, 'title' => $task->title]);

            return ApiResponse::success('Task updated successfully', $updatedTask);

        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.update', $e, ['task_id' => $task->id]);

            return ApiResponse::error('The application encountered an error while updating the task. Please try again later.');
        }
    }

    public function destroy(Task $task)
    {
        try {
            $task->update([
                'deleted_at' => now(),
            ]);

            $this->auditLogger->logSuccess('tasks.destroy', ['task_id' => $task->id, 'title' => $task->title]);

            return ApiResponse::success('Task moved to trash successfully');
        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.destroy', $e, ['task_id' => $task->id]);

            return ApiResponse::error('The application encountered an error while deleting the task. Please try again later.');
        }
    }
}
