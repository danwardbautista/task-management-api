<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Standarized API Response format
 */
class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);//decide later regarding http status code
    }

    public static function error(string $message, mixed $errors = null, int $statusCode = 500): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

}