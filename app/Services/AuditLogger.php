<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Simple audit trail logger service
 * Logs successful and failed actions with relevant info 
 */
class AuditLogger
{
    public function __construct(private Request $request) {}

    public function logSuccess(string $action, mixed $data = null): void
    {
        $logData = [
            'status' => 'success',
            'action' => $action,
            'user_id' => auth()->id() ?? 'guest', // guest for non authenticated request
            'ip' => $this->request->ip(),
            'method' => $this->request->method(),
            'url' => $this->request->fullUrl(),
            'timestamp' => now()->toISOString(),
        ];

        if ($data) {
            $logData['data'] = $data;
        }

        Log::info("Action completed: {$action}", $logData);
    }

    public function logError(string $action, \Exception $e, mixed $context = null): void
    {
        $logData = [
            'status' => 'error',
            'action' => $action,
            'user_id' => auth()->id() ?? 'guest',
            'error' => $e->getMessage(),
            'ip' => $this->request->ip(),
            'method' => $this->request->method(),
            'url' => $this->request->fullUrl(),
            'timestamp' => now()->toISOString(),
        ];

        if ($context) {
            $logData['context'] = $context;
        }

        Log::error("Action failed: {$action}", $logData);
    }
}