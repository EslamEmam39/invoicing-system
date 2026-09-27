<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_crud_and_admin_deletion(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => UserRole::Employee]));
        foreach ([
            'products' => ['name' => 'Widget', 'sku' => 'SKU-1', 'price' => '10.50', 'stock' => 5],
            'customers' => ['name' => 'Customer']
        ] as $path => $payload) {
            $id = $this->postJson("/api/$path", $payload)->assertCreated()->assertJsonPath('success', true)->json('data.id');
            $this->getJson("/api/$path/$id")->assertOk()->assertJsonPath('data.name', $payload['name']);
            $this->patchJson("/api/$path/$id", ['name' => 'Updated'] + $payload)->assertOk()->assertJsonPath('data.name', 'Updated');
            $this->getJson("/api/$path?per_page=1")->assertOk()->assertJsonPath('meta.per_page', 1);
            $this->deleteJson("/api/admin/$path/$id")->assertForbidden();
            Sanctum::actingAs(User::factory()->create(['role' => UserRole::Admin]));
            $this->deleteJson("/api/$path/$id")->assertStatus(405);
            $this->deleteJson("/api/admin/$path/$id")->assertOk();
            $this->getJson("/api/$path/$id")->assertNotFound();
            Sanctum::actingAs(User::factory()->create(['role' => UserRole::Employee]));
        }
    }

    public function test_authentication_and_validation(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
        $this->getJson('/api/customers')->assertUnauthorized();
        $this->deleteJson('/api/admin/products/1')->assertUnauthorized();
        $this->deleteJson('/api/admin/customers/1')->assertUnauthorized();
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/products', [])->assertUnprocessable()->assertJsonPath('success', false);
        $this->postJson('/api/customers', [])->assertUnprocessable();
        $this->getJson('/api/products?per_page=101')->assertUnprocessable();
        $payload = ['name' => 'Widget', 'sku' => 'SKU-1', 'price' => 10, 'stock' => 0];
        $this->postJson('/api/products', $payload)->assertCreated();
        $this->postJson('/api/products', $payload)->assertUnprocessable()->assertJsonValidationErrors('sku');
        $this->postJson('/api/products', ['sku' => 'SKU-2', 'price' => -1, 'stock' => -1] + $payload)
            ->assertUnprocessable()->assertJsonValidationErrors(['price', 'stock']);
    }

    public function test_linked_records_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        Sanctum::actingAs($user);
        $customer = Customer::create(['name' => 'Customer']);
        $product = Product::create(['name' => 'Product', 'sku' => 'LINKED', 'price' => 10]);
        $invoice = $customer->invoices()->create(['invoice_number' => 'INV-1', 'user_id' => $user->id]);
        $item = $invoice->items()->make(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10]);
        $item->subtotal = 10;
        $item->save();
        $this->deleteJson("/api/admin/customers/{$customer->id}")->assertConflict()->assertJsonPath('success', false);
        $this->deleteJson("/api/admin/products/{$product->id}")->assertConflict();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
