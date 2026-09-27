<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvoiceCancellationException;
use App\Exceptions\ProductInactiveException;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvoiceService
{


    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Invoice::query()
            ->visibleTo($user)
            ->with(['customer', 'items.product'])
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function show(Invoice $invoice): Invoice
    {
        return $invoice->load(['customer', 'items.product']);
    }

    public function cancel(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice): Invoice {
            // Re-read under lock: route binding may contain stale status.
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());

            if ($lockedInvoice->status !== InvoiceStatus::Issued) {
                throw new InvoiceCancellationException('This invoice has already been cancelled.');
            }

            if ($lockedInvoice->returns()->exists()) {
                throw new InvoiceCancellationException('An invoice with returns cannot be cancelled.');
            }

            $items = $lockedInvoice->items()->orderBy('product_id')->get();
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if (! $product || $product->stock > 4294967295 - $item->quantity) {
                    throw new InvoiceCancellationException('The invoice stock cannot be restored within the supported limits.');
                }
            }

            foreach ($items as $item) {
                $products->get($item->product_id)->increment('stock', $item->quantity);
            }

            $lockedInvoice->status = InvoiceStatus::Cancelled;
            $lockedInvoice->save();

            return $lockedInvoice->load(['customer', 'items.product']);
        }, 3);
    }

    public function create(array $data, User $user): Invoice
    {
        return DB::transaction(function () use ($data, $user): Invoice {
            $items = collect($data['items']);
            $productIds = $items->pluck('product_id')->map(fn($id) => (int) $id);

            if ($items->isEmpty() || $productIds->unique()->count() !== $items->count()) {
                throw ValidationException::withMessages(['items' => 'Provide at least one item and do not repeat products.']);
            }

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $lines = [];
            $total = '0.00';

            foreach ($items as $index => $item) {
                $product = $products->get($item['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages(["items.$index.product_id" => 'The selected product no longer exists.']);
                }

                if (!$product->is_active) {
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
                $lines[] = [$product, $quantity, $subtotal];
            }

            $invoice = Invoice::create([
                'invoice_number' => 'PENDING-' . Str::uuid(),
                'customer_id' => $data['customer_id'],
                'user_id' => $user->getKey(),
                'status' => InvoiceStatus::Issued,
                'issued_at' => now(),
            ]);

            foreach ($lines as [$product, $quantity, $subtotal]) {
                $line = $invoice->items()->make([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);
                $line->subtotal = $subtotal;
                $line->save();
                $product->decrement('stock', $quantity);
            }

            $invoice->total = $total;
            $invoice->invoice_number = 'INV-' . str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT);
            $invoice->save();

            return $invoice->load(['customer', 'items.product']);
        }, 3);
    }


}
