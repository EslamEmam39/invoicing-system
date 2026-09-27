<?php

namespace App\Exceptions\Invoice;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class InvoiceCancellationException extends RuntimeException implements ShouldntReport
{
    use ApiResponse;

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->getMessage(), 409);
    }
}
