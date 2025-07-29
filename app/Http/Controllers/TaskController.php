<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\TaskRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Task;

class TaskController extends Controller
{
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

            return ApiResponse::success('Tasks retrieved successfully', $tasks); //decide later regarding http status code
            
        } catch (\Exception $e) {
            Log::error('TaskController@index failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('The application encountered an error while retrieving tasks. Please try again later.');
        }
    }

    public function store(TaskRequest $request)
    {
        try {
            // Create task with validated data
            $task = Task::create([
                'title'    => $request->input('title'),
                'content'  => $request->input('content'),
                'status'   => $request->input('status', 'to-do'),
                'user_id'  => $request->input('user_id'),
            ]);

            return ApiResponse::success('Task created successfully', $task, 201);

        } catch (\Exception $e) {
            Log::error('TaskController@store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('The application encountered an error while creating the task. Please try again later.');
        }
    }

    public function update(TaskRequest $request, Task $task)
    {
        try {
            $task->update([
                'title'    => $request->input('title', $task->title),
                'content'  => $request->input('content', $task->content),
                'status'   => $request->input('status', $task->status),
                'user_id'  => $request->input('user_id', $task->user_id),
            ]);

            return ApiResponse::success('Task updated successfully', $task->fresh());

        } catch (\Exception $e) {
            Log::error('TaskController@update failed', [
                'task_id' => $task->id, //id and title or just id, decie later
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('The application encountered an error while updating the task. Please try again later.');
        }
    }

    public function destroy(Task $task)
    {
        try {
            $task->update([
                'deleted_at' => now(),
            ]);

            return ApiResponse::success('Task moved to trash successfully');
        } catch (\Exception $e) {
            Log::error('TaskController@destroy failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('The application encountered an error while deleting the task. Please try again later.');
        }
    }
}
