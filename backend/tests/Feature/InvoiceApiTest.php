<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_creation_and_show_return_nested_items_and_server_totals(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $customer = Customer::create(['name' => 'Customer']);
        $product = Product::create(['name' => 'Product', 'sku' => 'ONE', 'price' => '12.50', 'stock' => 10]);
        $response = $this->postJson('/api/invoices', [
            'customer_id' => $customer->id,
            'user_id' => 999,
            'total' => 1,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertCreated()->assertJsonPath('data.total', '25.00')
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.status', 'issued')
            ->assertJsonPath('data.items.0.unit_price', '12.50');
        $this->getJson('/api/invoices/'.$response->json('data.id'))->assertOk()
            ->assertJsonPath('data.customer.id', $customer->id)
            ->assertJsonPath('data.items.0.product.id', $product->id);
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_employee_only_sees_own_invoices_and_admin_sees_all(): void
    {
        $employee = User::factory()->create();
        $other = User::factory()->create();
        $customer = Customer::create(['name' => 'Customer']);
        $own = Invoice::create(['invoice_number' => 'INV-1', 'customer_id' => $customer->id, 'user_id' => $employee->id]);
        $foreign = Invoice::create(['invoice_number' => 'INV-2', 'customer_id' => $customer->id, 'user_id' => $other->id]);
        Sanctum::actingAs($employee);
        $this->getJson('/api/invoices?per_page=1')->assertOk()
            ->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.id', $own->id);
        $this->getJson("/api/invoices/{$foreign->id}")->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['role' => UserRole::Admin]));
        $this->getJson('/api/invoices')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson("/api/invoices/{$foreign->id}")->assertOk();
        $this->putJson("/api/invoices/{$own->id}", [])->assertStatus(405);
        $this->deleteJson("/api/invoices/{$own->id}")->assertStatus(405);
    }

    public function test_validation_and_stock_failures_leave_no_invoice(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $customer = Customer::create(['name' => 'Customer']);
        $product = Product::create(['name' => 'Product', 'sku' => 'ONE', 'price' => 10, 'stock' => 2]);
        $payload = ['customer_id' => $customer->id, 'items' => [['product_id' => $product->id, 'quantity' => 3]]];
        $this->postJson('/api/invoices', $payload)->assertConflict()->assertJsonPath('errors.available_stock', 2);
        $payload['items'][] = $payload['items'][0];
        $this->postJson('/api/invoices', $payload)->assertUnprocessable()->assertJsonValidationErrors('items.0.product_id');
        $this->postJson('/api/invoices', ['customer_id' => $customer->id, 'items' => []])->assertUnprocessable();
        $this->assertDatabaseCount('invoices', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_guests_cannot_access_invoice_endpoints(): void
    {
        $this->getJson('/api/invoices')->assertUnauthorized();
        $this->postJson('/api/invoices', [])->assertUnauthorized();
        $this->getJson('/api/invoices/1')->assertUnauthorized();
    }
}
