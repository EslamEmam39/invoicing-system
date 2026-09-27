<?php

namespace App\Exceptions\Auth;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InvalidCredentialsException extends RuntimeException
{
    use ApiResponse;

    public function __construct()
    {
        parent::__construct('The provided credentials are incorrect.');
    }

    public function render(Request $request): JsonResponse
    {
        return $this->errorResponse($this->getMessage(), 422);
    }
}
