<?php

namespace App\Exceptions\Invoice;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class InsufficientStockException extends RuntimeException implements ShouldntReport
{
    use ApiResponse;

    public function __construct(
        public readonly int $productId,
        public readonly int $requestedQuantity,
        public readonly int $availableStock,
    ) {
        parent::__construct('Insufficient stock for the requested product.');
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->getMessage(), 409, [
            'product_id' => $this->productId,
            'requested_quantity' => $this->requestedQuantity,
            'available_stock' => $this->availableStock,
        ]);
    }
}
