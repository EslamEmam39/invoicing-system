<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class InvoiceCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function invoice(): Invoice
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        Sanctum::actingAs($user);
        $customer = Customer::create(['name' => 'Customer']);
        $product = Product::create(['name' => 'Product', 'sku' => 'ONE', 'price' => 10, 'stock' => 10]);

        return app(InvoiceService::class)->create([
            'customer_id' => $customer->id,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ], $user);
    }

    public function test_admin_cancels_and_stock_is_restored_only_once(): void
    {
        $invoice = $this->invoice();
        $product = $invoice->items->first()->product;
        $product->update(['is_active' => false]);
        $url = "/api/admin/invoices/{$invoice->id}/cancel";
        $this->postJson($url)->assertOk()->assertJsonPath('data.status', 'cancelled')->assertJsonPath('data.total', '30.00');
        $this->assertSame(10, $product->fresh()->stock);
        $this->postJson($url)->assertConflict()->assertJsonPath('success', false);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseCount('invoice_items', 1);
    }

    public function test_invoice_with_returns_cannot_be_cancelled(): void
    {
        $invoice = $this->invoice();
        $invoice->returns()->create(['return_number' => 'RET-1']);
        $this->postJson("/api/admin/invoices/{$invoice->id}/cancel")->assertConflict();
        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
        $this->assertSame(7, $invoice->items->first()->product->fresh()->stock);
    }

    public function test_employee_cannot_cancel_even_own_invoice(): void
    {
        $invoice = $this->invoice();
        $owner = $invoice->user;
        $owner->update(['role' => UserRole::Employee]);
        Sanctum::actingAs($owner);
        $this->postJson("/api/admin/invoices/{$invoice->id}/cancel")->assertForbidden();
        $this->assertSame(7, $invoice->items->first()->product->fresh()->stock);
    }

    public function test_guest_cannot_cancel(): void
    {
        $this->postJson('/api/admin/invoices/1/cancel')->assertUnauthorized();
    }

    public function test_stock_restoration_is_rolled_back_on_status_write_failure(): void
    {
        $invoice = $this->invoice();
        $event = 'eloquent.updating: '.Invoice::class;
        Event::listen($event, function () {
            $this->assertSame(10, Product::first()->stock);
            throw new RuntimeException('Simulated status write failure');
        });
        try {
            app(InvoiceService::class)->cancel($invoice);
            $this->fail('Expected failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated status write failure', $exception->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
        $this->assertSame(7, Product::first()->stock);
    }
}
