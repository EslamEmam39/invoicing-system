<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_transactions_and_reruns_preserve_stock(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('products', 10);
        $this->assertDatabaseCount('customers', 10);
        $this->assertDatabaseCount('invoices', 4);
        $this->assertDatabaseCount('invoice_items', 6);
        $this->assertDatabaseCount('returns', 2);
        $this->assertDatabaseCount('return_items', 2);
        $this->assertSame(47, Product::where('sku', 'DEMO-001')->firstOrFail()->stock);
        $this->assertSame(15, Product::where('sku', 'DEMO-003')->firstOrFail()->stock);
        $this->assertSame(100, Product::where('sku', 'DEMO-004')->firstOrFail()->stock);
        $this->assertSame('2300.00', Invoice::where('invoice_number', 'DEMO-INV-001')->firstOrFail()->total);
        $this->assertSame(InvoiceStatus::Cancelled, Invoice::where('invoice_number', 'DEMO-INV-003')->firstOrFail()->status);
        $stock = Product::orderBy('id')->pluck('stock', 'id')->all();

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('invoices', 4);
        $this->assertDatabaseCount('invoice_items', 6);
        $this->assertDatabaseCount('returns', 2);
        $this->assertDatabaseCount('return_items', 2);
        $this->assertSame($stock, Product::orderBy('id')->pluck('stock', 'id')->all());
    }
}
