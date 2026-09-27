<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'return_number' => $this->return_number,
            'invoice_id' => $this->invoice_id,
            'returned_at' => $this->returned_at,
            'items' => ReturnItemResource::collection($this->whenLoaded('items')),
            'total' => $this->whenLoaded('items', fn () => $this->resource->totalAmount()),
            'created_at' => $this->created_at,
        ];
    }
}
