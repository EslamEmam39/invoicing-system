<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Exceptions\SalesReturn\SalesReturnException;
use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Models\User;
use App\Repositories\InvoiceItemRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\ProductRepository;
use App\Repositories\SalesReturnRepository;
use App\Support\DatabaseLimits;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesReturnService
{
    public function __construct(
        private readonly SalesReturnRepository $repository,
        private readonly ProductRepository $products,
        private readonly InvoiceRepository $invoices,
        private readonly InvoiceItemRepository $invoiceItems,
    ) {}

    public function paginate(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($user, $perPage);
    }

    public function show(SalesReturn $salesReturn): SalesReturn
    {
        return $this->repository->loadDetails($salesReturn);
    }

    public function create(Invoice $invoice, array $data): SalesReturn
    {
        return $this->repository->transaction(function () use ($invoice, $data): SalesReturn {
            // Share the invoice lock with cancellation and all other return requests.
            $invoice = $this->invoices->lockById($invoice->id);
            if ($invoice->status !== InvoiceStatus::Issued) {
                throw new SalesReturnException('A cancelled invoice cannot be returned.');
            }

            $requested = collect($data['items']);
            $ids = $requested->pluck('invoice_item_id')->map(fn ($id) => (int) $id);
            if ($requested->isEmpty() || $ids->unique()->count() !== $requested->count()) {
                throw ValidationException::withMessages(['items' => 'Provide distinct invoice items.']);
            }

            $items = $this->invoiceItems->lockItems($invoice, $ids);
            $products = $this->products->lockByIds($items->pluck('product_id'));
            $lines = [];

            foreach ($requested as $index => $input) {
                $item = $items->get($input['invoice_item_id']);
                if (! $item) {
                    throw ValidationException::withMessages(["items.$index.invoice_item_id" => 'This item does not belong to the invoice.']);
                }
                $quantity = (int) $input['quantity'];
                if ($quantity < 1 || $quantity > ($item->quantity - $this->invoiceItems->returnedQuantity($item))) {
                    throw new SalesReturnException("The return quantity exceeds the remaining quantity for invoice item {$item->id}.");
                }
                $product = $products->get($item->product_id);

                if (! $product) {
                    throw ValidationException::withMessages(["items.$index.invoice_item_id" => 'The related product no longer exists.']);
                }
                if ($product->stock > DatabaseLimits::MAX_STOCK - $quantity) {
                    throw new SalesReturnException('The returned stock exceeds the supported limits.');
                }
                $subtotal = bcmul($item->unit_price, (string) $quantity, 2);
                if (bccomp($subtotal, DatabaseLimits::MAX_AMOUNT, 2) > 0) {
                    throw new SalesReturnException('The return subtotal exceeds the supported amount.');
                }
                $lines[] = ['item' => $item, 'product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
            }

            $return = $this->repository->create($invoice, [
                'return_number' => 'PENDING-'.Str::uuid(),
                'returned_at' => now(),
            ]);

            foreach ($lines as $preparedLine) {
                $item = $preparedLine['item'];
                $product = $preparedLine['product'];
                $quantity = $preparedLine['quantity'];
                $this->repository->createItem($return, [
                    'invoice_item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $preparedLine['subtotal'],
                ]);
                $this->products->increaseStock($product, $quantity);
            }

            $this->repository->update($return, ['return_number' => 'RET-'.str_pad((string) $return->id, 6, '0', STR_PAD_LEFT)]);

            return $this->repository->loadDetails($return);
        });
    }
}
