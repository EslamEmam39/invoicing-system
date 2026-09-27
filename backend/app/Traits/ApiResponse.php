<?php

namespace App\Traits;

use App\Http\Resources\PaginatedResourceCollection;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function paginatedResponse(PaginatedResourceCollection $collection): JsonResponse
    {
        return $collection->additional([
            'success' => true,
            'message' => 'Request completed successfully.',
        ])->response();
    }

    protected function successResponse(mixed $data = null, string $message = 'Request completed successfully.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => (object) $errors,
        ], $status);
    }
}
