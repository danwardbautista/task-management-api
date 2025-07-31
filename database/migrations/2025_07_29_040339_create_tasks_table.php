<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {

            // Tasks main columns
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('task_image')->nullable();
            $table->enum('status', ['to-do', 'in-progress', 'done'])->default('to-do');
            $table->enum('task_state', ['draft', 'published'])->nullable()->default('draft');
            $table->timestamps();

            // User reference stuff
            $table->unsignedBigInteger('user_id')->nullable(); // null allowed for deactivated users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Sub tasks columns 
            $table->boolean('is_sub_task')->default(false);
            $table->unsignedBigInteger('parent_task_id')->nullable();

            // Custom trash columns instead of soft deletes 
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('permanent_delete_at')->nullable();

            // Indexing for search
            $table->index('title');
            $table->index(['parent_task_id', 'is_sub_task']);
            $table->index('deleted_at');
            $table->index('permanent_delete_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
