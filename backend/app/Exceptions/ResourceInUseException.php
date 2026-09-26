<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ResourceInUseException extends RuntimeException
{
    use ApiResponse;

    public function render(): JsonResponse
    {
        return $this->errorResponse('This record cannot be deleted because it is referenced by invoices.', 409);
    }
}
