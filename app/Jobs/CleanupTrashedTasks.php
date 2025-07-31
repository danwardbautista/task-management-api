<?php

namespace App\Jobs;

use App\Models\Task;
use App\Services\AuditLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupTrashedTasks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes timeout
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $days = 30
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(AuditLogger $auditLogger): void
    {
        $cutoffDate = now()->subDays($this->days);

        Log::info("Starting cleanup of tasks trashed more than {$this->days} days ago", [
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s')
        ]);

        // Find tasks that are soft deleted and older than the cutoff date
        $trashedTasks = Task::whereNotNull('deleted_at')
            ->whereNull('permanent_delete_at')
            ->where('deleted_at', '<=', $cutoffDate)
            ->get();

        if ($trashedTasks->isEmpty()) {
            Log::info('No tasks found to cleanup.');
            return;
        }

        Log::info("Found {$trashedTasks->count()} tasks to permanently delete.");

        $cleanedCount = 0;
        $failedCount = 0;

        foreach ($trashedTasks as $task) {
            try {
                // Delete associated images
                if ($task->task_image) {
                    Storage::disk('public')->delete($task->task_image);
                }

                // Permanently delete all associated subtasks first
                $subtasksDeleted = Task::where('parent_task_id', $task->id)
                    ->whereNotNull('deleted_at')
                    ->whereNull('permanent_delete_at')
                    ->get();

                $subtasksCount = $subtasksDeleted->count();

                foreach ($subtasksDeleted as $subtask) {
                    if ($subtask->task_image) {
                        Storage::disk('public')->delete($subtask->task_image);
                    }
                    $subtask->update(['permanent_delete_at' => now()]);
                }

                // Mark the main task as permanently deleted
                $task->update(['permanent_delete_at' => now()]);

                $auditLogger->logSuccess('tasks.auto_cleanup', [
                    'task_id' => $task->id,
                    'title' => $task->title,
                    'deleted_at' => $task->deleted_at,
                    'days_in_trash' => $task->deleted_at->diffInDays(now()),
                    'subtasks_permanently_deleted' => $subtasksCount
                ]);

                $cleanedCount++;

            } catch (\Exception $e) {
                $failedCount++;
                
                $auditLogger->logError('tasks.auto_cleanup', $e, [
                    'task_id' => $task->id,
                    'title' => $task->title
                ]);

                Log::error("Failed to delete task: {$task->title} (ID: {$task->id})", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("Cleanup job completed", [
            'successfully_deleted' => $cleanedCount,
            'failed_to_delete' => $failedCount,
            'total_processed' => $trashedTasks->count()
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CleanupTrashedTasks job failed', [
            'error' => $exception->getMessage(),
            'days' => $this->days
        ]);
    }
}
