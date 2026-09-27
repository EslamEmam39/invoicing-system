<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Exceptions\InsufficientStockException;
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
                $invoice->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
                $product->decrement('stock', $quantity);
            }

            $invoice->update([
                'total' => $total,
                'invoice_number' => 'INV-' . str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT),
            ]);

            return $invoice->load(['customer', 'items.product']);
        }, 3);
    }


}
