<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'content',
        'task_image',
        'status',
        'task_state',
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
            
            // Set task_state to null for subtasks (they inherit from parent)
            if ($task->is_sub_task) {
                $task->task_state = null;
            }
        });
    }

    // Auto set user_id
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

    // Get inherited task_state for subtasks, basically they inherit from parent
    public function getTaskStateAttribute($value)
    {
        if ($this->is_sub_task && $this->parent_task_id) {
            return Task::where('id', $this->parent_task_id)->value('task_state');
        }
        
        return $value;
    }

    // Making sure subtaks cannot toggle their state
    public function toggleTaskState()
    {
        if ($this->is_sub_task) {
            return false;
        }
        
        $this->task_state = $this->task_state === 'draft' ? 'published' : 'draft';
        return $this->save();
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
