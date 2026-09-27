<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Exceptions\Invoice\InsufficientStockException;
use App\Exceptions\Invoice\InvoiceCancellationException;
use App\Exceptions\Invoice\ProductInactiveException;
use App\Models\Invoice;
use App\Models\User;
use App\Repositories\InvoiceItemRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\ProductRepository;
use App\Support\DatabaseLimits;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepository $repository,
        private readonly ProductRepository $products,
        private readonly InvoiceItemRepository $invoiceItems,
    ) {}

    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($user, $perPage);
    }

    public function show(Invoice $invoice): Invoice
    {
        return $this->repository->loadDetails($invoice);
    }

    public function cancel(Invoice $invoice): Invoice
    {
        return $this->repository->transaction(function () use ($invoice): Invoice {
            // Re-read under lock: route binding may contain stale status.
            $lockedInvoice = $this->repository->lockById($invoice->getKey());

            if ($lockedInvoice->status !== InvoiceStatus::Issued) {
                throw new InvoiceCancellationException('This invoice has already been cancelled.');
            }

            if ($this->repository->hasReturns($lockedInvoice)) {
                throw new InvoiceCancellationException('An invoice with returns cannot be cancelled.');
            }

            $items = $this->invoiceItems->items($lockedInvoice);
            $products = $this->products->lockByIds($items->pluck('product_id'));

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if (! $product || $product->stock > DatabaseLimits::MAX_STOCK - $item->quantity) {
                    throw new InvoiceCancellationException('The invoice stock cannot be restored within the supported limits.');
                }
            }

            foreach ($items as $item) {
                $this->products->increaseStock($products->get($item->product_id), $item->quantity);
            }

            $this->repository->update($lockedInvoice, ['status' => InvoiceStatus::Cancelled]);

            return $this->repository->loadDetails($lockedInvoice);
        });
    }

    public function create(array $data, User $user): Invoice
    {
        return $this->repository->transaction(function () use ($data, $user): Invoice {
            $items = collect($data['items']);
            $productIds = $items->pluck('product_id')->map(fn ($id) => (int) $id);

            if ($items->isEmpty() || $productIds->unique()->count() !== $items->count()) {
                throw ValidationException::withMessages(['items' => 'Provide at least one item and do not repeat products.']);
            }

            $products = $this->products->lockByIds($productIds);
            $lines = [];
            $total = '0.00';

            foreach ($items as $index => $item) {
                $product = $products->get($item['product_id']);
                if (! $product) {
                    throw ValidationException::withMessages(["items.$index.product_id" => 'The selected product no longer exists.']);
                }

                if (! $product->is_active) {
                    throw new ProductInactiveException($product->id);
                }

                $quantity = (int) $item['quantity'];
                if ($quantity < 1) {
                    throw ValidationException::withMessages(["items.$index.quantity" => 'Quantity must be at least one.']);
                }

                if ($product->stock < $quantity) {
                    throw new InsufficientStockException($product->id, $quantity, $product->stock);
                }

                $subtotal = bcmul($product->price, (string) $quantity, 2);
                $total = bcadd($total, $subtotal, 2);
                if (bccomp($total, DatabaseLimits::MAX_AMOUNT, 2) > 0) {
                    throw ValidationException::withMessages(['items' => 'The invoice total exceeds the supported amount.']);
                }
                $lines[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
            }

            $invoice = $this->repository->create([
                'invoice_number' => 'PENDING-'.Str::uuid(),
                'customer_id' => $data['customer_id'],
                'user_id' => $user->getKey(),
                'status' => InvoiceStatus::Issued,
                'issued_at' => now(),
            ]);

            foreach ($lines as $preparedLine) {
                $product = $preparedLine['product'];
                $quantity = $preparedLine['quantity'];
                $this->invoiceItems->createItem($invoice, [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $preparedLine['subtotal'],
                ]);
                $this->products->decreaseStock($product, $quantity);
            }

            $this->repository->update($invoice, ['total' => $total, 'invoice_number' => 'INV-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT)]);

            return $this->repository->loadDetails($invoice);
        });
    }
}
