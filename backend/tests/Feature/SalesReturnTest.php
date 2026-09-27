<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\SalesReturnService;
use App\Support\DatabaseLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class SalesReturnTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_overflow_is_rejected_without_creating_a_return(): void
    {
        $invoice = $this->invoice();
        $item = $invoice->items->first();
        $item->product->update(['stock' => DatabaseLimits::MAX_STOCK]);
        $this->postJson('/api/returns', [
            'invoice_id' => $invoice->id,
            'items' => [['invoice_item_id' => $item->id, 'quantity' => 1]],
        ])->assertConflict();
        $this->assertDatabaseCount('returns', 0);
        $this->assertSame(DatabaseLimits::MAX_STOCK, $item->product->fresh()->stock);
    }

    private function invoice()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $customer = Customer::create(['name' => 'Customer']);
        $product = Product::create(['name' => 'Product', 'sku' => 'ONE', 'price' => '12.50', 'stock' => 10]);

        return app(InvoiceService::class)->create(['customer_id' => $customer->id, 'items' => [['product_id' => $product->id, 'quantity' => 4]]], $user);
    }

    public function test_partial_returns_use_sale_price_and_cannot_exceed_remaining_quantity(): void
    {
        $invoice = $this->invoice();
        $item = $invoice->items->first();
        $item->product->update(['price' => 999, 'is_active' => false]);
        $payload = ['invoice_id' => $invoice->id, 'items' => [['invoice_item_id' => $item->id, 'quantity' => 3]]];
        $this->postJson('/api/returns', $payload)->assertCreated()->assertJsonPath('data.total', '37.50')->assertJsonPath('data.items.0.unit_price', '12.50');
        $this->assertSame(9, $item->product->fresh()->stock);
        $this->postJson('/api/returns', $payload)->assertConflict();
        $payload['items'][0]['quantity'] = 1;
        $this->postJson('/api/returns', $payload)->assertCreated();
        $this->assertSame(10, $item->product->fresh()->stock);
        $this->assertSame('50.00', $invoice->fresh()->total);
    }

    public function test_foreign_invoice_is_forbidden_for_employee_but_allowed_for_admin(): void
    {
        $invoice = $this->invoice();
        $payload = ['invoice_id' => $invoice->id, 'items' => [['invoice_item_id' => $invoice->items->first()->id, 'quantity' => 1]]];
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/returns', $payload)->assertForbidden();
        $this->assertDatabaseCount('returns', 0);
        Sanctum::actingAs(User::factory()->create(['role' => UserRole::Admin]));
        $this->postJson('/api/returns', $payload)->assertCreated();
    }

    public function test_cancelled_invoice_and_duplicate_items_are_rejected(): void
    {
        $invoice = $this->invoice();
        $line = ['invoice_item_id' => $invoice->items->first()->id, 'quantity' => 1];
        $this->postJson('/api/returns', ['invoice_id' => $invoice->id, 'items' => [$line, $line]])->assertUnprocessable();
        $invoice->update(['status' => InvoiceStatus::Cancelled]);
        $this->postJson('/api/returns', ['invoice_id' => $invoice->id, 'items' => [$line]])->assertConflict();
        $this->assertDatabaseCount('returns', 0);
    }

    public function test_failure_after_stock_update_rolls_back_return_and_stock(): void
    {
        $invoice = $this->invoice();
        $item = $invoice->items->first();
        $event = 'eloquent.updating: '.SalesReturn::class;
        Event::listen($event, function () {
            $this->assertSame(7, Product::first()->stock);
            throw new RuntimeException('Simulated failure');
        });
        try {
            app(SalesReturnService::class)->create($invoice, ['items' => [['invoice_item_id' => $item->id, 'quantity' => 1]]]);
            $this->fail('Expected failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated failure', $exception->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertDatabaseCount('returns', 0);
        $this->assertDatabaseCount('return_items', 0);
        $this->assertSame(6, Product::first()->stock);
    }

    public function test_guest_cannot_create_return(): void
    {
        $this->postJson('/api/returns', [])->assertUnauthorized();
        $this->getJson('/api/returns')->assertUnauthorized();
        $this->getJson('/api/returns/1')->assertUnauthorized();
    }

    public function test_return_visibility_follows_invoice_ownership(): void
    {
        $invoice = $this->invoice();
        $return = app(SalesReturnService::class)->create($invoice, [
            'items' => [['invoice_item_id' => $invoice->items->first()->id, 'quantity' => 1]],
        ]);
        $this->getJson('/api/returns?per_page=1')->assertOk()->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $return->id);
        $this->getJson("/api/returns/{$return->id}")->assertOk()->assertJsonPath('data.total', '12.50')->assertJsonPath('data.items.0.quantity', 1);
        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/returns')->assertOk()->assertJsonPath('meta.total', 0);
        $this->getJson("/api/returns/{$return->id}")->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['role' => UserRole::Admin]));
        $this->getJson('/api/returns')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson("/api/returns/{$return->id}")->assertOk();
        $this->getJson('/api/returns/99999')->assertNotFound();
        $this->getJson('/api/returns?per_page=101')->assertUnprocessable();
    }
}
