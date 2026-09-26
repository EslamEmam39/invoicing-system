<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\ReturnStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicingModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_relationships_and_casts_work_with_persisted_records(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Test Customer']);
        $product = Product::create(['name' => 'Product', 'sku' => 'SKU-1', 'price' => '12.50']);
        $invoice = $customer->invoices()->create([
            'invoice_number' => 'INV-1',
            'user_id' => $user->id,
            'status' => InvoiceStatus::Issued,
            'total' => '25.00',
            'issued_at' => now(),
        ]);
        $item = $invoice->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => '12.50',
            'subtotal' => '25.00',
        ]);
        $this->assertSame(0, $item->returnedQuantity());
        $this->assertSame(2, $item->remainingQuantity());
        $return = $invoice->returns()->create([
            'return_number' => 'RET-1',
            'status' => ReturnStatus::Completed,
            'returned_at' => now(),
        ]);
        $returnItem = $return->items()->create([
            'invoice_item_id' => $item->id,
            'quantity' => 1,
            'unit_price' => '12.50',
            'subtotal' => '12.50',
        ]);

        $this->assertTrue($user->invoices->sole()->is($invoice));
        $this->assertTrue($customer->invoices->sole()->is($invoice));
        $this->assertTrue($product->invoiceItems->sole()->is($item));
        $this->assertTrue($invoice->customer->is($customer));
        $this->assertTrue($invoice->user->is($user));
        $this->assertTrue($invoice->items->sole()->is($item));
        $this->assertTrue($invoice->returns->sole()->is($return));
        $this->assertTrue($item->invoice->is($invoice));
        $this->assertTrue($item->product->is($product));
        $this->assertTrue($item->returnItems->sole()->is($returnItem));
        $this->assertTrue($return->invoice->is($invoice));
        $this->assertTrue($return->items->sole()->is($returnItem));
        $this->assertTrue($returnItem->salesReturn->is($return));
        $this->assertTrue($returnItem->invoiceItem->is($item));

        $this->assertSame(1, $item->returnedQuantity());
        $this->assertSame(1, $item->remainingQuantity());

        $secondReturn = $invoice->returns()->create([
            'return_number' => 'RET-2',
            'status' => ReturnStatus::Cancelled,
        ]);
        $secondReturn->items()->create([
            'invoice_item_id' => $item->id,
            'quantity' => 1,
            'unit_price' => '12.50',
            'subtotal' => '12.50',
        ]);

        $this->assertSame(1, $item->returnedQuantity());
        $this->assertSame(1, $item->remainingQuantity());

        $secondReturn->update(['status' => ReturnStatus::Completed]);

        $this->assertSame(2, $item->returnedQuantity());
        $this->assertSame(0, $item->remainingQuantity());

        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
        $this->assertSame(ReturnStatus::Completed, $return->fresh()->status);
        $this->assertSame('25.00', $invoice->fresh()->total);
        $this->assertSame('12.50', $product->fresh()->price);
        $this->assertSame('12.50', $item->fresh()->unit_price);
        $this->assertSame('25.00', $item->fresh()->subtotal);
        $this->assertSame('12.50', $returnItem->fresh()->unit_price);
        $this->assertSame('12.50', $returnItem->fresh()->subtotal);
        $this->assertTrue($customer->fresh()->is_active);
        $this->assertTrue($product->fresh()->is_active);
        $this->assertSame(0, $product->fresh()->stock);
        $this->assertSame(2, $item->fresh()->quantity);
        $this->assertSame(1, $returnItem->fresh()->quantity);
        $this->assertInstanceOf(\DateTimeInterface::class, $invoice->fresh()->issued_at);
        $this->assertInstanceOf(\DateTimeInterface::class, $return->fresh()->returned_at);

        $invoice->delete();

        $this->assertNull(Invoice::find($invoice->id));
        $this->assertTrue(Invoice::withTrashed()->findOrFail($invoice->id)->trashed());
        $this->assertTrue(InvoiceItem::whereKey($item->id)->exists());
        $this->assertTrue(SalesReturn::whereKey($return->id)->exists());
        $this->assertTrue(ReturnItem::whereKey($returnItem->id)->exists());
    }
}
