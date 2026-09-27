<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ProductInactiveException extends RuntimeException implements ShouldntReport
{
    use ApiResponse;

    public function __construct(public readonly int $productId)
    {
        parent::__construct('The requested product is inactive and cannot be sold.');
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->getMessage(), 409, [
            'product_id' => $this->productId,
        ]);
    }
}
