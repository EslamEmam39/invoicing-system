<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Collection;

class InvoiceItemRepository
{
    public function items(Invoice $invoice): Collection
    {
        return $invoice->items()->orderBy('product_id')->get();
    }

    public function lockItems(Invoice $invoice, iterable $ids): Collection
    {
        return $invoice->items()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    public function returnedQuantity(InvoiceItem $item): int
    {
        return (int) $item->returnItems()->sum('quantity');
    }

    public function createItem(Invoice $invoice, array $data): void
    {
        $item = $invoice->items()->make();
        $item->forceFill($data)->save();
    }
}
