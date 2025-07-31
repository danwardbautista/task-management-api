<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating test task data
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(2),
            'status' => $this->faker->randomElement(['to-do', 'in-progress', 'done']),
            'task_state' => $this->faker->randomElement(['draft', 'published']),
            'user_id' => 1, // assign to user 1 test
            'is_sub_task' => false,
            'parent_task_id' => null,
            'task_image' => null,
            'deleted_at' => null,
            'permanent_delete_at' => null,
        ];
    }

     public function subtask($parentTaskId = null): static
    {
        return $this->state(fn () => [
            'is_sub_task'     => true,
            'parent_task_id'  => $parentTaskId,
            'task_state'      => null, // optional override if subtasks inherit
        ]);
    }

    //untested stuff just need to fast track
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_state' => 'draft',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'task_state' => 'published',
        ]);
    }

    public function status($status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }

    public function forUser($userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}