<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function show($filename)
    {
        // Path definition
        $imagePath = 'task_images/' . $filename;
        
        // Check if user owns a task with this image
        $task = Task::where('task_image', $imagePath)->where('user_id', auth()->id())->first();
        
        if (!$task) {
            $this->auditLogger->logError('image.unauthorized_access', new \Exception('Unauthorized image access'), [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);
            abort(403);
        }
        
        // Check file existence
        if (!Storage::disk('public')->exists($imagePath)) {
            $this->auditLogger->logError('image.not_found', new \Exception('Image file not found'), [
                'filename' => $filename,
                'task_id' => $task->id
            ]);
            abort(404);
        }
        
        // Log successful access
        $this->auditLogger->logSuccess('image.served', [
            'filename' => $filename,
            'task_id' => $task->id,
            'user_id' => auth()->id()
        ]);
        
        // Serve and return the image file
        $fullPath = storage_path('app/public/' . $imagePath);
        return response()->file($fullPath);
    }
}