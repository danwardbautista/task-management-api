<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\SubtaskRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AuditLogger;
use App\Models\Task;

class SubtaskController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index($taskId)
    {
        try {
            // Find parent task but exclude soft delete
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            
            if (!$task) {
                return ApiResponse::error('Task not found or you do not have permission to access it.', null, 404);
            }

            // Get active subtasks only
            $subtasks = $task->subTasks()
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->orderBy('created_at', 'desc')
                ->get();

            $this->auditLogger->logSuccess('subtasks.index', ['parent_task_id' => $taskId, 'count' => $subtasks->count()]);

            return ApiResponse::success('Subtasks retrieved successfully', $subtasks);
            
        } catch (\Exception $e) {
            $this->auditLogger->logError('subtasks.index', $e, ['parent_task_id' => $taskId]);

            return ApiResponse::error('Task not found or unable to retrieve subtasks.', null, 404);
        }
    }

    public function show($taskId, $subtaskId)
    {
        try {
            // Verify parent task exists and user owns
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            // Verify subtask exists under this parent task
            $subtask = Task::where('id', $subtaskId)
                ->where('parent_task_id', $taskId)
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            
            if (!$task || !$subtask) {
                return ApiResponse::error('Subtask not found or you do not have permission to access it.', null, 404);
            }

            $this->auditLogger->logSuccess('subtasks.show', ['task_id' => $taskId, 'subtask_id' => $subtaskId]);

            return ApiResponse::success('Subtask retrieved successfully', $subtask);
            
        } catch (\Exception $e) {
            $this->auditLogger->logError('subtasks.show', $e, ['task_id' => $taskId, 'subtask_id' => $subtaskId]);

            return ApiResponse::error('The application encountered an error while retrieving the subtask. Please try again later.');
        }
    }

    public function store(SubtaskRequest $request, $taskId)
    {
        try {
            // Find parent task excluding soft deleted, ensure user ownership
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            
            if (!$task) {
                return ApiResponse::error('Task not found or you do not have permission to access it.', null, 404);
            }

            // Prevent creating subtask under another subtask 
            if ($task->is_sub_task) {
                return ApiResponse::error('Cannot create subtask under another subtask. Subtasks can only be created under main tasks.', null, 422);
            }

            $subtaskData = [
                'title'          => $request->input('title'),
                'content'        => $request->input('content'),
                'status'         => $request->input('status', 'to-do'),
                'is_sub_task'    => true,
                'parent_task_id' => $taskId,
            ];

            // Handle image upload if present
            if ($request->hasFile('task_image')) {
                $subtaskData['task_image'] = $request->file('task_image')->store('task_images', 'public');
            }

            // Create subtask with validated data, user_id auto set by model
            $subtask = Task::create($subtaskData);

            $this->auditLogger->logSuccess('subtasks.store', ['parent_task_id' => $taskId, 'subtask_id' => $subtask->id, 'title' => $subtask->title]);

            return ApiResponse::success('Subtask created successfully', $subtask, 201);

        } catch (\Exception $e) {
            $this->auditLogger->logError('subtasks.store', $e, ['parent_task_id' => $taskId]);

            return ApiResponse::error('The application encountered an error while creating the subtask. Please try again later.');
        }
    }

    public function update(SubtaskRequest $request, $taskId, $subtaskId)
    {
        try {
            // Verify parent task exists and user owns 
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
                
            // Verify subtask exists under this parent task
            $subtask = Task::where('id', $subtaskId)
                ->where('parent_task_id', $taskId)
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            
            if (!$task || !$subtask) {
                return ApiResponse::error('Subtask not found or you do not have permission to access it.', null, 404);
            }

            // Update subtask fields except user_id and parent_task_id to prevent ownership changes
            $updateData = [
                'title'   => $request->input('title', $subtask->title),
                'content' => $request->input('content', $subtask->content),
                'status'  => $request->input('status', $subtask->status),
            ];

            // Handle image upload or removal
            if ($request->hasFile('task_image')) {
                // Delete old image if exists
                if ($subtask->task_image) {
                    Storage::disk('public')->delete($subtask->task_image);
                }
                $updateData['task_image'] = $request->file('task_image')->store('task_images', 'public');
            } elseif ($request->has('task_image') && $request->input('task_image') === null) {
                // Delete file and set column to null
                if ($subtask->task_image) {
                    Storage::disk('public')->delete($subtask->task_image);
                }
                $updateData['task_image'] = null;
            }

            $subtask->update($updateData);

            // Check if parent task should auto complete when all subtasks are done
            $this->checkParentTaskCompletion($task);

            $updatedSubtask = $subtask->fresh();
            $this->auditLogger->logSuccess('subtasks.update', ['task_id' => $taskId, 'subtask_id' => $subtaskId, 'title' => $subtask->title]);

            return ApiResponse::success('Subtask updated successfully', $updatedSubtask);

        } catch (\Exception $e) {
            $this->auditLogger->logError('subtasks.update', $e, ['task_id' => $taskId, 'subtask_id' => $subtaskId]);

            return ApiResponse::error('The application encountered an error while updating the subtask. Please try again later.');
        }
    }

    public function destroy($taskId, $subtaskId)
    {
        try {
            // Verify parent task exists and user owns
            $task = Task::where('id', $taskId)
                ->where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            // Verify subtask exists under this parent task
            $subtask = Task::where('id', $subtaskId)
                ->where('parent_task_id', $taskId)
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->first();
            
            if (!$task || !$subtask) {
                return ApiResponse::error('Subtask not found or you do not have permission to access it.', null, 404);
            }

            // Soft delete
            $subtask->update([
                'deleted_at' => now(),
            ]);

            // Check if parent task should auto complete after subtask deletion
            $this->checkParentTaskCompletion($task);

            $this->auditLogger->logSuccess('subtasks.destroy', ['task_id' => $taskId, 'subtask_id' => $subtaskId, 'title' => $subtask->title]);

            return ApiResponse::success('Subtask moved to trash successfully');
        } catch (\Exception $e) {
            $this->auditLogger->logError('subtasks.destroy', $e, ['task_id' => $taskId, 'subtask_id' => $subtaskId]);

            return ApiResponse::error('The application encountered an error while deleting the subtask. Please try again later.');
        }
    }

    private function checkParentTaskCompletion(Task $task)
    {
        // Get all active subtasks for this parent task
        $activeSubtasks = $task->subTasks()
            ->whereNull('deleted_at')
            ->whereNull('permanent_delete_at')
            ->get();

        // Auto-complete parent task when all subtasks are done
        if ($activeSubtasks->count() > 0 && $activeSubtasks->every(fn($subtask) => $subtask->status === 'done')) {
            $task->update(['status' => 'done']);
            $this->auditLogger->logSuccess('task.auto_completed', ['task_id' => $task->id]);
        }
    }
}