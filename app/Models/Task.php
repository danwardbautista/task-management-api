<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id',
        'is_sub_task',
        'parent_task_id',
        'deleted_at',
        'permanent_delete_at',
    ];

    protected $casts = [
        'is_sub_task' => 'boolean',
        'deleted_at' => 'datetime',
        'permanent_delete_at' => 'datetime',
    ];

    // Auto set user_id to authenticated user when creating a task
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($task) {
            if (!$task->user_id) {
                $task->user_id = auth()->id();
            }
        });
    }

    // delete this if not needed later
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    // Progress calculation for main tasks only
    // Need to check this stuff better
    public function getProgressAttribute(): array
    {
        // Eager loaded relation check if available
        if ($this->relationLoaded('subTasks')) {
            $subtasks = $this->subTasks;
        } else {
            $subtasks = $this->subTasks()
                ->whereNull('deleted_at')
                ->whereNull('permanent_delete_at')
                ->get();
        }

        $total = $subtasks->count();
        $completed = $subtasks->where('status', 'done')->count();
        $percentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage
        ];
    }

    // Progress only for main tasks
    protected function getArrayableAppends()
    {
        $appends = parent::getArrayableAppends();
        
        if (!$this->is_sub_task) {
            $appends[] = 'progress';
        }
        
        return $appends;
    }
}
