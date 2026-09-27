<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Exceptions\Invoice\InsufficientStockException;
use App\Exceptions\Invoice\ProductInactiveException;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_amount_overflow_is_rejected_without_stock_changes(): void
    {
        $product = $this->product('LIMIT', ['price' => '99999999.99', 'stock' => 10001]);
        try {
            $this->createInvoice([['product_id' => $product->id, 'quantity' => 10001]]);
            $this->fail('Expected amount validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items', $exception->errors());
        }
        $this->assertDatabaseCount('invoices', 0);
        $this->assertSame(10001, $product->fresh()->stock);
    }

    private function product(string $sku, array $attributes = []): Product
    {
        return Product::create($attributes + ['name' => $sku, 'sku' => $sku, 'price' => '0.10', 'stock' => 10]);
    }

    private function createInvoice(array $items): Invoice
    {
        return app(InvoiceService::class)->create([
            'customer_id' => Customer::create(['name' => 'Customer'])->id,
            'items' => $items,
            'total' => 1,
            'user_id' => 999,
        ], User::factory()->create());
    }

    public function test_creation_uses_server_prices_and_updates_stock(): void
    {
        $product = $this->product('ONE');
        $invoice = $this->createInvoice([['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 999]]);
        $this->assertSame('0.30', $invoice->total);
        $this->assertSame('0.10', $invoice->items->sole()->unit_price);
        $this->assertSame('0.30', $invoice->items->sole()->subtotal);
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertSame('INV-'.str_pad((string) $invoice->id, 6, '0', STR_PAD_LEFT), $invoice->invoice_number);
        $this->assertNotEquals(999, $invoice->user_id);
    }

    public function test_insufficient_stock_leaves_no_invoice_or_stock_changes(): void
    {
        $product = $this->product('ONE');
        try {
            $this->createInvoice([['product_id' => $product->id, 'quantity' => 11]]);
            $this->fail('Expected insufficient stock exception.');
        } catch (InsufficientStockException $exception) {
            $this->assertSame(10, $exception->availableStock);
        }
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('invoice_items', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_inactive_product_is_rejected(): void
    {
        $product = $this->product('ONE', ['is_active' => false]);
        $this->expectException(ProductInactiveException::class);
        $this->createInvoice([['product_id' => $product->id, 'quantity' => 1]]);
    }

    public function test_failure_after_first_stock_deduction_rolls_back_everything(): void
    {
        $first = $this->product('ONE');
        $second = $this->product('TWO');
        $event = 'eloquent.creating: '.InvoiceItem::class;
        Event::listen($event, function (InvoiceItem $item) use ($first, $second) {
            if ($item->product_id === $second->id) {
                $this->assertSame(8, $first->fresh()->stock);
                throw new RuntimeException('Simulated write failure');
            }
        });
        try {
            $this->createInvoice([
                ['product_id' => $first->id, 'quantity' => 2],
                ['product_id' => $second->id, 'quantity' => 2],
            ]);
            $this->fail('Expected write failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated write failure', $exception->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertSame(10, $first->fresh()->stock);
        $this->assertSame(10, $second->fresh()->stock);
        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('invoice_items', 0);
    }
}
