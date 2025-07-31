<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        // Create 20 main tasks
        $mainTasks = Task::factory()->count(20)->create();
        
        // Create 20 subtasks randomly assigned to main tasks
        for ($i = 0; $i < 20; $i++) {
            $randomParent = $mainTasks->random();
            Task::factory()->subtask($randomParent->id)->create();
        }
    }
}