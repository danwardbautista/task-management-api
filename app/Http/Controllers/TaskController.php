<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AuditLogger;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(TaskRequest $request)
    {
        try {

            // Initial query where we exclude soft delete and sub tasks, filter by authenticated user
            $query = Task::query()
                ->where('user_id', auth()->id())
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
            $tasks = $query->with(['subTasks' => function($query) {
                $query->whereNull('deleted_at')->whereNull('permanent_delete_at');
            }])->paginate($perPage);

            $this->auditLogger->logSuccess('tasks.index', ['count' => $tasks->count()]);

            return ApiResponse::success('Tasks retrieved successfully', $tasks);
            
        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.index', $e);

            return ApiResponse::error('The application encountered an error while retrieving tasks. Please try again later.');
        }
    }

    public function show($taskId)
    {
        try {
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->with(['subTasks' => function($query) {
                    $query->whereNull('deleted_at')->whereNull('permanent_delete_at');
                }])
                ->first();
            
            if (!$task) {
                return ApiResponse::error('Task not found or you do not have permission to access it.', null, 404);
            }

            $this->auditLogger->logSuccess('tasks.show', ['task_id' => $task->id]);

            return ApiResponse::success('Task retrieved successfully', $task);
            
        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.show', $e, ['task_id' => $taskId]);

            return ApiResponse::error('Unable to retrieve task. Please try again later.');
        }
    }

    public function store(TaskRequest $request)
    {
        try {
            $taskData = [
                'title'      => $request->input('title'),
                'content'    => $request->input('content'),
                'status'     => $request->input('status', 'to-do'),
                'task_state' => $request->input('task_state', 'draft'),
            ];

            // Handle image upload request
            if ($request->hasFile('task_image')) {
                $taskData['task_image'] = $request->file('task_image')->store('task_images', 'public');
            }

            // Create task with validated data - user_id auto-set by model
            $task = Task::create($taskData);

            $this->auditLogger->logSuccess('tasks.store', ['task_id' => $task->id, 'title' => $task->title]);

            return ApiResponse::success('Task created successfully', $task, 201);

        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.store', $e);

            return ApiResponse::error('The application encountered an error while creating the task. Please try again later.');
        }
    }

    public function update(TaskRequest $request, $taskId)
    {
        try {
            $task = Task::where('id', $taskId)->where('user_id', auth()->id())->first();
            
            if (!$task) {
                return ApiResponse::error('Task not found or you do not have permission to access it.', null, 404);
            }

            // Update task fields except user_id to prevent ownership changes
            $updateData = [
                'title'   => $request->input('title', $task->title),
                'content' => $request->input('content', $task->content),
                'status'  => $request->input('status', $task->status),
            ];

            // Handle image upload or removal
            if ($request->hasFile('task_image')) {
                // Delete old image if exists
                if ($task->task_image) {
                    Storage::disk('public')->delete($task->task_image);
                }
                $updateData['task_image'] = $request->file('task_image')->store('task_images', 'public');
            } elseif ($request->has('task_image') && $request->input('task_image') === null) {
                // Delete file and set column to null
                if ($task->task_image) {
                    Storage::disk('public')->delete($task->task_image);
                }
                $updateData['task_image'] = null;
            }

            // Only allow task_state updates for main tasks
            if (!$task->is_sub_task && $request->has('task_state')) {
                $updateData['task_state'] = $request->input('task_state');
            }

            $task->update($updateData);

            $updatedTask = $task->fresh();
            $this->auditLogger->logSuccess('tasks.update', ['task_id' => $task->id, 'title' => $task->title]);

            return ApiResponse::success('Task updated successfully', $updatedTask);

        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.update', $e, ['task_id' => $taskId]);

            return ApiResponse::error('Unable to update task. Please try again later.');
        }
    }

    public function destroy($taskId)
    {
        try {
            $task = Task::where('id', $taskId)->where('user_id', auth()->id())->first();
            
            if (!$task) {
                return ApiResponse::error('Task not found or you do not have permission to access it.', null, 404);
            }

            $deletedAt = now();

            // Cascade delete children
            $subtasksCount = $task->subTasks()->whereNull('deleted_at')->count();
            if ($subtasksCount > 0) {
                $task->subTasks()->whereNull('deleted_at')->update([
                    'deleted_at' => $deletedAt,
                ]);
            }

            // Delete the parent task
            $task->update([
                'deleted_at' => $deletedAt,
            ]);

            $this->auditLogger->logSuccess('tasks.destroy', [
                'task_id' => $task->id, 
                'title' => $task->title,
                'subtasks_deleted' => $subtasksCount
            ]);

            $message = $subtasksCount > 0 
                ? "Task and {$subtasksCount} subtasks moved to trash successfully"
                : 'Task moved to trash successfully';

            return ApiResponse::success($message);
        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.destroy', $e, ['task_id' => $taskId]);

            return ApiResponse::error('Unable to delete task. Please try again later.');
        }
    }

    public function trashed()
    {
        try {
            $query = Task::query()
                ->where('user_id', auth()->id())
                ->whereNotNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->where('is_sub_task', false)
                ->orderBy('deleted_at', 'desc');

            $trashedTasks = $query->with(['subTasks' => function($query) {
                $query->whereNotNull('deleted_at')->whereNull('permanent_delete_at');
            }])->paginate(10);

            $this->auditLogger->logSuccess('tasks.trashed', ['count' => $trashedTasks->count()]);

            return ApiResponse::success('Trashed tasks retrieved successfully', $trashedTasks);

        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.trashed', $e);

            return ApiResponse::error('Unable to retrieve trashed tasks. Please try again later.');
        }
    }

    public function restore($taskId)
    {
        try {
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->whereNotNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            
            if (!$task) {
                return ApiResponse::error('Trashed task not found or you do not have permission to access it.', null, 404);
            }

            // Cascade restore: also restore all related subtasks that were deleted
            $subtasksCount = $task->subTasks()
                ->whereNotNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->count();
            
            if ($subtasksCount > 0) {
                $task->subTasks()
                    ->whereNotNull('deleted_at')
                    ->whereNull('permanent_delete_at')
                    ->update(['deleted_at' => null]);
            }

            // Restore the parent task
            $task->update([
                'deleted_at' => null,
            ]);

            $this->auditLogger->logSuccess('tasks.restore', [
                'task_id' => $task->id, 
                'title' => $task->title,
                'subtasks_restored' => $subtasksCount
            ]);

            $message = $subtasksCount > 0 
                ? "Task and {$subtasksCount} subtasks restored successfully"
                : 'Task restored successfully';

            return ApiResponse::success($message, $task);

        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.restore', $e, ['task_id' => $taskId]);

            return ApiResponse::error('Unable to restore task. Please try again later.');
        }
    }

    public function forceDelete($taskId)
    {
        try {
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->whereNotNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            
            if (!$task) {
                return ApiResponse::error('Trashed task not found or you do not have permission to access it.', null, 404);
            }

            // Get subtasks to delete and count them
            $subtasksToDelete = $task->subTasks()->whereNotNull('deleted_at')->whereNull('permanent_delete_at')->get();
            $subtasksCount = $subtasksToDelete->count();

            // Delete images from all subtasks
            foreach ($subtasksToDelete as $subtask) {
                if ($subtask->task_image) {
                    Storage::disk('public')->delete($subtask->task_image);
                }
            }

            // Also permanently delete all subtasks
            $task->subTasks()->whereNotNull('deleted_at')->update([
                'permanent_delete_at' => now(),
            ]);

            // Delete task image if exists
            if ($task->task_image) {
                Storage::disk('public')->delete($task->task_image);
            }

            $task->update([
                'permanent_delete_at' => now(),
            ]);

            $this->auditLogger->logSuccess('tasks.force_delete', [
                'task_id' => $task->id, 
                'title' => $task->title,
                'subtasks_permanently_deleted' => $subtasksCount
            ]);

            $message = $subtasksCount > 0 
                ? "Task and {$subtasksCount} subtasks permanently deleted successfully"
                : 'Task permanently deleted successfully';

            return ApiResponse::success($message);

        } catch (\Exception $e) {
            $this->auditLogger->logError('tasks.force_delete', $e, ['task_id' => $taskId]);

            return ApiResponse::error('Unable to permanently delete task. Please try again later.');
        }
    }
}
